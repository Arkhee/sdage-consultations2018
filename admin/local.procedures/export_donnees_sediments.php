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
define("URL_SEPARATOR","ABCDURLABCDURL");
define("NB_RESULTS_PER_QUERY",1000);
define("NB_PAGES_TOTAL_MAX",0);
$myClsParam=new clsParametre($database);
$myXLSFileName="";
$debug=false;
$output_for_format=true;
$mySeparateurCSV=",";

$cmbTypeSortie="complet";
$cur_format_sortie="";
$cmbFormatSortie="";
if($cmbFormatSortie=="carto")
	$cur_format_sortie="carto";
$cmbFormatSortie="xls";

$cmbStation="";
if(isset($_POST["hidStation"]))
	$cmbStation=$_POST["hidStation"];
//die("Groupes : ".Tools::Display($export_groups_array));
if(!is_array($export_groups_array) || count($export_groups_array)<=0)
	die("Aucun groupe de paramètres d'export fourni, arrêt de la procédure");
/*
 * Initialisation des variables de paramètres
 */

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

/*
 * Démarrage de la procédure d'export
 */

if(isset($_FILES["userfile"]["tmp_name"]))
	$myXLSFileName=$_FILES["userfile"]["tmp_name"];
$error_msg="";
$display_msg="";
$myCodeImport="IMP_DONNEES_SEDIMENTS"; //"IMP_DONNEES_POISSONS";
if($boolOutput) echo "Ouverture du type de fichier d'import/export : ".$myCodeImport."<br>\n";
$myObj=null;
if(isset($_POST["cmdOk"]))
{
	$database->setQuery("SELECT * FROM a_import_description WHERE code_import_description='".$myCodeImport."';");
	if($database->loadObject($myObj))
	{

		/**
		 * Préparation des filtres
		 */

		/*
		 * Codes support autorisés à l'export
		 */

			$database->setQuery("SELECT * FROM rel_lot_code_support WHERE lot_nom_table='prelevement_echantillon_physico_chimique' AND rlcs_type_export='sediments' AND rlcs_exporter=1");
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
				if(strtolower($curgroup["group"])!="pcb")
					$myWhereGroupesExport.=" OR (i.export_code_groupe='".addslashes($curgroup["group"])."' AND i.export_".$curgroup["type"]."_colonne=1) \n";
				else
					$myWhereGroupesExport.=" OR (i.export_code_groupe='".addslashes($curgroup["group"])."') \n";
				$firstgroup=false;
			}
		}
		$myWhereGroupesExport.=") AND ";


		if($boolOutput) echo "Le type a été ouvert. Objet : ".Tools::Display($myObj)."<br>\n";
		$myImportId=$myObj->id_a_import_description;
		if(is_null($myImportId) || intval($myImportId)<=0)
			die("Erreur : aucun ID d'import disponible");
		$myRequeteColonnes="SELECT i.*,r.*,par.* FROM (a_import_colonne as i,rel_import_colonne_import_description as r) " .
							" LEFT JOIN rel_import_colonne_parametre as rcp ON rcp.a_import_colonne_id_a_import_colonne=i.id_a_import_colonne ".
							" LEFT JOIN parametre as par ON rcp.parametre_id_parametre=par.id_parametre ".
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
			$myCodeColonneStation="";
			foreach($myListCols as $curcol)
			{
				//if($cmbFormatSortie=="html") echo "Test de ".Tools::Display($curcol)." ...".BR;
				if($curcol->type_import_colonne=="operation_prelevement_physicochimique_microbio" && $curcol->parametre_import_colonne=="sta_code_station")
				{
					//echo "Code trouvé : ".Tools::Display($curcol);
					$myCodeColonneStation=$curcol->cd_import_colonne;
					break;					
				}
			}
			if($cmbFormatSortie=="html") echo "Recherche colonne station ... ".$myCodeColonneStation.BR;
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


			$myRequeteExport="FROM echantillon as ech " .
					"LEFT JOIN prelevement_echantillon_physico_chimique as pepc 
						ON pepc.id_prelevement_echantillon_physico_chimique=ech.id_lot " .
					"LEFT JOIN operation_prelevement_physicochimique_microbio as oppc
						ON oppc.id_operation_prelevement_physicochimique_microbio=pepc.oppc_id_operation_prelevement_physicochimique_microbio " .
					"LEFT JOIN point_de_prelevement as ppr ON ppr.id_point_de_prelevement=oppc.ppr_id_point_de_prelevement " .
					//"LEFT JOIN station_de_mesure as sta ON sta.sta_code_station=oppc.sta_code_station " .
					"LEFT JOIN station_de_mesure as sta ON sta.id_station_de_mesure=oppc.sta_id_station_de_mesure " .
					"LEFT JOIN commune as com_sit ON com_sit.com_numero=sta.sta_com_code_situation " .
					"LEFT JOIN commune as com_lim ON com_lim.com_numero=sta.sta_com_code_limitrophe " .
					"LEFT JOIN departement as dep ON com_sit.departement_num_departement=dep.num_departement " .
					"LEFT JOIN region as reg ON reg.num_region=dep.region_num_region " .
					"RIGHT JOIN entite_hydrographique as hyd ON (hyd.cea_code_entite=sta.cea_code_entite " .$myWhereBV." ) ".
					"LEFT JOIN region_hydrographique as rhy ON (rhy.rh_ns_include_exports=1 AND rhy.rh_code=hyd.rh_code)".
					"LEFT JOIN intervenant as ive ON (ive.int_code_intervenant=oppc.oppc_int_code_intervenant_producteur) " .
					//"LEFT JOIN analyse as ana ON ana.ech_id_echantillon=ech.id_echantillon " ;
			$myWhere="";
			/*
			if($hidRegion=="" && $hidDepartement=="")
				$myWhere="";
			elseif($hidRegion!="" && $hidDepartement=="")
				$myWhere=" AND reg.num_region='".$hidRegion."' ";
			else
				$myWhere=" AND dep.num_departement='".$hidDepartement."' ";
			*/
			if($cmbStation!="")
				$myWhere.=" AND sta.sta_code_station='".$cmbStation."' ";
			
			if($hidEntiteHydro!="")
				$myWhere.=" AND hyd.cea_code_entite='".$hidEntiteHydro."' ";

			if($date_peche_debut!="")
				$myWhere.=" AND oppc.oppc_date_debut>='".$date_peche_debut."' ";
			if($date_peche_fin!="")
				$myWhere.=" AND oppc.oppc_date_debut<='".$date_peche_fin."' ";
			if($date_publication_debut!="")
				$myWhere.=" AND ech.ech_ns_date_saisie>='".$date_publication_debut."' ";
				
			$mySep=" AND ";
			if(isset($_POST["chkValeursValides"]) && $_POST["chkValeursValides"]=="1")
				$myRequeteExport.=" WHERE ech_ns_lot_valide='O' AND ech_ns_resultats_valides='O' AND ech_ns_autorisation_diffusion='O' ";
			elseif($myWhere!="")
				$myRequeteExport.=" WHERE ech.id_lot=ech.id_lot	 ";
			else
				$mySep=" WHERE ";
			$myRequeteExport.=" ".$myWhere." ".$mySep." pepc.pepc_sup_code_support=ech.sup_code_support AND ech.sup_code_support IN (".$myTxtListeCodesSupport.") ";
			
			
			$myRequeteExport_Count="SELECT COUNT(*) as nbocc ".$myRequeteExport;
			$myRequeteExport.=" GROUP BY id_echantillon"." ORDER BY pepc.pepc_date_debut_prelevement DESC ";
			$myRequeteExport="SELECT * ".$myRequeteExport;
			
			$database->setQuery($myRequeteExport_Count);
			//$boolOutput=true;
			if($boolOutput ) echo( "Requête : ".$database->getQuery());
			
			$myCountResultats=$database->loadObjectList();
			if($boolOutput) echo "Nombre d'échantillons trouvés : ".$myCountResultats[0]->nbocc."<br />\n";
			$myArrayTable=array();
			$row=0;
			$myNbTotalResultats=0;
			/*
			 * 
			 * Préparation des données de sortie
			 * 
			 */
			
			/*
			 * Filtre si résultat ou pas !
			 */
			if(is_array($myCountResultats) && ($myCountResultats[0]->nbocc)>0)
			{
				$myNbPages=ceil($myCountResultats[0]->nbocc/NB_RESULTS_PER_QUERY);
				if($myNbPages<=0) $myNbPages=1;
				if(NB_PAGES_TOTAL_MAX>0)
					$myNbPages=NB_PAGES_TOTAL_MAX;
				for($curpage=0;$curpage<$myNbPages;$curpage++)
				{
					$myCurRequeteExport=$myRequeteExport." LIMIT ".($curpage*NB_RESULTS_PER_QUERY).",".NB_RESULTS_PER_QUERY;
					$database->setQuery($myCurRequeteExport);
					if($boolOutput) echo "Requête export courante : ".$database->getQuery();
					//die("Requête export courante : ".$database->getQuery());
					$myListResultats=$database->loadObjectList();
					if(!is_array($myListResultats) || count($myListResultats)<=0)
					{
						//echo "Interruption pour absence de résultats : ".count($myListResultats).BR;						
						break;
					}
					$myNbTotalResultats+=count($myListResultats);
					if($curpage==0)
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
								if($curcol->parametre_import_colonne=="ana_resultat" && $curcol->id_parametre>0)
								{
									//SELECT udm.udm_libelle, par.*, COUNT(*) as nbocc FROM unites_de_mesure as udm,echantillon as ech, analyse as ana, parametre as par WHERE ana.par_id_parametre=par.id_parametre AND par.par_code_parametre='5433' AND ech.id_echantillon=ana.ech_id_echantillon AND ech.sup_code_support IN ('6') AND udm.udm_code_unite=ana.ana_unite_mesure GROUP BY udm.id_unites_de_mesure ORDER BY nbocc DESC LIMIT 0,1
									$myQueryUnite="SELECT udm.udm_libelle, COUNT(*) as nbocc FROM unites_de_mesure as udm,echantillon as ech, analyse as ana WHERE ana.par_id_parametre=".intval($curcol->id_parametre)." AND ech.id_echantillon=ana.ech_id_echantillon AND ech.sup_code_support IN (".$myTxtListeCodesSupport.") AND udm.udm_code_unite=ana.ana_unite_mesure  GROUP BY udm.id_unites_de_mesure ORDER BY nbocc DESC LIMIT 0,1;";
									$database->setQuery($myQueryUnite);
									//die($database->getQuery());
									$myListeUnites=$database->loadObjectList();
									$myLblUnite="";
									if(is_array($myListeUnites) && count($myListeUnites)>0 && isset($myListeUnites[0]->udm_libelle) && trim($myListeUnites[0]->udm_libelle)!="")
										$myLblUnite="(".utf8_decode($myListeUnites[0]->udm_libelle).")";
									$myArrayTable[$row][$curcol->cd_import_colonne]=stripslashes($curcol->export_libelle_colonne).$myLblUnite;
								}
								else
									$myArrayTable[$row][$curcol->cd_import_colonne]=stripslashes($curcol->export_libelle_colonne);
								//$myArrayTable[$row][$curcol->libelle_import_colonne]=$curcol->export_libelle_colonne;
							}
							//echo Tools::Display($curcol);
						}
						//die("Ligne d'entête : ".Tools::Display($myArrayTable)."");
						/*
						 * Création des lignes de données
						 */
					}
					//die();
					if($boolOutput) echo "Groupe d'export :".Tools::Display($myWhereGroupesExport);
	
					if($boolOutput) echo "Démarrage de la grande boucle".BR;
					
					foreach($myListResultats as $curresultat)
					{
						$row++;
						//$boolOutput=false;
						//if($boolOutput) echo "Recherche du champ dans ".Tools::Display($curresultat);
						
						$database->setQuery("SELECT a.*,i.cd_import_colonne,wgp.cd_groupe_parametre " .
											" FROM " .
											"	(analyse as a," .
											"	#__rel_import_colonne_parametre as r," .
											"	#__rel_import_colonne_import_description as rid, " .
											"	#__a_import_colonne as i, " .
											"	#__a_import_description as ides) ".
											"LEFT JOIN w_groupe_parametre as wgp ON wgp.cd_groupe_parametre=i.export_code_groupe  " .
											" WHERE " .
												$myWhereGroupesExport .
												"ides.code_import_description='".$myCodeImport."' ".
												" AND ides.id_a_import_description=rid.a_import_description_id_a_import_description ".
												" AND i.id_a_import_colonne=rid.a_import_colonne_id_a_import_colonne ".
												" AND a.par_id_parametre=r.parametre_id_parametre " .
												" AND r.a_import_colonne_id_a_import_colonne=i.id_a_import_colonne " .
												" AND a.ech_id_echantillon=".$curresultat->id_echantillon .
												" GROUP BY a.par_id_parametre ");
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
								{
									$myCurValeur=trim($myTableReindexationValeurs[$curvaleurs->cd_import_colonne]->ana_resultat);
								}
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
								//die(Tools::Display($curcol));
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
									/*
									 * EXCEPTIONS DE GESTION DE DONNEES - FIN
									 */
								
								
								$myArrayTable[$row][$curvaleurs->cd_import_colonne]=str_replace(",",".",$myCurValeur);
							}
							
							
							/*
							 * Calcul de la somme des PCB indicateurs
							 */
							$myArrayListePCBIndicateurs=array(1001,1002,1003,1004,1005,1006,1007);
							$myColSommePCBIndicateurs=$myCodeImport."_1008";
							$myArrayTable[$row][$myColSommePCBIndicateurs]=0.;
							$boolTousPCBIndicateurs=true;
							$myCountNombreValAuSeuil=0;
							foreach($myArrayListePCBIndicateurs as $curpcb)
							{
								$myCurColPCBIndicateur=$myCodeImport."_".$curpcb;
								if(isset($myArrayTable[$row][$myCurColPCBIndicateur]) && trim($myArrayTable[$row][$myCurColPCBIndicateur])!="")
								{
									if(substr($myArrayTable[$row][$myCurColPCBIndicateur],0,1)=="<")
									{
										$myCountNombreValAuSeuil++;
									}
									else
										$myArrayTable[$row][$myColSommePCBIndicateurs]+=floatval($myArrayTable[$row][$myCurColPCBIndicateur]);
									
								}
								else
									$boolTousPCBIndicateurs=false;
								
							}
							if(!$boolTousPCBIndicateurs)
								$myArrayTable[$row][$myColSommePCBIndicateurs]="";
							if($myCountNombreValAuSeuil==count($myArrayListePCBIndicateurs))
								$myArrayTable[$row][$myColSommePCBIndicateurs]="< Limite de Quantification";
								
							//echo "Somme PCB Indicateurs ligne ".$row."/".$myColSommePCBIndicateurs." : ".$myArrayTable[$row][$myColSommePCBIndicateurs].BR;
							//die("Ligne de données ".Tools::Display($myArrayTable[$row]));
							
							
						}
					}
					
				}
			
				
				/*
				 * 
				 * Nettoyage à posteriori des colonnes surnuméraires ajoutées pour les calculs de sommes
				 * 
				 */
				 
				/*
				 * Vérification de la liste des colonnes surnuméraires
				 */
				$myNettoyageCodesColonnes=array();
				foreach($export_groups_array as $curgroup)
				{
					$myCurGroupe=$curgroup["group"];
					$myColExport="export_".$curgroup["type"]."_colonne";
					foreach($myColOrdered as $key=>$curcol)
					{
						if($curcol->export_code_groupe==$myCurGroupe)
						{
							if($curcol->$myColExport!=1)
							{
								$myNettoyageCodesColonnes[]=$curcol->cd_import_colonne;
							}
						}
					}
				}
				
				/*
				 * Nettoyage effectif sur base de la liste préparée précédemment
				 */
				if(is_array($myNettoyageCodesColonnes) && count($myNettoyageCodesColonnes)>0)
					foreach($myArrayTable as $row=>$val)
						foreach($myNettoyageCodesColonnes as $curcode)
							unset($myArrayTable[$row][$curcode]);
				
				/*
				 * Post-traitement des valeurs
				 */
				foreach($myArrayTable as $row=>$curligne)
					foreach($curligne as $key=>$curitem)
					{
						/*
						 * Traitement des cellules vides
						 */
						if(trim($curitem)=="")
						{
							$myArrayTable[$row][$key]="-";
						}
						
						/*
						 * Traitement des stations : ajout d'un lien sur le code station dans le fichier excel
						 */
						if($key==$myCodeColonneStation && $curitem!="" && substr($curitem,0,2)=="06" && $cmbFormatSortie=="xls")
						{
							if($row>0)
								$myArrayTable[$row][$key]="http://sierm.eaurmc.fr/eaux-superficielles/etat-qualitatif.php?station=".$curitem."&donnees=signaletique&codeRegion=&codeDept=&codeCommune=&bassin=&coursdeau".URL_SEPARATOR.$curitem;
							else
								$myArrayTable[$row][$key]=$curitem;
							//echo __LINE__." Colonne code station ".$key." (".Tools::Display($myArrayTable[$row][$key]).")".BR;
						}
					}
				
				/*
				 * Vérification de la taille du tableau final après nettoyage des lignes vides
				 */
				if(count($myArrayTable)>1)
				{
						
					/*
					 * 
					 * Génération de la sortie en elle-même
					 * 
					 */
					
					if($boolOutput) echo "Nb lignes : ".($myLineStop-2)." sur ".(count($myArrayTable)-1)."<br />\n";
					
					/*
					 * Génération en CSV : routine générique en librairie
					 */
					if($cmbFormatSortie=="csv")
					{
						Tools::SendCSV("donnees-sediments.csv",$myArrayTable,true,$mySeparateurCSV);
					}
					/*
					 * Génération en HTML : fonction spécifique
					 */
					elseif($cmbFormatSortie=="html")
					{
						$myLineStop=-1;
						$myCurLine=0;
						echo "Rappel des options d'export : <br />\n";
						echo "Date début prélèvement : ".$date_peche_debut." (".$_POST["txtDatePecheDebut"].")<br />\n";
						echo "Date fin prélèvement : ".$date_peche_fin." (".$_POST["txtDatePecheFin"].")<br />\n";
						echo "Date début publication : ".$date_publication_debut." (".$_POST["txtDatePublicationDebut"].")<br />\n";
						echo "Groupes de publication : ".Tools::Display($export_groups_array).BR;
						echo "Nombre de pages : ".$myNbPages.BR;
						echo "Nombre de résultats (max) par page : ".NB_RESULTS_PER_QUERY.BR;
						echo "Colonne du code station :".$myCodeColonneStation.BR;
						//echo "Dernière requête count : ".$myRequeteExport_Count.BR;
						//echo "Requête de base : ".$myRequeteExport.BR;
						//echo "Dernière requête : ".$myCurRequeteExport.BR;
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
								//echo "Valeur de ".$keyarray."/".$keyval."=".$curarray[$keyval].BR;
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
					/*
					 * Génération xls version 2003 : utilisation d'une librairie externe sur base d'une entrée générique
					 */
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
									if(!isset($curarray[$keyval])) $curarray[$keyval]="-";
									if(isset($myColOrdered[$keyval]->export_format_valeur))
										$cell_format=$myColOrdered[$keyval]->export_format_valeur;
									$cell_format=((trim($cell_format)=="jj/mm/aaaa")?"@":$cell_format);
									if(!isset($formats_array[$cell_format]))
									{
										$formats_array[$cell_format] =& $workbook->addFormat();
										$formats_array[$cell_format]->setFontFamily('Arial');
										$formats_array[$cell_format]->setSize(10);
										$formats_array[$cell_format]->setNumFormat($cell_format);
										if($cell_format=="url")
										{
											$formats_array[$cell_format]->setUnderline(1);
											$formats_array[$cell_format]->setColor("blue");
										}
									}
									
									if($i==0)
										$cell_format="entete";
									if(strstr($curarray[$keyval],URL_SEPARATOR)!==false)
										$cell_format="url";
									if($cell_format=="url")
									{
										list($url,$label)=explode(URL_SEPARATOR,$curarray[$keyval]);
										$worksheet->writeUrl($i, $j,$url,$label,$formats_array[$cell_format]);
									}
									else
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
								Tools::DL_DownloadProgressive("donnees-sediments-export.xls",$tmpfname);
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
					$error_msg.="Aucun résultat<div style='display:none;'>".$database->getQuery()."</div><br />\n";
				}
				
			}
			else
			{
				$error_msg.="Aucun résultat<div style='display:none;'>".$database->getQuery()."</div><br />\n";
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