<?php
if(!isset($langtable["ae_massesdeau"])) $langtable["ae_massesdeau"]="Entité hydro";
if(!isset($langtable["id_massedeau"])) $langtable["id_massedeau"]="Entité hydro";
if(!isset($langtable["code_me"])) $langtable["code_me"]="Code masse d'eau";
if(!isset($langtable["libelle_me"])) $langtable["libelle_me"]="Libellé";
if(!isset($langtable["code_ssbv"])) $langtable["code_ssbv"]="Code SSBV";
if(!isset($langtable["categorie_me"])) $langtable["categorie_me"]="Catégorie";
if(!isset($langtable["statut_me"])) $langtable["statut_me"]="Statut";


class mdtb_ae_massesdeau extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__ae_massesdeau";
		$i=1;
		$this->add_field("id_massedeau","number","","","",0,0,0);
		//$this->add_field("faq_date","dateauto_creation","","","",$i,$i,$i++);
		$this->add_field("code_me","text","","","",$i,$i,$i++);
		$this->add_field("libelle_me","text","","","",$i,$i,$i++);
		$this->add_field("code_ssbv","reference","rel_ssbv_ss_ut","code_ssbv","code_ssbv",$i,$i,$i++);
		//$this->add_field("a_synonyme_id_a_synonyme","reference","a_synonyme","id_a_synonyme","id_a_synonyme",1,1,1);
		$this->add_field("categorie_me","text","","","",$i,$i,$i++);
		$this->add_field("statut_me","text","","","",$i,$i,$i++);

		
		$this->set_key("id_massedeau");
		$this->searchable=array("id_massedeau","code_me","libelle_me","categorie_me","statut_me");
		$this->display_in_search=array("id_massedeau","code_me","libelle_me","categorie_me","statut_me");
		$this->name="ae_massesdeau";
		$this->mode="self";


		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","ajaxsearchlist"=>"ajaxsearchlist","ajaxselectlist"=>"ajaxselectlist","form"=>"form","list"=>"list","detail"=>"detail");
		if(method_exists($this,"parent_init")) $this->parent_init();
	}
}