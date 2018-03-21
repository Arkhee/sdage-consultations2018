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

echo "<div  class=\"mainlayout\">\n";
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
$myBase->showMainContent();
TT_Template::BlockEnd();
echo "</div endid='mainContent' >\n";
$myBase->showFooter();
echo "</div>\n";
TT_Template::BoxInit("curbase");
TT_Template::HTMLBodyEnd();

?>