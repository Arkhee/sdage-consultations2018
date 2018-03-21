<?php
/*
 * diren-pcb
 * Created on 8 janv. 2009
 * Copyright  �  2009 Yannick B�temps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick B�temps
 * 
 * File : generate_includefile.php
 * Description : 
 * G�n�re le fichier d'include qui sera appel� pour g�n�rer toutes les classes
 * Ce fichier fait appel aux fichiers json du dossier d'import
 */
require_once("../local.lang/fr.inc.php");
require_once("utils.class.php");
require_once("../classes/tools.class.php");
if(!isset($_GET["pass"]) || $_GET["pass"]!="include")
	die("Pas les droits");

$json_path="importdata/";
$myClassList=ImportUtils::getClassList($json_path);
if($myClassList!==false)
{
	$myTxt="<?php
/*
 * diren-pcb
 * Created on 8 janv. 2009
 * Copyright  @  2007 Yannick B�temps yannick@alternetic.com
 * Author : Yannick B�temps
 * 
 * File : local.config.inc.php
 * Description : 
 * 
 */
";
	foreach($myClassList as $curclass)
	{
		$myTxt.="\n"."@include_once(\$path_pre.\"local.classes/mdtb_".$curclass.".class.php\");";
	}
	$myTxt.="\n?>";
	if(file_put_contents("../local.include.inc.php",$myTxt))
		echo "Fichier d'include g�n�r� correctement<br>\n";
	else
		echo "Erreur � la g�n�ration du fichier<br>\n";
}


?>
<a href="index.php">Retour</a>