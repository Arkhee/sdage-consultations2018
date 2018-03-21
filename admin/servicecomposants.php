<?php
/*
 * diren
 * Created on 22 juil. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : servicecomposants.php
 * Description : 
 * 
 */
 
$path_pre=""; 
require_once($path_pre."config.inc.php");
/**
 * Initialisation des variables
 */

$error_msg="";
$display_msg="";
$TheArrayAuthGlobals=array("action","param","q");

Tools::InitGlobals($TheArrayAuthGlobals,"");
Tools::BindToGlobal($_GET,$TheArrayAuthGlobals);
Tools::BindToGlobal($_POST,$TheArrayAuthGlobals);

if(isset($forward_action) && $forward_action!="")
	$action=$forward_action;
/**
 * Envoi des entêtes 
 */

header('Content-Type: text/html; charset=ISO-8859-1');

/**
 * Test des paramètres d'entrée
 */
switch($action)
{
	case "stations":
		$q=trim($q);
		if(strlen($q)>2)
		{
			$database->setQuery("SELECT * FROM station_de_mesure WHERE sta_code_station LIKE '".addslashes($q)."%' ORDER BY sta_code_station ASC");
			$myList=$database->loadObjectList();
			if(is_array($myList) && count($myList)>0)
			{
				foreach($myList as $curelement)
				{
					echo $curelement->sta_code_station."\n";
				}
			}
		}
		break;
	case "lotspoissons":
		$q=trim($q);
		if(strlen($q)>1)
		{
			$database->setQuery("SELECT lpp_code_lot FROM lot_de_poissons_preleves WHERE lpp_code_lot LIKE '".addslashes($q)."%' GROUP BY lpp_code_lot ORDER BY lpp_code_lot ASC;");
			$myList=$database->loadObjectList();
			if(is_array($myList) && count($myList)>0)
			{
				foreach($myList as $curelement)
				{
					echo $curelement->lpp_code_lot."\n";
				}
			}
		}
		break;
	case "intervenants":
		$q=trim($q);
		if(strlen($q)>2)
		{
			$database->setQuery("SELECT int_nom_intervenant,int_code_intervenant FROM intervenant WHERE int_nom_intervenant LIKE '".addslashes($q)."%' OR int_code_intervenant LIKE '".addslashes($q)."%' ORDER BY int_nom_intervenant ASC");
			$myList=$database->loadObjectList();
			if(is_array($myList) && count($myList)>0)
			{
				foreach($myList as $curelement)
				{
					echo $curelement->int_nom_intervenant." / ".$curelement->int_code_intervenant."|".$curelement->int_code_intervenant."\n";
				}
			}
		}
		break;
	case "libellecourseau":
		$q=trim($q);
		if(strlen($q)>2)
		{
			$database->setQuery("SELECT cea_nom_entite FROM entite_hydrographique WHERE cea_nom_entite LIKE '".addslashes($q)."%' ORDER BY cea_nom_entite ASC");
			$myList=$database->loadObjectList();
			if(is_array($myList) && count($myList)>0)
			{
				foreach($myList as $curelement)
				{
					echo $curelement->cea_nom_entite."\n";
				}
			}
		}
		break;
	default:
		break;
}

/**
 * Sortie du résultat demandé
 */

/**
 * Fin du script
 */
die();
?>