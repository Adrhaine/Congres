<?php
class Facture {

    // Connexion
    private $conn;
    // Table
    private $db_table = "facture";

    public $id_facture;
    public $date;
    public $reglee;
    public $leCongressiste;

    // Connexion BD
    public function __construct($db){
        $this->conn = $db;
    }

    public function addFacture($date, $id_congressiste, $reglee = 0) {
        try {
            // Insérer la facture avec uniquement les champs présents dans la table
            $sqlInsert = "INSERT INTO facture (id_facture, date, reglee, id_congressiste) VALUES (NULL, ?, ?, ?)";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->bindValue(1, $date);
            $stmtInsert->bindValue(2, $reglee);
            $stmtInsert->bindValue(3, $id_congressiste);
    
            // Exécuter l'insertion
            if ($stmtInsert->execute()) {
                return true; // Succès de l'ajout de la facture
            } else {
                return false; // Échec de l'ajout de la facture
            }
        } catch (Exception $e) {
            // En cas d'exception, afficher le message d'erreur pour le débogage
            echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }
     

    public function calculateHotelCost($id_congressiste) {
        $sql = "SELECT hotel.prix_par_participant, hotel.prix_petitdej, congressiste.petitdej
                FROM congressiste
                JOIN hotel ON congressiste.id_hotel = hotel.id
                WHERE congressiste.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            $hotelCost = $result['prix_par_participant'];
            if ($result['petitdej'] == 1) {
                $hotelCost += $result['prix_petitdej'];
            }
            return $hotelCost;
        }
        return 0; // Valeur par défaut si aucun hôtel n'est trouvé
    }
    
    public function calculateSessionsCost($sessions) {
        if (empty($sessions)) return 0;
    
        $placeholders = implode(',', array_fill(0, count($sessions), '?'));
        $sql = "SELECT SUM(prix) AS total_sessions FROM session WHERE id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);
    
        foreach ($sessions as $index => $sessionId) {
            $stmt->bindValue($index + 1, $sessionId, PDO::PARAM_INT);
        }
    
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total_sessions'] : 0;
    }
    
    public function calculateActivitesCost($activites) {
        if (empty($activites)) return 0;
    
        $placeholders = implode(',', array_fill(0, count($activites), '?'));
        $sql = "SELECT SUM(prix) AS total_activites FROM activite_culturelle WHERE id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);
    
        foreach ($activites as $index => $activiteId) {
            $stmt->bindValue($index + 1, $activiteId, PDO::PARAM_INT);
        }
    
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total_activites'] : 0;
    }
    

    public function removeFacture($id_facture) {
        $sql = "DELETE FROM $this->db_table WHERE id_facture = ?";
        $stmt = $this->conn->prepare($sql);
    
        $stmt->bindValue(1, $id_facture, PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Facture supprimée'], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['message' => 'Erreur lors de la suppression de la facture'], JSON_PRETTY_PRINT);
        }
    }

    public function getUneFacture(){
        $sql = "SELECT * FROM $this->db_table WHERE id = ?"; 
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(1, $this->id_facture);

        if($stmt->execute()){
            $message = 'Facture trouvée';
        }
        else{
            $message = 'Facture non trouvée';
        }
        header("Content-Type: application/json");
        $reponse = array ('message', $message);
        echo json_encode($reponse , JSON_PRETTY_PRINT);
    }

    public function getLesFactures($filtre, $orderBy = 'id_facture ASC') {
        $sql = "SELECT 
                    facture.id_facture, 
                    facture.date, 
                    facture.reglee, 
                    congressiste.nom AS congressiste_nom, 
                    congressiste.prenom AS congressiste_prenom,
                    organisme_payeur.nom AS organisme_nom
                FROM facture
                JOIN congressiste ON facture.id_congressiste = congressiste.id
                LEFT JOIN organisme_payeur ON congressiste.id_organisme_payeur = organisme_payeur.id";
    
        // Ajouter une clause WHERE selon le filtre
        if ($filtre === 'reglees') {
            $sql .= " WHERE facture.reglee = 1";
        } elseif ($filtre === 'non_reglees') {
            $sql .= " WHERE facture.reglee = 0";
        }
    
        // Ajouter la clause ORDER BY pour le tri
        $sql .= " ORDER BY $orderBy";
    
        // Préparer et exécuter la requête
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            return $stmt;
        } else {
            echo json_encode(['message' => 'Factures non trouvées'], JSON_PRETTY_PRINT);
            return null;
        }
    }
    
    

    public function getDetailsFacture($id_facture) {
        $factureDetails = $this->getFactureInfo($id_facture);
        if (!$factureDetails) {
            echo json_encode(['message' => 'Facture non trouvée'], JSON_PRETTY_PRINT);
            return null;
        }
    
        // Ajouter les détails supplémentaires
        $factureDetails['hotel'] = $this->getHotelDetails($factureDetails['id_congressiste']);
        $factureDetails['activites'] = $this->getActiviteDetails($factureDetails['id_congressiste']);
        $factureDetails['sessions'] = $this->getSessionDetails($factureDetails['id_congressiste']);
        
        // Ajouter l'organisme payeur
        $factureDetails['organisme_payeur'] = $this->getOrganismePayeur($factureDetails['id_congressiste']);
        
        return $factureDetails;
    }
    
    // Fonction pour récupérer les informations de l'organisme payeur
    public function getOrganismePayeur($id_congressiste) {
        $sql = "SELECT organisme_payeur.nom AS organisme_nom
                FROM congressiste
                LEFT JOIN organisme_payeur ON congressiste.id_organisme_payeur = organisme_payeur.id
                WHERE congressiste.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
    
    
    // Fonction pour récupérer les informations de base de la facture
    public function getFactureInfo($id_facture) {
        $sql = "SELECT 
                    facture.id_facture, facture.date, facture.reglee, 
                    congressiste.nom AS congressiste_nom, congressiste.prenom AS congressiste_prenom,
                    congressiste.acompte AS acompte, congressiste.id AS id_congressiste
                FROM facture
                JOIN congressiste ON facture.id_congressiste = congressiste.id
                WHERE facture.id_facture = ?";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_facture, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
    
    // Fonction pour récupérer les détails de l'hôtel
    public function getHotelDetails($id_congressiste) {
        $sql = "SELECT hotel.nom AS hotel_nom, hotel.prix_par_participant AS hotel_prix, hotel.prix_petitdej AS petitdej_prix
                FROM congressiste
                LEFT JOIN hotel ON congressiste.id_hotel = hotel.id
                WHERE congressiste.id = ?";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
    
    // Fonction pour récupérer les détails des activités culturelles
    public function getActiviteDetails($id_congressiste) {
        $sql = "SELECT activite_culturelle.nom AS activite_nom, activite_culturelle.prix AS activite_prix
                FROM participer_activite
                JOIN activite_culturelle ON participer_activite.id_activite = activite_culturelle.id
                WHERE participer_activite.id_congressiste = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Toujours retourner les activités spécifiques au congressiste
    }
    
    
    
    
    // Fonction pour récupérer les détails des sessions
    public function getSessionDetails($id_congressiste) {
        $sql = "SELECT session.nom AS session_nom, session.prix AS session_prix
                FROM participer_session
                JOIN session ON participer_session.id_session = session.id
                WHERE participer_session.id_congressiste = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Toujours retourner les sessions spécifiques au congressiste
    }
    
        

    public function calculateSessionsCostByCongressiste($id_congressiste) {
        $sql = "SELECT SUM(session.prix) AS total_sessions
                FROM participer_session
                JOIN session ON participer_session.id_session = session.id
                WHERE participer_session.id_congressiste = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total_sessions'] : 0;
    }
    
    public function calculateActivitesCostByCongressiste($id_congressiste) {
        $sql = "SELECT SUM(activite_culturelle.prix) AS total_activites
                FROM participer_activite
                JOIN activite_culturelle ON participer_activite.id_activite = activite_culturelle.id
                WHERE participer_activite.id_congressiste = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total_activites'] : 0;
    }    

    // Méthode pour récupérer les factures avec les informations du congressiste et de l'organisme payeur (admin)
public function getFacturesAdmin($filtre, $search) {
    $query = "SELECT facture.*, congressiste.nom AS congressiste_nom, congressiste.prenom AS congressiste_prenom, 
              organisme_payeur.nom AS organisme_nom
              FROM facture 
              LEFT JOIN congressiste ON facture.id_congressiste = congressiste.id
              LEFT JOIN organisme_payeur ON congressiste.id_organisme_payeur = organisme_payeur.id 
              WHERE 1";

    // Filtrage par état (réglée ou non réglée)
    if ($filtre === 'reglees') {
        $query .= " AND facture.reglee = 1";
    } elseif ($filtre === 'non_reglees') {
        $query .= " AND facture.reglee = 0";
    }

    // Recherche par nom de congressiste
    if (!empty($search)) {
        $query .= " AND (congressiste.nom LIKE :search OR congressiste.prenom LIKE :search)";
    }

    $stmt = $this->conn->prepare($query);
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt->bindParam(':search', $searchTerm);
    }
    $stmt->execute();

    return $stmt;
}

// Méthode pour récupérer les factures d'un congressiste spécifique avec recherche
public function getFacturesParCongressiste($id_congressiste, $search) {
    $query = "SELECT facture.*, congressiste.nom AS congressiste_nom, congressiste.prenom AS congressiste_prenom, 
              organisme_payeur.nom AS organisme_nom
              FROM facture 
              LEFT JOIN congressiste ON facture.id_congressiste = congressiste.id 
              LEFT JOIN organisme_payeur ON congressiste.id_organisme_payeur = organisme_payeur.id 
              WHERE facture.id_congressiste = :id_congressiste";

    // Recherche par nom de congressiste
    if (!empty($search)) {
        $query .= " AND (congressiste.nom LIKE :search OR congressiste.prenom LIKE :search)";
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_congressiste', $id_congressiste);
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt->bindParam(':search', $searchTerm);
    }
    $stmt->execute();

    return $stmt;
}

    public function toggleFactureReglee($id_facture) {
        // Récupérer l'état actuel de la facture
        $sqlSelect = "SELECT reglee FROM facture WHERE id_facture = ?";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->bindValue(1, $id_facture, PDO::PARAM_INT);
        $stmtSelect->execute();
        $currentStatus = $stmtSelect->fetchColumn();
    
        if ($currentStatus !== false) {
            // Inverser le statut (0 -> 1 ou 1 -> 0)
            $newStatus = $currentStatus == 1 ? 0 : 1;
    
            // Mettre à jour le statut dans la base de données
            $sqlUpdate = "UPDATE facture SET reglee = ? WHERE id_facture = ?";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindValue(1, $newStatus, PDO::PARAM_INT);
            $stmtUpdate->bindValue(2, $id_facture, PDO::PARAM_INT);
            $stmtUpdate->execute();
        }
    }
    
    public function factureExiste($id_congressiste) {
        $sql = "SELECT COUNT(*) AS total FROM facture WHERE id_congressiste = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $result['total'] > 0; // Retourne `true` si une facture existe
    }
    
    public function searchFactures($search) {
        $sql = "SELECT 
                    facture.id_facture, 
                    facture.date, 
                    facture.reglee, 
                    congressiste.nom AS congressiste_nom, 
                    congressiste.prenom AS congressiste_prenom, 
                    organisme_payeur.nom AS organisme_nom
                FROM facture
                JOIN congressiste ON facture.id_congressiste = congressiste.id
                LEFT JOIN organisme_payeur ON congressiste.id_organisme_payeur = organisme_payeur.id
                WHERE congressiste.nom LIKE :search OR congressiste.prenom LIKE :search";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
    
        return $stmt;
    }    
}
?>