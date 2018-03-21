<?php

class mdosout_metier
{
	var $params=array();
	var $db;
	var $section="accueil";
	var $texte_recherche="";
	var $code_me="";
	var $caracteristriques_me="";
	var $template=null;
	var $template_filenames=array("accueil"=>"accueil.tpl","searchresult"=>"searchresult.tpl","fiche"=>"fiche.tpl");
	var $template_name="mdosout.template";
	var $path_pre="";
	var $formPage="";
	var $msg_error="";
	var $msg_info="";
	var $search_result=array();

    function mdosout_metier(&$database,&$path_pre,$thePage="index.php")
    {
    	$this->path_pre=$path_pre;
    	$this->formPage=basename($thePage);
		//die("Nom : ".basename($thePage));
		if(trim($this->formPage)=="") $this->formPage="index.php";
    	$this->db=$database;
		require_once($this->path_pre."/".$this->template_name.".php");
		$this->template = new MyTableTemplateMdoSout($this->template_name,$this->path_pre);
		$this->template->set_filenames($this->template_filenames);
		//echo "Objet template : ".Tools::Display($this->template);
    }

    function bind($theParams)
    {
    	if(!is_array($theParams) || count($theParams)<=0)
    		return false;
    	foreach($theParams as $key=>$value)
    	{
    		$this->params[$key]=$value;
    	}
    	return true;
    }

    function handle()
    {
    	if(isset($this->params["section"]) && $this->params["section"]!="")
    		$this->section=$this->params["section"];

    	switch($this->section)
    	{
    		case "search":
    			$this->handle_Search();
    			break;
    		case "fiche":
    			$this->handle_Fiche();
    			break;
    		case "zonedetail":
    			 $this->handle_ZoneDetail();
    			 break;
    	}
    }

    function handle_ZoneDetail()
    {
    	if(!isset($this->params['zoneFile']) || !isset($this->params["zoneCode"]) || $this->params["zoneCode"]=="" || $this->params["zoneFile"]=="")
    	   $this->zoneDetail=false;
    	$this->params["zoneCode"]=trim(addslashes($this->params["zoneCode"]));
    	$this->params["zoneFile"]=trim(addslashes($this->params["zoneFile"]));
    	$this->zoneDetail=new stdClass();
    	switch($this->params['zoneFile'])
    	{
    		case "Communes_RM":
    			$this->zoneDetail->titre="Informations sur la commune";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->zoneRequete="SELECT com_numero as Numero_INSEE,com_nom as Nom, departement_num_departement as Departement FROM lexique_commune WHERE com_numero='".$this->params["zoneCode"]."';";
    			break;
    		case "Departements_RM":
    			$this->zoneDetail->titre="Informations sur le département";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->entites_hydro=false;
    			$this->zoneDetail->zoneRequete="SELECT nom_departement as Nom, n__departement as Numero,n__region as Numero_Region, nom__region as Nom_Region FROM lexique_departements_rmc WHERE n__departement='".$this->params["zoneCode"]."';";
                break;
    		case "MEPlandEau_RM":
    			$this->zoneDetail->titre="Informations sur le plan d'eau";
    			$this->zoneDetail->zoneRequete="SELECT code__pe as Code,nom__pe as Nom FROM lexique_me_plan_eau WHERE code__pe='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->secteurs=false;
     			$this->zoneDetail->entites_hydro=false;
   			break;
    		case "MERivieresPrincipales_RM":
    		case "MERivieresSecondaires_RM":
    			//nom1     nom_her     nom_typce   nom_ctx_pisci   influ_her   long    fmzone_nom
    			$this->zoneDetail->titre="Informations sur le cours d'eau";
    			$this->zoneDetail->zoneRequete="SELECT Code_MDO as Code,Nom_MDO as Nom , SSBV_Code_SSBV as Code_Sous_Bassin,Intitule Intitule_Sous_Bassin FROM lexique_obj_mdo_sup WHERE Code_MDO='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->entites_hydro=false;
    			break;
			case "EntiteHydroGeol":
				$myCodeME="";
				if(isset($this->params["zoneCode"]) && $this->params["zoneCode"]!="")
				{
					$myEntite=new entites_hydro($this->db);
					if($myEntite->loadCode($this->params["zoneCode"]))
						if(!is_null($myEntite->Code_me_eu) && $myEntite->Code_me_eu!="")
							$myCodeME=$myEntite->Code_me_eu;
				}
				if($myCodeME=="")
				{
					$this->zoneDetail->titre="Entité hydrogéologique";
					$this->zoneDetail->speciallink="Code : #CODE#";
					$this->zoneDetail->codeforlink="Code";
					$this->zoneDetail->zoneRequete="";
					$this->zoneDetail->secteurs=false;
					break;
				}
				$this->params["zoneCode"]=$myCodeME;
    		case "MEsoutAffleurante":
    		case "MEsoutProfondeur1":
    		case "MEsoutProfondeur2":
    		case "MEsoutSecteursAFFL":
    		case "MEsoutSecteursProfond":
    			$this->zoneDetail->titre="Masse d'eau souterraine";
    			$this->zoneDetail->speciallink="<a href=\"../db_mesout/index.php?section=fiche&txtRecherche=".$this->texte_recherche."&code_me=#CODE#\" target='_blank' >Accéder à la fiche de cette masse d'eau</a>";
    			$this->zoneDetail->codeforlink="Code";
    			$this->zoneDetail->zoneRequete="SELECT code_me as Code,code_me_euro as Code_Euro,libelle_me as Libelle FROM caracteristiques_me WHERE code_me_euro='".substr($this->params["zoneCode"],0,9)."';";
    			
				$this->zoneDetail->entites_hydro=new stdClass();
                $this->zoneDetail->entites_hydro->titre="Entités hydrogéologiques composant la masse d'eau";
                $this->zoneDetail->entites_hydro->entites_hydro_requete="SELECT code_entite as Codent,titre_fichier as Noment,type_fichier,nom_fichier FROM rel_entites_hydro_me_fichier  WHERE rel_entites_hydro_me_fichier.code_me_eu='".substr($this->params["zoneCode"],0,9)."' ORDER BY Codent ASC";
                //$this->zoneDetail->entites_hydro->entites_hydro_requete="SELECT Codent,Noment,type_fichier,nom_fichier FROM entites_hydro LEFT JOIN rel_entites_hydro_me_fichier ON (entites_hydro.Code_me_eu=rel_entites_hydro_me_fichier.code_me_eu) WHERE Code_me_eu='".substr($this->params["zoneCode"],0,9)."' ORDER BY Codent ASC";
				$this->zoneDetail->secteurs=new stdClass();
                $this->zoneDetail->secteurs->titre="Secteurs de cette masse d'eau";
                $this->zoneDetail->secteurs->secteursrequete="SELECT code_entite_v1,code_entite_v2,code_entite,type_fichier,nom_fichier FROM entites_hydrogeologiques_me LEFT JOIN rel_secteurme_fichier ON (code_entite_v1=code_entite OR code_entite_v2=code_entite) WHERE code_me_euro='".substr($this->params["zoneCode"],0,9)."' ORDER BY code_entite_v1 ASC,code_entite_v2 ASC";
    			break;
    		case "METransition_RM":
    			$this->zoneDetail->titre="Informations sur la masse d'eau de transition";
    			$this->zoneDetail->zoneRequete="SELECT Code_MDO as Code,Nom_MDO as Nom , SSBV_Code_SSBV as Code_Sous_Bassin,Intitule Intitule_Sous_Bassin FROM lexique_obj_mdo_sup WHERE Code_MDO='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->entites_hydro=false;
                $this->zoneDetail->secteurs=false;
    			break;
    		case "Regions_RM":
    			$this->zoneDetail->titre="Informations sur la région";
    			$this->zoneDetail->zoneRequete="SELECT num_region as Numero_Region, nom_region as Nom_Region FROM lexique_region WHERE nom_region='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->entites_hydro=false;
                $this->zoneDetail->secteurs=false;
    			break;
    		case "ssbv_RM":
    		case "ssbv2_RM":
    			$this->zoneDetail->titre="Informations sur le sous-bassin versant";
    			$this->zoneDetail->zoneRequete="SELECT Code_SSBV,Nom_SSBV FROM lexique_ssbv WHERE Code_SSBV='".$this->params["zoneCode"]."';";
    			$this->zoneDetail->secteurs=false;
    			$this->zoneDetail->entites_hydro=false;
    			break;
    		case "RhoneSaone":
    		default:
    			$this->zoneDetail=false;
    			break;
    	}

    	if($this->zoneDetail!==false)
    	{
    		$this->zoneDetail->informations=false;
    		if(isset($this->zoneDetail->zoneRequete) && $this->zoneDetail->zoneRequete!="")
    		{
    			$this->db->setQuery($this->zoneDetail->zoneRequete);
    			$myListeObjets=$this->db->loadObjectList();
    			$this->zoneDetail->informations=false;
    			if(is_array($myListeObjets) && count($myListeObjets)>0)
    			{
    				$this->zoneDetail->informations=get_object_vars($myListeObjets[0]);
    			}
    			if(isset($this->zoneDetail->entites_hydro) && is_object($this->zoneDetail->entites_hydro))
    			{
    				if(isset($this->zoneDetail->entites_hydro->entites_hydro_requete) && $this->zoneDetail->entites_hydro->entites_hydro_requete!="")
    				{
    					$this->db->setQuery($this->zoneDetail->entites_hydro->entites_hydro_requete);
    					$myListeEntites=$this->db->loadObjectList();
    					//echo "Résultat : ".Tools::Display($this->db->getQuery());
    					if(is_array($myListeEntites) && count($myListeEntites)>0)
    					{
                            foreach($myListeEntites as $curentite)
                            {
                            	if(!is_null($curentite->Codent) && $curentite->Codent!="")
                            	   $this->zoneDetail->entites_hydro->informations[$curentite->Codent][]=get_object_vars($curentite);
                            }
    					}
    				}
    			}
    		}
    	}
    }

    function handle_Fiche()
    {
    	if(isset($this->params["txtRecherche"]) && $this->params["txtRecherche"]!="")
    		$this->texte_recherche= $this->params["txtRecherche"];
    	if(isset($this->params["code_me"]) && $this->params["code_me"]!="")
    		$this->code_me= $this->params["code_me"];

		return $this->chargeME($this->code_me);

    }

    function chargeME($theCodeME)
    {
    	if(trim($theCodeME)=="")
    		return false;

		$this->code_me=addslashes($theCodeME);

    	$this->caracteristriques_me=array();
    	$myQueryChargeME="SELECT * FROM caracteristiques_me as cme WHERE cme.code_me='".$this->code_me."';";
    	$this->db->setQuery($myQueryChargeME);
    	$myListeME=$this->db->loadObjectList();
    	//echo "Liste me : ".Tools::Display($myQueryChargeME);
    	if(is_array($myListeME) && count($myListeME)==1)
    	{
    		$this->caracteristriques_me=get_object_vars($myListeME[0]);

    		/*
    		 * Chargement départements de la ME
    		 */
    		/*
    		$myQueryDepartement="SELECT ldrmc.* FROM lexique_departements_rmc as ldrmc,departements_me as dme WHERE dme.code_me='".$this->code_me."' AND ldrmc.n__departement=dme.n__departement;";
    		$this->db->setQuery($myQueryDepartement);
    		$myListeDep=$this->db->loadObjectList();
    		$this->caracteristriques_me["liste_departements"]=array();
    		if(is_array($myListeDep) && count($myListeDep)>0)
    		{
    			foreach($myListeDep as $key=>$val)
    			{
    				$this->caracteristriques_me["liste_departements"][]=get_object_vars($val);
    			}
    		}
    		 */

    		$this->caracteristriques_me["liste_departements"]=$this->getObjArrayFromQuery(
    			"SELECT ldrmc.* FROM lexique_departements_rmc as ldrmc,departements_me as dme WHERE dme.code_me='".$this->code_me."' AND ldrmc.n__departement=dme.n__departement;"
    			);

    		/*
    		 * Chargement entités de la ME
    		 */
    		/*
    		$myQueryEntites="SELECT * FROM entites_hydrogeologiques_me as ehme WHERE ehme.code_me='".$this->code_me."';";
    		$this->db->setQuery($myQueryEntites);
    		$myListeEntites=$this->db->loadObjectList();
    		$this->caracteristriques_me["liste_entites"]=array();
    		if(is_array($myListeEntites) && count($myListeEntites)>0)
    		{
    			foreach($myListeEntites as $key=>$val)
    			{
    				$this->caracteristriques_me["liste_entites"][]=get_object_vars($val);
    			}
    		}
    		 */
    		$this->caracteristriques_me["liste_entites"]=$this->getObjArrayFromQuery(
    			"SELECT * FROM entites_hydrogeologiques_me as ehme WHERE ehme.code_me='".$this->code_me."' ORDER BY code_entite_v1 ASC, code_entite_v2 ASC;"
    			);
    		/*
    		 * Chargement mdo sup en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_mdosup"]=$this->getObjArrayFromQuery(
		    		"SELECT mss.code_me_euro, lmsrmc.* FROM me_sup_sout as mss,lexique_me_sup_rmc as lmsrmc WHERE lmsrmc.id_mdo=mss.id_mdo AND mss.code_me='".$this->code_me."' AND mss.id_mdo IS NOT NULL ;"
    		 	);
    		/*
    		 * Chargement plans d'eau sup en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_plandeau"]=$this->getObjArrayFromQuery(
		    		"SELECT mpes.code_me_euro, lmpea.* FROM me_plan_eau_sout as mpes,lexique_me_plan_eau as lmpea WHERE lmpea.code__pe=mpes.code__pe AND mpes.code_me='".$this->code_me."' AND mpes.code__pe IS NOT NULL ;"
    		 	);
    		/*
    		 * Chargement occupation_sols en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_occupationsols"]=$this->getObjArrayFromQuery(
		    		"SELECT * FROM occupation_sols WHERE occupation_sols.code__pe='".$this->code_me."';"
    		 	);

    		/*
    		 * Chargement occupation_sols en lien avec la mdo sout
    		 */
    		 $this->caracteristriques_me["liste_volumespreleves"]=$this->getObjArrayFromQuery(
		    		"SELECT SUM( `volume_preleve` ) AS volume_total, `synthese_prelevement_mdo`.*
						FROM `synthese_prelevement_mdo`
						WHERE mdo_id ='".$this->code_me."'
						GROUP BY `code_regroupement_usage`;"
    		 	);
    	}

    }

    function getObjArrayFromQuery($theQuery)
    {
    	if(trim($theQuery)=="")
    		return array();
 		$this->db->setQuery($theQuery);
		$myListe=$this->db->loadObjectList();
		$myData=array();
		if(is_array($myListe) && count($myListe)>0)
		{
			foreach($myListe as $key=>$val)
			{
				$myData[]=get_object_vars($val);
			}
		}
    	return $myData;
    }

    function handle_Search()
    {
    	if(isset($this->params["txtRecherche"]) && $this->params["txtRecherche"]!="")
    		$this->texte_recherche= $this->params["txtRecherche"];

		if(!in_array($this->params["ssorder"],array("ASC","DESC")))
			$this->params["ssorder"]="ASC";
		if(!in_array($this->params["ssfield"],array("n__departement","libelle_me","code_me","nom__region")))
			$this->params["ssfield"]="code_me";
		$mySQLOrder=" ORDER BY ".$this->params["ssfield"]." ".$this->params["ssorder"];

		if($this->texte_recherche!="")
    	{
	    	$myQuery="	SELECT COUNT(*) as nboccme, cme.*,ldrmc.n__departement , ldrmc.nom_departement,ldrmc.nom__region 
						FROM
							caracteristiques_me as cme,
							entites_hydrogeologiques_me as ehme ,
							departements_me as dme,
							lexique_departements_rmc as ldrmc
						WHERE
							cme.code_me=dme.code_me AND
							ldrmc.n__departement=dme.n__departement AND
							cme.code_me=ehme.code_me AND
							(
								CAST(cme.code_me AS CHAR) LIKE '%".addslashes($this->texte_recherche)."%' OR
								cme.libelle_me LIKE '%".addslashes($this->texte_recherche)."%' OR
								ldrmc.n__departement LIKE '".addslashes($this->texte_recherche)."' OR
								ldrmc.nom_departement LIKE '%".addslashes($this->texte_recherche)."%' OR
								ldrmc.nom__region LIKE '%".addslashes($this->texte_recherche)
								/* ." OR code_entite_v1='".addslashes($this->texte_recherche)."' OR
								code_entite_v2='".addslashes($this->texte_recherche)."' */ ."%'
							)
						GROUP BY cme.code_me ".$mySQLOrder /* ORDER BY cme.code_me ASC */ ."
						";
    	}
    	else
    	{
	    	$myQuery="	SELECT COUNT(*) as nboccme, cme.*
						FROM
							caracteristiques_me as cme,
							entites_hydrogeologiques_me as ehme ,
							departements_me as dme,
							lexique_departements_rmc as ldrmc
						WHERE
							cme.code_me=dme.code_me AND
							ldrmc.n__departement=dme.n__departement AND
							cme.code_me=ehme.code_me
						GROUP BY cme.code_me ORDER BY cme.code_me ASC
						";

    	}
    	$this->db->setQuery($myQuery);
    	$this->search_result=$this->db->loadObjectList();

    	if(!is_array($this->search_result) || count($this->search_result)<=0)
    		$this->msg_info="Votre recherche n'a fourni aucun résultat";
    }


    function sectionContent()
    {
    	$myContent="";
    	switch($this->section)
    	{
    		case "accueil":
    		default:
    			$myContent=$this->sectionContent_Accueil();
    			break;
    		case "search":
    			$myContent=$this->sectionContent_Search();
    			break;
    		case "fiche":
    			$myContent=$this->sectionContent_Fiche();
    			break;
    		case "zonedetail":
    			$myContent=$this->sectionContent_ZoneDetail();
    			break;
    	}
    	return $myContent;
    }

    function sectionContent_ZoneDetail()
    {
        if($this->zoneDetail===false)
        {
        	return "Aucune information n'est disponible pour cette zone";
        }

    	$myContent="";
    	if(isset($this->zoneDetail->titre) && $this->zoneDetail->titre!="")
    	   $myContent.="<h3>".$this->zoneDetail->titre."</h3>\r\n";
        if(isset($this->zoneDetail->informations) && $this->zoneDetail->informations!==false && is_array($this->zoneDetail->informations) && count($this->zoneDetail->informations)>0)
        {
        	//$myContent.=Tools::Display($this->zoneDetail->secteurs);
        	$myContent.="<ul>";
        	foreach($this->zoneDetail->informations as $curinfokey=>$curinfovalue)
        	{
        		$myContent.="<li><strong>".str_replace("_","&nbsp;",$curinfokey)."&nbsp;:</strong>&nbsp;".$curinfovalue."</li>";
        	}
        	$myContent.="</ul>";
        	if(isset($this->zoneDetail->speciallink)
        		&& isset($this->zoneDetail->codeforlink)
        		&& isset($this->zoneDetail->informations[$this->zoneDetail->codeforlink]))
        		{
					$myContent.="<p>".str_replace("#CODE#",$this->zoneDetail->informations[$this->zoneDetail->codeforlink],$this->zoneDetail->speciallink)."</p>";
        			//$myContent.=(isset($this->zoneDetail->secteurs->informations) && count($this->zoneDetail->secteurs->informations)>0)?"Il y a des entités":"Il n'y pas d'entités";
        		}
        }
        if(isset($this->zoneDetail->entites_hydro->informations) && count($this->zoneDetail->entites_hydro->informations)>0)
        {
        	if(isset($this->zoneDetail->entites_hydro->titre) && trim($this->zoneDetail->entites_hydro->titre)!="")
        	{
        		$myContent.="<h3>".$this->zoneDetail->entites_hydro->titre."</h3>\r\n";

	        	$myContent.="<ul>";
        		foreach($this->zoneDetail->entites_hydro->informations as $keysecteur=>$valsecteur)
        		{
        			$myContentSecteur="";
        			$myContent.="<li>"."<strong>". /* $keysecteur."&nbsp;-&nbsp;".*/ $valsecteur[0]["Noment"]."</strong>";
        			if(is_array($valsecteur) && count($valsecteur)>0)
        			{
        				$myContentSecteur.="<ul>";
        				foreach($valsecteur as $cursecteur)
        				{
        					if($cursecteur["Codent"]=="")
        					{
        					   $myContent.="&nbsp;:&nbsp;Pas de fichier\r\n";
        					   $myContentSecteur="";
        					}
        					else
							{
								$myContentSecteur.="<li><span style='font-weight:bold;text-transform:capitalize;'>".$cursecteur["type_fichier"]."</span> : ";
								if(file_exists($path_pre."docs/".strtolower($cursecteur["type_fichier"])."/".$cursecteur["nom_fichier"]))
									$myContentSecteur.="<a  target='_blank' href='docs/".$cursecteur["type_fichier"]."/".$cursecteur["nom_fichier"]."'>";
								$myContentSecteur.=$cursecteur["nom_fichier"];
								if(file_exists($path_pre."docs/".strtolower($cursecteur["type_fichier"])."/".$cursecteur["nom_fichier"]))
									$myContentSecteur.="</a>";
								$myContentSecteur.="</li>";
							}
        				}
        				if($myContentSecteur!="")
        				    $myContentSecteur.="</ul>";
        			}
        			$myContent.=$myContentSecteur."</li>";
        		}
	        	$myContent.="</ul>";
        	}
        }
        return $myContent;
    }

    function sectionContent_Search()
    {
    	$this->mapDataToTemplate();
    	$this->template->assign_vars(array("FORM_PAGE"=>$this->formPage));

    	if(!is_array($this->search_result) || count($this->search_result)<=0)
    	{
    		return $this->template->pparse("accueil",true);
    	}
    	else
    	{
    		foreach($this->search_result as $curme)
    		{
    			$this->template->assign_block_vars
	            (
	                'tablecontent',
	                array
	                (
	                	'code_me' => $curme->code_me,
	                	'libelle_me' => $curme->libelle_me,
						'n__departement' => $curme->n__departement,
	                	'nom__region' => $curme->nom__region,
	                	'texte_recherche' => urlencode($this->texte_recherche)
	                )
	            );
    			//echo "Code : ".$curme->code_me.", Libellé : ".$curme->libelle_me.", Secteur : ".$curme->secteur_be_caracterisation.BR;
    		}
    		//echo Tools::Display($this->search_result);
    		return $this->template->pparse("searchresult",true);
    	}
    }
    function sectionContent_Accueil()
    {
    	$this->mapDataToTemplate();

    	$this->template->assign_vars(array("FORM_PAGE"=>$this->formPage));
        return $this->template->pparse("accueil",true);
    }
    function sectionContent_Fiche()
    {

    	$this->mapDataToTemplate();
    	$this->template->assign_vars(array("FORM_PAGE"=>$this->formPage));
    	foreach($this->caracteristriques_me as $curelement=>$curvalue)
		{
			if(is_array($curvalue) && count($curvalue)>0 && substr($curelement,0,6)=="liste_")
			{
				//echo "Assignation à ".$curelement." de ".Tools::Display($curvalue);
				foreach($curvalue as $curitem)
					$this->template->assign_block_vars
		            (
		                $curelement,$curitem
		            );
			}
			//echo "Code : ".$curme->code_me.", Libellé : ".$curme->libelle_me.", Secteur : ".$curme->secteur_be_caracterisation.BR;
		}
		return $this->template->pparse("fiche",true);
    }

    function mapDataToTemplate()
    {
		$this->template->assign_vars
        (
            array
            (
                'code_me' => $this->code_me,
                'texte_recherche' => $this->texte_recherche
            )
        );
		if(is_array($this->search_result) && count($this->search_result)==1)
		{
			$myObjVars=get_object_vars($this->search_result[0]);
			foreach($myObjVars as $key=>$val)
				if(is_array($val) || is_object($val))
					unset($myObjVars[$key]);
				else
					$myObjVars[$key]=str_replace("\n","<br />\n",$myObjVars[$key]);

			$this->template->assign_vars
	        (
	            $myObjVars
	        );

		}

		if(is_array($this->caracteristriques_me) && count($this->caracteristriques_me)>0)
		{
			$myObjVars=array();
			foreach($this->caracteristriques_me as $key=>$val)
				if(!is_array($val) && !is_object($val))
					$myObjVars[$key]=str_replace("\n","<br />\n",$val);

			///echo "Map de ".Tools::Display($myObjVars);
			$this->template->assign_vars
	        (
	            $myObjVars
	        );

		}
    }
}
?>