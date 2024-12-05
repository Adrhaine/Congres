<?php
class Session {
    public $id;
    public $nom;
    public $prix;
    public $heure;
    public $date;

    // Connexion
    private $conn;
    // Table
    private $db_table = "session";


    public function getAllSessions() {
        $sql = "SELECT id, nom, prix FROM $this->db_table";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute()) {
            return $stmt;
        } else {
            return null;
        }
    }

    public function getSessionsByCongressiste($id_congressiste) {
        $sql = "SELECT session.id AS id_session, session.nom, session.prix 
                FROM participer_session
                JOIN session ON participer_session.id_session = session.id
                WHERE participer_session.id_congressiste = ?";
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