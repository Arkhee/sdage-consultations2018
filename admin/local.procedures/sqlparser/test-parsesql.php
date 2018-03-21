<?php
/*
 * diren-pcb
 * Created on 16 janv. 2009
 * Copyright  �  2009 Yannick B�temps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick B�temps
 * 
 * File : parsesql.php
 * Description : 
 * Utilise la classe sqlparse.lib.php de phpMyAdmin pour parser des fichiers SQL de cr�ation
 * de base de donn�es, et sauve le r�sultat de ces analyses en objet Json dans un chemin adapt�
 */


/**
 * Gets core libraries and defines some variables
 */

//die("Occurences trouv�es :<pre>".print_r($mySQLArray,true)."</pre>\n");
//$mySessObject=serialize($_SESSION);
//file_put_contents("session.inc",$mySessObject);
$boolAffichageSeul=false;
$parse_vars=array("requete","cmdOk");
if(isset($_POST["chkOnlyDisplay"]) && $_POST["chkOnlyDisplay"]==1)
	$boolAffichageSeul=true;
require_once './libraries/common.inc.php';
$myRequete="";
if(isset($_POST["requete"]))
	$myRequete=$_POST["requete"];
require_once './libraries/relation.lib.php';
require_once("libraries/sqlparser.lib.php");
header("Content-Type: text/html; charset=iso-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Analyse des requ�tes SQL de cr�ation de table</title>
</head>
<body>
<?php

if($myRequete!="")
{
	echo "Requ�te envoy�s : <pre>".$myRequete."</pre>\n";
	$parsed_sql = PMA_SQP_parse($myRequete);
	//die("Affichage requ�te d�cod�e : <pre>".print_r($parsed_sql,true)."</pre>\n");
	$sql_info = PMA_SQP_analyze($parsed_sql);
	echo "Requ�te pars�e : <pre>".print_r($parsed_sql,true)."</pre>\n";
	echo "Infos : <pre>".print_r($sql_info,true)."</pre>\n";
	echo "<hr />\n";
}
?>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST">
		<label>Requ�te SQL : <br /><textarea style="width:500px; height:250px; " name="requete"></textarea></label><br />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>

</body>
</html>