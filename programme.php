<?php
define("NO_PCB_NEWS",true);
require_once(_APP_ROOT_DIR_."includes/init.inc.php"); 
/*
 * diren_mdosout
 * Created on 30 oct. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 *
 * File : index.php
 * Description :
 *
 */
 
$path_pre="admin/";
require_once($path_pre."config.inc.php");
//require_once($path_pre."local.classes/sdage_metier.class.php");
$myClasseMetierMDOSout=new sdage_metier($database,$path_pre,__FILE__);
$myClasseMetierMDOSout->setAuth($auth);
$myClasseMetierMDOSout->bind($_GET);
$myClasseMetierMDOSout->bind($_POST);
$myClasseMetierMDOSout->handle();
$myBaseGestion=new stdClass();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr"><!-- InstanceBegin template="/Templates/contenu-avec-menu.dwt" codeOutsideHTMLIsLocked="false" -->

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- InstanceBeginEditable name="doctitle" -->
<title>Masses d'eau souterraines - Fiches de caract&eacute;risation</title>
<!-- InstanceEndEditable -->
	<meta name="Description" content="Le Portail du bassin Rh&ocirc;ne-M&eacute;diterran&eacute;e, regroupe les services de l'Etat et organismes publics producteurs d'informations sur l'eau et les milieux aquatiques des r&eacute;gions Bourgogne (pour partie), Franche-Comt&eacute;, Languedoc-Roussillon, Provence-Alpes-C&ocirc;te-d'Azur et Rh&ocirc;ne-Alpes." />
<?php echo Util::insertJSHeaders(); ?>
<link href="/includes/js/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="/site.css" rel="stylesheet" type="text/css" />
<script defer src="js/fontawesome-all.min.js"></script>
<script defer src="js/frontctl.js"></script>
<link href="/misesenforme.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<link href="css/styles-fo.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<body>
	<div id="shadowLeftMenu" class="hidden-lg hidden-md"></div>
	<div id="header">
    	<a href="<?php echo DIREN_RACINE_WEB; ?>" title="Retour &agrave; l'accueil">
            <div id="accueilBandeau"  class="hidden-sm hidden-xs"></div>
            <div id="accueilBandeauMobile" class="hidden-md hidden-lg">
                <h1>L'eau dans le bassin Rh&ocirc;ne-M&eacute;diterran&eacute;e</h1>
            </div>
        </a>
	</div>
    <nav id="topMenu" class="navbar navbar-default" style="display:none;">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				 </button>
             </div>
			<div class="collapse navbar-collapse" id="menu-navbar">
				<?php echo Util::insertMenuPrincipal("utf8"); ?>
			</div>
		</div>
	</nav>
	
	<div id="content" class="container pageSetup">
		<div id="contentHeader" class="row">
			<div id="accueilBandeChemin" class="col-md-8"><!-- InstanceBeginEditable name="chemin" --><a href="index.php">Etat des masses d'eau dans le cadre du SDAGE 2016-2021</a><!-- InstanceEndEditable --></div>
			<div class="col-md-4">
				<?php echo Util::affRecherche(); ?>
			</div>			
		</div>
		<?php if($myClasseMetierMDOSout->section==="accueil")
		{
			require_once("menu_lateral_complementaire.php");
			?>
			
			<div id="contentCentral" class="col-md-9 col-sm-10 col-xs-10" role="main">
			<?php
		}
		else
		{
			?>
			<div id="leftMenu" class="col-md-3 col-sm-1 col-xs-1" role="complementary" style='display: none;'>
			</div>
			<div id="contentCentral" class="col-md-12 col-sm-12 col-xs-12" role="main">
			<?php
		}
		?>
        <!-- InstanceBeginEditable name="contenu" -->
		<?php if ($auth->isLoaded()) { ?>
        <h1 align="center">Précisions et consignes pour une consultation efficace</h1>
        
		<h2>Etat des lieux du bassin Rhône-Méditerranée 2019<br />
Consultation sur le niveau d’impact des pressions sur les masses d’eau</h2>
<ol>
	<li>La masse d’eau est l’unité spatiale pour identifier les mesures à conduire pour atteindre des objectifs de bon état des eaux définis par la directive cadre sur l’eau. De quelques km à quelques dizaines de km pour les cours d’eau et eaux côtières, de quelques dizaines d’hectares à quelques dizaines de Km² pour les plans d’eau et lagunes, et de plusieurs dizaines de Km² pour les eaux souterraines. Cette unité est désormais couramment utilisée par les acteurs. 
		<ul><li>Les avis demandés doivent reposer sur des appréciations (1) des impacts des activités humaines à l’échelle de la taille des masses d’eau (sans considérer les impacts très localisés)  et (2) des pressions sur lesquelles agir pour restaurer l’état des eaux à ces échelles.</li></ul></li>
	<li>Les impacts qualifient l’incidence des pressions humaines sur chaque masse d’eau. Leur évaluation sert à identifier les pressions qui peuvent empêcher d’atteindre le bon état. L’estimation de ces  impacts a été réalisée avec des données de la surveillance des milieux (qualité et quantité) du bassin, des études locales et de données sur les pressions issues d’outils et méthodes nationaux ou de bassin. Elle possède une validité statistique fondée sur des chroniques de données pluriannuelles et des approches modélisées adaptées à l’échelle et au contexte du bassin. Elle n’a pas pour objet de rendre compte des hétérogénéités locales mais au contraire de faire ressortir les situations contrastées entre les masses d’eau. Leur expertise par les acteurs consultés demande une connaissance de l’étendue des secteurs dégradés ou menacés, des points de pollution ou obstacles majeurs … ainsi que, le cas échéant, une expertise des effets des travaux réalisés, en application des programmes de mesures des SDAGE 2010-2015 et 2016-2021. 
		<ul><li>Il est demandé d’apprécier, à dire d’expert, les niveaux d’impacts proposées (1-faible ;           2-moyen ; 3-fort), en les comparant à la connaissance de la situation des masses d’eau par les acteurs de terrain. Il n’est pas attendu procéder à une vérification des données et des méthodes utilisées pour produire les évaluations proposées.</li>
		<li>Les demandes d’ajustement des impacts devront être fondées sur une connaissance actuelle de la dimension des problèmes rencontrés (ex : ordres de grandeur des linéaires ou superficies concernées). </li></ul></li>
	<li>Les pressions à l’origine du risque de non-atteinte des objectifs environnementaux subies par chaque masse d’eau sont celles dont l’effet est significatif par rapport à la taille de la masse d’eau, seules (pression dont l’impact est jugé fort) ou en association avec d’autres pressions (combinaison de plusieurs pressions dont l’impact individuel est moyen) pour être à l’origine d’une dégradation actuelle ou d’une menace de dégradation à un horizon proche (« pressions importantes »). Le travail de co-construction du programme de mesures portera sur ces pressions.
		<ul><li>Les demandes de modification des pressions à l’origine du risque de non-atteinte des objectifs environnementaux devront être justifiées par un ou des ajustements des impacts de ces pressions.</li></ul></li>
</ol>
<p>Les zonages pré-existants dans certains territoires (Zones vulnérables, zones sensibles, tronçons liste 2 au titre de la continuité, zones de répartition des eaux …) ne peuvent être retenus comme un motif d’ajustement des résultats. En effet, l’état des lieux est un travail technique de diagnostic qui permet, d’alimenter une éventuelle révision de ces zonages, lorsque nécessaire. </p>
<p>La recevabilité des avis émis dépendra du respect de ces consignes, garantes de la co-construction d’un l’état des lieux 2019 partagé pour élaborer une politique de l’eau dans le bassin, à la fois ambitieuse et réaliste pour la période 2022-2027.</p>

		<?php }?>
		
		  <!-- InstanceEndEditable -->
		</div>
	</div>
	
	<div id="footer" class="container">
		<?php Util::insertFooter(); ?>
	</div>
    <?php Util::affPied(true);	?>
</body>
<!-- InstanceEnd --></html>