<?php
include "./vue/entete.php";
?>

<div class="facture-container">
    <?php if ($detailsFacture && $detailsFacture['facture']) : ?>
        <?php $facture = $detailsFacture['facture']; ?>
        
        <div class="facture-header">
            <h2>Détails de la Facture #<?php echo htmlspecialchars($facture['id_facture']); ?></h2>
            <p><strong>Date :</strong> <?php echo htmlspecialchars($facture['date']); ?></p>
        </div>

        <div class="facture-section">
            <h3>Informations sur le Congressiste</h3>
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($facture['congressiste_nom']); ?></p>
            <p><strong>Prénom :</strong> <?php echo htmlspecialchars($facture['congressiste_prenom']); ?></p>
            <p><strong>Acompte :</strong> <?php echo number_format($facture['acompte'], 2); ?> €</p>
        </div>

        <?php if ($detailsFacture['hotel']) : ?>
            <div class="facture-section">
                <h3>Hôtel</h3>
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($detailsFacture['hotel']['hotel_nom']); ?></p>
                <p><strong>Prix par participant :</strong> <?php echo number_format($detailsFacture['hotel']['hotel_prix'], 2); ?> €</p>
                <?php if (!empty($detailsFacture['hotel']['petitdej_prix'])) : ?>
                    <p><strong>Petit-déjeuner :</strong> <?php echo number_format($detailsFacture['hotel']['petitdej_prix'], 2); ?> €</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="facture-section">
            <h3>Activités</h3>
            <?php if (!empty($detailsFacture['activites'])) : ?>
                <ul>
                    <?php foreach ($detailsFacture['activites'] as $activite) : ?>
                        <li><?= htmlspecialchars($activite['activite_nom']) . " - " . number_format($activite['activite_prix'], 2) . " €"; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>Aucune activité sélectionnée.</p>
            <?php endif; ?>
        </div>

        <div class="facture-section">
            <h3>Sessions</h3>
            <?php if (!empty($detailsFacture['sessions'])) : ?>
                <ul>
                    <?php foreach ($detailsFacture['sessions'] as $session) : ?>
                        <li><?= htmlspecialchars($session['session_nom']) . " - " . number_format($session['session_prix'], 2) . " €"; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>Aucune session sélectionnée.</p>
            <?php endif; ?>
        </div>

        <div class="facture-summary">
            <p><strong>Montant Hôtel :</strong> <?= number_format($detailsFacture['montants']['hotel'], 2); ?> €</p>
            <p><strong>Montant Sessions :</strong> <?= number_format($detailsFacture['montants']['sessions'], 2); ?> €</p>
            <p><strong>Montant Activités :</strong> <?= number_format($detailsFacture['montants']['activites'], 2); ?> €</p>
            <p><strong>Total :</strong> <?= number_format($detailsFacture['montants']['total'], 2); ?> €</p>
            <p><strong>Total après acompte :</strong> <?= number_format($detailsFacture['montants']['totalAfterAcompte'], 2); ?> €</p>
        </div>
    <?php else : ?>
        <p>Erreur : Aucun détail disponible pour cette facture.</p>
    <?php endif; ?>
</div>

<div class="button-container">
    <a href="index.php?action=defaut" class="button">Retour à la liste des factures</a>
    <a href="./?action=pdfFacture&id=<?= $facture['id_facture'] ?>" class="button">Voir PDF</a>
    <?php
if ($_SESSION['is_admin']) {
    echo '<a href="index.php?action=supprimerFacture&id=' . $facture['id_facture'] . '" 
          class="button button-delete" 
          onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette facture ?\');">
          Supprimer la facture
          </a>';
}
?>

    
</div>