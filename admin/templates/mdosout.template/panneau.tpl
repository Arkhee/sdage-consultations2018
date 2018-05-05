<div class="panneau" >
<a href="index.php"><button>Accéder aux données</button></a>
<form style="display: inline; float:right;" action="admin/index.php" method="get">
	<input type="hidden" name="redir" value="referer" />
	<input type="hidden" name="_tablename" value="%23__mdtb_users" />
	<input type="hidden" name="_action" value="logout" />
  <button>Déconnexion</button>
</form>
<h2>Votre espace</h2>
<div id="tabs">
  <ul>
    <li><a href="#tabs-avis">Vos avis</a></li>
    <li><a href="#tabs-compte">Informations personnelles</a></li>
  </ul>
  <div id="tabs-avis">
	{resultats}
  </div>
  <div id="tabs-compte">
    <form id="formMiseAJour" name="formMiseAJour" method="post" action="" onsubmit='return frontCtl.testFormulaireInscription(this);'>
		<label for='user_firstname'>Votre Prénom :</label><input type='text' name='user_firstname' id="user_firstname" value="{user_firstname}" /><br />
		<label for='user_name'>Votre Nom :</label><input type='text' name='user_name' id="user_name" value="{user_name}" /><br />
		<label for='type_structure'>Votre type de structure :</label>{CMB_TYPE_STRUCTURE}<br />
		<label for='user_nomstructure'>Nom de votre structure :</label><input type='text' name='user_nomstructure' id="user_nomstructure" value="{user_nomstructure}" /><br />
		<label for='user_email'>Votre EMail :</label><input type='text' name='user_email' id="user_email" value="{user_email}" /><br />
		<label for='password1'>Mot de passe :</label><input type='password' name='user_password' id="password1" value="" /><br/>
		<label for='password2'>Répétez le Mot de passe :</label><input type='password' name='user_password2' id="password2" value="" /><br/>
		<input type="submit" name="miseajour" value="Mise à jour" />
		<input type="hidden" name="section" id="section" value="connexion" />
		<input type="hidden" name="user_ID" id="user_ID" value="{user_ID}" />
		<input type="hidden" name="_tablename" id="_tablename" value="#__mdtb_users" />
	</form>
  </div>
</div><br />
<a href="index.php"><button>Accéder aux données</button></a>
</div>