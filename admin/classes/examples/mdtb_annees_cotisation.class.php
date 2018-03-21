<?php
if(!isset($langtable["annee_nom"]))
	$langtable["annee_nom"]="Ann&eacute;e";
if(!isset($langtable["annee_encours"]))
	$langtable["annee_encours"]="Ann&eacute;e en cours";

class mdtb_annees_cotisation extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__annees_cotisation";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_annees_cotisation","number","","","",0,0,0);
		$this->add_field("annee_nom","text","","","",$i,$i,$i++);
		$this->add_field("annee_encours","checkbox","","","",$i,$i,$i++);
		$this->set_key("id_annees_cotisation");
		$this->searchable=array("annee_nom");
		$this->name="Ann&eacute;es de cotisation";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="annee_nom";
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