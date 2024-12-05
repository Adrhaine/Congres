<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
        <title>Gestion des Factures</title>
    </head>
<body>
    <h1>Navigation</h1>
    
    <!-- Afficher le login de l'utilisateur connecté -->
    <div>
        <?php
        if (isset($_SESSION['login'])) { // Si l'utilisateur est connecté
            echo "Connecté en tant que : " . htmlspecialchars($_SESSION['login']);
        } else {
            echo "Non connecté";
        }
        ?>
    </div>

    <nav>
        <ul>
            <?php
            if (isset($_SESSION['login'])) { // Si l'utilisateur est connecté
                echo '<li><a href="./?action=defaut">Voir Facture</a></li>';

                // Si l'utilisateur est admin, afficher "Ajouter une Facture"
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
                    echo '<li><a href="./?action=ajoutFacture">Ajouter une Facture</a></li>';
                }
                echo '<li><a href="./?action=deconnexion">Se déconnecter</a></li>';
            }
            ?>
        </ul>
    </nav>
</body>
</html>
