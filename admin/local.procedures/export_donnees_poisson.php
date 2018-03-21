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
 
/*
 * Initialisation des librairies de base
 */
if(!isset($path_pre))
	$path_pre="../";
else
{
	chdir($path_pre."local.procedures");
	$path_pre="../";
}
require_once($path_pre."config.inc.php");
require_once("parametre.class.php");

/*
 * Initialisation des variables communes au traitement
 */

$myClsParam=new clsParametre($database);
$myXLSFileName="";
$debug=false;
$output_for_format=true;
$mySeparateurCSV=",";

$cmbTypeSortie="complet";
$cur_format_sortie="";
if($cmbFormatSortie=="carto")
	$cur_format_sortie="carto";
$cmbFormatSortie="xls";
//die("Groupes : ".Tools::Display($export_groups_array));
if(!is_array($export_groups_array) || count($export_groups_array)<=0)
	die("Aucun groupe de paramètres d'export fourni, arrêt de la procédure");
/*
 * Initialisation des variables de paramètres
 */
$cmbStation="";
if(isset($_POST["hidStation"]))
	$cmbStation=$_POST["hidStation"];

$hidBassinVersant="";
$hidEntiteHydro="";
$hidRegion="";
$hidDepartement="";
//echo "Demandes pour l'export : ".Tools::Display($export_groups_array);
//die();
if(isset($_POST["hidDepartement"])) $hidDepartement=$_POST["hidDepartement"];
if(isset($_POST["hidRegion"])) $hidRegion=$_POST["hidRegion"];
if(isset($_POST["hidEntiteHydro"])) $hidEntiteHydro=$_POST["hidEntiteHydro"];
if(isset($_POST["hidBassinVersant"])) $hidBassinVersant=$_POST["hidBassinVersant"];
if(isset($_POST["cmbTypeSortie"])) $cmbTypeSortie=$_POST["cmbTypeSortie"];

if(isset($_POST["cmbFormatSortie"]))
{
	$output_for_format=($_POST["cmbFormatSortie"]=="html"?true:false);
	$cmbFormatSortie=$_POST["cmbFormatSortie"];
	if($cmbFormatSortie=="carto")
		$cur_format_sortie="carto";
}
$boolOutput=$debug && $output_for_format;

$date_peche_debut="";
if(isset($_POST["txtDatePecheDebut"])) $date_peche_debut=$_POST["txtDatePecheDebut"];
$date_peche_fin="";
if(isset($_POST["txtDatePecheFin"])) $date_peche_fin=$_POST["txtDatePecheFin"];
$date_publication_debut="";
if(isset($_POST["txtDatePublicationDebut"])) $date_publication_debut=$_POST["txtDatePublicationDebut"];

$date_peche_debut=Tools::DateUserToSQL($TheDatePref,$date_peche_debut);
$date_peche_fin=Tools::DateUserToSQL($TheDatePref,$date_peche_fin);
$date_publication_debut=Tools::DateUserToSQL($TheDatePref,$date_publication_debut);
$sexe_lexique=array("M"=>"Mâle", "F"=>"Femelle", "N"=>"-");
//$donnees_tiret_defaut_lexique=array("Nombre d'individus / Echantillon","Poids de l'individu ou Total du lot (g)","Taille de l'individu ou moyenne du lot (mm)","Age de l'individu (année)","Sexe");
$donnees_tiret_defaut_lexique=array(
		"Poids de l'individu ou Total du lot (g)",
		"Taille de l'individu ou moyenne du lot (mm)",
		"Age de l'individu (année)",
		"Sexe",
		"Nombre d'individus / Echantillon",
		"Arsenic  (µg/g de poids frais)","Cadmium  (µg/g de poids frais)","Chrome  (µg/g de poids frais)","Cuivre  (µg/g de poids frais)","Mercure  (µg/g de poids frais)","Nickel  (µg/g de poids frais)","Plomb  (µg/g de poids frais)","Zinc  (µg/g de poids frais)","PBDE 28 (ng/g de matrice)","PBDE 47 (ng/g de matrice)","PBDE 99 (ng/g de matrice)","PBDE 100 (ng/g de matrice)","PBDE 153 (ng/g de matrice)","PBDE 154 (ng/g de matrice)","PBDE 183 (ng/g de matrice)","PBDE 205 (ng/g de matrice)","PBDE 209 (ng/g de matrice)","PFBS (ng/g de poids frais)","PFHxS (ng/g de poids frais)","PFHpS (ng/g de poids frais)","PFOS (ng/g de poids frais)","PFDS (ng/g de poids frais)","PFOSA (ng/g de poids frais)","PFBA (ng/g de poids frais)","PFPA (ng/g de poids frais)","PFHxA (ng/g de poids frais)","PFHpA (ng/g de poids frais)","PFOA (ng/g de poids frais)","PFNA (ng/g de poids frais)","PFDA (ng/g de poids frais)","PFUnA (ng/g de poids frais)","PFDoA (ng/g de poids frais)","PFTrDA (ng/g de poids frais)","PFTeDA (ng/g de poids frais)","Hexachlorobenzène  (pg/g de poids frais)","Hexachlorobutadiène  (pg/g de poids frais)"
		);
$donnees_tiret_defaut_selon_taille_lot=array(
		"Age de l'individu (année)",
		"Sexe");
/*
 * Démarrage de la procédure d'export
 */

if(isset($_FILES["userfile"]["tmp_name"]))
	$myXLSFileName=$_FILES["userfile"]["tmp_name"];
$error_msg="";
$display_msg="";
$myCodeImport="IMP_DONNEES_POISSONS";
if($boolOutput) echo "Ouverture du type de fichier d'import/export : ".$myCodeImport."<br>\n";
$myObj=null;
if(isset($_POST["cmdOk"]))
{
	$database->setQuery("SELECT * FROM a_import_description WHERE code_import_description='".$myCodeImport."';");
	if($database->loadObject($myObj))
	{


		/*
		 * Codes support autorisés à l'export
		 */

			$database->setQuery("SELECT * FROM rel_lot_code_support WHERE lot_nom_table='lot_de_poissons_preleves' AND rlcs_type_export='poissons' AND rlcs_exporter=1");
			$myListeCodesSupport=$database->loadObjectList();
			$myTxtListeCodesSupport="";
			if(is_array($myListeCodesSupport) && count($myListeCodesSupport))
				foreach($myListeCodesSupport as $curcodesupport)
					$myTxtListeCodesSupport.=(($myTxtListeCodesSupport!="")?",":"")."'".trim($curcodesupport->sup_code_support)."'";



		/*
		 * Préparation des requêtes des groupes d'export
		 */
		$myWhereGroupesExport="(\n";
		$myWhereGroupesExport.="( (i.export_code_groupe='' OR i.export_code_groupe='0' OR i.export_code_groupe IS NULL) AND i.export_complet_colonne=1) ";
		if(is_array($export_groups_array) && count($export_groups_array)>0)
		{
			$firstgroup=true;
			foreach($export_groups_array as $curgroup)
			{
				$myWhereGroupesExport.=" OR (i.export_code_groupe='".addslashes($curgroup["group"])."' AND i.export_".$curgroup["type"]."_colonne=1) \n";
				$firstgroup=false;
			}
		}
		$myWhereGroupesExport.=") AND ";


		if($boolOutput) echo "Le type a été ouvert. Objet : ".Tools::Display($myObj)."<br>\n";
		$myImportId=$myObj->id_a_import_description;
		if(is_null($myImportId) || intval($myImportId)<=0)
			die("Erreur : aucun ID d'import disponible");
		$myRequeteColonnes="SELECT * FROM a_import_colonne as i,rel_import_colonne_import_description as r " .
							"WHERE r.a_import_description_id_a_import_description=".$myImportId." AND " .
							$myWhereGroupesExport.
							"r.a_import_colonne_id_a_import_colonne=i.id_a_import_colonne " .
							"ORDER BY i.nbcols_import_colonne ASC;";
		$database->setQuery($myRequeteColonnes);
		//die("Requête : ".$database->getQuery());
		$myListCols=$database->loadObjectList();
		//$myColExport=($cmbTypeSortie=="complet")?"export_complet_colonne":"export_simple_colonne";
		$myPrelIndex=1;
		//echo "Colonnes définies dans cet import (".count($myListCols)."):".Tools::Display($myListCols)."<br>\n" ;
		if(is_array($myListCols) && count($myListCols)>0)
		{
			if($boolOutput) echo "Préparation de l'export<br>\n";
			$myColOrdered=array();
			$myArrayFieldsList=array();
			foreach($myListCols as $key=>$curcol)
			{
				//if(isset($curcol->$myColExport) && $curcol->$myColExport==1)
				{
					$myColOrdered[$curcol->cd_import_colonne]=$curcol;
					if($curcol->parametre_import_colonne!="ana_resultat")
						$myArrayFieldsList[$curcol->type_import_colonne][]=$curcol->parametre_import_colonne;
				}
			}
			//die(Tools::Display($myColOrdered));
			//if($boolOutput) echo "Export des colonnes : ".Tools::Display($myColOrdered);
			$myWhereBV="";
			if($hidBassinVersant!="")
				$myWhereBV.=" AND hyd.rh_code='".$hidBassinVersant."' ";


			$myRequeteExport="SELECT *,IF(tax.tax_nom_commun_taxon='',tax.tax_nom_latin_taxon,tax.tax_nom_commun_taxon) as tax_nom_commun_taxon  FROM echantillon as ech " .
					"LEFT JOIN lot_de_poissons_preleves as lpp ON ech.id_lot=lpp.id_lot_de_poissons_preleves " .
					"LEFT JOIN prelevement_elementaire_biologique as peb ON peb.id_prelevement_elementaire_biologique=lpp.peb_id_prelevement_elementaire_biologique " .
					"LEFT JOIN operation_prelevement_biologique as opb ON opb.id_operation_prelevement_biologique=peb.opb_id_operation_prelevement_biologique " .
					"LEFT JOIN preleveurs as prl ON prl.id_preleveurs=peb.prl_id_preleveurs " .
					"LEFT JOIN zones_de_peche as zdp ON zdp.peb_id_prelevement_elementaire_biologique=peb.id_prelevement_elementaire_biologique " .
					"LEFT JOIN point_de_prelevement as ppr ON ppr.id_point_de_prelevement=opb.ppr_id_point_de_prelevement " .
					"LEFT JOIN station_de_mesure as sta ON sta.id_station_de_mesure=ppr.sta_id_station_de_mesure " .
					//"LEFT JOIN station_de_mesure as sta ON sta.sta_code_station=ppr.sta_code_station " .
					"LEFT JOIN commune as com_sit ON com_sit.com_numero=sta.sta_com_code_situation " .
					"LEFT JOIN commune as com_lim ON com_lim.com_numero=sta.sta_com_code_limitrophe " .
					"LEFT JOIN departement as dep ON com_sit.departement_num_departement=dep.num_departement " .
					"LEFT JOIN region as reg ON reg.num_region=dep.region_num_region " .
					"RIGHT JOIN entite_hydrographique as hyd ON (hyd.cea_code_entite=sta.cea_code_entite " .$myWhereBV.")".
					"LEFT JOIN region_hydrographique as rhy ON (rhy.rh_ns_include_exports=1 AND rhy.rh_code=hyd.rh_code)".
					"LEFT JOIN taxon as tax ON tax.tax_code_taxon=lpp.tax_code_taxon " .
					"LEFT JOIN intervenant as ive ON (ive.int_code_intervenant=ech.int_code_intervenant_gestionnaire) " .
					"LEFT JOIN analyse as ana ON ana.ech_id_echantillon=ech.id_echantillon " .
					"";
			
			


			if($hidRegion=="" && $hidDepartement=="")
				$myWhere="";
			elseif($hidRegion!="" && $hidDepartement=="")
				$myWhere=" AND reg.num_region='".$hidRegion."' ";
			else
				$myWhere=" AND dep.num_departement='".$hidDepartement."' ";
				
			if(trim($cmbStation)!="")
				$myWhere.=" AND sta.sta_code_station='".trim(addslashes($cmbStation))."' ";
				
			if($hidEntiteHydro!="")
				$myWhere.=" AND hyd.cea_code_entite='".$hidEntiteHydro."' ";

			if($date_peche_debut!="")
				$myWhere.=" AND opb.opb_date_debut_prelevement>='".$date_peche_debut."' ";
			if($date_peche_fin!="")
				$myWhere.=" AND opb.opb_date_fin_prelevement<='".$date_peche_fin."' ";
			if($date_publication_debut!="")
				$myWhere.=" AND ech.ech_ns_date_saisie>='".$date_publication_debut."' ";
			/*
			if(isset($_POST["chkValeursValides"]) && $_POST["chkValeursValides"]=="1")
				$myRequeteExport.=" WHERE ech_ns_lot_valide='O' AND ech_ns_resultats_valides='O' AND ech_ns_autorisation_diffusion='O' ";
			elseif($myWhere!="")
				$myRequeteExport.=" WHERE ech.id_lot=ech.id_lot	 ";
			*/

			/*
			if(isset($_POST["chkValeursValides"]) && $_POST["chkValeursValides"]=="1")
				$myRequeteExport.=" WHERE ech.sup_code_support IS NULL AND ech_ns_lot_valide='O' AND ech_ns_resultats_valides='O' AND ech_ns_autorisation_diffusion='O' ";
			elseif($myWhere!="")
				$myRequeteExport.=" WHERE ech.sup_code_support IS NULL	 ";
			/*/
			if(isset($_POST["chkValeursValides"]) && $_POST["chkValeursValides"]=="1")
				$myRequeteExport.=" WHERE ech.sup_code_support IN (".$myTxtListeCodesSupport.") AND ech_ns_lot_valide='O' AND ech_ns_resultats_valides='O' AND ech_ns_autorisation_diffusion='O' ";
			elseif($myWhere!="")
				$myRequeteExport.=" WHERE ech.sup_code_support IN (".$myTxtListeCodesSupport.") ";
			//*/
			$myRequeteExport.=" ".$myWhere." GROUP BY id_echantillon";
			$database->setQuery($myRequeteExport);
			//$boolOutput=true;
			if($boolOutput || $cmbFormatSortie=="html") echo( "<div style='display:none;'>Requête : ".$database->getQuery()."</div>");
			//die("Requête : ".$database->getQuery());
			$myListResultats=$database->loadObjectList();
			if($boolOutput) echo "Nombre d'échantillons trouvés : ".count($myListResultats)."<br />\n";
			$myArrayTable=array();
			$row=0;
			
			/*
			 * 
			 * Préparation des données de sortie
			 * 
			 */
			
			/*
			 * Filtre si résultat ou pas !
			 */
			
			if(is_array($myListResultats) && count($myListResultats)>0)
			{
				
				/*
				 * Initialisation de la première ligne
				 */
				if($boolOutput) echo "Initialisation de la ligne d'entête".BR;
				foreach($myColOrdered as $key=>$curcol)
				{
					if(trim($curcol->export_libelle_colonne)=="")
					{
						$myArrayTable[$row][$curcol->cd_import_colonne]=$curcol->libelle_import_colonne;
						//$myArrayTable[$row][$curcol->libelle_import_colonne]=$curcol->libelle_import_colonne;
					}
					else
					{
						$myArrayTable[$row][$curcol->cd_import_colonne]=stripslashes($curcol->export_libelle_colonne);
						//$myArrayTable[$row][$curcol->libelle_import_colonne]=$curcol->export_libelle_colonne;
					}
				}
				//die("Ligne d'entête : ".Tools::Display($myArrayTable)."");
				/*
				 * Création des lignes de données
				 */
			
				
				if($boolOutput) echo "Groupe d'export :".Tools::Display($myWhereGroupesExport);

				if($boolOutput) echo "Démarrage de la grande boucle".BR;
				foreach($myListResultats as $curresultat)
				{
					$row++;
					//$boolOutput=false;
					//if($boolOutput) echo "Recherche du champ dans ".Tools::Display($curresultat);
					
					$database->setQuery("SELECT a.*,i.cd_import_colonne,wgp.cd_groupe_parametre " .
										" FROM " .
										"	analyse as a," .
										"	#__rel_import_colonne_parametre as r," .
										"	#__a_import_colonne as i " .
										"LEFT JOIN w_groupe_parametre as wgp ON wgp.cd_groupe_parametre=i.export_code_groupe  " .
										" WHERE " .
											$myWhereGroupesExport .
											"a.par_id_parametre=r.parametre_id_parametre AND " .
											"r.a_import_colonne_id_a_import_colonne=i.id_a_import_colonne AND " .
											"i.cd_import_colonne LIKE '".$myCodeImport."%' AND " .
											"ech_id_echantillon=".$curresultat->id_echantillon);
					//die("Requête : ".$database->getQuery());
					$myListValeurs=$database->loadObjectList();
					/*
					 * On n'ajoute la ligne que si celle-ci n'est pas vide : il faut des enregistrements
					 */
					if(is_array($myListValeurs) && count($myListValeurs)>0)
					{
						if($boolOutput) echo "Nombre de valeurs trouvées : ".count($myListValeurs).BR;
						/*
						 * Début de création du tableau global qui contiendra les données
						 */

						/*
						 * Réindexaction des résultats pour aller plus vite après
						 */
						if($boolOutput) echo "Réindexation de la table ...".BR;
						$myTableReindexationValeurs=array();
						foreach($myListValeurs as $keyvaleur=>$curvaleurs)
						{
							$myTableReindexationValeurs[$curvaleurs->cd_import_colonne]=&$myListValeurs[$keyvaleur];
						}
						//die("Réindexation :".Tools::Display($myTableReindexationValeurs));
						/*
						 * Test si les résultats de la ligne en cours sont vides ou inexistants
						 */
						if($boolOutput) echo "Test de la ligne".BR;
						$myLineEmpty=true;
						foreach($myListValeurs as $keyvaleur=>$curvaleurs)
						{
							if(isset($myTableReindexationValeurs[$curvaleurs->cd_import_colonne]->cd_groupe_parametre))
								$myCodeGroupe=$myTableReindexationValeurs[$curvaleurs->cd_import_colonne]->cd_groupe_parametre;
							else
								$myCodeGroupe="";
							if(isset($myTableReindexationValeurs[$curvaleurs->cd_import_colonne]->ana_resultat))
								$myCurValeur=trim($myTableReindexationValeurs[$curvaleurs->cd_import_colonne]->ana_resultat);
							else
								$myCurValeur="";
							if($boolOutput) echo "Pour la colonne ".$myField.", groupe : ".$myCodeGroupe.", Valeur : ".$myCurValeur.BR;
							
							if($myCodeGroupe!="" && $myCurValeur!="" && !is_null($myCurValeur))
								$myLineEmpty=false;
						}
						
						
						/*
						 * Si pas de données on passe à la ligne suivante
						 */
						if($boolOutput) echo "La ligne est ".(($myLineEmpty)?"vide":"occupée").BR;
						if($myLineEmpty)
							continue;
						
						/*
						 * Des données, on les sauve dans le tableau
						 */
						 
						/*
						 * D'abord le tableau de colonnes standard, hors paramètres 
						 */
						foreach($myColOrdered as $key=>$curcol)
						{
							$myArrayTable[$row][$curcol->cd_import_colonne]="";
							//$myArrayTable[$row][$curcol->libelle_import_colonne]="";
						}
						
						foreach($myColOrdered as $key=>$curcol)
						{
							$myField=$curcol->parametre_import_colonne;
							if($myField!="ana_resultat")
							{
								if($boolOutput) echo "Champ recherché : ".$myField."<br />\n";
								if(isset($curresultat->$myField))
								{
									if($boolOutput) echo "Trouvé, affectation de la valeur à la colonne : "	.$curcol->cd_import_colonne." = ".$curresultat->$myField."<br />\n";			
									$myCurValeur=$curresultat->$myField;
									switch($myColOrdered[$curcol->cd_import_colonne]->export_format_valeur)
									{
										case "jj/mm/aaaa":
											list($year,$month,$day)=explode("-",$myCurValeur);
											$myDateTime=mktime(1,1,1,$month,$day,$year);
											$myCurValeur=date("d/m/Y",$myDateTime);
											break;
										case "jj/mm/aa":
											list($year,$month,$day)=explode("-",$myCurValeur);
											$myDateTime=mktime(1,1,1,$month,$day,$year);
											$myCurValeur=date("d/m/y",$myDateTime);
											break;
									}
									$myArrayTable[$row][$curcol->cd_import_colonne]=$myCurValeur;
									//$myArrayTable[$row][$curcol->libelle_import_colonne]=$curresultat->$myField;
								}
								else
								{
									$myArrayTable[$row][$curcol->cd_import_colonne]="";
									//$myArrayTable[$row][$curcol->libelle_import_colonne]="";
								}
							
							/*
							 * EXCEPTIONS DE GESTION DE DONNEES - DEBUT
							 */
								if($cur_format_sortie=="carto")
								{
									$myExportFiltre=array("Poids de l'individu ou Total du lot (g)","Nombre d'individus / Echantillon","Nombre d'individu / Echantillon","Age de l'individu (année)","Sexe");
									$myCurCode=stripslashes(trim($myColOrdered[$curcol->cd_import_colonne]->export_libelle_colonne));
									$myCurValeur=$myArrayTable[$row][$curcol->cd_import_colonne];

									if(in_array($myCurCode,$myExportFiltre))
										if(is_null($myCurValeur) || intval($myCurValeur)<=0 || trim($myCurValeur)=="")
											$myArrayTable[$row][$curcol->cd_import_colonne]="Inconnu";

									if($myCurCode=="Taille de l'individu ou moyenne du lot (mm)")
										if(is_null($myCurValeur) || intval($myCurValeur)<=0 || trim($myCurValeur)=="")
											$myArrayTable[$row][$curcol->cd_import_colonne]="Inconnue";

								}
								else
								{
									if($curcol->cd_import_colonne=="IMP_DONNEES_POISSONS_45" || stripslashes(trim($myColOrdered[$curcol->cd_import_colonne]->libelle_import_colonne))=="Nombre d'individu / Echantillon" || stripslashes(trim($myColOrdered[$curcol->cd_import_colonne]->export_libelle_colonne))=="Nombre d'individus / Echantillon")
										if(is_null($myCurValeur) || intval($myCurValeur)<=0)
											$myArrayTable[$row][$curcol->cd_import_colonne]="";
											
									if(stripslashes(trim($myColOrdered[$curcol->cd_import_colonne]->export_libelle_colonne))=="Sexe")
									{
										if(isset($sexe_lexique[$myCurValeur]))
											$myArrayTable[$row][$curcol->cd_import_colonne]=$sexe_lexique[$myCurValeur];
									}
									//Poids de l'individu ou Total du lot (g)	Taille de l'individu ou moyenne du lot (mm)	Age de l'individu (année)	Sexe
									$myCurColName=stripslashes(trim($myColOrdered[$curcol->cd_import_colonne]->export_libelle_colonne));
									if(in_array($myCurColName,$donnees_tiret_defaut_lexique))
									{
										//echo "Colonne : ".$myCurColName." => ".$myCurValeur.BR;
										if(is_null($myCurValeur) || trim($myCurValeur)=="0" || trim($myCurValeur)=="")
										{
											$myCurValeur="-";
											$myArrayTable[$row][$curcol->cd_import_colonne]="-";
										}
										if($myCurColName=="Sexe" && $myCurValeur=="R")
										{
											$myCurValeur="-";
											$myArrayTable[$row][$curcol->cd_import_colonne]="-";
										}
									}

								}
								/*
								 * EXCEPTIONS DE GESTION DE DONNEES - FIN
								 */
								
							}
						}
						
						/*
						 * Puis le tableau des paramètres 
						 */
						
						foreach($myListValeurs as $keyvaleur=>$curvaleurs)
						{
							if(isset($myTableReindexationValeurs[$curvaleurs->cd_import_colonne]->ana_resultat))
							{
								$myCurValeur=trim($myTableReindexationValeurs[$curvaleurs->cd_import_colonne]->ana_resultat);
								/*
								 * Gestion du marqueur inférieur "<" en cas de limite de quantification
								 */
								if($curvaleurs->ana_code_remarque=="7" || $curvaleurs->ana_code_remarque=="10" || $myCurValeur<=$curvaleurs->ana_limite_quantification)
								{
									if(substr($myCurValeur,0,1)=="." || substr($myCurValeur,0,1)==",")
										$myCurValeur="0".strval($myCurValeur);
									$myCurValeur="<".strval($myCurValeur);
								}
								
							}
							else
								$myCurValeur="";
							//$myLibCurCol=$myColOrdered[$curvaleurs->cd_import_colonne]->libelle_import_colonne;
							/*
							 * Formatage général de la valeur selon le type de la colonne
							 */
							//echo ($curvaleurs->cd_import_colonne." ==> ".$myColOrdered[$curvaleurs->cd_import_colonne]->export_format_valeur).BR;
							if(isset($myColOrdered[$curvaleurs->cd_import_colonne]))
								switch($myColOrdered[$curvaleurs->cd_import_colonne]->export_format_valeur)
								{
									case "jj/mm/aaaa":
										list($year,$month,$day)=explode("-",$myCurValeur);
										$myDateTime=mktime(1,1,1,$month,$day,$year);
										$myCurValeur=date("d/m/Y",$myDateTime);
										break;
									case "jj/mm/aa":
										list($year,$month,$day)=explode("-",$myCurValeur);
										$myDateTime=mktime(1,1,1,$month,$day,$year);
										$myCurValeur=date("d/m/y",$myDateTime);
										break;
								}

								/*
								 * EXCEPTIONS DE GESTION DE DONNEES - DEBUT
								 */
								if($cur_format_sortie=="carto")
								{
//									TOTAL-TEQ (PCDD/F + PCB DL) (pg/g de poids frais)

									$myCurCode=stripslashes(trim($myColOrdered[$curvaleurs->cd_import_colonne]->export_libelle_colonne));
									if($myCurCode=="Total-TEQ inférieur ou supérieur au seuil de conformité")
									{
										if($myCurValeur=="Inférieur")
											$myCurValeur="Inférieur au seuil de conformité";
										if($myCurValeur=="Supérieur")
											$myCurValeur="Supérieur au seuil de conformité";
									}
								}
								else
								{
									$myCurColName=stripslashes(trim($myColOrdered[$curvaleurs->cd_import_colonne]->export_libelle_colonne));
									//echo "Nom de la colonne : ".$myCurColName.BR;
									//Poids de l'individu ou Total du lot (g)	Taille de l'individu ou moyenne du lot (mm)	Age de l'individu (année)	Sexe
									if(in_array($myCurColName,$donnees_tiret_defaut_lexique))
									{
										if(trim($myCurValeur)=="")
										{
											echo "Valeur mise à -";
											$myCurValeur="-";
										}
									}									
								}
								/*
								 * EXCEPTIONS DE GESTION DE DONNEES - FIN
								 */
							
							
							$myArrayTable[$row][$curvaleurs->cd_import_colonne]=str_replace(",",".",$myCurValeur);
						}
						//die("Ligne de données ".Tools::Display($myArrayTable[$row]));

						/*
						foreach($myListValeurs as $curvaleurs)
						{
							foreach($myColOrdered as $key=>$curcol)
							{
								$myField=$curcol->parametre_import_colonne;
								if($curcol->cd_import_colonne==$curvaleurs->cd_import_colonne)
								{
									$myField=$curcol->parametre_import_colonne;
									if(isset($curvaleurs->$myField))
										$myArrayTable[$row][$curcol->libelle_import_colonne]=str_replace(",",".",$curvaleurs->$myField);
									else
										$myArrayTable[$row][$curcol->libelle_import_colonne]="";
									//if($curvaleurs->ech_id_echantillon==42 && ($curcol->export_libelle_colonne=="TOTAL-TEQ (PCDD/F + PCB DL) / g de poids frai" || $curcol->export_libelle_colonne=="% MG"))
										//echo "fld ".$myField."/".$curcol->cd_import_colonne." => ".$curcol->export_libelle_colonne." => ".$curvaleurs->$myField." => ".$myArrayTable[$row][$curcol->libelle_import_colonne]."<br />\n";
								}
							}
						}
						*/
						
					}
				}
				
				/*
				 * Vérification de la taille du tableau final après nettoyage des lignes vides
				 */
				//die("Lignes : ".count($myArrayTable));
				if(count($myArrayTable)>1)
				{
	
					/*
					 * 
					 * Vérification du tableau de données, remplissage de cases manquantes le cas échéant
					 * 
					 */
					
					$myColIndexTailleLot=-1;
					foreach($myArrayTable[0] as $keyval=>$curvalue)
					{
						if($curvalue=="Nombre d'individus / Echantillon")
							$myColIndexTailleLot=$keyval;
					}
					
					foreach($myArrayTable[0] as $keyval=>$curvalue)
					{
						if(in_array($curvalue,$donnees_tiret_defaut_lexique))
						{
							for($i=1;$i<count($myArrayTable);$i++)
							{
								if(!isset($myArrayTable[$i][$keyval]) || trim($myArrayTable[$i][$keyval])=="")
								{
									$myArrayTable[$i][$keyval]="-";
								}
							}
						}
						
						/*
						 * Post-traitement des colonnes du tableau selon le nombre d'individus dans  le lot
						 * Si 1 individu : on affiche sexe et âge
						 * Sinon on affiche "-" à la place
						 */
						
						if(intval($myColIndexTailleLot)>=0)
							if(in_array($curvalue,$donnees_tiret_defaut_selon_taille_lot))								
								for($i=1;$i<count($myArrayTable);$i++)
								{
									if(intval($myArrayTable[$i][$myColIndexTailleLot])>1 || $myArrayTable[$i][$myColIndexTailleLot]==="-")
										$myArrayTable[$i][$keyval]="-";
								}
						
					}
	
					/*
					 * 
					 * Génération de la sortie en elle-même
					 * 
					 */
					
					if($boolOutput) echo "Nb lignes : ".($myLineStop-2)." sur ".(count($myArrayTable)-1)."<br />\n";
					
					if($cmbFormatSortie=="csv")
					{
						Tools::SendCSV("donnees-poisson.csv",$myArrayTable,true,$mySeparateurCSV);
					}
					elseif($cmbFormatSortie=="html")
					{
						$myLineStop=-1;
						$myCurLine=0;
						echo "Rappel des options d'export : <br />\n";
						echo "Date début pêche : ".$date_peche_debut." (".(isset($_POST["txtDatePecheDebut"])?$_POST["txtDatePecheDebut"]:"").")<br />\n";
						echo "Date fin pêche : ".$date_peche_fin." (".((isset($_POST["txtDatePecheFin"])?$_POST["txtDatePecheFin"]:"")).")<br />\n";
						echo "Date début publication : ".$date_publication_debut." (".$_POST["txtDatePublicationDebut"].")<br />\n";
						echo "Groupes de publication : ".Tools::Display($export_groups_array).BR;
						echo "Filtre sur la station : ".$cmbStation." (".$_POST["hidStation"].")".BR;
						
						echo "<table border=1 cellspacing=0 cellpadding=3>\n\t<thead><tr>\n";
						echo "\t\t<td>#</td>\n";
						foreach($myArrayTable[0] as $keyval=>$curvalue)
							echo "\t\t<td>".(($curvalue=="")?"&nbsp;":htmlentities($curvalue))."</td>\n";
						echo "</tr></thead>\n";
						
						$first=true;
						foreach($myArrayTable as $keyarray=>$curarray)
						{
							if($first) { $first=false; continue; } 
							echo "\t<tr>\n";
							echo "\t\t<td>".$keyarray."</td>\n";
							//foreach($curarray as $keyval=>$curvalue)
							foreach($myArrayTable[0] as $keyval=>$headvalue)
							{
								if(!isset($curarray[$keyval])) $curarray[$keyval]="";							
								echo "\t\t<td>".((trim($curarray[$keyval])=="")?"&nbsp;":htmlentities($curarray[$keyval]))."</td>\n";
							}
							echo "\t</tr>\n";
							$myCurLine++;
							if($myCurLine>$myLineStop && $myLineStop>0)
							{
								//echo "Fin !";
								break;
							}
						}
						echo "</table>\n";				
					}
					elseif($cmbFormatSortie=="xls" || $cmbFormatSortie=="carto")
					{
						//die( "Format : ".$cmbFormatSortie."/".$cur_format_sortie."<br />\n");
						require_once 'Writer.php';
						$tmpfname = @tempnam("","");
						if($tmpfname=="")
						{
							$error_msg.="Erreur dans le nom du fichier<br />\n";
						}
						else
						{
							$workbook = new Spreadsheet_Excel_Writer($tmpfname);
							$workbook->setVersion(8);
							$worksheet =& $workbook->addWorksheet('Titre');
							$cell_format="entete";
							$formats_array[$cell_format] =& $workbook->addFormat();
							$formats_array[$cell_format]->setFontFamily('Arial');
							//$format_Arial->setNumFormat("text");
							$formats_array[$cell_format]->setBold();
							$formats_array[$cell_format]->setSize(10);
							$formats_array[$cell_format]->setNumFormat('@');
		
							$i=0;
							foreach($myArrayTable as $keyarray=>$curarray)
							{
								$j=0;
								foreach($myArrayTable[0] as $keyval=>$headvalue)
								{
									$cell_format="@";
									//echo("Sortie excel avec format : col[".$keyval."]= ".$myColOrdered[$keyval]->export_format_valeur).BR;
									if(!isset($curarray[$keyval])) $curarray[$keyval]="";
									if(isset($myColOrdered[$keyval]->export_format_valeur))
										$cell_format=$myColOrdered[$keyval]->export_format_valeur;
									$cell_format=((trim($cell_format)=="jj/mm/aaaa")?"@":$cell_format);
									if(!isset($formats_array[$cell_format]))
									{
										$formats_array[$cell_format] =& $workbook->addFormat();
										$formats_array[$cell_format]->setFontFamily('Arial');
										$formats_array[$cell_format]->setSize(10);
										$formats_array[$cell_format]->setNumFormat($cell_format);
									}
									
									if($i==0)
										$cell_format="entete";
									$worksheet->write($i, $j,strval($curarray[$keyval]),$formats_array[$cell_format]);
									$j++;
								}
								//die();
	/*
								foreach($curarray as $keyval=>$curval)
								{
									$worksheet->write($i, $j,($curval),$format_Arial);
									$j++;
								}
	*/
								$i++;
							}
		
							$workbook->close();
							
							/*
							 * Récupération des données et envoi en streaming
							 */
							if(!headers_sent())
							{
								Tools::DL_DownloadProgressive("donnees-poissons-export.xls",$tmpfname);
								unlink($tmpfname);
								die();
							}
							else
							{
								$error_msg.="Entêtes déjà envoyés ! Des erreurs dans le script ? Impossible d'envoyer le téléchargement<br />\n";
								unlink($tmpfname);
							}
						}
					}
					echo "<hr />\n";
				}
				else
				{
					$error_msg.="Aucun résultat (auncune ligne)<div style='display:none;'>".$database->getQuery()."</div><br />\n";
				}

			}
			else
			{
				$error_msg.="Aucun résultat (retour incorrect)<div style='display:none;'>".$database->getQuery()."</div><br />\n";
			}	
		}
	}
}
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
$myRefFileAttr=pathinfo($_SERVER["HTTP_REFERER"]);
$myCurFileAttr=pathinfo(__FILE__);
if($myRefFileAttr["basename"]==$myCurFileAttr["basename"])
{
?>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label><input type="checkbox" name="chkValeursValides" value="1">&nbsp;Exporter les valeurs validées uniquement</label></br />
		<label>Format de sortie&nbsp;:&nbsp;<select name="cmbFormatSortie"><option value="html" selected>HTML</option><option value="csv">CSV</option><option value="xls">Excel</option></select></label><br />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>
	<a href="index.php">Retour</a>
<?php	
}
else
{
	?>
	<a href="<?php echo $_SERVER["HTTP_REFERER"]; ?>">Retour</a>
	<?php
}
?>