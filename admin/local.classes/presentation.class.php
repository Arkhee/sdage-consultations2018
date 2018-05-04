<?php
class presentation
{
	var $_section="";
	var $action="";
	var $zoneId=-1;
	var $zoneCode="";
	var $zoneType="";
	var $search_key;
	var $special_message="";
	
    var	$_search_description=
    		array(
				"departements"=>array("label"=>"le Département : ","zone_code"=>"","select_action"=>SELECT_ACTION,"fields"=>array("Code_Departement","Nom_Departement")),
				"regions"=>array("label"=>"la Région : ","zone_code"=>"","select_action"=>SELECT_ACTION,"fields"=>array("Nom_Region")),
				"postcode"=>array("label"=>"le Code postal : ","zone_code"=>"","select_action"=>SELECT_ACTION,"fields"=>array("Code_Postal","Nom_Commune")),
				"insee"=>array("label"=>"le N° insee : ","zone_code"=>"","select_action"=>SELECT_ACTION,"fields"=>array("Code_INSEE","Nom_Commune")),
				"townname"=>array("label"=>"la Commune : ","zone_code"=>"","select_action"=>SELECT_ACTION,"fields"=>array("Nom_Commune")),
				"ssbv"=>array("label"=>"le sous-bassin :<br>\n","zone_code"=>"Code_SSBV","select_action"=>OBJECTIFS_ACTION,"fields"=>array("Nom_SSBV", "Code_SSBV")),
				"mdosout"=>array("label"=>"la masse d'eau souterraine :<br>\n","zone_code"=>"Code_MDO","select_action"=>OBJECTIFS_ACTION,"fields"=>array("Nom_MDO", "Code_MDO")),
				"mdosup"=>array("label"=>"la masse d'eau superficielle :<br>\n","zone_code"=>"Code_MDO","select_action"=>OBJECTIFS_ACTION,"fields"=>array("Nom_MDO", "Code_MDO"))
				);
	var $_tpl;
	var $_db;
	var $_path_abs;
    function presentation($template,$section,$database)
    {
		global $path_abs;
		$this->_path_abs=$path_abs;
    	$this->_db=$database;
    	$this->_section=$section;
    	$this->_tpl=$template;
    	$this->bindParams();
    	$this->handle();
    }
    
    function headers()
    {
		$myNav="";
    	if(defined("NAVIGATION_TEXT_INCLUDE"))
    	{
    		if(file_exists($this->_path_abs."/".HEADERS_TEXT_INCLUDE))
    			$myNav=file_get_contents($this->_path_abs."/".HEADERS_TEXT_INCLUDE);
    		else
    			$myNav="<div id='header' style='display:none;>Fichier introuvable : ".HEADERS_TEXT_INCLUDE."</div>";
    	}
    	else
    	{    		
			$myNav="<div id='header' style='display:none;'>Define non défini</div>";
    	}
    			
    	return $myNav;
    }
    
	function navigation()
	{
		$myNav="";
    	if(defined("NAVIGATION_TEXT_INCLUDE"))
    	{
    		if(file_exists($this->_path_abs."/".NAVIGATION_TEXT_INCLUDE))
    			$myNav=file_get_contents($this->_path_abs."/".NAVIGATION_TEXT_INCLUDE);
    		else
    			$myNav="<div id='navigation' style='display:none;>Fichier introuvable : ".NAVIGATION_TEXT_INCLUDE."</div>";
    	}
    	else
    	{    		
			$myNav="<div id='bottom' style='display:none;'>Define non défini</div>";
    	}
    	
    	return $myNav;
		
	}

	function chemin()
	{
		$myNav="";
    	if(defined("PATH_TEXT_INCLUDE"))
    	{
    		if(file_exists($this->_path_abs."/".PATH_TEXT_INCLUDE))
    			$myNav=file_get_contents($this->_path_abs."/".PATH_TEXT_INCLUDE);
    		else
    			$myNav="<div id='bottom' style='display:none;>Fichier introuvable : ".PATH_TEXT_INCLUDE."</div>";
    	}
    	else
    	{    		
			$myNav="<div id='bottom' style='display:none;'>Define non défini</div>";
    	}
    			
    	return $myNav;
		
	}

	function baniere()
	{
		$myNav="";
    	if(defined("BANNER_TEXT_INCLUDE"))
    	{
    		if(file_exists($this->_path_abs."/".BANNER_TEXT_INCLUDE))
    			$myNav=file_get_contents($this->_path_abs."/".BANNER_TEXT_INCLUDE);
    		else
    			$myNav="<div id='bottom' style='display:none;>Fichier introuvable : ".BANNER_TEXT_INCLUDE."</div>";
    	}
    	else
    	{    		
			$myNav="<div id='bottom' style='display:none;'>Define non défini</div>";
    	}
    			
    	return $myNav;
		
	}


    function bottom()
    {
    	$myBottom="";
    	if(defined("BOTTOM_TEXT_INCLUDE"))
    	{
    		if(file_exists($this->_path_abs."/".BOTTOM_TEXT_INCLUDE))
    			$myBottom=file_get_contents($this->_path_abs."/".BOTTOM_TEXT_INCLUDE);
    		else
    			$myBottom="<div id='bottom' style='display:none;>Fichier introuvable : ".BOTTOM_TEXT_INCLUDE."</div>";
    	}
    	else
    	{    		
			$myBottom="<div id='bottom' style='display:none;'>Define non défini</div>";
    	}
    	$myBottom="<div class=\"".$this->_section."\">".$myBottom."</div>\n";	
    	return $myBottom;
    }
    
    function handle()
    {
    	switch($this->_section)
    	{
    		case "index":
    			break;
    		case "search":
    			if($this->action=="search" && $this->search_key=="")
    				$this->redirect(INDEX_ACTION."?special_message=".urlencode("<p>Veuillez entrer une commune, département ou région</p>"));
    			break;
    		case "select":
    			break;
    		case "zones":
    			break;
    		case "static":
    			break;
    		case "detail":
    			break;
    	}
    }
   
    function bindParams()
    {
    	$myArrProps=get_object_vars($this);
    	foreach($myArrProps as $curprop=>$curpropval)
    	{
    		if(substr($curprop,0,1)!="_")
    		{
    			if(isset($_GET[$curprop]))
    				$this->$curprop=$_GET[$curprop];
    			if(isset($_POST[$curprop]))
    				$this->$curprop=$_POST[$curprop];
    		}
    	}
    }	    

    function redirect($page)
    {
		if (headers_sent())
			echo "<script>document.location.href='".$page."';</script>\n";
		else
			header( "Location: ".$page );
    }

	function showSelectHeader($zonesgeo)
	{
		echo "Accès aux données pour ".$this->_search_description[$this->zoneType]["label"];
		echo "<b>";
		$first=true;
		//echo "Object : ".Tools::Display($zonesgeo);
		foreach($this->_search_description[$this->zoneType]["fields"] as $curfield)
		{
			if(!$first) echo "&nbsp;-&nbsp;";
			$first=false;
			if($this->zoneType=="ssbv")
				echo utf8_decode($zonesgeo->$curfield);
			else			
				echo $zonesgeo->$curfield;
		}
		echo "</b><br />\n";
	}

	function showSelectSSBVTables($zonesgeo)
	{
		echo "<h4>Veuillez sélectionner un bassin versant :</h4>";
		$myListMdo=$zonesgeo->loadMDOList($this->zoneType);
		$myListSSBV=$zonesgeo->loadSSBVList($this->zoneType);
		echo "			
			<table width='100%'>
				<tr>
				<td width='50%' valign=\"top\">
				<h5 class=\"zoneSelector\">Eaux souterraines :</h5>
				".$this->displayList($myListMdo,DEFAULT_ACTION)."
				</td>
				<td width='50%' valign=\"top\">
				<h5 class=\"zoneSelector\">Eaux de surface :</h5>
				".$this->displayList($myListSSBV,DEFAULT_ACTION)."
				</td>
				</tr>
			</table>
			";
		//echo "Object (".$this->zoneType.") : ".Tools::Display($zonesgeo);		
	}
	
	function showSelectSSBVMenu($zonesgeo,$zoneaction)
	{
		switch($this->zoneType)
		{
			case "mdosout":
			case "mdosup":
			case "ssbv":
				break;
			default:
				$myListMdo=$zonesgeo->loadMDOList($this->zoneType);
				$myListSSBV=$zonesgeo->loadSSBVList($this->zoneType);
				echo "<script type='text/javascript'>function menu_toggle(divid){ myDiv=document.getElementById(divid); if(myDiv.style.display=='none') myDiv.style.display='block'; else myDiv.style.display='none'; }</script>\n";
				echo "
					<div id=\"autreszones\" class=\"casebleue\">		
						<h4 style=\"color:#000000 !important; text-align:center !important;\">Autres zones</h4>
					</div>
					<div class=\"menuOtherZones\">
						<h5 class=\"zoneSelector\"><a href=\"#\" onclick=\"menu_toggle('mdolist');\">Eaux souterraines</a></h5>
						<div id=\"mdolist\" style=\"display:none;\">
							".$this->displayList($myListMdo,$zoneaction)."
						</div>
						
						<h5 class=\"zoneSelector\"><a href=\"#\" onclick=\"menu_toggle('ssbvlist');\">Eaux de surface</a></h5>
						<div id=\"ssbvlist\" style=\"display:none;\">
							".$this->displayList($myListSSBV,$zoneaction)."
						</div>
					</div>
					";
				//echo "Object (".$this->zoneType.") : ".Tools::Display($zonesgeo);		
				break;
		}
		
	}

	function showSelectContent()
	{
		$zonesgeo=new zonesgeo($this->_db);
		$zonesgeo->load($this->zoneId);
		//$this->showSelectHeader($zonesgeo);
		$this->showSelectSSBVTables($zonesgeo);
	}
   
	function displayList($theList,$theAction=DETAIL_ACTION)
	{
		if($theList===false || count($theList)<=0) return "";
		$buffer="";
		$buffer .= "<ul class=\"displayList\">\n";
		foreach($theList as $curmdo)
		{
			$buffer .= "<li><a href=\"".$theAction."?zoneCode=".$curmdo->Zone_Code_Zone."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">";
			$buffer .= trim($curmdo->Nom_Zone)."&nbsp;(".trim($curmdo->Zone_Code_Zone).")</a></li>";
		}
		$buffer .= "</ul>\n";
		return $buffer;
	}
	
	function showObjectifsNav()
	{
		switch($this->zoneType)
		{
			case "mdosout":
			case "mdosup":
			case "ssbv":
				break;
			default:
				$zonesgeo=new zonesgeo($this->_db);
				$zonesgeo->load($this->zoneId);
				$this->showSelectSSBVMenu($zonesgeo,OBJECTIFS_ACTION);
				break;
		}
	}
	
	function showRappelNav()
	{
		switch($this->zoneType)
		{
			case "mdosout":
				$zonesgeo=new obj_mdo_sout_etat($this->_db);
				break;
			case "mdosup":
				$zonesgeo=new obj_mdo_sup($this->_db);
				break;
			case "ssbv":
				$zonesgeo=new ssbv($this->_db);
				break;
			default:
				$zonesgeo=new zonesgeo($this->_db);
				break;
		}
		$zonesgeo->load($this->zoneId);
		$this->showSelectHeader($zonesgeo);
	}
	
	function showZoneNavigation($pos)
	{

		switch($this->zoneType)
		{
			case "mdosout":
				$myZType="mdosout";
				break;
			case "mdosup":
				$myZType="mdosup";
				break;
			case "ssbv":
				$myZType="ssbv";
				break;
			default:
				$zonegeo=new zonesgeo($this->_db);
				$myZType=$zonegeo->getZoneType($this->zoneCode);
				break;
		}
		
		if($myZType=="sout" || $myZType=="affl")
		{
			$ssbv=new communes_to_zones($this->_db);
			if($ssbv->loadCode($this->zoneCode))
			{
				
				echo "<a class=\"detailMainLink\" ".(($pos!="objectif")?"id=\"linkinactive\"":"")." href=\"".OBJECTIFS_ACTION."?zoneCode=".$this->zoneCode."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">Afficher les objectifs d'état pour cette masse d'eau</a>\n";
				echo "<a class=\"detailMainLink\" ".(($pos!="detail")?"id=\"linkinactive\"":"")." href=\"".DETAIL_ACTION."?zoneCode=".$this->zoneCode."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">Afficher les problèmes et mesures proposés pour cette masse d'eau</a>\n";
				echo "<br class=\"detailAfterLink\" />";
			}
			else
				echo "La masse d'eau ".$this->zoneCode." n'a pas été trouvée";
		}
		if($myZType=="mdosup")
		{
			$mdosup=new obj_mdo_sup($this->_db);
			if($mdosup->load($this->zoneId))
			{
				$ssbv=new ssbv($this->_db);
				$ssbv->loadCode($mdosup->SSBV_Code_SSBV);
				echo "<a class=\"detailMainLink\" ".(($pos!="objectif")?"id=\"linkinactive\"":"")." href=\"".OBJECTIFS_ACTION."?zoneCode=".$this->zoneCode."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">Afficher les objectifs d'état pour cette masse d'eau</a>\n";
				echo "<a class=\"detailMainLink\" ".(($pos!="detail")?"id=\"linkinactive\"":"")." href=\"".DETAIL_ACTION."?zoneCode=".$mdosup->SSBV_Code_SSBV."&zoneId=".$ssbv->ID."&zoneType=ssbv&zoneName=".$ssbv->Nom_SSBV."\">Afficher les problèmes et mesures proposés pour le sous-bassin de cette masse d'eau</a>\n";
				echo "<br class=\"detailAfterLink\" />";
			}
			else
				echo "La masse d'eau ".$this->zoneCode." n'a pas été trouvée";
		}
		if($myZType=="mdosout")
		{
			$ssbv=new obj_mdo_sout_etat($this->_db);
			if($ssbv->load($this->zoneId))
			{
				
				echo "<a class=\"detailMainLink\" ".(($pos!="objectif")?"id=\"linkinactive\"":"")." href=\"".OBJECTIFS_ACTION."?zoneCode=".$this->zoneCode."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">Afficher les objectifs d'état pour cette masse d'eau</a>\n";
				echo "<a class=\"detailMainLink\" ".(($pos!="detail")?"id=\"linkinactive\"":"")." href=\"".DETAIL_ACTION."?zoneCode=".$this->zoneCode."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">Afficher les problèmes et mesures proposés pour cette masse d'eau</a>\n";
				echo "<br class=\"detailAfterLink\" />";
			}
			else
				echo "La masse d'eau ".$this->zoneCode." n'a pas été trouvée";
		}
		if($myZType=="ssbv")
		{
			$ssbv=new ssbv($this->_db);
			if($ssbv->loadCode($this->zoneCode))
			{
				echo "<a class=\"detailMainLink\" ".(($pos!="objectif")?"id=\"linkinactive\"":"")." href=\"".OBJECTIFS_ACTION."?zoneCode=".$this->zoneCode."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">Afficher les objectifs d'état pour ce sous-bassin</a>\n";
				echo "<a class=\"detailMainLink\" ".(($pos!="detail")?"id=\"linkinactive\"":"")." href=\"".DETAIL_ACTION."?zoneCode=".$this->zoneCode."&zoneId=".$this->zoneId."&zoneType=".$this->zoneType."\">Afficher les problèmes et mesures proposés pour ce sous-bassin</a>\n";
				echo "<br class=\"detailAfterLink\" />";
			}
			else
				echo "Le bassin ".$this->zoneCode." n'a pas été trouvé";
		}
	}
	function showZoneHeader($pos)
	{

		switch($this->zoneType)
		{
			case "mdosout":
				$myZType="mdosout";
				break;
			case "mdosup":
				$myZType="mdosup";
				break;
			case "ssbv":
				$myZType="ssbv";
				break;
			default:
				$zonegeo=new zonesgeo($this->_db);
				$myZType=$zonegeo->getZoneType($this->zoneCode);
				break;
		}
		
		if($myZType=="sout" || $myZType=="affl")
		{
			$ssbv=new communes_to_zones($this->_db);
			if($ssbv->loadCode($this->zoneCode))
			{
				echo "Masse d'eau souterraine :<h4>".$ssbv->Nom_zone."&nbsp;(".$this->zoneCode.")</h4>\n";
			}
			else
				echo "La masse d'eau ".$this->zoneCode." n'a pas été trouvée";
		}
		if($myZType=="mdosup")
		{
			$ssbv=new obj_mdo_sup($this->_db);
			if($ssbv->load($this->zoneId))
			{
				echo "Masse d'eau superficielle :<h4>".$ssbv->Nom_MDO."&nbsp;(".$this->zoneCode.")</h4>\n";
			}
			else
				echo "La masse d'eau ".$this->zoneCode." n'a pas été trouvée";
		}
		if($myZType=="mdosout")
		{
			$ssbv=new obj_mdo_sout_etat($this->_db);
			if($ssbv->load($this->zoneId))
			{
				echo "Masse d'eau souterraine :<h4>".$ssbv->Nom_MDO."&nbsp;(".$this->zoneCode.")</h4>\n";
			}
			else
				echo "La masse d'eau ".$this->zoneCode." n'a pas été trouvée";
		}
		if($myZType=="ssbv")
		{
			$ssbv=new ssbv($this->_db);
			if($ssbv->loadCode($this->zoneCode))
			{
				echo "Sous-bassin : <h4>".utf8_decode($ssbv->Nom_SSBV)."&nbsp;(".$this->zoneCode.")</h4>\n";
			}
			else
				echo "Le bassin ".$this->zoneCode." n'a pas été trouvé";
		}
	}
	
	function showDetailNav()
	{
		switch($this->zoneType)
		{
			case "mdosout":
			case "mdosup":
			case "ssbv":
				break;
			default:
				$zonesgeo=new zonesgeo($this->_db);
				$zonesgeo->load($this->zoneId);
				$this->showSelectSSBVMenu($zonesgeo,DETAIL_ACTION);
				break;
		}
	}
	
	
	function showObjectifsContent()
	{
		//$zonesgeo=new zonesgeo($this->_db);
		//$zonesgeo->load($this->zoneId);
		//$this->showSelectHeader($zonesgeo);

		echo "<script type='text/javascript'>function content_toggle(divid){ myDiv=document.getElementById(divid); if(myDiv.style.display=='none') myDiv.style.display='block'; else myDiv.style.display='none'; }</script>\n";
		//echo "<h3>Objectifs pour les masses d'eau de ce bassin</h3>\n";
		echo "<h3>Liste des masses d'eau et objectifs d'état du SDAGE</h3>\n";
		switch($this->zoneType)
		{
			case "mdosout":
				$myCurZoneType="sout";
				break;
			case "mdosup":
				$myCurZoneType="sup";
				break;
			case "ssbv":
				$myCurZoneType="ssbv";
				break;
			default:
				$zonegeo=new zonesgeo($this->_db);
				$myCurZoneType=$zonegeo->getZoneType($this->zoneCode);
				break;
		}
		//echo __LINE__." => Type de la zone actuelle : ".$myCurZoneType."(".$this->zoneCode.")<br>\n";
		if($myCurZoneType==="ssbv" || $myCurZoneType==="sup")
		{
			//$myRel=new relation_zones_p1($this->_db);
			//$myZones=$myRel->search_zone($this->zoneCode);
			//echo __LINE__." => Zones : ".Tools::Display($myZones);
			//if($myZones===false)
			//{
			//	echo "<p>Aucune masse d'eau pour ce bassin</p>";
			//	return false;
			//}
			//$myMdoList=array();
			//foreach($myZones as $curzone)
			//	$myMdoList=array_merge($myMdoList,explode("#",$curzone->Liste_MDO));
				
			//foreach($myMdoList as $key=>$curmdo)
			//	if(trim($curmdo)=="")
			//		unset($myMdoList[$key]);

			//if(isset($myMdoList) && is_array($myMdoList) && count($myMdoList)>0)
			//{
			if($myCurZoneType==="ssbv")
			{
				$myMdoSup=new obj_mdo_sup($this->_db);
				//$myObjList=$myMdoSup->list_all_for_array($myMdoList);
				$myObjList=$myMdoSup->search(" SSBV_Code_SSBV='".addslashes($this->zoneCode)."' ");
			}
			if($myCurZoneType==="sup")
			{
				$myMdoList=array($this->zoneCode);
				if(isset($myMdoList) && is_array($myMdoList) && count($myMdoList)>0)
				{
					$myMdoSup=new obj_mdo_sup($this->_db);
					$myObjList=$myMdoSup->list_all_for_array($myMdoList);
				}
			}	
			foreach($myObjList as $key=>$curobj)
			{
				$myAnneeMax=((intval($curobj->BEC_Echeance)>intval($curobj->BEE_Echeance))?intval($curobj->BEC_Echeance):intval($curobj->BEE_Echeance));
				if($curobj->BE_Ecologique=="à préciser")
					$myEtatEco="Etat écologique à préciser";
				else
					$myEtatEco=$curobj->BE_Ecologique." écologique atteint en ".$curobj->BEE_Echeance;
				
				$myObjList[$key]->detail=
				 "	<li>".$myEtatEco."</li>\n".
				 "	<li>Bon état chimique atteint en ".$curobj->BEC_Echeance."</li>\n";
				 
				 if($curobj->BE_Echeance=="Objectif moins strict")
				 {
				 	$myObjList[$key]->detail.="	<li>Bon état global atteint en ".$myAnneeMax."</li>";
				 }
				 elseif(intval($curobj->BE_Echeance)>2000)
				 {
				 	$myObjList[$key]->detail.="	<li>Bon état global atteint en ".intval($curobj->BE_Echeance)."</li>";
				 	
				 }
				 
				if(intval($curobj->BE_Echeance)!=2015 && intval($curobj->BE_Echeance)!=2015)
				{
					if(intval($curobj->BE_Echeance)>2000)
						$myAnneeMax=($myAnneeMax>intval($curobj->BE_Echeance))?$myAnneeMax:intval($curobj->BE_Echeance);
					
					if(intval($curobj->BE_Echeance)>2000 || $curobj->BE_Echeance=="Objectif moins strict")
					{
						if($curobj->BE_Echeance=="Objectif moins strict")
							$myObjList[$key]->detail.="Moins strict pour ".$curobj->Justification_Parametre."\n";
						else
						{
								$myObjList[$key]->detail.="Justification de l'échéance ".$myAnneeMax." :\n";
								$myObjList[$key]->detail.="<ul>\n";
								if(trim($curobj->Justification_Cause)!="")
									$myObjList[$key]->detail.="	<li><b>Cause : </b>".$curobj->Justification_Cause."</li>\n";
								if(trim($curobj->Justification_Parametre)!="")
									$myObjList[$key]->detail.="	<li><b>Paramètre : </b>".$curobj->Justification_Parametre."</li>\n";
						}
					}
					else
					{
						$myObjList[$key]->detail.="<li>\n";
						$myObjList[$key]->detail.="Bon état global ".$curobj->BE_Echeance;
						if(trim($curobj->Justification_Cause)!="" || trim($curobj->Justification_Parametre)!="")
						{
							$myObjList[$key]->detail.="(".(($curobj->Justification_Cause!="")?$curobj->Justification_Cause:"");
							if(trim($curobj->Justification_Parametre)!="" && trim($curobj->Justification_Cause)!="")
								$myObjList[$key]->detail.=", ";
							$myObjList[$key]->detail.=$curobj->Justification_Parametre.")";	
						}
						$myObjList[$key]->detail.="</li>\n";
					}
					$myObjList[$key]->detail.="</ul>\n";
				}
			}
			//}
		}
		
		elseif($myCurZoneType=="sout" || $myCurZoneType=="affl")
		{
			$myMdoList[]=$this->zoneCode;
			$myMdoSout=new obj_mdo_sout_etat($this->_db);
			$myObjList=$myMdoSout->list_all_for_array($myMdoList);
			foreach($myObjList as $key=>$curobj)
			{
				$mySupplement="";
				if(intval($curobj->BE_echeance)!=2015)
				{
					$mySupplement=
						"<br />Justification de l'échéance ".$curobj->BE_echeance." : \n".
						"<ul>\n".
						"	<li>Cause : ".$curobj->Justification."</li>\n".
						"	<li>Paramètre : ".$curobj->Parametre."</li>\n".
						"</ul>\n";
						
				}
				$myObjList[$key]->detail=
					"	<li>Bon état quantitatif atteint en ".$curobj->Echeance_quantite."</li>\n".
					"	<li>Bon état chimique atteint en ".$curobj->Echeance_qualite."</li>\n".
					"	<li>Objectif global : bon état atteint en ".$curobj->BE_echeance.$mySupplement."</li>\n";
				/*
				 "	<li><b>Objectif qualité : </b>".$curobj->Objectif_qualite."</li>\n".
				 "	<li><b>Echéance qualité : </b>".$curobj->Echeance_qualite."</li>\n".
				 "	<li><b>Objectif quantité : </b>".$curobj->Objectif_quantite."</li>\n".
				 "	<li><b>Echéance quantité : </b>".$curobj->Echeance_quantite."</li>\n".
				 "	<li><b>BE écheance : </b>".$curobj->BE_echeance."</li>\n".
				 "	<li><b>Justification : </b>".$curobj->Justification."</li>\n".
				 "	<li><b>Paramètres : </b>".$curobj->Parametre."</li>\n";
				*/
			}
		}
		else
			$myObjList=array();
		
		if(isset($myObjList) && is_array($myObjList) && count($myObjList)>0)
		{
			$obj_item=0;
			echo "<ul class='detailProblems'>\n";
			foreach($myObjList as $curobj)
			{
				$obj_item++;
				echo "<li class='detailProblems'>\n";
				echo "<a href=\"#\" onclick=\"content_toggle('div_".$obj_item."')\">".$curobj->Nom_MDO." (".$curobj->Code_MDO.")</a>";
				echo "<ul class='listObjectifs' id='div_".$obj_item."' style=\"display:none;\">\n";
				echo $curobj->detail;
				echo "</ul>\n";
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
	}
	
	function getFormGpLink($section)
	{
		return FORMGP_FORM."?section=".$section."&zoneId=".$this->zoneId."&zoneCode=".$this->zoneCode."&zoneType=".$this->zoneType;
	}
	
	function showDetailContent()
	{
		//$zonesgeo=new zonesgeo($this->_db);
		//$zonesgeo->load($this->zoneId);
		//$this->showSelectHeader($zonesgeo);
		$nature_conversions=array("C"=>"Contractuelle","R"=>"Réglementaire","I"=>"Investissement");
		
		echo "<script type='text/javascript'>function content_toggle(divid){ myDiv=document.getElementById(divid); if(myDiv.style.display=='none') myDiv.style.display='block'; else myDiv.style.display='none'; }</script>";
		echo "<h3>Problèmes importants et mesures proposées</h3>\n";
		$myRel=new relation_zones_p1($this->_db);
		$myPb=$myRel->search_problems_for_zone($this->zoneCode);
		if($myPb!==false)
		{
			$div_counter=0;
			echo "<ul class='detailProblems'>\n";
			foreach($myPb as $pbkey=>$curpb)
			{
				$div_counter++;
				$myPbCode="Problème ".$pbkey." : ".($curpb[0]->Nom_Probleme);
				echo "<li class='detailProblems'>\n";
				echo "<a href=\"#\" onclick=\"content_toggle('div_".$div_counter."')\">".$myPbCode."</a>";
				echo "<ul class='detailProblemMesure' style='display:none'; id=\"div_".$div_counter."\">\n";
				foreach($curpb as $curmesure)
				{
					echo "<li class='detailProblemMesure'>\n";
					//echo "Mesure : ".Tools::Display($curmesure);
					echo "<h5>Mesure ".$curmesure->Mesure->Code_Mesure." : <b>".$curmesure->Mesure->Nom_Mesure."</b></h5>";
					echo "<blockquote>\n";
					echo "<p><b>Précisions : </b>".($curmesure->Mesure->Commentaires)."</p>\n";
					foreach($nature_conversions as $keynature=>$valnature)
						$curmesure->Mesure->Nature=str_replace($keynature,$valnature,$curmesure->Mesure->Nature);
					echo "<p><b>Nature : </b>".($curmesure->Mesure->Nature)."</p>\n";
					//echo "<b>Nature : </b>".($nature_conversions[$curmesure->Mesure->Nature])."<br>\n";
					echo "<p><b>Maitrise d'ouvrage : </b>".($curmesure->Mesure->Maitrise_Ouvrage)."</p>\n";
					echo "<p><b>Financement : </b>".($curmesure->Mesure->Financements)."</p>\n";
					echo "</blockquote>\n";
					/*
					$myComments=new commentaires($this->_db);
					$myListComments=$myComments->list_comments_for_relid($curmesure->ID);
					if($myListComments!==false)
					{
						echo "<p><b>Détails de la mesure :</b></p>";
						echo "<ul class='detailRemarquesMesure'>\n";
						foreach($myListComments as $curcomm)
						{
							echo "<li class='detailRemarquesMesure' style='margin-bottom:5px;'>";
								echo "<ul>\n";
								echo "<li><b>Libellé : </b>".utf8_decode($curcomm->Libelle)."</li>\n";
								echo "<li><b>Problème origine : </b>".utf8_decode($curcomm->Probleme_Origine)."</li>\n";
								echo "<li><b>Objectif SDAGE : </b>".utf8_decode($curcomm->Objectif_SDAGE_DCE)."</li>\n";
								echo "</ul>";
							echo "</li>";
						}
						echo "</ul>\n";
					}
					*/
					echo "</li>\n";
				}
				echo "</ul>\n";
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
		else
			echo "<h3>Aucun problème reporté !</h3>\n";

	}
	
    function showSearchContent()
    {
    	$search_description=$this->_search_description;
    	
    	if(trim($this->search_key)=="")
    	{
    		$this->redirect(INDEX_ACTION."?special_message=".urlencode("<p>Veuillez saisir un mot clef à rechercher</p>"));
    		return true;
    	}

    	if(strlen(trim($this->search_key))<2)
    	{
    		$this->redirect(INDEX_ACTION."?special_message=".urlencode("<p>Veuillez saisir un mot clef avec au minimum deux caractères</p>"));
    		return true;
    	}
    	
    	echo "<h3 class=\"searchRecap\"> <em>Résultat pour le critère de recherche : </em>".strtoupper($this->search_key)."</h3>\n";
    	
    	/**
    	 * Recherches géographiques
    	 */
    	 
    	$zonesgeo=new zonesgeo($this->_db);
    	$myDepartementsCode=$zonesgeo->search_department_code($this->search_key);
    	$myDepartementsName=$zonesgeo->search_department_name($this->search_key);
    	$resultat["departements"]=array();
    	$myDepartements=array();
    	if($myDepartementsCode!==false)
    		$resultat["departements"]=array_merge($myDepartementsCode,$resultat["departements"]);
    	if($myDepartementsName!==false)
    		$resultat["departements"]=array_merge($myDepartementsName,$resultat["departements"]);
    		
    	$resultat["regions"]=$zonesgeo->search_region($this->search_key);
    	$resultat["postcode"]=$zonesgeo->search_postcode($this->search_key);
    	$resultat["insee"]=$zonesgeo->search_insee($this->search_key);
    	$resultat["townname"]=$zonesgeo->search_town_name($this->search_key);
    	
    	/**
    	 * Recherches hydrographiques
    	 */
    	$mySearchKey="%".addslashes($this->search_key)."%";

    	$ssbv=new ssbv($this->_db);
    	$resultat["ssbv"]=$ssbv->search(" (Code_SSBV LIKE '".$mySearchKey."' OR Nom_SSBV LIKE '%".$mySearchKey."%')");
 
    	
    	$mdosout=new obj_mdo_sout_etat($this->_db);
    	$resultat["mdosout"]=$mdosout->search(" (Code_MDO LIKE '".$mySearchKey."' OR Nom_MDO LIKE '%".$mySearchKey."%')");
    	
    	$mdosup=new obj_mdo_sup($this->_db);
    	$resultat["mdosup"]=$mdosup->search(" (Code_MDO LIKE '".$mySearchKey."' OR Nom_MDO LIKE '%".$mySearchKey."%' OR Intitule LIKE '%".$mySearchKey."%')");
    	
   	
    	
    	$myNoResult=0;
    	$sum_result=0;
    	foreach($resultat as $curres)
    		if($curres===false || count($curres)<=0)
    		{
    			$myNoResult++;
    		}
    		else
    		{
		    	$sum_result+=count($curres);
    		}
    	if($sum_result==1)
    	{
    		foreach($resultat as $reskey=>$curres)
    			if($curres!==false && count($curres)>0)
    			{
    				$myZoneName="";
    				foreach($search_description[$reskey]["fields"] as $curfield)
    					$myZoneName.=$curres[0]->$curfield."";

    				$myCurCodeTxt="";
    				if($search_description[$reskey]["zone_code"]!="")
    				{
	    				$myZoneCodeField=$search_description[$reskey]["zone_code"];
	    				if(isset($curres[0]->$myZoneCodeField))
	    				{
	    					$myCurCode=$curres[0]->$myZoneCodeField;
	    					$myCurCodeTxt="&zoneCode=".$myCurCode;
	    				}
    				}

    				$this->redirect($search_description[$reskey]["select_action"]."?zoneId=".$curres[0]->ID.$myCurCodeTxt."&zoneType=".$reskey."&zoneName=".urlencode(trim($myZoneName)));
    				return true;
    			}
    	}
    	if($myNoResult==count($resultat))
    	{
    		$this->redirect(INDEX_ACTION."?special_message=".urlencode("<h3><center>Aucun résultat trouvé, veuillez reformuler votre recherche</center></h3>"));
    		return true;
    	}
    	echo "<ul>\n";
    	foreach($search_description as $cursearch_key=>$cursearch_value)
    	{
    		if($resultat[$cursearch_key]!==false && count($resultat[$cursearch_key])>0)
    		{
    			echo "<li>\n";
    			echo "Résultat pour ".$cursearch_value["label"];
    			echo "<ul>\n";
    			foreach($resultat[$cursearch_key] as $curvalue)
    			{
    				echo "<li>\n";
    				$myZoneName="";
    				foreach($search_description[$cursearch_key]["fields"] as $curfield)
    					$myZoneName.=($myZoneName!=""?"&nbsp;-&nbsp;":"").$curvalue->$curfield;
    				
    				$myCurCodeTxt="";
    				if($search_description[$cursearch_key]["zone_code"]!="")
    				{
	    				$myZoneCodeField=$search_description[$cursearch_key]["zone_code"];
	    				if(isset($curvalue->$myZoneCodeField))
	    				{
	    					$myCurCode=$curvalue->$myZoneCodeField;
	    					$myCurCodeTxt="&zoneCode=".$myCurCode;
	    				}
    				}
    				echo "<a href=\"".$search_description[$cursearch_key]["select_action"]."?zoneId=".$curvalue->ID.$myCurCodeTxt."&zoneType=".$cursearch_key."&zoneName=".urlencode(trim($myZoneName))."\">\n";
    				echo $myZoneName;
    				echo "</a>\n";
    				echo "</li>\n";
    			}
    			echo "</ul>\n";
    			echo "</li>\n";
    		}
    	}
    	echo "</ul>\n";
    }
    
    function showIndexContent()
    {
    	/*
			$this->_tpl->assign_block_vars('formfields',
					array(	'FIELD_NAME' => ($myFieldTitle),
							'FIELD_CONTENT' => $myFieldContent
						));
    	*/
    	if($this->_section=="search" || $this->_section=="select")
    		$this->special_message.="<br />Nouvelle Recherche&nbsp;:<br />";
    	if($this->special_message!="")
			$this->_tpl->assign_vars(array('SPECIAL_MESSAGE' => urldecode($this->special_message)));
		else
			$this->_tpl->assign_vars(array('SEARCH_RESULT' => ""));
		
		$this->_tpl->assign_vars(array('SEARCH_ACTION' => SEARCH_ACTION));
    	$this->_tpl->pparse("indexForm");
    }
}
?>