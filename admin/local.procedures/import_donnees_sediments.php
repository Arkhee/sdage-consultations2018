<?php
/*
 * diren-pcb
 * Created on 15 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : import_donnees_poisson.php
 * Description : 
 * Ce script réalise l'import des données issues du fichier donnees-poisson.csv
 * Les données sont prises ligne par ligne et récupérée en base à l'aide de la classe parametre.class.php
 * L'import se base sur les tables : a_import_description, a_import_colonne qui décrivent la structure de l'import
 */
$path_pre="../";
if(isset($argc) && isset($argv) && is_array($argv) && count($argv)>1)
{
	if(!defined("BR")) 	define("BR","\n");
}
else
{
	if(!defined("BR")) 	define("BR","<br />\n");
}
require_once($path_pre."config.inc.php");
require_once("parametre.class.php");
$myListeCodesSandreAutorises=array('2569','2571','2572','2573','2575','2566','2588','2589','2591','2592','2594','2593','2596','2597','5249','1091','5432','1089','1090','1627','5433','1243','5434','2032','5435','5436','5437','1239','1241','1242','1243','1244','1245','1246','1240','1624','1625','1626','1628','1884','1885','1886','2031','2048','2943','3024','3025','5803','1369','1388','1389','1392','1387','1386','1382','1383','2920','2919','2916','2915','2912','2911','2910','5997','1815','6025','5976','5975','5980','5978','5977','1199','1652');
$myClsParam=new clsParametre($database);
$myXLSFileName="";
$myNormalize=false;

if(isset($_POST["radUpload"]))
{
	switch($_POST["radUpload"])
	{
		case "upload":
			if(isset($_FILES["userfile"]["tmp_name"]))
				$myXLSFileName=$_FILES["userfile"]["tmp_name"];
			if(isset($_POST["chkNormalizeCSV"]) && $_POST["chkNormalizeCSV"]=="1")
				$myNormalize=true;
			break;
		case "default":
		case "dir":
			if(isset($_POST["userfilepath"]))
				$myXLSFileName=str_replace("\\","/",$_POST["userfilepath"]);
			if(isset($_POST["chkNormalizeCSVPath"]) && $_POST["chkNormalizeCSVPath"]=="1")
				$myNormalize=true;
			break;
	}
	
}
else
{
	if(isset($argc) && isset($argv) && is_array($argv) && count($argv)>1)
	{
		@ob_start();
		$myXLSFileName=$argv[1];
		if(!file_exists($myXLSFileName) || !is_readable($myXLSFileName))
		{
			$myXLSFileName="";
			$error_msg="Fichier inexistant ou non lisible".BR;
		}
	}
}
//echo Tools::Display($argc);
//echo Tools::Display($argv);
//die($myXLSFileName);
$error_msg="";
$display_msg="";
?>
<html><head><title>Import des données du fichier de données poissons</title></head>
<body>
	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>
	
	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
	
<?php
if(file_exists($myXLSFileName) && is_readable($myXLSFileName) )
{
	if($myNormalize)
	{
		echo "Normalisation du fichier ...";
		$myRetour=Tools::NormalizeCSV($myXLSFileName);
		echo ($myRetour?"OK":"ERREUR")."<br/>\n";
	}

	echo "Ouverture du fichier ".$myXLSFileName.BR;
	if(is_readable($myXLSFileName))
	{
		echo "Fichier lisible ...<br>\n";
		$xls=new clsDataFile($myXLSFileName,"csv",true);
/*
		$xls = new Spreadsheet_Excel_Reader($myXLSFileName);
*/
		echo "Fichier ouvert ...<br>\n";
	}
	else
		echo "Fichier non lisible<br>\n";
}
else
	echo "Fichier non existant<br>\n";
$row=1; 
$col=1;
/*
$database->setQuery("TRUNCATE table rel_import_colonne_import_description;");
$database->query();
$database->setQuery("TRUNCATE table a_import_description;");
$database->query();
$database->setQuery("TRUNCATE table a_import_colonne;");
$database->query();
//*/
if(isset($xls) && $xls->initok)
{
	Tools::TimeStartCounter();
	
	//die("Ligne de données : ".Tools::Display($xls->data[123]));
	$myCodeImport="IMP_DONNEES_SEDIMENTS";
	//echo "Nettoyage des tables des imports précédents".BR;
	//$cleaned=$myClsParam->cleanBatch($myCodeImport,"import",true);
	//die();
	echo "Initialisation du nouveau batch".BR;
	$myClsParam->initBatch($myCodeImport,"import",basename($myXLSFileName));
	echo "Ouverture du type d'import : ".$myCodeImport.BR;
	$myObj=null;
	$database->setQuery("SELECT * FROM a_import_description WHERE code_import_description='".$myCodeImport."';");
	if($database->loadObject($myObj))
	{
		echo "Le type a été ouvert. Objet : ".Tools::Display($myObj).BR;
		
	/*
		$myObj->id_a_import_description=null;
		$myObj->firstline_import_description=3;
		$myObj->headerline_import_description=3;
		$myObj->firstcolumn_import_description=1;
		$myObj->code_import_description=$myCodeImport;
		$myObj->libelle_import_description="Import Données Poissons";
		$myObj->auteur_import_description="YBS";
		$myObj->date_creation_import_description=date("Y-m-d");
		$myObj->date_modification_import_description=date("Y-m-d");
		$myObj->description_import_description="Import conçu pour récupération des données poissons du fichier DIREN";
		$database->insertObject("a_import_description",$myObj,"id_a_import_description");
	*/
		$myImportId=$myObj->id_a_import_description;
		if(is_null($myImportId) || intval($myImportId)<=0)
			die("Erreur : aucun ID d'import disponible");
		$myRequeteColonnes="SELECT * FROM a_import_colonne,rel_import_colonne_import_description " .
							"WHERE a_import_description_id_a_import_description=".$myImportId." " .
							"AND a_import_colonne_id_a_import_colonne=id_a_import_colonne AND import_inclut_colonne=1;";
		$database->setQuery($myRequeteColonnes);
		$myListCols=$database->loadObjectList();
		$myPrelIndex=1;
		//echo "Colonnes définies dans cet import (".count($myListCols)."):".Tools::Display($myListCols)."<br>\n" ;
		if(is_array($myListCols) && count($myListCols)>0)
		{
			echo "Démarrage de l'import effectif<br>\n";
			$myClsParametre=new clsParametre($database);
			
			$row=$myObj->headerline_import_description;
			echo "Déplacement à la ligne ".$row." du fichier par l'accès séquentiel".BR;
			$realrow=$xls->gotoRow($row);
			if($realrow!==intval($row))
				die(__LINE__." ==> Erreur de placement dans le fichier (réel : ".$realrow.", demandé : ".$row.")");
			$myArrayCol2Param=array();
			echo "Scan de la ligne d'entête pour replacer les colonnes<br>\n";
			echo "Ligne : ".implode(",",$xls->data[0]).BR;
			echo "Import de la colonne ".$myObj->firstcolumn_import_description." à la colonne ".$xls->colcount().BR;
			for($col=$myObj->firstcolumn_import_description;$col<$xls->colcount();$col++)
			{
				$myClsNb=$myClsParametre->rechercheParametre($xls->val($row,$col),$myImportId);
				echo "Vérification colonne ".$xls->val($row,$col)." ==> ".implode(",",$myClsNb).BR;
				foreach($myListCols as $key=>$val)
					if(in_array($val->cd_import_colonne,$myClsNb))
						$myArrayCol2Param[$col][]=$val;
				
			}
			
			/*
			 * Détection de colonnes déterminant l'import ou non
			 */
			$eauSupSoutColLabel="typsta";
			$codeSandreColLabel="codsandre";
			$eauSupSoutColNb=-1;
			$codeSandreColNb=-1;
			echo "Recherche des entêtes : colonne contenant les valeurs significatives pour un improt (typsta)".BR;
			for($col=$myObj->firstcolumn_import_description;$col<$xls->colcount();$col++)
			{
				if(strtolower($xls->val($row,$col))==$eauSupSoutColLabel)
					$eauSupSoutColNb=$col;
				if(strtolower($xls->val($row,$col))==$codeSandreColLabel)
					$codeSandreColNb=$col;
			}
			if($eauSupSoutColNb>0)
				echo "Entête significatif trouvé en colonne ".$eauSupSoutColNb.BR;
			//echo "ArrayCol2Param : ".Tools::Display($myArrayCol2Param);
			$myClsParam->setArrayToColAssociation($myArrayCol2Param);
			$myClsParam->checkValues();
			echo "Nombre de champs sans table :" .count($myClsParam->fields_without_table).BR;
			echo "Nombre de tables : ".count($myClsParam->tables_to_fields).BR;
			if(count($myClsParam->tables_to_fields)>0)
			{
				foreach($myClsParam->tables_to_fields as $curtable => $curfield)
				{
					echo "Table : ".$curtable.BR;
				}
				/*
				 * Nettoyage avant début du travail !!
				 */
				
				//die("Résultat nettoyage : ".Tools::Display($cleaned));
				//echo "Params : ".Tools::Display($myArrayCol2Param);
				//$myNBLignesDeDonees=intval($xls->rowcount()-$myObj->firstline_import_description+1);
				//echo "Début de la sauvegarde des paramètres, au total ".$myNBLignesDeDonees." lignes<br>\n";
				$myNBLignesDeDonees=-1;
				echo "Début de la sauvegarde des paramètres, nombre total de lignes inconnu (accès séquentiel)".BR;
				echo "Ligne de contenus : ".$myObj->firstline_import_description.BR;
				$realrow=$xls->gotoRow($myObj->firstline_import_description-1);
				echo "Démarrage à la ligne : ".$realrow.BR;
				//for ($row=$myObj->firstline_import_description;$row<$xls->rowcount();$row++)
				//echo "Colonnes : ".Tools::Display($myArrayCol2Param);
				$indexadded=1;
				while($row=$xls->getNextRow())
				{
					//echo "Nb colonnes : ".$xls->colcount().BR;
					/*
					 * Vérification des données: eaux sup ou sout
					 */
					//echo "Données : ".implode(";",$xls->data[$row]).BR;
					$myNumLigneDonnees=intval(1+$row-$myObj->firstline_import_description);
					
					$myCodeSandreCurRec=$xls->val($row,$codeSandreColNb);
					$myArrayListeParamsPCB=array('1089','1090','1091','1239','1240','1241','1242','1243','1244','1245','1246','1624','1625','1626','1627','1628','1885','1886','2032','5432','5433','5434','5435','5436','5437');
		    		$myTxtPCBComplement=" SANDRE PARAM : ".$myCodeSandreCurRec."";
		    		if(in_array(strval($myCodeSandreCurRec),$myArrayListeParamsPCB))
									$myTxtPCBComplement.=" / PCB ";
					$boolStockeLigne=in_array($myCodeSandreCurRec,$myListeCodesSandreAutorises);
					echo "Ligne de données N°".$myNumLigneDonnees."/ nb ajoutés : ".$indexadded++.", ligne fichier ".($row+1)."/".$myNBLignesDeDonees." critère : ".$xls->val($row,$eauSupSoutColNb).$myTxtPCBComplement." ligne incluse : ".(($boolStockeLigne)?"oui":"non").BR;
					if($eauSupSoutColNb>0 && strtolower($xls->val($row,$eauSupSoutColNb))=="sou")
						continue;
					if(!$boolStockeLigne)
						continue;
					//$myClsParam->cleanLine();
					for($col=$myObj->firstcolumn_import_description;$col<=$xls->colcount();$col++)
					{
						if(isset($myArrayCol2Param[$col]))
							$myClsParam->setParam($myArrayCol2Param[$col],$xls->val($row,$col));
					}
					//die("Ligne ".Tools::Display($myClsParam->curobject));
					//die();
					//echo "Objet : ".Tools::Display($myClsParam->curobject);
					//break;
					$previousItems=$myClsParam->saveLineSediments();
					//die(Tools::Display($myClsParam->curobject));
					//echo "Création d'un prélèvement : ".$myPrelIndex.BR;
					//break;
					//if($myNumLigneDonnees>12000) break;
					//if(intval($row)>=3000) break;
					file_put_contents($myXLSFileName.".log",@ob_get_contents(),FILE_APPEND);
					@ob_clean();
				}
				foreach($previousItems as $key=>$val)
					echo "Nb d'enregistrements de ".$key." : ".count($val).BR;
			}
			else
				echo "Aucune table : vérifiez le fichier d'import<br />\n";
		}
		else
			echo "Aucune colonne dans l'import.".Tools::Display($myRequeteColonnes).BR;
	}
	else
		echo "Type d'import introuvable<br>\n";
	echo "<hr />\n";
	echo "Temps total de traitement de l'import : ".Tools::TimeStopCounter().BR;
	
}

if(isset($argc) && isset($argv) && is_array($argv) && count($argv)>1)
{
	file_put_contents($myXLSFileName.".log",@ob_get_contents(),FILE_APPEND);
	//@ob_end_flush();
}
else
{
?>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<fieldset><legend><label><input type="radio" name="radUpload" value="upload" />&nbsp;Télécharger un fichier</label></legend>
		<label>Sélectionner le fichier CSV : <input type="file" name="userfile"  /></label><br />
		<label><input type="checkbox" name="chkNormalizeCSV" value="1" checked />Normaliser le fichier CSV : ôter mes retours chariot des contenus de cellules (\n seuls transformés en espaces)</label><br />
		</fieldset>
		<fieldset><legend><label><input type="radio" name="radUpload" value="dir" checked />&nbsp;Indiquer le chemin du fichier sur le disque du serveur</label></legend>
		<label><input type="text" name="userfilepath" style='width:300px;' /></label><br />
		<label><input type="checkbox" name="chkNormalizeCSVPath" value="1" />Normaliser le fichier CSV : ôter mes retours chariot des contenus de cellules (\n seuls transformés en espaces)</label><br />
		</fieldset>
		<input type="submit" name="cmdOk" value="Ok" />
	</form>

	<a href="index.php">Retour</a>
<?php
}
?>