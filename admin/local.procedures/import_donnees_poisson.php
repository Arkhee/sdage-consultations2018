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
require_once($path_pre."config.inc.php");
require_once("parametre.class.php");
$myClsParam=new clsParametre($database);
$myXLSFileName="";
if(isset($_FILES["userfile"]["tmp_name"]))
	$myXLSFileName=$_FILES["userfile"]["tmp_name"];
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
if(file_exists($myXLSFileName) )
{
	if(isset($_POST["chkNormalizeCSV"]) && $_POST["chkNormalizeCSV"]=="1")
	{
		echo "Normalisation du fichier ...";
		$myRetour=Tools::NormalizeCSV($myXLSFileName);
		echo ($myRetour?"OK":"ERREUR")."<br/>\n";
	}

	echo "Ouverture du fichier ".$myXLSFileName."<br>\n";
	if(is_readable($myXLSFileName))
	{
		echo "Fichier lisible ...<br>\n";
		$xls=new clsDataFile($myXLSFileName,"csv");
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
if($xls->initok)
{
	//die("Ligne de données : ".Tools::Display($xls->data[123]));
	$myCodeImport="IMP_DONNEES_POISSONS";
	echo "Ouverture du type d'import : ".$myCodeImport."<br>\n";
	$myObj=null;
	$database->setQuery("SELECT * FROM a_import_description WHERE code_import_description='".$myCodeImport."';");
	if($database->loadObject($myObj))
	{
		echo "Le type a été ouvert. Objet : ".Tools::Display($myObj)."<br>\n";
		
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
			$myArrayCol2Param=array();
			echo "Scan de la ligne d'entête pour replacer les colonnes<br>\n";
			echo "Ligne : ".implode(",",$xls->data[0])."<br>\n";
			echo "Import de la colonne ".$myObj->firstcolumn_import_description." à la colonne ".$xls->colcount()."<br>\n";
			for($col=$myObj->firstcolumn_import_description;$col<$xls->colcount();$col++)
			{
				$myClsNb=$myClsParametre->rechercheParametre($xls->val($row,$col),$myImportId);
				echo "Vérification colonne ".$xls->val($row,$col)." ==> ".implode(",",$myClsNb)."<br>\n";
				foreach($myListCols as $key=>$val)
					if(in_array($val->cd_import_colonne,$myClsNb))
						$myArrayCol2Param[$col][]=$val;
				
			}
			
			//echo "ArrayCol2Param : ".Tools::Display($myArrayCol2Param);
			$myClsParam->setArrayToColAssociation($myArrayCol2Param);
			$myClsParam->checkValues();
			echo "Nombre de champs sans table :" .count($myClsParam->fields_without_table)."<br />\n";
			echo "Nombre de tables : ".count($myClsParam->tables_to_fields)."<br />\n";
			if(count($myClsParam->tables_to_fields)>0)
			{
				foreach($myClsParam->tables_to_fields as $curtable => $curfield)
				{
					echo "Table : ".$curtable."<br />\n";
				}
				/*
				 * Nettoyage avant début du travail !!
				 */
				
				
				$database->setQuery("TRUNCATE TABLE #__preleveurs;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__zones_de_peche;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__point_de_prelevement;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__operation_prelevement_biologique;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__prelevement_elementaire_biologique;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__rel_lot_poissons_preleves;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__lot_de_poissons_preleves;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__echantillon;");
				$database->query();
				$database->setQuery("TRUNCATE TABLE #__analyse;");
				$database->query();
				
				//echo "Params : ".Tools::Display($myArrayCol2Param);
				$myNBLignesDeDonees=intval($xls->rowcount()-$myObj->firstline_import_description+1);
				echo "Début de la sauvegarde des paramètres, au total ".$myNBLignesDeDonees." lignes<br>\n";
				for ($row=$myObj->firstline_import_description;$row<$xls->rowcount();$row++)
				{
					echo "Ligne N°".intval(1+$row-$myObj->firstline_import_description)."/".$myNBLignesDeDonees."<br />\n";
					$myClsParam->cleanLine();
					for($col=$myObj->firstcolumn_import_description;$col<=$xls->colcount();$col++)
					{
						if(isset($myArrayCol2Param[$col]))
							$myClsParam->setParam($myArrayCol2Param[$col],$xls->val($row,$col));
					}
					//die("Ligne ".Tools::Display($myClsParam->curobject));
					//die();
					//echo "Objet : ".Tools::Display($myClsParam->curobject);
					//break;
					$myPrelIndex=$myClsParam->saveLine();
					//echo "Création d'un prélèvement : ".$myPrelIndex."<br>\n";
					//break;
					//*/
				}	
			}
			else
				echo "Aucune table : vérifiez le fichier d'import<br />\n";
		}
		else
			echo "Aucune colonne dans l'import.".Tools::Display($myRequeteColonnes)."<br>\n";
	}
	else
		echo "Type d'import introuvable<br>\n";
	echo "<hr />\n";
}
?>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label>Sélectionner le fichier CSV : <input type="file" name="userfile"  /></label><br />
		<label><input type="checkbox" name="chkNormalizeCSV" value="1" checked />Normaliser le fichier CSV : ôter mes retours chariot des contenus de cellules (\n seuls transformés en espaces)</label><br />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>


<a href="index.php">Retour</a>