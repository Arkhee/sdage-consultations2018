<?php
/*
 * diren-pcb
 * Created on 15 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : tpl_generate_importdescription.php
 * Description : 
 * Réalise l'import de la description de l'import en base
 * Cette description établit la correspondance entre les champs du fichier d'import et les champs de la table de destination
 */
 
/*
 * Initialisation du contexte, chargement des fichiers de paramètres
 */
$path_pre="../";

require_once($path_pre."config.inc.php");
require_once("utils.class.php");
require_once("parametre.class.php");
//$myImportParams["txtCodeImport"]="";

Tools::BindToGlobal($_GET);
Tools::BindToGlobal($_POST);
Tools::BindToGlobal($_FILES);
$error_msg="";
$display_msg="";
//require_once ($path_pre."local.lib/excel_reader2.php");
//$xls = new Spreadsheet_Excel_Reader("importdata/analyse-donnees-poisson.xls");
//$xls=new clsDataFile("importdata/repartition-donnees-excel-mcd.csv","csv");

/*
 * Initialisation des variables locales
 */
//$myCodeImport=$txtCodeImport;
?>
<html><head><title>Import des descriptions des champs du fichier de données sédiments</title></head>
<body>
<?php
$myCodeImport="IMP_DONNEES_SEDIMENTS";
if(isset($cmdOk) && $myCodeImport!="")
{
	if(isset($userfile) && is_readable($userfile["tmp_name"]))
	{
		if(isset($_POST["chkNormalizeCSV"]) && $_POST["chkNormalizeCSV"]=="1")
		{
			echo "Normalisation du fichier ...";
			$myRetour=Tools::NormalizeCSV($userfile["tmp_name"]);
			echo ($myRetour?"OK":"ERREUR")."<br/>\n";
		}
		$xls=new clsDataFile($userfile["tmp_name"],"csv");
		$row=1; //for ($row=1;$row<=$xls->rowcount();$row++)
		$col=1;
		//$myCodeImport="IMP_DONNEES_POISSONS";
		
		/*
		 * 
		 * Nettoyage des bases de correspondance : colonnes, description, relation entre les deux
		 * 
		 */
		
		//*
		if(isset($chkClearTable) && $chkClearTable=="1")
		{
			$database->setQuery("SELECT * FROM #__a_import_description WHERE code_import_description='".$myCodeImport."';");
			$myListImports=$database->loadObjectList();
			if(is_array($myListImports) && count($myListImports)>0)
			{
				foreach($myListImports as $curimport)
				{
					$myIdDescription=$curimport->id_a_import_description;
					$database->setQuery("SELECT GROUP_CONCAT(DISTINCT a_import_colonne_id_a_import_colonne) as list_col FROM #__rel_import_colonne_import_description WHERE  a_import_description_id_a_import_description=".$myIdDescription.";");
					$myListItems=$database->loadObjectList();
					if(is_array($myListItems) && count($myListItems)>0)
					{
						$myListTxt=$myListItems[0]->list_col;
						$database->setQuery("DELETE FROM #__rel_import_colonne_parametre WHERE a_import_colonne_id_a_import_colonne IN (".$myListTxt.");");
						$database->query();
						$database->setQuery("DELETE FROM #__a_import_colonne WHERE id_a_import_colonne IN (".$myListTxt.");");
						$database->query();
					}
					$database->setQuery("DELETE FROM #__rel_import_colonne_import_description WHERE a_import_description_id_a_import_description=".$myIdDescription.";");
					$database->query();
					$database->setQuery("DELETE FROM #__a_import_description WHERE id_a_import_description=".$myIdDescription.";");
					$database->query();
				}
			}
		}
		//*/
		
		/*
		 * Initialisation ou mise à jour de la table des descriptions d'import
		 * Le présent fichier initialise ou met à jour la description de l'import du fichier "données poissons" uniquement
		 */
		$myObj=null;
		$database->setQuery("SELECT * FROM a_import_description WHERE code_import_description='".$myCodeImport."';");
		if($database->loadObject($myObj))
		{
			$myObj->libelle_import_description="Import Données Poissons";
			$myObj->auteur_import_description="YBS";
			$myObj->firstline_import_description="".strval(IMPORTSEDIMENT_FIRST_ROW);
			$myObj->headerline_import_description="".strval(IMPORTSEDIMENT_HEADER_ROW);
			$myObj->firstcolumn_import_description="".strval(IMPORTSEDIMENT_FIRST_COL);
			$myObj->date_modification_import_description=date("Y-m-d");
			$myObj->description_import_description=$_FILES["userfile"]["name"]." => Import conçu pour récupération des données SEDIMENTS";
			$database->updateObject("a_import_description",$myObj,"id_a_import_description");
		}
		else
		{
			$myObj->id_a_import_description=null;
			$myObj->firstline_import_description="".strval(IMPORTSEDIMENT_FIRST_ROW);
			$myObj->headerline_import_description="".strval(IMPORTSEDIMENT_HEADER_ROW);
			$myObj->firstcolumn_import_description="".strval(IMPORTSEDIMENT_FIRST_COL);
			$myObj->code_import_description=$myCodeImport;
			$myObj->libelle_import_description="Import Données Poissons";
			$myObj->auteur_import_description="YBS";
			$myObj->date_creation_import_description=date("Y-m-d");
			$myObj->date_modification_import_description=date("Y-m-d");
			$myObj->description_import_description=$_FILES["userfile"]["name"]." => Import conçu pour récupération des données SEDIMENTS de l'AE";
			$database->insertObject("a_import_description",$myObj,"id_a_import_description");
		}
		
		$myImportId=$myObj->id_a_import_description;
		if(is_null($myImportId) || intval($myImportId)<=0)
			$error_msg.=("Erreur : aucun ID d'import disponible");
		else
		{
			/*
			 * Préparation de l'import des colonnes du fichier de données poissons
			 */
			echo "<a href=\"javascript:\" onclick=\"document.getElementById('logimport').style.display=((document.getElementById('logimport').style.display=='none')?'block':'none');\">Import des descriptions terminé, afficher le Log d'import</a><div style='display:none;' id='logimport'>";
			$myObj = new stdClass();
			$myNbAdded=0; $myNbErrAdded=0;
			$myNbUpdated=0; $myNbErrUpdated=0;
			//$database->setQuery("TRUNCATE TABLE parametre;");
			//$database->query();
			for ($row=1;$row<$xls->rowcount();$row++)
			{
				/*
				define("DESCR_COL_NAME",3);
				define("DESCR_COL_COLNB",4);
				define("DESCR_COL_IMPORTOUINON",5);
				define("DESCR_COL_TABLE",6);
				define("DESCR_COL_CHAMP",7);
				define("DESCR_COL_FORMAT",8);
				define("DESCR_COL_FORMATDESCRIPTION",9);
				define("DESCR_COL_CODESANDRE",10);
				define("DESCR_COL_SANDREOUINON",11);
				*/
				echo "Test de colonne d'import : ".$xls->val($row,DESCR_COL_IMPORTOUINON).", d'index ".$myCodeImport."_".$xls->val($row,DESCR_COL_COLNB)."<br>\n";
				//if($xls->val($row,DESCR_COL_IMPORTOUINON)!="1") continue;
				$myObj=null;
				$database->setQuery("SELECT * FROM a_import_colonne WHERE cd_import_colonne='".$myCodeImport."_".$xls->val($row,DESCR_COL_COLNB)."';");
				//echo "Requête de recherche : ".$database->getQuery();
				$boolNouvelImport=true;
				if($database->loadObject($myObj,false))
					$boolNouvelImport=false;
					
				$myObj->cd_import_colonne=$myCodeImport."_".$xls->val($row,DESCR_COL_COLNB);
				$myObj->libelle_import_colonne=$xls->val($row,DESCR_COL_NAME);
				$myObj->description_import_colonne=$xls->val($row,DESCR_COL_NAME);
				$myObj->nbcols_import_colonne=intval($xls->val($row,DESCR_COL_COLNB));
				$myObj->type_import_colonne=$xls->val($row,DESCR_COL_TABLE);
				$myObj->parametre_import_colonne=$xls->val($row,DESCR_COL_CHAMP);
				
				$myObj->format_import_colonne=$xls->val($row,DESCR_COL_FORMAT);
				$myObj->valeurformat_import_colonne=$xls->val($row,DESCR_COL_FORMATDESCRIPTION);
				
				$myObj->export_simple_colonne=$xls->val($row,DESCR_COL_EXPORT_SIMPLE);
				$myObj->export_complet_colonne=$xls->val($row,DESCR_COL_EXPORT_COMPLET);
				$myObj->export_libelle_colonne=$xls->val($row,DESCR_COL_EXPORT_LIBELLE);
				$myObj->export_format_valeur=$xls->val($row,DESCR_COL_EXPORT_FORMAT);
				$myObj->import_inclut_colonne=$xls->val($row,DESCR_COL_IMPORTOUINON);
				$myObj->export_code_groupe=$xls->val($row,DESCR_COL_EXPORT_GROUP);
				$myObj->import_date_modification=date("Y-m-d H:i:s");
				
				if(!$boolNouvelImport)
				{
					echo "\t\t... trouvé<br />\n";
					$return=$database->updateObject("a_import_colonne",$myObj,"id_a_import_colonne");
					if($return)
						$myNbUpdated++;
					else
					{
						$myNbErrUpdated++;
						$myErrUpdatedList[]=array($return,$database->getQuery(),$database->getErrorNum(),$database->getErrorMsg());
					}
				}
				else
				{
					$myObj->import_date_creation=date("Y-m-d H:i:s");   	 
					echo "\t\t... NON trouvé, ajout<br />\n";
					$myObj->id_a_import_colonne=null;
					$return=$database->insertObject("a_import_colonne",$myObj,"id_a_import_colonne");
					if($return)
					{
						$myObjRel=null;
						$myObjRel->a_import_description_id_a_import_description=$myImportId;
						$myObjRel->a_import_colonne_id_a_import_colonne=$myObj->id_a_import_colonne;
						$database->insertObject("rel_import_colonne_import_description",$myObjRel,"id_rel_import_colonne_import_description");
						$myNbAdded++;	
					}
					else
					{
						$myNbErrAdded++;
						$myErrAddedList[]=array($return,$database->getQuery(),$database->getErrorNum(),$database->getErrorMsg());
					}
					
				}


				/*
				 * Etablissement du lien entre la colonne ajoutée et le paramètre standard
				 */
				if($return && $myObj->type_import_colonne=="analyse")
				{
					$myObjId=$myObj->id_a_import_colonne;
					$database->setQuery("DELETE FROM #__rel_import_colonne_parametre WHERE a_import_colonne_id_a_import_colonne=".$myObjId."");
					$myCode=strval(intval($xls->val($row,DESCR_COL_COLNB)));
					$myCodeParamRef="D".str_repeat("0",4-strlen($myCode)).$myCode;
					$database->setQuery("SELECT * FROM #__parametre WHERE par_code_parametre='".$xls->val($row,DESCR_COL_CODESANDRE)."' OR par_code_parametre='".$myCodeParamRef."' GROUP BY id_parametre;");
					$myParamObj=null;
					if($database->loadObject($myParamObj))
					{
						echo "Ajout lien entre colonne et paramètre : ".$xls->val($row,DESCR_COL_CODESANDRE)."/".$myCodeParamRef." => ".$myObjId.", id_parametre : ".$myParamObj->id_parametre.", code final : ".$myParamObj->par_code_parametre."<br />\n";
						$myRel=new stdClass();
						$myRel->id_rel_import_colonne_parametre=null;
						$myRel->a_import_colonne_id_a_import_colonne=$myObjId;
						$myRel->parametre_id_parametre=$myParamObj->id_parametre;
						$database->insertObject("#__rel_import_colonne_parametre",$myRel,"id_rel_import_colonne_parametre");
					}
				}
			}
			
			echo "Nombre de colonnes ajoutés en base : ".$myNbAdded." / erreurs : ".$myNbErrAdded."<br>\n";
			if($myNbErrAdded>0)
				echo Tools::Display($myErrAddedList);
			echo "Nombre de colonnes mis à jour en base : ".$myNbUpdated." / erreurs : ".$myNbErrUpdated."<br>\n";
			if($myNbErrUpdated>0)
				echo Tools::Display($myErrUpdatedList);
		}
		echo "</div>\n";
		echo "<hr />\n";
	}
	else
	{
		$error_msg.="Erreur : aucun fichier<br/>\n";
	}	
}
?>
	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>
	
	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
	
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label>Sélectionner le fichier CSV : <input type="file" name="userfile"  /></label><br />
		<label><input type="checkbox" name="chkClearTable" value="1" checked />Nettoyer la table avant l'import (truncate table)</label><br />
		<label><input type="checkbox" name="chkNormalizeCSV" value="1" checked />Normaliser le fichier CSV : ôter mes retours chariot des contenus de cellules (\n seuls transformés en espaces)</label><br />
		<?php /*
		<label>
			Sélectionner le type de format d'import :
			<select name="txtCodeImport">
				<?php
					$myGroupes=new mdtb_a_import_description($database,$template_name,basename(__FILE__),$path_abs,true);
					$myGroupes->recSearch();
					$myGroupes->recFirst();
					do
					{
						$myObj=$myGroupes->recGetRecord();
						echo "<option value='".$myGroupes->recGetValue("code_import_description")."'>&nbsp;".$myGroupes->recGetValue("libelle_import_description")."<br />\n";
					} while($myGroupes->recNext());
				?>
			</select>
		</label>
		*/
		?>
		<br />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>

<a href="index.php">Retour</a>
</body>
</html>