<?php 
include "./vue/entete.php";
?>

<h1>Liste des Factures</h1>

<!-- Boutons de filtrage -->
<nav>
    <ul>
        <?php
    if ($_SESSION['is_admin']) {
        echo '<li><a href="?filtre=toutes">Voir Toutes les Factures</a></li>';
        echo'<li><a href="?filtre=reglees">Voir les Factures Réglées</a></li>';
        echo'<li><a href="?filtre=non_reglees">Voir les Factures Non Réglées</a></li>';
        }
        ?>
    </ul>
</nav>

<!-- Barre de recherche -->
<?php
if ($_SESSION['is_admin']) {
    echo '<form method="GET" action="" class="search-form">';
        echo '<input type="hidden" name="action" value="defaut">';
        // Utilisation de guillemets simples pour éviter le conflit avec les doubles guillemets de l'attribut
        echo '<input type="text" name="search" placeholder="Rechercher par nom de congressiste..." value="';
        echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; // Concatenation de la valeur
        echo '" class="search-input">';
    echo '<button type="submit" class="button">Rechercher</button>';
    echo '</form>';
}
?>


<?php
// Vérifier si des factures sont disponibles
if ($factures && $factures->rowCount() > 0) {
    echo '<div class="cards-container">';

    // Parcourir les factures et afficher chaque carte
    while ($row = $factures->fetch(PDO::FETCH_ASSOC)) {
        $regleeText = $row['reglee'] ? 'Oui' : 'Non';
        $toggleAction = $row['reglee'] ? 'Marquer Non Réglée' : 'Marquer Réglée';

        echo '<div class="card">';
        echo "<h3>Facture #" . htmlspecialchars($row['id_facture']) . "</h3>";
        echo "<p><strong>Date :</strong> " . htmlspecialchars($row['date']) . "</p>";
        echo "<p><strong>Réglée :</strong> $regleeText</p>";
        echo "<p><strong>Congressiste :</strong> " . 
             (!empty($row['congressiste_nom']) ? htmlspecialchars($row['congressiste_nom']) : 'Inconnu') . " " . 
             (!empty($row['congressiste_prenom']) ? htmlspecialchars($row['congressiste_prenom']) : '') . 
             "</p>";
        echo "<p><strong>Organisme Payeur :</strong> " . 
             (!empty($row['organisme_nom']) ? htmlspecialchars($row['organisme_nom']) : 'N/A') . 
             "</p>";

        // Si l'utilisateur est admin, afficher le bouton "Marquer Réglée"
        if ($_SESSION['is_admin']) {
            echo '<a href="./?toggle=' . htmlspecialchars($row['id_facture']) . '" class="button">' . htmlspecialchars($toggleAction) . '</a>';
        }
        
        echo '<a href="./?action=voirFacture&id=' . htmlspecialchars($row['id_facture']) . '" class="button">Voir Détails</a>';
        echo '</div>';
    }

    echo '</div>';
} else {
    echo "<p>Aucune facture trouvée.</p>";
}
?>
