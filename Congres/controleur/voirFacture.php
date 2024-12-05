<?php
include_once 'class/Facture.php';
include 'config/database.php';

$db = new Database();
$connection = $db->getConnexion();
$facture = new Facture($connection);

// Récupérer l'ID de la facture depuis l'URL
$id_facture = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_facture <= 0) {
    die("Erreur : ID de facture invalide.");
}

// Récupérer les informations de la facture
$factureInfo = $facture->getFactureInfo($id_facture);
// Vérifier si la facture existe
if (!$factureInfo) {
    die("Erreur : Facture introuvable.");
}

// Vérifier si l'utilisateur est admin ou appartient à la facture
$id_congressiste = $factureInfo['id_congressiste'];

if ($_SESSION['is_admin']) {
    // L'admin peut accéder à toutes les factures
    $userAuthorized = true;
} else {
    // Si ce n'est pas un admin, vérifier que l'utilisateur est celui auquel la facture appartient
    $userAuthorized = ($_SESSION['id_congressiste'] == $id_congressiste);
}

if (!$userAuthorized) {
    die('
        <div style="color: red; font-weight: bold;">
            Erreur : Vous n\'êtes pas autorisé à voir cette facture.
        </div>
        <br>
        <a href="./?action=vueFacture.php" style="padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;">
            Retourner à la liste des factures
        </a>
    ');
}

// Calcul des montants
$hotelCost = $facture->calculateHotelCost($id_congressiste);
$sessionCost = $facture->calculateSessionsCostByCongressiste($id_congressiste);
$activiteCost = $facture->calculateActivitesCostByCongressiste($id_congressiste);
$totalCost = $hotelCost + $sessionCost + $activiteCost;

// Calcul du total après déduction de l'acompte
$totalAfterAcompte = $totalCost - $factureInfo['acompte'];

// Récupérer les détails des activités et des sessions
$activiteDetails = $facture->getActiviteDetails($id_congressiste);
$sessionDetails = $facture->getSessionDetails($id_congressiste);

// Organiser les détails pour la vue
$detailsFacture = [
    'facture' => $factureInfo,
    'hotel' => $facture->getHotelDetails($id_congressiste),
    'activites' => $activiteDetails,
    'sessions' => $sessionDetails,
    'montants' => [
        'hotel' => $hotelCost,
        'sessions' => $sessionCost,
        'activites' => $activiteCost,
        'total' => $totalCost,
        'totalAfterAcompte' => $totalAfterAcompte
    ]
];

// Inclure la vue
include "./vue/vueDetailFacture.php";
?>