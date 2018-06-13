<?php
if(!isset($langtable["ae_avis"])) $langtable["ae_avis"]="Avis";
if(!isset($langtable["id_massedeau"])) $langtable["id_massedeau"]="Entité hydro";
if(!isset($langtable["id_pression"])) $langtable["id_pression"]="Entité hydro";
if(!isset($langtable["impact_estime"])) $langtable["impact_estime"]="Impact estimé";
if(!isset($langtable["pression_cause_du_risque"])) $langtable["pression_cause_du_risque"]="Pression cause du risque";
if(!isset($langtable["commentaires"])) $langtable["commentaires"]="Entité hydro";
if(!isset($langtable["documents"])) $langtable["documents"]="Pièce jointe";
if(!isset($langtable["date_modification"])) $langtable["date_modification"]="Entité hydro";
if(!isset($langtable["date_validation"])) $langtable["date_validation"]="Entité hydro";

class mdtb_ae_avis extends mdtb_table
{	
	function specific_init()
	{
		$this->table_name="#__ae_avis";
		$this->_tbl="#__ae_avis";
		$i=1;
		$this->add_field("id_avis","number","","","",0,0,0);
		$this->add_field("id_massedeau","reference","ae_massesdeau","id_massedeau","libelle_me",$i,$i,$i++);
		$this->add_field("id_pression","reference","ae_pressions","id_pression","libelle_pression",$i,$i,$i++);
		$this->add_field("id_user","reference","mdtb_users","user_ID","user_Login",$i,$i,$i++);
		$this->add_field("impact_estime","number","","","",$i,$i,$i++);
		$this->add_field("pression_cause_du_risque","number","","","",$i,$i,$i++);
		$this->add_field("commentaires","text","","","",$i,$i,$i++);
		$this->add_field("documents","text","","","",$i,$i,$i++);
		$this->add_field("date_modification","dateauto_modification","","","",$i,$i,$i++);
		$this->add_field("date_validation","date","","","",$i,$i,$i++);

		
		$this->set_key("id_avis");
		$this->searchable=array("impact","commentaires");
		$this->display_in_search=array("id_massedeau","id_pressions","impact","commentaire");
		$this->name="ae_massesdeau";
		$this->mode="self";


		
		$this->nbperpage=20;
		
		$this->_defaultparams->sortorder="ASC";
		$this->_defaultparams->sortfield="";
		$this->_template_sections=array("header"=>"header","footer"=>"footer","menu"=>"menu","ajaxsearchlist"=>"ajaxsearchlist","ajaxselectlist"=>"ajaxselectlist","form"=>"form","list"=>"list","detail"=>"detail");
		if(method_exists($this,"parent_init")) $this->parent_init();
	}
	
	public function getAvisDefaultObject()
	{
		$obj = new stdClass();
		$obj->avis_valide="";
		$obj->impact_estime="";
		$obj->pression_cause_du_risque="";
		$obj->justification="";
		$obj->lien_documents="";
		//die("Objet : <pre>".print_r($obj,true)."</pre>");
		return $obj;
	}
	
	public function getAvisPourPressionMdo($id_pression,$id_mdo)
	{
		if(!isset($this->_auth)) die("Pas de auth");
		if(!is_object($this->_auth)) die("auth pas objet");
		if(!$this->_auth->isLoaded())
		{
			$objAvisVide=new stdClass();
			$objAvisVide->pression_cause_du_risque="";
			$objAvisVide->impact_estime="";
			$objAvisVide->avis_valide="";
			return $objAvisVide;
			return array(); //die("auth not loaded");
		}
		//return $this->getAvisDefaultObject();
		$requeteAvis="
			SELECT 
				IF(a.date_validation='0000-00-00 00:00:00','','avis_valide') AS avis_valide,
				a.*
			FROM ".$this->table_name." AS a
			WHERE a.id_pression=".(int)$id_pression." AND a.id_massedeau=".(int)$id_mdo." AND a.id_user=".$this->_auth->user_ID.";
		";
		
		$this->_db->setQuery($requeteAvis);
		$liste=$this->_db->loadObjectList();
		if(!is_array($liste) || !count($liste))
		{
			$obj=$this->getAvisDefaultObject();
		}
		else
		{
			$obj = $liste[0];
			$obj->lien_documents="<a target='_blank' data-basehref='documents/' href='documents/".$obj->documents."'>".str_replace($id_mdo."_".$id_pression."_".$this->_auth->user_ID."-","",$obj->documents)."</a>";
		}
		
		return $obj;
	}
}