<?php
/*
 * Toutateam Groupware
 * Created on 14 mars 08
 * Copyright  ©  2008 Yannick Bétemps yannick@alternetic.com
 * www.toutateam.com
 * Author : Yannick Bétemps
 * 
 * Toutateam is a fork project of PHProjekt v4.0
 * 
 * File : layout.inc.php
 * Description : 
 * 
 */
$myBase->set_main_menu($TheMainMenu);
//$myBase->set_upload_dir($TheClsUser->getGroupFolder($user_group));
$myBase->set_default();

$myBase->init();
$myBase->binddata($_POST,$_GET);
$myBase->handle($_POST,$_GET);
$myBase->redir();
$myBase->set_curview("default");

$path_pre="./";
$myClasseMetierMDOSout=new sdage_metier($database,$path_pre,__FILE__);
$myClasseMetierMDOSout->bind($_GET);
$myClasseMetierMDOSout->bind($_POST);
$myClasseMetierMDOSout->bind($_FILES);
$myClasseMetierMDOSout->handle();
if($auth->user_Rank!="admin")
{
	die("Accès interdit");
}
/*
$database->setQuery("SELECT * FROM entreprises;");
$myList=$database->loadObjectList();
foreach($myList as $curentr)
{
	$curentr->entr_Code=trim($curentr->entr_Code);
	$curentr->entr_Nom=trim($curentr->entr_Nom);
	$database->updateObject("entreprises",$curentr,"entr_ID");
}
*/
TT_Template::HTMLHeaderBegin(Tools::Translate($myBase->name));
TT_Template::BoxIncludes();
$myBase->showHeader();
TT_Template::HTMLHeaderEnd();

/*
 * Page beginning : start tag
 */

TT_Template::HTMLBodyBegin();

echo "<div  class=\"mainlayout import\">\n";
if($myBase->hasMessages())
{
	TT_Template::BlockBegin("curbase","specialmessages","","mod_header");
	echo $myBase->getMessages();
	TT_Template::BlockEnd();
}

if($myBase->hasMenu())
	TT_Template::BlockBegin("curbase","menu","","mod_header");
$myBase->showMenu();
if($myBase->hasMenu())
	TT_Template::BlockEnd();
echo "<div id='mainContent' >\n";
TT_Template::BlockBegin("curbase","content","","mod_content");
?>
<h2>Import des données SDAGE</h2>
<form method="post" enctype="multipart/form-data">
	<label>Fichier CSV contenant les données :</label>
	<input type="file" name="import_echantillons">
	<input type="hidden" name="section" value="import" />
	<br /><input type="checkbox" name="skipupdate" id="skipupdate" value="skip" />
	<label for"skipupdate">Ne pas mettre à jour (import nouveaux uniquement)</label>
	<input type="submit" name="importer" value="Tester et importer" />
</form>
<?php
	if($myClasseMetierMDOSout->msg_error!="") {
		echo "<div id='display_err' style='border:1px solid #AA0000;padding:10px; font-size:16px;margin-top:10px;'>".$myClasseMetierMDOSout->msg_error."</div>";
	}
	if($myClasseMetierMDOSout->msg_info!="") {
		echo "<div id='display_msg' style='border:1px solid #00AA00;padding:10px; font-size:16px;margin-top:10px;'>".$myClasseMetierMDOSout->msg_info."</div>";
	}
?>
<h3>Liste des colonnes attendues</h3>
<?php 
$liste=sdage_metier::importGetListeColonnes();
echo "<ul>";
foreach($liste as $keyCol => $valCol)
{
	echo "<li>".$valCol."</li>";
}
echo "</ul>";
//echo "<pre>".print_r(sdage_metier::importGetListeColonnes(),true)."</pre>"; ?>
<?php
TT_Template::BlockEnd();
echo "</div endid='mainContent' >\n";
$myBase->showFooter();
echo "</div>\n";
TT_Template::BoxInit("curbase");
TT_Template::HTMLBodyEnd();
