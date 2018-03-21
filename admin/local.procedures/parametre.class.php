<?php
/*
 * diren-pcb
 * Created on 15 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : parametre.class.php
 * Description : 
 * Classes de manipulation des fichiers d'import
 * clsDataFile parse et manipule les données issues d'un fichier csv, afin d'en faciliter l'accès
 * clsParametre permet la correspondance entre des paramètres issus d'un fichier et les paramètres en base
 * Appelé par import-donnees-poisson.php
 */


define("IMPORT_HEADER_ROW",7); 
define("IMPORT_FIRST_COL",0); 
define("IMPORT_FIRST_ROW",8); 

define("IMPORTSEDIMENT_HEADER_ROW",0); 
define("IMPORTSEDIMENT_FIRST_COL",0); 
define("IMPORTSEDIMENT_FIRST_ROW",1); 

define("IMPORTRENDU_HEADER_ROW",1); 
define("IMPORTRENDU_FIRST_COL",0); 
define("IMPORTRENDU_FIRST_ROW",4); 

define("DESCR_COL_NAME",4);
define("DESCR_COL_COLNB",DESCR_COL_NAME+1);
define("DESCR_COL_IMPORTOUINON",DESCR_COL_NAME+2);
define("DESCR_COL_TABLE",DESCR_COL_NAME+3);
define("DESCR_COL_CHAMP",DESCR_COL_NAME+4);
define("DESCR_COL_FORMAT",DESCR_COL_NAME+5);
define("DESCR_COL_FORMATDESCRIPTION",DESCR_COL_NAME+6);
define("DESCR_COL_CODESANDRE",DESCR_COL_NAME+7);
define("DESCR_COL_SANDREOUINON",DESCR_COL_NAME+8);
define("DESCR_COL_CALCUL",DESCR_COL_NAME+9);
define("DESCR_COL_EXPORT_SIMPLE",DESCR_COL_NAME+13);
define("DESCR_COL_EXPORT_COMPLET",DESCR_COL_NAME+14);
define("DESCR_COL_EXPORT_LIBELLE",DESCR_COL_NAME+16);
define("DESCR_COL_EXPORT_FORMAT",DESCR_COL_NAME+17);
define("DESCR_COL_EXPORT_GROUP",DESCR_COL_NAME+18);


class clsParametre
{
	var $_db=null;
	var $parameters=array();
	var $curobject=array();
	var $fields_without_table=array();
	var $tables_to_fields=array();
	var $array2col=array();
	var $object_tables=array();
	var $import_batch;
	var $code_import;
    function clsParametre(&$database)
    {
    	$this->_db=$database;
    	$this->_db->setQuery("SELECT id_parametre,par_code_parametre FROM parametre;");
    	$myTableResult=$this->_db->loadObjectList();
    	if(is_array($myTableResult) && count($myTableResult)>0)
    		foreach($myTableResult as $curresult)
    			$this->parameters[$curresult->par_code_parametre]=$curresult->id_parametre;
    }
    
    function cleanBatch($theBatchName,$theBatchAuthor,$theBatchCleanValidated=false)
    {
    	$debug=false;
    	$cleaned=array();
    	if($theBatchCleanValidated)
    		$this->_db->setQuery("SELECT * FROM a_import_batch WHERE batch_name='".$theBatchName."' AND batch_author LIKE '".$theBatchAuthor."' ;");
    	else
    		$this->_db->setQuery("SELECT * FROM a_import_batch WHERE batch_name='".$theBatchName."' AND batch_author LIKE '".$theBatchAuthor."' AND batch_validated=1 ;");
    	$myBatchList=$this->_db->loadObjectList();
    	if($debug) echo "Démarrage du nettoyage des batchs".BR;
    	if(is_array($myBatchList) && count($myBatchList)>0)
    	{
    		if($debug) echo "Batchs trouvés : ".count($myBatchList).BR;
    		//echo Tools::Display($myBatchList);
    		foreach($myBatchList as $curbatch)
    		{
    			$this->_db->setQuery("SELECT * FROM a_import_batch_data WHERE a_import_batch_id_a_import_batch=".$curbatch->id_a_import_batch." GROUP BY batchdata_table;");
    			$myTablesList=$this->_db->loadObjectList();
    			if(is_array($myTablesList) && count($myTablesList)>0)
    			{
		    		//echo "Tables trouvées pour le batch n° ".$curbatch->id_a_import_batch." => ".count($myTablesList).BR;
		    		if($debug) echo Tools::Display($myTablesList);
    				foreach($myTablesList as $curtable)
    				{
    					$this->_db->setQuery("SELECT * FROM a_import_batch_data WHERE batchdata_table='".$curtable->batchdata_table."' AND a_import_batch_id_a_import_batch=".$curbatch->id_a_import_batch.";");
    					$myRecordsList=$this->_db->loadObjectList();
    					if(is_array($myRecordsList) && count($myRecordsList)>0)
    					{
    						if($debug) echo "Enregistrements trouvées pour la table : ".$curtable->batchdata_table." ".count($myRecordsList).BR;
    						$cleaned[$curtable->batchdata_table]=0;
    						foreach($myRecordsList as $currecord)
    						{
    							$fieldKey=$curtable->batchdata_table;
    							if($curtable->batchdata_table=="analyse")
    								$fieldKey="resultat";
    							$this->_db->setQuery("DELETE FROM ".$curtable->batchdata_table." WHERE id_".$fieldKey."=".$currecord->batchdata_record_id);
    							if($debug) echo "Requête d'effacement : ".$this->_db->getQuery();
    							if($this->_db->query())
    								$cleaned[$curtable->batchdata_table]++;
    						}
    					}
    					
    					$this->_db->setQuery("DELETE FROM a_import_batch_data WHERE batchdata_table='".$curtable->batchdata_table."' AND a_import_batch_id_a_import_batch=".$curbatch->id_a_import_batch.";");
    					if($debug) echo "Requête d'effacement : ".$this->_db->getQuery();
    					$this->_db->query();
    				}
    			}
    			else
    				if($debug) echo "Aucune Table trouvée pour le batch n° ".$curbatch->id_a_import_batch.BR;
    			$this->_db->setQuery("DELETE FROM a_import_batch WHERE id_a_import_batch=".$curbatch->id_a_import_batch.";");
	    		$this->_db->query();
    		}
    	}
    	else
    		echo "Erreur récupération des batchs"; //.Tools::Display($this->_db->getQuery());
    	return $cleaned;
    }
    
    function loadBatch($theBatchId)
    {
    	$this->_db->setQuery("SELECT * FROM a_import_batch WHERE id_a_import_batch=".intval($theBatchId));
    	$myList=$this->_db->loadObjectList();
    	if(is_array($myList) && count($myList)>0)
    	{
    		$this->import_batch=$myList[0];
			return true;    		
    	}
    	return false;
    }
    
    function initBatch($theImportName,$theUserName="import",$theFileName="")
    {
    	$this->import_batch=new stdClass();
    	$this->import_batch->batch_name=$theImportName;
    	$this->import_batch->batch_date=date("Y-m-d");
    	$this->import_batch->batch_time=date("H:i:s");
    	$this->import_batch->batch_author=$theUserName;
    	$this->import_batch->batch_validated=0;
    	$this->import_batch->batch_filename=$theFileName;
    	return $this->_db->insertObject("a_import_batch",$this->import_batch,"id_a_import_batch");
    }
    
    function batch_add_line($theTable,$theRecord)
    {
    	$myReturn=false;
    	//echo "Objet ? ".(is_object($this->import_batch)?"oui":"non")."<br >\n";
    	//echo "Isset ? ".(isset($this->import_batch->id_a_import_batch)?"oui":"non")."<br >\n";
    	//echo ">0 ? ".( $this->import_batch->id_a_import_batch>0?"oui":"non")."<br >\n";
    	if(	is_object($this->import_batch)
    		&& isset($this->import_batch->id_a_import_batch)
    		&& $this->import_batch->id_a_import_batch>0 )
    	{
    		$myObj=new stdClass();
    		$myObj->batchdata_table=$theTable;
    		$myObj->batchdata_record_id=$theRecord;
    		$myObj->a_import_batch_id_a_import_batch=$this->import_batch->id_a_import_batch;
    		$myReturn = $this->_db->insertObject("a_import_batch_data",$myObj,"id_a_import_batch_data");
    		$myReturn=$myObj->id_a_import_batch_data;
    		if($myReturn<=0)
    			echo "Erreur ajout : ".$this->_db->getErrorMsg()." (objet batch ".Tools::Display($this->import_batch).")<br />";
    	}
		return $myReturn;
    }
    
    function rechercheParametre($paramName,$importDescription)
    {
    	$paramName=addslashes($paramName);
    	$this->_db->setQuery("SELECT * FROM (a_import_colonne as aic, rel_import_colonne_import_description as ricid, a_import_description as aid) LEFT JOIN rel_import_colonne_synonyme as ris ON ris.a_import_colonne_id_a_import_colonne=aic.id_a_import_colonne LEFT JOIN a_synonyme AS `asy` ON asy.id_a_synonyme=ris.a_synonyme_id_a_synonyme WHERE (ricid.a_import_description_id_a_import_description=aid.id_a_import_description AND aid.id_a_import_description='".$importDescription."' AND aic.id_a_import_colonne=ricid.a_import_colonne_id_a_import_colonne) AND (asy.cd_synonyme='".$paramName."' OR aic.libelle_import_colonne='".$paramName."');");
    	$myObj=null;
    	$myObjList=$this->_db->loadObjectList();
    	$myArrResults=array();
    	if(is_array($myObjList) && count($myObjList)>0)
    	{
    		foreach($myObjList as $curres)
    			$myArrResults[]=$curres->cd_import_colonne;
    		return $myArrResults;	
    	}
    	//echo "Requête : ".$this->_db->getQuery();
    	return array();
    	//$this->_db->setQuery("SELECT * FROM a_import_colonne as aic LEFT JOIN rel_import_colonne_synonyme as ris ON ris.a_import_colonne_id_a_import_colonne=aic.id_a_import_colonne LEFT JOIN a_synonyme as ON as.id_a_synonyme=ris.a_synonyme_id_a_synonyme WHERE (as.cd_synonyme='".$paramName."' OR aic.libelle_import_colonne='".$paramName."';");
    }
    
    function cleanLine()
    {
    	$this->curobject=array();
    	return true;
    }
    
    function setArrayToColAssociation($theArray)
    {
    	$this->array2col=$theArray;
    }
    
    function initCodeToParamId()
    {
    	$this->_db->setQuery("SELECT cd_import_colonne,parametre_id_parametre FROM `rel_import_colonne_parametre`,a_import_colonne WHERE a_import_colonne.id_a_import_colonne=rel_import_colonne_parametre.`a_import_colonne_id_a_import_colonne`");
    	$myList=$this->_db->loadObjectList();
    	$myArrayCodeToParamId=array();
    	if(is_array($myList) && count($myList)>0)
    	{
    		foreach($myList as $curitem)
    		{
    			$myArrayCodeToParamId[$curitem->cd_import_colonne]=$curitem->parametre_id_parametre;
    		}
    		return $myArrayCodeToParamId;
    	}
    	return false;
    }
    
    function checkValues()
    {
    	global $classprefix,$template_name,$path_abs;
    	
    	$this->tables_to_fields=array();
    	$this->fields_without_table=array();
    	//echo "Table de correspondance : ".Tools::Display($this->array2col);
    	foreach($this->array2col as $curkey=>$curobjectarray)
    	{
    		foreach($curobjectarray as $curobject)
    		{
	    		//echo "Objet en cours : ".Tools::Display($curobject);
	    		if($curobject->type_import_colonne=="")
	    			$this->fields_without_table[]=$curobject->type_import_colonne;
	    		else
	    			$this->tables_to_fields[$curobject->type_import_colonne][]=$curobject->cd_import_colonne;
    		}
    	}
    	
    	foreach($this->tables_to_fields as $key=>$val)
    	{
    		if(class_exists($classprefix.$key))
    		{
    			$myClassName=$classprefix.$key;
    			$this->object_tables[$key]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
    		}
    	}
    }
    
    function setParam(&$colindexarray,$value)
    {
    	if(!is_array($colindexarray))
    	{
			die("Colindex n'est pas un tableau (val : ".$value.") : ".Tools::Display($colindexarray)."<br>\n");    		
    		return false;
    	}
    	foreach($colindexarray as $colindex)
    	{
	    	$this->curobject[$colindex->cd_import_colonne]->type=$colindex;
	    	$this->curobject[$colindex->cd_import_colonne]->value=$value;
    	}
    	//die("Sauvegarde de la colonne ".Tools::Display($colindex)." dans le prélèvement ".$prelindex." avec la valeur ".$value."<br>\n");    	
    }
    
    function formatInputData($theValue,$theType)
    {
    	$myValue=$theValue;
		if($theType->format_import_colonne=="date")
		{
			switch($theType->valeurformat_import_colonne)
			{
				case "jj/mm/aa":
				case "jj/mm/aaaa":
					if(strlen($myValue)==8)
						list($jour,$mois,$annee)=sscanf($myValue,"%d/%d/%2s");
					elseif(strlen($theValue)==10)
						list($jour,$mois,$annee)=sscanf($myValue,"%d/%d/%4s");
					else
						return $myValue;
					//echo "j/m/a => $jour,$mois,$annee ($theValue) <br />\n";
					if(intval($annee)>=50 && intval($annee)<100)
						$myAnnee=1900+intval($annee);
					elseif(intval($annee)<50)
						$myAnnee=2000+intval($annee);
					else
						$myAnnee=intval($annee);
					$myValue=date("Y-m-d",mktime(1,1,1,intval($mois),intval($jour),$myAnnee));
					//die( "Valeur ".$theValue." convertie en ".$myValue." (mktime(intval($mois),intval($jour),$myAnnee) : ".mktime(1,1,1,intval($mois),intval($jour),$myAnnee).")<br>\n");
					break;
				default:
					$myValue=date("Y-m-d",strtotime($myValue));
					break;
			}
		}
		return $myValue;
    }
    
    function formatOutputData($theValue,$theType)
    {
    	$myValue=$theValue;
		if($theType->format_import_colonne=="date")
		{
			switch($theType->valeurformat_import_colonne)
			{
				case "jj/mm/aa":
				case "jj/mm/aaaa":
					$myDate=strtotime($theValue);
					$myValue=date("d/m/y",$myDate);
					break;
				default:
					break;
			}
		}
		return $myValue;
    }
    
    function saveLine()
    {
    	
    	/*
    	 * Premiere partie, on initie le prélèvement en soi
    	 */
    	
    	/*
    	 * 
    	 * Ordre des opérations :
    	 * - Traiter preleveurs
    	 * - Traiter point_de_prelevement
    	 * - Traiter operation_prelevement_biologique
    	 * - Traiter prelevement_elementaire_biologique
    	 * - Traiter zones_de_peche
    	 * - Traiter lot_poisson_preleve => enfant
    	 * - Traiter lot_poisson_preleve => parent
    	 * - Traiter echantillon
    	 * - Traiter analyse
    	 * 
    	 */
 	
    	/*
    	 * Import préleveurs
    	*/
    	$this->object_tables["preleveurs"]->recNewRecord();
    	
    	/*
    	$this->object_tables["preleveurs"]->recSetValue("id_preleveurs",null);
		$this->object_tables["preleveurs"]->recSetValue("prl_nom","");
		$this->object_tables["preleveurs"]->recSetValue("prl_prenom","");
		$this->object_tables["preleveurs"]->recSetValue("prl_adresse","");
		$this->object_tables["preleveurs"]->recSetValue("prl_codepostal","");
		$this->object_tables["preleveurs"]->recSetValue("prl_ville","");
		$this->object_tables["preleveurs"]->recSetValue("prl_pays","");
		$this->object_tables["preleveurs"]->recSetValue("prl_telephone","");
		$this->object_tables["preleveurs"]->recSetValue("prl_telephone_mobile","");
		$this->object_tables["preleveurs"]->recSetValue("prl_societe","");
		$this->object_tables["preleveurs"]->recSetValue("prl_email","");
    	*/
    	
    	foreach($this->curobject as $key=>$curattrib)
    	{
    		if($curattrib->type->type_import_colonne=="preleveurs")
    		{
    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
    			$this->object_tables["preleveurs"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
    		}
    	}
    	if($this->object_tables["preleveurs"]->recGetValue("prl_nom")=="")
    		$this->object_tables["preleveurs"]->recSetValue("prl_nom"," ");
    	$this->object_tables["preleveurs"]->recStore();
    	
    	if($this->object_tables["preleveurs"]->recKeyValue()<=0)
    		die(__LINE__." => Pas d'enregistrement");
    	
    	/*
    	 * Import Point de prelevement
    	 */
    	
    	$this->object_tables["point_de_prelevement"]->recNewRecord();
    	
    	/*
    	$this->object_tables["point_de_prelevement"]->recSetValue("id_point_de_prelevement",null);
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_code","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_objet_principal","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_recommandations_sur_lieu","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_date_mise_en_service","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_date_mise_hors_service","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_nom_station","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_profondeur_recommandee","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_commentaires","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_mode_obtention_coordonnees","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x_amont","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y_amont","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x_aval","");
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y_aval","");
		$this->object_tables["point_de_prelevement"]->recSetValue("station_de_mesure_id_station_de_mesure","");
		*/
    	
    	foreach($this->curobject as $key=>$curattrib)
    	{
    		if($curattrib->type->type_import_colonne=="point_de_prelevement")
    		{
    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
    			$this->object_tables["point_de_prelevement"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
				if($curattrib->type->parametre_import_colonne=="sta_code_station")
				{
					$this->_db->setQuery("SELECT * FROM #__station_de_mesure WHERE sta_code_station='".$curattrib->value."';");
					$myObj=null;
					if($this->_db->loadObject($myObj))
						$this->object_tables["point_de_prelevement"]->recSetValue("sta_code_station",$myObj->sta_code_station );
				}    			
    		}
    	}
    	$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x_amont",$this->object_tables["point_de_prelevement"]->recGetValue("ppr_coord_x"));
		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y_amont",$this->object_tables["point_de_prelevement"]->recGetValue("ppr_coord_y"));
    	
    	if($this->object_tables["point_de_prelevement"]->recGetValue("ppr_objet_principal")=="")
    		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_objet_principal","-");
		$this->object_tables["point_de_prelevement"]->recStore();
    	if($this->object_tables["point_de_prelevement"]->recKeyValue()<=0)
    		die(__LINE__." => Pas d'enregistrement pour le point de prélèvement");
		$myIDPointDePrelevement=$this->object_tables["point_de_prelevement"]->recKeyValue();
		
		/*
		 * Import operation_prelevement_biologique
		 */
		
		$this->object_tables["operation_prelevement_biologique"]->recNewRecord();
		
    	/*
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("id_operation_prelevement_biologique",null);
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_date_debut_prelevement","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_heure_debut_prelevement","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_date_fin_prelevement","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_heure_fin_prelevement","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_code_intervenant_producteur","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_code_intervenant_preleveur","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("ppr_id_point_de_prelevement",$myIDPointDePrelevement);
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_longueur_site_prospectee","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_largeur_moyenne_lame_eau","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_interpretation_resultats","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_qualification_resultats","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_statut_resultats","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_commentaires","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_mode_conservation_principal_echantillons","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_mode_conservation_secondaire_echantillons","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_situation_particuliere","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_superficie_mouillee_totale","");
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_objectif_opb","");
		*/
		
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("ppr_id_point_de_prelevement",$myIDPointDePrelevement);
		
		foreach($this->curobject as $key=>$curattrib)
    		if($curattrib->type->type_import_colonne=="operation_prelevement_biologique")
    		{
    			//echo "Attribut opb : ".Tools::Display($curattrib);
    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
    			//echo "Valeur : ".$curattrib->value."<br />\n";
    			$this->object_tables["operation_prelevement_biologique"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
    		}
		$this->object_tables["operation_prelevement_biologique"]->recSetValue("ppr_id_point_de_prelevement",$myIDPointDePrelevement);
		
		//echo "Champs de la table opb : ".Tools::Display($this->object_tables["operation_prelevement_biologique"]->recGetFieldsList());
		/*
		if($this->object_tables["operation_prelevement_biologique"]->recGetValue("opb_date_debut_prelevement")==""
				|| $this->object_tables["operation_prelevement_biologique"]->recGetValue("opb_date_debut_prelevement")=="0000-00-00 00:00:00"
				|| is_null($this->object_tables["operation_prelevement_biologique"]->recGetValue("opb_date_debut_prelevement")==""))
			die("Err OPB : ".Tools::Display($this->object_tables["operation_prelevement_biologique"]->recGetRecord())." vs ".Tools::Display($this->curobject));
		*/
		$this->object_tables["operation_prelevement_biologique"]->recStore();
    	if($this->object_tables["operation_prelevement_biologique"]->recKeyValue()<=0)
    		die(__LINE__." => Pas d'enregistrement");
    		
		/*
		 * Import prelevement_elementaire_biologique
		 */
		
		$this->object_tables["prelevement_elementaire_biologique"]->recNewRecord();
		
    	/*
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("id_prelevement_elementaire_biologique",null);
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("opb_id_operation_prelevement_biologique",$this->object_tables["operation_prelevement_biologique"]->recKeyValue());
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("prl_id_preleveurs",$this->object_tables["preleveurs"]->recKeyValue());
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_code_prelevement_elementaire","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_materiel_utilise","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_vegetation_sur_prelevement","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_type_colmatage_placette","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_largeur_prospectee","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_longueur_propsectee","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_hauteur_eau_moyenne","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_surface_prospectee","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_distance_berge","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_volume_eau","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_commentaires","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_intensite_colmatage","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_type_diatomees_prelevees","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_facies_morpho_secondaire","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_groupe_prelevement_bio","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_zone_verticale_prospectee","");
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_profondeur_prelevement","");
		*/
		
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("opb_id_operation_prelevement_biologique",$this->object_tables["operation_prelevement_biologique"]->recKeyValue());
		$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("prl_id_preleveurs",$this->object_tables["preleveurs"]->recKeyValue());
		
		foreach($this->curobject as $key=>$curattrib)
    		if($curattrib->type->type_import_colonne=="prelevement_elementaire_biologique")
    		{
    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
    			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
    		}

		$this->object_tables["prelevement_elementaire_biologique"]->recStore();
    	if($this->object_tables["prelevement_elementaire_biologique"]->recKeyValue()<=0)
    		die(__LINE__." => Pas d'enregistrement");
		
    	/*
    	 * Import zones_de_peche
    	 */
    	global $classprefix,$template_name,$path_abs;
    	$myClassName=$classprefix."zones_de_peche";
    	if(!is_object($this->object_tables["zones_de_peche"]))
			$this->object_tables["zones_de_peche"]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
		$this->object_tables["zones_de_peche"]->recNewRecord();
		
    	/*
		$this->object_tables["zones_de_peche"]->recSetValue("id_zones_de_peche",null);
		$this->object_tables["zones_de_peche"]->recSetValue("zdp_date_creation","");
		$this->object_tables["zones_de_peche"]->recSetValue("zdp_code_zone","");
		$this->object_tables["zones_de_peche"]->recSetValue("zdp_libelle_zone","");
		$this->object_tables["zones_de_peche"]->recSetValue("zdp_coord_x","");
		$this->object_tables["zones_de_peche"]->recSetValue("zdp_coord_y","");
		$this->object_tables["zones_de_peche"]->recSetValue("peb_id_prelevement_elementaire_biologique","");
		$this->object_tables["zones_de_peche"]->recSetValue("zdp_commentaires","");
		$this->object_tables["zones_de_peche"]->recSetValue("zdp_mat_code_materiel","");
		*/
    	
    	foreach($this->curobject as $key=>$curattrib)
    		if($curattrib->type->type_import_colonne=="zones_de_peche")
    		{
				$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
    			$this->object_tables["zones_de_peche"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
    		}

		$myIdPEB=$this->object_tables["prelevement_elementaire_biologique"]->recKeyValue();
		$this->object_tables["zones_de_peche"]->recSetValue("peb_id_prelevement_elementaire_biologique",$myIdPEB);
		$this->object_tables["zones_de_peche"]->recStore();
    	if($this->object_tables["zones_de_peche"]->recKeyValue()<=0)
    		die(__LINE__." => Pas d'enregistrement");
				
    	//echo "Objet actuel : ".Tools::Display($this->curobject);
    	//$this->object_tables["point_de_prelevement"]->recStore();
    	//echo "Enregistrement préleveurs :".Tools::Display($this->object_tables["preleveurs"]->recGetRecord());
    	//echo "Enregistrement point_de_prelevement :".Tools::Display($this->object_tables["point_de_prelevement"]->recGetRecord());
   		
   		/*
   		 * Lot de poissons prélevés : parent et enfant(s) + relation
   		 */
   		/*
   		 * Champs pris en compte dans le fichier
			lot_de_poissons_preleves	lpp_code_lot
			lot_de_poissons_preleves	lpp_ns_diren_reference_echantillon
			lot_de_poissons_preleves	tax_code_taxon
			lot_de_poissons_preleves	lpp_effectif_lot
			lot_de_poissons_preleves	lpp_age_nombre_hivers
			lot_de_poissons_preleves	lpp_mode_determination_age
			lot_de_poissons_preleves	lpp_poids
			lot_de_poissons_preleves	lpp_taille_lot
			lot_de_poissons_preleves	lpp_sexe
   		 */
		$this->object_tables["lot_de_poissons_preleves"]->recNewRecord();
    	/*
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_lot","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_type_lot","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_effectif_lot","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_minimale","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_maximale","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_type_longueur","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_poids","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_sexe","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_maturite_poisson","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_prelevement_ecailles","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_age","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_resultat_liste_faunistique","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_degre_confiance_determination_taxon","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("peb_id_prelevement_elementaire_biologique","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("sup_code_support","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("tax_code_taxon","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_date_lyophilisation","");
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_diren_reference_echantillon","");
		*/
		
		//echo "Valeur des champs de la table : <br />\n";
		$myObjRef->lpp_code_lot = $this->fieldValue("lot_de_poissons_preleves","lpp_code_lot");
		$myObjRef->lpp_ns_diren_reference_echantillon = $this->fieldValue("lot_de_poissons_preleves","lpp_ns_diren_reference_echantillon");
		$myObjRef->tax_code_taxon = $this->fieldValue("lot_de_poissons_preleves","tax_code_taxon");
		$myObjRef->lpp_effectif_lot = $this->fieldValue("lot_de_poissons_preleves","lpp_effectif_lot");
		$myObjRef->lpp_age_nombre_hivers = $this->fieldValue("lot_de_poissons_preleves","lpp_age_nombre_hivers");
		$myObjRef->lpp_mode_determination_age = $this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_age");
		$myObjRef->lpp_poids = $this->fieldValue("lot_de_poissons_preleves","lpp_poids");
		$myObjRef->lpp_taille_lot = $this->fieldValue("lot_de_poissons_preleves","lpp_taille_lot");
		$myObjRef->lpp_sexe = $this->fieldValue("lot_de_poissons_preleves","lpp_sexe");
		
		/*
		 * Lot vaut plus que 3 OU un nombre indéterminé (-1) : création d'un lot parent seul
		 * Sinon, création d'un lot parent avec données éventuellement plus restreintes
		 */
		if(intval($myObjRef->lpp_effectif_lot)==0)
			$myObjRef->lpp_effectif_lot=-1;
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_lot",$myObjRef->lpp_code_lot);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_effectif_lot",$myObjRef->lpp_effectif_lot);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot",$myObjRef->lpp_taille_lot);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids",$myObjRef->lpp_poids);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe",$myObjRef->lpp_sexe);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers",$myObjRef->lpp_age_nombre_hivers);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_age",$myObjRef->lpp_mode_determination_age);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("tax_code_taxon",$myObjRef->tax_code_taxon);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_diren_reference_echantillon",$myObjRef->lpp_ns_diren_reference_echantillon);
		$this->object_tables["lot_de_poissons_preleves"]->recSetValue("peb_id_prelevement_elementaire_biologique",$myIdPEB);
		$this->object_tables["lot_de_poissons_preleves"]->recStore();
		$myIDLotParent=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
		$myRelLotClass=$classprefix."rel_lot_poissons_preleves";
		$myRelLot=new $myRelLotClass($this->_db,$template_name,basename(__FILE__),$path_abs,true);
		$myRelLot->recSetValue("id_rel_lot_poissons_preleves",null);
		$myRelLot->recSetValue("lpp_id_parent",$myIDLotParent);
		$myRelLot->recSetValue("lpp_id_enfant","");
		
		if($myObjRef->lpp_effectif_lot>0 && $myObjRef->lpp_effectif_lot<=3)
		{
			/*
			 * Lot vaut 1 à 3 : création d'un lot enfant individuel pour chaque, même si vide
			 * Création du lot parent en premier
			 */
			$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_lot","");
			if($myObjRef->lpp_effectif_lot==1)
			{
				/*
				 * Lot vaut 1 uniquement : inscription des détails des poissons dans le lot enfant
				 */
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
				$this->object_tables["lot_de_poissons_preleves"]->recStore();
				$myIDLotEnfant=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
				$myRelLot->recSetValue("id_rel_lot_poissons_preleves",null);
				$myRelLot->recSetValue("lpp_id_enfant",$myIDLotEnfant);
				$myRelLot->recStore();
			}		
			else
			{
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_effectif_lot","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_age","");

				for($i=1;$i<=$myObjRef->lpp_effectif_lot;$i++)
				{
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
					$this->object_tables["lot_de_poissons_preleves"]->recStore();
					$myIDLotEnfant=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
					$myRelLot->recSetValue("id_rel_lot_poissons_preleves",null);
					$myRelLot->recSetValue("lpp_id_enfant",$myIDLotEnfant);
					$myRelLot->recStore();
				}
			}
			
		}

		/*
		 * Import echantillon
		 */
		
		
		$this->object_tables["echantillon"]->recNewRecord();
		$this->object_tables["echantillon"]->recSetValue("id_lot",$myIDLotParent);
    	
    	foreach($this->curobject as $key=>$curattrib)
    	{
    		//echo "Intégration de ".$curattrib->value.", Type : ".Tools::Display($curattrib->type,true)."<br />\n";
    		if($curattrib->type->type_import_colonne=="echantillon")
    		{
				$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
    			$this->object_tables["echantillon"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
    			//echo "Colonne échantillon : ".$curattrib->type->parametre_import_colonne." => ".$curattrib->value."<br />\n";
    		}
    	}
    	
    	/*
    	 * Si aucune référence n'est fournie pour l'échantillon, on ne stocke pas les paramètres
    	 */
    	
		if($this->object_tables["echantillon"]->recGetValue("ech_reference_echantillon")!="")
		{
			$myStoreReturn=$this->object_tables["echantillon"]->recStore();
			if(!$myStoreReturn)
				die(__LINE__." => Erreur de création de l'enregistrement : ".$this->object_tables["echantillon"]->recDBError());
			$myIDEchantillon=$this->object_tables["echantillon"]->recKeyValue();
			//die($this->_db->getQuery());
			
			
			/*
			 * Import des résultats : analyse 
			 */
			 
			 
			$this->object_tables["analyse"]->recNewRecord();
	    	
	    	foreach($this->curobject as $key=>$curattrib)
	    		if($curattrib->type->type_import_colonne=="analyse" && $curattrib->type->type_import_colonne!="ana_resultat")
	    		{
					$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["analyse"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
	    	
	    	//echo __LINE__." => Objet analyse préparé :".Tools::Display($this->object_tables["analyse"]->recGetRecord());
	    	
	    	$myAssociationArrayCodeToParamId=$this->initCodeToParamId();
	    	if($myAssociationArrayCodeToParamId!==false)
	    	{
	    		//echo __LINE__." => Tableau associatif OK !<br />\n";
		    	$this->object_tables["analyse"]->recSetValue("ech_id_echantillon",$myIDEchantillon);
		    	foreach($this->curobject as $key=>$curattrib)
		    	{
		    		if($curattrib->type->type_import_colonne=="analyse" && $curattrib->type->parametre_import_colonne=="ana_resultat")
		    		{
			    		//echo __LINE__." => Colonne :" .$curattrib->type->type_import_colonne.", type : ".$curattrib->type->parametre_import_colonne."  !<br />\n";
		    			if(isset($myAssociationArrayCodeToParamId[$curattrib->type->cd_import_colonne]))
		    			{
							$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
							if($curattrib->value!="")
							{
								$this->object_tables["analyse"]->recSetValue($this->object_tables["analyse"]->recKeyName(),null);
				    			$this->object_tables["analyse"]->recSetValue("par_id_parametre",$myAssociationArrayCodeToParamId[$curattrib->type->cd_import_colonne]);
				    			$this->object_tables["analyse"]->recSetValue("ana_resultat","");
				    			$this->object_tables["analyse"]->recSetValue("ana_resultat",$curattrib->value);
								$this->object_tables["analyse"]->recStore();
							}
							//die(Tools::Display($this->_db->getQuery()));
		    			}
		    		}
		    	}
	    	}
		}
			
    	return true;
    	
    }
    
    function saveLineSediments()
    {
    	static $previousItems=array();
    	static $relCodesAEIntSandre=null;
    	/*
    	 * Premiere partie, on initie le prélèvement en soi
    	 */
    	
    	/*
    	 * 
    	 * Ordre des opérations :
    	 * - (Traiter point_de_prelevement : optionnel seulement si code point fourni)
    	 * - Traiter operation_prelevement_physicochimique_microbio
    	 * - Traiter prelevement_echantillon_physico_chimique
    	 * - Traiter echantillon
    	 * - Traiter analyse
    	 * 
    	 */
 	
 		/*
 		 * Préparation des tables de correspondance
 		 */
 		
 		if(!is_array($relCodesAEIntSandre))
 		{
 			$relCodesAEIntSandre=array();
 			global $classprefix,$template_name,$path_abs;
 			$myRelAEIntClassName=$classprefix."rel_intervenant_ae_sandre";
 			$relCodesAEIntSandreObjet=new $myRelAEIntClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
 			$relCodesAEIntSandreObjet->recSearch();
 			$relCodesAEIntSandreObjet->recFirst();
 			if($relCodesAEIntSandreObjet->recFirst() && $relCodesAEIntSandreObjet->recCount()>0)
	 			do
	 			{
	 				$myObj=$relCodesAEIntSandreObjet->recGetRecord();
	 				$relCodesAEIntSandre[$myObj->rias_code_ae]=$myObj->rias_code_sandre;
	 			} while($relCodesAEIntSandreObjet->recNext());
 		}
 	
    	/*
    	 * 
    	 * 
    	 * Import Point de prelevement
    	 * 
    	 * 
    	 */
    	
    	$this->object_tables["point_de_prelevement"]->recNewRecord();
    	
		$mySearchObj=array();
		$myCurStationDeMesureId=-1;
		foreach($this->curobject as $key=>$curattrib)
    	{
    		if(	$curattrib->type->type_import_colonne=="point_de_prelevement" )
    		{
    			$myCurTypeColonne=$curattrib->type->parametre_import_colonne;
    			switch($myCurTypeColonne)
    			{
	    			case "ppr_code":
	    				$cur_code_station=substr($curattrib->value,0,8);
	    				if(strlen($curattrib->value)>8)
	    				{
	    					$tmpCode=substr($curattrib->value,8,strlen($curattrib->value));
	    					$tmpCode=str_replace("-","",$tmpCode);
	    					//$tmpCode=intval($tmpCode);
		    				$mySearchObj[$myCurTypeColonne]=$tmpCode;
		    				if($mySearchObj[$myCurTypeColonne]==0)
		    					$mySearchObj[$myCurTypeColonne]="";
		    				else
		    					$mySearchObj[$myCurTypeColonne]=strval($mySearchObj[$myCurTypeColonne]);
	    				}
	    				break;
	    			case "id_station_de_mesure":
						$this->_db->setQuery("SELECT * FROM #__station_de_mesure WHERE sta_code_station='".$curattrib->value."';");
						$myObj=null;
						if($this->_db->loadObject($myObj))
							$myCurStationDeMesureId=$myObj->id_station_de_mesure;
	    				$mySearchObj[$myCurTypeColonne]=$this->formatInputData($myCurStationDeMesureId,$curattrib->type);
	    				break;
    			}
    		}
    	}
    	if($mySearchObj["sta_code_station"]=="")
    		$mySearchObj["sta_code_station"]=$cur_code_station;
    		
    	if(isset($mySearchObj["ppr_code"]) && $mySearchObj["ppr_code"]!="")
    	{
    		
    		/*
    		 * Traitement de l'ajout du point de prélèvement Uniquement si le code du point n'est pas nul
    		 * Création d'un point uniquement si existant dans les données AE.
    		 * On considère que s'il n'y a pas de point on est sur un cours d'eau
    		 */
    		//$mySearchObj["ppr_code"]=substr($mySearchObj["sta_code_station"],0,3);
    				
	    	$myTextSearchSQL="";
			foreach($mySearchObj as $key=>$val)
				$myTextSearchSQL.=(($myTextSearchSQL!="")?" AND ":"")."point_de_prelevement.".$key."='".addslashes($val)."'";
			if(!isset($previousItems["point_de_prelevement"][$myTextSearchSQL]))
			{
				//echo "Chaine de recherche : ".$myTextSearchSQL."<br />\n";
				
				$this->object_tables["point_de_prelevement"]->recSQLSearch($myTextSearchSQL);
		    	//echo "Nombre d'enregistrements ".$this->object_tables["point_de_prelevement"]->recCount()."<br>\n";
		    	//echo __LINE__." Objet : ".Tools::Display($this->curobject);
		    	//echo( __LINE__." => Recherche : \n".$this->object_tables["point_de_prelevement"]->_db->getQuery());
		    	$myCurRecordNew=false;
				if(!$this->object_tables["point_de_prelevement"]->recFirst())
				{
					$myCurRecordNew=true;			
					$this->object_tables["point_de_prelevement"]->recNewRecord();
				}
		    	foreach($this->curobject as $key=>$curattrib)
		    	{
		    		if($curattrib->type->type_import_colonne=="point_de_prelevement")
		    		{
		    			$myCurTypeColonne=$curattrib->type->parametre_import_colonne;
		    			switch($myCurTypeColonne)
		    			{
			    			case "ppr_code":
			    				$cur_code_ppr=$curattrib->value;
			    				$cur_code_station=substr($curattrib->value,0,8);
		    					$tmpCode=substr($curattrib->value,8,strlen($curattrib->value));
		    					$tmpCode=str_replace("-","",$tmpCode);
		    					//$tmpCode=intval($tmpCode);
			    			
			    				$curattrib->value=$tmpCode;
				    			$this->object_tables["point_de_prelevement"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
			    				break;
			    			default:
				    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
				    			$this->object_tables["point_de_prelevement"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
			    				break;
		    			}
		    		}
		    	}
		    	
		    	if($this->object_tables["point_de_prelevement"]->recGetValue("ppr_code")=="")
		    		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_code",
		    											substr($this->object_tables["point_de_prelevement"]->recGetValue("sta_code_station"),0,3));
		    	$myCodePPR=$this->object_tables["point_de_prelevement"]->recGetValue("ppr_code");
		    	
	    		$this->object_tables["point_de_prelevement"]->recSetValue("id_station_de_mesure",$myCurStationDeMesureId);
		    	
		    	if($this->object_tables["point_de_prelevement"]->recGetValue("ppr_objet_principal")=="")
		    		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_objet_principal",$cur_code_ppr);
		
				//echo "Objet ppr : ".Tools::Display($this->object_tables["point_de_prelevement"]->recGetRecord());
		
				$this->object_tables["point_de_prelevement"]->recStore();
		    	if($this->object_tables["point_de_prelevement"]->recKeyValue()<=0)
		    		die(__LINE__." => Pas d'enregistrement pour le point de prélèvement".Tools::Display($this->_db->getErrorMsg()));
				$myIDPointDePrelevement=$this->object_tables["point_de_prelevement"]->recKeyValue();
				//echo __LINE__." => Batch d'ajout de la ligne";
				if($myCurRecordNew)
					$this->batch_add_line("point_de_prelevement",$myIDPointDePrelevement);
				$previousItems["point_de_prelevement"][$myTextSearchSQL]=$myIDPointDePrelevement;
				//echo "Nouveau point de prélèvement : ".$myTextSearchSQL." : ".($myCurRecordNew?"ajouté":"repris")."<br />\n";
				
			}
			else
				$myIDPointDePrelevement=$previousItems["point_de_prelevement"][$myTextSearchSQL];
    	}
    	else
    		$myIDPointDePrelevement=-1;
		//echo __LINE__." => Batch d'ajout de la ligne : ".Tools::Display($this->_db->getQuery());
		/*
		 * 
		 * 
		 * Import operation_prelevement_biologique
		 * 
		 * 
		 */
		 
		/*
		 * Si on est sur un point de prélèvement, on l'intègre dans la recherche
		 */
		if(!isset($myCurCodeStation))
			$myCurCodeStation="";
		$mySearchObj=array();
		if($myIDPointDePrelevement>0)
			$mySearchObj["id_point_de_prelevement"]=$myIDPointDePrelevement;
		$myCurStationDeMesureId=-1;
		foreach($this->curobject as $key=>$curattrib)
    	{
    		if(	$curattrib->type->type_import_colonne=="operation_prelevement_physicochimique_microbio" )
    		{
    			$myCurTypeColonne=$curattrib->type->parametre_import_colonne;
    			switch($myCurTypeColonne)
    			{
    				case "id_station_de_mesure":
						$this->_db->setQuery("SELECT * FROM #__station_de_mesure WHERE sta_code_station='".$curattrib->value."';");
						$myObj=null;
						if($this->_db->loadObject($myObj))
							$myCurStationDeMesureId=$myObj->id_station_de_mesure;
						$mySearchObj[$myCurTypeColonne]=$myCurStationDeMesureId;
	    				break;
	    			/*
	    			case "sta_code_station":
	    				if($curattrib->value=="" && $myCurCodeStation!="")
	    				{
		    				$mySearchObj[$myCurTypeColonne]=$this->formatInputData($myCurCodeStation,$curattrib->type);
	    				}
	    				else
	    					$mySearchObj[$myCurTypeColonne]=$this->formatInputData($curattrib->value,$curattrib->type);
	    				break;
	    			*/
	    			case "oppc_date_debut":
	    			case "oppc_heure_debut":
	    				$mySearchObj[$myCurTypeColonne]=$this->formatInputData($curattrib->value,$curattrib->type);
	    				break;
	    			case "oppc_int_code_intervenant_producteur":
	    				if(isset($relCodesAEIntSandre[$curattrib->value]))
	    					$curattrib->value=$relCodesAEIntSandre[$curattrib->value];
	    				$mySearchObj[$myCurTypeColonne]=$this->formatInputData($curattrib->value,$curattrib->type);
	    				break;
    				
    			}
    		}
    	}
    	$myTextSearchSQL="";
		foreach($mySearchObj as $key=>$val)
			$myTextSearchSQL.=(($myTextSearchSQL!="")?" AND ":"")."operation_prelevement_physicochimique_microbio.".$key."='".addslashes($val)."'";
		
		if(!isset($previousItems["operation_prelevement_physicochimique_microbio"][$myTextSearchSQL]))
		{
			//echo "Filtre de recherche pour OPPC : ".$myTextSearchSQL.BR;
			$this->object_tables["operation_prelevement_physicochimique_microbio"]->recSQLSearch($myTextSearchSQL);
			$myCurRecordNew=false;			
			if(!$this->object_tables["operation_prelevement_physicochimique_microbio"]->recFirst())
			{
				$this->object_tables["operation_prelevement_physicochimique_microbio"]->recNewRecord();
				$myCurRecordNew=true;			
			}
			//echo "Objet trouvé : ".Tools::Display($this->object_tables["operation_prelevement_physicochimique_microbio"]->recGetRecord());
			//echo "Dernière requête : ".$this->_db->getQuery();
			//echo "Cur Object : ".Tools::Display($this->curobject).BR;
			foreach($this->curobject as $key=>$curattrib)
	    		if($curattrib->type->type_import_colonne=="operation_prelevement_physicochimique_microbio")
	    		{
	    			//echo "Valeur : ".$curattrib->value.BR."Type : ".Tools::Display($curattrib->type).BR;
	    			if($curattrib->type->parametre_import_colonne=="oppc_int_code_intervenant_producteur")
		    			if(isset($relCodesAEIntSandre[$curattrib->value]))
		    					$curattrib->value=$relCodesAEIntSandre[$curattrib->value];
	    				
	    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["operation_prelevement_physicochimique_microbio"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
	    	if($myIDPointDePrelevement>0)
				$this->object_tables["operation_prelevement_physicochimique_microbio"]->recSetValue("ppr_id_point_de_prelevement",$myIDPointDePrelevement);

			$this->object_tables["operation_prelevement_physicochimique_microbio"]->recSetValue("id_station_de_mesure",$myCurStationDeMesureId);
				
			//echo "Objet oppc : ".Tools::Display($this->object_tables["operation_prelevement_physicochimique_microbio"]->recGetRecord());
		
			$this->object_tables["operation_prelevement_physicochimique_microbio"]->recStore();
	    	if($this->object_tables["operation_prelevement_physicochimique_microbio"]->recKeyValue()<=0)
	    		die(__LINE__." => Pas d'enregistrement");
	    	$myIDOPPC=$this->object_tables["operation_prelevement_physicochimique_microbio"]->recKeyValue();
	    	//echo "ID enregistrement pour oppc : ".$myIDOPPC.BR;
	    	if($myCurRecordNew)
	    		$this->batch_add_line("operation_prelevement_physicochimique_microbio",$myIDOPPC);
			$previousItems["operation_prelevement_physicochimique_microbio"][$myTextSearchSQL]=$myIDOPPC;
			//echo "Nouvelle opération : ".$myTextSearchSQL." : ".($myCurRecordNew?"ajouté":"repris")."<br />\n";

		}
		else
			$myIDOPPC=$previousItems["operation_prelevement_physicochimique_microbio"][$myTextSearchSQL];
    	
		/*
		 * 
		 * 
		 * Import prelevement_echantillon_physico_chimique
		 * 
		 * 
		 */

		$mySearchObj=array();
		foreach($this->curobject as $key=>$curattrib)
    	{
    		if(	$curattrib->type->type_import_colonne=="prelevement_echantillon_physico_chimique" )
    		{
    			$myCurTypeColonne=$curattrib->type->parametre_import_colonne;
    			switch($myCurTypeColonne)
    			{
	    			case "pepc_date_debut":
	    			case "pepc_heure_debut":
	    			case "pepc_reference_prelevement":
	    				$mySearchObj[$myCurTypeColonne]=$this->formatInputData($curattrib->value,$curattrib->type);
	    				break;
    			}
    		}
    	}
    	$myTextSearchSQL="";
		foreach($mySearchObj as $key=>$val)
			$myTextSearchSQL.=(($myTextSearchSQL!="")?" AND ":"").$key."='".addslashes($val)."'";
		
		if(!isset($previousItems["prelevement_echantillon_physico_chimique"][$myTextSearchSQL]))
		{
			$myCurRecordNew=false;
			$this->object_tables["prelevement_echantillon_physico_chimique"]->recSQLSearch($myTextSearchSQL);
			if(!$this->object_tables["prelevement_echantillon_physico_chimique"]->recFirst())
			{
				$myCurRecordNew=true;
				$this->object_tables["prelevement_echantillon_physico_chimique"]->recNewRecord();
			}
	
			
			foreach($this->curobject as $key=>$curattrib)
	    		if($curattrib->type->type_import_colonne=="prelevement_echantillon_physico_chimique")
	    		{
	    			if($curattrib->type->parametre_import_colonne=="pepc_int_code_intervenant")
		    			if(isset($relCodesAEIntSandre[$curattrib->value]))
		    					$curattrib->value=$relCodesAEIntSandre[$curattrib->value];
	    			
	    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["prelevement_echantillon_physico_chimique"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
	
			$this->object_tables["prelevement_echantillon_physico_chimique"]->recSetValue("oppc_id_operation_prelevement_physicochimique_microbio",$myIDOPPC);
			
			//echo "Objet pepc : ".Tools::Display($this->object_tables["prelevement_echantillon_physico_chimique"]->recGetRecord());
			
			$this->object_tables["prelevement_echantillon_physico_chimique"]->recStore();
	    	if($this->object_tables["prelevement_echantillon_physico_chimique"]->recKeyValue()<=0)
	    		die(__LINE__." => Pas d'enregistrement");
			$myIDLotParent=$this->object_tables["prelevement_echantillon_physico_chimique"]->recKeyValue();
			//echo "ID enregistrement pour pepc : ".$myIDLotParent.BR;
	    	if($myCurRecordNew)
	    		$this->batch_add_line("prelevement_echantillon_physico_chimique",$myIDLotParent);
			
			$previousItems["prelevement_echantillon_physico_chimique"][$myTextSearchSQL]=$myIDLotParent;
			//echo "Nouveau prélèvement : ".$myTextSearchSQL." : ".($myCurRecordNew?"ajouté":"repris")."<br />\n";

		}
		else
			$myIDLotParent=$previousItems["prelevement_echantillon_physico_chimique"][$myTextSearchSQL];
    	
		/*
		 * Import echantillon
		 */
		
		$mySearchObj=array();
		foreach($this->curobject as $key=>$curattrib)
    	{
    		if(	$curattrib->type->type_import_colonne=="echantillon" )
    		{
    			$myCurTypeColonne=$curattrib->type->parametre_import_colonne;
    			switch($myCurTypeColonne)
    			{
	    			case "ech_reference_echantillon":
	    				$mySearchObj[$myCurTypeColonne]=$this->formatInputData($curattrib->value,$curattrib->type);
	    				break;
    			}
    		}
    	}
    	$myTextSearchSQL="";
		foreach($mySearchObj as $key=>$val)
			$myTextSearchSQL.=(($myTextSearchSQL!="")?" AND ":"").$key."='".addslashes($val)."'";
			
		if(!isset($previousItems["echantillon"][$myTextSearchSQL]))
		{

			$this->object_tables["echantillon"]->recSQLSearch($myTextSearchSQL);
			$myCurRecordNew=false;
			if(!$this->object_tables["echantillon"]->recFirst())
			{
				$myCurRecordNew=true;			
				$this->object_tables["echantillon"]->recNewRecord();
			}
	    	
	    	foreach($this->curobject as $key=>$curattrib)
	    	{
	    		if($curattrib->type->type_import_colonne=="echantillon")
	    		{
	    			if($curattrib->type->parametre_import_colonne=="int_code_intervenant_gestionnaire")
		    			if(isset($relCodesAEIntSandre[$curattrib->value]))
		    					$curattrib->value=$relCodesAEIntSandre[$curattrib->value];
	    			
					$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["echantillon"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
	    	}
			$this->object_tables["echantillon"]->recSetValue("id_lot",$myIDLotParent);
			$this->object_tables["echantillon"]->recSetValue("ech_ns_date_saisie",date("Y-m-d H:i:s"));
			$this->object_tables["echantillon"]->recSetValue("ech_ns_lot_valide","O");
			$this->object_tables["echantillon"]->recSetValue("ech_ns_resultats_valides","O");
			$this->object_tables["echantillon"]->recSetValue("ech_ns_autorisation_diffusion","O");
			/*
	    	 * Si aucune référence n'est fournie pour l'échantillon, on ne stocke pas les paramètres
	    	 */
	    	$myIDEchantillon=-1;
			if($this->object_tables["echantillon"]->recGetValue("ech_reference_echantillon")!="")
			{
				//echo "Nouvel échantillon : ".$myTextSearchSQL."<br />\n";
				$myStoreReturn=$this->object_tables["echantillon"]->recStore();
				if(!$myStoreReturn)
					die(__LINE__." => Erreur de création de l'enregistrement : ".$this->object_tables["echantillon"]->recDBError());
				$myIDEchantillon=$this->object_tables["echantillon"]->recKeyValue();
				//echo "ID enregistrement pour echantillon : ".$myIDEchantillon.BR;
				//echo "Objet echantillon : ".Tools::Display($this->object_tables["echantillon"]->recGetRecord());
				//die($this->_db->getQuery());
				if($myCurRecordNew)
					$this->batch_add_line("echantillon",$myIDEchantillon);
			}
			if($myIDEchantillon>0)
				$previousItems["echantillon"][$myTextSearchSQL]=$myIDEchantillon;
		}
		else
			$myIDEchantillon=$previousItems["echantillon"][$myTextSearchSQL];


		if($myIDEchantillon>0)
		{
			/*
			 * 
			 * Import des résultats : analyse 
			 * 
			 */
			
			$mySearchObj=array();
			//die(Tools::Display($this->curobject));
			foreach($this->curobject as $key=>$curattrib)
	    	{
	    		if(	$curattrib->type->type_import_colonne=="analyse" )
	    		{
	    			$myCurTypeColonne=$curattrib->type->parametre_import_colonne;
	    			switch($myCurTypeColonne)
	    			{
		    			case "par_id_parametre":
		    				$mySearchObj[$myCurTypeColonne]=$this->parameters[$curattrib->value];
		    				break;
	    			}
	    		}
	    	}
	    	$mySearchObj["ech_id_echantillon"]=$myIDEchantillon;
	    	$myTextSearchSQL="";
			foreach($mySearchObj as $key=>$val)
				$myTextSearchSQL.=(($myTextSearchSQL!="")?" AND ":"").$key."='".addslashes($val)."'";
			$myIDAnalyse=-1;
			if(!isset($previousItems["analyse"][$myTextSearchSQL]))
			{
				$this->object_tables["analyse"]->recSQLSearch($myTextSearchSQL);
				$myCurRecordNew=false;
				if(!$this->object_tables["analyse"]->recFirst())
				{
					$myCurRecordNew=true;	
					$this->object_tables["analyse"]->recNewRecord();
				}
				$myCurParametreId="";
		    	foreach($this->curobject as $key=>$curattrib)
		    	{
		    		if($curattrib->type->type_import_colonne=="analyse")
		    		{
		    			switch($curattrib->type->parametre_import_colonne)
		    			{
		    				case "par_id_parametre":
		    					$myCurValue=-1;
								$myCurParametreId=$curattrib->value;
		    					if($curattrib->value>0)
		    					{
		    						if(isset($this->parameters[$curattrib->value]))
			    					{
										$myCurValue=$this->parameters[$curattrib->value];
			    					}
		    					}
								$this->object_tables["analyse"]->recSetValue($curattrib->type->parametre_import_colonne,$myCurValue);
		    					break;
		    				case "int_id_intervenant":
								if($curattrib->type->parametre_import_colonne=="int_id_intervenant")
		    						if(isset($relCodesAEIntSandre[$curattrib->value]))
		    							$curattrib->value=$relCodesAEIntSandre[$curattrib->value];
		    					$this->object_tables["analyse"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
		    					break;
		    				default:
								$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
				    			$this->object_tables["analyse"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
		    					break;
		    			}
		    		}
				}
				if($this->object_tables["analyse"]->recGetValue("par_id_parametre")>0)
				{
					//echo "Objet analyse : ".Tools::Display($this->object_tables["analyse"]->recGetRecord());
					$this->object_tables["analyse"]->recSetValue("ech_id_echantillon",$myIDEchantillon);
					$myStoreReturn=$this->object_tables["analyse"]->recStore();
					if(!$myStoreReturn)
						die(__LINE__." => Erreur de création de l'enregistrement : ".$this->object_tables["analyse"]->recDBError());
					//echo "Objet analyse : ".Tools::Display($this->object_tables["analyse"]->recGetRecord());
				
					$myIDAnalyse=$this->object_tables["analyse"]->recKeyValue();
					if($myCurRecordNew)
						$this->batch_add_line("analyse",$myIDAnalyse);
				}
				else
				{
					echo "Echantillon : ".$myIDEchantillon.", Parametre : ".$myCurParametreId." non SANDRE".BR;
				}
				if($myIDAnalyse>0)
					$previousItems["analyse"][$myTextSearchSQL]=$myIDAnalyse;
			}
			else
				$myIDAnalyse=$previousItems["analyse"][$myTextSearchSQL];

			//echo $myIDAnalyse."=>".$myTextSearchSQL.BR;			
		}


    	return $previousItems;
    	
    }
    
    function setCodeImport($theCodeImport)
    {
    	$this->code_import=$theCodeImport;
    }
    
    function chargeDescriptionImports($theCodeImport)
    {
    	$myObj=null;
	  	$database=$this->_db;
	  	$database->setQuery("SELECT * FROM a_import_description WHERE code_import_description='".$theCodeImport."';");
		if($database->loadObject($myObj))
			return $myObj;
		return false;
    }
    
    function chargeArrayToColAssociation($xls,$theObj,$theTypeImport=array("1"))
    {
    	$debug=false;
    	$txtTypeImportAInclure="";
    	foreach($theTypeImport as $curtypeimport)
    		$txtTypeImportAInclure.=(($txtTypeImportAInclure!="")?" OR ":"")." (import_inclut_colonne & ".intval($curtypeimport).")>0 ";
    	$txtTypeImportAInclure="(".$txtTypeImportAInclure.")";
		$myImportId=$theObj->id_a_import_description;
		if(is_null($myImportId) || intval($myImportId)<=0)
			die("Erreur : aucun ID d'import disponible pour l'import ".$this->code_import);
		$myRequeteColonnes="SELECT * FROM a_import_colonne,rel_import_colonne_import_description " .
							"WHERE a_import_description_id_a_import_description=".$myImportId." " .
							"AND a_import_colonne_id_a_import_colonne=id_a_import_colonne AND ".$txtTypeImportAInclure.";";
		$this->_db->setQuery($myRequeteColonnes);
		$myListCols=$this->_db->loadObjectList();
		$myPrelIndex=1;
		//echo "Colonnes définies dans cet import (".count($myListCols)."):".Tools::Display($myListCols)."<br>\n" ;
		if(is_array($myListCols) && count($myListCols)>0)
		{
			if($debug) echo __LINE__." => Démarrage de l'import effectif<br>\n";
			$row=$theObj->headerline_import_description;
			$myArrayCol2Param=array();
			if($debug) echo __LINE__." => Scan de la ligne d'entête pour replacer les colonnes<br>\n";
			if($debug) echo __LINE__." => Ligne : ".implode(",",$xls->data[0])."<br>\n";
			if($debug) echo __LINE__." => Import de la colonne ".$theObj->firstcolumn_import_description." à la colonne ".$xls->colcount()."<br>\n";
			for($col=$theObj->firstcolumn_import_description;$col<$xls->colcount();$col++)
			{
				$myClsNb=$this->rechercheParametre($xls->val($row,$col),$myImportId);
				if($debug) echo __LINE__." => Vérification colonne ".$xls->val($row,$col)." ==> ".implode(",",$myClsNb)."<br>\n";
				foreach($myListCols as $key=>$val)
					if(in_array($val->cd_import_colonne,$myClsNb))
						$myArrayCol2Param[$col][]=$val;
				
			}
			return $myArrayCol2Param;
		}
		return false;
    }
    
    function savePeche()
    {

    	$debug=false;
    	global $classprefix,$template_name,$path_abs,$ThePrefs;
    	
    	/*
    	 * Actions préliminaires : vérification de la présence de pêches
    	 * On commence donc par chercher le code échantillon DIREN qui permet de faire la relation entre une pêche et les données associées
    	 */
    	
    	if($debug) echo __LINE__." => Début import ligne de données Pêche<br>\n";
    	//if($debug) echo __LINE__." => Objet actuel : ".Tools::Display($this->curobject)."<br>\n";
    	foreach($this->curobject as $key=>$curattrib)
    	{
			//if($debug) echo __LINE__." => Vérif type : ".$curattrib->type->type_import_colonne.", param : ".$curattrib->type->parametre_import_colonne."<br />\n";
			//if($debug) echo __LINE__." => Valeur : ".$curattrib->value."<br />\n";
    		
			if($curattrib->type->type_import_colonne=="station_de_mesure" && $curattrib->type->parametre_import_colonne=="sta_code_station")
			{
				$cur_code_station=$this->formatInputData($curattrib->value,$curattrib->type);
			}
			
			if($curattrib->type->type_import_colonne=="lot_de_poissons_preleves" && $curattrib->type->parametre_import_colonne=="lpp_ns_diren_reference_echantillon")
			{
				$lpp_ns_diren_reference_echantillon=$this->formatInputData($curattrib->value,$curattrib->type);
			}
	
			if($curattrib->type->type_import_colonne=="operation_prelevement_biologique" && $curattrib->type->parametre_import_colonne=="opb_date_debut_prelevement")
			{
				$cur_date_prelevement=$this->formatInputData($curattrib->value,$curattrib->type);
			}
    	}
    	
    	/*
    	 * Correction code DIREN pour enlever les espaces
    	 */
    	
    	$lpp_ns_diren_reference_echantillon=str_replace(" ","",$lpp_ns_diren_reference_echantillon);
    	
    	//if($debug) echo __LINE__." => Informations sur la pêche : station:".$cur_code_station.", date:".$cur_date_prelevement.", ref:".$lpp_ns_diren_reference_echantillon."<br />\n";
    	if(!isset($lpp_ns_diren_reference_echantillon) || $lpp_ns_diren_reference_echantillon=="")
	    	 return "Pas  d'échantillon trouvé, référence vide".BR;	
    	
	    if(!isset($cur_code_station) || $cur_code_station=="")
	    	 return "Pas  de station trouvée, référence vide".BR;	
    	
    	$this->_db->setQuery("SELECT * FROM #__station_de_mesure WHERE sta_code_station='".addslashes($cur_code_station)."'");
    	$myCurStation=new stdClass();
    	$myStationsList=$this->_db->loadObjectList();
    	if(!is_array($myStationsList) || count($myStationsList)<=0)
    		return "Impossible de charger la station (".$cur_code_station.") / ".$this->_db->getErrorMsg()."".BR;
    	$myCurStation=$myStationsList[0];
    	/*
    	 * Si le script poursuit son exécution c'est que le code d'échange est présent, on recherche une pêche sur base de ce code
    	 */

 		$this->object_tables["lot_de_poissons_preleves"]->recSQLSearch("lpp_ns_diren_reference_echantillon='".$lpp_ns_diren_reference_echantillon."'");
		if($debug) echo __LINE__." => Recherche lot ns diren : ".$lpp_ns_diren_reference_echantillon.BR;
		if(!$this->object_tables["lot_de_poissons_preleves"]->recFirst())
		{
    	 	//return "Pas  d'échantillon trouvé pour la référence ".$lpp_ns_diren_reference_echantillon.BR;
	    	 /*
	    	  * Aucune pêche trouvée : on va maintenant l'initialiser
	    	  */
    	  	if($debug) echo __LINE__." => Création de la pêche n°".$lpp_ns_diren_reference_echantillon.BR;
    	  	
	    	/*
	    	 * Import préleveurs
	    	*/
	    	
	    	if(!isset($this->object_tables["preleveurs"]))
	    	{
	    		$myClassName=$classprefix."preleveurs";
    			$this->object_tables["preleveurs"]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    	}
	    	$this->object_tables["preleveurs"]->recNewRecord();
	    	
	    	/*
	    	$this->object_tables["preleveurs"]->recSetValue("id_preleveurs",null);
			$this->object_tables["preleveurs"]->recSetValue("prl_nom","");
			$this->object_tables["preleveurs"]->recSetValue("prl_prenom","");
			$this->object_tables["preleveurs"]->recSetValue("prl_adresse","");
			$this->object_tables["preleveurs"]->recSetValue("prl_codepostal","");
			$this->object_tables["preleveurs"]->recSetValue("prl_ville","");
			$this->object_tables["preleveurs"]->recSetValue("prl_pays","");
			$this->object_tables["preleveurs"]->recSetValue("prl_telephone","");
			$this->object_tables["preleveurs"]->recSetValue("prl_telephone_mobile","");
			$this->object_tables["preleveurs"]->recSetValue("prl_societe","");
			$this->object_tables["preleveurs"]->recSetValue("prl_email","");
	    	*/
	    	
	    	foreach($this->curobject as $key=>$curattrib)
	    	{
	    		if($curattrib->type->type_import_colonne=="preleveurs")
	    		{
	    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["preleveurs"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
	    	}
	    	if($this->object_tables["preleveurs"]->recGetValue("prl_nom")=="")
	    		$this->object_tables["preleveurs"]->recSetValue("prl_nom","(non renseigné)"); //""$cur_date_prelevement."@".$cur_code_station);
	    	$this->object_tables["preleveurs"]->recStore();
	    	
	    	$myIDPreleveur=$this->object_tables["preleveurs"]->recKeyValue();
	    	if($myIDPreleveur<=0)
	    		die(__LINE__." => Pas d'enregistrement créé pour le préleveur");
	    	
	    	$myBatchError=$this->batch_add_line("preleveurs",$myIDPreleveur);
	    	if($debug) echo __LINE__." => Création preleveurs ".$myIDPreleveur." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
		    
		    if(!isset($this->object_tables["operation_prelevement_biologique"]))
	    	{
	    		$myClassName=$classprefix."operation_prelevement_biologique";
    			$this->object_tables["operation_prelevement_biologique"]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    	}
	    	
		
	    	/*
	    	 * Import Point de prelevement
	    	 */
	    	
	    	if(!isset($this->object_tables["point_de_prelevement"]))
	    	{
	    		$myClassName=$classprefix."point_de_prelevement";
    			$this->object_tables["point_de_prelevement"]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    	}
	    	
	    	$this->object_tables["point_de_prelevement"]->recNewRecord();
	    	
	    	/*
	    	$this->object_tables["point_de_prelevement"]->recSetValue("id_point_de_prelevement",null);
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_code","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_objet_principal","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_recommandations_sur_lieu","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_date_mise_en_service","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_date_mise_hors_service","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_nom_station","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_profondeur_recommandee","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_commentaires","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_mode_obtention_coordonnees","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x_amont","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y_amont","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x_aval","");
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y_aval","");
			$this->object_tables["point_de_prelevement"]->recSetValue("station_de_mesure_id_station_de_mesure","");
			*/
	    	
	    	foreach($this->curobject as $key=>$curattrib)
	    	{
	    		if($curattrib->type->type_import_colonne=="point_de_prelevement")
	    		{
	    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["point_de_prelevement"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value); 			
	    		}
	    	}
			$this->object_tables["point_de_prelevement"]->recSetValue("sta_id_station_de_mesure",$myCurStation->id_station_de_mesure );
	    	/*
	    	$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x",$myCurStation->sta_coord_x);
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y",$myCurStation->sta_coord_y);
	    	$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_x_amont",$this->object_tables["point_de_prelevement"]->recGetValue("ppr_coord_x"));
			$this->object_tables["point_de_prelevement"]->recSetValue("ppr_coord_y_amont",$this->object_tables["point_de_prelevement"]->recGetValue("ppr_coord_y"));
	    	*/
	    	if($this->object_tables["point_de_prelevement"]->recGetValue("ppr_objet_principal")=="")
	    		$this->object_tables["point_de_prelevement"]->recSetValue("ppr_objet_principal","-");
			$this->object_tables["point_de_prelevement"]->recStore();
			$myIDPointDePrelevement=$this->object_tables["point_de_prelevement"]->recKeyValue();
	    	if($myIDPointDePrelevement<=0)
	    		die(__LINE__." => Pas d'enregistrement pour le point de prélèvement");
			
			$myBatchError=$this->batch_add_line("point_de_prelevement",$myIDPointDePrelevement);
			if($debug) echo __LINE__." => Création point_de_prelevement ".$myIDPointDePrelevement." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
	    	
	    	
	    	
			/*
			 * Import operation_prelevement_biologique
			 */
			
			$this->object_tables["operation_prelevement_biologique"]->recNewRecord();
			
	    	/*
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("id_operation_prelevement_biologique",null);
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_date_debut_prelevement","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_heure_debut_prelevement","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_date_fin_prelevement","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_heure_fin_prelevement","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_code_intervenant_producteur","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_code_intervenant_preleveur","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("ppr_id_point_de_prelevement",$myIDPointDePrelevement);
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_longueur_site_prospectee","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_largeur_moyenne_lame_eau","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_interpretation_resultats","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_qualification_resultats","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_statut_resultats","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_commentaires","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_mode_conservation_principal_echantillons","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_mode_conservation_secondaire_echantillons","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_situation_particuliere","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_superficie_mouillee_totale","");
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_objectif_opb","");
			*/
			
			foreach($this->curobject as $key=>$curattrib)
	    		if($curattrib->type->type_import_colonne=="operation_prelevement_biologique")
	    		{
	    			//echo "Attribut opb : ".Tools::Display($curattrib);
	    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			//echo "Valeur : ".$curattrib->value."<br />\n";
	    			$this->object_tables["operation_prelevement_biologique"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("opb_date_fin_prelevement",$this->object_tables["operation_prelevement_biologique"]->recGetValue("opb_date_debut_prelevement"));
			$this->object_tables["operation_prelevement_biologique"]->recSetValue("ppr_id_point_de_prelevement",$myIDPointDePrelevement);
			$this->object_tables["operation_prelevement_biologique"]->recStore();
			$myIDOperation=$this->object_tables["operation_prelevement_biologique"]->recKeyValue();
	    	if($myIDOperation<=0)
	    		die(__LINE__." => Pas d'enregistrement");
	    	
	    	$myBatchError=$this->batch_add_line("operation_prelevement_biologique",$myIDOperation);
	    	if($debug) echo __LINE__." => Création operation_prelevement_biologique ".$myIDOperation." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
	    	
			/*
			 * Import prelevement_elementaire_biologique
			 */
			
			if(!isset($this->object_tables["prelevement_elementaire_biologique"]))
	    	{
	    		$myClassName=$classprefix."prelevement_elementaire_biologique";
    			$this->object_tables["prelevement_elementaire_biologique"]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    	}
	    	
    	
			$this->object_tables["prelevement_elementaire_biologique"]->recNewRecord();
			
	    	/*
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("id_prelevement_elementaire_biologique",null);
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("opb_id_operation_prelevement_biologique",$this->object_tables["operation_prelevement_biologique"]->recKeyValue());
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("prl_id_preleveurs",$this->object_tables["preleveurs"]->recKeyValue());
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_code_prelevement_elementaire","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_materiel_utilise","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_vegetation_sur_prelevement","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_type_colmatage_placette","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_largeur_prospectee","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_longueur_propsectee","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_hauteur_eau_moyenne","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_surface_prospectee","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_distance_berge","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_volume_eau","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_commentaires","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_intensite_colmatage","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_type_diatomees_prelevees","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_facies_morpho_secondaire","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_groupe_prelevement_bio","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_zone_verticale_prospectee","");
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("peb_profondeur_prelevement","");
			*/
			
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("opb_id_operation_prelevement_biologique",$myIDOperation);
			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue("prl_id_preleveurs",$this->object_tables["preleveurs"]->recKeyValue());
			
			foreach($this->curobject as $key=>$curattrib)
	    		if($curattrib->type->type_import_colonne=="prelevement_elementaire_biologique")
	    		{
	    			$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["prelevement_elementaire_biologique"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
	
			$this->object_tables["prelevement_elementaire_biologique"]->recStore();
			$myIDPrelevement=$this->object_tables["prelevement_elementaire_biologique"]->recKeyValue();
	    	if($myIDPrelevement<=0)
	    		die(__LINE__." => Pas d'enregistrement");
			$myBatchError=$this->batch_add_line("prelevement_elementaire_biologique",$myIDPrelevement);
			if($debug) echo __LINE__." => Création prelevement_elementaire_biologique ".$myIDPrelevement." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
			
	    	/*
	    	 * Import zones_de_peche
	    	 */


			if(!isset($this->object_tables["zones_de_peche"]))
	    	{
	    		$myClassName=$classprefix."zones_de_peche";
    			$this->object_tables["zones_de_peche"]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    	}
    	
	    	
	    	$this->object_tables["zones_de_peche"]->recNewRecord();
			
	    	/*
			$this->object_tables["zones_de_peche"]->recSetValue("id_zones_de_peche",null);
			$this->object_tables["zones_de_peche"]->recSetValue("zdp_date_creation","");
			$this->object_tables["zones_de_peche"]->recSetValue("zdp_code_zone","");
			$this->object_tables["zones_de_peche"]->recSetValue("zdp_libelle_zone","");
			$this->object_tables["zones_de_peche"]->recSetValue("zdp_coord_x","");
			$this->object_tables["zones_de_peche"]->recSetValue("zdp_coord_y","");
			$this->object_tables["zones_de_peche"]->recSetValue("peb_id_prelevement_elementaire_biologique","");
			$this->object_tables["zones_de_peche"]->recSetValue("zdp_commentaires","");
			$this->object_tables["zones_de_peche"]->recSetValue("zdp_mat_code_materiel","");
			*/
	    	
	    	foreach($this->curobject as $key=>$curattrib)
	    		if($curattrib->type->type_import_colonne=="zones_de_peche")
	    		{
					$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
	    			$this->object_tables["zones_de_peche"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
	    		}
	
			if($this->object_tables["zones_de_peche"]->recGetValue("zdp_libelle_zone")=="")
				$this->object_tables["zones_de_peche"]->recSetValue("zdp_libelle_zone",$myCurStation->sta_libelle_national);
			if($this->object_tables["zones_de_peche"]->recGetValue("zdp_code_zone")=="")
				$this->object_tables["zones_de_peche"]->recSetValue("zdp_code_zone",$myCurStation->sta_code_station);
			
			$this->object_tables["zones_de_peche"]->recSetValue("peb_id_prelevement_elementaire_biologique",$myIDPrelevement);
			$this->object_tables["zones_de_peche"]->recStore();
			$myIDZone=$this->object_tables["zones_de_peche"]->recKeyValue();
	    	if($myIDZone<=0)
	    		die(__LINE__." => Pas d'enregistrement pour la zone");
	    		
			$myBatchError=$this->batch_add_line("zones_de_peche",$myIDZone);
			if($debug) echo __LINE__." => Création zones_de_peche ".$myIDZone." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
			
	    	//echo "Objet actuel : ".Tools::Display($this->curobject);
	    	//$this->object_tables["point_de_prelevement"]->recStore();
	    	//echo "Enregistrement préleveurs :".Tools::Display($this->object_tables["preleveurs"]->recGetRecord());
	    	//echo "Enregistrement point_de_prelevement :".Tools::Display($this->object_tables["point_de_prelevement"]->recGetRecord());
	   		
	   		/*
	   		 * Lot de poissons prélevés : parent et enfant(s) + relation
	   		 */
	   		/*
	   		 * Champs pris en compte dans le fichier
				lot_de_poissons_preleves	lpp_code_lot
				lot_de_poissons_preleves	lpp_ns_diren_reference_echantillon
				lot_de_poissons_preleves	tax_code_taxon
				lot_de_poissons_preleves	lpp_effectif_lot
				lot_de_poissons_preleves	lpp_age_nombre_hivers
				lot_de_poissons_preleves	lpp_mode_determination_age
				lot_de_poissons_preleves	lpp_poids
				lot_de_poissons_preleves	lpp_taille_lot
				lot_de_poissons_preleves	lpp_sexe
	   		 */
	   		 
	   		/*
	   		 * Initialisation de la table au besoin
	   		 */
	   		 
	   		if(!isset($this->object_tables["lot_de_poissons_preleves"]))
	    	{
	    		$myClassName=$classprefix."lot_de_poissons_preleves";
    			$this->object_tables["lot_de_poissons_preleves"]=new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    	}
	    	
	    	/*
	    	 * Recherche d'un éventuel lot identique déjà en base
	    	 */
	   		 
	   		$this->object_tables["lot_de_poissons_preleves"]->recSQLSearch("lpp_ns_diren_reference_echantillon='".$lpp_ns_diren_reference_echantillon."'");
			if($debug) echo __LINE__." => Recherche lot ns diren : ".$lpp_ns_diren_reference_echantillon.BR;
			if(!$this->object_tables["lot_de_poissons_preleves"]->recFirst())
			{
		    	/*
		    	 * Pas de pêche trouvée, on l'enregistre
		    	 */
		    
		   		 
				$this->object_tables["lot_de_poissons_preleves"]->recNewRecord();
		    	/*
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_lot","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_type_lot","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_effectif_lot","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_minimale","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_maximale","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_type_longueur","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_poids","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_sexe","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_maturite_poisson","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_prelevement_ecailles","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_age","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_resultat_liste_faunistique","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_degre_confiance_determination_taxon","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("peb_id_prelevement_elementaire_biologique","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("sup_code_support","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("tax_code_taxon","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_date_lyophilisation","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_diren_reference_echantillon","");
				*/
				/*
				//echo "Valeur des champs de la table : <br />\n";
				//$myObjRef->lpp_code_lot = "P"; //$this->fieldValue("lot_de_poissons_preleves","lpp_code_lot");
				$myObjRef->lpp_ns_diren_reference_echantillon = $this->fieldValue("lot_de_poissons_preleves","lpp_ns_diren_reference_echantillon");
				$myObjRef->tax_code_taxon = $this->fieldValue("lot_de_poissons_preleves","tax_code_taxon");
				$myObjRef->lpp_effectif_lot = $this->fieldValue("lot_de_poissons_preleves","lpp_effectif_lot");
				$myObjRef->lpp_poids = $this->fieldValue("lot_de_poissons_preleves","lpp_poids");
				$myObjRef->lpp_mode_determination_poids = $this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_poids");
				$myObjRef->lpp_taille_lot = $this->fieldValue("lot_de_poissons_preleves","lpp_taille_lot");
				$myObjRef->lpp_type_longueur = $this->fieldValue("lot_de_poissons_preleves","lpp_type_longueur");
				$myObjRef->lpp_mode_determination_age = $this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_age");
				$myObjRef->lpp_mode_determination_sexe = $this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_sexe");
				$myObjRef->lpp_date_lyophilisation = formatInputData($this->fieldValue("lot_de_poissons_preleves","lpp_date_lyophilisation"),"jj/mm/aa");
				$myObjRef->lpp_ns_matiereseche_disponible = $this->fieldValue("lot_de_poissons_preleves","lpp_ns_matiereseche_disponible");
				$myObjRef->lpp_age_nombre_hivers = $this->fieldValue("lot_de_poissons_preleves","lpp_age_nombre_hivers");
				$myObjRef->lpp_sexe = $this->fieldValue("lot_de_poissons_preleves","lpp_sexe");
	
				$myObjRef->lpp_type_lot="";
				$myObjRef->lpp_taille_minimale = "";
				$myObjRef->lpp_taille_maximale = "";
				$myObjRef->lpp_prelevement_ecailles = "";
				*/
				
				/*
				 * Lot vaut plus que 3 OU un nombre indéterminé (-1) : création d'un lot parent seul
				 * Sinon, création d'un lot parent avec données éventuellement plus restreintes
				 */
				 
				/*
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_effectif_lot",$myObjRef->lpp_effectif_lot);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot",$myObjRef->lpp_taille_lot);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids",$myObjRef->lpp_poids);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe",$myObjRef->lpp_sexe);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers",$myObjRef->lpp_age_nombre_hivers);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_mode_determination_age",$myObjRef->lpp_mode_determination_age);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("tax_code_taxon",$myObjRef->tax_code_taxon);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_diren_reference_echantillon",$myObjRef->lpp_ns_diren_reference_echantillon);
				*/
				
			  	foreach($this->curobject as $key=>$curattrib)
		    		if($curattrib->type->type_import_colonne=="lot_de_poissons_preleves")
		    		{
						$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
		    			$this->object_tables["lot_de_poissons_preleves"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
		    		}
				
				
				if(intval($this->object_tables["lot_de_poissons_preleves"]->recGetValue("lpp_effectif_lot")==0))
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_effectif_lot",-1);
				
				$curlot_lpp_effectif_lot=$this->object_tables["lot_de_poissons_preleves"]->recGetValue("lpp_effectif_lot");
				
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_type_lot","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_minimale","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_maximale","");
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_prelevement_ecailles","");
				$lpp_ns_diren_reference_echantillon=str_replace(" ","",$lpp_ns_diren_reference_echantillon);
				echo "Enregistrement dans le lot parent du code diren n°".$lpp_ns_diren_reference_echantillon.BR;
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_diren_reference_echantillon",$lpp_ns_diren_reference_echantillon);
				
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_lot","P"); //$myObjRef->lpp_code_lot);
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("peb_id_prelevement_elementaire_biologique",$myIDPrelevement);
				//$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_date_lyophilisation",$this->formatInputData($this->fieldValue("lot_de_poissons_preleves","lpp_date_lyophilisation"),"jj/mm/aa")); 
				
				$this->object_tables["lot_de_poissons_preleves"]->recStore();
				$myIDLotParent=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
				$myBatchError=$this->batch_add_line("lot_de_poissons_preleves",$myIDLotParent);
				if($debug) echo __LINE__." => Création lot_de_poissons_preleves parent ".$myIDLotParent." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
		    	
				/*
				 * Création d'un code lot spécifique au lot parent avec l'id interne de la base
				 */
				$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_lot","P".$this->object_tables["lot_de_poissons_preleves"]->recKeyValue());
				$this->object_tables["lot_de_poissons_preleves"]->recStore();
				
				$myTableIndexPoissons=array(1=>"a",2=>"b",3=>"c");
				
				/*
				 * Cas spécifique d'un lot avec un seul enfant, on récupère dans le parent les valeurs de l'enfant
				 */
				if($curlot_lpp_effectif_lot==1)
				{
					/*
					lpp_sexe => Sexe ?
					lpp_poids => Poids ? (g)
					lpp_taille_lot => Longueur totale ? (mm)
					lpp_age_nombre_hivers => Age ? mini
					lpp_ns_age_incertitude => incertitude Age ? mini
					*/
					$curp=$myTableIndexPoissons[1];
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe",
						$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_sexe.".$curp));
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids",
						$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_poids.".$curp));
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot",
						$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_taille_lot.".$curp));
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers",
						$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_age_nombre_hivers.".$curp));
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_age_incertitude",
						$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_ns_age_incertitude.".$curp));
					$this->object_tables["lot_de_poissons_preleves"]->recStore();
				}
				
				$myRelLotClass=$classprefix."rel_lot_poissons_preleves";
				$myRelLot=new $myRelLotClass($this->_db,$template_name,basename(__FILE__),$path_abs,true);
				$myRelLot->recSetValue("id_rel_lot_poissons_preleves",null);
				$myRelLot->recSetValue("lpp_id_parent",$myIDLotParent);
				$myRelLot->recSetValue("lpp_id_enfant","");
				
				if($curlot_lpp_effectif_lot>0 && $curlot_lpp_effectif_lot<=3)
				{
					/*
					 * Lot vaut 1 à 3 : création d'un lot enfant individuel pour chaque, même si vide
					 * Création du lot parent en premier
					 */
					$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_code_lot","");
					if($curlot_lpp_effectif_lot==1)
					{
						/*
						 * Lot vaut 1 uniquement : inscription des détails des poissons dans le lot enfant
						 */
						$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
						$this->object_tables["lot_de_poissons_preleves"]->recStore();
						$myIDLotEnfant=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
						$myBatchError=$this->batch_add_line("lot_de_poissons_preleves",$myIDLotEnfant);
						if($debug) echo __LINE__." => Création lot_de_poissons_preleves enfant ".$myIDLotEnfant." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
		    	
						$myRelLot->recSetValue("id_rel_lot_poissons_preleves",null);
						$myRelLot->recSetValue("lpp_id_enfant",$myIDLotEnfant);
						$myRelLot->recStore();
					}		
					else
					{
						$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_effectif_lot","");
						$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe","");
						$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids","");
						$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot","");
						$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers","");
						$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_age_incertitude","");
		
						for($i=1;$i<=$curlot_lpp_effectif_lot;$i++)
						{
							$this->object_tables["lot_de_poissons_preleves"]->recSetValue("id_lot_de_poissons_preleves",null);
							$curp=$myTableIndexPoissons[$i];
							$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_sexe",
								$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_sexe.".$curp));
							$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_poids",
								$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_poids.".$curp));
							$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_taille_lot",
								$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_taille_lot.".$curp));
							$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_age_nombre_hivers",
								$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_age_nombre_hivers.".$curp));
							$this->object_tables["lot_de_poissons_preleves"]->recSetValue("lpp_ns_age_incertitude",
								$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_ns_age_incertitude.".$curp));
							$this->object_tables["lot_de_poissons_preleves"]->recStore();
							$myIDLotEnfant=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
							$myBatchError=$this->batch_add_line("lot_de_poissons_preleves",$myIDLotEnfant);
							if($debug) echo __LINE__." => Création lot_de_poissons_preleves enfant ".$myIDLotEnfant." (batch : ".(($myBatchError===false)?("erreur".Tools::Display($this->import_batch)):$myBatchError).")".BR;
		    				$myRelLot->recSetValue("id_rel_lot_poissons_preleves",null);
							$myRelLot->recSetValue("lpp_id_enfant",$myIDLotEnfant);
							$myRelLot->recStore();
						}
					}
				}	   
			} 	  
		}
	    else
	    {
	    	/*
	    	 * Une pêche a été trouvée, pour le moment on ne fait rien, on suppose qu'elle est complète et on poursuit l'exécution
	    	 */
	    	 if($debug) echo __LINE__." => La pêche n°".$lpp_ns_diren_reference_echantillon." existe déjà, on l'utilise et on continue le traitement".BR;
	    	 return "La pêche n°".$lpp_ns_diren_reference_echantillon." existe déjà, on l'utilise et on continue le traitement".BR;
	    }
    	return true;

   	
    }
    
    function saveLineRendu($theLexiques,$theCodeIntervenant,$theDateReception)
    {
    	$debug=false;
    	global $classprefix,$template_name,$path_abs,$ThePrefs;
    	
    	/*
    	 * Actions préliminaires : vérification de la présence de pêches
    	 * On commence donc par chercher le code échantillon DIREN qui permet de faire la relation entre une pêche et les données associées
    	 */
    	
    	if($debug) echo __LINE__." => Début import ligne de données Rendu<br>\n";
    	foreach($this->curobject as $key=>$curattrib)
    	{
			if($debug) echo __LINE__." => Vérif type : ".$curattrib->type->type_import_colonne.", param : ".$curattrib->type->parametre_import_colonne."<br />\n";
			if($debug) echo __LINE__." => Valeur : ".$curattrib->value."<br />\n";
    		
			if($curattrib->type->type_import_colonne=="lot_de_poissons_preleves" && $curattrib->type->parametre_import_colonne=="lpp_ns_diren_reference_echantillon")
			{
				$lpp_ns_diren_reference_echantillon=$this->formatInputData($curattrib->value,$curattrib->type);
			}
    	}
    	
    	
    	if($debug) echo __LINE__." => Echantillon trouvé : ".$lpp_ns_diren_reference_echantillon."<br />\n";
	    if($lpp_ns_diren_reference_echantillon=="")
	    	 return "Pas  d'échantillon trouvé, référence vide".BR;	
    	
    	/*
    	 * Correction code DIREN pour enlever les espaces
    	 */
    	
    	$lpp_ns_diren_reference_echantillon=str_replace(" ","",$lpp_ns_diren_reference_echantillon);
    	
    	
    	/*
    	 * Si le script poursuit son exécution c'est que le code d'échange est présent, on recherche une pêche sur base de ce code
    	 */

 		$this->object_tables["lot_de_poissons_preleves"]->recSQLSearch("lpp_ns_diren_reference_echantillon='".$lpp_ns_diren_reference_echantillon."'");
		if($debug) echo __LINE__." => Recherche lot ns diren : ".$lpp_ns_diren_reference_echantillon.BR;
		if(!$this->object_tables["lot_de_poissons_preleves"]->recFirst())
		{
	    	 return "Pas  d'échantillon trouvé pour la référence ".$lpp_ns_diren_reference_echantillon.BR;
		}
	    else
	    {
	    	/*
	    	 * Une pêche a été trouvée, pour le moment on ne fait rien, on suppose qu'elle est complète et on poursuit l'exécution
	    	 */
	    }
	    	 
	    	 
	    	 	
    	/*
    	 * Premiere partie, on initie le prélèvement en soi
    	 */
    	
    	/*
    	 * 
    	 * Ordre des opérations, en résumé :
    	 * - MAJ lot_poisson_preleve => enfant
    	 * - MAJ lot_poisson_preleve => parent
    	 * - MAJ echantillon
    	 * - MAJ analyse
    	 * 
    	 * En pratique :
    	 * - Retrouver le lot basé sur le n° de référence DIREN (lpp_ns_diren_reference_echantillon) (fait en préliminaire, juste au dessus)
    	 * - Mettre à jour la description des lots : age, poids, taille, sexe, pour les individus 1 à 3 (si taille 1<=taille<=3)
    	 * - Retrouver ou créer le ou les échantillons associés au lot parent retrouvé
    	 * - Retrouver parmi échantillons celui qui est associé au numéro de référence du labo
    	 *   - Si aucun, le créer, prendre la référence
    	 *   - Si trouvé, prendre la référence
    	 * - Avec la référence échantillon pour chaque valeur du fichier de données transmis par le labo
    	 * 	 - Rechercher les paramètres existants
    	 *   - Les mettre à jour
    	 *   - Si pas existant, les créer
    	 * 
    	 */
    	 
		$myParentLotId=-1;
    	if($debug) echo __LINE__." => Lots trouvés avec cette référence : ".$this->object_tables["lot_de_poissons_preleves"]->recCount()."<br />\n";
	    $myRelLotClass=$classprefix."rel_lot_poissons_preleves";
		$myRelLotEnfantLotParent=new $myRelLotClass($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    do
	    {
		    $myLotId=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
		    $myRelLotEnfantLotParent->recSQLSearch("lpp_id_parent=".$myLotId."");
		    if($myRelLotEnfantLotParent->recCount()>0)
		    {
				$myParentLotId=$myLotId; 	
		    	break;
		    }
		    /*
		     * On n'a pas trouvé de lot parent en direct, on cherche la présence d'un lot enfant portant ce code
		     */
		    $myRelLotEnfantLotParent->recSQLSearch("lpp_id_enfant=".$myLotId."");
		    if($myRelLotEnfantLotParent->recCount()<=0)
		    {
		    	/*
		    	 * On n'en trouve pas : cela signifie qu'il s'agit d'un lot qui ne possède pas de lot enfant car le nombre de poissons n'a pas permis de créer des lots enfants : nombre >3 ou inconnu
		    	 */
				$myParentLotId=$myLotId; 	
		    	break;
		    }
		    
	    } while($this->object_tables["lot_de_poissons_preleves"]->recNext());
	    if($debug) echo __LINE__." => Code lot parent trouvé : ".$myParentLotId."<br />\n";
	    if($myParentLotId<=0)
	    	return "Lot parent non trouvé".BR;

		if($debug) echo __LINE__." => Chargement du lot<br />\n";
	    $myObjRef=$this->object_tables["lot_de_poissons_preleves"]->recGetRecord();
	    if($debug) echo __LINE__." => Lot parent AVANT mise à jour : ".Tools::Display($myObjRef)."<br />\n";
		$myCurLotCodeSupport=$myObjRef->sup_code_support;
		if($myCurLotCodeSupport=="")
		{
			$myCurLotCodeSupport=4;
			$myObjRef->sup_code_support=$myCurLotCodeSupport;
		}
		foreach($this->curobject as $key=>$curattrib)
    	{
    		//echo "Intégration de ".$curattrib->value.", Type : ".Tools::Display($curattrib->type,true)."<br />\n";
    		if($curattrib->type->type_import_colonne=="lot_de_poissons_preleves")
    		{
    			if($curattrib->type->parametre_import_colonne=="")
				$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
				$curKey=$curattrib->type->parametre_import_colonne;
				if(isset($myObjRef->$curKey)) // On ne veut pas créer de nouvelles colonnes ! Sinon l'objet ne se sauvegardera pas
    				$myObjRef->$curKey=$curattrib->value;
    		}
    	}
		
		$myObjRef->lpp_ns_diren_reference_echantillon = $this->fieldValue("lot_de_poissons_preleves","lpp_ns_diren_reference_echantillon");
		$myObjRef->lpp_ns_diren_reference_echantillon=str_replace(" ","",$myObjRef->lpp_ns_diren_reference_echantillon);
		
		$myObjRef->lpp_effectif_lot =	$this->fieldValue("lot_de_poissons_preleves","lpp_effectif_lot");
		$myObjRef->lpp_poids =	$this->fieldValue("lot_de_poissons_preleves","lpp_poids");
		//$myObjRef->lpp_taille_lot =	$this->fieldValue("lot_de_poissons_preleves","lpp_taille_lot");
		
		$myObjRef->lpp_mode_determination_poids =	$this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_poids");
		$myObjRef->lpp_mode_determination_age =	$this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_age");
		$myObjRef->lpp_mode_determination_sexe =	$this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_sexe");
		
		$myObjRef->lpp_type_longueur =	$this->fieldValue("lot_de_poissons_preleves","lpp_type_longueur");
		$myObjRef->lpp_ns_commentaire_asconit=$this->fieldValue("lot_de_poissons_preleves","lpp_ns_commentaire_asconit");
		if($debug) echo __LINE__." => Lot parent mis à jour : ".Tools::Display($myObjRef)."<br />\n";
		
		$this->object_tables["lot_de_poissons_preleves"]->recStore($myObjRef);
		/*
		 * Stockage date de lyophilisation pour utilisation dans l'analyse => date d'analyse
		 */
		$myDateLyophilisationLot=$this->object_tables["lot_de_poissons_preleves"]->recGetValue("lpp_date_lyophilisation");
		
		$myIDLotParent=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
		if($debug) echo __LINE__." => Confirmation ID Lot après sauvegarde : ".$myIDLotParent."<br />\n";
		if($debug) echo __LINE__." => Effectif lot entre 1 et 3 ? Vérification : ".$myObjRef->lpp_effectif_lot."<br />\n";
		if($myObjRef->lpp_effectif_lot>0 && $myObjRef->lpp_effectif_lot<4)
		{
			if($debug) echo __LINE__." => Nombre total de lots, dont le lot parent : ".$this->object_tables["lot_de_poissons_preleves"]->recCount()."<br />\n";
			$this->object_tables["lot_de_poissons_preleves"]->recFirst();
			$myLetterIndex=array(1=>"a",2=>"b",3=>"c");
			$myArrayCleanEmptyFieldsList=array("lpp_code_lot","lpp_mode_determination_sexe","lpp_mode_determination_age","lpp_mode_determination_poids","lpp_poids","lpp_taille_lot","lpp_age_nombre_hivers","lpp_ns_age_incertitude","lpp_sexe");
			$indexLot=1;
			do
		    {
			    $myLotId=$this->object_tables["lot_de_poissons_preleves"]->recKeyValue();
			    if($myLotId!=$myIDLotParent)
			    {
			    	$myObjRefEnfant=$this->object_tables["lot_de_poissons_preleves"]->recGetRecord();
			    	$myObjRefEnfant->lpp_code_lot=$myObjRef->lpp_code_lot."-".$myLetterIndex[$indexLot];
			    	if($debug) echo __LINE__." => Lot enfant AVANT mise à jour : ".Tools::Display($myObjRefEnfant)."<br />\n";
					$myObjRefEnfant->lpp_mode_determination_sexe=$this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_sexe");
					$myObjRefEnfant->lpp_mode_determination_age=$this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_age");
					$myObjRefEnfant->lpp_mode_determination_poids=$this->fieldValue("lot_de_poissons_preleves","lpp_mode_determination_poids");
					
					$myObjRefEnfant->lpp_poids=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_poids.".$myLetterIndex[$indexLot]);
					$myObjRefEnfant->lpp_taille_lot=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_taille_lot.".$myLetterIndex[$indexLot]);
					$myObjRefEnfant->lpp_age_nombre_hivers=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_age_nombre_hivers.".$myLetterIndex[$indexLot]);
					$myObjRefEnfant->lpp_ns_age_incertitude=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_ns_age_incertitude.".$myLetterIndex[$indexLot]);
					$myObjRefEnfant->lpp_sexe=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_sexe.".$myLetterIndex[$indexLot]);
					if($myObjRef->lpp_effectif_lot==1)
					{
						$myObjRef->lpp_age_nombre_hivers=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_age_nombre_hivers.".$myLetterIndex[$indexLot]);
						$myObjRef->lpp_ns_age_incertitude=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_ns_age_incertitude.".$myLetterIndex[$indexLot]);
						$myObjRef->lpp_sexe=$this->fieldValue("lot_de_poissons_preleves.enfant","lpp_sexe.".$myLetterIndex[$indexLot]);
						$this->object_tables["lot_de_poissons_preleves"]->recStore($myObjRef);
					}
					$debug=true;
					if($debug) echo __LINE__." => Mise à jour du lot enfant index ".$indexLot."/".$myObjRefEnfant->lpp_effectif_lot." avec l'id : ".$myLotId."<br />\n";
					if($debug) echo __LINE__." => Lot enfant mis à jour : ".Tools::Display($myObjRefEnfant)."<br />\n";
					
					foreach($myArrayCleanEmptyFieldsList as $keyfield=>$fieldname)
						if(isset($myObjRefEnfant->$fieldname) && trim($myObjRefEnfant->$fieldname)=="")
							unset($myObjRefEnfant->$fieldname);
					$myReturn=$this->object_tables["lot_de_poissons_preleves"]->recStore($myObjRefEnfant);
					if($debug) echo __LINE__." => Enregistrement lot enfant : ".(($myReturn)?"OK":"Erreur").BR;
					if($debug) echo __LINE__." => Requete Enregistrement lot enfant : ".$this->_db->getQuery().BR;
					$debug=false;
				    $indexLot++;
			    }
		    } while($this->object_tables["lot_de_poissons_preleves"]->recNext());
		}
		
		/*
		 * Import echantillon
		 */
		
		$myRefEchantillon=$this->fieldValue("echantillon","ech_reference_echantillon");
		if($debug) echo __LINE__." => Recherche echantillon avec critère : sup_code_support=".$myCurLotCodeSupport." AND id_lot=".$myIDLotParent." AND ech_reference_echantillon='".$myRefEchantillon."'<br />\n";
		if($myRefEchantillon=="")
			return "Echantillon non trouvé pour les critères support=".$myCurLotCodeSupport." , id_lot=".$myIDLotParent." et ech_reference_echantillon=".$myRefEchantillon.BR;
		$this->object_tables["echantillon"]->recSQLSearch("ech_reference_echantillon='".$myRefEchantillon."'");
		if($debug) echo  __LINE__." => Requête de recherche : ".$this->_db->getQuery();
		$myCurRecordNew=false;
		if(!$this->object_tables["echantillon"]->recFirst())
		{
			$myCurRecordNew=true;
			if($debug) echo __LINE__." => Echantillon NON trouvé, on l'initialise<br />\n";
			//if($debug) echo __LINE__." => Requête précédente : ".$this->object_tables["echantillon"]->_db->getQuery()."<br />\n";
			
			$this->object_tables["echantillon"]->recNewRecord();
			$this->object_tables["echantillon"]->recSetValue("sup_code_support",$myCurLotCodeSupport);
			$this->object_tables["echantillon"]->recSetValue("ech_reference_echantillon",$myRefEchantillon);
	    	$this->object_tables["echantillon"]->recSetValue("ech_ns_lot_valide","O");
	    	$this->object_tables["echantillon"]->recSetValue("ech_ns_resultats_valides","O");
	    	$this->object_tables["echantillon"]->recSetValue("ech_ns_autorisation_diffusion","N");
	    	$this->object_tables["echantillon"]->recSetValue("ech_ns_lot_a_completer","N");

		}
		else
			if($debug) echo __LINE__." => Echantillon trouvé, il est chargé (nb trouvés : ".$this->object_tables["echantillon"]->recCount().")<br />\n";

    	
    	if($debug) echo __LINE__." => Boucle de copie des valeurs de l'échantillon<br />\n";
    	
    	// Définition de la liste des attributs qu'on ne veut pas mettre à jour après la création d'un enregistrement
    	$myArrayNoUpdate["echantillon"]=array("ech_ns_lot_valide","ech_ns_resultats_valides","ech_ns_autorisation_diffusion","ech_ns_lot_a_completer");
    	
    	foreach($this->curobject as $key=>$curattrib)
    	{
    		//echo "Intégration de ".$curattrib->value.", Type : ".Tools::Display($curattrib->type,true)."<br />\n";
    		if($curattrib->type->type_import_colonne=="echantillon")
    		{
				$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
				// On vérifie l'attribut en cours vs les attributs qu'on ne veut pas mettre à jour
				if(!in_array($curattrib->type->parametre_import_colonne,$myArrayNoUpdate["echantillon"]))
    				$this->object_tables["echantillon"]->recSetValue($curattrib->type->parametre_import_colonne,$curattrib->value);
    		}
    	}
		$this->object_tables["echantillon"]->recSetValue("id_lot",$myIDLotParent);
		$this->object_tables["echantillon"]->recSetValue("ech_date_reception",Tools::DateUserToSQL($ThePrefs->DatePrefs,$theDateReception));
    	$this->object_tables["echantillon"]->recSetValue("ech_heure_reception","00:00:00");
    	
    	/*
    	 * Correction : date saisie = date de validation des lots
    	 * Cette date est à saisir au moment de la publication !
    	 */
    	//$this->object_tables["echantillon"]->recSetValue("ech_ns_date_saisie",Tools::DateUserToSQL($ThePrefs->DatePrefs,$theDateReception));
    	$this->object_tables["echantillon"]->recSetValue("ech_ns_date_saisie","0000-00-00");
    	$this->object_tables["echantillon"]->recSetValue("sup_code_support","4");
    	
    	/*
    	 * Recherche de correspondance inutile depuis que le code d'intervenant est fourni directement depuis le fichier
    	$myCurGestionnaire=$this->object_tables["echantillon"]->recGetValue("int_code_intervenant_gestionnaire");
    	if(isset($theIntervenantGestionnaire[$myCurGestionnaire]))
    		$this->object_tables["echantillon"]->recSetValue("int_code_intervenant_gestionnaire",$theIntervenantGestionnaire[$myCurGestionnaire]);
    	*/
    	
    	/*
    	 * Si aucune référence n'est fournie pour l'échantillon, on ne stocke pas les paramètres
    	 */
    	if($debug) echo __LINE__." => Vérification de la présence d'une référence échantillon : ".$this->object_tables["echantillon"]->recGetValue("ech_reference_echantillon")."<br />\n";
		if($this->object_tables["echantillon"]->recGetValue("ech_reference_echantillon")!="")
		{
			if($debug) echo __LINE__." => Ref OK, on sauve  (date : ".$theDateReception." => ".Tools::DateUserToSQL($ThePrefs->DatePrefs,$theDateReception)."/".Tools::Display($ThePrefs->DatePrefs)." )<br />\n";
			//if($debug) echo __LINE__." => Objet sauvé : ".Tools::Display($this->object_tables["echantillon"]->recGetRecord());

			$myStoreReturn=$this->object_tables["echantillon"]->recStore();
			if(!$myStoreReturn)
				die(__LINE__." => Erreur de création de l'enregistrement : ".$this->object_tables["echantillon"]->recDBError());
			$myIDEchantillon=$this->object_tables["echantillon"]->recKeyValue();
			if($myCurRecordNew)
					$this->batch_add_line("echantillon",$myIDEchantillon);
			if($debug) echo __LINE__." => ID de l'échantillon sauvé : ".$myIDEchantillon."<br />\n";
			
			
			/*
			 * Import des résultats : analyse 
			 */
			if($debug) echo __LINE__." => Sauvegarde des résultats d'analyse, traitement de la liste des paramètres<br />\n";
			/*
			 * Préparation du tableau association réalisant le croisement entre paramètres et ID de paramètres
			 */
	    	$myAssociationArrayCodeToParamId=$this->initCodeToParamId();
	    	if($myAssociationArrayCodeToParamId!==false)
	    	{
	    		if($debug) echo __LINE__." => Tableau associatif OK !".BR; //.Tools::Display($myAssociationArrayCodeToParamId);
	    		/*
	    		 Boucle spécifique de récupération des données hors résultat, tel que la fraction, etc.
	    		 */
	    		// Initialisation des valeurs recherchées
	    		$curCodeFraction="";
	    		// Boucle sur les colonnes
		    	foreach($this->curobject as $key=>$curattrib)
		    	{
		    		if($curattrib->type->type_import_colonne=="analyse" && $curattrib->type->parametre_import_colonne!="ana_resultat" )
		    		{
		    			switch($curattrib->type->parametre_import_colonne)
		    			{
		    				case "faa_code_fraction":
		    					$curCodeFraction=$curattrib->value;
		    					if(isset($theLexiques["FractionAnalysee"][$curCodeFraction]))
		    						$curCodeFraction=$theLexiques["FractionAnalysee"][$curCodeFraction];
		    					break;
		    			}
		    		}
		    	}
		    	foreach($this->curobject as $key=>$curattrib)
		    	{
		    		// Filtre uniquement sur les résultats d'analyse
		    		if($curattrib->type->type_import_colonne=="analyse" && $curattrib->type->parametre_import_colonne=="ana_resultat" )
		    		{
		    			/*
		    			 * Cas spécifique : demande, depuis le fichier de données, d'effacement d'une valeur
		    			 * 
		    			 */
		    			if(trim($curattrib->value)=="X")
		    			{
			    			if(isset($myAssociationArrayCodeToParamId[$curattrib->type->cd_import_colonne]))
				    		{
			    				$myCurParamId=$myAssociationArrayCodeToParamId[$curattrib->type->cd_import_colonne];
			    				if($debug) echo "Analyse pré-existante : "."par_id_parametre=".$myCurParamId." AND ana_reference_producteur='".$myRefEchantillon."'"." ===== \n";
			    				$this->object_tables["analyse"]->recSQLSearch("par_id_parametre=".$myCurParamId." AND ana_reference_producteur='".$myRefEchantillon."'");
			    				if($this->object_tables["analyse"]->recFirst())
			    				{
			    					if($debug) echo "Enregistrement trouvé, effacement demande, exécution ...".BR;
			    					if(!$debug)
			    						$this->object_tables["analyse"]->recDelete();
			    				}
				    		}
		    			}
			    		
			    		/*
			    		 * Cas général : pas de - ni de X, donc une valeur à stocker
			    		 */
		    			if(trim($curattrib->value)!="-" && trim($curattrib->value)!="X")
		    			{
				    		//if($debug) echo __LINE__." => Colonne :" .$curattrib->type->type_import_colonne.", type : ".$curattrib->type->parametre_import_colonne."  ! ===== \n";
			    			if(isset($myAssociationArrayCodeToParamId[$curattrib->type->cd_import_colonne]))
			    			{
			    				$myCurParamId=$myAssociationArrayCodeToParamId[$curattrib->type->cd_import_colonne];
			    				//echo BR.BR.__LINE__." => ID du paramètre en cours TROUVE ! (".$myCurParamId.")".Tools::Display($curattrib);
			    				//if($debug) echo "Analyse pré-existante : "."par_id_parametre=".$myCurParamId." AND ana_reference_producteur='".$myRefEchantillon."'"." ===== \n";
			    				$this->object_tables["analyse"]->recSQLSearch("par_id_parametre=".$myCurParamId." AND ana_reference_producteur='".$myRefEchantillon."'");
			    				$myCurRecordNew=false;
			    				if(!$this->object_tables["analyse"]->recFirst())
			    				{
			    					$myCurRecordNew=true;
									$this->object_tables["analyse"]->recNewRecord();
									$this->object_tables["analyse"]->recSetValue($this->object_tables["analyse"]->recKeyName(),null);
									//if($debug) echo "NON trouvée ===== \n";
			    				}
			    				//else
									//if($debug) echo "TROUVEE ===== \n";
								
						    	$curattrib->value=$this->formatInputData($curattrib->value,$curattrib->type);
								if(trim($curattrib->value)!="")
								{
									$this->object_tables["analyse"]->recSetValue("ech_id_echantillon",$myIDEchantillon);
									$this->object_tables["analyse"]->recSetValue("ana_reference_producteur",$myRefEchantillon);
					    			$this->object_tables["analyse"]->recSetValue("par_id_parametre",$myCurParamId);
					    			$this->object_tables["analyse"]->recSetValue("faa_code_fraction",$curCodeFraction);
					    			if(isset($curattrib->type->format_import_colonne) && trim($curattrib->type->format_import_colonne)!="")
						    			$this->object_tables["analyse"]->recSetValue("ana_unite_mesure",$curattrib->type->format_import_colonne);
					    			
					    			$curattrib->value=str_replace(",",".",$curattrib->value);
					    			if(strstr($curattrib->value,"<")!==false)
					    			{
					    				$curattrib->value=trim(str_replace("<","",$curattrib->value));
						    			$this->object_tables["analyse"]->recSetValue("ana_resultat",$curattrib->value);
						    			$this->object_tables["analyse"]->recSetValue("ana_limite_quantification",$curattrib->value);
						    			$this->object_tables["analyse"]->recSetValue("ana_code_remarque","10");				    				
					    			}
					    			else
					    			{
						    			$this->object_tables["analyse"]->recSetValue("ana_resultat",trim($curattrib->value));
						    			$this->object_tables["analyse"]->recSetValue("ana_limite_quantification","");
						    			$this->object_tables["analyse"]->recSetValue("ana_code_remarque","1");				    				
					    			}
					    			
					    			/*
					    			 * Utilisation de la date de lyophilisation en lieu et place de la date d'analyse car on ne dispose pas de cette dernière
					    			 */
					    			//$this->object_tables["analyse"]->recSetValue("ana_date_analyse",Tools::DateUserToSQL($ThePrefs->DatePrefs,$theDateAnalyse));
					    			$this->object_tables["analyse"]->recSetValue("ana_date_analyse",$myDateLyophilisationLot);

					    			$this->object_tables["analyse"]->recSetValue("int_id_intervenant",$theCodeIntervenant);
					    			//if($debug) echo __LINE__." => Objet sauvé : ".Tools::Display($this->object_tables["analyse"]->recGetRecord());
					    			//if(!$debug)
					    			{
					    				if($this->object_tables["analyse"]->recStore())
						    			{
											//if($debug) echo "Donnée ok et sauvegardée (date : ".$theDateAnalyse." => ".Tools::DateUserToSQL($ThePrefs->DatePrefs,$theDateAnalyse)." )\n";
											$myIDAnalyse=$this->object_tables["analyse"]->recKeyValue();
						    				if($myCurRecordNew)
												$this->batch_add_line("analyse",$myIDAnalyse);
						    			}
										//else
											//if($debug) echo "Donnée ok mais PAS sauvegardée\n";
					    			}
								}
								else
									if($debug) echo "Aucune donnée\n";
								//die(Tools::Display($this->_db->getQuery()));
			    			}
			    			elseif($debug)
			    				echo BR.BR.__LINE__." => ID du paramètre en cours (".$curattrib->type->cd_import_colonne.") NON trouvé !".Tools::Display($curattrib);
			    			//if($debug) echo "<br />\n";
		    				
		    			}
		    		}
		    	}
	    	}
		}
			
    	return $myIDEchantillon;
    	
    }
    
    function fieldValue($theTableName,$theFieldName)
    {
		foreach($this->curobject as $key=>$curattrib)
    		if($curattrib->type->type_import_colonne==$theTableName && $curattrib->type->parametre_import_colonne==$theFieldName)
    			return $curattrib->value;
    	return null;
    }    
    
    function fieldValueFromLabel($theTableName,$theFieldLabel)
    {
		foreach($this->curobject as $key=>$curattrib)
    		if($curattrib->type->type_import_colonne==$theTableName && $curattrib->type->libelle_import_colonne==$theFieldLabel)
    			return $curattrib->value;
    	return null;
    }    
    function saveParam($objprel,$objparam,$value)
    {
    	$this->_db->setQuery("SELECT * FROM rel_analyse_parametre as rapa, rel_analyse_prelevement as rape WHERE rapa.analyse_id_analyse=rape.analyse_id_analyse AND rapa.parametre_id_parametre=".$objparam->id_parametre." AND rape.prelevement_id_prelevement=".$objprel->id_prelevement.";");
    	//echo "REcherche valeur existante requête : ".$this->_db->getQuery()."<br>\n";
    	$myObj=null;
		$myObj->cd_reference_producteur=$objprel->reference_prelevement_laboratoire;
		$myObj->d_date_analyse=$objprel->date_debut_prelevement;
		$myObj->h_heure_analyse=$objprel->heure_debut_prelevement;
		$myObj->foreign_cd_parametre=$objparam->cd_parametre;
		$myObj->n_resultat_analyse=floatval(str_replace(",",".",$value));
    	if($this->_db->loadObject($myObj))
    	{
    		$this->_db->updateObject("analyse",$myObj,"id_analyse");
    	}
    	else
    	{
	    	//unset($myObj->id_analyse);
	    	//echo "Insertion objet ".Tools::Display($myObj)."</pre>\n";
			if($this->_db->insertObject("analyse",$myObj,"id_analyse"))
			{
				//echo "Valeur ajoutée, requête : ".$this->_db->getQuery()."<br>\n";
		    	$myRelation=null;
				$myRelation->analyse_id_analyse=$myObj->id_analyse;
				$myRelation->parametre_id_parametre=$objparam->id_parametre;
				$this->_db->insertObject("rel_analyse_parametre",$myRelation,"id_rel_analyse_parametre");
				
		    	$myRelation=null;
				$myRelation->prelevement_id_prelevement=$objprel->id_prelevement;
				$myRelation->analyse_id_analyse=$myObj->id_analyse;
				$this->_db->insertObject("rel_analyse_prelevement",$myRelation,"id_rel_analyse_parametre");
			}
    	}
		
		/*
		$myObj->code_remarque_analyse
		$myObj->analyse_in_situ
		$myObj->difficultes_analyse
		$myObj->commentaire_analyse
		$myObj->commentaire_resultat
		$myObj->unite_mesure
		$myObj->statut_resultat_analyse
		$myObj->accreditation_analyse
		$myObj->limite_detection
		$myObj->limite_quantification
		$myObj->limite_saturation
		$myObj->incertitude_analytique
		*/
    }
}

class clsDataFile
{
	var $initok=false;
	var $data=array();
	var $fh=null;
	var $curline=0;
	var $progressive=false;
	
	function clsDataFile($file,$type="csv",$progressive=false)
	{
		$this->progressive=$progressive;
		if($type=="csv")
		{
			$this->fh=fopen($file,"rt");
			if(!$progressive)
			{
				$myLine=1;
				while($myLineArray=fgetcsv($this->fh,0,";","\""))
				{
					$myNbCols=count($myLineArray);
					$myEmptyLine=true;
					if(trim(implode("",$myLineArray))!="")
						$myEmptyLine=false;
					//echo "Nb cols pour ligne ".$myLine." : ".$myNbCols."<br>\n";
					//echo "Ligne : ".implode("|",$myLineArray);
					if(!$myEmptyLine)
					{
						$this->data[]=$myLineArray;
						$myLine++;					
					}
				}
				fclose($this->fh);
			}
			else
			{
				$this->curline=0;
			}
			$this->initok=true;
		}
		return false;
	}
	
	function getHeaders()
	{
		return $this->data[0];
	}
	
	function colcount($row=0)
	{
		if($row==0 || $this->progressive)
			$row=$this->curline;
		if(isset($this->data[intval($row)]))
			return count($this->data[intval($row)]);
		return 0;
	}
	
	function fileResetPosition()
	{
		rewind($this->fh);
		$this->curline=0;
		unset($this->data);
		if($myLineArray=fgetcsv($this->fh,0,";","\""))
		{
			$myNbCols=count($myLineArray);
			$myEmptyLine=true;
			if(trim(implode("",$myLineArray))!="")
				$myEmptyLine=false;
			//echo "Nb cols pour ligne ".$myLine." : ".$myNbCols."<br>\n";
			//echo "Ligne : ".implode("|",$myLineArray);
			if(!$myEmptyLine)
			{
				$this->data[$this->curline]=$myLineArray;
				return true;	
			}
		}
		return false;
	}
	
	function gotoRow($theLine)
	{
		if(!$this->progressive)
			return false;
		rewind($this->fh);
		$this->curline=0;
		unset($this->data);
		while($myLineArray=fgetcsv($this->fh,0,";","\""))
		{
			if($this->curline==$theLine)
			{
				$myNbCols=count($myLineArray);
				$myEmptyLine=true;
				if(trim(implode("",$myLineArray))!="")
					$myEmptyLine=false;
				//echo "Nb cols pour ligne ".$myLine." : ".$myNbCols."<br>\n";
				//echo "Ligne : ".implode("|",$myLineArray);
				if(!$myEmptyLine)
				{
					$this->data[$this->curline]=$myLineArray;
					return $this->curline;
				}
			}				
			$this->curline++;
		}
		return false;
	}
	
	function getNextRow()
	{
		if($this->progressive)
		{
			if($myLineArray=fgetcsv($this->fh,0,";","\""))
			{
				$myNbCols=count($myLineArray);
				$myEmptyLine=true;
				if(trim(implode("",$myLineArray))!="")
					$myEmptyLine=false;
				//echo "Nb cols pour ligne ".$myLine." : ".$myNbCols."<br>\n";
				//echo "Ligne : ".implode("|",$myLineArray);
				$this->curline++;
				unset($this->data);
				$this->data[$this->curline]=array();
				if(!$myEmptyLine)
				{
					$this->data[$this->curline]=$myLineArray;
					return $this->curline;
				}
			}
		}
		return false;
	}
	
	function rowcount()
	{
		if($this->progressive)
			return $this->curline;
		if(isset($this->data) && is_array($this->data)) 
			return count($this->data);
		return 0;
	}
	
	function val($row,$col)
	{
		if(isset($this->data[$row][$col]))
			return $this->data[$row][$col];
		else
			return "";
	}
}
?>