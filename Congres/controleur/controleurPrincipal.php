<?php
function controleurPrincipal($action){
    $lesActions = array();
    $lesActions["defaut"] = "facture.php";
    $lesActions["connexion"] = "connexion.php";
    $lesActions["deconnexion"] = "deconnexion.php";
    $lesActions["ajoutFacture"] = "ajoutFacture.php";
    $lesActions["voirFacture"] = "voirFacture.php";
    $lesActions["supprimerFacture"] = "supprimerFacture.php";
    $lesActions["pdfFacture"] = "pdfFacture.php";


    
    if (array_key_exists ( $action , $lesActions )){
        return $lesActions[$action];
    }
    else{
        return $lesActions["defaut"];
    }

}

?>