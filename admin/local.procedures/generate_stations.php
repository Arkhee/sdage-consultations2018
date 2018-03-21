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


define("IMPORT_COL_STATION_CODE_HYDRO",11);
define("IMPORT_COL_STATION_CODE",12);
define("IMPORT_COL_STATION_LIBELLE",13);
define("IMPORT_COL_STATION_COORDX",14);
define("IMPORT_COL_STATION_COORDY",15);
define("IMPORT_COL_STATION_SECTEUR",16);

$error_msg="";
$display_msg="";
//require_once ($path_pre."local.lib/excel_reader2.php");
//$xls = new Spreadsheet_Excel_Reader("importdata/analyse-donnees-poisson.xls");
//$xls=new clsDataFile("importdata/repartition-donnees-excel-mcd.csv","csv");
?>
<html><head><title>Import des stations complémentaires spécifiques DIREN issues du fichiers de données poisson</title></head>
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
		$myCleaned=array();
		for ($row=IMPORT_FIRST_ROW;$row<$xls->rowcount();$row++)
		{
			
			if($xls->val($row,IMPORT_COL_STATION_CODE)=="") 
				continue;
			$myObj=null;
			$myCode=strval($xls->val($row,IMPORT_COL_STATION_CODE));
			if(!in_array(substr($myCode,0,8),$myCleaned))
			{
				$myCleaned[]=substr($myCode,0,8);
				$database->setQuery("DELETE FROM #__station_de_mesure WHERE sta_code_station='".substr($myCode,0,8)."';");
				$database->query();
			}
			$database->setQuery("SELECT * FROM #__station_de_mesure WHERE sta_code_station='".$myCode."';");
			$myObjExists=false;
			if($database->loadObject($myObj))
				$myObjExists=true;

			$myObj->sta_code_station=$myCode;
			$myObj->cea_code_entite=$xls->val($row,IMPORT_COL_STATION_CODE_HYDRO);
			$myObj->sta_libelle_national=$xls->val($row,IMPORT_COL_STATION_LIBELLE);
			$myObj->sta_localisation_precise=$xls->val($row,IMPORT_COL_STATION_LIBELLE);
			$myObj->sta_nom_station=$xls->val($row,IMPORT_COL_STATION_LIBELLE);
			$myObj->sta_coord_x=$xls->val($row,IMPORT_COL_STATION_COORDX);
			$myObj->sta_coord_y=$xls->val($row,IMPORT_COL_STATION_COORDY);
			$myObj->sta_ns_secteur=$xls->val($row,IMPORT_COL_STATION_SECTEUR);
			$myObj->sta_modification=date("Y-m-d");
			$myObj->code_diren="";
			if(intval(substr($myCode,0,1))!=0)
				$myObj->code_diren=$myCode;
			
			if($myObjExists)
			{
				$return=$database->updateObject("#__station_de_mesure",$myObj,"id_station_de_mesure");
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
				$myObj->id_station_de_mesure=null;
				$myObj->sta_date_creation=date("Y-m-d");
				$return=$database->insertObject("#__station_de_mesure",$myObj,"id_station_de_mesure");
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
	echo "Nombre de stations ajoutés en base : ".$myNbAdded." / erreurs : ".$myNbErrAdded."<br>\n";
	if($myNbErrAdded>0)
		echo Tools::Display($myErrAddedList);
	echo "Nombre de stations mis à jour en base : ".$myNbUpdated." / erreurs : ".$myNbErrUpdated."<br>\n";
	if($myNbErrUpdated>0)
		echo Tools::Display($myErrUpdatedList);
	echo "<hr />\n";
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
		<input type="submit" name="cmdOk" value="Ok" />
	</form>

<a href="index.php">Retour</a>
</body>
</html>