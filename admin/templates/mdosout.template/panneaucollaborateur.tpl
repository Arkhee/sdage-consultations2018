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
    <li><a href="#tabs-avis">Avis déposés</a></li>
  </ul>
  <div id="tabs-avis">
	  <p><a href="csv.php">Télécharger tous les avis déposés au format CSV</a></p>
  </div>
</div>
