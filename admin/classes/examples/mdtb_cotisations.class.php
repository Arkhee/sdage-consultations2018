<?php
/*
ALTER TABLE `brgm_activites` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_adherents` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_annees_cotisation` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_associations` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_banques` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_cotisations` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_entreprises` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_lexique_options` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_liste_tarifaire` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_mode_reglement` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_reglement_type` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_rel_adherents_activites` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_rel_adherents_associations` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_rel_options_cotisants` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

ALTER TABLE `brgm_statut_reglement` ADD `user_ID` INT( 11 ) NOT NULL FIRST ,ADD `group_ID` INT( 11 ) NOT NULL AFTER `user_ID` ;

 */

/* Ajout de champs suite à réunion
ALTER TABLE `brgm_cotisations` ADD `annee_concernee` INT( 11 ) NOT NULL AFTER `id_cotisations` ;
ALTER TABLE `brgm_cotisations` ADD INDEX ( `annee_concernee` ) ;
ALTER TABLE `brgm_cotisations` ADD `cotis_lexique_id_lexique_types` INT( 11 ) NOT NULL ;
ALTER TABLE `brgm_cotisations` ADD INDEX ( `cotis_lexique_id_lexique_types` ) ;
ALTER TABLE `brgm_liste_tarifaire` ADD `liste_lexique_id_lexique_types` INT( 11 ) NOT NULL ;
ALTER TABLE `brgm_liste_tarifaire` ADD INDEX ( `liste_lexique_id_lexique_types` ) ;
ALTER TABLE `brgm_cotisations` ADD `cotis_lexique_id_lexique_options` INT( 11 ) NOT NULL ;
ALTER TABLE `brgm_cotisations` ADD INDEX ( `cotis_lexique_id_lexique_options` ) ;
ALTER TABLE `brgm_mode_reglement` ADD `code_mode` VARCHAR( 20 ) NOT NULL AFTER `id_mode_reglement` ;
ALTER TABLE `brgm_mode_reglement` ADD INDEX ( `code_mode` ) ;
ALTER TABLE `brgm_statut_reglement` ADD `nom_statut` VARCHAR( 60 ) NOT NULL ;
ALTER TABLE `brgm_statut_reglement` ADD INDEX ( `nom_statut` ) ;
UPDATE `brgm_statut_reglement` SET nom_statut = code_statut;
 */

if(!isset($langtable["_label_vide"]))
	$langtable["_label_vide"]="";

if(!isset($langtable["montant_encaisse"]))
	$langtable["montant_encaisse"]="Montant encaiss&eacute;";
if(!isset($langtable["montant_du"]))
	$langtable["montant_du"]="Montant d&ucirc;";
if(!isset($langtable["reglement_numero"]))
	$langtable["reglement_numero"]="Num&eacute;ro du r&eacute;glement";
if(!isset($langtable["reglement_datedevaleur"]))
	$langtable["reglement_datedevaleur"]="Date de valeur";
if(!isset($langtable["mode_reglement_id_mode_reglement"]))
	$langtable["mode_reglement_id_mode_reglement"]="Mode de r&eacute;glement";
if(!isset($langtable["statut_reglement_id_statut_reglement"]))
	$langtable["statut_reglement_id_statut_reglement"]="Statut du r&eacute;glement";
if(!isset($langtable["liste_tarifaire_id_liste_tarifaire"]))
	$langtable["liste_tarifaire_id_liste_tarifaire"]="Liste tarifaire";
if(!isset($langtable["reglement_type_id_reglement_type"]))
	$langtable["reglement_type_id_reglement_type"]="Type de r&eacute;glement";
if(!isset($langtable["adherents_id_adherents"]))
	$langtable["adherents_id_adherents"]="Adh&eacute;rent";
if(!isset($langtable["banques_id_banques"]))
	$langtable["banques_id_banques"]="Banque";
if(!isset($langtable["date_paiement"]))
	$langtable["date_paiement"]="Date de r&eacute;glement";
if(!isset($langtable["annee_concernee"]))
	$langtable["annee_concernee"]="R&eacute;glement pour l'ann&eacute;e";
if(!isset($langtable["cotis_lexique_id_lexique_types"]))
	$langtable["cotis_lexique_id_lexique_types"]="Type de cotisation";
if(!isset($langtable["cotis_lexique_id_lexique_options"]))
	$langtable["cotis_lexique_id_lexique_options"]="Option de cotisation";



class mdtb_cotisations extends mdtb_table
{	
	function specific_init()
	{
		$i=1;
		$this->table_name="#__cotisations";
		$this->add_field("id_cotisations","number","","","",0,0,0);
		$this->add_field("cotisation_datecreation","dateauto_creation","","","",0,0,0);
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		
		if($this->ischild())
			$this->add_field("adherents_id_adherents","reference","#__adherents","id_adherents","nom_adherent",0,0,0);
		else
			$this->add_field("adherents_id_adherents","reference","#__adherents","id_adherents","nom_adherent",$i,$i,$i++);
		$this->add_field("annee_concernee","reference","#__annees_cotisation","id_annees_cotisation","annee_nom",$i,$i,$i++);

		$this->add_field("statut_reglement_id_statut_reglement","reference","#__statut_reglement","id_statut_reglement","code_statut",$i,$i,$i++);
		$this->add_field("liste_tarifaire_id_liste_tarifaire","reference","#__annees_cotisation","id_annees_cotisation","annee_nom",0,$i,$i++);
		$this->add_field("cotis_lexique_id_lexique_types","reference","#__lexique_types","id_lexique_types","lexique_type",$i,$i,$i++);
		$this->add_field("cotis_lexique_id_lexique_options","reference","#__lexique_options","id_lexique_options","lexique_nom",$i,$i,$i++);
		$this->add_field("date_paiement","date","","","",$i,$i,$i++);
		$this->add_field("montant_encaisse","currency","","","",$i,$i,$i++);
		$this->add_field("montant_du","currency","","","",$i,$i,$i++);
		$this->add_field("mode_reglement_id_mode_reglement","reference","#__mode_reglement","id_mode_reglement","nom_mode",$i,$i,$i++);
		$this->add_field("reglement_numero","text","","","",$i,$i,$i++);
		$this->add_field("reglement_datedevaleur","date","","","",$i,$i,$i++);
		$this->add_field("banques_id_banques","reference","#__banques","id_banques","nom_banque",$i,$i,$i++);
		//$this->add_field("reglement_type_id_reglement_type","reference","#__reglement_type","id_reglement_type","nom_type_reglement",0,$i,$i++);
		
		$this->set_key("id_cotisations");
		$this->set_upload_dir("upload/");
		$this->set_images_path("images/");
		$this->searchable=array("adherents_id_adherents","date_paiement","montant_encaisse","montant_du","reglement_numero","reglement_datedevaleur","mode_reglement_id_mode_reglement","statut_reglement_id_statut_reglement","liste_tarifaire_id_liste_tarifaire","reglement_type_id_reglement_type","banques_id_banques");
		$this->searchable_large=true;
		$this->name="Cotisations des adh&eacute;rents";
		$this->mode="self";
		
		if($this->hasAuth())
			if($this->isAuth())
			{
				$this->add_sql_filter("group_ID",$this->table_name.".group_ID=".$this->_auth->group_ID);
				//$this->add_filter("group_ID",$this->_auth->group_ID);
			}
		$this->nbperpage=20;
		
		$this->add_global_action("action_extract_csv",Tools::Translate("Extraction CSV"));
		/*
		$this->_children=array(
					"mdtb_journal"=>array("parent_key"=>"plan_ID","child_key"=>"planning_plan_ID"),
					"mdtb_etapes"		=>array("parent_key"=>"plan_ID","child_key"=>"planning_plan_ID"),
					"mdtb_piecesjointes"=>array("parent_key"=>"plan_ID","child_key"=>"planning_plan_ID")
					); 
		*/
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="MDO_Code_MDO";
		$this->_template_sections=array(
	    'header' => 'header',
	    'detail' => 'detail',
	    'form' => 'form',
	    'list' => 'list',
	    'menu' => 'menu',
	    'footer' => 'footer');

	}
	
	function before_save()
	{
		$myKeyValue=$this->_recordset->key_value();
		if($myKeyValue===false || $myKeyValue<=0)
			$this->_firstrecord=true;
		else
			$this->_firstrecord=false;
		
		// Calcul du montant dû selon les options et le type sélectionné, et l'année de cotisation
		if(!isset($this->_recordset->montant_du) || $this->_recordset->montant_du=="")
		{
			$myListe=$this->_recordset->liste_tarifaire_id_liste_tarifaire;
			$myType=$this->_recordset->cotis_lexique_id_lexique_types;
			$myOption=$this->_recordset->cotis_lexique_id_lexique_options;
			$this->_db->setQuery(
				"SELECT * FROM #__liste_tarifaire WHERE " .
					"annees_cotisation_id_annees_cotisation=".$myListe." AND " .
					"liste_lexique_id_lexique_types=".$myType." AND " .
					"lexique_options_id_lexique_options=".$myOption.";");
			//echo "Requête : ".Tools::Display($this->_db->getQuery());
			$myObj=null;
			if($this->_db->loadObject($myObj))
				$this->_recordset->montant_du=$myObj->liste_montant;
		}
	}

	function after_save()
	{
		if($this->_firstrecord)
		{
		}
	}
	
	function after_delete($theId,$theFlagSuccess)
	{
		if($theFlagSuccess===true)
		{
		}
	}
	
	function action_extract_csv_link($action)
	{
		return $this->_get_global_action_link($action);
	}
	
	function action_extract_csv()
	{
		$this->_set_recordset_filters();
		$this->_recordset->_nbperpage=0;
		$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,$this->_params->curpage);
		//die("Requ�te : ".$this->_recordset->_db->getQuery());
		$myFile="";
		if($this->_recordset->count() >0)
		{
			$myLine="";		
			foreach($this->_fields as $key=>$val)
			{
				$myCurField=$this->_fields[$key];
				$myCurFieldName=$myCurField->field_name;
				$myLine.=($myLine!=""?";":"")."\"".$myCurFieldName."\"";
			}
			$myLine.="\r\n";
			$myFile.=$myLine;
			
			$this->_recordset->move_first();
			for($i=0;$i<$this->_recordset->count();$i++)
			{
				$myLine="";		
				foreach($this->_fields as $key=>$val)
				{
					$myCurField=$this->_fields[$key];
					$myCurFieldName=$myCurField->field_name;
					$myFieldContent=$this->_recordset->field_display($myCurFieldName);
					$myLine.=($myLine!=""?";":"")."\"".$myFieldContent."\"";
				}
				$myLine.="\r\n";
				$myFile.=$myLine;
				$this->_recordset->move_next();
			}
		}
		Tools::DL_DownloadBuffer("liste-cotisations.csv",$myFile);
		die();
	}
}



?>