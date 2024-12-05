<?php
class User {
    private $conn;
    private $db_table = "user"; // Table des utilisateurs

    public $id;
    public $login;
    public $mdp;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Méthode pour vérifier l'existence de l'utilisateur
    public function verifyUser($login, $mdp) {
        $sql = "SELECT * FROM $this->db_table WHERE login = ? AND mdp = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $login);
        $stmt->bindValue(2, $mdp);

        if ($stmt->execute()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login'] = $user['login'];

                // Vérifier si le login est "admin"
                if ($login === 'admin') {
                    $_SESSION['is_admin'] = true;
                } else {
                    $_SESSION['is_admin'] = false;
                }

                return true; // L'utilisateur est authentifié
            }
        }
        return false; // Si aucune correspondance n'a été trouvée
    }

    // Méthode pour récupérer les informations d'un utilisateur par ID
    public function getUserById($id) {
        $sql = "SELECT * FROM $this->db_table WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Retourne les données de l'utilisateur
        }
        return null; // Aucun utilisateur trouvé
    }

    // Méthode pour vérifier si un utilisateur existe déjà (par exemple, pour l'inscription)
    public function userExists($login) {
        $sql = "SELECT COUNT(*) FROM $this->db_table WHERE login = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $login);

        if ($stmt->execute()) {
            return $stmt->fetchColumn() > 0; // Retourne true si l'utilisateur existe
        }
        return false;
    }

    public function getIdCongressisteByLogin($login) {
        $query = "SELECT id_congressiste FROM user WHERE login = :login LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':login', $login);
        $stmt->execute();

        // Retourner l'id_congressiste
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id_congressiste'];
    }
}
?>
