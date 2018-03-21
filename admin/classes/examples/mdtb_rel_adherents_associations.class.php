<?php
if(!isset($langtable["adherents_id_adherents"]))
	$langtable["adherents_id_adherents"]="Adh&eacute;rent";
if(!isset($langtable["associations_id_association"]))
	$langtable["associations_id_association"]="Association";
if(!isset($langtable["adh_membre_ca"]))
	$langtable["adh_membre_ca"]="Membre du CA";
if(!isset($langtable["adh_membre_bureau"]))
	$langtable["adh_membre_bureau"]="Membre du Bureau";



class mdtb_rel_adherents_associations extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__rel_adherents_associations";
		$i=1;
		$this->add_field("user_ID","user","#__users","user_ID","user_Login",0,0,0);
		$this->add_field("group_ID","group","#__groupes","group_ID","group_Nom",0,0,0);
		$this->add_field("id_rel_adherents_associations","number","","","",0,0,0);
		if($this->ischild())
			$this->add_field("adherents_id_adherents","reference","#__adherents","id_adherents","nom_adherent",0,0,0);
		else
			$this->add_field("adherents_id_adherents","reference","#__adherents","id_adherents","nom_adherent",$i,$i,$i++);
		$this->add_field("associations_id_association","reference","#__associations","id_association","nom_association",$i,$i,$i++);
		$this->add_field("adh_membre_ca","checkbox","","","",$i,$i,$i++);
		$this->add_field("adh_membre_bureau","checkbox","","","",$i,$i,$i++);
		$this->set_key("id_rel_adherents_associations");
		$this->searchable=array("adherents_id_adherents","associations_id_association");
		$this->name="Associations des adh&eacute;rents";
		$this->mode="self";
		
		$this->nbperpage=20;

		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="associations_id_association";
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