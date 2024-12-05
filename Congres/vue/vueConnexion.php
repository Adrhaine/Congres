<?php 
   include "./vue/entete.php";
?>  

<form method="POST" action="./?action=connexion" class="connexion-form">
   <h2>Connexion</h2>

   <!-- Champ Login -->
   <label for="login">Login*:</label>
   <input type="text" name="login" id="login" value="" required>

   <!-- Champ Mot de passe -->
   <label for="mdp">Mot de passe*:</label>
   <input type="password" name="mdp" id="mdp" value="" required>

   <!-- Boutons -->
   <div class="button-container">
      <input type="submit" value="Valider" name="validerConnexion" class="button">
      <input type="reset" value="Annuler" class="button">
   </div>
</form>

<?php 
   include "./vue/pied.php";
?>