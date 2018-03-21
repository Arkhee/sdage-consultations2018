<?php
if(!isset($langtable["cotisations_id_cotisations"]))
	$langtable["cotisations_id_cotisations"]="Cotisation";
if(!isset($langtable["lexique_options_id_lexique_options"]))
	$langtable["lexique_options_id_lexique_options"]="Option dans le lexique";

class mdtb_rel_options_cotisants extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__rel_options_cotisants";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_rel_options_cotisations","number","","","",0,0,0);
		$this->add_field("lexique_options_id_lexique_options","reference","#__adherents","id_adherents","nom_adherent",$i,$i,$i++);
		$this->add_field("cotisations_id_cotisations","reference","#__activites","id_activites","activite_nom",$i,$i,$i++);
		$this->set_key("id_rel_options_cotisations");
		$this->searchable=array("adherents_id_adherents","activites_id_activites");
		$this->name="Activit&eacute;s des adh&eacute;rents";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="lexique_options_id_lexique_options";
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