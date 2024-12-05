<?php

session_unset();
session_destroy();
header("Location: ./?action=connexion");

include "./vue/vueConnexion.php";
exit;
?>