<?php
if(!isset($langtable["nom_type_reglement"]))
	$langtable["nom_type_reglement"]="Type de r&egrave;glement";

class mdtb_reglement_type extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__reglement_type";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_reglement_type","number","","","",0,0,0);
		$this->add_field("nom_type_reglement","text","","","",$i,$i,$i++);
		$this->set_key("id_reglement_type");
		$this->searchable=array("nom_type_reglement");
		$this->name="Types de r&eacute;glement";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="nom_type_reglement";
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