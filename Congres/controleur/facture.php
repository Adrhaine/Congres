<?php
include_once 'class/Facture.php';
include 'config/database.php';

$db = new Database();
$connection = $db->getConnexion();
$facture = new Facture($connection);

// Récupérer l'action pour marquer la facture comme réglée/non réglée
$toggleId = isset($_GET['toggle']) ? intval($_GET['toggle']) : null;

if ($toggleId !== null) {
    // Appeler la méthode toggleFactureReglee pour inverser l'état
    $facture->toggleFactureReglee($toggleId);

    // Redirection pour éviter la répétition de l'action sur rafraîchissement
    header("Location: ./?action=defaut");
    exit();
}

// Récupérer le filtre choisi
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'toutes';

// Récupérer le terme de recherche si présent
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Vérifier si l'utilisateur est admin ou pas
if ($_SESSION['is_admin']) {
    // Si l'utilisateur est admin, on récupère toutes les factures avec un filtre et recherche
    $factures = $facture->getFacturesAdmin($filtre, $search);
} else {
    // Si ce n'est pas un admin, on filtre les factures par id_congressiste et recherche
    $id_congressiste = $_SESSION['id_congressiste'];
    $factures = $facture->getFacturesParCongressiste($id_congressiste, $search);
}

include "./vue/vueFacture.php";
?>
