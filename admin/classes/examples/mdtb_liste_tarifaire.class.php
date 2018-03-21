<?php
if(!isset($langtable["lexique_options_id_lexique_options"]))
	$langtable["lexique_options_id_lexique_options"]="Nom de l'option";
if(!isset($langtable["annees_cotisation_id_annees_cotisation"]))
	$langtable["annees_cotisation_id_annees_cotisation"]="Ann&eacute;e de cotisation";
if(!isset($langtable["liste_montant"]))
	$langtable["liste_montant"]="Montant";
if(!isset($langtable["liste_lexique_id_lexique_types"]))
	$langtable["liste_lexique_id_lexique_types"]="Type de cotisation";
	
class mdtb_liste_tarifaire extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__liste_tarifaire";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_liste_tarifaire","number","","","",0,0,0);
		$this->add_field("annees_cotisation_id_annees_cotisation","reference","#__annees_cotisation","id_annees_cotisation","annee_nom",$i,$i,$i++);
		$this->add_field("liste_lexique_id_lexique_types","reference","#__lexique_types","id_lexique_types","lexique_type",$i,$i,$i++);
		$this->add_field("lexique_options_id_lexique_options","reference","#__lexique_options","id_lexique_options","lexique_nom",$i,$i,$i++);
		$this->add_field("liste_montant","text","","","",$i,$i,$i++);

		$combo_annees[""]=Tools::Translate("Toutes");
		
		$myDefAnnees=new mdtb_annees_cotisation($this->_db,$this->_template_name,$this->_script,$this->_curpath);
		$myDefAnnees->init();
		$this->_db->setQuery("SELECT * FROM ".$myDefAnnees->table_name." GROUP BY annee_nom ORDER BY annee_nom ASC");
		$myListQuery=$this->_db->loadObjectList();
		if($myListQuery!==false && count($myListQuery)>0)
			foreach($myListQuery as $curetap)
				$combo_annees[($curetap->id_annees_cotisation)]=Tools::Translate(stripslashes($curetap->annee_nom));
		$this->add_filter_combo("annees_cotisation_id_annees_cotisation",Tools::Translate("Ann&eacute;e"),$combo_annees);

		$combo_types[""]=Tools::Translate("Tous");
		$myDefTypes=new mdtb_lexique_types($this->_db,$this->_template_name,$this->_script,$this->_curpath);
		$myDefTypes->init();
		$this->_db->setQuery("SELECT * FROM ".$myDefTypes->table_name." GROUP BY lexique_type ORDER BY lexique_type ASC");
		$myListQuery=$this->_db->loadObjectList();
		if($myListQuery!==false && count($myListQuery)>0)
			foreach($myListQuery as $curetap)
				$combo_types[($curetap->id_lexique_types)]=Tools::Translate(stripslashes($curetap->lexique_type));
		$this->add_filter_combo("liste_lexique_id_lexique_types",Tools::Translate("Type"),$combo_types);

		

		$this->set_key("id_liste_tarifaire");
		$this->searchable=array("liste_montant");
		$this->name="Montants des Options";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="DESC";
		$this->_defaultparams->sortfield="annees_cotisation_id_annees_cotisation";
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