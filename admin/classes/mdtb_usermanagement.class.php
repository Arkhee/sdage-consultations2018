<?php


if(!isset($langtable["mdtb_groupes"]))
	$langtable["mdtb_groupes"]="Gestion : Groupes";
	
if(!isset($langtable["group_Nom"]))
	$langtable["group_Nom"]="Nom du Groupe";
	
class mdtb_groups extends mdtb_table
{	
	function specific_init()
	{	
		$this->table_name="#__mdtb__groupes";
		$i=1;
		$this->add_field("group_ID","number","","","",0,0,0);
		$this->add_field("group_Nom","text","","","",$i,$i,$i++);
		$this->set_key("group_ID");
		$this->searchable=array("group_Nom");
		$this->name="Liste des groupes";
		$this->mode="self";
		
		$this->nbperpage=10;
		$this->_children=array("mdtb_users"=>array("parent_key"=>"group_ID","child_key"=>"group_ID")); 
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="group_Nom";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","form"=>"form","list"=>"list","detail"=>"detail");
	}
}


if(!isset($langtable["mdtb_users"]))
	$langtable["mdtb_users"]="Gestion Utilisateurs";
	
if(!isset($langtable["user_ID"]))
	$langtable["user_ID"]="ID Utilisateur";
if(!isset($langtable["group_ID"]))
	$langtable["group_ID"]="Groupe";
if(!isset($langtable["user_Login"]))
	$langtable["user_Login"]="Login";
if(!isset($langtable["user_Name"]))
	$langtable["user_Name"]="Nom";
if(!isset($langtable["user_FirstName"]))
	$langtable["user_FirstName"]="Prénom";
if(!isset($langtable["user_Password"]))	
	$langtable["user_Password"]="Mot de passe";
if(!isset($langtable["user_Rank"]))	
	$langtable["user_Rank"]="Rang";
if(!isset($langtable["user_Mail"]))	
	$langtable["user_Mail"]="e-Mail";
if(!isset($langtable["user_Structure"]))
	$langtable["user_Structure"]="Structure";

class mdtb_users extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__mdtb_users";
		$i=1;
		$this->add_field("user_ID","number","","","",0,0,0);
		$this->add_field("user_Login","text","","","",$i,$i,$i++);
		//$this->add_field("user_CreationDate","date","","","",$i,$i,$i++);
		$this->add_field("group_ID","reference","#__mdtb_groupes","group_ID","group_Nom",$i,$i,$i++);
		$this->add_field("user_Name","text","","","",$i,$i,$i++);
		$this->add_field("user_FirstName","text","","","",$i,$i,$i++);
		$this->add_field("user_Mail","text","","","",$i,$i,$i++);
		$this->add_field("user_Password","password","","","",0,$i++,0);
		$this->add_field("user_Rank","combolist",array("admin"=>"Administrateur","user"=>"Utilisateur"),"","",$i,$i,$i++);
		$this->add_field("user_Structure","text","","","",$i,$i,$i++);
		$this->add_field("user_NomStructure","text","","","",$i,$i,$i++);
		$this->set_key("user_ID");
		$this->searchable=array("user_Login","user_Nom","user_Mail");
		$this->name="Liste des utilisateurs";
		$this->mode="self";
		
		$this->nbperpage=10;
		$this->_children=array("mdtb_usersrights"=>array("parent_key"=>"user_ID","child_key"=>"user_ID")); 
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="user_Nom";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","form"=>"form","list"=>"list","detail"=>"detail");
	}
}

if(!isset($langtable["mdtb_user_rights"]))
	$langtable["mdtb_user_rights"]="Gestion : Droits";
if(!isset($langtable["usri_ID"]))
	$langtable["usri_ID"]="ID Droits";
if(!isset($langtable["usri_Rights"]))
	$langtable["usri_Rights"]="Droits utilisateur";
if(!isset($langtable["usri_Table"]))
	$langtable["usri_Table"]="Table";
if(!isset($langtable["usri_Record_ID"]))
	$langtable["usri_Record_ID"]="Enregistrement";
if(!isset($langtable["usri_SQLFilter"]))	
	$langtable["usri_SQLFilter"]="Filtre SQL";



class mdtb_usersrights extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="#__mdtb_users_rights";
		$i=1;
		$this->add_field("usri_ID","number","","","",0,0,0);
		$this->add_field("user_ID","reference","#__mdtb_users","user_ID","user_Login",0,$i++,0);
		$this->add_field("group_ID","reference","#__mdtb_groupes","group_ID","group_Nom","",$i,$i,$i++);
		$this->add_field("usri_Rights","bits",array(1=>"Lecture",2=>"Ecriture",4=>"Effacement",8=>"Administration"),"","",$i,$i,$i++);
		$this->add_field("usri_Table","text","","","",$i,$i,$i++);
		$this->add_field("usri_Record_ID","number","","","",0,0,0);
		$this->add_field("usri_SQLFilter","text","","","",0,$i,$i++);
		$this->set_key("usri_ID");
		$this->searchable=array("user_Login","user_Nom","user_Mail");
		$this->name="Droits des utilisateurs";
		$this->mode="self";
		
		$this->nbperpage=10;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="user_ID";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","form"=>"form","list"=>"list","detail"=>"detail");
	}
}

?>