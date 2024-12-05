<?php 
include "./vue/entete.php";
?>

<h2>Créer une facture</h2>

<?php if (isset($messageErreur)): ?>
    <div class="error-message">
        <p><?= htmlspecialchars($messageErreur) ?></p>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <!-- Date -->
    <label for="date">Date :</label>
    <input type="date" id="date" name="date" value="<?= isset($_POST['date']) ? htmlspecialchars($_POST['date']) : '' ?>" required><br><br>

    <!-- Liste déroulante des congressistes -->
    <label for="id_congressiste">Congressiste :</label>
    <select id="id_congressiste" name="id_congressiste" onchange="this.form.submit()" required>
        <option value="">-- Sélectionnez un congressiste --</option>
        <?php
        require_once './class/Congressiste.php';

        $stmt = $congressiste->getAllCongressistes();

        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = (isset($_POST['id_congressiste']) && $_POST['id_congressiste'] == $row['id']) ? 'selected' : '';
                echo "<option value='{$row['id']}' $selected>{$row['nom']} {$row['prenom']}</option>";
            }
        }
        ?>
    </select><br><br>

    <?php if (isset($_POST['id_congressiste']) && !empty($_POST['id_congressiste']) && !isset($messageErreur)): ?>
    <?php
    // Calcul des montants pour le congressiste sélectionné
    $id_congressiste = $_POST['id_congressiste'];
    $hotelCost = $facture->calculateHotelCost($id_congressiste);
    $sessionCost = $facture->calculateSessionsCostByCongressiste($id_congressiste);
    $activiteCost = $facture->calculateActivitesCostByCongressiste($id_congressiste);
    $totalCost = $hotelCost + $sessionCost + $activiteCost;

    // Récupération des sessions et activités associées
    $sessions = $facture->getSessionDetails($id_congressiste);
    $activites = $facture->getActiviteDetails($id_congressiste);
    ?>

    <div class="facture-totals">
        <!-- Montant Hôtel -->
        <label>Montant Hôtel :</label>
        <span><?= number_format($hotelCost, 2) ?> €</span>

        <!-- Sessions -->
        <label>Sessions :</label>
        <?php if (!empty($sessions)): ?>
            <ul>
                <?php foreach ($sessions as $session): ?>
                    <li><?= htmlspecialchars($session['session_nom']) ?> - <?= number_format($session['session_prix'], 2) ?> €</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune session associée.</p>
        <?php endif; ?>
        <label>Montant pour les sessions :</label>
        <span><?= number_format($sessionCost, 2) ?> €</span>

        <!-- Activités -->
        <label>Activités Culturelles :</label>
        <?php if (!empty($activites)): ?>
            <ul>
                <?php foreach ($activites as $activite): ?>
                    <li><?= htmlspecialchars($activite['activite_nom']) ?> - <?= number_format($activite['activite_prix'], 2) ?> €</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune activité associée.</p>
        <?php endif; ?>
        <label>Montant pour les activités :</label>
        <span><?= number_format($activiteCost, 2) ?> €</span>

        <!-- Montant Total -->
        <label>Montant Total :</label>
        <span><?= number_format($totalCost, 2) ?> €</span>

        <!-- Bouton pour créer la facture -->
        <input type="hidden" name="total_cost" value="<?= $totalCost ?>">
        <button type="submit" name="create_facture" class="button">Créer la Facture</button>
    </div>
<?php endif; ?>
</form>
