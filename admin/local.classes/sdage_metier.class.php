<?php

class sdage_metier
{
	var $params=array();
	var $db;
	var $section="accueil";
	var $texte_recherche="";
	var $code_me="";
	var $caracteristriques_me="";
	var $template=null;
	public $auth=null;
	public $sections_avec_menu=array("index","accueil","connexion","panneau");
	var $template_filenames=array(
		"accueil"=>"accueil.tpl",
		"connexion"=>"connexion.tpl",
		"panneau"=>"panneau.tpl",
		"searchresult"=>"searchresult.tpl",
		"fiche"=>"fiche.tpl",
		"detail-pressions" => "detail-pressions.tpl"
		);
	var $template_name="mdosout.template";
	var $path_pre="";
	var $formPage="";
	var $msg_error="";
	var $msg_info="";
	var $search_result=array();

	public static $importColonnes=array(
		// code_masse_eau;code_pression;impact_2016;valeur_forcee_2016;rnabe_2021;pression_origine_risque_2021;impact_2019;rnabe_2027;pression_origine_risque_2027
		"code_me"=>"code_masse_eau",
		"id_pression" => "code_pression",
		"impact_2016"=>"impact_2016",
		"impact_valeur_forcee"=>"valeur_forcee_2016",
		"rnaoe_2021"=>"rnabe_2021",
		"pression_origine_2021"=>"pression_origine_risque_2021",
		"impact_2019"=>"impact_2019",
		"rnaoe_2027"=>"rnabe_2027",
		"pression_origine_2027"=>"pression_origine_risque_2027"
		/*
		"code_me"=>"Code masse d'eau",
		"libelle_pression" => "Pression",
		"impact_2016"=>"Classe d'impact SDAGE 2016 (1;2;3) ",
		"impact_valeur_forcee"=>"Valeur forcée impact SDAGE 2016 O/N",
		"impact_2019"=>"Classe d'impact EdL2019 (1;2;3)",
		"rnaoe_2021"=>"RNAOE 2021 (O/N)",
		"pression_origine_2021"=>"Pression à l'origine du risque 2021 (O/N)",
		"rnaoe_2027"=>"RNAOE 2027 (O/N)",
		"pression_origine_2027"=>"Pression à l'origine du risque 2027 (O/N)" */
	);

	
    public function __construct(&$database,&$path_pre,$thePage="index.php")
    {
    	$this->path_pre=$path_pre;
    	$this->formPage=basename($thePage);
		//die("Nom : ".basename($thePage));
		if(trim($this->formPage)=="") $this->formPage="index.php";
    	$this->db=$database;
		require_once($this->path_pre."/".$this->template_name.".php");
		$this->template = new MyTableTemplateMdoSout($this->template_name,$this->path_pre);
		$this->template->set_filenames($this->template_filenames);
		//echo "Objet template : ".Tools::Display($this->template);
    }

	public function authIsCreateur()
	{
		if(!$this->auth->isLoaded()) return false;
		if(!isset($this->auth) || !isset($this->auth->user_Rank)) return false;
		if($this->auth->user_Rank!=="crea") return false;
		return true;
	}

	public function authIsCollaborateur()
	{
		if(!$this->auth->isLoaded()) return false;
		if(!isset($this->auth) || !isset($this->auth->user_Rank)) return false;
		if($this->auth->user_Rank!=="coll") return false;
		return true;
		
	}
	
	public function setAuth($authobject)
	{
		$this->auth=$authobject;
	}
	
	public static function importGetListeColonnes()
	{
		return self::$importColonnes;
	}
	
    public function bind($theParams)
    {
    	if(!is_array($theParams) || count($theParams)<=0) return false;
    	foreach($theParams as $key=>$value)
    	{
    		$this->params[$key]=$value;
    	}
    	return true;
    }

    public function handle()
    {
    	if(isset($this->params["section"]) && $this->params["section"]!="")
		{
    		$this->section=$this->params["section"];
		}
    	switch($this->section)
    	{
			case "avis":
				$this->handle_Avis();
				break;
    		case "import":
    			$this->handle_Import();
    			break;
    		case "search":
    			$this->handle_Search();
    			break;
    		case "fiche":
    			$this->handle_Fiche();
    			break;
    		case "zonedetail":
    			 $this->handle_ZoneDetail();
    			 break;
    	}
    }

    public function handle_ZoneDetail()
    {
    	if(!isset($this->params['zoneFile']) || !isset($this->params["zoneCode"]) || $this->params["zoneCode"]=="" || $this->params["zoneFile"]=="")
    	   $this->zoneDetail=false;
    	$this->params["zoneCode"]=trim(addslashes($this->params["zoneCode"]));
    	$this->params["zoneFile"]=trim(addslashes($this->params["zoneFile"]));
    	$this->zoneDetail=new stdClass();
    	switch($this->params['zoneFile'])
    	{
    		case "Communes_RM":
    			$this->zoneDetail->titre="Informations sur la commune";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->zoneRequete="SELECT com_numero as Numero_INSEE,com_nom as Nom, departement_num_departement as Departement FROM lexique_commune WHERE com_numero='".$this->params["zoneCode"]."';";
    			break;
    		case "Departements_RM":
    			$this->zoneDetail->titre="Informations sur le département";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->entites_hydro=false;
    			$this->zoneDetail->zoneRequete="SELECT nom_departement as Nom, n__departement as Numero,n__region as Numero_Region, nom__region as Nom_Region FROM lexique_departements_rmc WHERE n__departement='".$this->params["zoneCode"]."';";
                break;
    		case "MEPlandEau_RM":
    			$this->zoneDetail->titre="Informations sur le plan d'eau";
    			$this->zoneDetail->zoneRequete="SELECT code__pe as Code,nom__pe as Nom FROM lexique_me_plan_eau WHERE code__pe='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->secteurs=false;
     			$this->zoneDetail->entites_hydro=false;
   			break;
    		case "MERivieresPrincipales_RM":
    		case "MERivieresSecondaires_RM":
    			//nom1     nom_her     nom_typce   nom_ctx_pisci   influ_her   long    fmzone_nom
    			$this->zoneDetail->titre="Informations sur le cours d'eau";
    			$this->zoneDetail->zoneRequete="SELECT Code_MDO as Code,Nom_MDO as Nom , SSBV_Code_SSBV as Code_Sous_Bassin,Intitule Intitule_Sous_Bassin FROM lexique_obj_mdo_sup WHERE Code_MDO='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->entites_hydro=false;
    			break;
			case "EntiteHydroGeol":
				$myCodeME="";
				if(isset($this->params["zoneCode"]) && $this->params["zoneCode"]!="")
				{
					$myEntite=new entites_hydro($this->db);
					if($myEntite->loadCode($this->params["zoneCode"]))
						if(!is_null($myEntite->Code_me_eu) && $myEntite->Code_me_eu!="")
							$myCodeME=$myEntite->Code_me_eu;
				}
				if($myCodeME=="")
				{
					$this->zoneDetail->titre="Entité hydrogéologique";
					$this->zoneDetail->speciallink="Code : #CODE#";
					$this->zoneDetail->codeforlink="Code";
					$this->zoneDetail->zoneRequete="";
					$this->zoneDetail->secteurs=false;
					break;
				}
				$this->params["zoneCode"]=$myCodeME;
    		case "MEsoutAffleurante":
    		case "MEsoutProfondeur1":
    		case "MEsoutProfondeur2":
    		case "MEsoutSecteursAFFL":
    		case "MEsoutSecteursProfond":
    			$this->zoneDetail->titre="Masse d'eau souterraine";
    			$this->zoneDetail->speciallink="<a href=\"../db_mesout/index.php?section=fiche&txtRecherche=".$this->texte_recherche."&code_me=#CODE#\" target='_blank' >Accéder à la fiche de cette masse d'eau</a>";
    			$this->zoneDetail->codeforlink="Code";
    			$this->zoneDetail->zoneRequete="SELECT code_me as Code,code_me_euro as Code_Euro,libelle_me as Libelle FROM caracteristiques_me WHERE code_me_euro='".substr($this->params["zoneCode"],0,9)."';";
    			
				$this->zoneDetail->entites_hydro=new stdClass();
                $this->zoneDetail->entites_hydro->titre="Entités hydrogéologiques composant la masse d'eau";
                $this->zoneDetail->entites_hydro->entites_hydro_requete="SELECT code_entite as Codent,titre_fichier as Noment,type_fichier,nom_fichier FROM rel_entites_hydro_me_fichier  WHERE rel_entites_hydro_me_fichier.code_me_eu='".substr($this->params["zoneCode"],0,9)."' ORDER BY Codent ASC";
                //$this->zoneDetail->entites_hydro->entites_hydro_requete="SELECT Codent,Noment,type_fichier,nom_fichier FROM entites_hydro LEFT JOIN rel_entites_hydro_me_fichier ON (entites_hydro.Code_me_eu=rel_entites_hydro_me_fichier.code_me_eu) WHERE Code_me_eu='".substr($this->params["zoneCode"],0,9)."' ORDER BY Codent ASC";
				$this->zoneDetail->secteurs=new stdClass();
                $this->zoneDetail->secteurs->titre="Secteurs de cette masse d'eau";
                $this->zoneDetail->secteurs->secteursrequete="SELECT code_entite_v1,code_entite_v2,code_entite,type_fichier,nom_fichier FROM entites_hydrogeologiques_me LEFT JOIN rel_secteurme_fichier ON (code_entite_v1=code_entite OR code_entite_v2=code_entite) WHERE code_me_euro='".substr($this->params["zoneCode"],0,9)."' ORDER BY code_entite_v1 ASC,code_entite_v2 ASC";
    			break;
    		case "METransition_RM":
    			$this->zoneDetail->titre="Informations sur la masse d'eau de transition";
    			$this->zoneDetail->zoneRequete="SELECT Code_MDO as Code,Nom_MDO as Nom , SSBV_Code_SSBV as Code_Sous_Bassin,Intitule Intitule_Sous_Bassin FROM lexique_obj_mdo_sup WHERE Code_MDO='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->entites_hydro=false;
                $this->zoneDetail->secteurs=false;
    			break;
    		case "Regions_RM":
    			$this->zoneDetail->titre="Informations sur la région";
    			$this->zoneDetail->zoneRequete="SELECT num_region as Numero_Region, nom_region as Nom_Region FROM lexique_region WHERE nom_region='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->entites_hydro=false;
                $this->zoneDetail->secteurs=false;
    			break;
    		case "ssbv_RM":
    		case "ssbv2_RM":
    			$this->zoneDetail->titre="Informations sur le sous-bassin versant";
    			$this->zoneDetail->zoneRequete="SELECT Code_SSBV,Nom_SSBV FROM lexique_ssbv WHERE Code_SSBV='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->entites_hydro=false;
    			break;
    		case "RhoneSaone":
    		default:
    			$this->zoneDetail=false;
    			break;
    	}

    	if($this->zoneDetail!==false)
    	{
    		$this->zoneDetail->informations=false;
    		if(isset($this->zoneDetail->zoneRequete) && $this->zoneDetail->zoneRequete!="")
    		{
    			$this->db->setQuery($this->zoneDetail->zoneRequete);
    			$myListeObjets=$this->db->loadObjectList();
    			$this->zoneDetail->informations=false;
    			if(is_array($myListeObjets) && count($myListeObjets)>0)
    			{
    				$this->zoneDetail->informations=get_object_vars($myListeObjets[0]);
    			}
    			if(isset($this->zoneDetail->entites_hydro) && is_object($this->zoneDetail->entites_hydro))
    			{
    				if(isset($this->zoneDetail->entites_hydro->entites_hydro_requete) && $this->zoneDetail->entites_hydro->entites_hydro_requete!="")
    				{
    					$this->db->setQuery($this->zoneDetail->entites_hydro->entites_hydro_requete);
    					$myListeEntites=$this->db->loadObjectList();
    					//echo "Résultat : ".Tools::Display($this->db->getQuery());
    					if(is_array($myListeEntites) && count($myListeEntites)>0)
    					{
                            foreach($myListeEntites as $curentite)
                            {
                            	if(!is_null($curentite->Codent) && $curentite->Codent!="")
                            	   $this->zoneDetail->entites_hydro->informations[$curentite->Codent][]=get_object_vars($curentite);
                            }
    					}
    				}
    			}
    		}
    	}
    }

    public function handle_Fiche()
    {
    	if(isset($this->params["txtRecherche"]) && $this->params["txtRecherche"]!="")
    		$this->texte_recherche= $this->params["txtRecherche"];
    	if(isset($this->params["code_me"]) && $this->params["code_me"]!="")
    		$this->code_me= $this->params["code_me"];

		return $this->chargeME($this->code_me);

    }

    public function chargeME($theCodeME)
    {
    	if(trim($theCodeME)=="")
    		return false;

		$this->code_me=addslashes($theCodeME);

    	$this->caracteristriques_me=array();
    	$myQueryChargeME="SELECT * FROM caracteristiques_me as cme WHERE cme.code_me='".$this->code_me."';";
    	$this->db->setQuery($myQueryChargeME);
    	$myListeME=$this->db->loadObjectList();
    	//echo "Liste me : ".Tools::Display($myQueryChargeME);
    	if(is_array($myListeME) && count($myListeME)==1)
    	{
    		$this->caracteristriques_me=get_object_vars($myListeME[0]);

    		/*
    		 * Chargement départements de la ME
    		 */
    		/*
    		$myQueryDepartement="SELECT ldrmc.* FROM lexique_departements_rmc as ldrmc,departements_me as dme WHERE dme.code_me='".$this->code_me."' AND ldrmc.n__departement=dme.n__departement;";
    		$this->db->setQuery($myQueryDepartement);
    		$myListeDep=$this->db->loadObjectList();
    		$this->caracteristriques_me["liste_departements"]=array();
    		if(is_array($myListeDep) && count($myListeDep)>0)
    		{
    			foreach($myListeDep as $key=>$val)
    			{
    				$this->caracteristriques_me["liste_departements"][]=get_object_vars($val);
    			}
    		}
    		 */

    		$this->caracteristriques_me["liste_departements"]=$this->getObjArrayFromQuery(
    			"SELECT ldrmc.* FROM lexique_departements_rmc as ldrmc,departements_me as dme WHERE dme.code_me='".$this->code_me."' AND ldrmc.n__departement=dme.n__departement;"
    			);

    		/*
    		 * Chargement entités de la ME
    		 */
    		/*
    		$myQueryEntites="SELECT * FROM entites_hydrogeologiques_me as ehme WHERE ehme.code_me='".$this->code_me."';";
    		$this->db->setQuery($myQueryEntites);
    		$myListeEntites=$this->db->loadObjectList();
    		$this->caracteristriques_me["liste_entites"]=array();
    		if(is_array($myListeEntites) && count($myListeEntites)>0)
    		{
    			foreach($myListeEntites as $key=>$val)
    			{
    				$this->caracteristriques_me["liste_entites"][]=get_object_vars($val);
    			}
    		}
    		 */
    		$this->caracteristriques_me["liste_entites"]=$this->getObjArrayFromQuery(
    			"SELECT * FROM entites_hydrogeologiques_me as ehme WHERE ehme.code_me='".$this->code_me."' ORDER BY code_entite_v1 ASC, code_entite_v2 ASC;"
    			);
    		/*
    		 * Chargement mdo sup en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_mdosup"]=$this->getObjArrayFromQuery(
		    		"SELECT mss.code_me_euro, lmsrmc.* FROM me_sup_sout as mss,lexique_me_sup_rmc as lmsrmc WHERE lmsrmc.id_mdo=mss.id_mdo AND mss.code_me='".$this->code_me."' AND mss.id_mdo IS NOT NULL ;"
    		 	);
    		/*
    		 * Chargement plans d'eau sup en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_plandeau"]=$this->getObjArrayFromQuery(
		    		"SELECT mpes.code_me_euro, lmpea.* FROM me_plan_eau_sout as mpes,lexique_me_plan_eau as lmpea WHERE lmpea.code__pe=mpes.code__pe AND mpes.code_me='".$this->code_me."' AND mpes.code__pe IS NOT NULL ;"
    		 	);
    		/*
    		 * Chargement occupation_sols en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_occupationsols"]=$this->getObjArrayFromQuery(
		    		"SELECT * FROM occupation_sols WHERE occupation_sols.code__pe='".$this->code_me."';"
    		 	);

    		/*
    		 * Chargement occupation_sols en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_volumespreleves"]=$this->getObjArrayFromQuery(
		    		"SELECT SUM( `volume_preleve` ) AS volume_total, `synthese_prelevement_mdo`.*
						FROM `synthese_prelevement_mdo`
						WHERE mdo_id ='".$this->code_me."'
						GROUP BY `code_regroupement_usage`;"
    		 	);
    	}

    }

    function getObjArrayFromQuery($theQuery)
    {
    	if(trim($theQuery)=="")
    		return array();
 		$this->db->setQuery($theQuery);
		$myListe=$this->db->loadObjectList();
		$myData=array();
		if(is_array($myListe) && count($myListe)>0)
		{
			foreach($myListe as $key=>$val)
			{
				$myData[]=get_object_vars($val);
			}
		}
    	return $myData;
    }

	public function listeMassesDeau()
	{
		$mdo=mdtb_table::InitObject("mdtb_ae_massesdeau");
		$mdo->recSQLSearch("1");
		$arrMdo=array();
		if($mdo->recFirst())
		{
			do
			{
				$arrMdo[$mdo->recGetValue("id_massedeau")]=$mdo->recGetRecord();
			} while($mdo->recNext());
		}
		return $arrMdo;
	}
	
	public function listeSSBV($typeindex="id")
	{
		$mdo=mdtb_table::InitObject("mdtb_ae_ssbv");
		$mdo->recSQLSearch("1");
		$arrMdo=array();
		if($mdo->recFirst())
		{
			do
			{
				if($typeindex=="id") $arrMdo[$mdo->recGetValue("id_ssbv")]=$mdo->recGetRecord();
				elseif($typeindex=="code") $arrMdo[$mdo->recGetValue("code_ssbv")]=$mdo->recGetRecord();
			} while($mdo->recNext());
		}
		return $arrMdo;
	}
	
	public function chargeMassesDeau()
	{
		$mdo=mdtb_table::InitObject("mdtb_ae_massesdeau");
		$mdo->recSQLSearch("1");
		$arrMdo=array();
		if($mdo->recFirst())
		{
			do
			{
				$arrMdo[$mdo->recGetValue("code_me")]=$mdo->recKeyValue();
			} while($mdo->recNext());
		}
		return $arrMdo;
	}
	
	public function chargePressions()
	{
		$pressions=mdtb_table::InitObject("mdtb_ae_pressions");
		$pressions->recSQLSearch("1");
		$arrPressions=array();
		if($pressions->recFirst())
		{
			do
			{
				$arrPressions[$pressions->recGetValue("libelle_pression")]=$pressions->recKeyValue();
			} while($pressions->recNext());
		}
		return $arrPressions;
	}
	
	public function listePressions()
	{
		$pressions=mdtb_table::InitObject("mdtb_ae_pressions");
		$pressions->recSQLSearch("1");
		$arrPressions=array();
		if($pressions->recFirst())
		{
			do
			{
				$arrPressions[$pressions->recKeyValue()]=$pressions->recGetValue("libelle_pression");
			} while($pressions->recNext());
		}
		return $arrPressions;
	}
	
	public function chargePressionsDeCSVEtConsolideListeAvecBase($csv)
	{
		
		//die("Enregistrements actuels dans les pressions : ".print_r($arrPressions,true));
		$listePressionsDansFichier=array();
		$nbAjouts=0;
		foreach($csv as $curData)
		{
			$listePressionsDansFichier[$curData["Pression"]]=$curData["Pression"];
			if(isset($arrPressions[$curData["Pression"]]))
			{
				$listePressionsDansFichier[$arrPressions[$curData["Pression"]]]=$curData["Pression"];
				unset($listePressionsDansFichier[$curData["Pression"]]);
			}
			/*
			 * Array
				(
					[Code masse d'eau] => FRDR10040
					[Pression] => Pollutions agricoles par les pesticides
					[Classe d'impact SDAGE 2016 (1;2;3) ] => 2
					[Valeur forcée impact SDAGE 2016 O/N] => O
					[Classe d'impact EdL2019 (1;2;3)] => 1
					[RNAOE 2021 (O/N)] => N
					[Pression à l'origine du risque 2021 (O/N)] => N
					[RNAOE 2027 (O/N)] => N
					[Pression à l'origine du risque 2027 (O/N)] => N
				)
			 */
		}
		
		foreach($listePressionsDansFichier as $keyCurPression => $valLblPression)
		{
			if($keyCurPression === $valLblPression)
			{
				$obj=new stdClass();
				$obj->id_pression=null;
				$obj->libelle_pression=$valLblPression;
				$pressions=mdtb_table::InitObject("mdtb_ae_pressions");
				$pressions->recNewRecord();
				$pressions->recStore($obj);
				$newId=$pressions->recKeyValue();
				$listePressionsDansFichier[$newId]=$valLblPression;
				unset($listePressionsDansFichier[$valLblPression]);
			}
		}
		
		$arrPressions=$this->chargePressions();
		return $arrPressions;
	}
	
	public function handle_Avis()
	{
		$action="$('#".$this->params["id_form_avis"]." label.sauvegardeerreur', window.parent.document).show();";
		if(!$this->auth->isLoaded() || $this->auth->user_Rank!="crea")
		{
			$this->msg_info.="<script>alert(\"Vous n'avez pas les droits d'écriture sur les avis : ".$this->auth->user_Rank."\");</script>";
			// Pas de sauvegarde de l'avis
			return false;
		}
		else
		{
			// TRaitement de la sauvegarde de l'avis
			if($this->params["sauverAvis"]) $action="$('#".$this->params["id_form_avis"]." label.sauvegardeok', window.parent.document).show();";
			if($this->params["validerAvis"])
			{
				$action="$('#".$this->params["id_form_avis"]." label.validationok', window.parent.document).show();";
				$action.="$('#".$this->params["id_form_avis"]." input.boutonaction', window.parent.document).remove();";
			}
			$this->msg_info.="<script>
				$('#".$this->params["id_form_avis"]."', window.parent.document).addClass('sauvegardeok');
				$('#".$this->params["id_form_avis"]." label.sauvegarde', window.parent.document).hide();
				".$action."	
			 </script>";
			//die("<script>alert('".$this->params["id_form_avis"]."');</script>");
			return false;
		}
	}
	
	public function handle_Import()
	{
		//echo "TRaitement de l'import sur params : <pre>".print_r($this->params,true)."</pre>";
		if(!isset($this->params["import_echantillons"]))
		{
			$this->msg_info="Aucun fichier fourni"; // : <pre>".$requeteME."</pre>";
			return false;
		}
		$csv=new CSV($this->params["import_echantillons"]["tmp_name"]);
		if(!$csv->isRead())
		{
			$this->msg_info="Impossible de lire le fichier"; // : <pre>".$requeteME."</pre>";
			return false;
		}
		$listeEntetes=$csv->getHeaders();
		$entetesAutorises=self::importGetListeColonnes();
		foreach($listeEntetes as $curEntete)
		{
			if(!in_array($curEntete,$entetesAutorises))
			{
				$this->msg_info="Erreur entete non trouvee : ".$curEntete; // : <pre>".$requeteME."</pre>";
				return false;
			}
		}
		$this->msg_info="Toutes les entetes ont été trouvées, démarrage de l'import";
		/*
		 * Import ou mise à jour des pressions
		 */
		
		//$arrPressions=$this->chargePressionsDeCSVEtConsolideListeAvecBase($csv);
		$arrPressions=$this->listePressions();
		$arrMassesDeau=$this->chargeMassesDeau();
		$erreurNomMDO=false;
		foreach($csv as $curData)
		{

			if(!isset($arrMassesDeau[$curData["code_masse_eau"]]))
			{
				$erreurNomMDO=true;
				$this->msg_error.="Erreur MDO non trouvée : ".$curData["code_masse_eau"]."<br />";
			}
		}
		//die(__LINE__." ERREUR ");
		if($erreurNomMDO)
		{
			return false;
		}
		//die("Fin après vérification des noms des MDO");
		//die("Liste indexée des masses d'eau : <pre>".print_r($arrMassesDeau,true)."</pre>");
		/*
		 * Import ou mise à jour des états des lieux, écrasement des valeurs précédentes
		 */
		$edl= mdtb_table::InitObject("mdtb_ae_edl_massesdeau");
		$nbEDLMaj=0;
		foreach($csv as $curData)
		{
			$obj=new stdClass();
			$edl->recSQLSearch("ae_edl_massesdeau.id_pression=".$arrPressions[$curData["Pression"]]." AND ae_edl_massesdeau.id_massedeau=".$arrMassesDeau[$curData["Code masse d'eau"]]);
			if($edl->recFirst())
			{
				$obj=$edl->recGetRecord();
				//echo "Enregistrement retrouvé "."id_pression=".$arrPressions[$curData["Pression"]]." AND id_massedeau=".$arrMassesDeau[$curData["Code masse d'eau"]]." <pre>".print_r($obj,true)."</pre>";
			}
			else
			{
				$edl->recNewRecord();
				$obj->id_edl_massedeau=null;
			}
			/*
			$obj->id_massedeau=$arrMassesDeau[$curData["Code masse d'eau"]];
			$obj->impact_2016=$curData["Classe d'impact SDAGE 2016 (1;2;3) "];
			$obj->impact_valeur_forcee=$curData["Valeur forcée impact SDAGE 2016 O/N"]=="O"?1:0;
			$obj->impact_2019=$curData["Classe d'impact EdL2019 (1;2;3)"];
			$obj->rnaoe_2021=$curData["RNAOE 2021 (O/N)"]=="O"?1:0;
			$obj->pression_origine_2021=$curData["Pression à l'origine du risque 2021 (O/N)"]=="O"?1:0;
			$obj->rnaoe_2027=$curData["RNAOE 2027 (O/N)"]=="O"?1:0;
			$obj->pression_origine_2027=$curData["Pression à l'origine du risque 2027 (O/N)"]=="O"?1:0;
			*/
			
			$obj->id_pression=$curData["code_pression"]; //$arrPressions[$curData["Pression"]];
			$obj->id_massedeau=$arrMassesDeau[$curData["code_masse_eau"]];
			$obj->impact_2016=$curData["impact_2016"];
			$obj->impact_valeur_forcee=$curData["valeur_forcee_2016"];
			$obj->rnaoe_2021=$curData["rnabe_2021"];
			$obj->pression_origine_2021=$curData["pression_origine_risque_2021"];
			$obj->impact_2019=$curData["impact_2019"];
			$obj->rnaoe_2027=$curData["rnabe_2027"];
			$obj->pression_origine_2027=$curData["pression_origine_risque_2027"];
			$edl->recStore($obj);
			//echo "Enregistrement objet.<pre>".print_r($obj,true)."</pre><br />";
			$nbEDLMaj++;
		}
		$this->msg_info.="<br />Mise à jour terminée avec : ".$nbEDLMaj." enregistrements<br />";
		//echo "Fichier ouvert, entetes : ".print_r($csv->getHeaders(),true)."<br />";
	}
    public function handle_Search()
    {
		$this->texte_recherche="";
		$this->liste_ssbv="";
		$this->liste_ss_ut="";
		$this->liste_impacts="";
		$this->liste_pressions="";
    	if(isset($this->params["txtRecherche"]) && $this->params["txtRecherche"]!="")
		{
    		$this->texte_recherche= $this->params["txtRecherche"];
		}
    	if(isset($this->params["liste_ssbv"]) && is_array($this->params["liste_ssbv"]) && count($this->params["liste_ssbv"]))
		{
    		$this->liste_ssbv= "'".implode("','",$this->params["liste_ssbv"])."'";
		}
    	if(isset($this->params["liste_ss_ut"]) && is_array($this->params["liste_ss_ut"]) && count($this->params["liste_ss_ut"]))
		{
    		$this->liste_ss_ut= "'".implode("','",$this->params["liste_ss_ut"])."'";
		}
		
		if(isset($this->params["liste_impacts"]) && is_array($this->params["liste_impacts"]) && count($this->params["liste_impacts"]))
		{
			$this->liste_impacts="'".implode("','",$this->params["liste_impacts"])."'";
		}
		
		if(isset($this->params["liste_pressions"]) && is_array($this->params["liste_pressions"]) && count($this->params["liste_pressions"]))
		{
			$this->liste_pressions="'".implode("','",$this->params["liste_pressions"])."'";
		}
		
		if(isset($this->params["liste_typesmdo"]) && is_array($this->params["liste_typesmdo"]) && count($this->params["liste_typesmdo"])) // && $this->params["liste_typesmdo"]!='toutes' && $this->params["liste_typesmdo"]!='')
		{
			//die("Types : ".print_r($this->params["liste_typesmdo"],true));
			foreach($this->params["liste_typesmdo"] as &$clef) $clef=addslashes($clef);
			$this->liste_typesmdo="'".implode("','",($this->params["liste_typesmdo"]))."'";
			
			//die("liste : ".$this->liste_typesmdo);
		}
		
		if(!in_array($this->params["ssorder"],array("ASC","DESC")))
			$this->params["ssorder"]="ASC";
		if(!in_array($this->params["ssfield"],array("categorie_me","code_ssbv","libelle_me","code_me","code_ss_ut")))
			$this->params["ssfield"]="code_me";
		$mySQLOrder=" ORDER BY ".$this->params["ssfield"]." ".$this->params["ssorder"];
		$joinImpactOuPression="";
		
		if($this->liste_pressions!="")
		{
			$joinImpactOuPression=" LEFT JOIN ae_edl_massesdeau AS edl ON edl.id_massedeau=mdo.id_massedeau ";
		}
		if($this->liste_impacts!="")
		{
			$joinImpactOuPression=" LEFT JOIN ae_edl_massesdeau AS edl ON edl.id_massedeau=mdo.id_massedeau ";
		}
		
		$requeteME="
			SELECT COUNT(*) AS nboccme,mdo.*,ssbv.*,ssut.*,
				IF(rsoutssut.code_ss_ut IS NOT NULL,rsoutssut.code_ss_ut,ssut.code_ss_ut) AS code_ss_ut,
				IF(rsoutssut.code_ss_ut IS NOT NULL,rsoutssut.code_ss_ut,ssut.code_ss_ut) AS code_ss_ut_sort
			FROM ae_massesdeau AS mdo
			LEFT JOIN ae_ssbv AS ssbv ON ssbv.code_ssbv=mdo.code_ssbv
			LEFT JOIN ae_ss_ut AS ssut ON ssbv.code_ss_ut=ssut.code_ss_ut
			LEFT JOIN rel_me_sout_ss_ut AS rsoutssut ON mdo.code_me=rsoutssut.code_me
			".$joinImpactOuPression."
			WHERE mdo.id_massedeau IS NOT NULL
		";
		if($this->texte_recherche!="")
		{
			$keywords = preg_split("/[\s,]+/", $this->texte_recherche);
			foreach($keywords as $curKeyword)
			{
				$curKeyword= addslashes($curKeyword);
				$requeteME.=" AND (mdo.code_me LIKE '%".$curKeyword."%' OR mdo.libelle_me LIKE '%".$curKeyword."%') ";
			}
		}
		if($this->liste_typesmdo!="")
		{
			//die("liste : ".$this->liste_typesmdo);
			$requeteME.=" AND mdo.categorie_me IN (".$this->liste_typesmdo.") ";
			/*
			switch($this->liste_typesmdo)
			{
				case "mdosup":
					$requeteME.=" AND mdo.categorie_me!='Eau souterraine' ";
					break;
				case "mdosout":
					$requeteME.=" AND mdo.categorie_me='Eau souterraine' ";
					break;
			}
			 * 
			 */
		}
		if($this->liste_ssbv!="")
		{
			$requeteME.=" AND ssbv.code_ssbv IN ($this->liste_ssbv) ";
		}
		if($this->liste_ss_ut!="")
		{
			$requeteME.=" AND (ssbv.code_ss_ut IN ($this->liste_ss_ut) OR rsoutssut.code_ss_ut IN ($this->liste_ss_ut)) ";
		}
		
		if($this->liste_pressions!="")
		{
			$requeteME.=" AND edl.id_pression IN ($this->liste_pressions) ";
		}
		if($this->liste_impacts!="")
		{
			$requeteME.=" AND edl.impact_2019 IN ($this->liste_impacts) ";
		}
		
		//echo "<pre>".$requeteME."</pre>";
		/*
		 * Sort
		 */
		$sortField="code_me";
		$sortOrder="ASC";
		if(isset($this->params["ssfield"]) && $this->params["ssfield"]!=="") $sortField=addslashes($this->params["ssfield"]);
		if(isset($this->params["ssorder"]) && $this->params["ssorder"]!=="") $sortOrder=addslashes($this->params["ssorder"]);
		$requeteME.=" GROUP BY mdo.id_massedeau ";
		$requeteME.=" ORDER BY ". $sortField . " ".$sortOrder." ";
    	$this->db->setQuery($requeteME);
    	$this->search_result=$this->db->loadObjectList();
		
    	if(!is_array($this->search_result) || count($this->search_result)<=0 || $this->search_result[0]->nboccme==0 )
		{
			$this->msg_info="Votre recherche n'a fourni aucun résultat<div style='display:none'>".$requeteME."</div>";
		}
		//echo "<pre>".$requeteME."</pre>";
    }


    function sectionContent()
    {
    	$myContent="";
    	switch($this->section)
    	{
			case "panneau":
				$myContent=$this->sectionContent_Panneau();
				break;
			case "connexion":
				$myContent=$this->sectionContent_Connexion();
				break;
    		case "accueil":
    		default:
    			$myContent=$this->sectionContent_Accueil();
    			break;
    		case "search":
    			$myContent=$this->sectionContent_Search();
    			break;
    		case "fiche":
    			$myContent=$this->sectionContent_Fiche();
    			break;
    		case "zonedetail":
    			$myContent=$this->sectionContent_ZoneDetail();
    			break;
    	}
    	return $myContent;
    }
	
	public function sectionHasMenu()
	{
		if(in_array($this->section,$this->sections_avec_menu))
		{
			return true;
		}
		return false;
	}
	
	public function getSection()
	{
		return $this->section;
	}
	
	public function initSection()
	{
		if($this->auth->isLoaded()) $this->setSection("panneau");
		else $this->setSection("connexion");
	}

	public function setSection($section)
	{
		$this->section=$section;
	}
	
    function sectionContent_ZoneDetail()
    {
        if($this->zoneDetail===false)
        {
        	return "Aucune information n'est disponible pour cette zone";
        }

    	$myContent="";
    	if(isset($this->zoneDetail->titre) && $this->zoneDetail->titre!="")
    	   $myContent.="<h3>".$this->zoneDetail->titre."</h3>\r\n";
        if(isset($this->zoneDetail->informations) && $this->zoneDetail->informations!==false && is_array($this->zoneDetail->informations) && count($this->zoneDetail->informations)>0)
        {
        	//$myContent.=Tools::Display($this->zoneDetail->secteurs);
        	$myContent.="<ul>";
        	foreach($this->zoneDetail->informations as $curinfokey=>$curinfovalue)
        	{
        		$myContent.="<li><strong>".str_replace("_","&nbsp;",$curinfokey)."&nbsp;:</strong>&nbsp;".$curinfovalue."</li>";
        	}
        	$myContent.="</ul>";
        	if(isset($this->zoneDetail->speciallink)
        		&& isset($this->zoneDetail->codeforlink)
        		&& isset($this->zoneDetail->informations[$this->zoneDetail->codeforlink]))
        		{
					$myContent.="<p>".str_replace("#CODE#",$this->zoneDetail->informations[$this->zoneDetail->codeforlink],$this->zoneDetail->speciallink)."</p>";
        			//$myContent.=(isset($this->zoneDetail->secteurs->informations) && count($this->zoneDetail->secteurs->informations)>0)?"Il y a des entités":"Il n'y pas d'entités";
        		}
        }
        if(isset($this->zoneDetail->entites_hydro->informations) && count($this->zoneDetail->entites_hydro->informations)>0)
        {
        	if(isset($this->zoneDetail->entites_hydro->titre) && trim($this->zoneDetail->entites_hydro->titre)!="")
        	{
        		$myContent.="<h3>".$this->zoneDetail->entites_hydro->titre."</h3>\r\n";

	        	$myContent.="<ul>";
        		foreach($this->zoneDetail->entites_hydro->informations as $keysecteur=>$valsecteur)
        		{
        			$myContentSecteur="";
        			$myContent.="<li>"."<strong>". /* $keysecteur."&nbsp;-&nbsp;".*/ $valsecteur[0]["Noment"]."</strong>";
        			if(is_array($valsecteur) && count($valsecteur)>0)
        			{
        				$myContentSecteur.="<ul>";
        				foreach($valsecteur as $cursecteur)
        				{
        					if($cursecteur["Codent"]=="")
        					{
        					   $myContent.="&nbsp;:&nbsp;Pas de fichier\r\n";
        					   $myContentSecteur="";
        					}
        					else
							{
								$myContentSecteur.="<li><span style='font-weight:bold;text-transform:capitalize;'>".$cursecteur["type_fichier"]."</span> : ";
								if(file_exists($path_pre."docs/".strtolower($cursecteur["type_fichier"])."/".$cursecteur["nom_fichier"]))
									$myContentSecteur.="<a  target='_blank' href='docs/".$cursecteur["type_fichier"]."/".$cursecteur["nom_fichier"]."'>";
								$myContentSecteur.=$cursecteur["nom_fichier"];
								if(file_exists($path_pre."docs/".strtolower($cursecteur["type_fichier"])."/".$cursecteur["nom_fichier"]))
									$myContentSecteur.="</a>";
								$myContentSecteur.="</li>";
							}
        				}
        				if($myContentSecteur!="")
        				    $myContentSecteur.="</ul>";
        			}
        			$myContent.=$myContentSecteur."</li>";
        		}
	        	$myContent.="</ul>";
        	}
        }
        return $myContent;
    }

    function sectionContent_Search()
    {
    	$this->prepareForm();
    	if(!is_array($this->search_result) || count($this->search_result)<=0)
    	{
    		return $this->template->pparse("accueil",true);
    	}
    	else
    	{
			//$edl=mdtb_table::InitObject("mdtb_ae_edl_massesdeau");
			$arrMassesDeau=$this->listeMassesDeau();
			$arrSSBV=$this->listeSSBV("code");
    		foreach($this->search_result as $curme)
    		{
				// SELECT COUNT(*) as nbocc FROM ae_edl_massesdeau LEFT JOIN ae_massesdeau as id_massedeau_ae_massesdeau ON id_massedeau_ae_massesdeau.id_massedeau=ae_edl_massesdeau.id_massedeau  WHERE  ( ae_edl_massesdeau.id_massedeau=2356) 
				$requeteSearch="ae_edl_massesdeau.id_massedeau=".$curme->id_massedeau;
				if($this->liste_pressions!="")
				{
					$requeteSearch.=" AND ae_edl_massesdeau.id_pression IN (".$this->liste_pressions.") ";
				}
				if($this->liste_impacts!="")
				{
					$requeteSearch.=" AND ae_edl_massesdeau.impact_2019 IN (".$this->liste_impacts.") ";
				}
				//$edl->recSQLSearch($requeteSearch);
				$requeteSQL="SELECT ae_edl_massesdeau.*, (SELECT COUNT(*) FROM ae_avis WHERE ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) AS nbavis FROM ae_edl_massesdeau WHERE ".$requeteSearch." GROUP BY ae_edl_massesdeau.id_pression";
				$this->db->setQuery($requeteSQL);
				$listeEdl=$this->db->loadObjectList();
				$detailPressions="Aucune pression pour cette masse d'eau"; //.$requeteSQL;
				
				$mdtbAvis= mdtb_table::InitObject("mdtb_ae_avis");
				if(is_array($listeEdl) && count($listeEdl)) //($edl->recFirst())
				{
					$arrPressions=$this->listePressions();
					foreach($listeEdl as $edl)  //do
					{
						if(!$this->authIsCollaborateur())
						{
							$edl->nbavis="-";
							$edl->impact_valeur_forcee="-";
						}
						else
						{
							$edl->impact_valeur_forcee=$edl->impact_valeur_forcee?"O":"N";
						}
						
						$objAvis=$mdtbAvis->getAvisPourPressionMdo($edl->id_pression,$edl->id_massedeau);
						
						$arrpression_cause_du_risque=array();
						$arrpression_cause_du_risque[]=array("id"=>"","value"=>"");
						$arrpression_cause_du_risque[]=array("id"=>"1","value"=>"Oui");
						$arrpression_cause_du_risque[]=array("id"=>"0","value"=>"Non");
						$CMB_PRESSION_CAUSE_DU_RISQUE=mdtb_forms::combolist("pression_cause_du_risque",$arrpression_cause_du_risque,$objAvis->pression_cause_du_risque);
						//die("cmb pression  : ".print_r($CMB_PRESSION_CAUSE_DU_RISQUE,true));
						$arrImpacts=array(); //array("id"=>"","value"=>""));
						$arrImpacts[]=array("id"=>"","value"=>"");
						for($i=1;$i<=3;$i++) { $arrImpacts[]=array("id"=>$i,"value"=>$i); }
						$CMB_IMPACT_ESTIME=mdtb_forms::combolist("impact_estime",$arrImpacts,$objAvis->impact_estime);
						
						//die("Boucle sur edl : ".print_r($listeEdl,true));
						$this->template->assign_block_vars
						(
							'pressions',
							array
							(
								/*
								'code_me' =>  $arrMassesDeau[$edl->recGetValue("id_massedeau")]->code_me,
								'libelle_me' => $arrMassesDeau[$edl->recGetValue("id_massedeau")]->libelle_me,
								'categorie_me' => $arrMassesDeau[$edl->recGetValue("id_massedeau")]->categorie_me,
								'code_ssbv' => $arrMassesDeau[$edl->recGetValue("id_massedeau")]->code_ssbv,
								'id_pression' => $edl->recGetValue("id_pression"),
								'libelle_pression' => $arrPressions[$edl->recGetValue("id_pression")],
								'impact_2016' => $edl->recGetValue("impact_2016"),
								'impact_valeur_forcee' => $edl->recGetValue("impact_valeur_forcee")?"O":"N",
								'impact_2019' => $edl->recGetValue("impact_2019"),
								'rnaoe_2021' => $edl->recGetValue("rnaoe_2021")?"O":"N",
								'pression_origine_2021' => $edl->recGetValue("pression_origine_2021")?"O":"N",
								'rnaoe_2027' => $edl->recGetValue("rnaoe_2021")?"O":"N",
								'pression_origine_2027'=> $edl->recGetValue("pression_origine_2027")?"O":"N",
								*/
								'code_me' =>  $arrMassesDeau[$edl->id_massedeau]->code_me,
								'libelle_me' => $arrMassesDeau[$edl->id_massedeau]->libelle_me,
								'categorie_me' => $arrMassesDeau[$edl->id_massedeau]->categorie_me,
								'code_ssbv' => $arrMassesDeau[$edl->id_massedeau]->code_ssbv,
								'libelle_ssbv' => $arrSSBV[$arrMassesDeau[$edl->id_massedeau]->code_ssbv]->libelle_ssbv,
								'id_pression' => $edl->id_pression,
								'libelle_pression' => $arrPressions[$edl->id_pression],
								'impact_2016' => $edl->impact_2016,
								'impact_valeur_forcee' => $edl->impact_valeur_forcee, //?"O":"N",
								'impact_2019' => $edl->impact_2019,
								'rnaoe_2021' => $edl->rnaoe_2021?"O":"N",
								'pression_origine_2021' => $edl->pression_origine_2021?"O":"N",
								'rnaoe_2027' => $edl->rnaoe_2021?"O":"N",
								'pression_origine_2027'=> $edl->pression_origine_2027?"O":"N",
								"nbavis" => $edl->nbavis,
								"avis_valide"=>$objAvis->avis_valide,
								"impact_estime"=>$objAvis->impact_estime,
								"pression_cause_du_risque"=>$objAvis->pression_cause_du_risque,
								"justification"=>$objAvis->commentaires,
								"lien_documents"=>$objAvis->lien_documents,
								"CMB_PRESSION_CAUSE_DU_RISQUE" => $CMB_PRESSION_CAUSE_DU_RISQUE,
								"CMB_IMPACT_ESTIME" => $CMB_IMPACT_ESTIME
							)
						);
					} //while($edl->recNext());
					$detailPressions=$this->template->pparse("detail-pressions",true);
				}
				
    			$this->template->assign_block_vars
	            (
	                'tablecontent',
	                array
	                (
	                	'code_me' => $curme->code_me,
	                	'libelle_me' => $curme->libelle_me,
	                	'categorie_me' => $curme->categorie_me,
						'code_ssbv' => $curme->code_ssbv,
						'libelle_ssbv' => $curme->libelle_ssbv,
	                	'code_ss_ut' => $curme->code_ss_ut,
	                	'libelle_ss_ut' => $curme->libelle_ss_ut,
	                	'texte_recherche' => urlencode($this->texte_recherche),
						'detail_pressions'=> $detailPressions
	                )
	            );
    			//echo "Code : ".$curme->code_me.", Libellé : ".$curme->libelle_me.", Secteur : ".$curme->secteur_be_caracterisation.BR;
    		}
    		//echo Tools::Display($this->search_result);
    		return $this->template->pparse("searchresult",true);
    	}
    }
	
	private function prepareForm()
	{
    	$this->template->assign_vars(array("FORM_PARAMS"=>"<pre>Params : ".print_r($this->params,true)."</pre>"."<pre>GET : ".print_r($_GET,true)."</pre>"."<pre>POST : ".print_r($_POST,true)."</pre>"));
		$this->mapDataToTemplate();
		$this->template->assign_vars(array("FORM_PAGE"=>$this->formPage));
		$ssut= mdtb_table::InitObject("mdtb_ae_ss_ut");
		// htmlGetComboMultiple($theName,$theKey,$theVal,$theSQLSearch,$theValues=array()
		$comboSSUT=$ssut->htmlGetComboMultiple("liste_ss_ut","code_ss_ut","code_ss_ut,libelle_ss_ut","1",$this->params["liste_ss_ut"]);
    	$this->template->assign_vars(array("CMB_SS_UT"=>$comboSSUT));
		
		$ssbv= mdtb_table::InitObject("mdtb_ae_ssbv");
		$comboSSBV=$ssbv->htmlGetComboMultiple("liste_ssbv","code_ssbv","code_ssbv,libelle_ssbv","1",$this->params["liste_ssbv"]);
    	$this->template->assign_vars(array("CMB_SSBV"=>$comboSSBV));
		
		$pressions= mdtb_table::InitObject("mdtb_ae_pressions");
		$comboPressions=$pressions->htmlGetComboMultiple("liste_pressions","id_pression","libelle_pression","1",$this->params["liste_pressions"]);
    	$this->template->assign_vars(array("CMB_PRESSIONS"=>$comboPressions));
    	$arrImpacts=array(); //array("id"=>"","value"=>""));
		for($i=0;$i<=3;$i++) { $arrImpacts[]=array("id"=>$i,"value"=>$i); }
		$listeImpacts=mdtb_forms::combolistmultiple("liste_impacts",$arrImpacts,$this->params["liste_impacts"]);
		$this->template->assign_vars(array("CMB_IMPACT"=>$listeImpacts));
		
		
		$mdtbMdo=mdtb_table::InitObject("mdtb_ae_massesdeau");
		$listeTypesMDO=$mdtbMdo->getListeTypesMassesDEau();
		$arrTypesMdo=array(); //array("id"=>"toutes","value"=>"Tous types de masses d'eau"));
		foreach($listeTypesMDO as $curTypeMdo)
		{
			//echo "Type <pre>".print_r($curTypeMdo,true)."</pre><br/>";
			$arrTypesMdo[]=array("id"=>$curTypeMdo->categorie_me,"value"=>$curTypeMdo->categorie_me);
		}
		foreach($this->params["liste_typesmdo"] as &$clef)
		{
			$clef= stripslashes($clef);
		}
		$listeTypesMDO=mdtb_forms::combolistmultiple("liste_typesmdo",$arrTypesMdo,$this->params["liste_typesmdo"]);
		
		$this->template->assign_vars(array("CMB_TYPEMDO"=>$listeTypesMDO));
		
		$pressions=$this->listePressions();
		
		$queryParams= http_build_query(
			array("txtRecherche"=>$this->params["txtRecherche"],
				"liste_ssbv"=>$this->params["liste_ssbv"],
				"liste_ss_ut"=>$this->params["liste_ss_ut"])
		);
		$this->template->assign_vars(array("QUERY_PARAMS"=>$queryParams));
		$this->template->assign_vars(array("texte_recherche"=>$this->params["txtRecherche"]));
		//echo "<pre>".print_r($this->params,true)."</pre>";
	}
	
	public function sectionContent_Connexion()
	{
		$this->prepareForm();
		$this->template->assign_vars(array("FORM_CONNEXION_PAGE"=>$this->path_pre));
		$this->template->assign_vars(array("FORM_RETURN_URL"=>"referer"));
        return $this->template->pparse("connexion",true);
	}
	
	public function sectionContent_Panneau()
	{
		$this->prepareForm();
		$this->template->assign_vars(array("FORM_CONNEXION_PAGE"=>$this->path_pre));
		$this->template->assign_vars(array("FORM_RETURN_URL"=>"referer"));
        return $this->template->pparse("panneau",true);
	}
	
    public function sectionContent_Accueil()
    {
    	$this->prepareForm();
        return $this->template->pparse("accueil",true);
    }
    function sectionContent_Fiche()
    {

    	$this->prepareForm();
    	foreach($this->caracteristriques_me as $curelement=>$curvalue)
		{
			if(is_array($curvalue) && count($curvalue)>0 && substr($curelement,0,6)=="liste_")
			{
				//echo "Assignation à ".$curelement." de ".Tools::Display($curvalue);
				foreach($curvalue as $curitem)
					$this->template->assign_block_vars
		            (
		                $curelement,$curitem
		            );
			}
			//echo "Code : ".$curme->code_me.", Libellé : ".$curme->libelle_me.", Secteur : ".$curme->secteur_be_caracterisation.BR;
		}
		return $this->template->pparse("fiche",true);
    }

    function mapDataToTemplate()
    {
		$this->template->assign_vars
        (
            array
            (
                'code_me' => $this->code_me,
                'texte_recherche' => $this->texte_recherche
            )
        );
		if(is_array($this->search_result) && count($this->search_result)==1)
		{
			$myObjVars=get_object_vars($this->search_result[0]);
			foreach($myObjVars as $key=>$val)
				if(is_array($val) || is_object($val))
					unset($myObjVars[$key]);
				else
					$myObjVars[$key]=str_replace("\n","<br />\n",$myObjVars[$key]);

			$this->template->assign_vars
	        (
	            $myObjVars
	        );

		}

		if(is_array($this->caracteristriques_me) && count($this->caracteristriques_me)>0)
		{
			$myObjVars=array();
			foreach($this->caracteristriques_me as $key=>$val)
				if(!is_array($val) && !is_object($val))
					$myObjVars[$key]=str_replace("\n","<br />\n",$val);

			///echo "Map de ".Tools::Display($myObjVars);
			$this->template->assign_vars
	        (
	            $myObjVars
	        );

		}
    }
}
?>