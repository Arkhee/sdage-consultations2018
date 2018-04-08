<?php
/*
 * Created on 16 mai 07
 * Copyright  @  2007 Yannick Bétemps yannick@alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : index.php
 * Description : 
 * 
 */
$path_pre="";
require_once("config.inc.php");
$myBase=new mdtb_ae_edl_massesdeau($database,$template_name,basename(__FILE__),$path_abs);
$myBase->set_auth($auth);
require_once("layout.inc.php");