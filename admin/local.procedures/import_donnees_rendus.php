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
if(file_exists($path_pre."local.classes/batchfile.class.php"))
	include_once($path_pre."local.classes/batchfile.class.php");

if(!defined("BR")) 	define("BR","<br />\n");
$myClsParam=new clsParametre($database);
$myXLSFileName="";
$cmbIntervenant="";
//$cmbIntervenantGestionnaire="";
//$txtDateAnalyse="";
$txtDateReception="";

// Définition de la correspondance entre les codes d'intervenant fichier et les codes d'intervenant en n° d'ID
// Champ de données concerné : "Source de la donnée"
$myIdGestionnaire=array("DIREN BG"=>"711","DIREN FC"=>"716","DIREN LR"=>"721","DIREN RA"=>"732");
$myCodeGestionnaire=array("DIREN BG"=>"17210681700015","DIREN FC"=>"17250681800019","DIREN LR"=>"17340680200038","DIREN RA"=>"962");
$myLexiqueFractionAnalysee=array("NQNT"=>"155","F"=>"102","E"=>"101");
$myLexiques["CodeGestionnaire"]=$myCodeGestionnaire;
$myLexiques["FractionAnalysee"]=$myLexiqueFractionAnalysee;
Tools::BindToGlobal($_GET);
Tools::BindToGlobal($_POST);
Tools::BindToGlobal($_FILES);
if(isset($userfile["tmp_name"]))
{
	$myXLSFileName=$userfile["tmp_name"];
	$myBatchFileName=$userfile['name'];	
}
$error_msg="";
$display_msg="";
if(!isset($action)) $action = "";
$myCodeImport="IMP_DONNEES_RENDUS";
$myAuteurImport="importrendu";
$myBatchFile=new batchfile($database);
if($action=="deletebatch" && $batch_id>0)
{
	$display_msg.= "Effacement du lot ".$batch_id.BR;
	$myBatchFile->delete($batch_id);
}
if($action=="deletebatchdata" && $batch_id>0)
{
	$display_msg.= "Effacement du lot ".$batch_id." et des données associées".BR;
	$myBatchFile->delete_batch_data($batch_id);
}
if($action=="validatebatch" && $batch_id>0)
{
	$display_msg.= "Validation du lot ".$batch_id.BR;
	$myBatchFile->validate($batch_id);
}
if($action=="unvalidatebatch" && $batch_id>0)
{
	$display_msg.= "InValidation du lot ".$batch_id.BR;
	$myBatchFile->unvalidate($batch_id);
}

/*
 * 
 * 
 * 
 * Traitement des fichiers et sauvegarde des données en base
 * 
 * 
 * 
 */

$row=1; 
$col=1;

if($action=="sendcsv" &&  file_exists($myXLSFileName) )
{
	
	if(isset($_POST["chkNormalizeCSV"]) && $_POST["chkNormalizeCSV"]=="1")
	{
		$display_msg.= "Normalisation du fichier ...";
		$myRetour=Tools::NormalizeCSV($myXLSFileName);
		$display_msg.= ($myRetour?"OK":"ERREUR")."<br/>\n";
	}

	
	$display_msg.= "Ouverture du fichier ".$myXLSFileName."<br>\n";
	if(is_readable($myXLSFileName))
	{
		$display_msg.= "Fichier lisible ...<br>\n";
		$xls=new clsDataFile($myXLSFileName,"csv");
/*
		$xls = new Spreadsheet_Excel_Reader($myXLSFileName);
*/
		$display_msg.= "Fichier ouvert ...<br>\n";
	}
	else
		$display_msg.= "Fichier non lisible<br>\n";
}
else
	if($myXLSFileName!="")
		$display_msg.= "Fichier non existant<br>\n";
/*
$database->setQuery("TRUNCATE table rel_import_colonne_import_description;");
$database->query();
$database->setQuery("TRUNCATE table a_import_description;");
$database->query();
$database->setQuery("TRUNCATE table a_import_colonne;");
$database->query();
//*/
if(isset($xls) && is_object($xls) && $xls->initok)
{
	
	$display_msg.= "Ouverture du type d'import : ".$myCodeImport."<br>\n";
	$display_msg.= "Initialisation du nouveau batch".BR;
	$myClsParam->setCodeImport($myCodeImport);
	$myClsParam->initBatch($myCodeImport,$myAuteurImport,basename($myBatchFileName));
	$display_msg.= "Ouverture du type d'import : ".$myCodeImport.BR;
	
	$myObj=null;
	$database->setQuery("SELECT * FROM a_import_description WHERE code_import_description='".$myCodeImport."';");
	if($database->loadObject($myObj))
	{
		//$display_msg.= "Le type a été ouvert. Objet : ".Tools::Display($myObj)."<br>\n";
		$myImportId=$myObj->id_a_import_description;
		if(is_null($myImportId) || intval($myImportId)<=0)
			die("Erreur : aucun ID d'import disponible");
		$myRequeteColonnes="SELECT * FROM a_import_colonne,rel_import_colonne_import_description " .
							"WHERE a_import_description_id_a_import_description=".$myImportId." " .
							"AND a_import_colonne_id_a_import_colonne=id_a_import_colonne AND (import_inclut_colonne & 1)>0 ";
		$database->setQuery($myRequeteColonnes);
		$myListCols=$database->loadObjectList();
		$myPrelIndex=1;
		//echo "Colonnes définies dans cet import (".count($myListCols)."):".Tools::Display($myListCols)."<br>\n" ;
		if(is_array($myListCols) && count($myListCols)>0)
		{
			$display_msg.= "Démarrage de l'import effectif<br>\n";
			$myClsParametre=new clsParametre($database);
			$row=$myObj->headerline_import_description;
			$myArrayCol2Param=array();
			//$display_msg.= "Scan de la ligne d'entête pour replacer les colonnes<br>\n";
			//$display_msg.= "Ligne : ".implode(",",$xls->data[0])."<br>\n";
			$display_msg.= "Import de la colonne ".$myObj->firstcolumn_import_description." à la colonne ".$xls->colcount()."<br>\n";
			for($col=$myObj->firstcolumn_import_description;$col<$xls->colcount();$col++)
			{
				$myClsNb=$myClsParametre->rechercheParametre($xls->val($row,$col),$myImportId);
				//$display_msg.= "Vérification colonne ".$xls->val($row,$col)." ==> ".implode(",",$myClsNb)."<br>\n";
				foreach($myListCols as $key=>$val)
					if(in_array($val->cd_import_colonne,$myClsNb))
						$myArrayCol2Param[$col][]=$val;
				
			}
			
			//die("ArrayCol2Param : ".Tools::Display($myArrayCol2Param));
			$myClsParam->setArrayToColAssociation($myArrayCol2Param);
			$myClsParam->checkValues();
			$display_msg.= "Nombre de champs sans table :" .count($myClsParam->fields_without_table)."<br />\n";
			$display_msg.= "Nombre de tables : ".count($myClsParam->tables_to_fields)."<br />\n";
			foreach($myClsParam->tables_to_fields as $curtable => $curfield)
			{
				$display_msg.= "Table : ".$curtable."<br />\n";
			}
			
			
			/*
			 * Vérification et création des pêches si elles n'existent pas
			 */
			$debugpeche=false;
    	  	$myClsParamPeche=new clsParametre($database);
    	  	$myClsParamPeche->setCodeImport($myCodeImport);
    	  	$myClsParamPeche->loadBatch($myClsParam->import_batch->id_a_import_batch);
			//echo "Objet batch : ".Tools::Display($myClsParam->import_batch).BR;
			
    	  	$myCurObj=$myClsParamPeche->chargeDescriptionImports($myClsParamPeche->code_import);
    	  	if($myCurObj!==false)
    	  	{
    	  		$display_msg.= __LINE__." => Description des imports chargée pour la pêche".BR;
    	  		$myCurArrayCol2Param=$myClsParamPeche->chargeArrayToColAssociation($xls,$myCurObj,array("2"));
    	  		if($myCurArrayCol2Param!==false)
    	  		{
	    	  		$display_msg.= __LINE__." => Tableau associatif défini".BR;
    	  			$myClsParamPeche->setArrayToColAssociation($myCurArrayCol2Param);
					$myClsParamPeche->checkValues();
					if($debugpeche) echo __LINE__." => Nombre de champs sans table :" .count($myClsParamPeche->fields_without_table)."<br />\n";
					if($debugpeche) echo __LINE__." => Nombre de tables : ".count($myClsParamPeche->tables_to_fields)."<br />\n";
					//echo "ArrayCol2Param : ".Tools::Display($myArrayCol2Param);
					if(count($myClsParamPeche->tables_to_fields)>0)
					{
						foreach($myClsParamPeche->tables_to_fields as $curtable => $curfield)
						{
							if($debugpeche) echo __LINE__." => Table : ".$curtable."<br />\n";
						}
						
						//echo "Params : ".Tools::Display($myArrayCol2Param);
						$myNBLignesDeDonees=intval($xls->rowcount()-$myObj->firstline_import_description+1);
						if($debugpeche) echo __LINE__." => Début de la sauvegarde des paramètres, au total ".($myNBLignesDeDonees-1)." lignes de données".BR;
						for ($row=$myObj->firstline_import_description;$row<$xls->rowcount();$row++)
						{
							$display_msg.= "Ligne N°".intval(1+$row-$myObj->firstline_import_description)."/".($myNBLignesDeDonees-1)."<br />\n";
							$myClsParamPeche->cleanLine();
							for($col=$myObj->firstcolumn_import_description;$col<=$xls->colcount();$col++)
							{
								if(isset($myCurArrayCol2Param[$col]))
									$myClsParamPeche->setParam($myCurArrayCol2Param[$col],$xls->val($row,$col));
							}
							$myPecheIndex=$myClsParamPeche->savePeche();
							if($myPecheIndex!==true)
								$display_msg.= $myPecheIndex;
							//break;
						}	
					}
					else
						 if($debugpeche) echo __LINE__." => Aucune table : vérifiez le fichier d'import<br />\n";
    	  		}
				else
					if($debugpeche) echo __LINE__." => Aucune colonne dans l'import.<br>\n";
    	  	}
			
			//die(__LINE__." => Fin des traitements");

			/*
			 * 
			 * Insertion des données au sein des pêches
			 * 
			 */
			
			
			$myNBLignesDeDonees=intval($xls->rowcount()-$myObj->firstline_import_description+1);
			$display_msg.= "Début de la sauvegarde des paramètres, au total ".$myNBLignesDeDonees." lignes<br>\n";
			
			$myLigneReprise=-1;
			for ($row=$myObj->firstline_import_description;$row<$xls->rowcount();$row++)
			{
				$myCurLigne=intval(1+$row-$myObj->firstline_import_description);
				if($myCurLigne>$myLigneReprise)
				{
					$display_msg.= "Ligne N°".$myCurLigne."/".$myNBLignesDeDonees."<br />\n";
					$myClsParam->cleanLine();
					for($col=$myObj->firstcolumn_import_description;$col<=$xls->colcount();$col++)
					{
						//echo "Colonne : ".$col."<br>\n";
						if(isset($myArrayCol2Param[$col]))
							$myClsParam->setParam($myArrayCol2Param[$col],$xls->val($row,$col));
					}
					//echo "Objet : ".Tools::Display($myClsParam->curobject);
					//break;
					//continue;
					//$display_msg.= "Objet courant : ".Tools::Display($myClsParam->curobject);
					//break;
					//$myIntervenant=new mdtb_intervenant($database,$template_name,basename(__FILE__),$path_abs,true);
					//$myIntervenant->recLoad($cmbIntervenantGestionnaire);
					//$myCodeGestionnaire=$myIntervenant->recGetValue("int_code_intervenant");
					//continue;
					$myPrelIndex=$myClsParam->saveLineRendu($myLexiques,$cmbIntervenant,$txtDateReception);
					$display_msg.= "Création d'un prélèvement : ".$myPrelIndex.BR."<br>\n";
					//if($myCurLigne>5)
						//break;
				}
				//break;
				//*/
			}
		}
		else
			$display_msg.= "Aucune colonne dans l'import.".Tools::Display($myRequeteColonnes)."<br>\n";
	}
	else
		$display_msg.= "Type d'import introuvable<br>\n";
	$display_msg.= "<hr />\n";
}

/*
 * 
 * 
 * 
 * Fin du Traitement 
 * 
 * 
 * 
 */

TT_Template::HTMLHeaderBegin(Tools::Translate("Import des données du fichier de Rendu"),"","../");
TT_Template::HTMLHeaderEnd();
TT_Template::HTMLBodyBegin();
?>
<body>
	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>
	
	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
<style>
	fieldset { padding:5px; margin-bottom:10px; }
	fieldset strong { margin:0px; padding:3px; font-size:14px; }
	table.tableBatchList { width:600px; border:2px solid #333333; border-collapse:collapse; }
	table.tableBatchList th { padding:3px; background-color:#EAEAEA; border:1px solid #888888; }
	table.tableBatchList td { padding:3px; border:1px solid #888888; }
</style>

<fieldset>
	<legend><strong>Importer un nouveau fichier Rendu Labo pour les données Poisson</strong></legend>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label>Sélectionner le fichier CSV : <input type="file" name="userfile"  /></label>
		<?php /*
		<label>
			Sélectionner le gestionnaire de cette donnée :
			<select name="cmbIntervenantGestionnaire" style="width:250px;">
				<?php
					$myIntervenant=new mdtb_intervenant($database,$template_name,basename(__FILE__),$path_abs,true);
					$myIntervenant->recSearch("","int_nom_intervenant","ASC");
					$myIntervenant->recFirst();
					do
					{
						$myObj=$myIntervenant->recGetRecord();
						echo "<option value='".$myIntervenant->recKeyValue()."' ".(($cmbIntervenantGestionnaire==$myIntervenant->recKeyValue())?"selected":"").">&nbsp;".$myIntervenant->recGetValue("int_nom_intervenant")."<br />\n";
					} while($myIntervenant->recNext());
				?>
			</select>
		</label>
		*/
		?>
		<label>
			Sélectionner le laboratoire d'analyses :
			<select name="cmbIntervenant" style="width:250px;">
				<?php
					$myIntervenant=new mdtb_intervenant($database,$template_name,basename(__FILE__),$path_abs,true);
					$myIntervenant->recSearch("","int_nom_intervenant","ASC");
					$myIntervenant->recFirst();
					do
					{
						$myObj=$myIntervenant->recGetRecord();
						echo "<option value='".$myIntervenant->recKeyValue()."' ".(($cmbIntervenant==$myIntervenant->recKeyValue())?"selected":"").">&nbsp;".$myIntervenant->recGetValue("int_nom_intervenant")."<br />\n";
					} while($myIntervenant->recNext());
				?>
			</select>
		</label>
		<label>Date Réception : <?php echo TT_Template::FORM_GetDate("txtDateReception",$txtDateReception); ?><br /><em>Date, au jour près, à laquelle l'échantillon est pris en charge par le laboratoire chargé d'y effectuer des analyses</em></label>
		<?php /* <label>Date d'analyse : <?php echo TT_Template::FORM_GetDate("txtDateAnalyse",$txtDateAnalyse); ?></label> */ ?>
		<label><input type="checkbox" name="chkNormalizeCSV" value="1" checked />Normaliser le fichier CSV : ôter mes retours chariot des contenus de cellules (\n seuls transformés en espaces)</label><br />
		<input type="submit" name="cmdOk" value="Ok" />
		<input type="hidden" name="action" value="sendcsv" />
	</form>
	</fieldset>
	<fieldset>
		<legend><strong>Gérer les imports précédents</strong></legend>
		
		<?php $myList=$myBatchFile->list_batch($myCodeImport,$myAuteurImport); ?>
		<table class="tableBatchList" border="0" cellspacing="0" cellpadding="3">
			<thead>
		      <tr>
		         <th>Id</th>
		         <th>Type du batch</th>
		         <th>Date</th>
		         <th>Heure</th>
		         <th>Auteur</th>
		         <th>Nb enr.</th>
		         <th>Statut</th>
		         <th>Actions</th>
		      </tr>
		   </thead>
		   <?php
		   	if($myList!==false)
		   	{
		   		foreach($myList as $curbatch)
		   		{
		   			?>
		   				<tr>
		   					<td  rowspan="2"><?php echo $curbatch->id_a_import_batch; ?></td>
		   					<td ><?php echo $curbatch->batch_name; ?></td>
		   					<td ><?php echo $curbatch->batch_date; ?></td>
		   					<td ><?php echo $curbatch->batch_time; ?></td>
		   					<td ><?php echo $curbatch->batch_author; ?></td>
		   					<td ><?php echo $curbatch->batch_taille; ?></td>
		   					<td ><?php echo ($curbatch->batch_validated==1)?"Validé":"Attente"; ?></td>
		   					<td  rowspan="2">
		   						<?php if($curbatch->batch_validated!=1) { ?>
		   						<a href="<?php echo basename($_SERVER["SCRIPT_NAME"])."?action=deletebatch&batch_id=".$curbatch->id_a_import_batch; ?>">Effacer le batch</a><br />
		   						<a href="<?php echo basename($_SERVER["SCRIPT_NAME"])."?action=deletebatchdata&batch_id=".$curbatch->id_a_import_batch; ?>">Effacer le batch et les données</a><br />
		   						<a href="<?php echo basename($_SERVER["SCRIPT_NAME"])."?action=validatebatch&batch_id=".$curbatch->id_a_import_batch; ?>">Valider le batch</a></td>
		   						<?php } else { ?>
		   						<a href="<?php echo basename($_SERVER["SCRIPT_NAME"])."?action=unvalidatebatch&batch_id=".$curbatch->id_a_import_batch; ?>">Invalider le batch</a></td>
		   						<?php } ?>
		   				</tr>
		   				<tr>
		   					<td colspan="6" ><?php echo $curbatch->batch_filename; ?></td>
		   				<tr>
		   			<?php
		   		}
		   	}
		   ?>
		</table>
			
	</fieldset>
	
<a href="index.php">Retour</a>