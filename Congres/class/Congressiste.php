<?php
class Congressiste {
    public $id;
    public $nom;
    public $prenom;
    public $adresse;
    public $email;
    public $acompte;
    public $date_inscription;
    public $preference_hotel;
    public $petitdej;
    public $id_organisme_payeur;
    public $id_hotel;

    // Connexion
    private $conn;
    // Table
    private $db_table = "congressiste";

    public function getAllCongressistes() {
        $sql = "SELECT id, nom, prenom FROM $this->db_table";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute()) {
            return $stmt; // Retourne le statement pour utilisation dans la vue
        } else {
            return null; // Retourne null en cas d'échec
        }
    }

    public function __construct($db){
        $this->conn = $db;
    }
    
    
}
?>