<?php
/*
 * Created on 16 mai 07
 * Copyright  ©  2007 Yannick Bétemps yannick@alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : index.php
 * Description : 
 * 
 */
$path_pre="";
require_once("config.inc.php");
$myBase=new mdtb_groups($database,$template_name,basename(__FILE__),$path_abs);
$myBase->set_auth($auth);
require_once("layout.inc.php");
die();
//$myBase->set_upload_dir($TheClsUser->getGroupFolder($user_group));
$myBase->set_default();
$myBase->init();
$myBase->binddata($_POST,$_GET);
$myBase->handle($_POST,$_GET);
$myBase->redir();
$myBase->set_curview("default");
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

echo "<div class=\"mainlayout\">\n";
if($myBase->hasMessages())
{
	TT_Template::BlockBegin("fichesaccueil","specialmessages","","mod_header");
	echo $myBase->getMessages();
	TT_Template::BlockEnd();
}

if($myBase->hasMenu())
	TT_Template::BlockBegin("fichesaccueil","menu","","mod_header");
$myBase->showMenu();
if($myBase->hasMenu())
	TT_Template::BlockEnd();

TT_Template::BlockBegin("fichesaccueil","content","","mod_content");
$myBase->showMainContent();
TT_Template::BlockEnd();

$myBase->showFooter();
echo "</div>\n";
TT_Template::BoxInit("fichesaccueil");
TT_Template::HTMLBodyEnd();
?>