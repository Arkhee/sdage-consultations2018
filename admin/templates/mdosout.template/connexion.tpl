{MSG_ERREUR}
<form id="formConnexion" name="formConnexion" method="post" action="{FORM_CONNEXION_PAGE}">
	<label for='login'>Identifiant :</label><input type='text' name='user_Login' id="login" value="" /><br />
	<label for='password'>Mot de passe :</label><input type='password' name='user_Password' id="password" value="" /><br/>
	<input type="submit" class="buttonConnect" name="connexion" value="Connexion" />
	<input type="hidden" name="section" id="section" value="connexion" />
	<input type="hidden" name="_tablename" id="_tablename" value="#__mdtb_users" />
	<input type="hidden" name="redir" id="redir" value="{FORM_RETURN_URL}" />
</form>
<center>
	<p>Vous n'avez pas encore de compte ?<br /><a href="{LIEN_INSCRIPTION}">Inscrivez-vous ici</a></p>
</center>
