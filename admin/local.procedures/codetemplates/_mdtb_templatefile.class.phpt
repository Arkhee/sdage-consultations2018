<?php
[TRANSLATIONS]

class [CLASSNAME] extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__[TABLENAME]";
		$i=1;
[FIELDSLIST]
		
		$this->set_key("[KEYNAME]");
		$this->searchable=array([SEARCHABLEFIELDS]);
		$this->display_in_search=array([DISPLAYINSEARCHFIELDS]);
		$this->name="[TABLELABEL]";
		$this->mode="self";
[EVENT_HANDLER_ACTION]
		
		$this->nbperpage=[DEFAULTNBPERPAGE];
		[CHILDRENLIST]
		$this->_defaultparams->sortorder="[DEFAULTSORTORDER]";
		$this->_defaultparams->sortfield="[DEFAULTORDERFIELD]";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","ajaxsearchlist"=>"ajaxsearchlist","ajaxselectlist"=>"ajaxselectlist","form"=>"form","list"=>"list","detail"=>"detail");
	}
}
?>