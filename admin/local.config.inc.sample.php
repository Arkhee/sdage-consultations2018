<?php
$db_user = "diren_mdosout";
$db_pass = "diren_mdosout";
$db_name = 'diren_mdosout';
$db_host="localhost";
$db_prefix="";
$classprefix="mdtb_";
$ThePrefs->UseAuth=true;
$ThePrefs->WebmasterAddress="yannick@alternetic.com";
require_once($path_pre."local.classes/presentation.class.php");
if(file_exists("local.config.menu.inc.php"))
	include_once("local.config.menu.inc.php");
else
{
	$TheArrayModules=array(
	

		);
}

if(file_exists("local.config.staticmenu.inc.php"))
{
	require_once("local.config.staticmenu.inc.php");
	if(isset($TheMenuStatic) && is_array($TheMenuStatic) && count($TheMenuStatic)>0)
		$TheArrayModules=array_merge($TheMenuStatic,$TheArrayModules);
}

?>