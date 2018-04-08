<?php
if(!isset($langtable["ae_avis"])) $langtable["ae_avis"]="Avis";
if(!isset($langtable["id_massedeau"])) $langtable["id_massedeau"]="Entité hydro";
if(!isset($langtable["id_pression"])) $langtable["id_pression"]="Entité hydro";
if(!isset($langtable["impact"])) $langtable["impact"]="Entité hydro";
if(!isset($langtable["commentaires"])) $langtable["commentaires"]="Entité hydro";
if(!isset($langtable["date_modification"])) $langtable["date_modification"]="Entité hydro";
if(!isset($langtable["date_validation"])) $langtable["date_validation"]="Entité hydro";

class mdtb_ae_avis extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__ae_avis";
		$i=1;
		$this->add_field("id_avis","number","","","",0,0,0);
		$this->add_field("id_massedeau","reference","ae_massesdeau","id_massedeau","libelle_me",$i,$i,$i++);
		$this->add_field("id_pression","reference","ae_pressions","id_pression","libelle_pression",$i,$i,$i++);
		$this->add_field("impact","number","","","",$i,$i,$i++);
		$this->add_field("commentaires","text","","","",$i,$i,$i++);
		$this->add_field("date_modification","dateauto_modification","","","",$i,$i,$i++);
		$this->add_field("date_validation","date","","","",$i,$i,$i++);

		
		$this->set_key("id_avis");
		$this->searchable=array("impact","commentaires");
		$this->display_in_search=array("id_massedeau","id_pressions","impact","commentaire");
		$this->name="ae_massesdeau";
		$this->mode="self";


		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","ajaxsearchlist"=>"ajaxsearchlist","ajaxselectlist"=>"ajaxselectlist","form"=>"form","list"=>"list","detail"=>"detail");
		if(method_exists($this,"parent_init")) $this->parent_init();
	}
}