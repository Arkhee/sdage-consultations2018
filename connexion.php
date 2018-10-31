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
$myClasseMetierMDOSout->initSection();
$myClasseMetierMDOSout->bind($_FILES);
$myClasseMetierMDOSout->bind($_GET);
$myClasseMetierMDOSout->bind($_POST);
$myClasseMetierMDOSout->handle();
//die("Ligne ... ".__LINE__." auth : ".($auth->isLoaded()?"connecte":"deconnecte")."<br />\r\n".print_r($auth,true));
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
<script defer src="js/frontctl.js<?php Tools::displayDateTagForFile(__DIR__."/js/frontctl.js"); ?>"></script>
<link href="/misesenforme.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<link href="css/styles-fo.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
</head>
<body class="<?php echo $auth->isLoaded()?("connecte connecte_".$auth->user_Rank):""; ?>">
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
			<div id="accueilBandeChemin" class="col-md-8"><!-- InstanceBeginEditable name="chemin" --><a href="index.php">Impact des pressions sur les masses d'eau</a><!-- InstanceEndEditable --></div>
			<div class="col-md-4">
				<?php echo Util::affRecherche(); ?>
			</div>			
		</div>
		<?php if($myClasseMetierMDOSout->sectionHasMenu())
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
        <h1 align="center">
			<?php if(!$auth->isLoaded()) { ?>
			Consultation 2018 - Connexion
			<?php } else { ?>
			Liste de vos avis et compte
			<?php } ?>
		</h1>
		<?php if(sdage_metier::isConsultationTerminee()) { 
			echo "<h3>Consultation terminée : ".time()." vs ".strtotime(sdage_metier::DATE_FIN_CONSULTATION)."</h3>";
		} ?>
        <?php if($myClasseMetierMDOSout->msg_error!="") { ?>
							<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $myClasseMetierMDOSout->msg_error;?></div><br />
					<?php } ?>

					<?php if($myClasseMetierMDOSout->msg_info!="") { ?>
							<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $myClasseMetierMDOSout->msg_info;?></div>
					<?php } ?>
				  <?php echo $myClasseMetierMDOSout->sectionContent(); ?>
		  <!-- InstanceEndEditable -->
		</div>
	</div>
	
	<div id="footer" class="container">
		<?php Util::insertFooter(); ?>
	</div>
    <?php Util::affPied(true);	?>
</body>
<!-- InstanceEnd --></html>