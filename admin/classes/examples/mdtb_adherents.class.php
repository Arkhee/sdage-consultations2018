<?php
/*
ALTER TABLE `brgm_adherents` ADD `comprofiler_user_id` INT( 11 ) NOT NULL AFTER `id_adherents` ;
ALTER TABLE `brgm_adherents` ADD INDEX ( `comprofiler_user_id` ) ;
 */



if(!isset($langtable["nom_adherent"]))	
	$langtable["nom_adherent"]="Nom";	
if(!isset($langtable["adh_adresse"]))	
	$langtable["nom_adherent"]="Nom";	
	
if(!isset($langtable["entreprises_id_entreprises"]))	
	$langtable["entreprises_id_entreprises"]="Entreprise";	
if(!isset($langtable["adh_numero_aih"]))	
	$langtable["adh_numero_aih"]="Num&eacute;ro AIH";	
if(!isset($langtable["adh_numero_cfh"]))	
	$langtable["adh_numero_cfh"]="Num&eacute;ro CFH";	

if(!isset($langtable["comprofiler_user_id"]))	
	$langtable["comprofiler_user_id"]="Utilisateur Joomla";	
if(!isset($langtable["cb_adhnumeroaih"]))	
	$langtable["cb_adhnumeroaih"]="Num&eacute;ro AIH";	
if(!isset($langtable["cb_adhnumerocfh"]))	
	$langtable["cb_adhnumerocfh"]="Num&eacute;ro CFH";	

if(!isset($langtable["adh_datedebut"]))	
	$langtable["adh_datedebut"]="Date de d&eacute;but";	
if(!isset($langtable["adh_date_demission"]))	
	$langtable["adh_date_demission"]="Date de d&eacute;mission";	

	$langtable["adh_adresse"]="Adresse";
if(!isset($langtable["adh_codepostal"]))	
	$langtable["adh_codepostal"]="Code Postal";
if(!isset($langtable["adh_ville"]))	
	$langtable["adh_ville"]="Ville";
if(!isset($langtable["adh_pays"]))	
	$langtable["adh_pays"]="Pays";
if(!isset($langtable["adh_email_personnel"]))	
	$langtable["adh_email_personnel"]="e-Mail Personnel";
if(!isset($langtable["adh_email_entreprise"]))	
	$langtable["adh_email_entreprise"]="e-Mail Entreprise";
if(!isset($langtable["name"]))	
	$langtable["name"]="Nom";

class jos_users extends mdtb_table
{
	function specific_init()
	{
		$this->table_name="jos_users";
		$i=1;
		$this->add_field("id","number","","","",0,0,0);
		$this->add_field("name","text","","","",$i,$i,$i++);
		$this->set_key("id");
		$this->searchable=array("name","id");
		$this->name="Liste des Utilisateurs Joomla";
		$this->mode="self";
		$this->nbperpage=10;
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="name";
	}
}
class jos_comprofiler extends mdtb_table
{
	function specific_init()
	{
		$this->table_name="jos_comprofiler";
		$i=1;
		$this->add_field("id","number","","","",0,0,0);
		$this->add_field("user_id","number","","","",$i,$i,$i++);
		$this->add_field("lastname","text","","","",$i,$i,$i++);
		$this->add_field("cb_adhnumeroaih","text","","","",$i,$i,$i++);
		$this->add_field("cb_adhnumerocfh","text","","","",$i,$i,$i++);
		$this->set_key("id");
		$this->searchable=array("cb_adhnumerocfh","cb_adhnumerocfh","user_id","lastname");
		$this->name="Liste des Utilisateurs";
		$this->mode="self";
		$this->nbperpage=10;
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="lastname";
	}
}

class mdtb_adherents extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__adherents";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_adherents","number","","","",0,0,0);
		$this->add_field("comprofiler_user_id","reference","jos_users","id","name",$i,$i,$i++);
		$this->add_field("nom_adherent","text","","","",$i,$i,$i++);
		$this->add_field("cb_adhnumeroaih","foreign","jos_comprofiler","user_id","comprofiler_user_id",$i,0,$i++);
		$this->add_field("cb_adhnumerocfh","foreign","jos_comprofiler","user_id","comprofiler_user_id",$i,0,$i++);
		//$this->add_field("name","foreign","jos_users","id","comprofiler_user_id",$i,$i,$i++);
		/*

		$this->add_field("entreprises_id_entreprises","reference","#__entreprises","id_entreprises","nom_entreprise",0,$i,$i++);
		$this->add_field("adh_numero_aih","text","","","",$i,$i,$i++);
		$this->add_field("adh_numero_cfh","text","","","",$i,$i,$i++);
		$this->add_field("adh_datedebut","date","","","",$i,$i,$i++);
		$this->add_field("adh_date_demission","date","","","",$i,$i,$i++);

		$this->add_field("adh_adresse","longtext","","","",0,$i,$i++);
		$this->add_field("adh_codepostal","text","","","",$i,$i,$i++);
		$this->add_field("adh_ville","list_auto","","","",$i,$i,$i++);
		$this->add_field("adh_pays","list_auto","","","",$i,$i,$i++);

		$this->add_field("adh_email_personnel","text","","","",0,$i,$i++);
		$this->add_field("adh_email_entreprise","text","","","",0,$i,$i++);
		*/
		$this->set_key("id_adherents");
		$this->searchable=array("nom_adherent","adh_numero_aih","adh_numero_cfh","adh_datedebut","adh_date_demission",
								"adh_adresse","adh_codepostal","adh_pays","adh_email_personnel","adh_email_entreprise");
		$this->name="Liste des Adh&eacute;rents";
		$this->mode="self";
		$this->nbperpage=10;
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="nom_adherent";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","form"=>"form","list"=>"list","detail"=>"detail");

		$this->_foreigndefinition=array(
					"jos_comprofiler"		=>array("parent_key"=>"comprofiler_user_id","child_key"=>"user_id"),
					"jos_users"				=>array("parent_key"=>"comprofiler_user_id","child_key"=>"id")
		);

		$this->_children=array(
					"mdtb_cotisations"	=>	array("parent_key"=>"id_adherents","child_key"=>"adherents_id_adherents"),
					"mdtb_rel_adherents_associations"	=>	array("parent_key"=>"id_adherents","child_key"=>"adherents_id_adherents"),
					"mdtb_rel_adherents_activites"	=>	array("parent_key"=>"id_adherents","child_key"=>"adherents_id_adherents")
					); 

		$this->add_global_action("action_check_new_members",Tools::Translate("V&eacute;rification des nouveaux membres"));

		if($this->hasAuth())
			if($this->isAuth())
			{
				$this->add_sql_filter("group_ID",$this->table_name.".group_ID=".$this->_auth->group_ID);
				//$this->add_filter("group_ID",$this->_auth->group_ID);
			}
	}
	
	function action_check_new_members_link($action)
	{
		return $this->_get_global_action_link($action);
	}
	
	function action_check_new_members()
	{
		$myQuery="SELECT * FROM jos_comprofiler WHERE cb_adhnumeroaih<>'' OR cb_adhnumerocfh<>'' OR cb_adhnumeroaih IS NOT NULL OR cb_adhnumerocfh IS NOT NULL;";
		$this->_db->setQuery($myQuery);
		$myListeMembres=$this->_db->loadObjectList();
		if(!is_array($myListeMembres) || count($myListeMembres)<=0)
		{
			$this->_messages[]="Aucun membre dans la base";
			return false;
		}
		$this->_messages[]="Comparaison des tables des membres : portail vs gestion des cotisations<br>\n";
		$myQuery="SELECT * FROM ".$this->table_name.";";
		$this->_db->setQuery($myQuery);
		$myListAdherents=$this->_db->loadObjectList();
		$myIndexAdherents=array();
		if(is_array($myListAdherents) && count($myListAdherents)>0)
		{
			$this->_messages[]="Construction de l'index des adherents a usage unique<br>\n";
			foreach($myListAdherents as $curadh)
			$myIndexAdherents[]=$curadh->comprofiler_user_id;
		}
		//$this->_messages[]="Index des adhérents : ".Tools::Display($myIndexAdherents);
		$this->_messages[]="Etablissement de la liste des membres a importer sur la table (nombre : ".count($myListeMembres).")<br>\n";//" : ".Tools::Display($myListeMembres)."<br>\n";
		$myIndexImportMembres=array();
		foreach($myListeMembres as $key=>$curmembre)
		{
			if(!in_array($curmembre->id,$myIndexAdherents))
				$myIndexImportMembres[]=$curmembre;				
		}
		if(!is_array($myIndexImportMembres) || count($myIndexImportMembres)<=0)
		{
			$this->_messages[]="Aucun nouvel adh&eacute;rent &agrave; ajouter &agrave;	 la base";
			return false;
		}
		foreach($myIndexImportMembres as $curmembre)
		{
			$myUser=null;
			$myObj=new stdClass();
			$this->_db->setQuery("SELECT * FROM jos_users WHERE id=".$curmembre->id.";");
			$this->_db->loadObject($myUser);
			
			$myObj->user_ID=$this->_auth->user_ID;
			$myObj->group_ID=$this->_auth->group_ID;
			$myObj->nom_adherent=$myUser->name;
			$myObj->comprofiler_user_id=$curmembre->id;
			$return=$this->_db->insertObject($this->table_name,$myObj,"id_adherents");
			$this->_messages[]="Ajout du membre : ".$myUser->name." ... ".(($return)?"OK":"echec")."<br />\n";
		}
		//$this->_messages[]="Membres à importer : ".Tools::Display($myIndexImportMembres);
	}
}

?>