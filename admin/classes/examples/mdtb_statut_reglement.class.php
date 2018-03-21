<?php
if(!isset($langtable["code_statut"]))
	$langtable["code_statut"]="Statut de r&egrave;glement";

class mdtb_statut_reglement extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__statut_reglement";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_statut_reglement","number","","","",0,0,0);
		$this->add_field("code_statut","text","","","",$i,$i,$i++);
		$this->add_field("nom_statut","text","","","",$i,$i,$i++);
		$this->set_key("id_statut_reglement");
		$this->searchable=array("code_statut");
		$this->name="Statuts de r&eacute;glement";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="code_statut";
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