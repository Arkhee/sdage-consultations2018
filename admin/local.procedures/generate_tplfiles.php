<?php
/*
 * diren-pcb
 * Created on 8 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : generate_tplfiles.php
 * Description : 
 * Fabrique les fichier tpl_ qui sont les pages permettant l'accès aux tables de la base de données
 * La liste des fichiers tpl est construite à partir du dossier importdata, et des fichiers json qu'il contient
 */
if(!isset($_GET["pass"]) || $_GET["pass"]!="reset_tpl_files")
	die("Pas les droits");
$overwrite=0;
if(isset($_GET["overwrite"]) && $_GET["overwrite"]=1)
	$overwrite=1;
require_once("utils.class.php");
/*
$myArrayClasses=array("lieu_peche",
"parametre",
"point_de_prelevement",
"rel_analyse_fraction_analysee",
"rel_analyse_individu",
"rel_analyse_intervenant",
"rel_analyse_parametre",
"rel_analyse_prelevement",
"rel_import_colonne_import_description",
"rel_import_colonne_parametre",
"rel_intervenant_a_type_intervenant",
"rel_intervenant_prelevement",
"rel_i_analyse_fraction_analysee",
"rel_i_analyse_individu",
"rel_i_analyse_intervenant",
"rel_i_analyse_i_prelevement",
"rel_i_analyse_parametre",
"rel_i_prelevement_cours_eau",
"rel_i_prelevement_espece",
"rel_i_prelevement_lieu_peche",
"rel_i_prelevement_point_de_prelevement",
"rel_parametre_a_formule_parametre",
"rel_parametre_a_synonyme",
"rel_parametre_coefficient_parametre",
"rel_parametre_w_groupe_parametre",
"rel_prelevement_cours_eau",
"rel_prelevement_espece",
"rel_prelevement_lieu_peche",
"rel_prelevement_parametre",
"rel_prelevement_point_de_prelevement",
"rel_prelevement_station_prelevement",
"secteur_peche",
"station_prelevement",
"w_groupe_parametre");
*/
$path_pre="../";
$json_path="importdata/";
$tpl_path="codetemplates/";
$myFile=file_get_contents($tpl_path."tpl_default.phpt");
$myClassList=ImportUtils::getClassList($json_path);
if($myClassList!==false)
{
	foreach($myClassList as $curclass)
	{
		echo "Réinitialisation du fichier tpl pour la classe ".$curclass." ... \n";
		$myCurFile=str_replace("[CLASSNAME]","mdtb_".$curclass,$myFile);
		if($overwrite==1 || !file_exists($path_pre."tpl_".$curclass.".php"))
		{
			file_put_contents($path_pre."tpl_".$curclass.".php",$myCurFile);
			echo "fichier créé<br />";
		}
		else
			echo "fichier NON créé<br />";
	}
}

?>
<a href="index.php">Retour</a>