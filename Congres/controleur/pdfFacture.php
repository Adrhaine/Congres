<?php
include_once 'class/Facture.php';
include 'config/database.php';
require_once './libs/fpdf.php';

// Activer le tampon de sortie
ob_start();

// Connexion à la base de données
$db = new Database();
$connection = $db->getConnexion();
$facture = new Facture($connection);

// Récupérer l'ID de la facture
$id_facture = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_facture <= 0) {
    die("Erreur : ID de facture invalide.");
}

$factureInfo = $facture->getFactureInfo($id_facture);
if (!$factureInfo) {
    die("Erreur : Facture introuvable.");
}

$id_congressiste = $factureInfo['id_congressiste'];
$hotelCost = $facture->calculateHotelCost($id_congressiste);
$sessionCost = $facture->calculateSessionsCostByCongressiste($id_congressiste);
$activiteCost = $facture->calculateActivitesCostByCongressiste($id_congressiste);
$totalCost = $hotelCost + $sessionCost + $activiteCost;
$totalAfterAcompte = $totalCost - $factureInfo['acompte'];

$activiteDetails = $facture->getActiviteDetails($id_congressiste);
$sessionDetails = $facture->getSessionDetails($id_congressiste);

// Récupérer les informations de l'organisme payeur
$organismePayeur = $facture->getOrganismePayeur($id_congressiste);
$organismeNom = isset($organismePayeur['organisme_nom']) ? $organismePayeur['organisme_nom'] : 'Non renseigné';

// Génération du PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// En-tête : Logo et informations de l'entreprise
$pdf->SetFont('Arial', 'B', 16);
$pdf->Image('./logo.jpg', 10, 6, 30); // Remplace 'logo.jpg' par le chemin correct
$pdf->Cell(100, 10, utf8_decode('Factures&Co'), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(90, 10, utf8_decode('Adresse de l\'entreprise : Rue Exemple, Paris'), 0, 1, 'R');
$pdf->Cell(0, 10, utf8_decode('Téléphone : +33 1 23 45 67 89 | Email : contact@entreprise.com'), 0, 1, 'R');

// Ligne de séparation
$pdf->Ln(5);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, 35, 200, 35);

// Informations sur le client
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Facture à :'), 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, utf8_decode(
    $factureInfo['congressiste_nom'] . ' ' . $factureInfo['congressiste_prenom'] . "\n" . 
    'Organisme payeur : ' . $organismeNom
), 0);

// Détails de la facture
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Facture No :'), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, $factureInfo['id_facture'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Date :'), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, $factureInfo['date'], 0, 1);

// Tableau des coûts
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(90, 10, utf8_decode('Description'), 1, 0, 'L', true);
$pdf->Cell(50, 10, utf8_decode('Quantité'), 1, 0, 'C', true);
$pdf->Cell(50, 10, utf8_decode('Prix (EUR)'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
if ($hotelCost > 0) {
    $pdf->Cell(90, 10, utf8_decode('Hébergement'), 1);
    $pdf->Cell(50, 10, '1', 1, 0, 'C');
    $pdf->Cell(50, 10, number_format($hotelCost, 2), 1, 1, 'C');
}

if (!empty($activiteDetails)) {
    foreach ($activiteDetails as $activite) {
        $pdf->Cell(90, 10, utf8_decode($activite['activite_nom']), 1);
        $pdf->Cell(50, 10, '1', 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($activite['activite_prix'], 2), 1, 1, 'C');
    }
}

if (!empty($sessionDetails)) {
    foreach ($sessionDetails as $session) {
        $pdf->Cell(90, 10, utf8_decode($session['session_nom']), 1);
        $pdf->Cell(50, 10, '1', 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($session['session_prix'], 2), 1, 1, 'C');
    }
}

// Totaux
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, utf8_decode('Sous-total'), 1, 0, 'R');
$pdf->Cell(50, 10, number_format($totalCost, 2), 1, 1, 'C');

$pdf->Cell(140, 10, utf8_decode('Acompte'), 1, 0, 'R');
$pdf->Cell(50, 10, number_format($factureInfo['acompte'], 2), 1, 1, 'C');

$pdf->Cell(140, 10, utf8_decode('Total dû'), 1, 0, 'R');
$pdf->Cell(50, 10, number_format($totalAfterAcompte, 2), 1, 1, 'C');

// Conditions générales
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->MultiCell(0, 10, utf8_decode(
    'Conditions générales : Cette facture est payable sous 30 jours. Tout retard de paiement peut entraîner des pénalités.'
));

// Télécharger ou afficher le PDF
ob_end_clean();
$pdf->Output('I', 'Facture_' . $factureInfo['id_facture'] . '.pdf');

?>