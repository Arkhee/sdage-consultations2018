<?php
/*
 * diren-pcb
 * Created on 16 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : parsesql.php
 * Description : 
 * Utilise la classe sqlparse.lib.php de phpMyAdmin pour parser des fichiers SQL de création
 * de base de données, et sauve le résultat de ces analyses en objet Json dans un chemin adapté
 */

/**
 * Gets core libraries and defines some variables
 */

	function handle_sql_file($theTmpName,$theCreateFile,$theInsertFile)
	{
		if($theTmpName=="")
			die("Erreur aucun fichier fourni !");
		if(!file_exists($theTmpName))
			die("Erreur le fichier n'existe pas");
		$myDlFile=file_get_contents($theTmpName);
		$myDlFile=utf8_decode($myDlFile);
		file_put_contents($theTmpName,$myDlFile);
		unset($myDlFile);
		unlink($theCreateFile);
		unlink($theInsertFile);
		if(file_exists($theInsertFile) || file_exists($theCreateFile))
			die("Impossible d'effacer les fichiers de sortie (".$theInsertFile." ou (".$theCreateFile."))'");
		//$myArrayDLFile=explode("\r\n",$myDlFile);
		$fh_create=fopen($theCreateFile,"wt");
		$fh_insert=fopen($theInsertFile,"wt");
		$fh=fopen($theTmpName,"rt");
		$linenb=1;
		while(!feof($fh))
		{
			//echo "Traitement ligne ".$linenb++." ... ";
			$myCurLine=fgets($fh);
			preg_match_all("|`(.*)`|U", $myCurLine, $out, PREG_SET_ORDER);
			foreach($out as $curoccurence)
			{
				$newoccurence=replace_bad_chars($curoccurence[0]);
				$myCurLine=str_replace($curoccurence[0],$newoccurence,$myCurLine);
			}
			if(substr(trim($myCurLine),0,1)!="#")
			{
				if(substr(trim($myCurLine),0,6)!="INSERT")
				{
					fwrite($fh_create,$myCurLine);
					//echo "écriture INSERT".BR;										
				}
				else
				{
					fwrite($fh_insert,$myCurLine);
					//echo "écriture CREATE".BR;										
				}
			}
		}
		//echo "Fin du traitement".BR;
		fclose($fh_insert);
		fclose($fh_create);
		fclose($fh);
	}

	function replace_bad_chars($nompage)
	{
		$nompage = strtolower($nompage);
		$nompage = str_replace(" ","__",$nompage);
		$nompage = strtr($nompage,"àáâãäåæîïíìòóôõöøðúùûüéèêëýÿçþß/'\"-","aaaaaaaiiiiooooooduuuueeeeyycts____");
	 	$nompage = eregi_replace("/[^a-z0-9_:~\\\/\-`]/i","_",$nompage);
		return $nompage;
	}
		
	if(!defined("BR")) define("BR","<br/>\n");

	//die("Occurences trouvées :<pre>".print_r($mySQLArray,true)."</pre>\n");
	//$mySessObject=serialize($_SESSION);
	//file_put_contents("session.inc",$mySessObject);
	//if($debug) echo __LINE__. " => Session : <pre>".print_r($_SESSION,true)."</pre>";
	$debug=false;
	$action_on_table="sendfile";
	$json_path="../importdata/";
	if(isset($_POST["action_on_table"]))
		$action_on_table=$_POST["action_on_table"];
	switch($action_on_table)
	{
		case "sendtable":
			$mySQLFileName=$json_path.$_POST["sendtable_filename"];
			break;
		case "sendfile":
		default:
			$mySQLFileName=$_FILES["userfile"]["tmp_name"];
			break;
	}
	if($debug) echo __LINE__. " => Fichiers : ".$mySQLFileName."<br >\n";
	//$mySQLFileNameDefault="C:\\Users\\yb\\Documents\\Travail\\Entreprise\\Clients\\DIREN\\projets\\pcb\\analyse\\modele-relationnel\\modele-pcb-acquisition-donnees-bio_2.sql";
	if(file_exists($mySQLFileName))
	{
		require_once './libraries/common.inc.php';
		require_once './libraries/relation.lib.php';
		require_once("libraries/sqlparser.lib.php");
		/*
		 * Initialisation des variables après nettoyage de session par phpMyAdmin
		 */
		$debug=false;
		$path_pre="../../";
		require($path_pre."local.config.inc.php");
		require_once($path_pre."config.inc.php");
		$action_on_table="sendfile";
		if(isset($_POST["action_on_table"]))
			$action_on_table=$_POST["action_on_table"];
			
		$json_path="../importdata/";
		$fileCreate=$json_path."create.sql";
		$fileInsert=$json_path."insert.sql";
		$updateData=0;
		$updateStructure=0;
		switch($action_on_table)
		{
			case "sendtable":
				$mySQLFileName=$json_path.$_POST["sendtable_filename"];
				break;
			case "sendfile":
			default:
				$mySQLFileName=$_FILES["userfile"]["tmp_name"];
				if(isset($_POST["chkUpdateData"])) $updateData=$_POST["chkUpdateData"];
				if(isset($_POST["chkUpdateStructure"])) $updateStructure=$_POST["chkUpdateStructure"];
				if($debug) echo __LINE__. " => Début traitement du fichier sql<pre>".print_r($_FILES,true)."</pre>".BR;
				if($debug) echo __LINE__. " => Fichier transmis : <pre>".print_r($_FILES["userfile"],true)."</pre>\n";
				//if($debug) die("fin, objet database : ".Tools::Display($database));
				if($debug) echo __LINE__. " => Traitement fichier ".$mySQLFileName.BR;
				handle_sql_file($mySQLFileName,$fileCreate,$fileInsert);
				$mySQLFileName=$fileCreate;
				break;
		}
		
		/*
		 * Début  des traitements
		 */
		
		if($debug) echo __LINE__. " => Traitement fichier fait".BR;
		if($debug) echo __LINE__. " => Lecture du fichier SQL : ".$mySQLFileName."<br>\n";
		$mySQLFile=file_get_contents($mySQLFileName);
		$mySQLFile=str_replace("\n"," ",str_replace("\r"," ",$mySQLFile));
		$myTablesList=explode("CREATE  TABLE IF NOT EXISTS",$mySQLFile);
		if($debug) echo __LINE__. " => Tableau d'arrays : <pre>".print_r($myTablesList,true)."</pre>\n";
		$mySQLArray=array();
		foreach($myTablesList as $cursql)
		{
			$cursql="CREATE  TABLE IF NOT EXISTS".$cursql;
			/*
			 * MyISAM sans commentaires
			 */
			preg_match_all("|CREATE  TABLE IF NOT EXISTS(.*)ENGINE = MyISAM;|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			
			/*
			 * MyIsam avec commentaires
			 */
			preg_match_all("|CREATE  TABLE IF NOT EXISTS(.*)ENGINE = MyISAM COMMENT = '(.*)';|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			
			
			/*
			 * MyIsam sans commentaires ni if not exists
			 */
			preg_match_all("|CREATE TABLE(.*)ENGINE=myisam DEFAULT CHARSET=utf8;|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			
			/*
			 * MyIsam sans commentaires ni if not exists
			 */
			preg_match_all("|CREATE TABLE(.*)ENGINE=MyISAM(.*)DEFAULT CHARSET=latin1;|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			
			
			/*
			 * InnoDB sans commentaires
			 */
			preg_match_all("|CREATE  TABLE IF NOT EXISTS(.*)ENGINE = InnoDB|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			
			
			/*
			 * InnoDB avec commentaires
			 */
			preg_match_all("|CREATE  TABLE IF NOT EXISTS(.*)ENGINE = InnoDB COMMENT = '(.*)'|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			
			
			/*
			 * Neutre sans commentaires
			 */
			preg_match_all("|CREATE  TABLE IF NOT EXISTS(.*)\);|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			/*
			 * Neutre avec commentaires
			 */
			preg_match_all("|CREATE  TABLE IF NOT EXISTS(.*)\)COMMENT = '(.*)';|U",
			    $cursql,
			    $out, PREG_SET_ORDER);
			if(is_array($out) && count($out)>0) $mySQLArray=array_merge($out,$mySQLArray);
			if($debug) echo __LINE__. " => Requêtes trouvées :  <pre>".print_r($mySQLArray,true)."</pre>\n";
			unset($out);
		}
		if($debug) echo __LINE__. " => contenu <br>\n<pre>".print_r($mySQLArray,true)."<br>\n";
	}
	header("Content-Type: text/html; charset=iso-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Analyse des requêtes SQL de création de table</title>
</head>
<body>
<?php
	if(isset($_POST["cmdOk"]) && !file_exists($mySQLFileName))
		echo "Fichier non défini : ".$mySQLFileName."<br>\n";
	if(isset($mySQLArray) && is_array($mySQLArray) && count($mySQLArray)>0)
	{
		echo "<script type='text/javascript'>function toggle(divid){ document.getElementById(divid).style.display=(document.getElementById(divid).style.display=='none')?'block':'none';}</script>";
		$i=1;
		foreach($mySQLArray as $curarray)
		{
			//echo "Décomposition de ".Tools::Display($curarray);
			$parsed_sql = PMA_SQP_parse($curarray[0]);
			//die("Affichage requête décodée : <pre>".print_r($parsed_sql,true)."</pre>\n");
			$sql_info = PMA_SQP_analyze($parsed_sql);
			foreach($parsed_sql as $parsedindex=>$parsed)
			{
				if($parsed["type"]=="quote_backtick" && $parsed_sql[$parsedindex+1]["type"]=="punct_bracket_open_round")
				{
					$sql_info[0]["tablename"]=str_replace("`","",$parsed["data"]); //str_replace("`","",$parsed_sql[5]["data"]);
					break;
				}
			}
			
			if(trim($sql_info[0]["tablename"])=="")
				continue;
				
			preg_match_all("#PRIMARY KEY \(`(.*)`\)#",
			    str_replace("\n","",str_replace("\r","",$sql_info[0]["unsorted_query"])),
			    $out, PREG_SET_ORDER);
			if(strstr($out[0][1],"`")!==false && substr($out[0][1],0,1)!="`")
			{
				$myTmpTable=explode("`",$out[0][1]);
				$out[0][1]=$myTmpTable[0];
			}
			$sql_info[0]["tablekey"]=$out[0][1];
			/*
			 *  Analyse des champs pour trouver des commentaires, permettant de déterminer la position des champs (entre autres)
			 */ 
			//die("Objet : <pre>".print_r($sql_info[0],true)."</pre>");
			//foreach($sql_info[0]["create_table_fields"] as $fld_key=>$fld_value)
			
			foreach($parsed_sql as $elementindex=>$curelement)
			{
				if($curelement["type"]=="alpha_reservedWord" && $curelement["data"]=="COMMENT" && isset($parsed_sql[$elementindex+1]) && $parsed_sql[$elementindex+1]["type"]=="quote_single")
				{
					for($j=$elementindex;$j>0;$j--)
					{
						if($parsed_sql[$j]["type"]=="quote_backtick")
						{
							$myFieldName=str_replace("`","",$parsed_sql[$j]["data"]);
							if(isset($sql_info[0]["create_table_fields"][$myFieldName]))
							{
								//$myOptions=explode(",",substr($parsed_sql[$elementindex+1]["data"],1,strlen($parsed_sql[$elementindex+1]["data"])-2));
								$optionsraw=substr($parsed_sql[$elementindex+1]["data"],1,strlen($parsed_sql[$elementindex+1]["data"])-2);
								$optionsraw=str_replace("\\n","\n",$optionsraw);
								$myOptions=split("[\n,]",$optionsraw);
								foreach($myOptions as $curoption)
								{
									$myCurOptionArray=explode("=",$curoption);
									if(is_array($myCurOptionArray) && count($myCurOptionArray)==2) 
										$sql_info[0]["create_table_fields"][$myFieldName]["options"][$myCurOptionArray[0]]=$myCurOptionArray[1];
								}
							}
							break;
						}
					}
				}
			}
			if(isset($curarray[2]))
			{
				$sql_info[0]["tablecomments"]=$curarray[2];
				$myOptions=split("[\\n,]",$sql_info[0]["tablecomments"]);
				//$sql_info[0]["tableoptions"]["options"]=$myOptions;
				foreach($myOptions as $curoption)
				{
					$myCurOptionArray=explode("=",$curoption);
					if(is_array($myCurOptionArray) && count($myCurOptionArray)==2) 
						$sql_info[0]["tableoptions"][$myCurOptionArray[0]]=$myCurOptionArray[1];
				}

			}
			//echo "Objet JSon :<pre>".json_encode($sql_info)."</pre>\n";
			echo "<a href=\"javascript:toggle('div".$i."');\">Table ".$i." : ".$sql_info[0]["tablename"].", clef : ".$sql_info[0]["tablekey"]." => ".$json_path.$sql_info[0]["tablename"].".json (".(($boolAffichageSeul)?"aff":"paff").")</a><br />\n";
			echo "<div id='div".$i."' style='display:none;'>\n";
			//echo "Affichage requête décodée : <pre>".print_r($parsed_sql,true)."</pre>\n";
			echo "CurArray : <pre>".print_r($curarray,true)."</pre>";
			echo "Affichage infos de requête : <pre>".print_r($sql_info[0],true)."</pre>\n";
			//echo "Objet après decode :<pre>".print_r(json_decode(json_encode($sql_info)),true)."</pre>\n";
			file_put_contents($json_path.$sql_info[0]["tablename"].".json",json_encode($sql_info[0]));
			echo "</div>\n";
			
			/*
			 * Traitement des requêtes si demande de mise à jour de la structure en base 
			 */
			//echo "Exécution sur élément : ".Tools::Display($sql_info[0]);
			//die("Exécution terminée");
			//echo "Mise à jour de la structure : ".$updateStructure.BR;
			//echo "Nom de la table : ".$sql_info[0]["tablename"].BR;
			//echo "Type de requête : ".$sql_info[0]["querytype"].BR;
			if($updateStructure=="1" && $sql_info[0]["tablename"]!="" 
				&& isset($sql_info[0]["querytype"]) && $sql_info[0]["querytype"]=="CREATE" 
				&& isset($sql_info[0]["unsorted_query"]) && $sql_info[0]["unsorted_query"]!="")
			{
				//echo "Exécution sur élément : ".Tools::Display($sql_info[0]);
				//echo "Effacement de la table précédente ".$sql_info[0]["tablename"]." en base".BR;
				$database->setQuery("DROP TABLE ".$sql_info[0]["tablename"]);
				$database->query();
				//echo "Insertion de la table ".$sql_info[0]["tablename"]." en base".BR;
				$database->setQuery($sql_info[0]["unsorted_query"]);
				$database->query();
			}
			if($updateData=="1" && $sql_info[0]["tablename"]!="")
			{
				$database->setQuery("TRUNCATE TABLE ".$sql_info[0]["tablename"]);
				$database->query();
			}
			echo "La table a été réinitialisée".BR;
			$i++;
		}
		if($updateData=="1")
		{
			echo "<a href=\"javascript:toggle('divData');\">Insertion des nouvelles données en base</a>".BR;
			echo "<div id='divData' style='display:none;'>\n";
			if(file_exists($fileInsert))
			{
				$fh=fopen($fileInsert,"rt");
				$i=1;
				while(!feof($fh))
				{
					$myLine=fgets($fh);
					$myLine=trim($myLine);
					if(substr($myLine,0,6)=="INSERT")
					{
						$database->setQuery($myLine);
						$myReturn=$database->query();
						//echo "Ligne ".$i++." => ".(($myReturn)?"OK":("ERREUR : ".$database->getErrorMsg())).BR;
					}
				}
			}
			echo "</div>\n";
		}
		echo "<br /><hr /><br />\n";
	}
?>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label>Sélectionner le fichier SQL : <input type="file" name="userfile"  /></label><br />
		<label><input type="checkbox" name="chkUpdateStructure" value="1" />La structure des tables a changé, il faut la réimporter. ATTENTION cela effacera toutes les données stockées en base.</label><br />
		<label><input type="checkbox" name="chkUpdateData" value="1" checked />Insérer les données contenu dans le fichier fourni, en lieu et place des données actuelles</label><br />
		<input type="submit" name="cmdOk" value="Ok" />
		<input type="hidden" name="action_on_table" value="sendfile" />
	</form>
	<a href="index.php">Retour</a>
</body>
</html>