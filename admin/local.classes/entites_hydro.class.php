<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of entites_hydroclass
 *
 * @author yb
 */

class entites_hydro extends mosDBTable
{

	var $id_entite;
	var $Codent;
	var $Noment;
	var $Code_me;
	var $Code_me_eu;
	var $Type_litho;
	var $Etat;
	var $Superf_tot;
	var $Superf_sou;
	var $Superf_aff;
	var $Limites;
	var $Karstique;
	var $Structure;
	var $Apports_ne;
	var $Generalite;
	var $Commentair;

	var $_key="id_entite";
	var $_tablename="entites_hydro";

    function entites_hydro(&$database)
    {
    	$this->mosDBTable( $this->_tablename, $this->_key, $database );
    }
	/*
	 	CREATE TABLE entites_hydro(
		id_entite INT NOT NULL AUTO_INCREMENT,
		Codent VARCHAR(10),
		Noment TEXT,
		Code_me VARCHAR(12),
		Code_me_eu VARCHAR(12),
		Type_litho TEXT,
		Etat TEXT,
		Superf_tot DOUBLE(15,6),
		Superf_sou DOUBLE(15,6),
		Superf_aff DOUBLE(15,6),
		Limites TEXT,
		Karstique TEXT,
		Structure TEXT,
		Apports_ne TEXT,
		Generalite TEXT,
		Commentair TEXT,
		PRIMARY KEY(id_entite));
	 */
	var $Nom_Entite="";

	function setProperty($theProp,$theVal)
	{
		if(property_exists($this, $theProp))
		{
			$this->$theProp=$theVal;
			return true;
		}
		return false;
	}

	function loadCode($theCode)
	{
		$this->_db->setQuery("SELECT * FROM ".$this->_tablename." WHERE Codent='".addslashes($theCode)."';");
		return $this->_db->loadObject($this);
	}

	function store()
	{
		if(isset($this->Codent) && $this->Codent!="")
		{
			$myObj=new entites_hydro($this->_db);
			if($myObj->loadCode($this->Codent))
			{
				$this->id_entite=$myObj->id_entite;
			}
			return parent::store();
		}
			return false;
	}
}
?>
