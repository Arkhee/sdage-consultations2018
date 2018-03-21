<?php
/*
 * diren-pcb
 * Created on 8 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : generate_classfiles.php
 * Description : 
 * Génère les fichiers de classe, décrivant le fonctionnement des formulaires de MDTB
 * Ces fichiers de classe sont basés sur la description SQL des tables
 * La description SQL est fournie dans les fichiers json générés par parsesql.php
 */
$path_pre="../";
require_once("../local.lang/fr.inc.php");
require_once("utils.class.php");
require_once("../classes/database.php");
require_once("../classes/mdtb.class.php");
require_once("../classes/tools.class.php");
require_once("../local.config.inc.php");
if(file_exists("../local.classes/userdefined.class.php"))
	include_once("../local.classes/userdefined.class.php");

if(!isset($_GET["pass"]) || $_GET["pass"]!="class")
	die("Pas les droits");
$overwrite=0;
if(isset($_GET["overwrite"]) && $_GET["overwrite"]=1)
	$overwrite=1;
$json_path="importdata/";
$tpl_path="codetemplates/";
$myClassList=ImportUtils::getClassList($json_path);
if($myClassList!==false)
{
	$myClassDescriptionArray=array();
	$myTemplate=file_get_contents($tpl_path."_mdtb_templatefile.class.phpt");
	//echo "Liste : ".Tools::Display($myClassList);
	foreach($myClassList as $curtable)
	{
		if($curtable=="")
			continue;
		if(!file_exists($json_path.$curtable.".json") || !is_readable($json_path.$curtable.".json"))
			continue;
		$myTableDescriptionTxt=file_get_contents($json_path.$curtable.".json");
		$myTableDescription=json_decode($myTableDescriptionTxt);
		if(!is_object($myTableDescription))
			continue;
		//die("description : ".$curtable." => ".Tools::Display($myTableDescription).Tools::Display($myTableDescriptionTxt));
		$myClassDescriptionArray[$curtable]=$myTableDescription;
	}
	foreach($myClassDescriptionArray as $curtable => $curdescription)
	{
		$myFileName="../local.classes/".$classprefix."".$curtable.".class.php";
		if(($overwrite==1 && file_exists($myFileName) && is_writable($myFileName)) ||  !file_exists($myFileName) )
		{
			
			$myTranslations="";
			if(!isset($langtable[$curtable]))
					$myTranslations.="if(!isset(\$langtable[\"".$curtable."\"])) \$langtable[\"".$curtable."\"]=\"".$curtable."\";\n";
			$myFields="";
			$myFullFieldsArray=array();
			$mySearchable=array();
			$myDisplayInSearchList=array();
			$resetIndex=true;
			foreach($curdescription->create_table_fields as $key=>$curfield)
			{
				$myFullFieldsArray[]=$key;
				if(!isset($curfield->type))
					continue;
				if($key!="" && $key!=$curdescription->tablekey)
					$mySearchable[]=$key;
				if(!isset($langtable[$key]))
					$myTranslations.="if(!isset(\$langtable[\"".$key."\"])) \$langtable[\"".$key."\"]=\"".$key."\";\n";
				$myTmpField=ImportUtils::fieldFromDescription($curdescription,$key,$resetIndex);
				if(trim($myTmpField)!="")
					$myFields.="\t\t".$myTmpField."\n";
					
				$myCurDispInSearch=1;
				if(isset($curdescription->create_table_fields->$key->options->pos))
					if(strlen($curdescription->create_table_fields->$key->options->pos)==4)
					{
						$myCurDispInSearch=$curdescription->create_table_fields->$key->options->pos[3];
					}
				if($myCurDispInSearch==1)
					$myDisplayInSearchList[]=$key;
					
				$resetIndex=false;
			}
			
			$curfile=$myTemplate;
			$curfile=str_replace("[TRANSLATIONS]",$myTranslations,$curfile);
			$curfile=str_replace("[FIELDSLIST]",$myFields,$curfile);
			$curfile=str_replace("[CLASSNAME]",$classprefix.$curdescription->tablename,$curfile);
			$curfile=str_replace("[TABLENAME]",$curdescription->tablename,$curfile);
			$curfile=str_replace("[KEYNAME]",$curdescription->tablekey,$curfile);
			$curfile=str_replace("[TABLELABEL]",$curdescription->tablename,$curfile);

			$myCurEvents="";
			
			//echo "Création des userdefined : ".Tools::Display($myPrefsUserDefined);
			if(isset($ThePrefs->userdefined[$classprefix.$curdescription->tablename]))
				$myPrefsUserDefined=$ThePrefs->userdefined[$classprefix.$curdescription->tablename];
				
			if(isset($myPrefsUserDefined) && is_array($myPrefsUserDefined) && count($myPrefsUserDefined)>0)
			{
				//echo __LINE__ . "Cur events ".Tools::Display($myPrefsUserDefined);
				foreach($myPrefsUserDefined as $curaction=>$curmethod)
				{
					if($curmethod!="")
						$myCurEvents.="\t\t\$this->add_event_handler(\"".$curaction."\",\"".$curmethod."\");\n";	
				}
			}
			$curfile=str_replace("[EVENT_HANDLER_ACTION]","\n".$myCurEvents,$curfile);
						
			if(isset($curdescription->tableoptions->sort_field) 
				&& $curdescription->tableoptions->sort_field!=""
				&& in_array($curdescription->tableoptions->sort_field,$myFullFieldsArray))
				$curfile=str_replace("[DEFAULTORDERFIELD]",$curdescription->tableoptions->sort_field,$curfile);
			else
			$curfile=str_replace("[DEFAULTORDERFIELD]",$curdescription->tablekey,$curfile);

			if(isset($curdescription->tableoptions->nb_per_page) 
				&& intval($curdescription->tableoptions->nb_per_page)>0)
				$curfile=str_replace("[DEFAULTNBPERPAGE]",$curdescription->tableoptions->nb_per_page,$curfile);
			else
				$curfile=str_replace("[DEFAULTNBPERPAGE]",20,$curfile);
			
			if(isset($curdescription->tableoptions->sort_order) 
				&& ( strtoupper($curdescription->tableoptions->sort_order)=="ASC"
						|| strtoupper($curdescription->tableoptions->sort_order)=="DESC"))
				$curfile=str_replace("[DEFAULTSORTORDER]",$curdescription->tableoptions->sort_order,$curfile);
			else
				$curfile=str_replace("[DEFAULTSORTORDER]","ASC",$curfile);
			$curfile=str_replace("[SEARCHABLEFIELDS]","\"".implode("\",\"",$mySearchable)."\"",$curfile);
			$curfile=str_replace("[DISPLAYINSEARCHFIELDS]","\"".implode("\",\"",$myDisplayInSearchList)."\"",$curfile);
			
			$myTxtChildrenTemplate=ImportUtils::setRelationTemplate($myClassDescriptionArray,$curdescription->tablename,$classprefix);
			//echo "Template pour le fichier ".$curtable["description"]->name." : <pre>".print_r($myChildrenTemplate,true)."</pre>\n";
			$curfile=str_replace("[CHILDRENLIST]",$myTxtChildrenTemplate,$curfile);
			//die("Fichier ".$curdescription->tablename.": ".Tools::Display($curfile));
			file_put_contents($myFileName,$curfile);
			echo("Création de : ".$myFileName."<br />\n");
		}
		else
			echo "Fichier ".$myFileName." existe déjà ou non inscriptible<br />\n";
	}
}


?>
<a href="index.php">Retour</a>