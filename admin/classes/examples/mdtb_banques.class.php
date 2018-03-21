<?php
if(!isset($langtable["nom_banque"]))
	$langtable["nom_banque"]="Banque";
if(!isset($langtable["code_banque"]))
	$langtable["code_banque"]="Code";

class mdtb_banques extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__banques";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_banques","number","","","",0,0,0);
		$this->add_field("code_banque","text","","","",0,$i,$i++);
		$this->add_field("nom_banque","text","","","",$i,$i,$i++);
		$this->set_key("id_banques");
		$this->searchable=array("nom_banque");
		$this->name="Banques";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="nom_banque";
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