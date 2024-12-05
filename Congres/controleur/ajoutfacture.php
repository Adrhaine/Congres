<?php

require_once './config/database.php';
require_once './class/Facture.php';
require_once './class/Congressiste.php';
require_once './class/Session.php';
require_once './class/Activite.php';
require_once './class/Hotel.php';

// Connexion à la base de données
$database = new Database();
$db = $database->getConnexion();

// Instancier les classes nécessaires
$facture = new Facture($db);
$congressiste = new Congressiste($db);

// Variables pour afficher les montants
$hotelCost = 0;
$sessionCost = 0;
$activiteCost = 0;
$totalCost = 0;

// Récupération des données POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $id_congressiste = $_POST['id_congressiste'];

    // Vérification si une facture existe déjà
    if (!empty($id_congressiste) && $facture->factureExiste($id_congressiste)) {
        $messageErreur = "Une facture existe déjà pour ce congressiste.";
    } else {
        $reglee = isset($_POST['reglee']) ? 1 : 0; // Facture réglée ou non

        // Calculer les montants
        if (!empty($id_congressiste)) {
            $hotelCost = $facture->calculateHotelCost($id_congressiste);
            $sessionCost = $facture->calculateSessionsCostByCongressiste($id_congressiste);
            $activiteCost = $facture->calculateActivitesCostByCongressiste($id_congressiste);
            $totalCost = $hotelCost + $sessionCost + $activiteCost;
        }

        // Si "Créer la facture" est cliqué
        if (isset($_POST['create_facture'])) {
            $success = $facture->addFacture($date, $id_congressiste, $reglee);
            if ($success) {
                header("Location: ./index.php?action=defaut");
                exit();
            } else {
                $messageErreur = "Erreur : la facture n'a pas été ajoutée.";
            }
        }
    }
}

// Inclure la vue du formulaire de création de facture
include "./vue/vueAjouterFacture.php";
