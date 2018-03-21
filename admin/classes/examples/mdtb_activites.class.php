<?php
if(!isset($langtable["activite_code"]))
	$langtable["activite_code"]="Code";
if(!isset($langtable["activite_nom"]))
	$langtable["activite_nom"]="Activit&eacute;s";
if(!isset($langtable["activite_excel"]))
	$langtable["activite_excel"]="Excel";

class mdtb_activites extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__activites";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_activites","number","","","",0,0,0);
		$this->add_field("activite_code","text","","","",$i,$i,$i++);
		$this->add_field("activite_nom","text","","","",$i,$i,$i++);
		$this->add_field("activite_excel","text","","","",$i,$i,$i++);
		$this->set_key("id_activites");
		$this->searchable=array("activite_nom");
		$this->name="Activit&eacute;s";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="activite_nom";
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