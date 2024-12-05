<?php
class Activite {
    public $id;
    public $nom;
    public $prix;
    public $date;
    public $heure;

    // Connexion
    private $conn;
    // Table
    private $db_table = "activite_culturelle";


    public function getAllActivites() {
        $sql = "SELECT id, nom, prix FROM $this->db_table";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute()) {
            return $stmt;
        } else {
            return null;
        }
    }

    public function getActivitesByCongressiste($id_congressiste) {
        $sql = "SELECT activite_culturelle.id AS id_activite, activite_culturelle.nom, activite_culturelle.prix 
                FROM participer_activite
                JOIN activite_culturelle ON participer_activite.id_activite = activite_culturelle.id
                WHERE participer_activite.id_congressiste = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id_congressiste, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
       

        public function __construct($db){
        $this->conn = $db;
    }
    
}
?>