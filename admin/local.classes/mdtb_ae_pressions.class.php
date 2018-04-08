<?php
if(!isset($langtable["ae_pressions"])) $langtable["ae_pressions"]="Pressions";
if(!isset($langtable["id_pression"])) $langtable["id_pression"]="Id Pression";
if(!isset($langtable["libelle_pression"])) $langtable["libelle_pression"]="Pression";


class mdtb_ae_pressions extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__ae_pressions";
		$i=1;
		$this->add_field("id_pression","number","","","",0,0,0);
		//$this->add_field("faq_date","dateauto_creation","","","",$i,$i,$i++);
		$this->add_field("libelle_pression","text","","","",$i,$i,$i++);
		
		$this->set_key("id_pression");
		$this->searchable=array("libelle_pression");
		$this->display_in_search=array("libelle_pression");
		$this->name="ae_pressions";
		$this->mode="self";


		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","ajaxsearchlist"=>"ajaxsearchlist","ajaxselectlist"=>"ajaxselectlist","form"=>"form","list"=>"list","detail"=>"detail");
		if(method_exists($this,"parent_init")) $this->parent_init();
	}
}