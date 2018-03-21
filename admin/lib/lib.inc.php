<?php
/*
 * Toutateam Groupware
 * Created on 12 mars 08
 * Copyright  �  2008 Yannick B�temps yannick@alternetic.com
 * www.toutateam.com
 * Author : Yannick B�temps
 * 
 * Toutateam is a fork project of PHProjekt v4.0
 * 
 * File : libs.php
 * Description : 
 * 
 */

require_once($path_pre."lib/lib.functions.inc.php");
require_once($path_pre."classes/database.php");
require_once($path_pre."classes/tools.class.php");
require_once($path_pre."classes/template.class.php");
require_once($path_pre."classes/mdtb.class.php");
require_once($path_pre."classes/users.class.php");
require_once($path_pre."classes/usersrights.class.php");

require_once($path_pre."classes/mdtb_empty.class.php");
require_once($path_pre."classes/mdtb_usermanagement.class.php");

require_once($path_pre.$template_name.".php");


foreach($TheArrayModules as $keymodule=>$curmodule)
{
	if(strstr($keymodule,"#sep#")!==false)
	{
		$TheMainMenu[]=
			array( "type"=>"separator","label" => ($curmodule),"link"=>"");
	}
	else	
		$TheMainMenu[]=
			array( "type"=>"item","label" => ($curmodule), "link" => $keymodule.".php" );
}

require_once($path_pre."lib/lang.inc.php");
$database = new database( $db_host, $db_user, $db_pass, $db_name, $db_prefix );
if($database->_errorNum!=0)
	die($database->_errorNum." : ".$database->_errorMsg);
?>