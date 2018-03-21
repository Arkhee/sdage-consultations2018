<?php
class mdtb_empty extends mdtb_table
{	
	function specific_init()
	{
		
		$this->table_name="";
		$i=1;
		$this->add_field("ID","user","#__mdtb_users","user_ID","user_Login",0,0,0);

		$this->set_key("");
		$this->searchable=array("");
		$this->name="Vide";
		$this->mode="self";
		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="";
		$this->_defaultparams->sortfield="";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","form"=>"form","list"=>"list","detail"=>"detail");

	}
}
?>