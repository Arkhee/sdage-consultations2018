<?php

class batchfile extends mosDBTable
{
	var $_db;
	var $_tablename="a_import_batch";
	var $_key="id_a_import_batch";
	var $id_a_import_batch=null;
	var $batch_name;
	var $batch_date;
	var $batch_author;
	var $batch_validated;
	var $batch_filename;
	
	function batchfile(&$database)
	{
		$this->_db=$database;
		$this->mosDBTable( $this->_tablename, $this->_key, $this->_db );
	}
	
	function clear_batch()
	{
    	$key=$this->_key;
    	$this->$key=null;
    	$this->batch_name="";
    	$this->batch_date=date("Y-m-d");
    	$this->batch_time=date("H:i:s");
    	$this->batch_author="";
    	$this->batch_validated=0;
    	$this->batch_filename="";
	}
	
	function list_batch($theImportName,$theUserName="import")
	{
		$this->_db->setQuery("SELECT b.*,COUNT(*) as batch_taille FROM ".$this->_tablename." as b LEFT JOIN #__a_import_batch_data as d ON ( b.id_a_import_batch=d.a_import_batch_id_a_import_batch) WHERE b.batch_name='".addslashes($theImportName)."' AND b.batch_author='".addslashes($theUserName)."' GROUP BY b.id_a_import_batch ORDER BY b.id_a_import_batch DESC ;");
		$myList=$this->_db->loadObjectList();
		if(is_array($myList) && count($myList)>0)
			return $myList;
		return false;
	}
	
    function init($theImportName,$theUserName="import",$theFileName="")
    {
    	if($this->key_value()!==false)
    		return true;
    	$key=$this->_key;
    	$this->$key=null;
    	$this->batch_name=$theImportName;
    	$this->batch_date=date("Y-m-d");
    	$this->batch_time=date("H:i:s");
    	$this->batch_author=$theUserName;
    	$this->batch_validated=0;
    	$this->batch_filename=$theFileName;
    	return $this->store();
    }
    
    function validate($theId=null)
    {
    	if(!is_null($theId))
    		$this->load($theId);
    	if($this->key_value()===false)
    		return false;
    	$this->batch_validated=1;
    	return $this->store();
    }
    
    function unvalidate($theId=null)
    {
    	if(!is_null($theId))
    		$this->load($theId);
    	if($this->key_value()===false)
    		return false;
    	$this->batch_validated=0;
    	return $this->store();
    }
    
    function add_line($theTable,$theRecord)
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
    		if(!$myReturn)
    			echo "Erreur ajout : ".$this->_db->getErrorMsg()."<br />";
    	}
		return $myReturn;
    }
    
    function load($theId)
    {
    	return parent::load($theId);
    }
    
    function get_line($theId)
    {
    	if(!is_null($theId) && $theId>0)
    	{
	    	$this->_db->setQuery("SELECT * FROM #__a_import_batch_data WHERE id_a_import_batch_data=".$theId);
	    	$myList=$this->_db->loadObjectList();
	    	if(is_array($myList) && count($myList)>0)
	    		return $myList;
    	}
    	return false;
    	
    }
    
    function list_batch_lines()
    {
    	if($this->key_value()===false)
    		return false;
    	
    	$this->_db->setQuery("SELECT * FROM #__a_import_batch_data WHERE a_import_batch_id_a_import_batch=".$this->key_value() ." ORDER BY a_import_batch_id_a_import_batch ASC");
    	$myList=$this->_db->loadObjectList();
    	if(is_array($myList) && count($myList)>0)
    		return $myList;
    	return false;
    }
    
    function key_value()
    {
    	$key=$this->_key;
    	if(!isset($this->$key) || is_null($this->$key))
    		return false;
    	return $this->$key;  	
    }
    
    function delete_line($theLineId)
    {
    	if(!is_null($theLineId) && $theLineId>0)
    	{
    		$this->_db->setQuery("DELETE FROM #__a_import_batch_data WHERE id_a_import_batch_data=".$theLineId);
    		return $this->_db->query();
    	}
    	return false;
    }
    
    function delete_batch_data($theId=null)
    {
		global $classprefix,$template_name,$path_abs;
		$debugdelete=false;
		if($debugdelete) echo __LINE__." => Nettoyage batch n°".$theId.BR;
    	if(!is_null($theId))
    		$this->load($theId);
    	if($this->key_value()===false)
    		return false;
    	$myLines=$this->list_batch_lines();
    	if(is_array($myLines) && count($myLines)>0)
    	{
    		$myObjectsList=array();
    		foreach($myLines as $curline)
			{
				// Pour chaque ligne, effacement de la donnée correspondante dans la table
				if(!isset($myObjectsList[$curline->batchdata_table]))
				{
	    			$myClassName=$classprefix.$curline->batchdata_table;
	    			$curobject =
	    						new $myClassName($this->_db,$template_name,basename(__FILE__),$path_abs,true);
	    			$myObjectsList[$curline->batchdata_table]=new stdClass();
	    			$myObjectsList[$curline->batchdata_table]->key_name=trim($curobject->recKeyName());
	    			$myObjectsList[$curline->batchdata_table]->table_name=trim($curobject->recTableName());
	    			if($myObjectsList[$curline->batchdata_table]->key_name=="" || $myObjectsList[$curline->batchdata_table]->table_name=="")
	    				$myObjectsList[$curline->batchdata_table]=false;
	    			unset($curobject);
				}
				
				if(is_object($myObjectsList[$curline->batchdata_table]))
				{
					$this->_db->setQuery("DELETE FROM ".$myObjectsList[$curline->batchdata_table]->table_name." WHERE ".addslashes($myObjectsList[$curline->batchdata_table]->key_name)."=".$curline->batchdata_record_id);
					if($debugdelete) echo __LINE__." => Requête : ".$this->_db->getQuery();
					else 
						$this->_db->query();
				}
			}
    	}
    	if($debugdelete) echo __LINE__." => Fin script effacement du batch".BR;
		else
			$this->delete();
    }
    
    function delete($theId=null)
    {
    	if(!is_null($theId))
    		$this->load($theId);
    		
    	if($this->key_value()===false)
    		return false;
    	
    	$this->_db->setQuery("DELETE FROM #__a_import_batch_data WHERE a_import_batch_id_a_import_batch=".$this->key_value());
    	if($this->_db->query())
    	{
    		parent::delete();
    	}
    }
}
?>