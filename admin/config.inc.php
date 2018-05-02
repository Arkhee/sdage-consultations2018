<?php
/*
 * Created on 16 mai 07
 * Copyright  @  2007 Yannick Bétemps yannick@alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : config.inc.php
 * Description : 
 * 
 */
/*
$db_user = 'mdtb_dev';
$db_pass = 'mdtb_dev';
$db_name = 'mdtb_dev';
$db_host="localhost";
$db_prefix="mdtb_";
*/
// Localised date parameters
if(!isset($TheDatePref)) $TheDatePref=new stdClass();

$TheDatePref->SelFormat="dd/MM/yyyy";
$TheDatePref->JSSelFormat="dd/mm/yy";
$TheDatePref->DispFormat="d/m/Y";
$TheDatePref->ScanFormat="%d/%d/%d";
$TheDatePref->ScanOrder="dmy";
$TheDatePref->FunctionCheck="chkISODate"; // chkISODate
if(!isset($ThePrefs)) $ThePrefs=new stdClass();

$ThePrefs->TmpPdfDir=__DIR__."/../pdf/";
$ThePrefs->style_box="";
$ThePrefs->lang="fr";
$ThePrefs->encoding="UTF-8";
//$ThePrefs->encoding="ISO-8859-1";
$ThePrefs->DatePrefs=$TheDatePref;
$ThePrefs->UseAuth=false;

$ThePrefs->From="webmaster@rhone-mediterranee.eaufrance.fr";
$ThePrefs->FromName="Webmaster SIE";
$ThePrefs->SMTPHost="smtp.zoho.com";
$ThePrefs->SMTPPort=587;
$ThePrefs->SMTPUser="webmaster@rhone-mediterranee.eaufrance.fr";
$ThePrefs->SMTPPassword="errc69c0ba37b";

$ThePrefs->AdminGroupPourAlertesMails=3;
$ThePrefs->BaseFolder=dirname($_SERVER["SCRIPT_FILENAME"]);
$ThePrefs->DocumentsFolder=$ThePrefs->BaseFolder."/documents/";
if(!file_exists($ThePrefs->BaseFolder."/documents/")) mkdir($ThePrefs->BaseFolder."/documents/");
$TheArrayModules=array(
						"index"=>"MDTB",
						
						"#sep#1"=>"<hr />",
						
						"groupes"=>"Groupes",
						"users"=>"Utilisateurs");
$TheMainMenu[]=array( "type"=>"title","label" => ("<h3>Menu Principal</h3>") );
if(!file_exists($path_pre."local.config.inc.php"))
{
	header("location:setup.php");
}
require_once($path_pre."local.config.inc.php");
if(!isset($db_name) || !isset($db_user) || $db_name=="" || $db_user=="")
{
	
	header("location:setup.php");
}
header('Content-type: text/html; charset='.$ThePrefs->encoding);
if(file_exists($path_pre."lang/".addslashes($ThePrefs->lang).".inc.php")) include_once($path_pre."lang/".addslashes($ThePrefs->lang).".inc.php");
if(file_exists($path_pre."local.lang/".addslashes($ThePrefs->lang).".inc.php")) include_once($path_pre."local.lang/".addslashes($ThePrefs->lang).".inc.php");
// Stop editting here !!
@session_start();
$path_abs=dirname(__FILE__);
require_once($path_pre."lib/lib.inc.php");
require_once($path_pre."local.include.inc.php");
if(file_exists($path_pre."local.classes/userdefined.class.php"))
	include_once($path_pre."local.classes/userdefined.class.php");
	
$auth=null;

if($ThePrefs->UseAuth)
{
	$auth=new users($database,$path_pre."local.data/create-users-structure.sql");
	$auth->start();
}

define("LIB_EXCLUDE_SELECT2",true);