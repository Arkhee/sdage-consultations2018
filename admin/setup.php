<?php
/*
 * diren-pcb
 * Created on 3 avr. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : setup.php
 * Description : 
 * Fichier d'installation pour MDTB
 */
$localconfig_filename=$path_pre."local.config.inc.php";

$localconfig_template=
"<?php
\$db_user = \"[db_user]\";
\$db_pass = \"[db_pass]\";
\$db_name = '[db_name]';
\$db_host=\"[db_host]\";
\$db_prefix=\"[db_prefix]\";
\$classprefix=\"[classprefix]\";
\$ThePrefs->UseAuth=\"[useauth]\";
\$ThePrefs->WebmasterAddress=\"[WebmasterAddress]\";
if(file_exists(\"local.config.menu.inc.php\"))
	include_once(\"local.config.menu.inc.php\");
else
{
	\$TheArrayModules=array(
[ARRAYMODULES]
		);
}

?>";
$status="";
$error_msg="";
$display_msg="";
$action=(isset($_POST["action"])?$_POST["action"]:"");
$useauth=(isset($_POST["useauth"])?$_POST["useauth"]:"1");

switch($action)
{
	case "save":
		$mySave=false;
		if($status=="nofile" || !file_exists($localconfig_filename) || is_writable($localconfig_filename))
		{
			if($_POST["db_user"]!="" && $_POST["db_name"]!="" && $_POST["db_host"]!="")
			{
				$localconfig=$localconfig_template;
				foreach($_POST as $key => $value)
					$localconfig=str_replace("[".$key."]",$value,$localconfig);
			}
			$localconfig_arraymodules="";
			if(isset($_POST["arrayModules"]) && $_POST["arrayModules"]!="")
			{
				$myArray=unserialize(urldecode($_POST["arrayModules"]));
				if(is_array($myArray) && count($myArray)>0)
				{
					foreach($myArray as $key=>$val)
					{
						$localconfig_arraymodules.="\t\t\"".$key."\"=>\"".$val."\",\n";
					}
				}
			}
			$localconfig=str_replace("[ARRAYMODULES]",$localconfig_arraymodules,$localconfig);
			$mySave=file_put_contents($localconfig_filename,$localconfig);
		}
		else
			$error_msg="Fichier non réinscriptible<br />";
		if($mySave==false)
			$error_msg.="Fichier non sauvé !<br />\n";
		else
			$display_msg.="Fichier sauvé<br />\n";
		break;
}

if(!file_exists($localconfig_filename))
	$status="nofile";
if($status!="nofile")
{
	require_once($localconfig_filename);
	if(!isset($db_name) || !isset($db_user) || $db_name=="" || $db_user=="")
		$status="badparam";
}
if(isset($ThePrefs->UseAuth))
	$useauth=$ThePrefs->UseAuth;
?>
<html>
	<head><title>Setup MDTB</title></head>
	<body>
		<?php if($display_msg!="") { ?>
				<div style="border:1px solid #00EA00;padding:10px; width:400px; position:relative; left:50%; margin-left:-200px;"><?php echo $display_msg; ?></div>
		<?php } ?>
		<?php if($error_msg!="") { ?>
				<div style="border:1px solid #EA0000;padding:10px; width:400px; position:relative; left:50%; margin-left:-200px;"><?php echo $error_msg; ?></div>
		<?php } ?>
		<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
			<table width="400px" align="center">
				<caption>
					<h3>Veuillez saisir les paramètres de votre base de données</h3>
				</caption>
				<tr><td>Nom d'utilisateur</td><td><input type="text" name="db_user" value="<?php echo $db_user; ?>"/></td></tr>
				<tr><td>Mot de passe</td><td><input type="text" name="db_pass" value="<?php echo $db_pass; ?>"/></td></tr>
				<tr><td>Nom de la base</td><td><input type="text" name="db_name" value="<?php echo $db_name; ?>"/></td></tr>
				<tr><td>Serveur</td><td><input type="text" name="db_host" value="<?php echo $db_host; ?>"/></td></tr>
				<tr><td>Préfixe pour les tables</td><td><input type="text" name="db_prefix" value="<?php echo $db_prefix; ?>"/></td></tr>
				<tr><td>Préfixe de classe</td><td><input type="text" name="classprefix" value="<?php echo $classprefix; ?>"/></td></tr>
				<tr><td>Utiliser l'authentification</td><td><select name="useauth" ><option value="1" <?php echo ($ThePrefs->UseAuth=="1"?"selected":""); ?>>Oui</option><option value="0"<?php echo ($ThePrefs->UseAuth=="0"?"selected":""); ?>>Non</option></td></tr>
				<tr><td>E-Mail du responsable du site</td><td><inputy type="text" value="<?php echo $ThePrefs->WebmasterAddress; ?>"  name="WebmasterAddress"></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" name="cmdOk" value="Sauver" /></td></tr>
				<input type="hidden" name="arrayModules" value="<?php echo urlencode(serialize($TheArrayModules)); ?>" />
				<input type="hidden" name="action" value="save" />
			</table>
		</form>
	</body>
</html>