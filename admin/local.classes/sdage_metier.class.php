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
	const LISTMODE_NORMAL="";
	const LISTMODE_LIST="list";
	const LISTMODE_SHORTLIST="shortlist";
	const MESSAGE_SAISIE_NOTE_METHODE="<p><i style=\"color:#993300;\" class=\"fas fa-exclamation-circle\"></i>&nbsp;Indiquer qu'une pression est cause du risque implique une valeur d'impact de 2 ou 3</p>";
	const MESSAGE_MEMO_CONNEXION="MESSAGE_MEMO_CONNEXION";
	const MESSAGE_DSI_RGPD="message_rgpd.php";
	public $auth=null;
	public $sections_avec_menu=array("index","accueil","connexion","inscription","inscription_interdit","inscription_retour");
	public static $pagination=20;
	public static $extensions_autorisees=array("pdf","zip"); //jpg","jpeg","gif","png","pdf","doc","docx","zip","xls","xlsx");
	public static $formats_export=array(
		"default"=>array(
					"code_masse_eau"=> array("libelle"=>"Code masse eau","format"=>"text","formatavance"=>"","table"=>"SDG_MASSE_EAU","codechamp" => "ME_CODE"),
					"code_sous_bassin"=> array("libelle"=>"Code sous-bassin","format"=>"text","formatavance"=>"","table"=>"SDG_MASSE_EAU","codechamp" => "ME_CODE"),
					"code_sous_unite"=> array("libelle"=>"Code sous-unite","format"=>"text","formatavance"=>"","table"=>"SDG_MASSE_EAU","codechamp" => "ME_CODE"),
					"code_pression"=> array("libelle"=>"Code pression","format"=>"int","formatavance"=>"","table"=>"SDG_PRESSION","codechamp" => "PRE_CODE"),
					"nom_pression"=> array("libelle"=>"Nom pression","format"=>"text","formatavance"=>"","table"=>"SDG_PRESSION","codechamp" => "PRE_CODE"),
					"impact_2016"=> array("libelle"=>"Impact SDAGE 2016","format"=>"int","formatavance"=>"","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_2016"),
					"valeur_forcee_2016"=> array("libelle"=>"Valeur forcée 2016","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_FORCE_2016"),
					"rnabe_2021"=> array("libelle"=>"RNABE 2021","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_MASSE_EAU","codechamp" => "ME_RNABE_2021"),
					"pression_origine_risque_2021"=> array("libelle"=>"Pression origine risque 2021","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_ORIGINE_RISQUE_2021"),
					"impact_2019"=> array("libelle"=>"Impact EDL 2019","format"=>"int","formatavance"=>"","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_2019"),
					"rnabe_2027"=> array("libelle"=>"RNABE 2027","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_MASSE_EAU","codechamp" => "ME_RNABE_2027"),
					"pression_origine_risque_2027"=> array("libelle"=>"Pression origine risque 2027","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_ORIGINE_RISQUE_2027"),
					"code_avis"=> array("libelle"=>"Code avis","format"=>"int","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_CODE"),
					"pression_cause_du_risque"=> array("libelle"=>"Pression cause du risque","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_CAUSE_RISQUE"),
					"impact_estime"=> array("libelle"=>"Impact estimé","format"=>"int","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_IMPACT_ESTIME"),
					"NomStructure"=> array("libelle"=>"Structure","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_STRUCTURE"),
					"NomCreateur"=> array("libelle"=>"Nom","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_PERSONNE_NOM"),
					"PrenomCreateur"=> array("libelle"=>"Prénom","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_PERSONNE_PRENOM"),
					"validation"=> array("libelle"=>"Date","format"=>"datefr","formatavance"=>"JJ/MM/AAAA","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_DATE"),
					"commentaires"=> array("libelle"=>"Commentaire","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_COMMENTAIRE"),
					"documents" => array("libelle"=>"Documents","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_COMMENTAIRE"),
					"url_document" => array("libelle"=>"URL Documents","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_COMMENTAIRE")
					),
		"crea"=>array(
					"code_masse_eau"=> array("libelle"=>"Code masse eau","format"=>"text","formatavance"=>"","table"=>"SDG_MASSE_EAU","codechamp" => "ME_CODE"),
					"code_sous_bassin"=> array("libelle"=>"Code sous-bassin","format"=>"text","formatavance"=>"","table"=>"SDG_MASSE_EAU","codechamp" => "ME_CODE"),
					"code_sous_unite"=> array("libelle"=>"Code sous-unite","format"=>"text","formatavance"=>"","table"=>"SDG_MASSE_EAU","codechamp" => "ME_CODE"),
					"code_pression"=> array("libelle"=>"Code pression","format"=>"int","formatavance"=>"","table"=>"SDG_PRESSION","codechamp" => "PRE_CODE"),
					"nom_pression"=> array("libelle"=>"Nom pression","format"=>"text","formatavance"=>"","table"=>"SDG_PRESSION","codechamp" => "PRE_CODE"),
					"impact_2016"=> array("libelle"=>"Impact SDAGE 2016","format"=>"int","formatavance"=>"","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_2016"),
					//"valeur_forcee_2016"=> array("libelle"=>"Valeur forcée 2016","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_FORCE_2016"),
					"rnabe_2021"=> array("libelle"=>"RNABE 2021","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_MASSE_EAU","codechamp" => "ME_RNABE_2021"),
					"pression_origine_risque_2021"=> array("libelle"=>"Pression origine risque 2021","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_ORIGINE_RISQUE_2021"),
					"impact_2019"=> array("libelle"=>"Impact EDL 2019","format"=>"int","formatavance"=>"","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_2019"),
					"rnabe_2027"=> array("libelle"=>"RNABE 2027","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_MASSE_EAU","codechamp" => "ME_RNABE_2027"),
					"pression_origine_risque_2027"=> array("libelle"=>"Pression origine risque 2027","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_ORIGINE_RISQUE_2027"),
					"code_avis"=> array("libelle"=>"Code avis","format"=>"int","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_CODE"),
					"pression_cause_du_risque"=> array("libelle"=>"Pression cause du risque","format"=>"ouinon","formatavance"=>"1/0","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_CAUSE_RISQUE"),
					"impact_estime"=> array("libelle"=>"Impact estimé","format"=>"int","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_IMPACT_ESTIME"),
					"NomStructure"=> array("libelle"=>"Structure","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_STRUCTURE"),
					"NomCreateur"=> array("libelle"=>"Nom","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_PERSONNE_NOM"),
					"PrenomCreateur"=> array("libelle"=>"Prénom","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_PERSONNE_PRENOM"),
					"validation"=> array("libelle"=>"Date","format"=>"datefr","formatavance"=>"JJ/MM/AAAA","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_DATE"),
					"commentaires"=> array("libelle"=>"Commentaire","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_COMMENTAIRE"),
					"documents" => array("libelle"=>"Documents","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_COMMENTAIRE"),
					"url_document" => array("libelle"=>"URL Documents","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_COMMENTAIRE")
					),
		"coll" => array(
					"code_masse_eau"=> array("libelle"=>"Code masse eau","format"=>"text","formatavance"=>"","table"=>"SDG_MASSE_EAU","codechamp" => "ME_CODE"),
					"code_pression"=> array("libelle"=>"Code pression","format"=>"int","formatavance"=>"","table"=>"SDG_PRESSION","codechamp" => "PRE_CODE"),
					"impact_2016"=> array("libelle"=>"Impact SDAGE 2016","format"=>"int","formatavance"=>"","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_2016"),
					"valeur_forcee_2016"=> array("libelle"=>"Valeur forcée 2016","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_FORCE_2016"),
					"rnabe_2021"=> array("libelle"=>"RNABE 2021","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_MASSE_EAU","codechamp" => "ME_RNABE_2021"),
					"pression_origine_risque_2021"=> array("libelle"=>"Pression origine risque 2021","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_ORIGINE_RISQUE_2021"),
					"impact_2019"=> array("libelle"=>"Impact EDL 2019","format"=>"int","formatavance"=>"","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_IMPACT_2019"),
					"rnabe_2027"=> array("libelle"=>"RNABE 2027","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_MASSE_EAU","codechamp" => "ME_RNABE_2027"),
					"pression_origine_risque_2027"=> array("libelle"=>"Pression origine risque 2027","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_PROPOSITION_BASSIN","codechamp" => "PB_ORIGINE_RISQUE_2027"),
					"code_avis"=> array("libelle"=>"Code avis","format"=>"int","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_CODE"),
					"pression_cause_du_risque"=> array("libelle"=>"Pression cause du risque","format"=>"bool","formatavance"=>"1/0","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_CAUSE_RISQUE"),
					"impact_estime"=> array("libelle"=>"Impact estimé","format"=>"int","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_IMPACT_ESTIME"),
					"NomStructure"=> array("libelle"=>"Structure","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_STRUCTURE"),
					"NomCreateur"=> array("libelle"=>"Nom","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_PERSONNE_NOM"),
					"PrenomCreateur"=> array("libelle"=>"Prénom","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_PERSONNE_PRENOM"),
					"validation"=> array("libelle"=>"Date","format"=>"datefr","formatavance"=>"JJ/MM/AAAA","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_DATE"),
					"commentaires"=> array("libelle"=>"Commentaire","format"=>"text","formatavance"=>"","table"=>"SDG_AVIS_CONSULTATION","codechamp" => "AVS_COMMENTAIRE")
					)
	);
	var $TypesStructures=array(
				array("id"=>"","value" => "(choisir un type de structure)"),
				array("id"=>"Conseils départementaux","value" => "Conseils départementaux"),
				array("id"=>"Conseils régionaux","value" => "Conseils régionaux"),
				array("id"=>"EPTB, structures locales de gestion de l'eau","value" => "EPTB, structures locales de gestion de l'eau"),
				array("id"=>"Parcs nationaux et régionaux","value" => "Parcs nationaux et régionaux"),
				array("id"=>"Chambres d’agriculture","value" => "Chambres d’agriculture"),
				array("id"=>"Chambres de commerce et d’industrie","value" => "Chambres de commerce et d’industrie"),
				array("id"=>"Chambres des métiers et de l’artisanat","value" => "Chambres des métiers et de l’artisanat"),
				array("id"=>"Grands établissements industriels (EDF, CNR, BRL)","value" => "Grands établissements industriels (EDF, CNR, BRL)"),
				array("id"=>"Fédérations pour la pêche et la protection du milieu aquatique","value" => "Fédérations pour la pêche et la protection du milieu aquatique"),
				array("id"=>"Associations de protection de la nature, CREN et autres associations majeures éventuelles","value" => "Associations de protection de la nature, CREN et autres associations majeures éventuelles"),
				array("id"=>"Autre","value" => "Autre")
			);
	var $template_filenames=array(
		"accueil"=>"accueil.tpl",
		"inscription"=>"inscription.tpl",
		"inscription_interdit"=>"inscription_interdit.tpl",
		"inscription_retour"=>"inscription_retour.tpl",
		"connexion"=>"connexion.tpl",
		"panneau"=>"panneau.tpl",
		"panneaucollaborateur"=>"panneaucollaborateur.tpl",
		"formulaire-recherche"=>"formulaire-recherche.tpl",
		"searchresult"=>"searchresult.tpl",
		"searchresultlist"=>"searchresultlist.tpl",
		"searchresultshortlist"=>"searchresultshortlist.tpl",
		"fiche"=>"fiche.tpl",
		"detail-avis" => "detail-avis.tpl",
		"detail-pressions" => "detail-pressions.tpl",
		"detail-pressions-shortlist" => "detail-pressions-shortlist.tpl",
		"detail-pressions-list" => "detail-pressions-list.tpl"
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
		if(defined("RECHERCHE_PAGINATION")) self::$pagination=(int)RECHERCHE_PAGINATION;
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
			case "inscription":
				$this->handle_Inscription();
				break;
			case "avis":
				$this->handle_Avis();
				break;
			case "pdf":
				$this->handle_PDF();
				break;
			case "csv":
				$this->handle_CSV();
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
    		case "connexion":
    			$this->handle_Connexion();
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
    			$this->zoneDetail->titre="Informations sur le sous-bassin";
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
	
	public function handle_Avis()
	{
		global $ThePrefs;
		if(!isset($this->params["debug"])) $this->params["debug"]=0;
		//$action="$('#".$this->params["id_form_avis"]." label.sauvegardeerreur', window.parent.document).show();";
		$action="window.parent.frontCtl.triggerEventsSauvegardeErreur('#".$this->params["id_form_avis"]."');";
		$this->params["id_pression"]=intval($this->params["id_pression"]);
		$this->params["id_massedeau"]=intval($this->params["id_massedeau"]);
		$this->params["justification"]=trim($this->params["justification"]);
		$this->params["impact_estime"]=trim($this->params["impact_estime"]);
		$this->params["pression_cause_du_risque"]=trim($this->params["pression_cause_du_risque"]);
		if($this->params["id_pression"]<=0 
			|| $this->params["id_massedeau"]<=0 
			|| $this->params["impact_estime"]==""
			|| $this->params["justification"]=="")
		{
			$this->msg_info.="<script>alert(\"Erreur : paramètres incorrects\");</script>";
			return false;
		}
		
		if(!$this->auth->isLoaded() || $this->auth->user_Rank!="crea")
		{
			$this->msg_info.="<script>alert(\"Vous n'avez pas les droits d'écriture sur les avis : ".$this->auth->user_Rank."\");</script>";
			// Pas de sauvegarde de l'avis
			return false;
		}
		else
		{
			// TRaitement de la sauvegarde de l'avis
			$obj=new stdClass();
			$avis= mdtb_table::InitObject("mdtb_ae_avis");
			//file_put_contents(__DIR__."/creation-avis.log","Début création avis : ".$this->params["id_pression"]."/".$this->params["id_massedeau"]."/".$this->auth->user_ID."\r\n");
			$avis->recSQLSearch("ae_avis.id_pression=".$this->params["id_pression"]." AND ae_avis.id_massedeau=".$this->params["id_massedeau"]." AND ae_avis.id_user=".$this->auth->user_ID);
			if($avis->recFirst())
			{
				if($this->params["debug"]) file_put_contents(__DIR__."/creation-avis.log","Avis trouvé !\r\n",FILE_APPEND);
				$obj=$avis->recGetRecord();
				//file_put_contents(__DIR__."/creation-avis.log","Avis existant : ".print_r($obj,true),FILE_APPEND);
				if($obj->date_validation!=="0000-00-00 00:00:00")
				{
					$this->msg_info.="<script>alert(\"Erreur : avis déjà validé, il ne peut être modifié\");</script>";
					return false;
				}
				if($this->params["supprimerPJ"])
				{
					if($obj->documents!="")
					{
						$erreurSuppression=true;
						$fichierDocument=$ThePrefs->DocumentsFolder.$obj->documents;
						if(file_exists($fichierDocument))
						{
							if(is_writable($fichierDocument))
							{
								$obj->documents="";
								if($avis->recStore($obj))
								{
									if(unlink($fichierDocument))
									{
										$action="window.parent.frontCtl.triggerEventsPJSupprimee('#".$this->params["id_form_avis"]."');";
										$erreurSuppression=false;
									}
								}
							}
						}
						if($erreurSuppression)
						{
							$action="window.parent.frontCtl.triggerEventsErreurSuppressionPJ('#".$this->params["id_form_avis"]."');";
						}
						
						$this->msg_info.="<script>
							window.parent.frontCtl.triggerEventsSauvegarde('#".$this->params["id_form_avis"]."');
							".$action."	
						 </script>";
						die($this->msg_info);
					}
				}
			}
			else
			{
				if($this->params["debug"]) file_put_contents(__DIR__."/creation-avis.log","Nouvel avis !\r\n",FILE_APPEND);
				//file_put_contents(__DIR__."/creation-avis.log","Nouvel Avis",FILE_APPEND);
				$avis->recNewRecord();
			}
			
			$erreurPJ="";
			$obj->id_massedeau=$this->params["id_massedeau"];
			$obj->id_pression=$this->params["id_pression"]; //$arrPressions[$curData["Pression"]];
			$obj->id_user=$this->auth->user_ID; //$arrPressions[$curData["Pression"]];
			$obj->impact_estime=$this->params["impact_estime"];
			$obj->pression_cause_du_risque=$this->params["pression_cause_du_risque"];
			$obj->commentaires=$this->params["justification"];
			$obj->commentaires=str_replace("’","'",$obj->commentaires);
			$obj->commentaires=str_replace("œ","oe",$obj->commentaires);
			if(!isset($obj->documents)) $obj->documents="";
			$fichierTelecharge="";
			$nomDeBaseFichierTelecharge="";
			$hasPJ=false;
			if($this->params["debug"]) file_put_contents(__DIR__."/creation-avis.log","Document transmis : ".print_r($this->params["documents"],true),FILE_APPEND);
			if($this->params["debug"]) file_put_contents(__DIR__."/creation-avis.log","Fichiers transmis : ".print_r($_FILES,true),FILE_APPEND);
			if(isset($this->params["documents"]) && is_array($this->params["documents"]) && isset($this->params["documents"]["tmp_name"]))
			{
				if($this->params["debug"]) file_put_contents(__DIR__."/creation-avis.log","Document transmis test initial ok",FILE_APPEND);
				$path_parts=pathinfo($this->params["documents"]["name"]);
				$extension= strtolower($path_parts["extension"]);
				if(in_array($extension,self::$extensions_autorisees))
				{
					if($this->params["debug"]) file_put_contents(__DIR__."/creation-avis.log","Document transmis test extension ok",FILE_APPEND);
					// Traitement du fichier téléchargé
					$nomDeBaseFichierTelecharge=$this->params["documents"]["name"];
					$newFileName=$obj->id_massedeau."_".$obj->id_pression."_".$obj->id_user."-".$this->params["documents"]["name"];
					$fichierTelecharge=$ThePrefs->DocumentsFolder.$newFileName;
					$retourMove=move_uploaded_file($this->params["documents"]["tmp_name"], $ThePrefs->DocumentsFolder.$newFileName);
					if(!$retourMove) $erreurPJ="ERREUR lors de la sauvegarde du fichier ! Contactez un administrateur";
					$hasPJ=true;
					$obj->documents=$newFileName;
				}
				else
				{
					$erreurPJ="ERREUR pour import de la pièce jointe : extension interdite";
					//file_put_contents(__DIR__."/creation-avis.log","Extension interdite : ".$this->params["documents"]["name"]."\r\n",FILE_APPEND);
				}
			}
			$obj->date_modification=date('Y-m-d H:i:s');
			
			
			
			if($this->params["sauverAvis"])
			{
				$debug=defined("_DEBUG_UPLOAD_FICHIERS_")?_DEBUG_UPLOAD_FICHIERS_:false; //isset($this->param["debug"])?(bool)$this->param["debug"]:0;
				$retour=$avis->recStore($obj);
				if($debug) file_put_contents(__DIR__."/debug-fichier-dl.log","Retour : \r\n".print_r($obj,true));
				if($retour)
				{
					if($debug) file_put_contents(__DIR__."/debug-fichier-dl.log","docs : ".print_r($this->params["documents"],true).", fichier : $fichierTelecharge, nom de base : $nomDeBaseFichierTelecharge\r\n",FILE_APPEND);
					if($fichierTelecharge!="" && $nomDeBaseFichierTelecharge!="")
					{
						$newobj=$avis->recGetRecord();
						$newobj->documents=$newobj->id_avis."-".$nomDeBaseFichierTelecharge;
						if($debug) file_put_contents(__DIR__."/debug-fichier-dl.log","nouveau nom : ".$newobj->documents."\r\n",FILE_APPEND);
						if($debug) file_put_contents(__DIR__."/debug-fichier-dl.log","rename : ".$fichierTelecharge.",".$ThePrefs->DocumentsFolder.$newobj->documents."\r\n",FILE_APPEND);
						if(rename($fichierTelecharge,$ThePrefs->DocumentsFolder.$newobj->documents))
						{
							if($debug) file_put_contents(__DIR__."/debug-fichier-dl.log","rename OK\r\n",FILE_APPEND);
							$retourMAJDocument=$avis->recStore($newobj);
							$newobj2=$avis->recGetRecord();
							if($debug) file_put_contents(__DIR__."/debug-fichier-dl.log","retour rename : ".print_r($retourMAJDocument,true)."\r\nObjet : ".print_r($newobj2,true),FILE_APPEND);
						}
						else
						{
							if($debug) file_put_contents(__DIR__."/debug-fichier-dl.log","rename ERREUR\r\n",FILE_APPEND);
						}
					}
					$action="window.parent.frontCtl.triggerEventsSauvegarde('#".$this->params["id_form_avis"]."');";
				}
				else
				{
					if($fichierTelecharge!="")
					{
						unlink($fichierTelecharge);
					}
				}
			}
			if($this->params["validerAvis"])
			{
				//file_put_contents(__DIR__."/savepdf.log","Validation avis : ".$obj->id_avis."\r\n",FILE_APPEND);
				$obj->date_validation=date('Y-m-d H:i:s');
				if(false) $avis=new mdtb_ae_avis();
				$retour=$avis->recStore($obj);
				//file_put_contents(__DIR__."/savepdf.log","Validation avis sauvegardee\r\n",FILE_APPEND);
				if($retour) {
					//file_put_contents(__DIR__."/savepdf.log","Validation avis sauvegardee OK\r\n",FILE_APPEND);
					/*
					 * Sauvegarde du pdf et envoi du mail
					 */
					$this->params["id_avis"]=$avis->recKeyValue();
					//file_put_contents(__DIR__."/savepdf.log","Clef : ".$this->params["id_avis"]."\r\n",FILE_APPEND);
					$fichier=$this->handle_PDF(true);
					
					/*
					 * Dump avis après validation
					 */
					$this->dumpBaseAvis();
					$sujet = "Votre avis validé le ".date("d/m/Y")." sur la masse d'eau "." et la pression "."";
					$message = "Vous trouverez ci-joint le récipissé de validation d'avis ci-joint";
					//file_put_contents(__DIR__."/savepdf.log","Preparation envoi de mail :\r\nSujet : ".$sujet."\r\nFichiers : ".print_r($fichier,true)."\r\n",FILE_APPEND);
					Tools::PHPMailer($this->auth->user_Mail, $sujet, $message,array($fichier));
					/* Finalisation des actions de confirmation */
					//window.parent.$(document).trigger('complete');
					//$action="window.parent.$(document).trigger('valideravis','#".$this->params["id_form_avis"]."');";
					$action="window.parent.frontCtl.triggerEventsValidation('#".$this->params["id_form_avis"]."');";
					//$action="$('#".$this->params["id_form_avis"]." label.validationok', window.parent.document).show();";
					//$action.="$('#".$this->params["id_form_avis"]." input.boutonaction', window.parent.document).remove();";
					//$action.="$('#".$this->params["id_form_avis"]." input,#".$this->params["id_form_avis"]." textarea,#".$this->params["id_form_avis"]." select,', window.parent.document).disable();";
				}
			}
			$actionErreurPJ="";
			if($erreurPJ!=="") $actionErreurPJ="alert('".$erreurPJ."');";
			$jsHasPJ=($hasPJ?"window.parent.frontCtl.triggerHasPJ('#".$this->params["id_form_avis"]."')":"");
			$this->msg_info.="<script>
				window.parent.frontCtl.triggerEventsSauvegarde('#".$this->params["id_form_avis"]."');
				".$action."
				".$jsHasPJ."
				".$actionErreurPJ."
			 </script>";
			die($this->msg_info);
			//die("<script>alert('".$this->params["id_form_avis"]."');</script>");
			return false;
		}
	}
	
	private function dumpBaseAvis()
	{
		global $ThePrefs;
		global $db_user,$db_pass,$db_name,$db_host;
		if(file_exists($ThePrefs->DumpsFolder) && is_dir($ThePrefs->DumpsFolder) && is_writable($ThePrefs->DumpsFolder))
		{
			$fichierDump=$ThePrefs->DumpsFolder.$db_name."-".date("Y-m-H-i-s").".sql";
			return shell_exec("mysqldump -u ".$db_user." -p".$db_pass." ".$db_name." > ".$fichierDump);
		}
		return false;
			
	}
	
	public function handle_Inscription()
	{
		if(!isset($this->params["clef"]) || $this->params["clef"]!==_CLEF_INSCRIPTION_)
		{
			$this->msg_error="Clef incorrecte"; // : <pre>".$requeteME."</pre>";
			return false;
		}
		return true;
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
			if(!isset($arrPressions[$curData["code_pression"]])) // V2rification que l'id de pression existe dans le référentiel
			{
				$erreurPression=true;
				$this->msg_error.="Erreur Pression non trouvée : ".$curData["code_pression"]."<br />";
			}
		}
		//die(__LINE__." ERREUR ");
		if($erreurNomMDO ||$erreurPression)
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
		
		//file_put_contents(__DIR__."/import.log","Import en cours ".date("Y-m-d H:i:s")."\r\n");
		foreach($csv as $curData)
		{
			$obj=new stdClass();
			$edl->recSQLSearch("ae_edl_massesdeau.id_pression=".$curData["code_pression"]." AND ae_edl_massesdeau.id_massedeau=".$arrMassesDeau[$curData["code_masse_eau"]]);
			//file_put_contents(__DIR__."/import.log","Import pression ".$curData["code_pression"]." et mdo ".$arrMassesDeau[$curData["code_masse_eau"]]."... ",FILE_APPEND);
			if($edl->recFirst())
			{
				//file_put_contents(__DIR__."/import.log"," MISE A JOUR  ... ",FILE_APPEND);
				if(isset($this->params["skipupdate"]) && $this->params["skipupdate"]==="skip")
				{
					//file_put_contents(__DIR__."/import.log"," skip \r\n",FILE_APPEND);
					continue;
				}
				$obj=$edl->recGetRecord();
				//echo "Enregistrement retrouvé "."id_pression=".$arrPressions[$curData["Pression"]]." AND id_massedeau=".$arrMassesDeau[$curData["Code masse d'eau"]]." <pre>".print_r($obj,true)."</pre>";
			}
			else
			{
				//file_put_contents(__DIR__."/import.log"," CREATION ... ",FILE_APPEND);
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
			//file_put_contents(__DIR__."/import.log"," affectation valeurs ... ",FILE_APPEND);
			$retour=$edl->recStore($obj);
			//file_put_contents(__DIR__."/import.log"," sauvegarde : ".($retour?"ok":"erreur")."\r\n",FILE_APPEND);
			//echo "Enregistrement objet.<pre>".print_r($obj,true)."</pre><br />";
			$nbEDLMaj++;
		}
		//file_put_contents(__DIR__."/import.log","IMPORT TERMINE\r\n",FILE_APPEND);
		$this->msg_info.="<br />Mise à jour terminée avec : ".$nbEDLMaj." enregistrements<br />";
		//echo "Fichier ouvert, entetes : ".print_r($csv->getHeaders(),true)."<br />";
	}
    public function handle_Search($currentuser=false)
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
		if(!in_array($this->params["ssfield"],array("categorie_me","code_ssbv","libelle_me","code_me","code_ss_ut","code_ss_ut_sort")))
			$this->params["ssfield"]="code_me";
		$sort_order=$this->params["ssorder"]; 
		$sort_field=$this->params["ssfield"]; 
		if(isset($this->params["field_order"])) $sort_order=$this->params["field_order"];
		if(isset($this->params["field_sort"])) $sort_field=$this->params["field_sort"];
		//die(__LINE__." Params : ".print_r($this->params,true)." => requete : ".$mySQLOrder);
		$mySQLOrder=" ORDER BY ".$sort_field." ".$sort_order;
		$joinImpactOuPression="";
		
		if($this->liste_pressions!="" || ($currentuser && $this->auth->isLoaded()))
		{
			$joinImpactOuPression=" LEFT JOIN ae_edl_massesdeau AS edl ON edl.id_massedeau=mdo.id_massedeau ";
		}
		if($this->liste_impacts!="" || ($currentuser && $this->auth->isLoaded()))
		{
			$joinImpactOuPression=" LEFT JOIN ae_edl_massesdeau AS edl ON edl.id_massedeau=mdo.id_massedeau ";
		}
		$joinCreateur="";
		if($currentuser && $this->auth->isLoaded())
		{
			$joinCreateur=" RIGHT JOIN ae_avis AS avis ON (avis.id_user=".$this->auth->user_ID." AND avis.id_massedeau=mdo.id_massedeau AND avis.id_pression=edl.id_pression) ";
		}
		$requeteMEChampsListe="
			SELECT COUNT(*) AS nboccme,
			(SELECT COUNT(*) FROM ae_avis WHERE ae_avis.id_massedeau=mdo.id_massedeau) AS code_me_nb_avis,
			mdo.*,
			ssbv.*,
			ssut.*,
				IF(rsoutssut.code_ss_ut IS NOT NULL,rsoutssut.code_ss_ut,ssut.code_ss_ut) AS code_ss_ut,
				IF(rsoutssut.code_ss_ut IS NOT NULL,rsoutssut.code_ss_ut,ssut.code_ss_ut) AS code_ss_ut_sort";
		$requeteMEChampsCount="SELECT COUNT(*) AS nboccme ";
		$requeteME="
			FROM ae_massesdeau AS mdo
			LEFT JOIN ae_ssbv AS ssbv ON ssbv.code_ssbv=mdo.code_ssbv
			LEFT JOIN rel_me_sout_ss_ut AS rsoutssut ON mdo.code_me=rsoutssut.code_me
			LEFT JOIN ae_ss_ut AS ssut ON (ssbv.code_ss_ut=ssut.code_ss_ut OR rsoutssut.code_ss_ut=ssut.code_ss_ut)
			".$joinImpactOuPression."
			".$joinCreateur."
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
		
		if(isset($this->params["field_sort"])) $sortField=$this->params["field_sort"];
		if(isset($this->params["field_order"])) $sortOrder=$this->params["field_order"];
		
		
		$requeteME.=" GROUP BY mdo.id_massedeau ";
		$requeteME.=" ORDER BY ". $sortField . " ".$sortOrder." ";
		//die(__LINE__." Params : ".print_r($this->param,true)." => requete : ".$requeteME);
		/*file_put_contents(__DIR__."/derniere-recherche.log","Mémoire : ". memory_get_usage().
			"\r\nRequête count : \r\n".$requeteMEChampsCount.$requeteME."\r\n"
		);*/
		// Requete count : 
    	$this->db->setQuery($requeteMEChampsListe.$requeteME);
    	$this->db->query();
		$this->nbresultats=$this->db->getNumRows();
		
		$curpage=isset($this->params["pagination"])?intval($this->params["pagination"]):1;
		$curpage=($curpage<=0)?1:$curpage;
		if(($curpage-1)*self::$pagination>$this->nbresultats)
		{
			$curpage=1;
			$this->params["pagination"]=1;
		}
		$requeteMELimit=" LIMIT ".($curpage-1)*self::$pagination.",".self::$pagination;
		//file_put_contents(__DIR__."/derniere-recherche.log","Compte : ".$this->nbresultats."\r\nRequête resultats : \r\n".$requeteMEChampsListe.$requeteME.$requeteMELimit."\r\n",FILE_APPEND);
    	$this->db->setQuery($requeteMEChampsListe.$requeteME.$requeteMELimit);
    	$this->search_result=$this->db->loadObjectList();
		
		
    	if(!is_array($this->search_result) || count($this->search_result)<=0 || $this->search_result[0]->nboccme==0 )
		{
			$this->msg_info="Votre recherche n'a fourni aucun résultat<div style='display:none'>".$requeteMEChampsListe.$requeteME."</div>";
		}
		if(is_array($this->search_result) && count($this->search_result)>self::$pagination)
		{
			//$this->msg_info="Trop de résultats à votre recherche, veuillez ajouter un filtre. Sortie limitée à 200 résultats";
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
			case "inscription":
				$myContent=$this->sectionContent_Inscription();
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
	
	public function initSection($section=null)
	{
		if(is_null($section))
		{
			if($this->auth->isLoaded())
			{
				$this->setSection("panneau");
			}
			else
			{
				$this->setSection("connexion");
			}
		}
		else
		{
			$this->setSection($section);
		}
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

	public function handle_CSV()
	{
		$nomFichier="liste-avis-valides.csv";
		$isColl=false;
		if(!$this->auth->isLoaded()) die("Non authentifié");
		if($this->auth->user_Rank!="coll")
		{
			$filtreUser="WHERE a.date_validation!='0000-00-00 00:00:00' AND edl.id_pression=a.id_pression AND a.id_user=".$this->auth->user_ID;
		}
		else
		{
			$isColl=true;
			$filtreUser="WHERE a.date_validation!='0000-00-00 00:00:00' AND a.id_avis IS NOT NULL AND edl.id_pression=a.id_pression ";
			if(isset($this->params["mdo"]) && $this->params["mdo"]=="1")
			{
				$nomFichier="liste-masses-deau-et-avis.csv";
				$filtreUser="";
			}
		}
		/*
		code_masse_eau txt
		code_pression txt
		impact_2016 numerique 1/2/3
		valeur_forcee_2016 1/0 num
		rnabe_2021 1/0 num
		pression_origine_risque_2021 1/0 num
		impact_2019 numerique
		rnabe_2027 1/0 num
		pression_origine_risque_2027 1/0 num
		code_avis numerique
		pression_cause_du_risque 1/0 num
		impact_estime 1/2/3 num
		structure txt
		nom txt
		prenom txt
		date txt jj/mm/aaaa
		commentaire txt
		nom_piece_jointe TXT
		url_piece_jointe TXT
		*/
		$requete="
			SELECT 
				e.code_me AS code_masse_eau,
				e.code_ssbv AS code_sous_bassin,
				s.code_ss_ut AS code_sous_unite,
				p.id_pression AS code_pression,
				p.libelle_pression AS nom_pression,
				edl.impact_2016,
				".($isColl?"edl.impact_valeur_forcee AS valeur_forcee_2016,":"")."
				edl.rnaoe_2021 AS rnabe_2021,
				edl.pression_origine_2021 AS pression_origine_risque_2021,
				edl.impact_2016 AS impact_2019,
				edl.rnaoe_2027 AS rnabe_2027,
				edl.pression_origine_2027 AS pression_origine_risque_2027,
				a.id_avis AS code_avis,
				a.pression_cause_du_risque,
				a.impact_estime,
				u.user_NomStructure AS NomStructure,
				u.user_Name AS NomCreateur,
				u.user_FirstName AS PrenomCreateur,
				u.user_Structure AS TypeStructure,
				CONCAT(DAY(a.date_validation),'/',MONTH(a.date_validation),'/',YEAR(a.date_validation)) AS validation,
				a.commentaires,
				a.documents,
				CONCAT('http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["SCRIPT_NAME"])."/documents/',a.documents) AS url_document
			FROM ae_massesdeau AS e
			LEFT JOIN ae_ssbv AS s ON s.code_ssbv=e.code_ssbv
			LEFT JOIN ae_edl_massesdeau AS edl ON edl.id_massedeau=e.id_massedeau
			LEFT JOIN ae_pressions AS p ON edl.id_pression=p.id_pression
			LEFT JOIN ae_avis AS a ON (a.id_massedeau=e.id_massedeau AND a.id_pression=p.id_pression)
			LEFT JOIN mdtb_users AS u ON a.id_user=u.user_ID
			".$filtreUser."
		";
		$this->db->setQuery($requete);
		if(isset($this->params["sql"]) && $this->params["sql"]=="debug")
		{
			die($this->db->getQuery());
		}
		$liste=$this->db->loadObjectList();
		if(isset($this->params["dbgsql"])  && $this->params["dbgsql"]==1) die("Requete : ".$requete." => ".print_r($liste,true));
		$format=isset(self::$formats_export[$this->auth->user_Rank])?self::$formats_export[$this->auth->user_Rank]:self::$formats_export["default"];
		Tools::SendCSV($nomFichier, $liste,true,";",$format,true);
		die();
	}
	public function handle_PDF($save=false)
	{
		global $ThePrefs;
		//file_put_contents(__DIR__."/savepdf.log","Demarrage génération PDF\r\n",FILE_APPEND);
		if(!$this->auth->isLoaded()) die("Non authentifié");
		//file_put_contents(__DIR__."/savepdf.log","Authentifié\r\n",FILE_APPEND);
		if(isset($this->params["id_avis"]) && $this->params["id_avis"]>0)
		{
			//file_put_contents(__DIR__."/savepdf.log","ID OK\r\n",FILE_APPEND);
			if(false) $objAvis=new mdtb_ae_avis();
			$objAvis=mdtb_table::InitObject("mdtb_ae_avis");
			//file_put_contents(__DIR__."/savepdf.log","Chargement avis ".$this->params["id_avis"]." ...\r\n",FILE_APPEND);
			if(!$objAvis->recLoad((int)$this->params["id_avis"]))
			{
				//file_put_contents(__DIR__."/savepdf.log","ERREUR Chargement avis ".$this->params["id_avis"]." ... : ".mdtb_table::$_latest_query."\r\n",FILE_APPEND);
				die("ID Incorrect");
			}
			//file_put_contents(__DIR__."/savepdf.log","Chargement OK\r\n",FILE_APPEND);
			if($objAvis->recGetValue("id_user")!=$this->auth->user_ID)
			{
				die("Vous n'avez pas les droits");
			}
			//file_put_contents(__DIR__."/savepdf.log","User OK pour avis\r\n",FILE_APPEND);
		}
		
		$joinAvis=" RIGHT JOIN ae_avis ON (ae_avis.id_user=".$this->auth->user_ID." AND ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) ";
		
		$requeteSQL="
		SELECT ae_edl_massesdeau.*,1 as nbavis
		FROM ae_edl_massesdeau 
		".$joinAvis."
		WHERE ae_avis.id_avis=".$this->params["id_avis"]." 
		GROUP BY ae_edl_massesdeau.id_pression";
		//die($requeteSQL);
		$this->db->setQuery($requeteSQL);
		//file_put_contents(__DIR__."/savepdf.log","Recherche des avis : ".$this->db->getQuery()."\r\n",FILE_APPEND);
		$listeEdl=$this->db->loadObjectList();
		$detailPressions="Aucune pression pour cette masse d'eau"; //.$requeteSQL;

		$mdtbAvis= mdtb_table::InitObject("mdtb_ae_avis");
		//file_put_contents(__DIR__."/savepdf.log","Requete de recherche de l'avis  : nb de résultats : ".count($listeEdl)."\r\n",FILE_APPEND);
		if(is_array($listeEdl) && count($listeEdl)) //($edl->recFirst())
		{
			$arrPressions=$this->listePressions();
			$this->template->clear_block_var("pressions");
			foreach($listeEdl as $edl)  //do
			{
				if(!$this->authIsCollaborateur())
				{
					$edl->nbavis="-";
					$edl->impact_valeur_forcee="-";
				}
				else
				{
					$edl->impact_valeur_forcee=$edl->impact_valeur_forcee?"Oui":"Non";
				}

				$objAvis=$mdtbAvis->getAvisPourPressionMdo($edl->id_pression,$edl->id_massedeau);
				$arrMassesDeau=$this->listeMassesDeau();
				$arrSSBV=$this->listeSSBV("code");
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
				$icone_avis="fa-plus-circle";
				$tooltip_avis="Vous n'avez pas encore donné votre avis sur cette pression";
				if($objAvis->avis_valide=="avis_valide")
				{
					$icone_avis="fa-check-circle";
					$tooltip_avis="Vous avez validé l'avis donné sur cette pression";
				}
				elseif($objAvis->impact_estime!="")
				{
					$icone_avis="fa-edit";
					$tooltip_avis="Vous avez rédigé un avis sur cette pression mais il n'est pas encore validé";
				}
				
				$nom_et_structure="<h4 style='margin-top:0px;'>".$this->auth->user_FirstName." ".$this->auth->user_Name." - ".$this->auth->user_Structure."</h4>";
				//die("Boucle sur edl : ".print_r($listeEdl,true));
				$this->template->assign_vars
				(
					array
					(
						'nom_et_structure' => $nom_et_structure,
						'user_rank' => $this->auth->user_Rank,
						'id_avis' =>  $objAvis->id_avis,
						'code_me' =>  $arrMassesDeau[$edl->id_massedeau]->code_me,
						'id_massedeau' =>  $edl->id_massedeau,
						'libelle_me' => $arrMassesDeau[$edl->id_massedeau]->libelle_me,
						'categorie_me' => $arrMassesDeau[$edl->id_massedeau]->categorie_me,
						'code_ssbv' => $arrMassesDeau[$edl->id_massedeau]->code_ssbv,
						'libelle_ssbv' => $arrSSBV[$arrMassesDeau[$edl->id_massedeau]->code_ssbv]->libelle_ssbv,
						'id_pression' => $edl->id_pression,
						'libelle_pression' => $arrPressions[$edl->id_pression],
						'impact_2016' => $edl->impact_2016,
						'impact_valeur_forcee' => $edl->impact_valeur_forcee, //?"Oui":"Non",
						'impact_2019' => $edl->impact_2019,
						'rnaoe_2021' => $edl->rnaoe_2021?"Oui":"Non",
						'pression_origine_2021' => $edl->pression_origine_2021?"Oui":"Non",
						'rnaoe_2027' => $edl->rnaoe_2021?"Oui":"Non",
						'pression_origine_2027'=> $edl->pression_origine_2027?"Oui":"Non",
						"nbavis" => $edl->nbavis,
						"avis_valide"=>$objAvis->avis_valide,
						"class_avis_valide"=>$objAvis->avis_valide=="avis_valide"?"valide":"",
						"lbl_avis_valide"=>$objAvis->avis_valide=="avis_valide"?"Validé&nbsp;<i class=\"far fa-file-pdf\"></i>":"",
						"lien_avis_valide"=>("pdf.php?id_avis=".$objAvis->id_avis),
						"date_modification"=>date("d/m/Y",strtotime($objAvis->date_modification)),
						"date_validation"=>$objAvis->date_validation!="0000-00-00 00:00:00"?date("d/m/Y",strtotime($objAvis->date_validation)):"",
						"impact_estime"=>$objAvis->impact_estime,
						"icone_avis"=>$icone_avis,
						"tooltip_avis"=>$tooltip_avis,
						"pression_cause_du_risque"=>$objAvis->pression_cause_du_risque==1?"Oui":"Non",
						"justification"=>str_replace("\r\n","<br />\r\n",wordwrap($objAvis->commentaires,128,"<br />")),
						"lien_documents"=>$objAvis->lien_documents,
						"class_documents"=>trim($objAvis->documents)==""?"":"document",
						"CMB_PRESSION_CAUSE_DU_RISQUE" => $CMB_PRESSION_CAUSE_DU_RISQUE,
						"CMB_IMPACT_ESTIME" => $CMB_IMPACT_ESTIME
					)
				);
			} //while($edl->recNext());
			$detailPressions=$this->template->pparse("detail-avis",true);
		}
		if($this->params["html"]==1)
		{
			die($detailPressions);
		}
		//die($detailPressions);
		if($save)
		{
			$baseName="avis-valide-".$this->params["id_avis"].".pdf";
			//file_put_contents(__DIR__."/savepdf.log","Sauvegarde PDF : ".$baseName."\r\n",FILE_APPEND);
			$file=array("name"=>$baseName,"path"=>$ThePrefs->TmpPdfDir.$baseName);
			//file_put_contents(__DIR__."/savepdf.log","Fichier : ".print_r($file,true)."\r\n",FILE_APPEND);
			Tools::HTML2PDF($detailPressions,$file["path"],false);
			//file_put_contents(__DIR__."/savepdf.log","Retour HTML2PDF : ".(file_exists($file["path"])?"existe":"erreur")."\r\n",FILE_APPEND);
			return $file;
		}
		else
		{
			Tools::HTML2PDF($detailPressions,"avis-valide-".$this->params["id_avis"].".pdf");
			die();
		}
	}
	
	
	public function getUneLignePression()
	{
		// NON FINI ! NE PAS UTILISER
		//$edl=mdtb_table::InitObject("mdtb_ae_edl_massesdeau");
		$arrMassesDeau=$this->listeMassesDeau();
		$arrSSBV=$this->listeSSBV("code");
		$joinAvis="";
		$countAvis="(SELECT COUNT(*) FROM ae_avis WHERE ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) AS nbavis"; 
		if($this->auth->isLoaded())
		{
			$joinAvis=" RIGHT JOIN ae_avis ON (ae_avis.id_user=".$this->auth->user_ID." AND ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) ";
			$countAvis="(SELECT COUNT(*) FROM ae_avis WHERE ae_avis.id_user=".$this->auth->user_ID." AND ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) AS nbavis"; 
		}
		//$edl->recSQLSearch($requeteSearch);
		$requeteSQL="
		SELECT ae_edl_massesdeau.*,
		".$countAvis."
		FROM ae_edl_massesdeau 
		".$joinAvis."
		WHERE ".$requeteSearch."
		GROUP BY ae_edl_massesdeau.id_pression";
		//die($requeteSQL);
		$this->db->setQuery($requeteSQL);
		$listeEdl=$this->db->loadObjectList();
		$detailPressions="Aucune pression pour cette masse d'eau"; //.$requeteSQL;

		foreach($this->search_result as $curme)
		{




			$mdtbAvis= mdtb_table::InitObject("mdtb_ae_avis");
			if(is_array($listeEdl) && count($listeEdl)) //($edl->recFirst())
			{
				$arrPressions=$this->listePressions();
				$this->template->clear_block_var("pressions");
				foreach($listeEdl as $edl)  //do
				{
					if(!$this->authIsCollaborateur())
					{
						$edl->nbavis="-";
						$edl->impact_valeur_forcee="-";
					}
					else
					{
						$edl->impact_valeur_forcee=$edl->impact_valeur_forcee?"Oui":"Non";
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
					$icone_avis="fa-plus-circle";
					$tooltip_avis="Vous n'avez pas encore donné votre avis sur cette pression";
					if($objAvis->avis_valide=="avis_valide")
					{
						$icone_avis="fa-check-circle";
						$tooltip_avis="Vous avez validé l'avis donné sur cette pression";
					}
					elseif($objAvis->impact_estime!="")
					{
						$icone_avis="fa-edit";
						$tooltip_avis="Vous avez rédigé un avis sur cette pression mais il n'est pas encore validé";
					}
					//die("Boucle sur edl : ".print_r($listeEdl,true));
					$this->template->assign_vars
					(
						array
						(

							'id_avis' =>  $objAvis->id_avis,
							'code_me' =>  $arrMassesDeau[$edl->id_massedeau]->code_me,
							'id_massedeau' =>  $edl->id_massedeau,
							'libelle_me' => $arrMassesDeau[$edl->id_massedeau]->libelle_me,
							'categorie_me' => $arrMassesDeau[$edl->id_massedeau]->categorie_me,
							'code_ssbv' => $arrMassesDeau[$edl->id_massedeau]->code_ssbv,
							'libelle_ssbv' => $arrSSBV[$arrMassesDeau[$edl->id_massedeau]->code_ssbv]->libelle_ssbv,
							'id_pression' => $edl->id_pression,
							'libelle_pression' => $arrPressions[$edl->id_pression],
							'impact_2016' => $edl->impact_2016,
							'impact_valeur_forcee' => $edl->impact_valeur_forcee, //?"Oui":"Non",
							'impact_2019' => $edl->impact_2019,
							'rnaoe_2021' => $edl->rnaoe_2021?"Oui":"Non",
							'pression_origine_2021' => $edl->pression_origine_2021?"Oui":"Non",
							'rnaoe_2027' => $edl->rnaoe_2021?"Oui":"Non",
							'pression_origine_2027'=> $edl->pression_origine_2027?"Oui":"Non",
							"nbavis" => $edl->nbavis,
							"avis_valide"=>$objAvis->avis_valide,
							"lbl_avis_valide"=>$objAvis->avis_valide=="avis_valide"?"Validé&nbsp;<i class=\"far fa-file-pdf\"></i>":"En cours d'édition",
							"lien_avis_valide"=>$objAvis->avis_valide=="avis_valide"?("pdf.php?id_avis=".$objAvis->id_avis):"#",
							"date_modification"=>date("d/m/Y",strtotime($objAvis->date_modification)),
							"date_validation"=>$objAvis->date_validation!="0000-00-00 00:00:00"?date("d/m/Y",strtotime($objAvis->date_validation)):"",
							"impact_estime"=>$objAvis->impact_estime,
							"icone_avis"=>$icone_avis,
							"tooltip_avis"=>$tooltip_avis,
							"pression_cause_du_risque"=>$objAvis->pression_cause_du_risque,
							"justification"=>$objAvis->commentaires,
							"justification_length"=>strlen($objAvis->commentaires),
							"lien_documents"=>$objAvis->lien_documents,
							"class_documents"=>trim($objAvis->documents)==""?"":"document",
							"CMB_PRESSION_CAUSE_DU_RISQUE" => $CMB_PRESSION_CAUSE_DU_RISQUE,
							"CMB_IMPACT_ESTIME" => $CMB_IMPACT_ESTIME
						)
					);
				}
				$detailPressions=$this->template->pparse("ligne-pression.tpl",true);
			}
		}
				
	}
	
	
    function sectionContent_Search($listmode=self::LISTMODE_NORMAL,$currentuser=false)
    {
		$indexparite=0;
    	$this->prepareForm();
    	if(!is_array($this->search_result) || count($this->search_result)<=0)
    	{
			if($listmode==self::LISTMODE_NORMAL)
			{
				return $this->template->pparse("accueil",true);
			}
			else
			{
				if($this->section=="panneau")
				{
					return "Vous n'avez pas encore saisi d'avis"; //Aucun résultat";
				}
				else
				{
					return "Aucun résultat";
				}
			}
    	}
    	else
    	{
			//$edl=mdtb_table::InitObject("mdtb_ae_edl_massesdeau");
			$arrMassesDeau=$this->listeMassesDeau();
			$arrSSBV=$this->listeSSBV("code");
			
			$nb_pages=ceil($this->nbresultats/self::$pagination);
			if(!isset($this->params["pagination"])) $this->params["pagination"]=1;
			$arrPages=array();
			for($i=1;$i<=$nb_pages;$i++) $arrPages[]=array("id"=>$i,"value"=>$i);
			$cmbPagination= mdtb_forms::combolist("pagination",$arrPages,$this->params["pagination"],"","onchange='form.submit();'");
			$cmbPagination='<div class="pagination_resultat">Page&nbsp;:&nbsp;'.$cmbPagination.'&nbsp;/&nbsp;'.$nb_pages.'</div>';
			$this->template->assign_var("CMB_PAGINATION", $cmbPagination);
			$this->template->assign_var("nb_pages", $nb_pages);
			$this->template->assign_var("MESSAGE_SAISIE_NOTE_METHODE", self::MESSAGE_SAISIE_NOTE_METHODE);
			
			$nbaffiche=0;
    		foreach($this->search_result as $curme)
    		{
				$nbaffiche++;
				if($nbaffiche>self::$pagination) break;
				
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
				$joinAvis="";
				$countAvis="(SELECT COUNT(*) FROM ae_avis WHERE ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) AS nbavis"; 
				if($currentuser && $this->auth->isLoaded())
				{
					$joinAvis=" RIGHT JOIN ae_avis ON (ae_avis.id_user=".$this->auth->user_ID." AND ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) ";
					$countAvis="(SELECT COUNT(*) FROM ae_avis WHERE ae_avis.id_user=".$this->auth->user_ID." AND ae_avis.id_massedeau=ae_edl_massesdeau.id_massedeau AND ae_avis.id_pression=ae_edl_massesdeau.id_pression) AS nbavis"; 
				}
				//$edl->recSQLSearch($requeteSearch);
				$requeteSQL="
				SELECT ae_edl_massesdeau.*,
				".$countAvis."
				FROM ae_edl_massesdeau 
				".$joinAvis."
				WHERE ".$requeteSearch."
				GROUP BY ae_edl_massesdeau.id_pression";
				//die($requeteSQL);
				$this->db->setQuery($requeteSQL);
				$listeEdl=$this->db->loadObjectList();
				$detailPressions="Aucune pression pour cette masse d'eau"; //.$requeteSQL;
				
				$mdtbAvis= mdtb_table::InitObject("mdtb_ae_avis");
				if(is_array($listeEdl) && count($listeEdl)) //($edl->recFirst())
				{
					$arrPressions=$this->listePressions();
					$this->template->clear_block_var("pressions");
					foreach($listeEdl as $edl)  //do
					{
						if(!$this->authIsCollaborateur())
						{
							$edl->nbavis="-";
							$edl->impact_valeur_forcee="-";
						}
						else
						{
							$edl->impact_valeur_forcee=$edl->impact_valeur_forcee?"Oui":"Non";
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
						$icone_avis="fa-plus-circle";
						$classe_avis="acreer";
						$tooltip_avis="Vous n'avez pas encore donné votre avis sur cette pression";
						if($objAvis->avis_valide=="avis_valide")
						{
							$classe_avis="valide";
							$icone_avis="fa-check-circle";
							$tooltip_avis="Vous avez validé l'avis donné sur cette pression";
						}
						elseif($objAvis->impact_estime!="")
						{
							$classe_avis="edition";
							$icone_avis="fa-edit";
							$tooltip_avis="Vous avez rédigé un avis sur cette pression mais il n'est pas encore validé";
						}
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
								'impact_valeur_forcee' => $edl->recGetValue("impact_valeur_forcee")?"Oui":"Non",
								'impact_2019' => $edl->recGetValue("impact_2019"),
								'rnaoe_2021' => $edl->recGetValue("rnaoe_2021")?"Oui":"Non",
								'pression_origine_2021' => $edl->recGetValue("pression_origine_2021")?"Oui":"Non",
								'rnaoe_2027' => $edl->recGetValue("rnaoe_2021")?"Oui":"Non",
								'pression_origine_2027'=> $edl->recGetValue("pression_origine_2027")?"Oui":"Non",
								*/
								'id_avis' =>  $objAvis->id_avis,
								'code_me' =>  $arrMassesDeau[$edl->id_massedeau]->code_me,
								'id_massedeau' =>  $edl->id_massedeau,
								'libelle_me' => $arrMassesDeau[$edl->id_massedeau]->libelle_me,
								'categorie_me' => $arrMassesDeau[$edl->id_massedeau]->categorie_me,
								'code_ssbv' => $arrMassesDeau[$edl->id_massedeau]->code_ssbv,
								'libelle_ssbv' => $arrSSBV[$arrMassesDeau[$edl->id_massedeau]->code_ssbv]->libelle_ssbv,
								'id_pression' => $edl->id_pression,
								'libelle_pression' => $arrPressions[$edl->id_pression],
								'impact_2016' => $edl->impact_2016,
								'impact_valeur_forcee' => $edl->impact_valeur_forcee, //?"Oui":"Non",
								'impact_2019' => $edl->impact_2019,
								'rnaoe_2021' => $edl->rnaoe_2021?"Oui":"Non",
								'pression_origine_2021' => $edl->pression_origine_2021?"Oui":"Non",
								'rnaoe_2027' => $edl->rnaoe_2021?"Oui":"Non",
								'pression_origine_2027'=> $edl->pression_origine_2027?"Oui":"Non",
								"nbavis" => $edl->nbavis,
								"avis_valide"=>$objAvis->avis_valide,
								"lbl_avis_valide"=>$objAvis->avis_valide=="avis_valide"?"Validé&nbsp;<i class=\"far fa-file-pdf\"></i>":"En cours d'édition",
								"lien_avis_valide"=>$objAvis->avis_valide=="avis_valide"?("pdf.php?id_avis=".$objAvis->id_avis):"#",
								"class_avis_valide"=>$classe_avis, //$objAvis->avis_valide=="avis_valide"?"valide":"",
								"date_modification"=>date("d/m/Y",strtotime($objAvis->date_modification)),
								"date_validation"=>$objAvis->date_validation!="0000-00-00 00:00:00"?date("d/m/Y",strtotime($objAvis->date_validation)):"",
								"impact_estime"=>$objAvis->impact_estime,
								"icone_avis"=>$icone_avis,
								"tooltip_avis"=>$tooltip_avis,
								"pression_cause_du_risque"=>$objAvis->pression_cause_du_risque,
								"justification"=>$objAvis->commentaires,
								"justification_length"=>strlen($objAvis->commentaires),
								"lien_documents"=>$objAvis->lien_documents,
								"class_documents"=>trim($objAvis->documents)==""?"":"document",
								"CMB_PRESSION_CAUSE_DU_RISQUE" => $CMB_PRESSION_CAUSE_DU_RISQUE,
								"CMB_IMPACT_ESTIME" => $CMB_IMPACT_ESTIME
							)
						);
					} //while($edl->recNext());
					$detailPressions=$this->template->pparse("detail-pressions".($listmode!=""?"-":"").$listmode,true);
				}
				
    			$this->template->assign_block_vars
	            (
	                'tablecontent',
	                array
	                (
	                	'code_me' => $curme->code_me,
	                	'code_me_nb_avis' => $curme->code_me_nb_avis,
	                	'lbl_nb_avis_mdo' => ( ($curme->code_me_nb_avis>0 && $this->authIsCollaborateur())?("&nbsp;(".$curme->code_me_nb_avis."&nbsp;avis)"):""),
						'line_odd_even' => "parite".$indexparite++%2,
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
			$formulaireRecherche=$this->template->pparse("formulaire-recherche",true);
			$this->template->assign_var("FORMULAIRE_RECHERCHE",$formulaireRecherche);
    		return $this->template->pparse("searchresult".$listmode,true);
    		
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
    	
		$this->template->assign_vars(array("DONNEES_LISTE_SS_UT"=>json_encode($ssut->recGetAllRecordsAssArrayOfObjects())));
		$this->template->assign_vars(array("DONNEES_LISTE_SSBV"=>json_encode($ssbv->recGetAllRecordsAssArrayOfObjects())));
		
		$pressions= mdtb_table::InitObject("mdtb_ae_pressions");
		$comboPressions=$pressions->htmlGetComboMultiple("liste_pressions","id_pression","libelle_pression","1",$this->params["liste_pressions"]);
    	$this->template->assign_vars(array("CMB_PRESSIONS"=>$comboPressions));
    	$arrImpacts=array(); //array("id"=>"","value"=>""));
		for($i=1;$i<=3;$i++) { $arrImpacts[]=array("id"=>$i,"value"=>$i); }
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
		if(is_array($this->params["liste_typesmdo"] )&& count($this->params["liste_typesmdo"]))
		{
			foreach($this->params["liste_typesmdo"] as &$clef)
			{
				$clef= stripslashes($clef);
			}
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
		if(!isset($this->params["field_order"])) $this->params["field_order"]="ASC";
		$this->template->assign_vars(array("field_order"=>$this->params["field_order"]));
		if(!isset($this->params["field_sort"])) $this->params["field_sort"]="code_me";
		$this->template->assign_vars(array("field_sort"=>$this->params["field_sort"]));
		//echo "<pre>".print_r($this->params,true)."</pre>";
		$formulaireRecherche=$this->template->pparse("formulaire-recherche",true);
		$this->template->assign_var("FORMULAIRE_RECHERCHE",$formulaireRecherche);		
	}
	
	public function sectionContent_Connexion()
	{
		$this->prepareForm();
		$this->template->assign_vars(array("FORM_CONNEXION_PAGE"=>$this->path_pre));
		$this->template->assign_vars(array("FORM_RETURN_URL"=>"referer"));
		//$this->template->assign_vars(array("MESSAGE_MEMO_CONNEXION"=>self::MESSAGE_MEMO_CONNEXION));
		
		
        return $this->template->pparse("connexion",true);
	}
	
	public function sectionContent_Panneau()
	{
		if($this->auth->user_Rank=="coll")
		{
			return $this->template->pparse("panneaucollaborateur",true);
			
		}
		
		if($this->auth->user_Rank=="crea")
		{

			//$this->SendMailTest();
			$this->prepareForm();
			$this->template->assign_vars(array("FORM_CONNEXION_PAGE"=>$this->path_pre));
			$this->template->assign_vars(array("FORM_RETURN_URL"=>"referer"));


			$arrTypeStructure=$this->TypesStructures;
			$cmbTypeStructure= mdtb_forms::combolist("type_structure",$arrTypeStructure,$this->auth->user_Structure);
			
			$this->handle_Search(true); // Création de la recherche sur les avis de l'utilisateur courant
			$resultats=$this->sectionContent_Search(self::LISTMODE_SHORTLIST,true);

			$this->template->assign_vars(array(
					"user_ID" => $this->auth->user_ID,
					"user_name" => $this->auth->user_Name,
					"user_firstname" => $this->auth->user_FirstName,
					"user_email" => $this->auth->user_Mail,
					"user_nomstructure" => $this->auth->user_NomStructure,
					"resultats" => $resultats,
					"CMB_TYPE_STRUCTURE"=>$cmbTypeStructure
			));

			//print_r($this->search_result);
			return $this->template->pparse("panneau",true);
			
		}
	}
	
    public function sectionContent_Accueil()
    {
    	$this->prepareForm();
        return $this->template->pparse("accueil",true);
    }
	
	
    public function sectionContent_Inscription()
    {
		//$this->template->assign_var("MESSAGE_MEMO_CONNEXION",self::MESSAGE_MEMO_CONNEXION);
		$this->template->assign_var("MESSAGE_DSI_RGPD",file_get_contents(_APP_ROOT_DIR_.dirname($_SERVER["SCRIPT_NAME"])."/".self::MESSAGE_DSI_RGPD));
		if(!isset($this->params["clef"]) || $this->params["clef"]!==_CLEF_INSCRIPTION_)
		{
			$this->msg_error="Clef incorrecte"; // : <pre>".$requeteME."</pre>";
			return $this->template->pparse("inscription_interdit",true);
		}
		$this->template->assign_var("CLEF_TRANSMISE", $this->params["clef"]);
		if(!isset($this->params["inscription"]))
		{
			$arrTypeStructure=$this->TypesStructures;
			$cmbTypeStructure= mdtb_forms::combolist("type_structure",$arrTypeStructure,$this->auth->user_Structure);
			$this->template->assign_var("CMB_TYPE_STRUCTURE",$cmbTypeStructure);
			return $this->template->pparse("inscription",true);
		}
		/*
		 * Test paramètres d'entrée
		 */
		$retourTest="";
		$paramsTest=array("user_name"=>"Nom","user_firstname"=>"Prénom","user_email"=>"EMail","user_nomstructure"=>"Nom de la structure","type_structure"=>"Type de structure","user_password" => "Mot de passe");
		foreach($paramsTest as $key => $label)
		{
			if(!isset($this->params[$key]) || trim($this->params[$key])==="")
			{
				$retourTest.="Le champ ".$label." est incorrect<br />";
			}
			else
			{
				$this->params[$key]=addslashes($this->params[$key]);
			}
		}
		$this->template->assign_var("user_name",$this->params["user_name"]);
		$this->template->assign_var("user_firstname",$this->params["user_firstname"]);
		$this->template->assign_var("user_email",$this->params["user_email"]);
		$this->template->assign_var("user_nomstructure",$this->params["user_nomstructure"]);
		//$this->template->assign_var("type_structure",$this->params["type_structure"]);
		
		
		$arrTypeStructure=$this->TypesStructures;
		$cmbTypeStructure= mdtb_forms::combolist("type_structure",$arrTypeStructure,$this->params["type_structure"]);
		$this->template->assign_var("CMB_TYPE_STRUCTURE",$cmbTypeStructure);
		
		if($retourTest!="")
		{
			$this->template->assign_var("MSG_ERREUR","<div class='msg_erreur'>".$retourTest."</div>");
			//echo __LINE__."<br />";
			return $this->template->pparse("inscription",true);
		}
		if(false) $objUser=new mdtb_users();
		$objUser=mdtb_table::InitObject("mdtb_users");
		$objUser->recSQLSearch("user_Mail='".$this->params["user_email"]."'");
		if($objUser->recCount()>0)
		{
			//die(__LINE__." => tests : ".print_r($this->params,true));
			$this->template->assign_var("MSG_ERREUR","<div class='msg_erreur'>Un compte existe déjà avec cet email</div>");
			return $this->template->pparse("inscription",true);
		}
		$objNewUser=new stdClass();
		$objNewUser->group_ID=4;
		$objNewUser->user_Login=$this->params["user_email"];
		$objNewUser->user_Name=$this->params["user_name"];
		$objNewUser->user_FirstName=$this->params["user_firstname"];
		$objNewUser->user_Mail=$this->params["user_email"];
		$objNewUser->user_Password=md5(trim($this->params["user_password"]));
		$objNewUser->user_NomStructure=$this->params["user_nomstructure"];
		$objNewUser->user_Structure=$this->params["type_structure"];
		$objNewUser->user_Rank="crea";
		if($objUser->recStore($objNewUser))
		{
			$this->SendMailInscriptionCreateur($objNewUser);
			return $this->template->pparse("inscription_retour",true);
		}
		$this->template->assign_var("MSG_ERREUR","<div class='msg_erreur'>Un problème est survenu à la création du compte, merci de contacter le webmaster</div>");
        return $this->template->pparse("inscription_retour",true);
    }
	
	private function SendMailTest()
	{
		global $ThePrefs;
		//echo __LINE__." => Mail test ...";
		if(false) $objUsers=new mdtb_users();
		$objUsers=mdtb_table::InitObject("mdtb_users");
		$objUsers->recSQLSearch("mdtb_users.group_ID=".(int)$ThePrefs->AdminGroupPourAlertesMails);
		//echo __LINE__." => recherche "."group_ID=".(int)$ThePrefs->AdminGroupPourAlertesMails." ... ".mdtb_table::$_latest_query."<br />";
		$subject="Consultations 2018 : un nouveau créateur vient de s'inscrire";
		$message="Inscription d'un nouveau créateur : \r\n";
		if($objUsers->recCount())
		{
			//echo __LINE__." => nb envois ".$objUsers->recCount()." ...";
			$objUsers->recFirst();
			do
			{
				$to=$objUsers->recGetValue("user_Mail");
				if(trim($to)!="")
				{
					//echo __LINE__." => envoi vers ".$to." ...";
					Tools::PHPMailer($to,$subject,$message);
				}
			} while($objUsers->recNext());
		}
	}
	
	public function getUrlConnexion($absolute=true)
	{
		if($absolute)
		{
			return "https://".$_SERVER["HTTP_HOST"]."/". dirname($_SERVER["SCRIPT_NAME"])."/connexion.php";
		}
		else
		{
			return "/".basename($_SERVER["SCRIPT_NAME"])."/connexion.php";
		}
	}
	
	private function SendMailInscriptionCreateur($user)
	{
		global $ThePrefs;
		$mailFrom=$ThePrefs->From; //="webmaster@rhone-mediterranee.eaufrance.fr";
		$nameFrom=$ThePrefs->FromName; //="Webmaster SIE";
		$subject="Consultations 2018 : un nouveau créateur vient de s'inscrire";
		
		$subjectPourCreateur="Création de compte pour la consultation 2018 : confirmation de votre création de compte";
		
		$messagePourCommun="<ul><li><b>Nom : </b>".$user->user_Name."</li>\r\n".
		"<li><b>Prénom :</b>".$user->user_FirstName."</li>\r\n".
		"<li><b>Email :</b>".$user->user_Mail."</li>\r\n".
		"<li><b>Type de structure :</b>".$user->user_Structure."<</li>\r\n".
		"<li><b>Nom de la structure :</b>".$user->user_NomStructure."</li>\r\n".
		"<li><b>Date inscription :</b>".date("d/m/Y")."</li></ul>";
		
		$message="<b>Inscription d'un nouveau créateur :</b><br />\r\n".
		$message=$message.$messagePourCommun;
		
		$messagePourCreateur="<b>Confirmation de création de compte : </b><br />\r\n".$messagePourCommun."<br />\r\n".
			"Pour déposer un avis, rendez-vous sur le lien suivant : <a href='".$this->getUrlConnexion()."'>Déposer un avis</a><br />\r\n".
			"Utilisez l'adresse e-mail utilisée en tant qu'identifiant pour accéder au service : <b>".$user->user_Mail."</b><br />\r\n".
			"En cas de problème lors de votre connexion contactez Fabienne Barratier à l'adresse <a href='mailto:Fabienne.BARRATIER@eaurmc.fr'>Fabienne.BARRATIER@eaurmc.fr</a><br />\r\n===========<br />\r\n".
			"<hr />".file_get_contents(_APP_ROOT_DIR_.dirname($_SERVER["SCRIPT_NAME"])."/contenu_avertissement_saisie.php");
		
		Tools::PHPMailer($user->user_Mail,$subjectPourCreateur,$messagePourCreateur);
		//echo __LINE__." => Mail test ...";
		if(false) $objUsers=new mdtb_users();
		$objUsers=mdtb_table::InitObject("mdtb_users");
		$objUsers->recSQLSearch("mdtb_users.group_ID=".(int)$ThePrefs->AdminGroupPourAlertesMails);
		if($objUsers->recCount())
		{
			//echo __LINE__." => nb envois ".$objUsers->recCount()." ...";
			$objUsers->recFirst();
			do
			{
				$to=$objUsers->recGetValue("user_Mail");
				if(trim($to)!="")
				{
					//echo __LINE__." => envoi vers ".$to." ...";
					Tools::PHPMailer($to,$subject,$message);
				}
			} while($objUsers->recNext());
		}
		
	}
	
    public function handle_Connexion()
    {
		if(!$this->auth->isLoaded()) return false;
		if(isset($this->params["user_ID"]) && $this->params["user_ID"]>=0 && $this->params["user_ID"]!=$this->auth->user_ID)
		{
			die("Incohérence au niveau des utilisateurs");
			return false;
		}
		//die("Ligne ... ".__LINE__." auth : ".($auth->isLoaded()?"connecte":"deconnecte")."<br />\r\n".print_r($auth,true));
		if(!isset($this->params["miseajour"]) || $this->params["miseajour"]=="") return false;
		$this->setSection("panneau");
		$modif=false;
		if($this->params["user_name"]!="" && $this->params["user_name"]!=$this->auth->user_Name)
		{
			$this->auth->user_Name=$this->params["user_name"];
			$modif=true;
		}
		
		if($this->params["user_email"]!="" && $this->params["user_email"]!=$this->auth->user_Mail)
		{
			$this->auth->user_Mail=$this->params["user_email"];
			$this->auth->user_Login=$this->params["user_email"];
			$modif=true;
		}
		
		
		if($this->params["user_firstname"]!="" && $this->params["user_firstname"]!=$this->auth->user_FirstName)
		{
			$this->auth->user_FirstName=$this->params["user_firstname"];
			$modif=true;
		}
		
		
		if($this->params["user_nomstructure"]!="" && $this->params["user_nomstructure"]!=$this->auth->user_NomStructure)
		{
			$this->auth->user_NomStructure=$this->params["user_nomstructure"];
			$modif=true;
		}
		
		
		if($this->params["type_structure"]!="" && $this->params["type_structure"]!=$this->auth->user_Structure)
		{
			$this->auth->user_Structure=$this->params["type_structure"];
			$modif=true;
		}
		
		
		if($this->params["user_password"]===$this->params["user_password2"] && $this->params["user_password"]!="" && md5($this->params["user_password"]) != $this->auth->user_Password)
		{
			$this->auth->user_Password=md5($this->params["user_password"]);
			$modif=true;
		}
		
		if($modif)
		{
			return $this->auth->store();
		}	
		
		return false;
    }
	
	
	
    public function sectionContent_Fiche()
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