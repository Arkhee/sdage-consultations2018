<?php
/*
 * diren-pcb
 * Created on 15 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : tpl_generate_importparams.php
 * Description : 
 * A partir d'un fichier de référence, analyse-donnees-poisson.xls, réalise l'import des paramètres
 * dans la table des paramètres. Ce fichier de référence est le même que celui utilisé dans generate_importdescription.php
 * Il s'agit du pendant de ce script, mais côté paramètre en base.
 */
$path_pre="../";
require_once($path_pre."config.inc.php");
//require_once '../local.lib/excel_reader2.php';
//$xls = new Spreadsheet_Excel_Reader("importdata/analyse-donnees-poisson.xls");
require_once("utils.class.php");
require_once("parametre.class.php");


$error_msg="";
$display_msg="";
//require_once ($path_pre."local.lib/excel_reader2.php");
//$xls = new Spreadsheet_Excel_Reader("importdata/analyse-donnees-poisson.xls");
//$xls=new clsDataFile("importdata/repartition-donnees-excel-mcd.csv","csv");
?>
<html><head><title>Import des descriptions des champs du fichier de données poissons</title></head>
<body>
<?php
if(isset($_POST["cmdOk"]))
{
	if(isset($_FILES["userfile"]) && is_readable($_FILES["userfile"]["tmp_name"]))
	{
		$xls=new clsDataFile($_FILES["userfile"]["tmp_name"],"csv");
		$myObj = new stdClass();
		$myNbAdded=0; $myNbErrAdded=0;
		$myNbUpdated=0; $myNbErrUpdated=0;
		
		//$database->setQuery("TRUNCATE TABLE parametre;");
		//$database->query();
		for ($row=1;$row<=$xls->rowcount();$row++)
		{
			
			if($xls->val($row,DESCR_COL_CODESANDRE)=="" || $xls->val($row,DESCR_COL_IMPORTOUINON)==0 || $xls->val($row,DESCR_COL_TABLE)!="analyse") 
				continue;
			$myObj=null;
			$myCode=strval(intval($xls->val($row,DESCR_COL_COLNB)));
			$myCodeParamRef="D".str_repeat("0",4-strlen($myCode)).$myCode;
			$database->setQuery("SELECT * FROM parametre WHERE par_code_parametre='".$myCodeParamRef."';");
			if($database->loadObject($myObj))
			{
				$myObj->par_nom=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_statut="valide";
				$myObj->par_date_creation=date("Y-m-d");
				$myObj->par_date_modification=date("Y-m-d");
				$myObj->par_auteur="SP";
				$myObj->par_libelle_court=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_libelle_long=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_definition=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_references_biblio="";
				$myObj->par_commentaires="";
				$myObj->par_nom_international=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_calcule=$xls->val($row,DESCR_COL_CALCUL);
				//echo "MAJ : ".Tools::Display($myObj)."<br />\n";
				///*
				$return=$database->updateObject("#__parametre",$myObj,"id_parametre");
				if($return) $myNbUpdated++;
				else
				{
					$myNbErrUpdated++;
					$myErrUpdatedList[]=array($return,$database->getQuery(),$database->getErrorNum(),$database->getErrorMsg());
				}
				//*/
			}
			else
			{
				$myObj->id_parametre=null;
				$myObj->par_code_parametre=$myCodeParamRef;
				$myObj->par_nom=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_statut="valide";
				$myObj->par_date_creation=date("Y-m-d");
				$myObj->par_date_modification=date("Y-m-d");
				$myObj->par_auteur="SP";
				$myObj->par_libelle_court=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_libelle_long=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_definition=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_references_biblio="";
				$myObj->par_commentaires="";
				$myObj->par_nom_international=$xls->val($row,DESCR_COL_NAME);
				$myObj->par_calcule=$xls->val($row,DESCR_COL_CALCUL);



				//for ($col=1;$col<= $xls->colcount() ;$col++)
				//echo "Param ligne ".$row." : ".$xls->val($row,$col)."<br>\n";
				/*
				$myObj->id_parametre=null;
				$myObj->cd_parametre=$xls->val($row,9);
				$myObj->date_creation_parametre=date("Y-m-d");
				$myObj->date_modification_parametre=date("Y-m-d");
				$myObj->auteur_parametre="YBS";
				$myObj->nom_parametre=$xls->val($row,1);
				$myObj->statut_parametre="ok";
				$myObj->libelle_court_parametre=$xls->val($row,1);
				$myObj->libelle_long_parametre=$xls->val($row,1);
				$myObj->type_parametre=$xls->val($row,8);
				$myObj->formule_parametre=$xls->val($row,13);
				$myObj->parametre_calcule=((strtolower($xls->val($row,8))!="valeur")?"1":"0");
				$myObj->lexique_table_parametre=$xls->val($row,10);
				$myObj->lexique_champ_parametre=$xls->val($row,11);
				$myObj->formule_coefficient_parametre=$xls->val($row,12);
				$myObj->definition_parametre="";
				$myObj->reference_bibliographique_parametre="";
				$myObj->commentaire_parametre="";
				$myObj->nom_international_parametre=$xls->val($row,9);
				*/
				//echo "Ajout : ".Tools::Display($myObj)."<br />\n";
				///*
				$return=$database->insertObject("#__parametre",$myObj,"id_parametre");
				if($return) $myNbAdded++;
				else
				{
					$myNbErrAdded++;
					$myErrAddedList[]=array($return,$database->getQuery(),$database->getErrorNum(),$database->getErrorMsg());
				}
				//*/
			}
		}	
	}
	echo "Nombre de paramètres ajoutés en base : ".$myNbAdded." / erreurs : ".$myNbErrAdded."<br>\n";
	if($myNbErrAdded>0)
		echo Tools::Display($myErrAddedList);
	echo "Nombre de paramètres mis à jour en base : ".$myNbUpdated." / erreurs : ".$myNbErrUpdated."<br>\n";
	if($myNbErrUpdated>0)
		echo Tools::Display($myErrUpdatedList);
	echo "<hr />\n";
}
/*
if(class_exists("mdtb_rel_import_colonne_parametre"))
{
	echo "La classe 'mdtb_rel_import_colonne_parametre' existe !<br />\n";
	//$mySearchText="MIRI%";
	$myStation=new mdtb_rel_import_colonne_parametre($database,$template_name,basename(__FILE__),$path_abs,true); //($database,$template_name,basename(__FILE__),$path_abs,true);
	//$myStation->recSearch($mySearchText);
	$myArr=$myStation->recGetFieldsList();
	echo "Liste des champs : ".Tools::Display($myArr);
	$myStation->recSetValue("parametre_id_parametre",99999);
	$myStation->recSetValue("a_import_colonne_id_a_import_colonne",66666);
	$myObj=$myStation->recGetRecord();
	//echo Tools::Display($myObj);
	//$myStation->recStore();
	// *
	echo "NB Valeurs trouvées pour ".$mySearchText." : ".$myStation->recCount()."<br />\n";
	$myStation->recFirst();
	$myCount=0;
	do
	{
		$myObj=$myStation->recGetRecord();
		echo Tools::Display($myObj);
		//$myObj->sta_superficie_bassin_topo=0;
		//$myStation->recSetRecord($myObj);
		//$myStation->recStore($myObj);
	} while($myStation->recNext());
	echo "Arrêt, count : ".$myCount."<br>\n";
	//* /
}
//*/
?>
	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>
	
	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
	
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label>Sélectionner le fichier CSV : <input type="file" name="userfile"  /></label><br />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>

<a href="index.php">Retour</a>
</body>
</html>