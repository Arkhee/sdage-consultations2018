<?php

if(!isset($langtable["ae_ss_ut"])) $langtable["ae_ss_ut"]="Sous unité territoriale";
if(!isset($langtable["id_ss_ut"])) $langtable["id_ss_ut"]="Sous unité territoriale";
if(!isset($langtable["code_ss_ut"])) $langtable["code_ss_ut"]="Code SS UT";
if(!isset($langtable["libelle_ss_ut"])) $langtable["libelle_ss_ut"]="Libellé";


class mdtb_ae_ss_ut extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__ae_ss_ut";
		$i=1;
		$this->add_field("id_ss_ut","number","","","",0,0,0);
		//$this->add_field("faq_date","dateauto_creation","","","",$i,$i,$i++);
		$this->add_field("code_ss_ut","text","","","",$i,$i,$i++);
		$this->add_field("libelle_ss_ut","text","","","",$i,$i,$i++);

		
		$this->set_key("id_ss_ut");
		$this->searchable=array("id_ss_ut","code_ss_ut","libelle_ss_ut");
		$this->display_in_search=array("id_ss_ut","code_ss_ut","libelle_ss_ut");
		$this->name="ae_ss_ut";
		$this->mode="self";


		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","ajaxsearchlist"=>"ajaxsearchlist","ajaxselectlist"=>"ajaxselectlist","form"=>"form","list"=>"list","detail"=>"detail");
		if(method_exists($this,"parent_init")) $this->parent_init();
	}
}