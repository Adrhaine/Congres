<?php
include "./controleur/controleurPrincipal.php";
session_start();  // Assurer que la session est démarrée

// Si l'utilisateur n'est pas connecté et qu'il essaie d'accéder à une page autre que la connexion
if (!isset($_SESSION['user_id']) && $_GET["action"] != "connexion") {
    // Redirige vers la page de connexion
    header("Location: ./?action=connexion");
    exit();
}

// Si l'utilisateur est connecté, on peut charger l'action demandée
if (isset($_GET["action"])) {
    $action = $_GET["action"];
} else {
    $action = "defaut";  // Action par défaut si rien n'est spécifié
}

// On charge le contrôleur correspondant à l'action
$fichier = controleurPrincipal($action);
include "./controleur/$fichier";
?>
