<?php

class users extends mosDBTable
{
	var $user_ID=null;
	var $group_ID=-1;
	var $user_Login="";
	var $user_Name="";
	var $user_FirstName="";
	var $user_Mail="";
	var $user_Password="";
	var $user_Rank="";
	var $user_Structure="";
	var $user_NomStructure="";
	
	var $_tablename="#__mdtb_users";
	var $_key="user_ID";
	var $_params=null;
	var $_messages=array();
	var $_users_default_file="";
    function users(&$database,$defaultUsersFile="")
	{
		$this->mosDBTable( $this->_tablename, $this->_key, $database );
		$this->_users_default_file=$defaultUsersFile;
		$this->check_users_file();
    }
    
    function check_users_file()
    {
    	$debug=false;
    	if($debug) echo __LINE__." => ".__FUNCTION__.BR;
		if($this->_users_default_file!="" && file_exists($this->_users_default_file))
		{
	    	if($debug) echo __LINE__." => ".$this->_users_default_file.BR;
			$this->_db->setQuery("SHOW TABLES;");
			$myListTables=$this->_db->loadObjectList();
			$listHeader="";
			$bolTablesExist=false;
	    	if($debug) echo __LINE__." => ".Tools::Display($myListTables).BR;
			if(is_array($myListTables) && count($myListTables)>0)
			{
				foreach($myListTables as $curtable)
				{
					if($listHeader=="")
					{
						$myArrayTable=get_object_vars($curtable);
						foreach($myArrayTable as $headername => $headerval)
							$listHeader=$headername;
					}
			    	if($debug) echo __LINE__." => Comparaison ".$curtable->$listHeader." vs ".str_replace("#__",$this->_db->_table_prefix,"#__mdtb_users").BR;
					if($curtable->$listHeader==str_replace("#__",$this->_db->_table_prefix,"#__mdtb_users"))
						$bolTablesExist=true;
				}
			}
	    	if($debug) echo __LINE__." => Users ".(($bolTablesExist)?"existe":"à créer").BR;
			if(!$bolTablesExist)
			{
				$fh=fopen($this->_users_default_file,"rt");
				if($fh)
				while(!feof($fh))
				{
					$myQuery=fgets($fh);
					$this->_db->setQuery($myQuery);
					$myReturn=$this->_db->query();
					if(!$myReturn)
					{
						fclose($fh);
						die("Impossible de créer les tables utilisateur");
					}
				}
		    	if($debug) echo __LINE__." => Users créé !".BR;
				fclose($fh);
			}
		}
    }
    
    function _bind()
    {
    	if(isset($_GET["_tablename"]) && urldecode($_GET["_tablename"])==$this->_tablename)
	    	if(isset($_GET["_action"]))
				$this->_params->action=$_GET["_action"];
				
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
    
    function load_user($user,$pass,$enc)
    {
    	$cr_pass=$pass;
    	if($enc)
    		$cr_pass=md5($pass);
    	$this->_db->setQuery("SELECT * FROM ".$this->_tablename." WHERE user_Login='".addslashes($user)."' AND user_Password='".addslashes($cr_pass)."';");
    	$myReturn=$this->_db->loadObject($this);
		//die("Connexion avec ".$user." : ".print_r("SELECT * FROM ".$this->_tablename." WHERE user_Login='".addslashes($user)."' AND user_Password='".addslashes($cr_pass)."';",true)." : ".$myReturn->user_Login);
    	return $myReturn;
    }
    
    function start()
    {
    	$debug=false;
    	$this->_bind();
    	if(isset($this->_params->action) && $this->_params->action=="logout")
    	{
    		$this->_logout();
    		return false;
    	}
    	
    	$myUserLogin="";
    	$myUserPass="";
    	$myEncPass=true;
    	
    	if(isset($_SESSION["user_Login"]) && isset($_SESSION["user_Password"]) && $_SESSION["user_Login"]!="" && $_SESSION["user_Password"]!="") 
    	{
    		$myUserLogin=$_SESSION["user_Login"];
    		$myUserPass=$_SESSION["user_Password"];
    		$myEncPass=false;
    	}
    	
    	if(isset($this->_params->user_Login) && isset($this->_params->user_Password) && $this->_params->user_Login!="" && $this->_params->user_Password!="")
    	{
    		$myUserLogin=$this->_params->user_Login;
    		$myUserPass=$this->_params->user_Password;
    		$myEncPass=true;
    	}
    	
    	if($debug) echo "Chargement utilisateur : ".$myUserLogin." / ".$myUserPass."<br>\n";
    	if($myUserLogin!="" && $myUserPass!="")
    	{
    		if($this->load_user($myUserLogin,$myUserPass,$myEncPass))
    		{
    			$this->_login();
				if($debug) echo "Login !";    			
    		}
    		else
    			if($debug) echo "Pas de login";
    	}
    	else
    	{
    		$this->_messages[]=Tools::Translate("Login ou mot de passe invalide !! Veuillez vous identifier");
    		$this->_logout();
    		//return false;
    	}
    	return true;
    }

	function _login()
	{
		$myObj=get_object_vars($this);
    	foreach($myObj as $key=>$val)
    		if(substr($key,0,1)!="_")
    				$_SESSION[$key]=$this->$key;
	}

	function _logout()
	{
		$myObj=get_object_vars($this);
		foreach($myObj as $key=>$val)
		if(substr($key,0,1)!="_")
		{
			$_SESSION[$key]="";
			unset($_SESSION[$key]);
		}
	}
	
	function getLogoutUrl($url)
	{
		return $url.="?_action=logout&_tablename=".urlencode($this->_tablename);
	}
	
    function getLoginForm($url)
    {
    	$form = "";
		$form .= "<div id=\"login_form\">";    		
    	if(is_array($this->_messages) && count($this->_messages)>0)
    	{
			$form.="<div id=\"login_messages\">";    		
    		$form.=implode("<br>\n",$this->_messages)."<br>\n";
    		$form.="</div>";
    	}
    	$form .= TT_Template::FORM_Begin($url);
    	$form .= "Login&nbsp;:&nbsp;".TT_Template::FORM_GetText("user_Login");
    	$form .= "Passe&nbsp;:&nbsp;".TT_Template::FORM_GetPass("user_Password");
    	$form .= TT_Template::FORM_GetHidden("_tablename",$this->_tablename);
    	$form .= "&nbsp;".TT_Template::FORM_GetSubmit("cmdOk","login");
    	$form .= TT_Template::Form_End();
		$form .= "</div>";
    	return $form;
    }
    
    function isLoaded()
    {
    	if($this->user_ID>0) return true;
    	return false;
    }
    
    function isUser()
    {
    	if(!$this->isLoaded()) return false;
    	if($this->user_Rank=="user") return true;
    	return false;
    }
    
    function isAdmin()
    {
    	if(!$this->isLoaded()) return false;
    	if($this->user_Rank=="admin") return true;
    	return false;
    }
    
    function getRights($table,$record=-1)
    {
    	$myRights=new usersrights($this->_db);
    	if($myRights->load_rights($this->user_ID,$this->group_ID,$table,$record))
		{
    		return $myRights;
		}
    	return false;
    }
    
    function canRead($table,$record=-1)
    {
    	if(!$this->isLoaded()) return false;
    	$myRights=new usersrights($this->_db);
    	if($myRights->load_rights($this->user_ID,$this->group_ID,$table,$record));
    	if($myRights->isLoaded())
    	{
    		return $myRights->canRead();
    	}
    	if($this->user_Rank=="admin") return true;
    	return false;
    }

    function canWrite($table,$record=-1)
    {
    	if(!$this->isLoaded()) return false;
    	$myRights=new usersrights($this->_db);
    	if($myRights->load_rights($this->user_ID,$this->group_ID,$table,$record));
    	if($myRights->isLoaded())
    	{
    		return $myRights->canWrite();
    	}
    	if($this->user_Rank=="admin") return true;
    	return false;
    }

    function canDelete($table,$record=-1)
    {
    	if(!$this->isLoaded()) return false;
    	$myRights=new usersrights($this->_db);
    	if($myRights->load_rights($this->user_ID,$this->group_ID,$table,$record));
    	if($myRights->isLoaded())
    	{
    		return $myRights->canDelete();
    	}
    	if($this->user_Rank=="admin") return true;
    	return false;
    }
    
    function canAdmin($table,$record=-1)
    {
    	if(!$this->isLoaded()) return false;
    	$myRights=new usersrights($this->_db);
    	if($myRights->load_rights($this->user_ID,$this->group_ID,$table,$record));
    	if($myRights->isLoaded())
    	{
    		return $myRights->canAdmin();
    	}
    	if($this->user_Rank=="admin") return true;
    	return false;
    }
    
    
}