<?php
/*
 * diren-pcb
 * Created on 30 mars 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : ajaxsearchlist.php
 * Description : 
 * 
 */
header("Content-Type: text/html; charset=iso-8859-1");
require_once("config.inc.php");

//echo "Paramètres GET<pre>".print_r($_GET,true)."</pre><br>\n";
//echo "Paramètres POST<pre>".print_r($_POST,true)."</pre><br>\n";
if(isset($_GET["class_name"]) && $_GET["class_name"]!="" && class_exists($_GET["class_name"]))
	$cur_class_name=$_GET["class_name"];

/**
* @global mdtb_table $myBase Déclaration de table
*/
$myBase=new $cur_class_name($database,$template_name,basename(__FILE__),$path_abs);
$myBase->_defaultparams->parent_table=$_GET["parent_table"];
$myBase->_defaultparams->parent_item=$_GET["parent_item"];
$myBase->_defaultparams->parent_file=$_GET["parent_file"];
$myBase->set_auth($auth);
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
//echo "PArent : ".$myBase->_params->parent_table."=>".$myBase->_params->parent_item."<br />\n";
echo "<div  class=\"mainlayout\">\n";

TT_Template::BlockBegin("curbase","content","","mod_content");
$myBase->showMainContent("ajaxsearchlist");
TT_Template::BlockEnd();

$myBase->showFooter();
echo "</div>\n";
?>