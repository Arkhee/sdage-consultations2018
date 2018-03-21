<?php
if(!isset($langtable["nom_mode"]))
	$langtable["nom_mode"]="Mode de r&egrave;glement";
if(!isset($langtable["code_mode"]))
	$langtable["code_mode"]="Code du Mode de r&egrave;glement";

class mdtb_mode_reglement extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__mode_reglement";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_mode_reglement","number","","","",0,0,0);
		$this->add_field("code_mode","text","","","",$i,$i,$i++);
		$this->add_field("nom_mode","text","","","",$i,$i,$i++);
		$this->set_key("id_mode_reglement");
		$this->searchable=array("nom_mode");
		$this->name="Modes de r&eacute;glement";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="nom_mode";
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