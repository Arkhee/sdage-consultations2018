<?php
if(!isset($langtable["nom_association"]))
	$langtable["nom_association"]="Association";
if(!isset($langtable["sigle_association"]))
	$langtable["sigle_association"]="Sigle";
if(!isset($langtable["asso_personnes"]))
	$langtable["asso_personnes"]="Personnes";
if(!isset($langtable["asso_entreprises"]))
	$langtable["asso_entreprises"]="Entreprises";
if(!isset($langtable["asso_adresse"]))
	$langtable["asso_adresse"]="Adresse";
if(!isset($langtable["asso_codepostal"]))
	$langtable["asso_codepostal"]="Code Postal";
if(!isset($langtable["asso_ville"]))
	$langtable["asso_ville"]="Ville";
if(!isset($langtable["asso_cedex"]))
	$langtable["asso_cedex"]="CEDEX";
if(!isset($langtable["asso_telephone"]))
	$langtable["asso_telephone"]="Telephone";
if(!isset($langtable["asso_fax"]))
	$langtable["asso_fax"]="Fax";
if(!isset($langtable["asso_mail"]))
	$langtable["asso_mail"]="Mail";
if(!isset($langtable["asso_web"]))
	$langtable["asso_web"]="Web";





class mdtb_associations extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__associations";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_association","number","","","",0,0,0);
		$this->add_field("sigle_association","text","","","",$i,$i,$i++);
		$this->add_field("nom_association","text","","","",$i,$i,$i++);
		$this->add_field("asso_personnes","checkbox","","","",$i,$i,$i++);
		$this->add_field("asso_entreprises","checkbox","","","",$i,$i,$i++);
		$this->add_field("asso_adresse","longtext","","","",0,$i,$i++);
		$this->add_field("asso_codepostal","text","","","",$i,$i,$i++);
		$this->add_field("asso_ville","text","","","",0,$i,$i++);
		$this->add_field("asso_cedex","text","","","",0,$i,$i++);
		$this->add_field("asso_telephone","text","","","",$i,$i,$i++);
		$this->add_field("asso_fax","text","","","",$i,$i,$i++);
		$this->add_field("asso_mail","text","","","",0,$i,$i++);
		$this->add_field("asso_web","text","","","",0,$i,$i++);

		$this->set_key("id_association");
		$this->searchable=array("nom_association","sigle_association");
		$this->name="Associations";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="nom_association";
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