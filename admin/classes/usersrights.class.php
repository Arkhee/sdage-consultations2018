<?php

class usersrights extends mosDBTable
{
	var $usri_ID=null;
	var $user_ID=null;
	var $group_ID=-1;
	var $usri_Rights=0;
	var $usri_Table="";
	var $usri_Record_ID=-1;
	var $usri_SQLFilter="";
	
	var $_tablename="#__mdtb_users_rights";
	var $_key="usri_ID";
	var $_params=null;
	
    function usersrights(&$database)
	{
		$this->mosDBTable( $this->_tablename, $this->_key, $database );
    }
    
    function _bind()
    {
    	if(isset($_POST["_tablename"]))
    	{
    		if($this->_tablename==$_POST["_tablename"])
    		{
		    	$myObj=get_object_vars($this);
		    	foreach($myObj as $key=>$val)
		    	{
		    		if(substr($key,0,1)!="_")
		    			if(isset($_POST[$key]))
		    				$this->_params->$key=$_POST[$key];
		    	}
    		}
    	}
    }
    
    function load_rights($user,$group,$table,$record=-1)
    {
    	$this->_db->setQuery("SELECT * FROM ".$this->_tablename." WHERE user_ID=".addslashes($user)." AND group_ID=".addslashes($group)." AND usri_Table='".addslashes($table)."' AND usri_Record_ID=".addslashes($record).";");
    	return $this->_db->loadObject($this);
    }
    
    function isLoaded()
    {
    	if($this->usri_ID>0) return true;
    	return false;
    }
    
    function canRead()
    {
    	if(!$this->isLoaded()) return false;
    	if($this->usri_Rights & 1) return true;
    	return false;
    }

    function canWrite()
    {
    	if(!$this->isLoaded()) return false;
    	if($this->usri_Rights & 2) return true;
    	return false;
    }
    
    function canDelete()
    {
    	if(!$this->isLoaded()) return false;
    	if($this->usri_Rights & 4) return true;
    	return false;
    }

    function canAdmin()
    {
    	if(!$this->isLoaded()) return false;
    	if($this->usri_Rights & 8) return true;
    	return false;
    }
    
    function getSQLFilter()
    {
    	if(!$this->isLoaded()) return "";
    	return $this->usri_SQLFilter;
    }
}
?>