<?php

if(!isset($langtable["nom_entreprise"]))	
	$langtable["nom_entreprise"]="Nom";	
if(!isset($langtable["entr_adresse"]))	
	$langtable["entr_adresse"]="Adresse";
if(!isset($langtable["entr_codepostal"]))	
	$langtable["entr_codepostal"]="Code Postal";
if(!isset($langtable["entr_ville"]))	
	$langtable["entr_ville"]="Ville";
if(!isset($langtable["entr_pays"]))	
	$langtable["entr_pays"]="Pays";
if(!isset($langtable["entr_email"]))	
	$langtable["entr_email"]="e-Mail";

class mdtb_entreprises extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__entreprises";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_entreprises","number","","","",0,0,0);
		$this->add_field("nom_entreprise","text","","","",$i,$i,$i++);
		$this->add_field("entr_adresse","longtext","","","",0,$i,$i++);
		$this->add_field("entr_codepostal","text","","","",$i,$i,$i++);
		$this->add_field("entr_ville","list_auto","","","",$i,$i,$i++);
		$this->add_field("entr_pays","list_auto","","","",$i,$i,$i++);
		$this->add_field("entr_email","text","","","",0,$i,$i++);
		$this->set_key("id_entreprises");
		$this->searchable=array("nom_entreprise","entr_adresse","entr_codepostal","entr_ville","entr_pays","entr_email");
		$this->name="Liste des Entreprises";
		$this->mode="self";
		$this->nbperpage=10;
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="nom_entreprise";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","form"=>"form","list"=>"list","detail"=>"detail");
		if($this->hasAuth())
			if($this->isAuth())
			{
				$this->add_sql_filter("group_ID",$this->table_name.".group_ID=".$this->_auth->group_ID);
				//$this->add_filter("group_ID",$this->_auth->group_ID);
			}
	}
}

?>