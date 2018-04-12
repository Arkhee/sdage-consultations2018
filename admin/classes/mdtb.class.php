<?php
// Mise en place du template
require_once($path_pre."lib/html_template.php");
if(!isset($template_name))
	$template_name="default";

$template_filenames=array(
	    'header' => 'header.tpl',
	    'footer' => 'footer.tpl');


class mdtb_table extends mosDBTable
{
	var $ID=null;
	var $table_name="";
	var $name="";
	var $target="";
	var $mode="self";
	var $sortfield_key="";
	var $sortfield_order="";
	var $searchable_large=true;

	var $_tablename="mdtb_tables";
	var $_actions=array();
	var $_global_actions=array();
	var $_mainmenu=false;
	var $_firstrecord=true;
	var $_recordset;
	var $_sql_filters=array();
	var $_filters=array();
	var $_filtercombos=array();
	var $_fields;
	var $_fields_address;
	var $_template;
	var $_template_name;
	var $_template_sections;
	var $_template_path="";
	var $_key="";
	var $_views;
	var $_script="";
	var $_files_path="";
	var $_images_path="images/";
	var $_defaulttable=false;
	var $_sessionvars=array("sortorder","sortfield","curpage","viewname");
	var $_is_child=false;
	var $_curpath;
	var $_messages=array();
	var $_auth=null;
	var $_currange="";

	var $_path_pre;
	var $_user_ID;
	var $_user_group;
	var $_prefs=null;

	public static function InitObject($class)
	{
		global $database,$template_name,$path_abs,$auth;
		$obj = new $class($database,$template_name,basename(__FILE__),$path_abs,true);
		if(!is_null($auth) && is_object($auth)) $obj->set_auth($auth);
		return $obj;
	}
	
	
	function init_begin()
	{
		if(!isset($this->_defaultparams)) $this->_defaultparams=new stdClass();
		$this->_defaultparams->section="list";
		$this->_defaultparams->action="";
		$this->_defaultparams->itemid="";
		$this->_defaultparams->viewname="default";
		$this->_defaultparams->itemlist="";
		$this->_defaultparams->curpage=1;
		$this->_defaultparams->filter="";
		$this->_defaultparams->sortorder="";
		$this->_defaultparams->sortfield="";
		$this->_defaultparams->redir="";
		$this->_defaultparams->viewname="default";
		$this->_defaultparams->specialaction="";
		$this->_defaultparams->display="";

		$this->_template_sections=array("menu"=>"menu","form"=>"form","ajaxsearchlist"=>"ajaxsearchlist","list"=>"list","detail"=>"detail");
	}

	function __construct(&$database,$template_name,$script_name,$curpath="",$data_access_only=false)
	{
		if(!is_object($database))
			die("Erreur critique");

		{
			global $path_pre;
			if(!$this->hasAuth())
				@session_start();

			$this->_db=$database;

			// Affectation du nom du script appelé
			$this->_script=$script_name;
			// Initialisation des templates
			$this->_template=$template_name;
			$this->_template_name=$template_name;
			$this->_curpath=$curpath;
			$this->_path_pre=$path_pre;
		}

		if(!$this->hasAuth())
			@session_start();

		if($data_access_only)
			$this->init();
	}

	function init()
	{
		// Fonction d'Initialisation
		$this->init_begin();
		// Initialisation des champs, fonction à adapter dans les classes enfant
		$this->specific_init();
		$this->check_default_params();
		if($this->hasAuth())
		{
			if(!$this->_auth->isAdmin())
			{
				$myRights=$this->authGetRights();
				$myAuthSQLFilter="";
				if($myRights!==false)
				{
					$myAuthSQLFilter=$myRights->getSQLFilter();
				}
				if($myAuthSQLFilter!="")
				{
					$this->add_sql_filter("rightsfilter",$myAuthSQLFilter);
				}
			}
		}

		// Initialisation de la classe parente pour enregistrer directement les paramètres de la table
		//echo "Clef : ".$this->_key."<br>\n";
		$this->mosDBTable( $this->_tablename, $this->_key, $this->_db );
		//echo "tbl key : ".$this->_tbl_key."<br>\n";
		$this->_recordset=new mdtb_recordset($this->_db,$this->table_name,$this->_fields,$this->_key,$this->_files_path);
		$this->_recordset->_mdtb=&$this;
		$this->_recordset->set_nb_elements_per_page($this->nbperpage);

		//echo "Recordset : ".print_r($this->_recordset,true);
		// Appels en fin d'initialisation
		$this->_template_init($this->_template,$this->_curpath);
		$this->init_end();
		//echo "Récap vues : <pre>".print_r($this->_views,true)."</pre>\n";
	}


	function set_auth($auth)
	{
		if(is_object($auth))
			$this->_auth=$auth;
		else
			$this->_auth=null;

		if($this->isAuth())
		{
			if(isset($this->_auth->user_ID))
				$this->_user_ID=$this->_auth->user_ID;
			else
				$this->_user_ID=null;
			if(isset($this->_auth->group_ID))
			$this->_user_group=$this->_auth->group_ID;
			else
				$this->_user_group=null;
		}
	}

	function set_main_menu($arraymenu)
	{
		if(is_array($arraymenu) && count($arraymenu)>0)
			$this->_mainmenu=$arraymenu;
		else
			$this->_mainmenu=false;
	}

	function isAuth()
	{
		if(is_null($this->_auth)) return true;
		return $this->_auth->isLoaded();
	}

	function authGetRights()
	{
		if(!$this->hasAuth()) return false;
		if(!$this->isAuth()) return false;
		return $this->_auth->getRights($this->table_name);
	}

	function authCanRead()
	{
		if(!$this->hasAuth()) return true;
		if(!$this->isAuth()) return false;
		$rights=$this->authGetRights();
		if($rights!==false)
			return $rights->canRead();
 		if($this->_auth->isAdmin()) return true;
		return false;
	}

	function authCanWrite()
	{
		if(!$this->hasAuth()) return true;
		if(!$this->isAuth()) return false;
		$rights=$this->authGetRights();
		if($rights!==false)
			return $rights->canWrite();
		if($this->_auth->isAdmin()) return true;
		return false;
	}

	function authError()
	{
		return "<div id=\"msg_rights_error\">".Tools::Translate("Vous n'avez pas les droits pour cette action")."</div>";
	}

	function authCanDelete()
	{
		if(!$this->hasAuth()) return true;
		if(!$this->isAuth()) return false;
		$rights=$this->authGetRights();
		if($rights!==false)
			return $rights->canDelete();
		if($this->_auth->isAdmin()) return true;
		return false;
	}

	function authCanAdmin()
	{
		if(!$this->hasAuth()) return true;
		if(!$this->isAuth()) return false;
		$rights=$this->authGetRights();
		if($rights!==false)
			$rights->canAdmin();
		if($this->_auth->isAdmin()) return true;
		return false;
	}

	function hasAuth()
	{
		if(!is_null($this->_auth) && is_object($this->_auth)) return true;
		return false;
	}

	function getAuthLogout()
	{
		if(!$this->hasAuth()) return false;
		return $this->_auth->getLogoutUrl($this->_script);
	}

	function AuthLogin()
	{
		if(is_null($this->_auth)) return true;
		return $this->_auth->getLoginForm($this->_script);
	}

	function addMessage($theMessage)
	{
		if(!is_array($this->_messages))
			$this->_messages=array();
		$this->_messages[]=$theMessage;
	}

	function hasMessages()
	{
		if(isset($_SESSION["_messages"]) && is_array($_SESSION["_messages"]) && count($_SESSION["_messages"])>0)
		{
			return true;
		}
		if(!is_array($this->_messages))
			$this->_messages=array();
		if(count($this->_messages)>0)
		{
			return true;
		}
		return false;
	}


	function addMessagesToSession()
	{
		if($this->hasMessages())
		{
			$_SESSION["_messages"]=$this->_messages;
		}
	}
	

	function getMessages()
	{
		if(!is_array($this->_messages))
		{
			$this->_messages=array();
		}
		if(isset($_SESSION["_messages"]) && is_array($_SESSION["_messages"]) && count($_SESSION["_messages"]))
		{
			$this->_messages=  array_merge($this->_messages,$_SESSION["_messages"]);
			unset($_SESSION["_messages"]);
		}
		$myConcatMsg="";
		if(count($this->_messages)>0)
		{
			foreach($this->_messages as $curmsg)
				$myConcatMsg.=(($myConcatMsg!="")?"<br>\n":"").$curmsg;
		}
		return $myConcatMsg;
	}

	function clearMessages()
	{
		$this->_messages=array();
		return true;
	}


	// Modif YB du 26/10/2011 - Ajout d'une fonction générique d'extraction au format CSV
	function CSVExtract_link($action)
	{
		return $this->_get_global_action_link($action);
	}

	public function CSVExtract()
	{
		$this->_db->setQuery("SELECT * FROM ".$this->table_name);
		$myList=$this->_db->loadObjectList();
		if(is_array($myList) && count($myList)>0)
		{
			$myArrCuritem=get_object_vars( $myList[0] );
			$myArrResultats=array();
			$myColsList=array();
			foreach($myList as $curitem)
			{
				$curline = array();
				foreach($myArrCuritem as $key=>$val)
				{
					$curline[$key]=$curitem->$key;
					if(!in_array($key,$myColsList))
						$myColsList[]=$key;
				}
				$myArrResultats[]=$curline;
			}
			
			$myContent="";
			foreach($myArrResultats as $curitem)
			{
				if($myContent=="")
				{
					$myLine="";
					foreach($myColsList as $itemval)
					{
						$itemval=html_entity_decode($itemval);
						$itemval=str_replace(";",":",$itemval);
						$itemval=str_replace("\n","\r",$itemval);
						$myLine.=($myLine!=""?";":"").$itemval;
					}
					$myContent.=$myLine."\r\n";
				}
				$myLine="";
				foreach($myColsList as $itemval)
				{
					if(!isset($curitem[$itemval])) $curitem[$itemval]=0;
					
					$curitem[$itemval]=html_entity_decode($curitem[$itemval]);
					$curitem[$itemval]=str_replace(";",":",$curitem[$itemval]);
					$curitem[$itemval]=str_replace("\n","\r",$curitem[$itemval]);
					
					$myLine.=($myLine!=""?";":"").str_replace("\n"," ",str_replace("\r","",$curitem[$itemval]));
				}
				$myContent.=$myLine."\r\n";
			}
			$tablename=str_replace($this->_db->_table_prefix,"",$this->table_name);
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			header ("Content-type: application/octet-stream");
			header ("Content-disposition: inline; filename=\"".str_replace("#__",$this->_db->_table_prefix,$tablename).".csv\"");
			header ("Content-Length: " .strlen($myContent));
			die($myContent);
			
		}
	}


	function _get_combo_filter_href()
	{
		$href="";
		if(isset($this->_filtercombos) && is_array($this->_filtercombos) && count($this->_filtercombos)>0)
		{
			foreach($this->_filtercombos as $curcombo)
			{
				$combovar="filtercmb_".$curcombo["name"];
				if(isset($this->_params->$combovar) && $this->_params->$combovar!="")
				{
					$href.="&".$combovar."=".$this->_params->$combovar;
				}
			}
		}
		return $href;
	}

	function check_default_params()
	{
		if(isset($this->_filtercombos) && is_array($this->_filtercombos) && count($this->_filtercombos)>0)
		{
			foreach($this->_filtercombos as $curcombo)
			{
				$combovar="filtercmb_".$curcombo["name"];
				//echo "Définition propriété : ".$combovar."<br>\n";
				$this->_defaultparams->$combovar="";
			}
		}

	}

	function specific_init()
	{

	}

	function init_end()
	{
		// Initialisation des variables correspondant aux vues des tables
		$this->_views_init();
		// Calcul des ordres d'apparence des champs
		$this->_views_compute();
	}

	function _views_init()
	{
		$myViewsList=$this->_template_sections; //array("form","detail","list");
		foreach($myViewsList as $curview => $curtemplate)
		{
			if(!isset($this->_views[$curview]["default"])) $this->_views[$curview]["default"]=new stdClass();
			$this->_views[$curview]["default"]->key=array();
			$this->_views[$curview]["default"]->pos=array();
		}
	}

	function set_images_path($theDir)
	{
		$this->_images_path=$theDir;
	}


	function set_upload_dir($theDir)
	{
		$this->_files_path=$theDir;
	}

	function set_default()
	{
		$this->_defaulttable=true;
	}

	function set_curview($theView)
	{
		$this->_params->viewname=$theView;
	}

	function get_curview()
	{
		return $this->_params->viewname;
	}

	function ischild()
	{
		return $this->_is_child;
	}

	function is_default_table()
	{
		return $this->_defaulttable;
	}

	function _views_compute()
	{
		if(!is_array($this->_fields) || count($this->_fields)<0)
			return false;
		$myViewsList=$this->_template_sections; //array("form","detail","list");
		//echo "Fields : ".Tools::Display($this->_fields);
		foreach($this->_fields as $key=>$curfield)
			$this->_fields_address[$curfield->field_name]=$key;

		foreach($myViewsList as $cv=>$tpl)
		{
			$keyviewname="default";
			//foreach($this->_views[$cv] as $keyviewname=>$valviewname)
			{
				$myPos=array();
				$myPosField="view_".$cv;
				foreach($this->_fields as $key=>$curfield)
				{
					if(isset($curfield->$myPosField) && $curfield->$myPosField>0)
						$myPos[$curfield->$myPosField][]=$key;
					else
						$this->_views[$cv][$keyviewname]->hidden[]=$key;
				}
				ksort($myPos);
				$index=1;

				foreach($myPos as $mySubPos)
					foreach($mySubPos as $myKey)
						$this->_views[$cv][$keyviewname]->pos[$index++]=$myKey;

				foreach($this->_views[$cv][$keyviewname]->pos as $pos=>$key)
					$this->_views[$cv][$keyviewname]->key[$key]=$pos;
			}

		}
	}

	function add_view($section,$name,$pos)
	{
		$i=0;
		foreach($this->_fields as $keyfield=>$curfield)
		{
			if(isset($pos[$curfield->field_name]))
			{
				$this->_views[$section][$name]->pos[++$i]=$keyfield;
				$this->_views[$section][$name]->key[$keyfield]=$i;
			}
			else
			{
				$this->_views[$section][$name]->hidden[]=$keyfield;
			}
		}
	}

	function add_global_action($action_name,$action_label)
	{
		//echo "Ajout action, vérification de la méthode ".$action_name." ...<br>\n";
		if($action_name!="" && method_exists($this,$action_name) && method_exists($this,$action_name."_link"))
		{
			if(!isset($this->_global_actions[$action_name]))
			{
				$this->_global_actions[$action_name]=array(
												"name"=>$action_name,
												"label"=>$action_label);
			}
		}
	}

	function add_event_handler($theEvent,$theAction)
	{
		if(!is_array($this->_events[$theEvent]))
			$this->_events[$theEvent]=array();
		if(!in_array($theEvent,$this->_events[$theEvent]))
			$this->_events[$theEvent][]=$theAction;
	}

	function add_action($action_name,$action_label,$list_view=1,$detail_view=1,$form_view=1,$icon="",$target="")
	{
		//echo "Ajout action, vérification de la méthode ".$action_name." ...<br>\n";
		if($action_name!="" && method_exists($this,$action_name))
		{
			if(!isset($this->_actions[$action_name]))
			{
				$this->_actions[$action_name]=array(
												"name"=>$action_name,
												"label"=>$action_label,
												"list_view"=>$list_view,
												"detail_view"=>$detail_view,
												"form_view"=>$form_view,
												"icon"=>$icon,
												"target"=>$target);
			}
		}
	}

	function set_action_target($action_name,$action_target)
	{
		//echo "Définition de l'action ".$action_name." à ".$action_target."<br>\n";
		if(isset($this->_actions[$action_name]) && $action_target!="")
		{
			//echo "Action trouvée ...<br>\n";
			$this->_actions[$action_name]["target"]=$action_target;
		}
	}

	function field_init()
	{

	}

	function set_param($theParamName,$theParamValue)
	{
		if($theParamName!="")
		{
			$this->_params->$theParamName=$theParamValue;
		}
	}

	function set_key($theKey)
	{
		$this->_key=$theKey;
	}


	function hasMenu()
	{
		if(!$this->isAuth()) return false;
		if(isset($this->_params->display) && $this->_params->display=="nomenu")
			return false;
		return true;
	}

	function showMenu()
	{
		global $ThePrefs;
		
		if(!$this->isAuth()) return false;
		
		$section=isset($this->_params->section)?$this->_params->section:"";
		// Affichage du handle "menu"
		$myLinkLogout="";
		$myLinkForm="";
		$myLinkList="";
		$myViewsList="";
		$myViewsMenu="";
		$myLinksMenu="";
		$myForm="";

		if($this->hasMenu())
		{
			switch($section)
			{
				case "form":
					$myActiveMenuForm="id=\"menu_on\"";
					$myActiveMenuListe="";
					break;
				case "list":
					$myActiveMenuListe="id=\"menu_on\"";
					$myActiveMenuForm="";
					break;
				default:
					$myActiveMenuListe="";
					$myActiveMenuForm="";
					break;
			}

			if(!$this->ischild())
			{
				//echo "Filtre : ".$this->_params->filter."<br>\n";
				if($this->hasAuth())
				{
					$myLinkLogout="<a class=\"stylebutton\" href=\"".$this->getAuthLogout()."\" >".Tools::Translate("Déconnexion")."</a>\n"."&nbsp;";
				}
				$myLinkForm="<a class=\"stylebutton\" href=\"".$this->get_href("form")."\" ".$myActiveMenuForm.">".Tools::Translate("Ajouter")."</a>\n"."&nbsp;";
				$myLinkList="<a class=\"stylebutton\" href=\"".$this->get_href("list")."\" ".$myActiveMenuListe.">".Tools::Translate("Lister")."</a>\n"."&nbsp;";

				$myViewsList="";
				if(is_array($this->_views["list"]) && count($this->_views["list"])>1)
				{
					$myCurrentView=$this->_params->viewname;
					foreach($this->_views["list"] as $key => $val)
					{
						$this->_params->viewname=$key;
						$myViewsList.="<a href=\"".$this->get_href("list")."\">".Tools::Translate($key)."</a>&nbsp;";
					}
					$myViewsList=Tools::Translate("Choisir une vue")."&nbsp;:&nbsp;".($myViewsList);
					$this->_params->viewname=$myCurrentView;
				}
			}
			else
			{
				$myLinkList="<a class=\"stylebutton\" href=\"".$this->get_href("list")."\" ".$myActiveMenuListe.">".Tools::Translate("Lister")."</a>\n"."&nbsp;";
				$myLinkForm="<a href=\"".$this->get_href("form")."\" ".$myActiveMenuForm.">".Tools::Translate("Ajouter")."</a>\n"."&nbsp;";
			}

			$myLinksMenu="";

			$myLinksMenu  .= "<ul class=\"menu_items_list\">";
			if($this->ischild())
			{
				$myLinksMenu .= "<li class=\"menu_item\">".Tools::Translate($this->name)."&nbsp;-&nbsp;"."</li>";
			}
			if($myLinkList!="")
				$myLinksMenu .= "<li class=\"menu_item\">".$myLinkList."</li>";
			if($myLinkForm!="")
				$myLinksMenu .= "<li class=\"menu_item\">".$myLinkForm."</li>";
			$myCurFileName="";
			$myPathInfo=pathinfo($_SERVER["SCRIPT_NAME"]);
			//echo Tools::Display($myPathInfo);
			$myCurFileName=$myPathInfo["basename"];
			if($this->ischild())
				$myLinksMenu .= "<li class=\"menu_item\"><a href=\"ajaxsearchlist.php?class_name=".get_class($this)."&parent_item=".$this->_reference->key_value."&parent_file=".$myCurFileName."&parent_table=".urlencode($this->_reference->table)."&height=450&width=600\" class=\"thickbox\" title=\"".Tools::Translate("Associer une nouvelle entrée")."\">".Tools::Translate("Associer une nouvelle entrée")."</a></li>";
			if(isset($this->_global_actions) && count($this->_global_actions)>0)
			{
				foreach($this->_global_actions as $curaction)
				{
					$myLinkGlobalAction=$curaction["name"]."_link";
					if(method_exists($this,$curaction["name"]) && method_exists($this,$myLinkGlobalAction))
					{
						$myLinkResult=$this->$myLinkGlobalAction($curaction);
						if($myLinkResult!="")
							$myLinksMenu .= "<li class=\"menu_item\">".$myLinkResult."</li>";
					}
				}
			}
			if($myLinkLogout!="")
				$myLinksMenu .= "<li class=\"menu_item\">".$myLinkLogout."</li>";
			$myLinksMenu .= "</ul>";

			$myViewsMenu=$myViewsList;

			$myPrefixTable=$this->ischild()?"child_":"";
			$myForm = mdtb_forms::beginform("filterform",$this->_script);
			//echo "Filtres : <pre>".print_r($this->_filters,true)."</pre><br>\n";
			if(isset($this->_filtercombos) && is_array($this->_filtercombos) && count($this->_filtercombos)>0)
			{
				$myForm.= "<strong>".Tools::Translate("Filtres")."&nbsp;:&nbsp;</strong>";
				foreach($this->_filtercombos as $curcombo)
				{
					$combovar="filtercmb_".$curcombo["name"];
					if(!isset($this->_params->$combovar))
					{
						echo " param ".$combovar." n'existe pas, définition<br>\n";
						$this->_params->$combovar="";
					}
					$myFilterValue="";
					if(isset($this->_filters[$curcombo["name"]]))
						$myFilterValue=$this->_filters[$curcombo["name"]];
					if($myFilterValue!="" && $this->_params->$combovar=="")
						$this->_params->$combovar=$myFilterValue;
					//echo "Valeur du filtre actuel sur ".$curcombo["name"]." : ".$myFilterValue."<br>\n";
					$myForm.= $curcombo["label"].":".
								mdtb_forms::comboarray(
									$myPrefixTable."filtercmb_".$curcombo["name"],
									$curcombo["list"],
									$this->_params->$combovar,
									"combo_list")
								."&nbsp;";
				}
				$myForm.= mdtb_forms::submit("cmdOk",Tools::Translate("OK"),"input_button");
			}
			$myForm.= "<br>\n<strong>".Tools::Translate("Rechercher")."&nbsp;:&nbsp;</strong>";
			$myForm.= mdtb_forms::text($myPrefixTable."filter",$this->_params->filter,"input_text");
			$myForm.= mdtb_forms::hidden($myPrefixTable."table",$this->table_name);
			$myForm.= mdtb_forms::hidden($myPrefixTable."section","list");
			$myForm.= mdtb_forms::hidden($myPrefixTable."curpage","1");

			if($this->ischild())
			{
				$myForm.= mdtb_forms::hidden("table",$this->_reference->table);
				$myForm.= mdtb_forms::hidden("itemid",$this->_reference->key_value);
				$myForm.= mdtb_forms::hidden("section","detail");
			}

			$myForm.= mdtb_forms::submit("cmdOk",Tools::Translate("OK"),"input_button");
			$myForm.= mdtb_forms::endform();

			$this->_template->destroy();

			$myMainMenu="";
			if(!$this->ischild() && $this->_mainmenu!==false)
			{
				$indexMenu=1;
				foreach($this->_mainmenu as $key=>$curmenu)
				{
					if($curmenu["type"]=="title")
					{
						if($indexMenu>1) $myMainMenu .="</ul>";
						$myMainMenu .= Tools::Translate($curmenu["label"]);
						if($indexMenu<count($this->_mainmenu)) $myMainMenu .="<ul class=\"menu_items_list\">";
					}
					else
					{
						if($indexMenu==1)
							$myMainMenu .= "<ul class=\"menu_items_list\">";
					}
					if($curmenu["type"]=="item")
						$myMainMenu .= "<li class=\"menu_item\"><a href=\"".$this->_path_pre.$curmenu["link"]."\">".Tools::Translate($curmenu["label"])."</a></li>";
					if($curmenu["type"]=="separator")
						$myMainMenu .= "</ul>".$curmenu["label"]."<ul class=\"menu_items_list\">";

					$indexMenu++;
				}
				$myMainMenu .= "</ul>\n";

				if($this->hasAuth())
				{
					$myLinkLogout="<a class=\"stylebutton\" href=\"".$this->getAuthLogout()."\" >".Tools::Translate("D&eacute;connexion")."</a>\n"."&nbsp;";
					$myMainMenu .= "<hr />\n";
					$myMainMenu .= "<ul class=\"menu_items_list\">";
					$myMainMenu .= "<li class=\"menu_item\">".$myLinkLogout."</li>";
					$myMainMenu .= "</ul>\n";
				}


				$this->_template->assign_block_vars
	            (
	                'mainmenu',
	                array
	                (
	                	'MAINMENU_MORE' => "",
	                	'MAINMENU_LINKS' => $myMainMenu
	                )
	            );

			}

			$this->_template->assign_vars(
			    array
			    (
			    	'ACTIVEMENU_LINKS' => $myLinksMenu,
			    	'ACTIVEMENU_VIEWS' => $myViewsMenu,
			    	'ACTIVEMENU_FILTER' => $myForm
			    )
			);
			$this->_template->pparse('menu');
		}

	}


	function showMainMenu()
	{
		if(!$this->isAuth()) return false;
		$section=$this->_params->section;
		// Affichage du handle "menu"
		$myLinkLogout="";
		$myLinkForm="";
		$myLinkList="";
		$myViewsList="";
		$myViewsMenu="";
		$myLinksMenu="";
		$myForm="";

		if($this->hasMenu())
		{
			$this->_template->destroy();

			$myMainMenu="";
			if(!$this->ischild() && $this->_mainmenu!==false)
			{
				foreach($this->_mainmenu as $key=>$curmenu)
					if($curmenu["type"]=="title")
						$myMainMenu .= "<h3>".Tools::Translate($curmenu["label"])."</h3>";

				$myMainMenu .= "<ul class=\"menu_items_list\">";
				foreach($this->_mainmenu as $key=>$curmenu)
				{
					if($curmenu["type"]=="item")
						$myMainMenu .= "<li class=\"menu_item\"><a href=\"".$this->_path_pre.$curmenu["link"]."\">".Tools::Translate($curmenu["label"])."</a></li>";
				}
				$myMainMenu .= "</ul>\n";
				$this->_template->assign_block_vars
	            (
	                'mainmenu',
	                array
	                (
	                	'MAINMENU_MORE' => "",
	                	'MAINMENU_LINKS' => $myMainMenu
	                )
	            );

			}

			$this->_template->assign_vars(
			    array
			    (
			    	'ACTIVEMENU_LINKS' => $myLinksMenu,
			    	'ACTIVEMENU_VIEWS' => $myViewsMenu,
			    	'ACTIVEMENU_FILTER' => $myForm
			    )
			);
			$this->_template->pparse('menu');
		}

	}

	function _template_init($template_name,$path="")
	{
		if($path=="")
			$path=dirname(__FILE__);
		foreach($this->_template_sections as $curkey=>$cursection)
			$template_filenames[$curkey]=$cursection.'.tpl';

		$myTemplateFile=$path."/".$template_name.".php";
		//echo "Inclusion de ".$myTemplateFile."<br>\n";
		require_once($myTemplateFile);
		$template = new MyTableTemplate($template_name,$path);
		$template->set_filenames($template_filenames);

		//$this->_template_path="templates/".$template_name."/";
		$this->_template_name=$template_name;
		$this->_template=$template;
	}

	function _bind($thePost)
	{
		if	(
				isset($thePost["table"]) && $thePost["table"]==$this->table_name ||
				isset($thePost["child_table"]) && $thePost["child_table"]==$this->table_name
			)
		{
			$this->_recordset->binddata($thePost);
		}
	}

	function _get_hidden_fields()
	{
		$myViewsList=$this->_template_sections;
		$cv=$this->_params->section;
		$view=$this->_params->viewname;
		if(isset($this->_template_sections[$cv]))
		{
			if(isset($this->_views[$cv][$view]))
				return $this->_views[$cv][$view]->hidden;
			else
				return $this->_views[$cv]["default"]->hidden;
		}
		return false;
	}
	function _get_current_view()
	{
		$myViewsList=$this->_template_sections;
		$cv=$this->_params->section;
		if($cv=="ajaxsearchlist" || $cv=="ajaxselectlist")
			$cv="list";
		$view=$this->_params->viewname;
		if(isset($this->_template_sections[$cv]))
		{
			if(isset($this->_views[$cv][$view]))
				return $this->_views[$cv][$view]->pos;
			else
				return $this->_views[$cv]["default"]->pos;
		}
		return false;
	}

	function _save_session_vars($theRange,$theTable,$theObject)
	{
		if(headers_sent()) return false;
		//echo __LINE__."<br>\n";
		$mySessArray=$this->_sessionvars;
		//echo __LINE__."<br>\n";
		//$theTable=str_replace("#","",$theTable);
		foreach($mySessArray as $curfield)
			if(is_object($theObject) && isset($theObject->$curfield))
			{
				if(!isset($_SESSION[$theRange]) || !is_array($_SESSION[$theRange] )) $_SESSION[$theRange]=array();
				if(!isset($_SESSION[$theRange][$theTable])) $_SESSION[$theRange][$theTable]=array();
				if(!isset($_SESSION[$theRange][$theTable][$curfield])) $_SESSION[$theRange][$theTable][$curfield]="";
				$_SESSION[$theRange][$theTable][$curfield]=$theObject->$curfield;
			}
		//echo __LINE__."<br>\n";
	}

	function _load_session_vars($theRange,$theTable,&$theObject)
	{
		$mySessArray=$this->_sessionvars;
		foreach($mySessArray as $curfield)
			if(isset($_SESSION[$theRange][$theTable][$curfield]))
			{
				//echo "LEcture de la variable de session : [$theRange][$theTable] -> $curfield = ".$_SESSION[$theRange][$theTable]->$curfield."<br>\n";
				$theObject->$curfield=$_SESSION[$theRange][$theTable][$curfield];
			}
	}

	function binddata($thePost,$theGet)
	{
		$debug=false;
		if($this->is_default_table())
		{
			if(!isset($this->_params)) $this->_params=new stdClass();
			$this->_params->table=$this->table_name;
		}
		if(isset($theGet["table"])) $this->_params->table=urldecode($theGet["table"]);
		if(isset($thePost["table"])) $this->_params->table=$thePost["table"];

		if($debug) echo __LINE__." => Table : ".$this->_params->table." vs ".$this->table_name."<br>\n";
		if($debug) echo __LINE__." => GET : <pre>".print_r($theGet,true)."</pre>\n";

		$this->_params->child_table="";
		if(isset($theGet["child_table"])) $this->_params->child_table=urldecode($theGet["child_table"]);
		if(isset($thePost["child_table"])) $this->_params->child_table=$thePost["child_table"];
		if($debug) echo __LINE__." => Table child : ".$this->_params->child_table." vs ".$this->table_name."<br>\n";

		$this->_params->child_classname="";
		if(isset($theGet["child_classname"])) $this->_params->child_classname=urldecode($theGet["child_classname"]);
		if(isset($thePost["child_classname"])) $this->_params->child_classname=$thePost["child_classname"];

		$this->_params->child_item="";
		if(isset($theGet["child_item"])) $this->_params->child_item=urldecode($theGet["child_item"]);
		if(isset($thePost["child_item"])) $this->_params->child_item=$thePost["child_item"];
		if($debug) echo __LINE__." => Item child : ".$this->_params->child_item."<br>\n";

		$this->_currange="";
		if($this->ischild())
		{
			//$this->_currange="child";
			//$this->_params=$this->_defaultparams;
			//$this->_load_session_vars("child",$this->table_name,$this->_params);
			$this->_load_session_vars("child",$this->table_name,$this->_defaultparams);
			//echo "is child : ".$this->table_name."<br>\n";
		}
		if(!isset($myChildParams)) $myChildParams=new stdClass();
		$myChildParams->avant=$this->_params;
		if(isset($this->_params->child_table) && $this->_params->child_table==$this->table_name)
		{
			$this->_load_session_vars("child",$this->table_name,$this->_defaultparams);
			if($debug) echo "Début de la boucle sur les clefs de l'objet<br>\n";
			$myParamsObjects=get_object_vars($this->_defaultparams);
			$this->_backurl=$this->_script;
			foreach($myParamsObjects as $key=>$value)
			{
				if($debug) echo __LINE__."==> "."Param child_".$key.", default : ".$this->_defaultparams->$key."<br>\n";
				switch($key)
				{
					case "table":
						if($debug) echo __LINE__."==> "."Param child_".$key." get : ".$theGet[$key].", post : ".$thePost[$key].", final : ".$this->_params->$key."<br>\n";
						break;
					default:
						$this->_params->$key=isset($theGet["child_".$key])?$theGet["child_".$key]:$this->_defaultparams->$key;
						$this->_params->$key=isset($thePost["child_".$key])?$thePost["child_".$key]:$this->_params->$key;
						if($debug) echo __LINE__."==> "."Param child_".$key." get : ".$theGet[$key].", post : ".$thePost[$key].", final : ".$this->_params->$key."<br>\n";
						break;
				}
			}
			$this->_currange="child";
			$this->_save_session_vars("child",$this->table_name,$this->_params);
			//echo "Sauvegarde des variables de session child pour ".$this->table_name."<br>\n";
		}
		$myChildParams->apres=$this->_params;

		if($debug) echo "Paramètres pour table ".$this->table_name." vs ".$this->_params->table."<br>\nAvant : ".print_r($myChildParams->avant,true)."<br>\nAprès : ".print_r($myChildParams->avant,true)."<br>\n";

		if(isset($this->_params->table) && $this->_params->table==$this->table_name)
		{
			$this->_load_session_vars("parent",$this->table_name,$this->_defaultparams);
			if($debug) echo "Début de la boucle sur les clefs de l'objet<br>\n";
			$myParamsObjects=get_object_vars($this->_defaultparams);
			$this->_backurl=$this->_script;

			foreach($myParamsObjects as $key=>$value)
			{
				if($debug) echo __LINE__."==> "."Param ".$key.", default : ".$this->_defaultparams->$key."<br>\n";
				switch($key)
				{
					case "table":
						if($debug) echo __LINE__."==> "."Param ".$key." get : ".$theGet[$key].", post : ".$thePost[$key].", final : ".$this->_params->$key."<br>\n";
						break;
					default:
						$this->_params->$key=isset($theGet[$key])?$theGet[$key]:$this->_defaultparams->$key;
						$this->_params->$key=isset($thePost[$key])?$thePost[$key]:$this->_params->$key;
						if($debug) @print( __LINE__."==> "."Param ".$key." get : ".$theGet[$key].", post : ".$thePost[$key].", final : ".$this->_params->$key."<br>\n");
						break;
				}
				$this->_backurl.=(($this->_backurl==$this->_script)?"?":"&amp;").$key."=".$this->_params->$key;
			}
			if($debug) echo __LINE__."==> "."URL de retour : ".$this->_backurl."<br>\n";
			$this->_currange="parent";
			if($debug) echo __LINE__."==> "."URL de retour : ".$this->_backurl."<br>\n";
			$this->_save_session_vars("parent",$this->table_name,$this->_params);
			if($debug) echo __LINE__."==> "."URL de retour : ".$this->_backurl."<br>\n";
			$this->_backurl=($this->_backurl);
			if($debug) echo __LINE__."==> "."URL de retour : ".$this->_backurl."<br>\n";

		}

		//echo "Tri de ".$this->table_name.": ".$this->_params->sortfield."/".$this->_params->sortorder."<br>\n";

		// Activation des filtres transmis en paramètre par les listes déroulantes
		$myRealParamsObjects=get_object_vars($this->_params);
		foreach($myRealParamsObjects as $key=>$value)
		{
			if(strstr($key,"filtercmb_")!==false && $value!="")
			{
				$key=str_replace("filtercmb_","",$key);
				//echo "Ajout du filter $key,$value<br>\n";
				$this->add_sql_filter($key,$this->table_name.".".$key." LIKE '".addslashes($value)."'");
				//$this->add_filter($key,$value);
			}
		}

	}

	function _launchevent($theEvent,$theId,$theDeleteReturn)
	{
		if(isset($this->_events[$theEvent]) && is_array($this->_events[$theEvent]) && count($this->_events[$theEvent])>0)
		{
			foreach($this->_events[$theEvent] as $curevent)
			{
				$userdefined=new userdefined($this);
				if(method_exists($userdefined,$curevent))
				{
					switch($theEvent)
					{
						case "after_delete":
								$userdefined->$curevent($theId,$theDeleteReturn);
							break;
						case "before_delete":
							$userdefined->$curevent($theId);
							break;
						default:
							$userdefined->$curevent();
							break;
					}
				}
			}
		}
	}

	function before_handle()
	{

	}

    function on_read()
    {
        return true;   
    }
    

	function before_save()
	{
		return true;
	}

	function after_save()
	{
		// Préparation des fichiers : nettoyage en cas de mise à jour du référentiel
		return true;
	}


	function before_update()
	{
	   return true;
	}

	function after_update()
	{
	   return true;
	}

	function before_create()
	{
	   return true;
	}

	function after_create()
	{
	   return true;
	}

	function before_load()
	{
	   return true;
	}

	function after_load()
	{
	   return true;
	}



    function error_after_save()
    {
		return true;
	}

	function before_delete($theId)
	{
		return true;
	}

	function after_delete($theId,$theDeleteReturn)
	{
		return true;
	}


	function handle($thePost,$theGet)
	{

		$debug=false;
		$this->before_handle();
		$this->_launchevent("before_handle",-1,true);
		if($debug) echo __LINE__." => Action ".(isset($this->_params->action)?$this->_params->action:"")."<br>\n";

		if(isset($this->_params->action))
			switch($this->_params->action)
			{
				case "download":
					if($this->_recordset->load($this->_params->itemid))
					{
						$myCurField=$this->_params->itemlist;
						//die("Téléchargement pour field (child : ".$this->ischild().") : ".$myCurField." : <pre>".print_r($this->_recordset,1)."</pre>");
						if($myCurField!="" && isset($this->_recordset->$myCurField))
						{
							$myFile=unserialize($this->_recordset->$myCurField);
							if(Tools::DL_DownloadProgressive($myFile->original,$this->_files_path.$myFile->ondisk)===false)
								die("Download error !!");
							else
								die();
						}
					}
					break;
				case "store":
					if($this->authCanWrite())
					{
						if(isset($this->_recordset))
						{
							if(isset($this->_reference->table) && isset($this->_reference->key_value))
								$this->_recordset->set_reference($this->_reference);

							if($debug) echo __LINE__." => Recordset : ".Tools::Display($this->_recordset);
							$this->_bind($thePost);
							if($debug) echo __LINE__."<br>\n";
							$myKeyValue=$this->_recordset->key_value();
							if($myKeyValue===false || intval($myKeyValue)<=0)
								$this->_firstrecord=true;
							else
								$this->_firstrecord=false;
							if(method_exists($this,"before_save"))
							{
								$this->before_save();
								$this->_launchevent("before_save",-1,true);
							}
							$this->_recordset->record_save();
							if(method_exists($this,"after_save"))
							{
								$this->after_save();
								$this->_launchevent("after_save",-1,true);
							}
							if($debug) echo __LINE__."Requête : ".$this->_recordset->_db->getQuery()."<br>\n";
							$this->_params->section="detail";
							if($debug) echo __LINE__."<br>\n";
							$myKey=$this->_recordset->_key;
							if($debug) echo __LINE__."<br>\n";
							$this->_params->itemid=$this->_recordset->$myKey;
							if($debug) echo __LINE__."<br>\n";
						}
						else
							if($debug) echo __LINE__." => Recordset non défini</pre>";
					}
					else
						$this->_message[]=Tools::Translate("Vous n'avez pas les droits d'&eacute;criture pour cet enregistrement !");
					break;
				case "delete":
					if($this->authCanDelete())
					{
						if($this->_params->itemid>0)
						{
							if(isset($this->_reference->table) && isset($this->_reference->key_value))
								$this->_recordset->set_reference($this->_reference);
							if(method_exists($this,"before_delete"))
							{
								$this->before_delete($this->_params->itemid);
								$this->_launchevent("before_delete",$this->_params->itemid,true);
							}
							$myDeleteReturn=$this->_recordset->delete($this->_params->itemid);
							if(method_exists($this,"after_delete"))
							{
								$this->after_delete($this->_params->itemid,$myDeleteReturn);
								$this->_launchevent("after_delete",$this->_params->itemid,$myDeleteReturn);
							}
						}
					}
					else
						$this->_message[]=Tools::Translate("Vous n'avez pas les droits d'effacement pour cet enregistrement !");
					break;
				case "delrelation":
					if($this->authCanWrite())
					{
						$p=$this->_params;
						if($p->itemid>0 && $p->child_classname!="" && $p->child_item>0 && class_exists($p->child_classname))
						{
							if(isset($this->_children[$p->child_classname]))
							{
								$c=$this->_children[$p->child_classname];
								$parent_key=$c['parent_key'];
								$child_key=$c['child_key'];
								if(method_exists($this,"before_delrelation"))
									$this->before_delrelation($this->_params->itemid);
								$this->_db->setQuery("DELETE FROM ".$c['child_rel']." WHERE ".$parent_key."='".$p->itemid."' AND ".$child_key."='".$p->child_item."';");
								$this->_db->query();
								if(method_exists($this,"after_delrelation"))
								{
									$this->after_delrelation($this->_params->itemid,$myDeleteReturn);
									$this->_launchevent("after_delrelation",$this->_params->itemid,$myDeleteReturn);
								}
							}
						}
					}

					break;
				case "addrelation":

					if($this->authCanWrite())
					{
						$p=$this->_params;
						if($p->itemid>0 && $p->child_classname!="" && $p->child_item>0 && class_exists($p->child_classname))
						{
							//die(__LINE__."=> p : ".Tools::Display($p)."c : ".Tools::Display($c));
							if(isset($this->_children[$p->child_classname]))
							{
								$c=$this->_children[$p->child_classname];
								if(method_exists($this,"before_addrelation"))
									$this->before_addrelation($this->_params->itemid);
					/*
					 * $this->_children["mdtb_a_import_description"]= array(	"child_type"=>"relative",
																		"child_rel"=>"rel_import_colonne_import_description",
																		"parent_key"=>"a_import_colonne_id_a_import_colonne",
																		"child_key"=>"a_import_description_id_a_import_description");

					 */
								$parent_key=$c['parent_key'];
								$child_key=$c['child_key'];
								$this->_db->setQuery("SELECT * FROM ".$c['child_rel']." WHERE ".$parent_key."='".$p->itemid."' AND ".$child_key."='".$p->child_item."';");
								$myList=$this->_db->loadObjectList();
								if(!is_array($myList) || count($myList)<=0)
								{
									$myObj=null;
									$myObj->$parent_key=$p->itemid;
									$myObj->$child_key=$p->child_item;
									$this->_db->insertObject($c['child_rel'],$myObj);
								}
								//$myDeleteReturn=$this->_recordset->delete($this->_params->itemid);
								//echo "Add relation : ".Tools::Display($this->_params);
								if(method_exists($this,"after_addrelation"))
								{
									$this->after_addrelation($this->_params->itemid,$myDeleteReturn);
									$this->_launchevent("after_addrelation",$this->_params->itemid,$myDeleteReturn);
								}
							}
						}
					}
					else
						$this->_message[]=Tools::Translate("Vous n'avez pas les droits d'effacement pour cet enregistrement !");
					break;
				case "special":
					if($this->_params->specialaction!="")
					{
						if($debug) echo __LINE__." => Action spéciale ".$this->_params->specialaction."<br>\n";
						$specialaction=$this->_params->specialaction;
						if(method_exists($this,$specialaction))
						{
							if($debug) echo __LINE__." => La méthode existe<br>\n";
							if($this->_params->itemid>0)
							{
								if($debug) echo __LINE__." => Id vaut ".$this->_params->itemid."<br>\n";
								if($this->_recordset->load($this->_params->itemid))
								{
									if($debug) echo __LINE__." => Action spéciale : ".$this->_params->specialaction." sur l'enregistrement : ".$this->_recordset->key_value()."<br>\n";
									$this->$specialaction($this->_recordset);
								}
							}
						}
					}
					break;
				case "globalspecial":
					$debug=false;
					if($this->_params->specialaction!="")
					{
						if($debug) echo __LINE__." => Action spéciale ".$this->_params->specialaction."<br>\n";
						$specialaction=$this->_params->specialaction;
						if(method_exists($this,$specialaction))
						{
							if($debug) echo __LINE__." => La méthode existe<br>\n";
							if($debug) echo __LINE__." => Action spéciale : ".$this->_params->specialaction." sur l'enregistrement : ".$this->_recordset->key_value()."<br>\n";
							$this->$specialaction($this->_recordset);
						}
					}
					break;
			}


		$this->after_handle();
		$this->_launchevent("after_handle",-1,true);
	}

	function after_handle()
	{
		if($this->_currange=="")
		{
			$this->_params=$this->_defaultparams;
			if($this->ischild())
				$this->_load_session_vars("child",$this->table_name,$this->_params);
			else
				$this->_load_session_vars("parent",$this->table_name,$this->_params);
		}

		//echo "Tri de ".$this->table_name.": ".$this->_params->sortfield."/".$this->_params->sortorder."<br>\n";
		if($this->ischild())
			unset($this->_children);
		$this->_init_children();
	}

	function hide_children($children)
	{
		$list=array();
		if(is_array($children))
			$list=$children;
		if(is_string($children))
			$list[]=$children;
		foreach($list as $child)
		{
			unset($this->_children[$child]);
		}
	}
	function _init_children()
	{
		if(isset($this->_params->section) && isset($this->_params->itemid))
			if($this->_params->section=="detail")
			{
				if($this->_params->itemid>0)
					if(isset($this->_children) && count($this->_children)>0)
					{
						foreach($this->_children as $child=>$childdef)
						{
							//echo __LINE__." => définition child : ".Tools::Display($childdef);
							if(class_exists($child))
							{
								$this->_childrenobjects[$child]=new $child($this->_db,$this->_template_name,$this->_script,$this->_curpath);
								$this->_childrenobjects[$child]->set_upload_dir($this->_files_path);
								if($childdef["child_type"]=="lexique")
								{
									$myParentKey=$childdef["parent_key"];
									//$this->_recordset=new mdtb_recordset($this->_db,$this->table_name,$this->_fields,$this->_key,$this->_files_path);
									$this->_recordset->load($this->_params->itemid);
									$myId=$this->_recordset->$myParentKey;
									$this->_childrenobjects[$child]->set_as_child($this->table_name,$myId,$childdef);
								}
								else
									$this->_childrenobjects[$child]->set_as_child($this->table_name,$this->_params->itemid,$childdef);
								//echo "Enfant initialisé !!<br><br><br>\n";
								if($this->hasAuth())
									$this->_childrenobjects[$child]->set_auth($this->_auth);
								$this->_childrenobjects[$child]->init();
								$this->_childrenobjects[$child]->binddata($_POST,$_GET);
								$this->_childrenobjects[$child]->handle($_POST,$_GET);
								//echo "Objet child : ".Tools::Display($this->_childrenobjects[$child]);
							}
						}
					}
			}
	}

	function redir($theRedir="")
	{
		$debug=false;
		if(isset($this->_params->error_msg) && $this->_params->error_msg!="")
			return false;
		$myRedir=$this->_params->redir;
		if($theRedir!="")
			$myRedir=$theRedir;
		if($myRedir==="referer" && $_SERVER["HTTP_REFERER"]!="")
		{
			$myRedir=$_SERVER["HTTP_REFERER"];
		}
		if($debug) echo __LINE__." => Redirection vers : ".$myRedir."<br>\n";
		$myBeginUrl=strtolower(substr($myRedir,0,7));
		if($debug) echo "Début url : ".$myBeginUrl."<br>\n";
		if($myBeginUrl!="http://" && $myBeginUrl!="https://")
			$myRedir="http://".$myRedir;
		if($debug) echo "Url finale : ".$myRedir."<br>\n";
		if($myRedir!="http://" && $myRedir!="https://")
		{
			if(!headers_sent())
			{
				if($this->hasMessages())
				{
					$this->addMessagesToSession();
				}
				if($debug) echo "Redir par headers<br>\n";
				header("Location: ".$myRedir);
				die();
			}
			else
			{
				if($debug) echo "Redir par script<br>\n";
				echo "<script type=\"text/javascript\">document.location.href='".$myRedir."';</script>\n";
				die();
			}

			return true;
		}
		return false;
	}

	function add_filter_combo($field_name,$filter_label,$filter_combo)
	{
		if($field_name!="" && is_array($filter_combo) && count($filter_combo)>0)
			$this->_filtercombos[$field_name]=array("name"=>$field_name,"label"=>$filter_label,"list"=>$filter_combo);
	}

	function add_sql_filter($name,$filter)
	{
		$this->_sql_filters[strval($name)]=strval($filter);
	}

	function clear_sql_filter($name)
	{
		if(isset($this->_sql_filters[strval($name)]))
			unset($this->_sql_filters[strval($name)]);
	}

	function add_filter($field_name,$filter_value)
	{
		$this->_filters[$field_name]=$filter_value;
	}

	function clear_filter($field_name)
	{
		if(isset($this->_filters[$field_name]))
			unset($this->_filters[$field_name]);
	}

	function _type_decompose($type)
	{
		$myObj=new stdClass();
		$myObj->type=$type;
		$myObj->subtype="";
		if(strpos($myObj->type,"_")!==false && strpos($myObj->type,"_")>0)
		{
			$myListTypes=explode("_",$myObj->type);
			switch($myListTypes[0])
			{
				case "reference":
					$myObj->type="reference";
					$myObj->subtype=$myListTypes[1];
					break;
			}
		}
		return $myObj;

	}

	function _field_type($type)
	{
		$myObj=$this->_type_decompose($type);
		return $myObj->type;
	}

	function _field_subtype($type)
	{
		$myObj=$this->_type_decompose($type);
		return $myObj->subtype;
	}


	// Modif YB : ajout d'une fonction pour cacher un champ
	function hide_field($field_name)
	{
		$fields=array();
		if(is_array($field_name))
			$fields=$field_name;
		if(is_string($field_name))
			$fields[]=$field_name;
		foreach($fields as $curfield)
		if(isset($this->_fields[$curfield]))
		{
			$this->_fields[$curfield]->view_list=0;
			$this->_fields[$curfield]->view_form=0;
			$this->_fields[$curfield]->view_detail=0;
		}
	}
	

	// Modif YB : ajout d'une fonction pour cacher un champ
	function remove_field($field_name)
	{
		$fields=array();
		if(is_array($field_name))
			$fields=$field_name;
		if(is_string($field_name))
			$fields[]=$field_name;
		foreach($fields as $curfield)
		if(isset($this->_fields[$curfield]))
		{
			unset($this->_fields[$curfield]);
		}
	}

	function add_field($field_name,$type="text",$ref_table="",$ref_key="",$ref_label="",$view_line=1,$view_form=1,$view_detail=1,$list=array(),$validation="")
	{
		$myObj=new mdtb_field($this->_db,$this->_path_pre,$this->_user_group,$this->_user_ID);
		$myObj->table_name=$this->table_name;
		$myObj->field_name=$field_name;
		$myObj->type=$this->_field_type($type);
		$myObj->subtype=$this->_field_subtype($type);
		$myObj->ref_table=$ref_table;
		$myObj->ref_key=$ref_key;
		$myObj->ref_label=$ref_label;
		$myObj->view_list=$view_line;
		$myObj->view_form=$view_form;
		$myObj->view_detail=$view_detail;
		$myObj->validation=$validation;
		$myObj->set_list($list);
		// Modif YB : ajout de la clef correspondant au champ
		$this->_fields[$field_name]=$myObj;
	}

	function edit_field($field_name,$field_property,$field_value)
	{
		if(isset($this->_fields[$field_name]))
		{
			if(isset($this->_fields[$field_name]->$field_property))
			{
				$this->_fields[$field_name]->$field_property=$field_value;
				return true;
			}
		}
		return false;
	}

	function _table_begin()
	{
		$myForm  = "";
		//$myForm .= "<table class=\"mdtb_form_table\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
 		$this->_template->assign_vars(array('TABLE_BEGIN' => $myForm));
		//return $myForm;
	}

	function _table_end()
	{
		$myForm  = "";
 		$this->_template->assign_vars(array('TABLE_END' => $myForm));
	}

	function _form_begin($target,$theId=null)
	{
		$myFieldPrefix="";
		if($this->ischild())
			$myFieldPrefix="child_";

		$myForm = mdtb_forms::beginform("mdtb_tables",$target);
		$myForm.= mdtb_forms::hidden($myFieldPrefix."table",$this->table_name);
		$myForm.= mdtb_forms::hidden($myFieldPrefix."action","store");

		if($this->ischild())
		{
			$myForm.= mdtb_forms::hidden("table",$this->_reference->table);
			$myForm.= mdtb_forms::hidden("itemid",$this->_reference->key_value);
			$myForm.= mdtb_forms::hidden("section","detail");
		}


		/*
		$myForm  = "";
		$myForm .= "<form method=\"post\" action=\"".$target."\"  enctype=\"multipart/form-data\" id=\"mdtb_tables\" name=\"mdtb_tables\">";
		$myForm .= "<input type=\"hidden\" name=\"table\" value=\"".$this->table_name."\" id=\"table\" >\n";
		$myForm .= "<input type=\"hidden\" name=\"action\" value=\"store\" id=\"action\" >\n";
		$myForm .= "\n";
		*/
		$this->_template->assign_vars(array('FORM_BEGIN' => $myForm));
	}

	function _form_end($theId=null)
	{
		$myForm = mdtb_forms::endform();
		/*
		$myForm  = "";
		$myForm .= "</form>\n";
		*/
		$myReturnURL = $this->get_return_href($theId);
		$myFormActions="";
		if($this->authCanWrite())
		{
			$myFormActions .= mdtb_forms::submit("_cmdValidate",Tools::Translate("Valider"),"input_button");
		}
		$myFormActions .= mdtb_forms::button("_cmdRetour",Tools::Translate("Retour"),"onclick=\"location.href='".$myReturnURL."'\" ","input_button");

		//$myFormActions="<input type=\"submit\" name=\"_cmdValidate\" value=\"".Tools::Translate("Valider")."\">";
		//$myFormActions.="&nbsp;<input type=\"button\" name=\"_cmdRetour\" onclick=\"location.href='".$myReturnURL."'\" value=\"".Tools::Translate("Retour")."\">";

		$mySpecialActions="";
		$mySpecialActionsLabel="";
		if(!is_null($theId) && $theId>0 && isset($this->_actions) && is_array($this->_actions) && count($this->_actions)>0)
		{
			foreach($this->_actions as $curaction)
			{
				if($curaction["form_view"]==1)
					$mySpecialActions.=$this->_get_action_link("form",$curaction,$this->_recordset);

			}
			$mySpecialActionsLabel=Tools::Translate("Actions sp&eacute;ciales");
		}

		$this->_template->assign_vars(array('ACTION_SPECIAL_LABEL' => $mySpecialActionsLabel));
		$this->_template->assign_vars(array('ACTION_SPECIAL' => $mySpecialActions));
		$this->_template->assign_vars(array('FORM_ACTIONS' => $myFormActions));
		$this->_template->assign_vars(array('FORM_END' => $myForm));
	}


	function get_return_href($theId=null)
	{
		$myReturnURL="";
		if($theId>0)
			$myReturnURL=$this->get_href("detail",$theId);
		if($theId===null || $theId<=0)
			$myReturnURL=$this->get_href("list");
		if(!isset($this->_params->back))
			$this->_params->back="";
		if($this->_params->back!="")
			$myReturnURL=$this->_params->back;
		return $myReturnURL;
	}

	function get_section()
	{
		if(!isset($this->_params->section))
			return "";
		return $this->_params->section;
	}

	function get_ajaxselectlist($theField)
	{
		global $classprefix;
		$myHref="";
		if(class_exists($classprefix.$theField->ref_table))
		{
			$myHref="ajaxselectlist.php?table=".urlencode($theField->ref_table).
					"&ref_key=".$theField->ref_key.
					"&ref_label=".$theField->ref_label.
					"&class_name=".$classprefix.$theField->ref_table.
					"&fielditem_hidden=".$theField->field_name.
					"&fielditem_label="."lbl_".$theField->field_name.
					"&height=450&width=600".
					"&filter=".urlencode($this->_params->filter).
					"&viewname=".$this->_params->viewname;
		}
		return $myHref;
	}

	function get_parent_href_detail($theUrl,$section,$theId=-1,$theField="",$theOrder="",$thePage=1,$theSkipComboFilters=false)
	{
		$myScript=$theUrl;
		$myFieldPrefix="";
		$myParentTable="";
		if(!$this->ischild())
		{
			return "";
		}
		$myView="&viewname=".$this->_params->viewname;
		switch($section)
		{
			case "detail":
			case "form":
				$myHref=$theUrl."?table=".urlencode($this->table_name)."&section=".$section."&itemid=".$theId.$myView;
				break;
		}
		return $myHref;
	}

	function get_href($section,$theId=-1,$theField="",$theOrder="",$thePage=1,$theSkipComboFilters=false)
	{
		$myFieldPrefix="";
		$myParentTable="";
		if($this->ischild())
		{
			$myFieldPrefix="child_";
			$myParentTable=$this->_parent_href;
		}
		$myView="&".$myFieldPrefix."viewname=".$this->_params->viewname;
		switch($section)
		{
			case "detail":
				$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&".$myFieldPrefix."section=".$section."&".$myFieldPrefix."itemid=".$theId.$myView.$myParentTable;
				break;
			case "form":
				$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&".$myFieldPrefix."section=".$section."&".$myFieldPrefix."itemid=".$theId.$myView.$myParentTable;
				break;
			case "delete":
				$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&".$myFieldPrefix."section=list&".$myFieldPrefix."action=delete&".$myFieldPrefix."itemid=".$theId.$myView.$myParentTable;
				break;
			case "delrelation":
				//$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&".$myFieldPrefix."section=list&".$myFieldPrefix."action=addrelation&parent_item=".$this->_params->parent_item."&parent_table=".($this->_params->parent_table)."&".$myFieldPrefix."itemid=".$theId.$myView.$myParentTable;
				//tpl_a_import_description.php?table=%23__a_import_description&section=detail&itemid=1&viewname=default
				$myHref=$this->_script."?table=".urlencode($this->_reference->table)."&itemid=".$this->_reference->key_value."&section=detail&action=delrelation&child_item=".$theId."&child_classname=".get_class($this)."&child_table=".urlencode($this->table_name);
				break;
			case "addrelation":
				//$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&".$myFieldPrefix."section=list&".$myFieldPrefix."action=addrelation&parent_item=".$this->_params->parent_item."&parent_table=".($this->_params->parent_table)."&".$myFieldPrefix."itemid=".$theId.$myView.$myParentTable;
				//tpl_a_import_description.php?table=%23__a_import_description&section=detail&itemid=1&viewname=default
				$myHref=$this->_params->parent_file."?table=".urlencode($this->_params->parent_table)."&itemid=".$this->_params->parent_item."&section=detail&action=addrelation&child_item=".$theId."&child_classname=".get_class($this)."&child_table=".urlencode($this->table_name);
				break;
			case "list":
				//echo "Field : ".$theField.", order : ".$theOrder."<br>\n";
				if($theField=="")
					$theField=$this->_params->sortfield;
				if($theOrder=="")
					$theOrder=$this->_params->sortorder;
				if(intval($thePage)<=0)
					$thePage=1;

				if($theSkipComboFilters)
					$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&".$myFieldPrefix."section=".$section."&".$myFieldPrefix."sortfield=".$theField."&".$myFieldPrefix."sortorder=".$theOrder."&".$myFieldPrefix."curpage=".$thePage."&".$myFieldPrefix."filter=".urlencode($this->_params->filter).$myView.$myParentTable;
				else
					$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&".$myFieldPrefix."section=".$section."&".$myFieldPrefix."sortfield=".$theField."&".$myFieldPrefix."sortorder=".$theOrder."&".$myFieldPrefix."curpage=".$thePage."&".$myFieldPrefix."filter=".urlencode($this->_params->filter).$this->_get_combo_filter_href().$myView.$myParentTable;
				break;
			case "ajaxsearchlist":
				//echo "Field : ".$theField.", order : ".$theOrder."<br>\n";
				if($theField=="")
					$theField=$this->_params->sortfield;
				if($theOrder=="")
					$theOrder=$this->_params->sortorder;
				if(intval($thePage)<=0)
					$thePage=1;

				if($theSkipComboFilters)
					$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&class_name=".get_class($this)."&parent_item=".$this->_params->parent_item."&parent_file=".$this->_params->parent_file."&parent_table=".urlencode($this->_params->parent_table)."&height=450&width=600&".$myFieldPrefix."section=".$section."&".$myFieldPrefix."sortfield=".$theField."&".$myFieldPrefix."sortorder=".$theOrder."&".$myFieldPrefix."curpage=".$thePage."&".$myFieldPrefix."filter=".urlencode($this->_params->filter).$myView.$myParentTable;
				else
					$myHref=$this->_script."?".$myFieldPrefix."table=".urlencode($this->table_name)."&class_name=".get_class($this)."&parent_item=".$this->_params->parent_item."&parent_file=".$this->_params->parent_file."&parent_table=".urlencode($this->_params->parent_table)."&height=450&width=600&".$myFieldPrefix."section=".$section."&".$myFieldPrefix."sortfield=".$theField."&".$myFieldPrefix."sortorder=".$theOrder."&".$myFieldPrefix."curpage=".$thePage."&".$myFieldPrefix."filter=".urlencode($this->_params->filter).$this->_get_combo_filter_href().$myView.$myParentTable;
				break;
		}
		return $myHref;
	}

/*
 *
 * Fonctions de sorties HTML
 * Raccourcis pour faciliter la manipulation des données
 *
 */

	public function htmlGetCombo($theName,$theKey,$theVal,$theSQLSearch,$theCurValue="",$theSetDefText=true,$theDefText="",$theClass="",$theParams="")
	{
		$myComboList=array();
		$myFieldsList=$this->recGetFieldsList();
		//echo "Liste des valeurs : ".Tools::Display($myComboList);
		if(in_array($theKey,$myFieldsList) && in_array($theVal,$myFieldsList))
		{
			if($theSetDefText)
				$myComboList[]=array("id"=>"","value"=>$theDefText);
			$this->recSQLSearch($theSQLSearch);
			if($this->recFirst())
			{
				do
				{
					$myComboList[]=array("id"=>$this->recGetValue($theKey),"value"=>$this->recGetValue($theVal));
				} while($this->recNext());
			}
			//echo "Liste des valeurs : ".Tools::Display($myComboList);
			return mdtb_forms::combolist($theName,$myComboList,$theCurValue,$theClass,$theParams);
		}
		return false;
	}
	
	public function htmlGetComboMultiple($theName,$theKey,$theVal,$theSQLSearch,$theValues=array(),$theSetDefText=true,$theDefText="",$theClass="",$theParams="")
	{
		$myComboList=array();
		$myFieldsList=$this->recGetFieldsList();
		//echo "Liste des valeurs : ".Tools::Display($myComboList);
		$arrVals=explode(",",$theVal);
		if(in_array($theKey,$myFieldsList) /* && in_array($theVal,$myFieldsList) */)
		{
			if($theSetDefText)
				$myComboList[]=array("id"=>"","value"=>$theDefText);
			$this->recSQLSearch($theSQLSearch);
			if($this->recFirst())
			{
				do
				{
					$lblValue="";
					foreach($arrVals as $curVal)
					{
						if(in_array($curVal,$myFieldsList))
						{
							$lblValue.=($lblValue!=""?" - ":"").$this->recGetValue($curVal);
						}
					}
					if($lblValue!="") $myComboList[]=array("id"=>$this->recGetValue($theKey),"value"=>$lblValue);
				} while($this->recNext());
			}
			//echo "Liste des valeurs : ".Tools::Display($myComboList);
			return mdtb_forms::combolistmultiple($theName,$myComboList,$theValues,$theClass,$theParams);
		}
		return false;
	}
//===============================
//===============================
//===============================

	function showMainContent($theSection="")
	{
		if(!$this->isAuth())
		{
			echo $this->AuthLogin();
			return false;
		}
		if(!$this->authCanRead())
		{
			echo $this->authError();
			return false;
		}

		$section=$this->_params->section;
		if($theSection!="") $section=$theSection;
		//die(__FUNCTION__."@".__LINE__." section=".$section.", item : ".$this->_params->itemid."<br>\n");
		//die(__LINE__." => Objet : ".Tools::Display($this));
		if(isset($this->_template_sections[$section]))
		{

			//die(__FUNCTION__."@".__LINE__." section=".$section.", item : ".$this->_params->itemid."<br>\n");
			switch($section)
			{
				case "detail":
					$this->showDetail($this->_params->itemid);
					break;
				case "form":
					$this->showForm($this->_script,$this->_params->itemid);
					break;
				case "list":
					$this->showList();
					break;
				case "ajaxsearchlist":
					$this->showAjaxSearchList();
					break;
				case "ajaxselectlist":
					$this->showAjaxSelectList();
					break;
			}

		}
	}

	function set_as_child($ref_table,$ref_key_value,$child_definition)
	{
		$this->_is_child=true;
		$this->_reference->table=$ref_table;
		$this->_reference->key_value=$ref_key_value;
		$this->_reference->child_key=$child_definition["child_key"];
		$this->_reference->parent_key=$child_definition["parent_key"];
		if(isset($child_definition["child_type"]) && $child_definition["child_type"]=="relative"
			&& isset($child_definition["child_rel"]) && $child_definition["child_rel"]!="")
		{
			$this->_reference->child_rel=$child_definition["child_rel"];
			$this->_reference->child_type=$child_definition["child_type"];
		}
		else
			$this->_reference->child_type="direct";
		$this->_parent_href="&table=".urlencode($ref_table)."&itemid=".$ref_key_value."&section=detail";
	}

	function showHeader()
	{
		//$this->_template->destroy();
		$this->_template->assign_vars(array('TITLE' => $this->_template->getHtml($this->name)));
		$this->_template->pparse('header');
	}

	function showFooter()
	{
		if(!$this->isAuth()) return false;
		$this->_template->pparse('footer');
	}

	function showDetail($theId)
	{
		if(!$this->authCanRead())
		{
			echo $this->authError();
			return false;
		}
		$this->_template->destroy();
		$this->_setDetail($theId);
		echo "<div class=\"detail_tables\">\n";
		if($this->_recordset->isloaded() && isset($this->_children) && count($this->_children)>0)
		{
			$myBoolOneChildIsDetail=false;
			foreach($this->_children as $child=>$childdef)
				if(class_exists($child))
				{
					if(($this->_childrenobjects[$child]->get_section()!="list"))
						$myBoolOneChildIsDetail=true;
				}
			//if(!$myBoolOneChildIsDetail)
				$this->_template->pparse("detail");

			foreach($this->_children as $child=>$childdef)
				if(class_exists($child))
				{
					echo "<div class=\"child_table\">\n";
					if(!$myBoolOneChildIsDetail)
					{
						$this->_childrenobjects[$child]->showMenu();
						echo "<br class=\"eolbr\"/>\n";
						$this->_childrenobjects[$child]->showMainContent();
					}
					else
					{
						if($this->_childrenobjects[$child]->get_section()!="list")
						{
							$this->_childrenobjects[$child]->showMenu();
							echo "<br class=\"eolbr\"/>\n";
							$this->_childrenobjects[$child]->showMainContent();
						}
					}
					echo "</div info=\"child_tables\">\n";
				}
		}
		else
			$this->_template->pparse("detail");
		echo "</div info=\"detail_tables\">\n";
	}

	function showForm($theScript="",$theId=null)
	{
		if(!$this->authCanWrite())
		{
			echo $this->authError();
			return false;
		}

		if($theScript=="")
			$theScript=$this->_script;
		$this->_template->destroy();
		$this->_setForm($theScript,$theId);
		$this->_template->pparse("form");
	}

	function showList()
	{
		if(!$this->authCanRead())
		{
			echo $this->authError();
			return false;
		}
		$this->_template->destroy();

		$this->_setList();
		$this->_template->pparse("list");
	}

	function showAjaxSearchList()
	{
		if(!$this->authCanRead())
		{
			echo $this->authError();
			return false;
		}

		$this->_template->destroy();
		$this->_setAjaxSearchList();
		$this->_template->pparse("ajaxsearchlist");
	}


	function showAjaxSelectList()
	{

		if(!$this->authCanRead())
		{
			echo $this->authError();
			return false;
		}

		$this->_template->destroy();
		$this->_setAjaxSelectList();
		$this->_template->pparse("ajaxselectlist");
	}

	function _set_recordset_filters()
	{
		$debug=false;
		if(isset($this->_reference->table) && isset($this->_reference->key_value))
		{
			$this->_recordset->set_reference($this->_reference);
		}
		$this->_recordset->filter_clearall();

		foreach($this->_filters as $filter_key=>$filter_value)
		{
			foreach($this->_fields as $curfield)
			{
				if($curfield->field_name==$filter_key)
				{
					$this->_recordset->filter_set($filter_key,$filter_value,false);
				}
			}
		}

		if(isset($this->_sql_filters) && is_array($this->_sql_filters) && count($this->_sql_filters)>0)
		{
			foreach($this->_sql_filters as $curfiltername=>$cursqlfilter)
			{
				$this->_recordset->sql_filter_clear($curfiltername);
				$this->_recordset->sql_filter_set($curfiltername,$cursqlfilter);
			}
		}

		//echo "Filtres du recordset : ".print_r($this->_recordset->_filters,true)."<br>\n";
		//$debug=true;
		if($debug) echo __LINE__." searchable ? => ".$this->_params->filter."<br>\n ";
		if(isset($this->searchable) && is_array($this->searchable) && count($this->searchable)>0)
		{
			if($debug) echo __LINE__." oui ... filtre sur les champs ".print_r($this->searchable,true)." avec les valeurs : ".$this->_params->filter."<br>\n ";
			foreach($this->searchable as $myCurField)
			{
				if($debug) echo __LINE__." filtre sur ".$myCurField."<br>\n";
				$this->_recordset->filter_set($myCurField,$this->_params->filter,$this->searchable_large);
			}
		}
		else
			return false;
	}

	function showShortList()
	{
		$this->_template->destroy();
		$this->_setList();
		if($this->_recordset->count() >0)
			$this->_template->pparse("shortlist");

	}

	function prepareView()
	{
		$this->_loadRecordset();
	}

	function viewCount()
	{
		return $this->_get_recordset_count();
	}

	function _loadRecordset()
	{
		$this->_set_recordset_filters();
		//echo "Tri : ".$this->_params->sortfield."/".$this->_params->sortorder."<br>\n";
		$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,$this->_params->curpage);

	}

	function _get_recordset_count()
	{
		return $this->_recordset->count();
	}

	function _setAjaxSelectList()
	{
		if(count($this->_fields)>0)
		{
			//echo __FUNCTION__."@".__LINE__." : ".print_r($this->_fields,true)."<br>\n";
			$this->_template->assign_vars(array( "LABEL_ACTIONS"=>Tools::Translate("Actions")));

			$this->_set_recordset_filters();
			//echo "Tri : ".$this->_params->sortfield."/".$this->_params->sortorder."<br>\n";
			$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,$this->_params->curpage);
			//echo __LINE__." => Query : ".$this->_recordset->_db->getQuery();
			$this->_table_begin();
			$myView=$this->_get_current_view();
			//echo "Liste des champs : ".Tools::Display($myView)."<br>\n";
			// Définition de l'entête
			foreach($myView as $pos=>$key)
			{


				$myCurField=$this->_fields[$key];
				$myCurSort=$this->_params->sortorder;
				$myLinkClass=" class=\"mdtb_header_link thickbox\" ";
				if($myCurField->field_name==$this->_params->sortfield)
				{
					$myCurSort=(($myCurSort=="ASC")?"DESC":"ASC");
					$myLinkClass=" class=\"mdtb_header_link mdtb_header_current thickbox\" ";
				}
				$myCurHeaderLink="<a href=\"".$this->get_href("ajaxsearchlist",-1,$myCurField->field_name,$myCurSort,$this->_params->curpage)."\" ".$myLinkClass.">".Tools::Translate($myCurField->field_name)."</a>";

				$this->_template->assign_block_vars('tableheader',
								array(	'HEADER_CELL' => $myCurHeaderLink ));
			}

			// Définition du pied de tableau
			$curpage=$this->_params->curpage;
			$sortfield=$this->_params->sortfield;
			$sortorder=$this->_params->sortorder;
			$myTotalNbPages=$this->_recordset->get_page_count();
			$myLinkPagePrev=$this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,$curpage-1);
			$myLinkPageNext=$this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,$curpage+1);
			//echo "Filter : ".$this->_params->filter."<br>\n";
			//echo "Page suivante : ".$myLinkPageNext."<br>\n";
			$myNavBar = "<div class=\"navbar\">";
			$myNavBar.= (($curpage>1)?"<a href=\"".$myLinkPagePrev."\">":"")."&nbsp;&lt;&lt;&nbsp;".(($curpage>1)?"</a>":"");
			/*
			$myNavBar.= "<select name=\"curpage\" id=\"curpage\" onchange=\"javascript:location.href=this.value\">\n";
			for($myIndex=1;$myIndex<=$myTotalNbPages;$myIndex++)
			{
				$myLinkPageCur=$this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,$myIndex);
				$myNavBar.= "<option value=\"".$myLinkPageCur."\" ".(($myIndex==$curpage)?"selected":"").">".$myIndex."</option>\n";
			}
			$myNavBar.= "</select>\n";
			*/
			$myNavBar.= (($curpage<$myTotalNbPages)?"<a class=\"thickbox\" href=\"".$myLinkPageNext."\">":"")."&nbsp;&gt;&gt;&nbsp;".(($curpage<$myTotalNbPages)?"</a>":"");
			$myNavBar.= "</div>";

			$this->_template->assign_vars
            (
                array
                (
                    'TABLE_COL_COUNT' => count($myView)+1,
                    'FOOTER_CELL' => $myNavBar,
                    'LABEL_FILTER' => Tools::Translate("Filtre"),
                    'TEXT_FILTER' => $this->_params->filter,
                    'URL_DESTINATION'=> $this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,1),
                    'LABEL_CMD_FILTER'=> Tools::Translate("Filtrer"),
                    'FIELDITEM_LABEL'=>$this->_params->fielditem_label,
                    'FIELDITEM_HIDDEN'=>$this->_params->fielditem_hidden
                )
            );

			//echo "Nb d'enregistrements : ".$this->_recordset->count()."<br>\n";
			if($this->_recordset->count() >0)
			{
				$this->_recordset->move_first();
				//foreach($this->_recordset as $myCurRecord)
				for($i=0;$i<$this->_recordset->count();$i++)
				{
					$mySpecialActions="";
					$mySpecialActionsIcons="";
					if(isset($this->_actions) && is_array($this->_actions) && count($this->_actions)>0)
					{
						foreach($this->_actions as $curaction)
						{
							if($curaction["list_view"]==1)
							{
								$mySpecialActions.=$this->_get_action_link("list",$curaction,$this->_recordset);
								$mySpecialActionsIcons.=$this->_get_action_icon("list",$curaction,$this->_recordset);
							}
						}
					}
					$myEditLink=""; $myEditLabel=""; $myEditMore=" style=\"display:none;\" ";
					$myDelLink=""; $myDelLabel="";  $myDelMore=" style=\"display:none;\" ";
					if($this->authCanWrite())
					{
						$myAddRelationLink=$this->get_href("addrelation",$this->_recordset->key_value());
					}

					$this->_template->assign_block_vars
		            (
		                'tablecontent',
		                array
		                (

		                )
		            );

					foreach($myView as $pos=>$key)
					{
						$myCurField=$this->_fields[$key];
						$myCurFieldName=$myCurField->field_name;
						//$myFieldContent=$myCurRecord->field_display($myCurFieldName);
						$myFieldContent=$this->_recordset->field_display($myCurFieldName);


						$this->_template->assign_block_vars
			            (
			                'tablecontent.rowcontent',
			                array
			                (
			                    'CONTENT_CELL' => /* $this->_template->getHtml */ stripslashes($myFieldContent)
			                )
			            );
			            $theId=""; $theLabel="";
			            if($this->_params->ref_key!="" && $this->_params->ref_label!="")
			            {
				            $theIdKey=$this->_params->ref_key;
				            $theId=addslashes($this->_recordset->$theIdKey);
				            $theLabelKey=$this->_params->ref_label;
				            $theLabel=addslashes($this->_recordset->$theLabelKey);
			            }

						$this->_template->assign_block_vars
			            (
			                'tablecontent.rowcontent.linktype_detail_open',
			                array
			                (
			                    'LINK' => "javascript:",
			                    'ONCLICK' => "setItem('".($theId)."','".($theLabel)."');" //alert('click ".addslashes(Tools::Display($this->_params))." à ".$this->_recordset->key_value()."');"
			                )
			            );

					}
					$this->_recordset->move_next();
				}
			}
			else
			{
				$this->_table_end();
				//echo Tools::Translate("Aucun enregistrement");
			}
		}
	}

	function _setAjaxSearchList()
	{
		if(count($this->_fields)>0)
		{
			//echo __FUNCTION__."@".__LINE__." : ".print_r($this->_fields,true)."<br>\n";
			$this->_template->assign_vars(array( "LABEL_ACTIONS"=>Tools::Translate("Actions")));

			$this->_set_recordset_filters();
			//echo "Tri : ".$this->_params->sortfield."/".$this->_params->sortorder."<br>\n";
			$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,$this->_params->curpage);
			//echo __LINE__." => Query : ".$this->_recordset->_db->getQuery();
			$this->_table_begin();
			$myView=$this->_get_current_view();
			//echo "Liste des champs : ".Tools::Display($myView)."<br>\n";
			// Définition de l'entête
			foreach($myView as $pos=>$key)
			{


				$myCurField=$this->_fields[$key];
				$myCurSort=$this->_params->sortorder;
				$myLinkClass=" class=\"mdtb_header_link thickbox\" ";
				if($myCurField->field_name==$this->_params->sortfield)
				{
					$myCurSort=(($myCurSort=="ASC")?"DESC":"ASC");
					$myLinkClass=" class=\"mdtb_header_link mdtb_header_current thickbox\" ";
				}
				$myCurHeaderLink="<a href=\"".$this->get_href("ajaxsearchlist",-1,$myCurField->field_name,$myCurSort,$this->_params->curpage)."\" ".$myLinkClass.">".Tools::Translate($myCurField->field_name)."</a>";

				$this->_template->assign_block_vars('tableheader',
								array(	'HEADER_CELL' => $myCurHeaderLink ));
			}

			// Définition du pied de tableau
			$curpage=$this->_params->curpage;
			$sortfield=$this->_params->sortfield;
			$sortorder=$this->_params->sortorder;
			$myTotalNbPages=$this->_recordset->get_page_count();
			$myLinkPagePrev=$this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,$curpage-1);
			$myLinkPageNext=$this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,$curpage+1);
			//echo "Filter : ".$this->_params->filter."<br>\n";
			//echo "Page suivante : ".$myLinkPageNext."<br>\n";
			$myNavBar = "<div class=\"navbar\">";
			$myNavBar.= (($curpage>1)?"<a href=\"".$myLinkPagePrev."\">":"")."&nbsp;&lt;&lt;&nbsp;".(($curpage>1)?"</a>":"");
			/*
			$myNavBar.= "<select name=\"curpage\" id=\"curpage\" onchange=\"javascript:location.href=this.value\">\n";
			for($myIndex=1;$myIndex<=$myTotalNbPages;$myIndex++)
			{
				$myLinkPageCur=$this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,$myIndex);
				$myNavBar.= "<option value=\"".$myLinkPageCur."\" ".(($myIndex==$curpage)?"selected":"").">".$myIndex."</option>\n";
			}
			$myNavBar.= "</select>\n";
			*/
			$myNavBar.= (($curpage<$myTotalNbPages)?"<a class=\"thickbox\" href=\"".$myLinkPageNext."\">":"")."&nbsp;&gt;&gt;&nbsp;".(($curpage<$myTotalNbPages)?"</a>":"");
			$myNavBar.= "</div>";

			$this->_template->assign_vars
            (
                array
                (
                    'TABLE_COL_COUNT' => count($myView)+1,
                    'FOOTER_CELL' => $myNavBar,
                    'LABEL_FILTER' => Tools::Translate("Filtre"),
                    'TEXT_FILTER' => $this->_params->filter,
                    'URL_DESTINATION'=> $this->get_href("ajaxsearchlist",-1,$sortfield,$sortorder,1),
                    'LABEL_CMD_FILTER'=> Tools::Translate("Filtrer")
                )
            );

			//echo "Nb d'enregistrements : ".$this->_recordset->count()."<br>\n";
			if($this->_recordset->count() >0)
			{
				$this->_recordset->move_first();
				//foreach($this->_recordset as $myCurRecord)
				for($i=0;$i<$this->_recordset->count();$i++)
				{
					$mySpecialActions="";
					$mySpecialActionsIcons="";
					if(isset($this->_actions) && is_array($this->_actions) && count($this->_actions)>0)
					{
						foreach($this->_actions as $curaction)
						{
							if($curaction["list_view"]==1)
							{
								$mySpecialActions.=$this->_get_action_link("list",$curaction,$this->_recordset);
								$mySpecialActionsIcons.=$this->_get_action_icon("list",$curaction,$this->_recordset);
							}
						}
					}
					$myEditLink=""; $myEditLabel=""; $myEditMore=" style=\"display:none;\" ";
					$myDelLink=""; $myDelLabel="";  $myDelMore=" style=\"display:none;\" ";
					if($this->authCanWrite())
					{
						$myAddRelationLink=$this->get_href("addrelation",$this->_recordset->key_value());
					}

					$this->_template->assign_block_vars
		            (
		                'tablecontent',
		                array
		                (

		                )
		            );

					foreach($myView as $pos=>$key)
					{
						$myCurField=$this->_fields[$key];
						$myCurFieldName=$myCurField->field_name;
						//$myFieldContent=$myCurRecord->field_display($myCurFieldName);
						$myFieldContent=$this->_recordset->field_display($myCurFieldName);


						$this->_template->assign_block_vars
			            (
			                'tablecontent.rowcontent',
			                array
			                (
			                    'CONTENT_CELL' => /* $this->_template->getHtml */ stripslashes($myFieldContent)
			                )
			            );

						$this->_template->assign_block_vars
			            (
			                'tablecontent.rowcontent.linktype_detail_open',
			                array
			                (
			                    'LINK' => $myAddRelationLink,
			                    'ONCLICK' => ""
			                )
			            );

					}
					$this->_recordset->move_next();
				}
			}
			else
			{
				$this->_table_end();
				//echo Tools::Translate("Aucun enregistrement");
			}
		}
	}

	function _setList()
	{
		if(count($this->_fields)>0)
		{
			//echo __FUNCTION__."@".__LINE__." : ".print_r($this->_fields,true)."<br>\n";
			$this->_template->assign_vars(array( "LABEL_ACTIONS"=>Tools::Translate("Actions")));

			if($this->mode=="caller")
			{
					$this->_template->assign_block_vars('linktype_caller',
									array(	'REF_KEY' => "clef", "REF_LABEL"=>"label"));

			}
			$this->_set_recordset_filters();
			//echo "Tri : ".$this->_params->sortfield."/".$this->_params->sortorder."<br>\n";
			$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,$this->_params->curpage);
			//echo __LINE__." => Query : ".$this->_recordset->_db->getQuery();
			$this->_table_begin();
			$myView=$this->_get_current_view();

			// Définition de l'entête
			foreach($myView as $pos=>$key)
			{
				$myCurField=$this->_fields[$key];
				$myCurSort=$this->_params->sortorder;
				$myLinkClass=" class=\"mdtb_header_link\" ";
				if($myCurField->field_name==$this->_params->sortfield)
				{
					$myCurSort=(($myCurSort=="ASC")?"DESC":"ASC");
					$myLinkClass=" class=\"mdtb_header_link mdtb_header_current\" ";
				}
				if($myCurField->type!="callback")
					$myCurHeaderLink="<a href=\"".$this->get_href("list",-1,$myCurField->field_name,$myCurSort,$this->_params->curpage)."\" ".$myLinkClass.">".Tools::Translate($myCurField->field_name)."</a>";
				else
					$myCurHeaderLink=Tools::Translate($myCurField->field_name);

				$this->_template->assign_block_vars('tableheader',
								array(	'HEADER_CELL' => $myCurHeaderLink ));
			}

			// Définition du pied de tableau
			$curpage=$this->_params->curpage;
			$sortfield=$this->_params->sortfield;
			$sortorder=$this->_params->sortorder;
			$myTotalNbPages=$this->_recordset->get_page_count();
			$myLinkPagePrev=$this->get_href("list",-1,$sortfield,$sortorder,$curpage-1);
			$myLinkPageNext=$this->get_href("list",-1,$sortfield,$sortorder,$curpage+1);
			//echo "Filter : ".$this->_params->filter."<br>\n";
			//echo "Page suivante : ".$myLinkPageNext."<br>\n";
			$myNavBar = "<div class=\"navbar\">";
			$myNavBar.= (($curpage>1)?"<a href=\"".$myLinkPagePrev."\">":"")."&nbsp;&lt;&lt;&nbsp;".(($curpage>1)?"</a>":"");
			$myNavBar.= "<select name=\"curpage\" id=\"curpage\" onchange=\"javascript:location.href=this.value\">\n";
			for($myIndex=1;$myIndex<=$myTotalNbPages;$myIndex++)
			{
				$myLinkPageCur=$this->get_href("list",-1,$sortfield,$sortorder,$myIndex);
				$myNavBar.= "<option value=\"".$myLinkPageCur."\" ".(($myIndex==$curpage)?"selected":"").">".$myIndex."</option>\n";
			}
			$myNavBar.= "</select>\n";
			$myNavBar.= (($curpage<$myTotalNbPages)?"<a href=\"".$myLinkPageNext."\">":"")."&nbsp;&gt;&gt;&nbsp;".(($curpage<$myTotalNbPages)?"</a>":"");
			$myNavBar.= "</div>";

			$this->_template->assign_vars
            (
                array
                (
                    'TABLE_COL_COUNT' => count($myView)+1,
                    'FOOTER_CELL' => $myNavBar
                )
            );

			//echo "Nb d'enregistrements : ".$this->_recordset->count()."<br>\n";
			if($this->_recordset->count() >0)
			{
				$this->_recordset->move_first();
				//foreach($this->_recordset as $myCurRecord)
				for($i=0;$i<$this->_recordset->count();$i++)
				{
					$mySpecialActions="";
					$mySpecialActionsIcons="";
					if(isset($this->_actions) && is_array($this->_actions) && count($this->_actions)>0)
					{
						foreach($this->_actions as $curaction)
						{
							if($curaction["list_view"]==1)
							{
								$mySpecialActions.=$this->_get_action_link("list",$curaction,$this->_recordset);
								$mySpecialActionsIcons.=$this->_get_action_icon("list",$curaction,$this->_recordset);
							}
						}
					}
					$myEditLink=""; $myEditLabel=""; $myEditMore=" style=\"display:none;\" ";
					$myDelLink=""; $myDelLabel="";  $myDelMore=" style=\"display:none;\" ";
					if($this->authCanWrite())
					{
						$myEditLink=$this->get_href("form",$this->_recordset->key_value());
						$myEditLabel=Tools::Translate("Modifier");
						$myEditMore="";
					}
					if($this->authCanDelete())
					{
						$myDelLink=$this->get_href("delete",$this->_recordset->key_value());
						$myDelLabel=Tools::Translate("Effacer");
						$myDelMore="onclick=\"return confirm('".Tools::Translate("Etes-vous s&ucirc;r ?")."');\"";
					}

					$myLinkDissociate="";
					if($this->ischild() && $this->authCanDelete())
					{
						$myDisslLink=$this->get_href("delrelation",$this->_recordset->key_value());
						$myDissLabel=Tools::Translate("Dissocier");
						$myDissMore="onclick=\"return confirm('".Tools::Translate("Etes-vous s&ucirc;r ?")."');\"";
						$myLinkDissociate="<a href=\"".$myDisslLink."\" ".$myDissMore.">".$myDissLabel."</a>\n";
					}

					$this->_template->assign_block_vars
		            (
		                'tablecontent',
		                array
		                (
		                	'ACTION_SPECIAL' => $mySpecialActions,
		                	'ACTION_SPECIAL_ICON' => $mySpecialActionsIcons,
		                    //'ACTION_EDIT_LINK' => $this->get_href("form",$myCurRecord->get_key_value()),
		                    'ACTION_EDIT_LINK' => $myEditLink,
		                    'ACTION_EDIT_LABEL' => $myEditLabel,
		                    //'ACTION_DEL_LINK' => $this->get_href("delete",$myCurRecord->get_key_value()),
		                    'ACTION_DEL_LINK' => $myDelLink,
		                    'ACTION_DEL_LABEL' => $myDelLabel,
		                    'ACTION_EDIT_MORE' => $myEditMore,
		                    'ACTION_DEL_MORE' => $myDelMore,
		                    'ACTION_DISSOCIATE' => $myLinkDissociate
		                )
		            );

					foreach($myView as $pos=>$key)
					{
						$myCurField=$this->_fields[$key];
						$myCurFieldName=$myCurField->field_name;
						//$myFieldContent=$myCurRecord->field_display($myCurFieldName);
						$myFieldContent=$this->_recordset->field_display($myCurFieldName);


						$this->_template->assign_block_vars
			            (
			                'tablecontent.rowcontent',
			                array
			                (
			                    'CONTENT_CELL' => /* $this->_template->getHtml */ stripslashes($myFieldContent)
			                )
			            );

						$this->_template->assign_block_vars
			            (
			                'tablecontent.rowcontent.linktype_detail_open',
			                array
			                (
			                    'LINK' => $this->get_href("detail",$this->_recordset->key_value())
			                )
			            );

					}
					$this->_recordset->move_next();
				}
			}
			else
			{
				$this->_table_end();
				//echo Tools::Translate("Aucun enregistrement");
			}
		}
	}



	function _setForm($target,$theId=null)
	{
		$this->_recordset->filter_set($this->_recordset->key_name(),$theId,false);
		$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,1);
		$this->_recordset->move_first();
		$myRecordLoaded=false;
		if($this->_recordset->_result_nb_rows>0)
			$myRecordLoaded=true;
		//$myRecordLoaded=$this->_recordset->load($theId);

		if(!$myRecordLoaded && $theId>0)
			$theId=null;
		$myTableName=str_replace("#__",$this->_db->_table_prefix,$this->table_name);
		$this->_template->assign_vars( array ( 'TABLE_NAME' => $myTableName) );

		$this->_form_begin($target,$theId);
		if(count($this->_fields)>0)
		{
			$myView=$this->_get_current_view();
			foreach($myView as $pos=>$key)
			{
				$myCurField=$this->_fields[$key];
				$myFieldTitle=Tools::Translate($myCurField->field_name);
				$myFieldValue=$myCurField->field_name;
				//$myFieldContent=$this->_recordset[0]->get_form_field($myCurField->field_name);
				if($this->ischild() && $this->_reference->child_key==$myCurField->field_name && !$myRecordLoaded)
				{
					$myFieldContent=$this->_recordset->field_form($myCurField->field_name,$this->_reference->key_value);
				}
				else
					$myFieldContent=$this->_recordset->field_form($myCurField->field_name);

				$myDebugFieldContent=$this->_recordset->$myFieldValue;
				//echo "Contenu du champ : ".$myFieldValue."/".$key." : ".$myDebugFieldContent."<br>\n";

				$myFieldNameStyle="";
				$myFielContentStyle="";
				if($myCurField->type=="display_content")
				{
					$myColSpan=($myCurField->ref_key=="")?" colspan=\"2\" ":"";
					$myFieldNameStyle=$myColSpan." style=\"".$myCurField->ref_table."\"";
					if($myCurField->ref_key=="")
						$myFielContentStyle="style=\"display:none;\"";
					else
					{
						$myFieldContent=$myCurField->ref_key;
						$myFielContentStyle=$myCurField->ref_label;
					}
				}

				$this->_template->assign_block_vars('formfields',
													array(	'FIELD_NAME' => /* $this->_template->getHtml */ ($myFieldTitle),
															'FIELD_NAME_STYLE' => $myFieldNameStyle,
															'FIELD_CONTENT' => $myFieldContent,
															'FIELD_CONTENT_STYLE' => $myFielContentStyle
													));
			}

			$myHiddenView=$this->_get_hidden_fields();
			foreach($myHiddenView as $pos=>$key)
			{
				$myCurField=$this->_fields[$key];
				$myFieldTitle=Tools::Translate($myCurField->field_name);
				$myFieldValue=$myCurField->field_name;
				$myFieldContent=$this->_recordset->hidden_field_form($myCurField->field_name);
				//$myFieldContent=$this->_recordset->$myFieldValue;
				$this->_template->assign_block_vars('formhidden',
													array(	'HIDDEN_FIELD_NAME' => $myFieldValue,
															'HIDDEN_FIELD_VALUE' => $myFieldContent
													));
			}


		}
		$this->_form_end($theId);
	}

	function _setDetail($theId=null)
	{
		$this->_recordset->filter_set($this->_recordset->key_name(),$theId,false);
		$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,1);
		$this->_recordset->move_first();
		//$this->_recordset->load($theId);

		//echo "Clef : ".$this->_key." / ".$this->_tbl_key."<br>\n";
		//echo "Chargement de ".$theId." : ".$this->_recordset->_db->getQuery();
		$myBackURL=$this->get_return_href(null);
		//echo "URL de retour : ".$myBackURL."<br>\n";
		if(count($this->_fields)>0)
		{
			$mySpecialActions="";
			$mySpecialActionsLabel="";
			if(isset($this->_actions) && is_array($this->_actions) && count($this->_actions)>0)
			{
				foreach($this->_actions as $curaction)
				{
					if($curaction["detail_view"]==1)
						$mySpecialActions.=$this->_get_action_link("detail",$curaction,$this->_recordset);

				}
				$mySpecialActionsLabel=Tools::Translate("Actions sp&eacute;ciales");
			}
			$mySpecialActionsMore="";
			if($mySpecialActionsLabel=="" && $mySpecialActions=="") $mySpecialActionsMore=" style=\"display:none;\" ";

			$myEditLink=""; $myEditLabel=""; $myEditMore=" style=\"display:none;\" ";
			$myDelLink=""; $myDelLabel="";  $myDelMore=" style=\"display:none;\" ";
			if($this->authCanWrite())
			{
				$myEditLink=$this->get_href("form",$this->_recordset->key_value());
				$myEditLabel=Tools::Translate("Modifier");
				$myEditMore="";
			}

			if($this->authCanDelete())
			{
				$myDelLink=$this->get_href("delete",$this->_recordset->key_value());
				$myDelLabel=Tools::Translate("Effacer");
				$myDelMore="onclick=\"return confirm('".Tools::Translate("Etes-vous s&ucirc;r ?")."');\"";
			}

			$this->_template->assign_vars
            (
                array
                (
                	'ACTION_SPECIAL_LABEL' => $mySpecialActionsLabel,
                	'ACTION_SPECIAL' => $mySpecialActions,
                	'ACTION_SPECIAL_MORE' => $mySpecialActionsMore,
                    'ACTION_EDIT_LABEL' => $myEditLabel,
                    'ACTION_BACK_LABEL' => Tools::Translate("Retour"),
                    'ACTION_DEL_LABEL' => $myDelLabel,
                    'ACTION_EDIT_LINK' => $myEditLink,
                    'ACTION_BACK_LINK' => $myBackURL,
                    'ACTION_DEL_LINK' => $myDelLink,
                    'ACTION_EDIT_MORE' =>$myEditMore,
                    'ACTION_BACK_MORE' => "",
                    'ACTION_DEL_MORE' => $myDelMore
                )
            );

			$myView=$this->_get_current_view();
			foreach($myView as $pos=>$key)
			{
				$myCurField=$this->_fields[$key];
				$myFieldTitle=Tools::Translate($myCurField->field_name);
				$myFieldValue=$myCurField->field_name;
				$myFieldContent=$this->_recordset->field_display($myCurField->field_name);
				$myFieldNameStyle="";
				$myFielContentStyle="";
				if($myCurField->type=="display_content")
				{
					$myColSpan=($myCurField->ref_key=="")?" colspan=\"2\" ":"";
					$myFieldNameStyle=$myColSpan." style=\"".$myCurField->ref_table."\"";
					if($myCurField->ref_key=="")
						$myFielContentStyle="style=\"display:none;".$myCurField->ref_label."\"";
					else
					{
						$myFieldContent=$myCurField->ref_key;
						$myFielContentStyle=$myCurField->ref_label;
					}
				}
				$this->_template->assign_block_vars('formfields',
													array(	'FIELD_NAME' => /* $this->_template->getHtml */ ($myFieldTitle),
															'FIELD_NAME_STYLE' => $myFieldNameStyle,
															'FIELD_CONTENT' => $myFieldContent,
															'FIELD_CONTENT_STYLE' => $myFielContentStyle
													));
			}
		}
	}

	function _get_action_href($cursection,$curaction,$currecordset)
	{
		$myLink="";
		//echo "Destination de la cible de l'action spéciale : '".$curaction["target"]."'<br>\n";
		if(isset($curaction["name"]) && isset($curaction["label"]) && $curaction["name"]!="" && $curaction["label"]!="")
		{
			if($curaction["target"]=="")
			{
				switch($cursection)
				{
					case "list":
						$curpage=$this->_params->curpage;
						$sortfield=$this->_params->sortfield;
						$sortorder=$this->_params->sortorder;
						$myTotalNbPages=$currecordset->get_page_count();
						$myLink=$this->get_href("list",-1,$sortfield,$sortorder,$curpage) . "&itemid=".$currecordset->key_value();
						break;
					case "form":
						$myLink=$this->get_href("form",$currecordset->key_value());
						break;
					case "detail":
						$myLink=$this->get_href("detail",$currecordset->key_value());
						break;
				}
			}
			else
			{
				$myLink=$this->_path_pre.$curaction["target"].$currecordset->key_value();
			}
		}
		return $myLink;
	}

	function _get_global_action_link($curaction)
	{
		if(isset($curaction["name"]) && isset($curaction["label"]) && $curaction["name"]!="" && $curaction["label"]!="")
		{
			$myLink=$this->get_href("list");
			$myLink="<a href=\"".$myLink."&action=globalspecial&specialaction=".$curaction["name"]."\">".$curaction["label"]."</a>\n";
		}
		return $myLink;
	}

	function _get_action_link($cursection,$curaction,$currecordset)
	{

		if(isset($curaction["name"]) && isset($curaction["label"]) && $curaction["name"]!="" && $curaction["label"]!="")
		{
			$myLink=$this->_get_action_href($cursection,$curaction,$currecordset);
			$myLink="<a href=\"".$myLink."&action=special&specialaction=".$curaction["name"]."\">".$curaction["label"]."</a>\n";			$check_action_function="_".$curaction["name"];
			if(method_exists($this,$check_action_function))
				$myLink=$this->$check_action_function($myLink,$currecordset);
			//echo "Action courante : ".print_r($curaction,true)."<br>\n";
		}
		return $myLink;
	}

	function _get_action_icon($cursection,$curaction,$currecordset)
	{
		$myLink="";
		if(isset($curaction["name"]) && isset($curaction["label"]) && $curaction["name"]!="" && $curaction["label"]!="")
		{
			$myLink=$this->_get_action_href($cursection,$curaction,$currecordset);
			$myLink="<a href=\"".$myLink."&action=special&specialaction=".$curaction["name"]."\"><img src=\"".$this->_path_pre.$curaction["icon"]."\" border=\"0\" width=\"10\"></a>\n";			$check_action_function="_".$curaction["name"];
			$check_action_function="_".$curaction["name"];
			if(method_exists($this,$check_action_function))
				$myLink=$this->$check_action_function($myLink,$currecordset);
			//echo "Action courante : ".print_r($curaction,true)."<br>\n";
		}
		return $myLink;
	}


/*
 * Fonctions d'accès direct aux données depuis la table
 */

	function recGetFieldsList()
	{
		return $this->_recordset->_get_fields_array();
	}

	function recGetRecord()
	{
		$myArray=$this->_recordset->_get_fields_array();
		if($myArray!==false)
		{
			$myObj=new stdClass();
			foreach($myArray as $key=>$value)
				$myObj->$value=$this->_recordset->$value;
			return $myObj;
		}
		return false;
	}

	function recSetRecord($myObj)
	{
		$myArray=$this->_recordset->_get_fields_array();
		if($myArray!==false && is_object($myObj))
		{
			foreach($myArray as $key=>$value)
				if(isset($myObj->$value))
					$this->_recordset->$value=$myObj->$value;

			return true;
		}
		return false;
	}

	function recNewRecord()
	{
		$myArray=$this->_recordset->_get_fields_array();
		if($myArray!==false)
		{
			foreach($myArray as $key=>$value)
			{
				if($value==$this->_recordset->key_name())
					$this->_recordset->$value=null;
				else
					$this->_recordset->$value="";
			}

			return true;
		}
		return false;
	}

	function recGetValue($theField)
	{
		$myRealFieldName=$this->_recordset->_get_field_from_name($theField);
		if($myRealFieldName==false)
			return null;
		else
			return $this->_recordset->$theField;
	}
	function recSetValue($theField,$theValue)
	{
		$myRealFieldName=$this->_recordset->_get_field_from_name($theField);
		if($myRealFieldName!=false)
			$this->_recordset->$theField=$theValue;
	}

	function recDBError()
	{
		return $this->_recordset->_db->getErrorNum()."&nbsp;:&nbsp;". $this->_recordset->_db->getErrorMsg();
	}

	function recKeyName()
	{
		return $this->_recordset->key_name();
	}

	function recTableName()
	{
		return $this->_recordset->_tablename;
	}

	function recKeyValue()
	{
		return $this->_recordset->key_value();
	}

	function recStore($myObj=false)
	{
		if($myObj!==false && is_object($myObj))
			if(!$this->recSetRecord($myObj))
				return false;
		return $this->_recordset->record_save();
	}

	function recLoad($theId)
	{
		return $this->_recordset->load($theId);
	}

	function recSQLSearch($theSQLQuery,$theFieldSort="",$theOrderSort="")
	{
		if($theFieldSort!="") $this->_params->sortfield=$theFieldSort;
		else $this->_params->sortfield=$this->_defaultparams->sortfield;
		if($theOrderSort!="") $this->_params->sortorder=$theOrderSort;
		else $this->_params->sortorder=$this->_defaultparams->sortorder;
		$this->_params->filter="";
		$this->_recordset->sql_filter_set("default",$theSQLQuery);
		$this->_set_recordset_filters();
		$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,1,false,true);
	}

	function recSearch($theSearchValue="",$theFieldSort="",$theOrderSort="")
	{
		if($theFieldSort!="") $this->_params->sortfield=$theFieldSort;
		else $this->_params->sortfield=$this->_defaultparams->sortfield;
		if($theFieldSort!="") $this->_params->sortfield=$theFieldSort;
		else $this->_params->sortorder=$this->_defaultparams->sortorder;

		$this->_params->filter=$theSearchValue;
		$this->_set_recordset_filters();
		$this->_recordset->record_search($this->_params->sortfield,$this->_params->sortorder,1,false,true);
		//echo "Requête : ".$this->_recordset->_db->getQuery();
	}

	function recNext()
	{
		return $this->_recordset->move_next();
	}

	function recPrev()
	{
		return $this->_recordset->move_prev();
	}

	function recFirst()
	{
		return $this->_recordset->move_first();
	}

	function recLast()
	{
		return $this->_recordset->move_last();
	}

	function recCount()
	{
		return $this->_recordset->get_row_count();
	}

	function recDelete()
	{
		return $this->_recordset->record_delete();
	}

}
class mdtb_recordset extends mosDBTable
{
	var $_tablename="";
	var $_fieldslist;
	var $_pointer=0;
	var $_records=array();
	var $_key="";
	var $_filters=array();
	var $_sql_filters=array();
	var $_files_path="";
	var $_nbperpage=20;
	var $_result_nb_rows=0;
	var $_isloaded=false;
	var $_mdtb=null;

	function mdtb_recordset(&$database,$tablename,&$fieldslist,$tablekey="ID",$files_path)
	{
		//echo "Init avec : name=$tablename, key=$tablekey, path=$files_path<br>\n";
		$this->_key=$tablekey;
		$this->_files_path=$files_path;
		$this->_fieldslist=&$fieldslist;
		$this->_tablename=$tablename;
		if(count($fieldslist)>0)
		{
			foreach($fieldslist as $myCurField)
			{
				$myFieldName=$myCurField->field_name;
				$this->$myFieldName="";
				$this->_filters[$myFieldName]["value"]="";
				$this->_filters[$myFieldName]["large"]=true;
			}
			//echo "init : ".$this->_tablename."<br>\n";
			$this->mosDBTable( $this->_tablename, $this->_key, $database );
		}
	}

	function set_reference($ref)
	{

		$this->_reference->table=$ref->table;
		$this->_reference->key_value=$ref->key_value;
		if(isset($ref->child_type))
			$this->_reference->child_type=$ref->child_type;
		else
			$this->_reference->child_type="direct";
		if(isset($ref->child_rel))
			$this->_reference->child_rel=$ref->child_rel;
		if(isset($ref->child_key))
			$this->_reference->child_key=$ref->child_key;
		if(isset($ref->parent_key))
			$this->_reference->parent_key=$ref->parent_key;

	}


	function set_nb_elements_per_page($theNb)
	{
		$myNb=intval($theNb);
		if($myNb>0)
			$this->_nbperpage=$myNb;

	}

	function _clear_current()
	{
		if(count($this->_fieldslist)>0)
		{
			foreach($this->_fieldslist as $myCurField)
			{
				$myFieldName=$myCurField->field_name;
				$this->$myFieldName="";
				if($myFieldName==$this->_key)
					$this->$myFieldName=null;
			}
			return true;
		}
		return false;
	}

	function _set_current_to_record()
	{
		if(count($this->_fieldslist)>0)
		{
			foreach($this->_fieldslist as $myCurField)
			{
				$myFieldName=$myCurField->field_name;
				if(isset($this->$myFieldName))
				{
					if(!isset($this->_records[$this->_pointer])) $this->_records[$this->_pointer]=new stdClass();
					$this->_records[$this->_pointer]->$myFieldName=$this->$myFieldName;
				}
			}
			return true;
		}
		return false;
	}

	function _set_record_to_current()
	{
		if(isset($this->_records[$this->_pointer]))
			if(count($this->_fieldslist)>0)
			{
				$this->_clear_current();
				foreach($this->_fieldslist as $myCurField)
				{
					$myFieldName=$myCurField->field_name;
					if(isset($this->_records[$this->_pointer]->$myFieldName))
						$this->$myFieldName=$this->_records[$this->_pointer]->$myFieldName;
				}
				return true;
			}
		return false;
	}

	function _get_fields_array()
	{
		$myArray=array();
		if(is_array($this->_fieldslist) && count($this->_fieldslist) )
		{
			foreach($this->_fieldslist as $curfield)
			{
				$myArray[]=$curfield->field_name;
			}
			return $myArray;
		}
		return false;
	}


	function _get_field_from_name($theName)
	{
		if($theName!="")
		{
			foreach($this->_fieldslist as $curfield)
			{
				if($curfield->field_name===$theName)
					return $curfield;
			}
		}
		return false;
	}

	function _build_filter_for_field($theField,$theValue)
	{
		switch($theField->type)
		{
			case "text":
			case "textlong":
			case "number":
			case "date":
			default:
				$myQryField=$theField->table_name.".".$theField->field_name;
				return $myQryField." LIKE '".addslashes($theValue)."' ";
				break;
			case "image":
			case "file":
				$myQryField=$theField->table_name.".".$theField->field_name;
				return $myQryField." REGEXP ';s:.*:\".*".str_replace("%",".*",addslashes($theValue)).".*\";s:'";
				//return $myQryField." LIKE '".$theValue."' ";
				break;
			case "foreign":
			case "foreign_shortdate":
				$myQueryRef=$theField->field_name."_".$theField->ref_table.".".$theField->field_name." LIKE '".addslashes($theValue)."' ";
				return $myQueryRef;
				break;
			case "reference":
				$myRefTable=$theField->field_name."_".$theField->ref_table;
				if(in_array($theField->field_name,$this->_mdtb->searchable))
					$myQryFieldRef=$myRefTable.".".$theField->ref_label;
				else
					$myQryFieldRef=$myRefTable.".".$theField->ref_key;
				//$myQryCrossRef=$theField->ref_table.".".$theField->ref_key."=".$theField->table_name.".".$theField->field_name;
				$myQueryRef=" ".$myQryFieldRef." LIKE '".addslashes($theValue)."' ";
				return $myQueryRef;
				break;
		}
	}

	function _build_filter($theFiltersCompulsory)
	{
		$myWhere="";
		$myCurLinkKey="";
		$myLinkKey=" AND ";

		// First build a table of all search texts
		$mySearchTerms=array();
		foreach($this->_filters as $key=>$filter)
		{
			$myCurField=$this->_get_field_from_name($key);
			if($myCurField!==false)
			{

				$myStr=trim($filter["value"]);
				if($myStr!="")
				{
					$mySearchArray=explode(" ",$myStr);
					if(count($mySearchArray)>0)
						foreach($mySearchArray as $myTerm)
						{
							//echo "Ajout du filtre ".$myTerm." pour le champ ".$myCurField->field_name."<br>\n";
							$myCurField->largefilter=$filter["large"];
							$mySearchTerms[$myTerm][]=$myCurField;
						}
				}
			}
		}

		foreach($mySearchTerms as $key => $curterm)
		{
			//echo "Ajout des termes recherchés";
			$myTermSearch="";
			$myTermCurLink="";
			$myTermLink=" OR ";
			$indent=0;
			foreach($curterm as $curfield)
			{

				$myLocalSearch=$key;
				$myLocalSearch=str_replace("*","%",$myLocalSearch);
				if($curfield->largefilter)
					$myLocalSearch="%".$myLocalSearch."%";
				$myTmpSearch=$myTermCurLink.$this->_build_filter_for_field($curfield,$myLocalSearch);
				//echo __LINE__."=> (".$indent++.")".Tools::Display($curfield).$myTmpSearch."<br>\n";
				$myTermSearch.=$myTmpSearch;
				$myTermCurLink=$myTermLink;
			}
			$myTermSearch="( ".$myTermSearch." )";
			$myWhere.=$myCurLinkKey.$myTermSearch;
			$myCurLinkKey=$myLinkKey;
		}
		$myQueryReference="";
		if(isset($this->_reference->table) && isset($this->_reference->key_value))
		{
			foreach($this->_fieldslist as $curfield)
			{
				//echo "Comp : ".$curfield->ref_table."==".$this->_reference->table."<br>\n";
				//echo "Champs ".$curfield->ref_table." vs ".$curfield->table_name.".".$curfield->field_name."=".$this->_reference->key_value." <br>";
				if(str_replace("#__",$this->_db->_table_prefix,$curfield->ref_table)==str_replace("#__",$this->_db->_table_prefix,$this->_reference->table))
				{
					$myQueryReference=" ".$curfield->table_name.".".$curfield->field_name."=".$this->_reference->key_value." ";
					//echo __LINE__." => Reference : ".$myQueryReference."<br>\n";
				}
			}
		}

		$myConcatSqlFilters="";
		if(isset($this->_sql_filters) && is_array($this->_sql_filters) && count($this->_sql_filters)>0)
		{
			foreach($this->_sql_filters as $curfilter)
			{
				if(trim($curfilter)!="")
					$myConcatSqlFilters.=($myConcatSqlFilters!=""?$myLinkKey:"")." ( ". $curfilter .") ";
			}
		}
		if($myWhere=="")
		{
			$myWhere="".(($myQueryReference!='')?(" WHERE ".$myQueryReference):"");
		}
		else
			$myWhere=" WHERE (".$myWhere.") ".(($myQueryReference!='')?(" AND ".$myQueryReference):"");

		if($myConcatSqlFilters!="")
			$myWhere.=(($myWhere=="")?" WHERE ":$myLinkKey).$myConcatSqlFilters;


		/*
		 * Joint relatif aux tables liées par relation indirecte
		 */
		if(isset($this->_reference))
		{
			$myWhereRelTable="";
			//echo "Child type : ".$this->_reference->child_type."<br>\n";
			if(isset($this->_reference->child_type) && $this->_reference->child_type=="relative")
			{
				$myWhereRelTable.=" (".$this->_reference->child_rel.".".$this->_reference->parent_key."=".$this->_reference->key_value;
				$myWhereRelTable.=" AND ".$this->_reference->child_rel.".".$this->_reference->child_key."=".$this->key_name().")";
			}
			if($myWhereRelTable!="")
				$myWhere.=(($myWhere=="")?" WHERE ":$myLinkKey).$myWhereRelTable;

		}



		return $myWhere;
	}

	function _build_ref_join()
	{
		$debug=false;
		$myWhereRef="";

		/*
		 * Joint relatif aux tables liées par relation indirecte
		 */
		if(isset($this->_reference->child_type) && $this->_reference->child_type=="relative")
		{
			$myWhereRef.=" LEFT JOIN ".$this->_reference->child_rel." ON ".$this->_tablename.".".$this->key_name()."=".$this->_reference->child_rel.".".$this->_reference->child_key." ";
		}


		/*
		 * Joints relatifs aux champs croisés ou références tierses
		 */
		foreach($this->_fieldslist as $curfield)
		{
			$myRefTable=$curfield->field_name."_".$curfield->ref_table;
			if($curfield->type=="reference")
				$myWhereRef.=" LEFT JOIN ".$curfield->ref_table." as ".$myRefTable." ON ".$myRefTable.".".$curfield->ref_key."=".$curfield->table_name.".".$curfield->field_name." ";
			if(strpos($curfield->type,"foreign")===0)
			{
				if($debug) echo "Type ".$curfield->type.", table de ref : ".$curfield->ref_table."<br>\n";
				$myKeyTable=str_replace("#__",$this->_db->_table_prefix,$curfield->ref_table);
				if($debug) echo "Table parent ".$myKeyTable."<br>\n";
				if($debug) echo "Déclaration des enfants : ".Tools::Display($this->_mdtb->_children);
				$child_found=false;
				if(isset($this->_mdtb->_children) && is_array($this->_mdtb->_children) && count($this->_mdtb->_children)>0)
					foreach($this->_mdtb->_children as $childclass => $child)
					{
						if($debug) echo "Child ".$childclass."<br>\n";
						if(!$child_found)
						{
							if(class_exists(trim($childclass)))
							{
								if($debug) echo "Classe existe<br>\n";
								$myObjChild=new $childclass($this->_mdtb->_db,$this->_mdtb->_template_name,$this->_mdtb->_script,$this->_mdtb->_curpath);
								$myObjChild->init();
								$myChildTable=str_replace("#__",$this->_db->_table_prefix,$myObjChild->table_name);
								if($debug) echo "Table enfant ".$myChildTable." vs keytable : ".$myKeyTable."<br>\n";
								if($myChildTable==$myKeyTable)
								{
									if($debug) echo "===== Bonne table trouvÃ©e =====<br>\n";
									$myWhereRef.=" LEFT JOIN ".$curfield->ref_table." as ".$myRefTable." ON (".$myRefTable.".".$curfield->ref_key."=".$curfield->table_name.".".$curfield->ref_label." AND ".$myRefTable.".".$child["child_key"]."=".$curfield->table_name.".".$child["parent_key"]." ) ";
									$child_found=true;
								}
							}
						}
					}
				if(isset($this->_mdtb->_foreigndefinition) && is_array($this->_mdtb->_foreigndefinition) && count($this->_mdtb->_foreigndefinition)>0)
					foreach($this->_mdtb->_foreigndefinition as $childclass => $child)
					{
						if($debug) echo "Child ".$childclass."<br>\n";
						if(!$child_found)
						{
							if(class_exists(trim($childclass)))
							{
								$debug=false;
								if($debug) echo "Classe existe<br>\n";
								$myObjChild=new $childclass($this->_mdtb->_db,$this->_mdtb->_template_name,$this->_mdtb->_script,$this->_mdtb->_curpath);
								$myObjChild->init();
								$myChildTable=str_replace("#__",$this->_db->_table_prefix,$myObjChild->table_name);
								if($debug) echo "Table enfant ".$myChildTable." vs keytable : ".$myKeyTable."<br>\n";
								if($myChildTable==$myKeyTable)
								{
									if($debug) echo "===== Bonne table trouvée =====<br>\n";
									$myWhereRef.=" LEFT JOIN ".$curfield->ref_table." as ".$myRefTable." ON (".$myRefTable.".".$curfield->ref_key."=".$curfield->table_name.".".$curfield->ref_label." AND ".$myRefTable.".".$child["child_key"]."=".$curfield->table_name.".".$child["parent_key"]." ) ";
									$child_found=true;
								}
							}
						}
					}
				if(!$child_found)
					$myWhereRef.=" LEFT JOIN ".$curfield->ref_table." as ".$myRefTable." ON (".$myRefTable.".".$curfield->ref_key."=".$curfield->table_name.".".$curfield->ref_label." AND ".$myRefTable.".".$this->key_name()."=".$curfield->table_name.".".$this->key_name()." ) ";
				$debug=false;
			}
			if(($curfield->type=="user" || $curfield->type=="user_creation" || $curfield->type=="group"))
				if( $curfield->ref_key!="" && $curfield->ref_table!="" && $curfield->ref_label!="" )
					$myWhereRef.=" LEFT JOIN ".$curfield->ref_table." as ".$myRefTable." ON ".$myRefTable.".".$curfield->ref_key."=".$curfield->table_name.".".$curfield->field_name." ";
		}
		if($debug) echo "wherehref ".$myWhereRef."<br>\n";
		return $myWhereRef;
	}

	function _build_query($theSort,$theOrder,$thePage,$theFiltersCompulsory,$count_query=false,$limiteless_search=false)
	{
		// Get list of refrerenced tables
		$myTablesList[]=$this->_tablename;
		/*
		foreach($this->_fieldslist as $curfield)
		{
			if(!in_array($curfield->table_name,$myTablesList))
				$myTablesList[]=$curfield->table_name;
			if($curfield->type=="reference" && !in_array($curfield->ref_table,$myTablesList))
				$myTablesList[]=$curfield->ref_table;
		}
		$myTxtListTables=implode(",",$myTablesList);
		*/
		// BEgin query

		if(!$count_query)
			$myQuery="SELECT * FROM ".$this->_tablename;
		else
			$myQuery="SELECT COUNT(*) as nbocc FROM ".$this->_tablename;

		// Get sort field information and build groupe by
		//echo __FUNCTION__."@".__LINE__." ==> ".$theSort." / ".$theOrder."<br>\n";
		$myCurFieldSort=$this->_get_field_from_name($theSort);
		$myGroup=" GROUP BY ".$this->_tablename.".".$this->_key." ";
		if($myCurFieldSort!==false)
			$myRefTable=$myCurFieldSort->field_name.
							"_".$myCurFieldSort->ref_table;
		// Get sort field info and build order by
		if($myCurFieldSort===false)
		{
			$myOrder=" ORDER BY ".$this->_tablename.".".$this->_key." ASC ";
		}
		elseif($myCurFieldSort->type=="reference")
			$myOrder=" ORDER BY ".$myRefTable.".".$myCurFieldSort->ref_label." ".$theOrder;
		elseif(strpos($myCurFieldSort->type,"foreign")===0)
			$myOrder=" ORDER BY ".$myRefTable.".".$myCurFieldSort->field_name." ".$theOrder;
		else
			$myOrder=" ORDER BY ".$myCurFieldSort->table_name.".".$myCurFieldSort->field_name." ".$theOrder;

		$myLimit="";
		if($this->_nbperpage>0 && !$count_query && !$limiteless_search)
		{
	    	$myLimitBegin=($thePage-1)*$this->_nbperpage;
	    	$myLimitEnd=$this->_nbperpage;
	    	$myLimit.=" LIMIT ".$myLimitBegin.",".$myLimitEnd;
		}

		// Get real Where clause
		$myWhere = $this->_build_filter($theFiltersCompulsory);

		// Get Reference Join Tables
		$myRefJoin=$this->_build_ref_join();

		// Concatenate all, exit
		if(!$count_query)
			$myQuery=$myQuery.$myRefJoin.$myWhere.$myGroup.$myOrder.$myLimit;
		else
			$myQuery=$myQuery.$myRefJoin.$myWhere;
		//echo "Requête : ".Tools::Display($myQuery);
		return $myQuery;
	}

	function clear()
	{
		unset($this->_records);
		$this->_records=array();
		$this->_pointer=0;
		$this->_clear_current();
		$this->_set_current_to_record();
	}

	function binddata($theArray)
	{
		$debug=false;
		if($debug) echo __FUNCTION__."@".__LINE__."<br>\n";
		if(count($this->_fieldslist)>0)
		{
			$this->_clear_current();
			foreach($this->_fieldslist as $myCurField)
			{
				$myFieldName=$myCurField->field_name;
				if($debug && isset($theArray[$myFieldName])) echo "Contenu de \$_POST[\$myFieldName]=".$theArray[$myFieldName]."<br>\n";
				if($debug) echo "Champ : ".$myFieldName.", type:".$myCurField->type."<br>\n";
				if($myCurField->type=="file" || $myCurField->type=="image")
				{
					unset($this->$myFieldName);
					if(isset($_FILES[$myFieldName]) && $_FILES[$myFieldName]["tmp_name"]!="")
					{
						$myFileName=new stdClass();
						$myFileName->original=$_FILES[$myFieldName]["name"];
						$myFileName->ondisk=Tools::GetRandomFileName($myFileName->original,$this->_files_path);
						$this->$myFieldName=serialize($myFileName);
						if($debug) echo "Champ ".$myFieldName." vaut ".$this->$myFieldName."<br>\n";
						$myReturn = move_uploaded_file($_FILES[$myFieldName]["tmp_name"],$this->_files_path.$myFileName->ondisk);
						if($debug) echo "Copie de ".$_FILES[$myFieldName]["tmp_name"]." vers ".$this->_files_path.$myFileName->ondisk." : ".(($myReturn)?"ok":"pas ok")."<br>\n";
					}
				}
				else
				{
					if(isset($theArray[$myFieldName]))
					{
						$myFieldValue=$theArray[$myFieldName];
						//echo "Traitement du champ ".$myFieldName." de Type : ".$myCurField->type."<br>\n";
						switch($myCurField->type)
						{
							case "bits":
								$myBitVal=0;
								foreach($myCurField->ref_table as $key=>$keyval)
								{
									if(isset($theArray[$myFieldName."_".$key]))
									{
										//echo "Valeur du champ ".$myFieldName."_".$key." : ".$theArray[$myFieldName."_".$key]."<br>\n";
										$myCheckValue=$theArray[$myFieldName."_".$key];
										if($myCheckValue==1)
											$myBitVal+=$key;
									}
								}
								$myFieldValue=$myBitVal;
								$this->$myFieldName=$myFieldValue;
								if($myFieldName==$this->_key)
								{
									if(intval($this->$myFieldName)<=0)
										$this->$myFieldName=null;
								}
								break;
							case "password":
								if(trim($theArray[$myFieldName])!="")
								{
									if($theArray[$myFieldName]!=$theArray[$myFieldName."_check"])
									{
										$this->_messages[]=Tools::Translate("Les mots de passe sont diff&eacute;rents, mise &agrave; jour du mot de passe annul&eacute;e");
										unset($this->$myFieldName);
									}
									else
										$this->$myFieldName=md5($myFieldValue);
								}
								if(isset($this->$myFieldName) && trim($this->$myFieldName)=="")
									unset($this->$myFieldName);
								break;
							case "checkboxlist_multiple":
							case "combolist_multiple":
								$this->$myFieldName=serialize($myFieldValue);
								//echo "Sauvegarde du champ ".$myFieldName.":".$this->$myFieldName."<br>\n";
								break;
							case "date":
							case "shortdateauto_session":
							case "shortdateauto":
								$myEnv=new TT_Env();
								$myDatePref=$myEnv->DatePref;
								$myFieldValue=Tools::DateUserToSQL($myDatePref,$myFieldValue);
								if($myCurField->type=="shortdateauto_session")
								{
									$_SESSION[$myCurField->ref_key]=$myFieldValue;
									//echo "Date sauvegardée : ".$myFieldValue." vers la variable de session ".$myCurField->ref_key." de valeur ".$_SESSION[$myCurField->ref_key]."<br>\n";
								}
							default:
								$this->$myFieldName=$myFieldValue;
								if($myFieldName==$this->_key)
								{
									if(intval($this->$myFieldName)<=0)
										$this->$myFieldName=null;
								}
								break;
						}
					}
					else
					{
						if($myFieldName==$this->_key)
							$this->$myFieldName=null;
						else
						{
							if(isset($theArray[$myFieldName]))
								$this->$myFieldName=$theArray[$myFieldName];
						}
					}
				}
				/*
				if($myCurField->type=="callback")
				{
					unset($this->$myFieldName);
				}
				if(strpos($myCurField->type,"foreign")===0)
				{
					unset($this->$myFieldName);
				}
				*/
			}
			$this->_clean_external_fields_from_current();
			return true;
		}
		return false;
	}

	function _clean_external_fields_from_current()
	{
		if(count($this->_fieldslist)>0)
		{
			foreach($this->_fieldslist as $myCurField)
			{
				$myFieldName=$myCurField->field_name;
				if($myCurField->type=="callback")
				{
					unset($this->$myFieldName);
				}
				if(strpos($myCurField->type,"foreign")===0)
				{
					unset($this->$myFieldName);
				}
			}
		}
	}

	function addnew()
	{
		$this->_set_current_to_record();
		$this->_pointer=count($this->_records);
		$this->_clear_current();
		$this->_set_current_to_record();
	}

	function record_save()
	{
		$this->_set_current_to_record();
		$this->_clean_external_fields_from_current();
		//if($this->_tablename=="#__operation_prelevement_biologique")
		//	echo "Objet : ".Tools::Display($this);
		$myReturn = $this->store();
		if(!$myReturn)
		{
			//echo "Erreur : ".$this->_db->getErrorMsg()."<br>\n";
			//echo "Requête d'enregistrement : ".$this->_db->getQuery();
		}
		else
		{
			//echo "Requête : ".$this->_db->getQuery();
			//echo "Clef : ".$this->_tbl_key."<br>\n";
			//echo "Valeur de la clef : ".$this->id_point_de_prelevement."/".$this->key_value()."<br />\n";
			//$this->_set_record_to_current();
			//echo "Ref : ".Tools::Display($this->_reference)."<br>\n";
			if(isset($this->_reference->child_type) && $this->_reference->child_type=="relative")
			{
				$myObj=new stdClass();
				$this->_db->setQuery("SELECT * FROM ".$this->_reference->child_rel." WHERE ".$this->_reference->child_key."=".$this->key_value()." AND ".$this->_reference->parent_key."=".$this->_reference->key_value);
				if(!$this->_db->loadObject($myObj))
				{
					$child_key=$this->_reference->child_key;
					$parent_key=$this->_reference->parent_key;
					$myObj->$child_key=$this->key_value();
					$myObj->$parent_key=$this->_reference->key_value;
					$this->_db->insertObject($this->_reference->child_rel,$myObj);
				}
			}
		}
		return $myReturn;
	}

	function move_next()
	{
		if(!$this->iseof())
		{
			$this->_pointer++;
			$this->_set_record_to_current();
			return true;
		}
		else
			return false;
	}

	function update()
	{
		$this->_set_current_to_record();
	}

	function move_prev()
	{
		if(!$this->isbof())
		{
			$this->_pointer--;
			$this->_set_record_to_current();
			return true;
		}
		else
			return false;
	}

	function move_first()
	{
		$this->_pointer=0;
		return $this->_set_record_to_current();
	}

	function move_last()
	{
		$this->_pointer=$this->lastindex();
		return $this->_set_record_to_current();
	}

	function isloaded()
	{
		$myKey=$this->_key;
		if(is_null($this->$myKey))
			return false;
		return true;
	}

	function isbof()
	{
		if($this->_pointer==0)
			return true;
		return false;
	}

	function iseof()
	{
		$myCount=count($this->_records)-1;
		if($this->_pointer==$myCount)
			return true;
		return false;
	}

	function delete($theId)
	{
		//echo "Effacement ".Tools::Display($this)."<br>\n";
		if(isset($this->_reference->child_type) && $this->_reference->child_type=="relative")
		{
			$myDelQuery="DELETE FROM ".$this->_reference->child_rel." WHERE ".$this->_reference->child_key."=".$theId." AND ".$this->_reference->parent_key."=".$this->_reference->key_value;
			$this->_db->setQuery($myDelQuery);
			//echo "Requête effacement relation : ".$this->_db->getQuery();
			$this->_db->query();
		}
		$myReturn=parent::delete($theId);
		//echo "Requête effacement global : ".$this->_db->getQuery();
		return $myReturn;
	}

	function record_delete()
	{
		$myKey=$this->_key;
		if(!is_null($this->$myKey))
		{
			$this->delete();
			unset($this->_records[$this->_pointer]);
			if($this->_pointer>0)
				$this->_pointer--;
			$this->_set_record_to_current();
		}
	}

	function load($theId)
	{
		$this->clear();
		$myReturn=parent::load($theId);
		if($myReturn)
			$this->_set_current_to_record();
		return $myReturn;
	}

	function get_page_count()
	{
		$myLowPage=intval($this->_result_nb_rows/$this->_nbperpage);
		if(($myLowPage*$this->_nbperpage)<$this->_result_nb_rows)
			$myLowPage++;
		return $myLowPage;
	}

	function get_row_count()
	{
		return $this->_result_nb_rows;
	}

	function record_search($theSort,$theOrder,$thePage=1,$theFiltersCompulsory=false,$theLimitlessSearch=false)
	{
		$debug=false;

		$this->_result_nb_rows=0;
		$myQuery=$this->_build_query($theSort,$theOrder,$thePage,$theFiltersCompulsory,false,$theLimitlessSearch);
		$myQueryCount=$this->_build_query($theSort,$theOrder,$thePage,$theFiltersCompulsory,true,$theLimitlessSearch);
		//echo "Requête de compte : <pre>".$myQueryCount."</pre>".BR;
		//echo "Requête de recherche : <pre>".$myQuery."</pre>".BR;
		if($myQuery!==false)
		{
			$this->_db->setQuery($myQueryCount);
			//echo "Requête de compte : ".$this->_db->getQuery();
			$myCount=null;
			$myCount=$this->_db->loadObjectList();
			if(is_array($myCount) && count($myCount)>0)
			{
				$this->_result_nb_rows=$myCount[0]->nbocc;
				$this->clear();
				$this->_db->setQuery($myQuery);
				//if(isset($this->_reference->table) && isset($this->_reference->key_value))
				if($debug) //*/
					echo __FUNCTION__."@".__LINE__." => Requête de recherche : ".$this->_db->getQuery();
				$this->_records=$this->_db->loadObjectList();
				if($debug) echo "Erreur:  ".$this->_db->_errorMsg;
				$this->_pointer=0;
				$return=$this->_set_record_to_current();
				if($debug) echo __FUNCTION__."@".__LINE__."=> Retour : ".$return."<br>\n";
			}
		}
	}

	function key_name()
	{
		return $this->_key;
	}

	function key_value()
	{
		$myKey=$this->_key;
		if(!isset($this->$myKey))
			return false;
		return $this->$myKey;
	}

	function field_display($theFieldName)
	{
		if(count($this->_fieldslist)>0)
			foreach($this->_fieldslist as $myCurField)
			{
				if($myCurField->field_name==$theFieldName)
				{
					if(!isset($this->$theFieldName))
						$this->$theFieldName="";
					return $myCurField->get_value($this,$this->$theFieldName);
				}
			}
		return "";
	}

	function field_form($theFieldName,$default=null)
	{
		if(count($this->_fieldslist)>0)
			foreach($this->_fieldslist as $myCurField)
			{
				if($myCurField->field_name==$theFieldName)
				{
					if(!is_null($default))
						return $myCurField->get_field($this,$default);
					else
						return $myCurField->get_field($this,$this->$theFieldName);
				}
			}
		return "";
	}

	function hidden_field_form($theFieldName)
	{
		if(count($this->_fieldslist)>0)
			foreach($this->_fieldslist as $myCurField)
			{
				if($myCurField->field_name==$theFieldName)
				{
					return $myCurField->get_hidden_field_value($this,$this->$theFieldName);
				}
			}
		return "";
	}


	function gotokey($theKeyValue)
	{
		if(count($this->_records)>0)
		{
			$myKey=$this->_key;
			foreach($this->_records as $key=>$currecord)
			{
				if($theKeyValue==$currecord->$myKey)
				{
					$this->_pointer=$key;
					$this->_set_record_to_current();
					return true;
				}
			}
		}
		return false;
	}

	function filter_get($theFilterField)
	{
		if(isset($this->_filters[$theFilterField]))
			return $this->_filters[$theFilterField]["value"];
		return "";
	}

	function sql_filter_get($name)
	{
		if(isset($this->_sql_filters[$name]))
			return $this->_sql_filters[$name];
		return "";
	}

	function sql_filter_set($name,$filter)
	{
		$this->_sql_filters[$name]=$filter;
	}

	function sql_filter_clear($name)
	{
		if(isset($this->_sql_filters[$name]))
			unset( $this->_sql_filters[$name] );
	}

	function filter_set($theFilterField="",$theFilterValue="",$theSearchLarge=true)
	{
		$debug=false;
		if($debug) echo htmlentities(__LINE__." Ajout filtre ".$theFilterField."=".$theFilterValue." obligatoire : ".(($theSearchLarge)?"non":"oui") )."...\n";
		if(isset($this->_filters[$theFilterField]))
		{
			$this->_filters[$theFilterField]["value"]=$theFilterValue;
			$this->_filters[$theFilterField]["large"]=$theSearchLarge;
			if($debug) echo "ok<br>\n";
			return true;
		}
		if($debug) echo "erreur<br>\n";
		return false;
	}

	function filter_clear($theFilterField="")
	{
		if(isset($this->_filters[$theFilterField]))
		{
			$this->_filters[$theFilterField]["value"]="";
			$this->_filters[$theFilterField]["large"]=true;
			return true;
		}
		return false;

	}

	function filter_clearall()
	{
		if(count($this->_filters)>0)
		{
			foreach($this->_filters as $keyfilter=>$curfilter)
			{
				$this->_filters[$keyfilter]["value"]="";
				$this->_filters[$keyfilter]["large"]=true;
			}
		}
		return false;
	}

	function curindex()
	{
		return $this->_pointer;
	}

	function curkey()
	{
		$myKey=$this->_key;
		return $this->$myKey;
	}

	function count()
	{
		return count($this->_records);
	}

	function lastindex()
	{
		return count($this->_records)-1;
	}
}


class mdtb_field
{
	var $ID=-1;
	var $table_name;
	var $field_name="";
	var $type="";
	var $ref_table="";
	var $ref_key="";
	var $ref_label="";
	var $view_list=1;
	var $view_form=1;
	var $view_detail=1;
	var $view_menu=0;

	var $_listvalues=array();
	var $_date_format="d/m/Y";
	var $_full_date_format="d/m/Y H:i:s";
	var $_db;
	var $_path_pre;
	var $_user_group;
	var $_user_ID;

	function mdtb_field(&$database,$path_pre="",$user_group=-1,$user_ID)
	{
		$this->_db=$database;
		$this->_path_pre=$path_pre;
		$this->_user_group=$user_group;
		$this->_user_ID=$user_ID;
	}

	function set_list($theList)
	{
		if(is_array($theList))
		{
			$this->_listvalues=$theList;
			return true;
		}
		else
			$this->_listvalues=array();

		return false;
	}

	function get_value($parent,$value)
	{
		global $ThePrefs;
		if(!isset($ThePrefs->encoding)) $ThePrefs->encoding="ISO-8859-1";
		switch($this->type)
		{
			case "user_creation":
			case "user":
				$myKey=$this->ref_label;
				$myUser=new users($this->_db);
				$myLoadedUser=$this->_user_ID;
				if(intval($value)>0)
					$myLoadedUser=$value;
				if($myUser->load($myLoadedUser))
					$myField=$myUser->$myKey;
				else
					$myField=$myLoadedUser;
				return $myField;
				break;
			case "callback":
				$method_name="callback_".$this->field_name."_value";
				if(method_exists($parent->_mdtb,$method_name))
				{
					return $parent->_mdtb->$method_name($this,$value);
				}
				else
					return "";
				break;
			case "checkbox":
				if($value==1)
				{
					return "<img border=\"0\" src=\"".$this->_path_pre.$parent->_mdtb->_images_path."ok.gif\" />\n";
				}
				break;
			case "password":
				return Tools::Translate("(mot de passe crypt&eacute;)");
				break;
			case "image":
			case "file":
				$myFile=unserialize($value);
				if($myFile!==false)
				{
					$myRecKey=$parent->_key;
					$myHref=$parent->_mdtb->get_href("detail",$parent->$myRecKey);
					$myFieldPrefix="";
					if($parent->_mdtb->ischild())
						$myFieldPrefix="child_";
					$myHref.="&".$myFieldPrefix."action=download&".$myFieldPrefix."itemlist=".$this->field_name;
					return "<a target=\"_blank\" href=\"".$myHref."\">".$myFile->original."</a>";
				}
				else
					return "";
				break;
			case "bits":
				$myReturn="";
				$myReturn .= "<table class='bitslist bitlabel".$this->field_name."' cellspacing='0'><tr>";
				foreach($this->ref_table as $key=>$keyval)
				{
					$cur_checked=($value & $key)?1:0;
					$myReturn.=(($cur_checked==1)?("<td><img src='images/ok.gif'>\n<span class='bitlabel'>".$keyval."</span></td>"):"<td>&nbsp;</td>");
					//echo $keyval." : ".$cur_checked."/".$value."<br>\n";
				}
				$myReturn.="</tr></table>\n";
				return $myReturn;
				break;
			case "readonlylist":
				if(isset($this->ref_table[$value]))
					return htmlentities($this->ref_table[$value]);
				else
					return htmlentities($value);
				break;

			case "combolist":
				if(isset($this->ref_table[$value]))
					return htmlentities($this->ref_table[$value]);
				else
					return htmlentities($value);
				break;
			case "checkboxlist_multiple":
			case "combolist_multiple":
				$default_array=$value;
				if(!is_array($value) && $value!="")
					$default_array=unserialize($value);
				if(!is_array($default_array) && $value=="")
					$default_array=array();
				$myReturn="";
				foreach($default_array as $curelement)
				{
					if(isset($this->ref_table[$curelement]))
						$myReturn.=($myReturn!=""?", ":"").htmlentities($this->ref_table[$curelement]);
				}
				return $myReturn;
				break;
			case "float":
				$myVal=floatval(str_replace(",",".",$value));
				return round($myVal,$this->ref_table);
				break;
			case "currency":
				$value=round(floatval($value),2);
			default:
			case "foreign":
			case "time":
			case "text":
			case "wbdlist":
			case "number":
				return htmlentities($value);
				break;
			case "longtext":
				return str_replace("\n","<br />\n",htmlentities($value));
				break;
			case "html":
				return $value;
				break;
			case "dateauto_creation":
				if(trim($value)=="")
					return htmlentities($value);
				if( trim($value)=="0000-00-00 00:00:00" || trim($value)=="0000-00-00")
					return htmlentities("");

				$myDate=@strtotime($value);
				if($myDate===false || $myDate===-1)
					return htmlentities($value);
				else
					return date($this->_full_date_format,$myDate);
				break;
				break;
			case "date":
				if(trim($value)=="" || trim($value)=="0000-00-00 00:00:00" || trim($value)=="0000-00-00")
					return "";

				$myDate=@strtotime($value);
				if($myDate===false || $myDate===-1)
					return htmlentities($value);
				else
					return date($this->_date_format,$myDate);
				break;
			case "dateauto":
				if(trim($value)=="" || trim($value)=="0000-00-00 00:00:00" || trim($value)=="0000-00-00")
					return htmlentities($value);

				$myDate=@strtotime($value);
				if($myDate===false || $myDate===-1)
					return htmlentities($value);
				else
					return date($this->_date_format,$myDate);
				break;
			case "shortdateauto_session":
				if(trim($value)=="" || trim($value)=="0000-00-00 00:00:00" || trim($value)=="0000-00-00")
					return htmlentities($value);

				$myDate=@strtotime($value);
				if($myDate===false || $myDate===-1)
					return htmlentities($value);
				else
					return date($this->_date_format,$myDate);
				break;
			case "foreign_shortdate":
			case "shortdate":
			case "shortdateauto":
				if(trim($value)=="" || trim($value)=="0000-00-00 00:00:00" || trim($value)=="0000-00-00")
					return htmlentities($value);

				$myDate=@strtotime($value);
				if($myDate===false || $myDate===-1)
					$myReturn=htmlentities($value);
				else
					$myReturn=date($this->_date_format,$myDate);
				return $myReturn;
				break;
			case "groupmember":
				$myValList=array();
				$myClsUsers=new clsUsers($this->_db,$this->_path_pre,"",$this->_user_group);
				$myList=$myClsUsers->getUsersListForGroup($this->_user_group,false);
				if(is_array($myList) && count($myList)>0)
				{
					foreach($myList as $key=>$curuser)
					{
						if($curuser->ID==$value)
						{
							return htmlentities($curuser->loginname);
						}
					}
				}
				break;
			case "reference":
				$myFieldReference=new mdtb_fieldreference($this->_db,$this,$value);
				return htmlentities(Tools::Translate(stripslashes($myFieldReference->value)));
				break;
		}
	}

function get_field($parent,$default="")
{
	$myField="";
	switch($this->type)
	{
		case "foreign":
			$myField=$default;
			break;
		case "readonly_text":
			$myField = $default.TT_Template::FORM_GetHidden($this->field_name,$default);
			break;
		case "callback":
			$method_name="callback_".$this->field_name."_field";
			if(method_exists($parent->_mdtb,$method_name))
			{
				return $parent->_mdtb->$method_name($this,$default);
			}
			else
				return "";
			break;
		case "reference":
			switch($this->subtype)
			{
				case "long":
					$myField="<div style='border:1px solid #C0C0C0;width: 80%;height:18px;overflow:auto;' id='lbl_".$this->field_name."'></div>\n";
					$myField.="<input type='hidden' name='".$this->field_name."' id='".$this->field_name."' value='".$default."' />";
					//$this->field->ref_label=="" || $this->field->ref_key=="" || $this->field->ref_table==""
					$myField.="<a href=\"".$parent->_mdtb->get_ajaxselectlist($this)."\" class=\"thickbox\">".Tools::Translate("Sélectionner")."</a>";
					//$myField.="<a href=\"".$parent->_mdtb->get_href("ajaxselectlist",-1)."\" class=\"thickbox\">".Tools::Translate($this->field_name)."</a>";
					break;
				default:
					$myFieldReference=new mdtb_fieldreference($this->_db,$this,$default);
					$myRefList=$myFieldReference->reflist();
					$myDefaultOption=array("id"=>"0","value"=>Tools::Translate("(choisissez une valeur)"));
					$myRefList[]=$myDefaultOption;
					if($default=="")
						$default="0";
					foreach($myRefList as $key=>$val)
						$myRefList[$key]["value"]=Tools::Translate(stripslashes($myRefList[$key]["value"]));
					//echo "Défaut : ".$default."<br>\nListe : ".Tools::Display($myRefList,true);
					//$myField=mdtb_forms::combolist($this->field_name,$myRefList,$default,"mdtb_form_combofield");
					$myField=TT_Template::FORM_GetListCombo($this->field_name,$myRefList,$default,"","mdtb_form_combofield");
					break;
			}
			break;
		case "image":
		case "file":
			$myFile=unserialize($default);
			if($myFile===false)
			{
				$myFile=new stdClass();
				$myFile->original="";
				$myFile->ondisk="";
			}
			$myField=mdtb_forms::file($this->field_name,$myFile->original,$myFile->ondisk,"mdtb_form_filefield");
			break;
		case "html":
			$myField=TT_Template::FORM_GetHtmlEditor($this->_path_pre,$this->field_name,$default,"","mdtb_form_htmlfield");
			break;
		case "longtext":
			$myField=TT_Template::FORM_GetLongText($this->field_name,$default,"","mdtb_form_textfield"); //mdtb_forms::longtext($this->field_name,$default,"mdtb_form_textfield");
			break;
		case "date":
			$myEnv=new TT_Env();
			$myDatePref=$myEnv->DatePref;
			$myDate=$default;
			//if($default=="") $default=date("Y-m-d H:i:s");
			if(trim($default)!="" && trim($default)!="0000-00-00 00:00:00"  && trim($default)!="0000-00-00")
			{
				$myTstDate=@strtotime($default);
				if($myTstDate===false)
					$myDate=""; //date($myDatePref->DispFormat);
				else
					$myDate=date($myDatePref->DispFormat,$myTstDate);
				$default=$myDate;
			}
			else
				$default="";
			//$myField="<input type=\"text\" class=\"mdtb_form_textfield\" name=\"".$this->field_name."\" value=\"".$default."\" >";
			$myField=TT_Template::FORM_GetDate($this->field_name,$default); //($theName,$theDefault="",$theParams="",$theClass="",$theFormat="",$theDateInit=true)
			break;
		case "bits":
			$myField  = TT_Template::FORM_GetHidden($this->field_name,$default);
			$myField .= "<table class='bitslist bitlabel".$this->field_name."' cellpadding='0'><tr>";
			foreach($this->ref_table as $key=>$value)
			{
				$cur_checked=($default & $key)?1:0;
				$myField.="<td><label>".TT_Template::FORM_GetCheckbox($this->field_name."_".$key,1,$cur_checked)."&nbsp;<span class='bitlabel'>".$value."</span></label>&nbsp;</td>";
			}
			$myField.="</tr></table>\n";
			break;
		case "checkbox":
			$myField=TT_Template::FORM_GetCheckbox($this->field_name,1,$default);
			break;
		case "dateauto_modification":
			$myField=TT_Template::FORM_GetHidden($this->field_name,date("Y-m-d H:i:s"));
			break;
		case "dateauto_creation":
			if(trim($default)=="" || is_null($default) ) $default=date("Y-m-d H:i:s");
			$myDate=$default;
			$myTstDate=strtotime($default);
			if($myTstDate===false)
				$myDate=date("Y-m-d H:i:s");
			$myField=TT_Template::FORM_GetHidden($this->field_name,$myDate);
			break;
		case "dateauto":
			if(trim($default)=="" || is_null($default) ) $default=date("Y-m-d H:i:s");
			$myDate=$default;
			$myTstDate=strtotime($default);
			if($myTstDate===false)
				$myDate=date("Y-m-d H:i:s");
			else
				$myDate=date("Y-m-d",$myTstDate);
			$myField=TT_Template::FORM_GetText($this->field_name,$myDate);
			break;
		case "shortdate":
			$myEnv=new TT_Env();
			$myDatePref=$myEnv->DatePref;
			$myDate=$default;
			if($default=="") $myDate=$default;
			else
			{
				$myTstDate=strtotime($default);
				if($myTstDate===false)
					$myDate="";
				else
					$myDate=date($myDatePref->DispFormat,$myTstDate);
			}
			$myField=TT_Template::FORM_GetDate($this->field_name,$myDate);
			break;
		case "shortdateauto":
			$myEnv=new TT_Env();
			$myDatePref=$myEnv->DatePref;
			$myDate=$default;
			if($default=="") $default=date("Y-m-d H:i:s");
			$myTstDate=strtotime($default);
			if($myTstDate===false)
				$myDate=date($myDatePref->DispFormat);
			else
				$myDate=date($myDatePref->DispFormat,$myTstDate);
			$myField=TT_Template::FORM_GetDate($this->field_name,$myDate);
			break;
		case "shortdateauto_session":
			$default="";
			if($this->ref_key!="")
				if(isset($_SESSION[$this->ref_key]))
					$default=$_SESSION[$this->ref_key];
			$myEnv=new TT_Env();
			$myDatePref=$myEnv->DatePref;
			$myDate=$default;
			if($default=="") $default=date("Y-m-d H:i:s");
			$myTstDate=strtotime($default);
			if($myTstDate===false)
				$myDate=date($myDatePref->DispFormat);
			else
				$myDate=date($myDatePref->DispFormat,$myTstDate);
			$myField=TT_Template::FORM_GetDate($this->field_name,$myDate);
			break;
		case "list_auto":
			$myCurFieldName=$this->field_name;
			$parent->_db->setQuery("SELECT * FROM ".$parent->_tablename." GROUP BY ".$myCurFieldName." ORDER BY ".$myCurFieldName." ASC;");
			$myFieldsList=$parent->_db->loadObjectList();
			$myRefTable=array();
			$myRefTable[""]="";
			if($myFieldsList!==false && count($myFieldsList)>0)
				foreach($myFieldsList as $curfieldvalue)
					$myRefTable[$curfieldvalue->$myCurFieldName]=$curfieldvalue->$myCurFieldName;
			//echo "Ref table pour le champ ".$myCurFieldName.":".Tools::Display($myRefTable);
			$myField=TT_Template::FORM_GetText($this->field_name,$default,"","");
			$myField.=TT_Template::FORM_GetArrayCombo("cmb_".$this->field_name,$myRefTable," "," onChange=\"document.getElementById('".$this->field_name."').value=this.value;\" ","mdtb_form_combofield");
			break;
		case "list":
			if(isset($this->ref_table[$default]))
				$myFieldVal=$this->ref_table[$default];
			else
				$myFieldVal=$default;

			$myField=TT_Template::FORM_GetText($this->field_name,$myFieldVal,"","");
			$myField.=TT_Template::FORM_GetArrayCombo("cmb_".$this->field_name,$this->ref_table," "," onChange=\"document.getElementById('".$this->field_name."').value=this.value;\" ","mdtb_form_combofield");
			break;
		case "password":
			$myField  = TT_Template::FORM_GetPass($this->field_name,"","","")."<br>\n";
			$myField .= TT_Template::FORM_GetPass($this->field_name."_check",""," onblur=\"if(document.getElementById('".$this->field_name."').value!=document.getElementById('".$this->field_name."_check').value) { document.getElementById('passerror').style.display='block';} else { document.getElementById('passerror').style.display='none';}\" ","");
			$myField .= "<div id=\"passerror\" style=\"border:1px solid #EA0000; display:none;\">".Tools::Translate("Erreur de mot de passe : les deux mots de passe ne sont pas identiques")."</div>\n";
			break;
		case "combolist":
			$myField.=TT_Template::FORM_GetArrayCombo($this->field_name,$this->ref_table,$default,"","mdtb_form_combofield");
			break;
		case "checkboxlist_multiple":
			if($this->ref_key=="")
				$myMaxChecked=0;
			else
				$myMaxChecked=intval($this->ref_key);
			$default_array=$default;
			if(!is_array($default) && $default!="")
				$default_array=unserialize($default);
			if(!is_array($default_array) || $default=="")
				$default_array=array();
			$myField ="";
			if($myMaxChecked>0)
			{
				$myField .="
					<script type=\"text/javascript\">
						function checknumber_".$this->field_name."()
						{
							var theFieldName='".$this->field_name."[]';\n							var occ_max=".$myMaxChecked.";
							var arr_fields=document.getElementsByName(theFieldName);
							var nb_occ=0;
							for(i=0;i<arr_fields.length;i++)
							{
								if(arr_fields[i].checked)
									nb_occ++;
							}
							if(nb_occ>occ_max) { alert('Nombre maximum de selections ('+occ_max+') depasse'); return false; }
							return true;
						}
					</script>
						";
			}
			$myField .= "<div class=\"checkboxlist ".$this->field_name."\">\n";
			//echo "Table enregistrée : ".Tools::Display($default_array);
			//echo "Ref Table enregistrée : ".Tools::Display($this->ref_table);
			$myField .= "<ul class='checkboxlist checkboxlabel".$this->field_name."'>\n";
			foreach($this->ref_table as $key=>$curelement)
			{
				$curchecked="";
				if(in_array($key,$default_array))
					$curchecked=$key;
				// FORM_GetCheckbox($theName,$theDefault="",$theValue="",$theParams="",$theClass="")
				$myField .= "<li>".TT_Template::FORM_GetCheckbox($this->field_name."[]",$key,$curchecked," onclick=\"return checknumber_".$this->field_name."();\"","mdtb_form_combofield")."&nbsp;<span class='checkboxlabel'>".$curelement."</span></li>";
			}
			$myField .= "</ul>\n";
			$myField .= "</div>\n";
			break;
		case "combolist_multiple":
			$default_array=$default;
			if(!is_array($default) && $default!="")
				$default_array=unserialize($default);
			if(!is_array($default_array) || $default=="")
				$default_array=array();
			$myField.=TT_Template::FORM_GetArrayComboList($this->field_name,$this->ref_table,$default_array,4,true,"","mdtb_form_combofield");
			break;
		case "wbdlist":
			$myField=TT_Template::FORM_GetText($this->field_name,$default,"","");
			if(class_exists("list_manager"))
			{
				$myWbd=new list_manager($this->_db,$this->_path_pre,"",$this->_user_group);
				$myList=$myWbd->list_items($this->ref_table);
				$myList["-1"]=" ";
				if($myList!==false)
				{
					foreach($myList as $key=>$val) $myValList[$val]=$val;
					$myField.=TT_Template::FORM_GetArrayCombo("cmb_".$this->field_name,$myValList," "," onChange=\"document.getElementById('".$this->field_name."').value=this.value;\" ","mdtb_form_combofield");
				}
			}
			break;
		case "time":
			$myHourList=array();
			$myMinList=array();
			for($i=1;$i<=23;$i++)
			{
				$myTxt=(($i<10)?"0":"").strval($i);
				$myHourList[$myTxt]=$myTxt;
			}
			for($i=0;$i<=55;$i+=5)
			{
				$myTxt=(($i<10)?"0":"").strval($i);
				$myMinList[$myTxt]=$myTxt;
			}
			$default=str_replace("h",":",$default);
			$myEnv=new TT_Env();
			$curhour=$myEnv->Calendar->DayBegin; $curmin="00";
			if(strstr($default,":")!==FALSE)
				list($curhour,$curmin)=explode(":",$default);
			$myField ="<script type=\"text/javascript\">function ".$this->field_name."_setTime() { document.getElementById('".$this->field_name."').value=document.getElementById('".$this->field_name."_hour').value+':'+document.getElementById('".$this->field_name."_min').value;}</script>\n";
			$myField.=TT_Template::FORM_GetArrayCombo($this->field_name."_hour",$myHourList,$curhour," onblur=\"".$this->field_name."_setTime();\" onclick=\"".$this->field_name."_setTime();\"","mdtb_form_combofield");
			$myField.=TT_Template::FORM_GetArrayCombo($this->field_name."_min",$myMinList,$curmin," onblur=\"".$this->field_name."_setTime();\" onclick=\"".$this->field_name."_setTime();\"","mdtb_form_combofield");
			$myField.=TT_Template::FORM_GetHidden($this->field_name,$default);
			break;
		case "groupmember":
			$myValList=array();
			$myClsUsers=new clsUsers($this->_db,$this->_path_pre,"",$this->_user_group);
			$myList=$myClsUsers->getUsersListForGroup($this->_user_group,false);
			if(is_array($myList) && count($myList)>0)
			{
				foreach($myList as $key=>$curuser)
					$myValList[$curuser->ID]=$curuser->loginname;
			}
			$myField=TT_Template::FORM_GetArrayCombo($this->field_name,$myValList,$default,"","mdtb_form_combofield");
			break;
		case "readonlylist":
			if(isset($this->ref_table[$default]))
				$myField=$this->ref_table[$default];
			else
				$myField=$default;

			$myField.=TT_Template::FORM_GetHidden($this->field_name,$default);
			break;
		case "currency":
			$default=round(floatval($default),2);
		case "text":
		case "number":
		default:
			$myField=TT_Template::FORM_GetText($this->field_name,$default,"","mdtb_form_textfield");
			//$myField=mdtb_forms::text($this->field_name,$default,"mdtb_form_textfield");
				break;
		}
		return $myField;
	}

	function get_hidden_field_value($parent,$default="")
	{
		$myField="";
		switch($this->type)
		{
			case "dateauto_modification":
				$myField=date("Y-m-d H:i:s");
				break;
			case "dateauto_creation":
				if(trim($default)=="" || is_null($default) ) $default=date("Y-m-d H:i:s");
				$myDate=$default;
				$myTstDate=strtotime($default);
				if($myTstDate===false)
					$myDate=date("Y-m-d H:i:s");
				$myField=$myDate;
				break;
			case "user_creation":
				if(intval($default)>0)
				{
					$myUser=new users($this->_db);
					if($myUser->load($default))
					{
						$myField = intval($default);
						break;
					}
				}
				// no break : default value is "user" field value
			case "user":
				if(intval($default)<=0 || $default=="")
					$myField=$this->_user_ID;
				else
					$myField=$default;
				break;
			case "group":
				if(intval($default)<=0 || $default=="")
					$myField=$this->_user_group;
				else
					$myField=$default;
				break;
			case "reference":
				if($parent->_mdtb->ischild())
				{
					if($parent->_mdtb->_reference->child_key==$this->field_name)
					{
						$myField=$parent->_mdtb->_reference->key_value;
						break;
					}
				}
			default:
				$myField=$default;
				break;
		}
		return $myField;
	}


}


class mdtb_fieldreference
{
	var $value="";
	var $record=null;
	var $field=null;
	var $db;
	function mdtb_fieldreference(&$database,$field,$value)
	{
		$this->db=&$database;
		$this->record=null;
		$this->field=&$field;
		return $this->load($value);
	}

	function load($value)
	{
		if($this->field->type!="reference" || $this->field->ref_label=="" || $this->field->ref_key=="" || $this->field->ref_table=="" || $value=="")
			return false;
		$this->db->setQuery("SELECT ".$this->field->ref_label." as value FROM ".$this->field->ref_table." WHERE ".$this->field->ref_key."='".$value."';");
		//echo "Requête : ".$this->db->getQuery()."<br>\n";
		if($this->db->loadObject($this->record))
		{
			$this->value=$this->record->value;
			return true;
		}
		return false;
	}

	function reflist()
	{
		$debug=false;
		$myList=null;
		$this->db->setQuery("SELECT ".$this->field->ref_key." as id, ".$this->field->ref_label." as value FROM ".$this->field->ref_table." GROUP BY ".$this->field->ref_key." ORDER BY ".$this->field->ref_label." ASC;");
		if($debug) echo __FUNCTION__."@".__LINE__." => Requête : ".$this->db->getQuery();
		$myList=$this->db->loadObjectList();
		if(count($myList)<=0)
			return false;
		$myArrList=array();
		foreach($myList as $curelement)
			$myArrList[]=array("id"=>$curelement->id,"value"=>$curelement->value);
		return $myArrList;
	}
}

class mdtb_forms
{
	function mdtb_forms()
	{

	}

	function beginform($theName,$theScript)
	{
		return "<form name=\"".$theName."\" id=\"".$theName."\" action=\"".$theScript."\" method=\"post\"  enctype=\"multipart/form-data\">\n";
	}

	function endform()
	{
		return "</form>\n";
	}

	function submit($theName,$theValue,$theClass)
	{
		return "<input type=\"submit\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theValue."\" class=\"".$theClass."\">\n";
	}

	function button($theName,$theValue,$theParams,$theClass)
	{
		return "<input type=\"button\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theValue."\" class=\"".$theClass."\" ".$theParams.">\n";
	}

	function combolist($theName,$theList,$theDefault="",$theClass="")
	{
		$myCombo = "<select name=\"".$theName."\" id=\"".$theName."\" class=\"".$theClass."\" >\n";
		if(count($theList)>0)
			foreach($theList as $curoption)
			{
				$myCombo.= "<option value=\"".$curoption["id"]."\" ".(((string)$curoption["id"]==(string)$theDefault)?"selected":"")." >".$curoption["value"]."</option>\n";
			}
		$myCombo.= "</select>";
		return $myCombo;
	}
	
	
	function combolistmultiple($theName,$theList,$theDefaults=array(),$theClass="")
	{
		$myCombo = "<select name=\"".$theName."[]\" id=\"".$theName."\" class=\"".$theClass."\" multiple >\n";
		if(count($theList)>0)
			foreach($theList as $curoption)
			{
				$myCombo.= "<option value=\"".$curoption["id"]."\" ".((in_array((string)$curoption["id"],$theDefaults)?"selected":""))." >".$curoption["value"]."</option>\n";
			}
		$myCombo.= "</select>";
		return $myCombo;
	}


	function comboarray($theName,$theList,$theDefault="",$theClass="")
	{
		$myCombo = "<select name=\"".$theName."\" id=\"".$theName."\" class=\"".$theClass."\" >\n";
		if(count($theList)>0)
			foreach($theList as $keyoption => $valoption)
			{
				$myCombo.= "<option value=\"".$keyoption."\" ".(($keyoption==$theDefault)?"selected":"")." >".$valoption."</option>\n";
			}
		$myCombo.= "</select>";
		return $myCombo;
	}


	function file($theName,$theDefault="",$theOnDisk="",$theClass="")
	{
		$myFileField = "<span class=\"".$theClass."_label\">".$theDefault."</span>\n";
		$myFileField.= "<input type=\"file\" class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\" >\n";
		return $myFileField;
	}

	function text($theName,$theDefault="",$theClass="")
	{
		return "<input type=\"text\" class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theDefault."\" >\n";
	}

	function hidden($theName,$theDefault="")
	{
		return "<input type=\"hidden\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theDefault."\" >\n";
	}

	function longtext($theName,$theDefault="",$theClass="")
	{
		return "<textarea class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\">".$theDefault."</textarea>\n";
	}
}

class default_user_defined
{
	var $path_abs;
	var $db;

	function default_user_defined($obj)
	{
    	$this->db=&$obj->_db;
    	$this->path_abs=&$obj->_curpath;
    	$this->obj=$obj;
	}
}
