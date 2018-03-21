<?php
if(!isset($langtable["lexique_nom"]))
	$langtable["lexique_nom"]="Nom de l'option de cotisation";

class mdtb_lexique_options extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__lexique_options";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_lexique_options","number","","","",0,0,0);
		$this->add_field("lexique_nom","text","","","",$i,$i,$i++);
		$this->set_key("id_lexique_options");
		$this->searchable=array("lexique_nom");
		$this->name="Options de Cotisation";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="lexique_nom";
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