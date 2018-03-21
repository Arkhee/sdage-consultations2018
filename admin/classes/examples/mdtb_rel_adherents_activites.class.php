<?php
if(!isset($langtable["adherents_id_adherents"]))
	$langtable["adherents_id_adherents"]="Adh&eacute;rent";
if(!isset($langtable["activites_id_activites"]))
	$langtable["activites_id_activites"]="Activit&eacute;";

class mdtb_rel_adherents_activites extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__rel_adherents_activites";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_rel_adherents_activites","number","","","",0,0,0);
		if($this->ischild())
			$this->add_field("adherents_id_adherents","reference","#__adherents","id_adherents","nom_adherent",0,0,0);
		else
			$this->add_field("adherents_id_adherents","reference","#__adherents","id_adherents","nom_adherent",$i,$i,$i++);
		$this->add_field("activites_id_activites","reference","#__activites","id_activites","activite_nom",$i,$i,$i++);
		$this->set_key("id_rel_adherents_activites");
		$this->searchable=array("adherents_id_adherents","activites_id_activites");
		$this->name="Activit&eacute;s des adh&eacute;rents";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="activites_id_activites";
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