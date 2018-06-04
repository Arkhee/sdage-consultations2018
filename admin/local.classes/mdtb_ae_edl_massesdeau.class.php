<?php
if(!isset($langtable["ae_edl_massesdeau"])) $langtable["ae_edl_massesdeau"]="Etat des lieux pour la pression";
if(!isset($langtable["id_edl_massedeau"])) $langtable["id_edl_massedeau"]="Etat des lieux";
if(!isset($langtable["id_massedeau"])) $langtable["id_massedeau"]="Entité hydro";
if(!isset($langtable["id_pression"])) $langtable["id_pression"]="Pression";
if(!isset($langtable["impact_2016"])) $langtable["impact_2016"]="Impact SDAGE 2016";
if(!isset($langtable["impact_valeur_forcee"])) $langtable["impact_valeur_forcee"]="Impact valeur forcée";
if(!isset($langtable["impact_2019"])) $langtable["impact_2019"]="Impact EDL 2019";
if(!isset($langtable["rnaoe_2021"])) $langtable["rnaoe_2021"]="RNAOE 2021";
if(!isset($langtable["pression_origine_2021"])) $langtable["pression_origine_2021"]="Pression origine 2021";
if(!isset($langtable["rnaoe_2027"])) $langtable["rnaoe_2027"]="RNAOE 2027";
if(!isset($langtable["pression_origine_2027"])) $langtable["pression_origine_2027"]="Pression origine 2027";


class mdtb_ae_edl_massesdeau extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__ae_edl_massesdeau";
		$i=1;
		$this->add_field("id_edl_massedeau","number","","","",0,0,0);
		$this->add_field("id_massedeau","reference","ae_massesdeau","id_massedeau","libelle_me",$i,$i,$i++);
		$this->add_field("id_pression","number","ae_pressions","id_pression","libelle_pression",$i,$i,$i++);
		$this->add_field("impact_2016","text","","","",$i,$i,$i++);
		$this->add_field("impact_valeur_forcee","text","","","",$i,$i,$i++);
		$this->add_field("impact_2019","text","","","",$i,$i,$i++);
		$this->add_field("rnaoe_2021","text","","","",$i,$i,$i++);
		$this->add_field("pression_origine_2021","text","","","",$i,$i,$i++);
		$this->add_field("rnaoe_2027","text","","","",$i,$i,$i++);
		$this->add_field("pression_origine_2027","text","","","",$i,$i,$i++);
		$this->set_key("id_edl_massedeau");
		$this->searchable=array("id_edl_massedeau","impact_2016");
		$this->display_in_search=array("id_edl_massedeau","impact_2016");
		$this->name="ae_edl_massesdeau";
		$this->mode="self";


		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","ajaxsearchlist"=>"ajaxsearchlist","ajaxselectlist"=>"ajaxselectlist","form"=>"form","list"=>"list","detail"=>"detail");
		if(method_exists($this,"parent_init")) $this->parent_init();
	}
}