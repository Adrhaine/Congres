<?php
// Assure-toi que ta connexion à la base de données est initialisée
require_once './config/database.php'; 
require_once './class/User.php'; 

$database = new Database(); 
$db = $database->getConnexion(); 
$user = new User($db); 

if (isset($_POST["validerConnexion"])) {
    $login = $_POST["login"];
    $mdp = $_POST["mdp"];

    if ($user->verifyUser($login, $mdp)) {
        // Vérification si l'utilisateur est admin
        if ($login === 'admin') {
            $_SESSION['is_admin'] = true;  // L'utilisateur est admin
        } else {
            $_SESSION['is_admin'] = false; // Utilisateur normal
            // Récupérer l'id du congressiste lié à cet utilisateur
            $id_congressiste = $user->getIdCongressisteByLogin($login);
            $_SESSION['id_congressiste'] = $id_congressiste;
        }

        // Sauvegarder le login dans la session
        $_SESSION['login'] = $login;

        // Rediriger en fonction du rôle
        if ($_SESSION['is_admin']) {
            header("Location: ./?action=default.php");
        } else {
            header("Location: ./?action=default");
        }
        exit();
    } else {
        echo '<div style="color: red; font-weight: bold; text-align: center; font-size: 18px; margin-bottom: 10px;">Erreur : Veuillez vous connecter.</div>';
    }
}


include "./vue/vueConnexion.php";
?>
