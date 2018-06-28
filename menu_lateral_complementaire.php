<div id="leftMenu" class="col-md-3 col-sm-1 col-xs-1" role="complementary">
	<nav data-spy="affix" data-offset-top="175" class="hidden-sm hidden-xs affix-top" id="leftMenuPCScreen">
		<ul>
			<li role="menu" class="dropdown">
				<a href="index.php" title="Rubrique gestion de l'eau" class="toRight">Consultation 2018</a>
				<ul class="dropdown-menu" role="menu">
					  <li class="recherche"><a href="index.php">Recherche de masses d'eau</a></li>
					  <?php if ($auth->isLoaded()) { ?><li><a href="programme.php">Précisions et consignes</a></li><?php }?>
					  <li><a href="connexion.php"><?php echo ($auth->isLoaded()?"Votre espace":"Déposer un avis"); ?></a></li>
					  <li class="recherche"><a href="https://eaurmc.lizmap.com/map/index.php/view/map/?repository=eaurmc&project=RNAOE_Actualisation_Etat_des_lieux" target="_blank">Accès cartographique aux données</a></li>
				</ul>
			</li>
		</ul>
	</nav>
	<?php echo ($auth->isLoaded())?"<div class=\"identification\">Bonjour, ".$auth->user_FirstName." ".$auth->user_Name."</div>":""; ?>
</div>