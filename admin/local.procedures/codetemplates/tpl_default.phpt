<?php
/*
 * Created on 16 mai 07
 * Copyright  @  2007 Yannick B�temps yannick@alternetic.com
 * Author : Yannick B�temps
 * 
 * File : index.php
 * Description : 
 * 
 */
$path_pre="";
require_once("config.inc.php");
$myBase=new [CLASSNAME]($database,$template_name,basename(__FILE__),$path_abs);
$myBase->set_auth($auth);
require_once("layout.inc.php");
?>