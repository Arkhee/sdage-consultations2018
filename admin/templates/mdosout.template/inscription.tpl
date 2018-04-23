<h2>Merci de votre intérêt pour le programme de mesure</h2>
<p>Avant de pouvoir donner votre avis, merci de vous inscrire via le formulaire ci-dessous</p>
<em>Notez que votre email sera utilisé en tant qu'identifiant pour la connexion au service</em>
<div id="msg_erreur_inscription"></div>
{MSG_ERREUR}
<form id="formInscription" name="formInscription" method="post" action="{FORM_INSCRIPTION_PAGE}" onsubmit='return frontCtl.testFormulaireInscription(this);'>
	<label for='user_firstname'>Votre Prénom :</label><input type='text' name='user_firstname' id="user_firstname" value="{user_firstname}" /><br />
	<label for='user_name'>Votre Nom :</label><input type='text' name='user_name' id="user_name" value="{user_name}" /><br />
	<label for='type_structure'>Votre type de structure :</label><select name='type_structure' id="type_structure">
		<option value="">(Choisissez un structure)</option>
		<option value="Conseils départementaux">Conseils départementaux</option>
		<option value="Conseils régionaux EPTB, structures locales de gestion de l'eau">Conseils régionaux EPTB, structures locales de gestion de l'eau</option>
		<option value="Parcs nationaux et régionaux">Parcs nationaux et régionaux</option>
		<option value="Chambres d’agriculture">Chambres d’agriculture</option>
		<option value="Chambres de commerce et d’industrie">Chambres de commerce et d’industrie</option>
		<option value="Chambres des métiers et de l’artisanat">Chambres des métiers et de l’artisanat</option>
		<option value="Grands établissements industriels (EDF, CNR, BRL)">Grands établissements industriels (EDF, CNR, BRL)</option>
		<option value="Fédérations pour la pêche et la protection du milieu aquatique">Fédérations pour la pêche et la protection du milieu aquatique</option>
		<option value="Associations de protection de la nature, CREN et autres associations majeures éventuelles">Associations de protection de la nature, CREN et autres associations majeures éventuelles</option>
	</select><br />
	<label for='user_nomstructure'>Nom de votre structure :</label><input type='text' name='user_nomstructure' id="user_nomstructure" value="{user_nomstructure}" /><br />
	<label for='user_email'>Votre EMail :</label><input type='text' name='user_email' id="user_email" value="{user_email}" /><br />
	<label for='password1'>Mot de passe :</label><input type='password' name='user_password' id="password1" value="" /><br/>
	<label for='password2'>Répétez le Mot de passe :</label><input type='password' name='user_password2' id="password2" value="" /><br/>
	<input type="submit" name="inscription" value="Inscription" />
	<input type="hidden" name="section" id="section" value="inscription" />
	<input type="hidden" name="clef" id="clef" value="{CLEF_TRANSMISE}" />
	<input type="hidden" name="_tablename" id="_tablename" value="#__mdtb_users" />
	<input type="hidden" name="redir" id="redir" value="{FORM_RETURN_URL}" />
</form>
