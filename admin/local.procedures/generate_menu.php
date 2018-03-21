<?php
/*
 * diren-pcb
 * Created on 26 mars 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : generate_menu.php
 * Description : Génère automatiquement le menu à afficher en colonne de gauche
 */
require_once("../local.lang/fr.inc.php");
require_once("utils.class.php");
require_once("../classes/tools.class.php");
if(!isset($_GET["pass"]) || $_GET["pass"]!="menu")
	die("Pas les droits");
$json_path="importdata/";
$tpl_path="codetemplates/";
$menu_file="../local.config.menu.inc.php";
$myClassList=ImportUtils::getClassList($json_path);
if($myClassList!==false)
{
	$myClassDescriptionArray=array();
	$myTemplate=file_get_contents($tpl_path."_mdtb_templatefile.class.phpt");
	//echo "Liste : ".Tools::Display($myClassList);
	$myTxtMenu="<?php \n \$TheArrayModules=array(\n";
	foreach($myClassList as $curtable)
		$myTxtMenu.="\"tpl_".$curtable."\"=>(\"".$curtable."\"),\n";
	$myTxtMenu.=");\n?>";
	if(file_put_contents($menu_file,$myTxtMenu))
		echo "Menu créé<br>\n";
	else
		echo "Erreur à la création du menu<br>\n";
}
?>
<a href="index.php">Retour</a>