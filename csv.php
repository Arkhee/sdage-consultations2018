<?php
define("NO_PCB_NEWS",true);
require_once(_APP_ROOT_DIR_."includes/init.inc.php"); 
/*
 * diren_mdosout
 * Created on 30 oct. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 *
 * File : index.php
 * Description :
 *
 */

$path_pre="admin/";
require_once($path_pre."config.inc.php");
//require_once($path_pre."local.classes/sdage_metier.class.php");
$myClasseMetierMDOSout=new sdage_metier($database,$path_pre,__FILE__);
$myClasseMetierMDOSout->setAuth($auth);
$myClasseMetierMDOSout->initSection("csv");
$myClasseMetierMDOSout->bind($_GET);
$myClasseMetierMDOSout->bind($_POST);
$myClasseMetierMDOSout->handle();
die();