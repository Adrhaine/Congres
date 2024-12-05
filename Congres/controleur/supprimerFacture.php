<?php
include_once 'class/Facture.php';
include 'config/database.php';

$db = new Database();
$connection = $db->getConnexion();
$facture = new Facture($connection);

// Récupérer l'ID de la facture
$id_facture = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Supprimer la facture si l'ID est valide
if ($id_facture > 0) {
    $facture->removeFacture($id_facture);
}

// Rediriger vers la liste des factures après suppression
header("Location: index.php?action=defaut");
exit();
