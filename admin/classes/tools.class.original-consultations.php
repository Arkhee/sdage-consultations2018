<?php
// Version : 0.0.4
define("MAXINDENT", 10);
define("TAB", '|  ');
define("LOG_MAX_SIZE", 1000000);
if(!defined("BR")) define("BR","<br />\n");
$traceactive=true;

class Tools
{
	var $_logfile;
	var $_indent;
	var $_pathpre;
	
	function Tools($thePath="")
	{
		$this->_pathpre=$thePath;
	}
	
	function Display($theObject)
	{
		return "<pre>".print_r($theObject,true)."</pre>\n";
	}
	
	function SetSessionVar($theObject,$theName,$theSerialize=true)
	{
		if(session_is_registered($theName))
			session_unregister($theName);
		return $_SESSION[$theName]=(($theSerialize)?serialize($theObject):$theObject);
	}
	
	function GetSessionVar($theName,$theSerialize=true)
	{
		if (!session_is_registered($theName))
			return false;
		return ($theSerialize)?unserialize($_SESSION[$theName]):($_SESSION[$theName]);
	}
	
	function BindToGlobal($theRefArray,$theArrayAuth=null)
	{
		//echo "Bind de : ".Tools::Display($theRefArray);
		if(is_array($theRefArray) && count($theRefArray)>0)
		{
			foreach($theRefArray as $key=>$value)
			{
				if(is_null($theArrayAuth) || (is_array($theArrayAuth) && in_array($key,$theArrayAuth)))
				{
					global $$key;
					$$key=$value;
				}
			}
		}
	}
	
	function  DisplayGlobals($theGlobalsArray)
	{
		$myReturn="";
		foreach($theGlobalsArray as $val)
		{
			global $$val;
			if(isset($$val))			
				$myReturn.=Tools::Display($val."=".$$val);
			else
				$myReturn.=Tools::Display("Undef:".$val);
		}
		return $myReturn;
	}
	
	function InitGlobals($theGlobalsArray,$theDefaultValue="")
	{
		if(is_array($theGlobalsArray) && count($theGlobalsArray)>0)
			foreach($theGlobalsArray as $curglobal)
			{
				global $$curglobal;
				$$curglobal=$theDefaultValue;
			}
	}
	
	function SecureForDB($var)
	{
		global $database;
		if(is_object($database))
			return $database->escape($var);
		else
			return addslashes($var);
	}
	
	function Translate($theText)
	{
		global $langtable;
		if(isset($langtable[$theText]))
			return $langtable[$theText];
		else
			return $theText;
	}

	function GetRandomFileName($theFile,$thePath)
	{
		$myNewFileName=Tools::Scramble()."_".$theFile;
		while(file_exists($thePath.$myNewFileName))
			$myNewFileName=Tools::Scramble()."_".$theFile;
		return $myNewFileName;
	}


	function SendXLS($theFileName,&$theData,$theColDefinition=array(),$thePathPre="",$theHasHeader=true)
	{
		$myArrayTable=$theData;
		$myColOrdered=$theColDefinition;
		$curdir=getcwd();
		$path_include=$thePathPre."local.procedures/";
		chdir($path_include);
		require_once 'Writer.php';
		chdir($curdir);
		$tmpfname = @tempnam("","");
		if($tmpfname=="")
		{
			$error_msg.="Erreur dans le nom du fichier<br />\n";
		}
		else
		{
			$workbook = new Spreadsheet_Excel_Writer($tmpfname);
			$workbook->setVersion(8);
			$worksheet =& $workbook->addWorksheet('Titre');
			$cell_format="entete";
			$formats_array[$cell_format] =& $workbook->addFormat();
			$formats_array[$cell_format]->setFontFamily('Arial');
			//$format_Arial->setNumFormat("text");
			$formats_array[$cell_format]->setBold();
			$formats_array[$cell_format]->setSize(10);
			$formats_array[$cell_format]->setNumFormat('@');

			$i=0;
			//echo "Donn�es : ".Tools::Display($myArrayTable);
			foreach($myArrayTable as $keyarray=>$curarray)
			{
				$j=0;
				foreach($myArrayTable[0] as $keyval=>$headvalue)
				{
					$cell_format="@";
					//echo("Sortie excel avec format : col[".$keyval."]= ".$myColOrdered[$keyval]->export_format_valeur).BR;
					if(!isset($curarray[$keyval])) $curarray[$keyval]="";
					if(isset($myColOrdered[$keyval]->export_format_valeur))
						$cell_format=$myColOrdered[$keyval]->export_format_valeur;
					$cell_format=((trim($cell_format)=="jj/mm/aaaa")?"@":$cell_format);
					if(!isset($formats_array[$cell_format]))
					{
						$formats_array[$cell_format] =& $workbook->addFormat();
						$formats_array[$cell_format]->setFontFamily('Arial');
						$formats_array[$cell_format]->setSize(10);
						$formats_array[$cell_format]->setNumFormat($cell_format);
					}
					
					if($i==0)
						$cell_format="entete";
					//echo $curarray[$keyval].";";
					$worksheet->write($i, $j,strval($curarray[$keyval]),$formats_array[$cell_format]);
					$j++;
				}
				//echo "<br >\n";
				//die();
/*
							foreach($curarray as $keyval=>$curval)
							{
								$worksheet->write($i, $j,($curval),$format_Arial);
								$j++;
							}
*/
				$i++;
			}

			$workbook->close();
			
			/*
			 * R�cup�ration des donn�es et envoi en streaming
			 */
			if(!headers_sent())
			{
				Tools::DL_DownloadProgressive($theFileName,$tmpfname);
				unlink($tmpfname);
				die();
			}
			else
			{
				$error_msg.="Ent�tes d�j� envoy�s ! Des erreurs dans le script ? Impossible d'envoyer le t�l�chargement<br />\n";
				unlink($tmpfname);
			}
		}
	
	}

	function SendCSV($theFileName,$theData,$theHasHeader=true,$theSeparateur,$theStoreFile="")
	{
		$mySeparateurCSV=$theSeparateur;
		$myCurLine=0;
		$myLineStop=-1;
		$myArrayTable=$theData;
		
		/*
		 * Vérification des données
		 */
		foreach($myArrayTable as $keyarray=> &$curarray)
		{
			if(is_object($curarray)) $curarray=(array)$curarray;
		}
		/*
		 * Formatage des donn�es
		 */
		if($theHasHeader)
		{
			foreach($myArrayTable as $keyarray=>$curarray)
			{
				foreach($myArrayTable[0] as $keyval=>$headvalue)
				{
					if(!isset($curarray[$keyval])) $curarray[$keyval]="";							
					$myArrayTable[$keyarray][$keyval]=str_replace("\"","\"\"",str_replace("\r\n","\n",$curarray[$keyval]));
				}
			}
		}
		//	foreach($curarray as $keyval=>$curvalue)
		//		$myArrayTable[$keyarray][$keyval]=str_replace("\"","''",str_replace("\r\n","\n",$curvalue));
		
		/*
		 * Sortie fichier
		 */
		if($theStoreFile!="")
			$tmpFileForDownload = $theStoreFile;
		else
			$tmpFileForDownload = tmpfile();
		$myFileSize=0;
		foreach($myArrayTable as $keyarray=>$curarray)
		{
			$myLine= utf8_decode("\"".implode("\"".$mySeparateurCSV."\"",$myArrayTable[$keyarray])."\"\r\n");
			$myFileSize+=strlen($myLine);
			fwrite($tmpFileForDownload, $myLine);
			$myCurLine++;
			if($myCurLine>$myLineStop && $myLineStop>0)
				break;
		}
		
		/*
		 * R�cup�ration des donn�es et envoi en streaming
		 */
		if($theStoreFile!="")
		{
			return fclose($tmpFileForDownload);
		}
		if(!headers_sent())
		{
			Tools::DL_Downloadheaders($theFileName,($myFileSize));
			fseek($tmpFileForDownload, 0);
			while(!feof($tmpFileForDownload))
				echo fread($tmpFileForDownload, 1024);
			fclose($tmpFileForDownload); // ceci va effacer le fichier
			die();
		}
		else
		{
			$error_msg.="Ent�tes d�j� envoy�s ! Des erreurs dans le script ? Impossible d'envoyer le t�l�chargement<br />\n";
		}

	}


	function NormalizeCSV($theFile)
	{
		if(file_exists($theFile) && is_writable($theFile))
		{
			$myFileContent=file_get_contents($theFile);
			if($myFileContent===false)
				return false;
			$myFileContent=str_replace("\n"," ",$myFileContent);
			$myFileContent=str_replace("\r ","\r\n",$myFileContent);
			$mySize=file_put_contents($theFile,$myFileContent);
			if($mySize===false)
				return false;
			if(strlen($myFileContent)==$mySize)
				return true;
		}
		return false;
	}


	function Trace($text)
	{
		global $indent,$traceactive;
		if (!$traceactive) return true;
		if (isset($this) && isset($this->_logfile))
		{
			$logfile = $this->_logfile;
			$idt = $this->_indent+1;
		}
		else
		{
			$logfile = Tools::getLogName();
			$idt = $indent;
		}

		Tools::writeTrace($text, $idt, $logfile);
	}
	
	function scramble() {
	  srand((double)microtime()*1000000);
	  $str="";
	  $char = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMANOPQRSTUVWXYZ";
	  while (strlen($str) < 12) { $str .= substr($char,(rand()%(strlen($char))),1); }
	  return $str;
	}

	function ajaxInit()
	{
		echo "<script type=\"text/javascript\" src=\"".$this->_pathpre."lib/js/prototype.lite.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"".$this->_pathpre."lib/js/moo.ajax.js\"></script>\n";		
	}

	function ajaxGetFunction($theName,$theScript,$theDest,$theParamArray=array(),$theStaticParams="",$theOnComplete="")
	{
		static $myFuctionsArray=array();
		if(!in_array($theName,$myFuctionsArray))
		{
			$myFuctionsArray[]=$theName;
			$myFunction="";
	    	$myFunction.= "<script  language=\"JavaScript\" type=\"text/javascript\">\n";
	    	$myFunction.= "
			    function ".$theName."(".implode(",",$theParamArray).")
			    {
			        new ajax ('".$theScript."', {\n";
			 
			 $myFunction.= "			          postBody: '".$theStaticParams.(($theStaticParams!="")?"&":"");
			 $myTxtParams="";
			 if(count($theParamArray)>0)
			 {
			 	foreach($theParamArray as $key=>$value)
			 	{
			 		$myTxtParams.=(($myTxtParams!="")?"+'&":"").$value."='+".$value;
					//echo $value."='+".$value."+'&";
			 	}
			 	$myFunction.= $myTxtParams;
			 }
			 if($theOnComplete!="") $myFunction.= ",\n		           onComplete : ".$theOnComplete;
			 if($theDest!="") $myFunction.= ",\n	    		       update: '".$theDest."'";
			 $myFunction.= "});    
					//alert('appel fonction ".$theName." avec params ".implode(",",$theParamArray)."')
			    }
			";
			 
			$myFunction.= "</script>\n";
		}
		else
		{
			$myFunction="";
		}	
		return $myFunction;
	}

	function ajaxAddFunction($theName,$theScript,$theDest,$theParamArray=array(),$theStaticParams="",$theOnComplete="")
	{
		echo Tools::ajaxGetFuction($theName,$theScript,$theDest,$theParamArray,$theStaticParams,$theOnComplete);
	}

	function ajaxAddPrototypeFunction($theName,$theScript,$theDest,$theParamArray=array(),$theStaticParams="",$theOnComplete="")
	{
	    	echo "<script  language=\"JavaScript\" type=\"text/javascript\">\n";
	    	echo "
			    function ".$theName."(".implode(",",$theParamArray).")
			    {

					new ajax ('$theScript', {
						update: $('".$theDest."'),
			            postBody: '".$theStaticParams.(($theStaticParams!="" && count($theParamArray)>0)?"&":"");
						 $myTxtParams="";
						 if(count($theParamArray)>0)
						 {
						 	foreach($theParamArray as $key=>$value)
						 	{
						 		$myTxtParams.=(($myTxtParams!="")?"+'&":"").$value."='+".$value;
								//echo $value."='+".$value."+'&";
						 	}
						 	echo $myTxtParams;
						 }
						 elseif($theStaticParams!="")
						 	echo "'";
						 if($theOnComplete!="") echo ",\n		           onComplete : ".$theOnComplete;
					echo "	
			        });
				}"; 
				/*
					
			        new Ajax.Updater ('".$theDest."','".$theScript."', {\n";
			 
			 echo "			          postBody: '".$theStaticParams.(($theStaticParams!="")?"&":"");
			 $myTxtParams="";
			 if(count($theParamArray)>0)
			 {
			 	foreach($theParamArray as $key=>$value)
			 	{
			 		$myTxtParams.=(($myTxtParams!="")?"+'&":"").$value."='+".$value;
					//echo $value."='+".$value."+'&";
			 	}
			 	echo $myTxtParams;
			 }
			 if($theOnComplete!="") echo ",\n		           onComplete : ".$theOnComplete;
			 echo "});    
				//	alert('appel fonction ".$theName." avec params '+".implode("+','+",$theParamArray).");
			    }
			";
			 */
			echo "</script>\n";		
	}

	function ajaxCall($theFunction,$theParams)
	{
		$myTxtParams="";
		if(count($theParams)>0)
		{
			foreach($theParams as $myCurParam)
			{
				$myTxtParams.=(($myTxtParams!="")?",":"")."document.getElementById('".$myCurParam."').value";
			}
		}
		echo $theFunction."(".$myTxtParams.");";
	}
	
	
	function writeTrace($t, $idt, $nf) {
		global $indent,$traceactive;
		
		if (!$traceactive) return true;
		if ($indent <= MAXINDENT) {
	
			// Supprimer le fichier si > 500Ko (ne tester que sur les traces de premier niveau, 
			//   pour optimisation)
			if ($indent == 0) {
				$size = @filesize($nf);
				if ($size > LOG_MAX_SIZE) {
					@unlink($nf);
				}
			}
	
			// FICHIER TEXTE
			//echo __FILE__."/".__FUNCTION__."/".__LINE__." : log dans le fichier ".$nf." du texte ".$t."<br />\n";
			if ($f = fopen($nf, "a+")) {

				// Ecrire message
				if (is_array($t) || is_object($t)) {
				 
					if (is_array($t)) 
						Tools::writeIdent($f, 'array : ', $idt);
					if (is_object($t)) 
						Tools::writeIdent($f, get_class($t).' : ', $idt);
				
					$lignes = explode("\n", var_export($t, true));
					foreach($lignes as $ligne) {
						Tools::writeIdent($f, $ligne, $idt);
					}
				} else {
					if ($t == '')  {
						Tools::writeIdent($f, "cha�ne vide", $idt);
					} else {
						Tools::writeIdent($f, $t, $idt);
					}
				}
			}
		}
	} 
	
	function writeIdent($f, $ligne, $idt) {
		// indenter
		fwrite($f, str_repeat(TAB, $idt).$ligne."\n");
	}

	function getLogName()
	{	
		global $logpath,$logname;
		$myCurLogName="tracefile.log";
		$myLogFullName="";
		if(isset($logpath)) $myLogFullName=$logpath."/";
		if(isset($logname))
			if($logname!="")
				$myCurLogName=$logname;
				
		$myLogFullName.=$myCurLogName;
		
		return $myLogFullName;	
	}
	
	function TimeStartCounter()
	{
		global $starttime;
		$starttime = microtime();
		$startarray = explode(" ", $starttime);
		$starttime = $startarray[1] + $startarray[0];	
		return $starttime;
	}

	function TimeStopCounter()
	{
		global $starttime;
		$endtime = microtime();
		$endarray = explode(" ", $endtime);
		$endtime = $endarray[1] + $endarray[0];
		$totaltime = $endtime - $starttime;
		$totaltime = round($totaltime,5);
		return $totaltime;
	}
	function TimeSetVal($theDatabase,$theRefId,$theSessId,$theParam,$theVal)
	{
		if($theParam=="") return false;
		Tools::Trace(__FUNCTION__."=>D�finition de ".$theParam." � ".$theVal." avec id=".$theSessId." et refid=".$theRefId);
		if(!is_object($theDatabase))
		{
			Tools::Trace(__FUNCTION__."=> base de donn�es non d�finie !!");
			return false;
		}
		$myObj=null;
		$theDatabase->setQuery("SELECT * FROM page_statistics WHERE refid='".$theRefId."' AND id=".$theSessId.";");
		if(!$theDatabase->loadObject($myObj))
		{
			Tools::Trace(__FUNCTION__."=> impossible de charger l'objet");
			return false;
		}
		$myObj->$theParam=$theVal;
		$theDatabase->updateObject("page_statistics",$myObj,"id");
		Tools::Trace(__FUNCTION__."=> requ�te : ".$theDatabase->getQuery());
		
	}
	
	function DisplayTimeCounter()
	{
		echo "<div class=\"time_counter\">".getTranslation("Temps de g�n�ration de la page")." :".time_get_counter()."s</div>\n";
	}
	
	function TimeStoreCounter($theDatabase,$theStartTime,$theUserId,$theShowScript=false,$thePath="")
	{
		if(is_object($theDatabase))
		{
			$myEndTime=Tools::TimeStartCounter();
			$myObj=null;
			$myObj->refid=session_id();
			$myObj->userid=$theUserId;
			$myObj->starttime=$theStartTime;
			$myObj->computetime=$myEndTime;
			$myObj->userip=$_SERVER["REMOTE_ADDR"];
			
			$theDatabase->insertObject("page_statistics",$myObj,"id");
			Tools::Trace(__FUNCTION__."=> temps :".$theStartTime);
			Tools::Trace(__FUNCTION__."=> requ�te : ".$theDatabase->getQuery());
			if($theShowScript)
				echo "<script src=\"".$thePath."stats.php?sessid=".$myObj->id."&refid=".session_id()."\" type=\"text/javascript\"></script>";
		}
	}
	
	function DateUserToSQL($thePref,$theDateStr)
	{
		if(!isset($thePref->ScanFormat))
			$thePref->ScanFormat="%d-%d-%d";
		switch($thePref->ScanOrder)
		{
			case "dmy":
				list($myDate["d"],$myDate["m"],$myDate["y"])=sscanf($theDateStr,$thePref->ScanFormat);
				break;
			case "ymd":
				list($myDate["y"],$myDate["m"],$myDate["d"])=sscanf($theDateStr,$thePref->ScanFormat);
				break;
		}
		//echo "Date : " .$theDateStr.", Format : ".$thePref->ScanFormat.", Order : ".$thePref->ScanOrder.", Scan : ".$myDate["y"].",".$myDate["m"].",".$myDate["d"]."<br />\n";
		if(strlen($myDate["m"])<2)
			$myDate["m"]="0".$myDate["m"];

		if(strlen($myDate["d"])<2)
			$myDate["d"]="0".$myDate["d"];
		$mySQLDate="";
		$myIntDate=mktime(1,1,1,intval($myDate["m"]),intval($myDate["d"]),intval($myDate["y"]));
		if($myIntDate!==false && $myIntDate!==-1 && intval($myDate["y"])>0 && intval($myDate["m"])>0 && intval($myDate["d"])>0)
			$mySQLDate=$myDate["y"]."-".$myDate["m"]."-".$myDate["d"];
		return $mySQLDate;
	}

	function DateSQLToUser($thePref,$theDateStr)
	{
		if($theDateStr=="")
			return "";
		if(!isset($thePref->ScanFormat))
			$thePref->ScanFormat="%d-%d-%d";
		list($myDate["y"],$myDate["m"],$myDate["d"])=sscanf($theDateStr,"%4s-%2s-%2s");
		$myDate=mktime(1,1,1,$myDate["m"],$myDate["d"],$myDate["y"]);
		$myUserDate=date($thePref->DispFormat,$myDate);
		/*
		switch($thePref->ScanOrder)
		{
			case "dmy":
				$myUserDate=sprintf($thePref->ScanFormat,$myDate["d"],$myDate["m"],$myDate["y"]);
				break;
			default:
			case "ymd":
				$myUserDate=sprintf($thePref->ScanFormat,$myDate["y"],$myDate["m"],$myDate["d"]);
				break;
		}
		*/
		return $myUserDate;
	}
	
	function TimeCheckTable($database)
	{

		$database->setQuery("show tables like 'page_statistics';");
		$myRowList=$database->loadRowList();
		if(count($myRowList)<=0)
		{
			$myQuery["page_statistics"]="
					CREATE TABLE `page_statistics` (
					  `id` int(11) NOT NULL auto_increment,
					  `refid` varchar(32) NOT NULL default '',
					  `userid` int(11) NOT NULL default '0',
					  `starttime` decimal(21,6) default NULL,
					  `computetime` decimal(21,6) default NULL,
					  `displaytime` decimal(21,6) default NULL,
					  `page` text,
					  `userip` text,
					  PRIMARY KEY  (`id`),
					  KEY `clef_refid` (`refid`)
					);
				";
			$database->setQuery($myQuery["page_statistics"]);
			$database->query();
			
		}
	}
	function Tail()
	{
		$debugtail=false;
		
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>d�but");
		if(session_id()=="")
			session_start();
		if(!isset($_SESSION["tail_file"]))
			$_SESSION["tail_file"]="";
		$tail_file=$_SESSION["tail_file"];
		$tail_name=$_POST["tail"];
		if(!isset($_SESSION["tail_sess_name"])) $_SESSION["tail_sess_name"]=$_POST["tail"];
		else
		{
			if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>fichier d�j� charg� dans la session pr�c�dente");
			if($_SESSION["tail_sess_name"]!=$_POST["tail"])
			{
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>le fichier est diff�rent, on r�initialise");
				$_SESSION["tail_sess_name"]=$_POST["tail"];
				//$_POST["tail_action"]="reset";
				$tail_file="";
			}
		}
		if($_POST["tail_action"]=="reset") $tail_file="";
		$myNewFileContent="";
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>Fin init");
		if(substr($tail_name,0,7)=="http://")
		{
			$myNewFileContent=file_get_contents($tail_name);
		}
		else
		{
			if(file_exists($tail_name))
				$myNewFileContent=file_get_contents($tail_name);
		}
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>fichier charg� (".strlen($myNewFileContent).")");
		if($myNewFileContent!="")
		{
			if($tail_file=="")
			{
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>premi�re it�ration");
				$tail_file=$myNewFileContent;
				$_SESSION["tail_file"]=$tail_file;
				return $tail_file;
			}
			if(strlen($tail_file)<=strlen($myNewFileContent))
			{
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>it�rations suivantes");
				$myDiffBuff=substr($myNewFileContent,strlen($tail_file),strlen($myNewFileContent));
				$tail_file=$myNewFileContent;
				$_SESSION["tail_file"]=$myNewFileContent;
				
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>it�rations suivantes. Diff�rence : (".strlen($myDiffBuff).") : ".$myDiffBuff);
				return $myDiffBuff;
			}
			else
			{
				$_SESSION["tail_file"]=$myNewFileContent;
				return "";
			}
		}
		
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>fin fichier vide");
		$tail_file=$myNewFileContent;
		$_SESSION["tail_file"]=$tail_file;
		return $myNewFileContent;
	}
	
	function Tail_showHTML()
	{
		echo "<html><head><title>Suivi de ".$_GET["tail"]."</title>";
	    echo "<script type=\"text/javascript\" src=\"scripts/prototype.lite.js\"></script>\n";
	    echo "<script type=\"text/javascript\" src=\"scripts/moo.ajax.js\"></script>\n";
		echo "<script  language=\"JavaScript\" type=\"text/javascript\">\n";
		echo "
		    function updateTail(theParam)
		    {
				divtail='divtail';
				if(theParam=='')
				{
					theParam='tail';
				}
				//alert('update');
				//document.getElementById('divcontent').style.backgroundColor='#FFCCCC';
		        new ajax ('tools.class.php', {
		            postBody: 'tail=".$_GET["tail"]."&tail_action='+theParam,
					update: $(divtail), 
		            onComplete : loadComplete
		        });    
		    }
			function myTimer()
			{
				updateTail('');
				timeoutID = window.setTimeout('window.myTimer()',1000);
			}
			function loadComplete()
			{
				//document.getElementById('divcontent').style.backgroundColor='#FFFFFF';
				document.getElementById('divcontent').innerHTML=document.getElementById('divcontent').innerHTML+document.getElementById('divtail').innerHTML;
				//document.getElementById('divtail').innerHTML='';
				//alert(document.getElementById('divtail').innerHTML);
				//alert(document.getElementById('divcontent').innerHTML);
				//window.scrollBy(0,300);
			}
			";
		echo "</script>\n";
		echo "</head>\n";
		echo "<body  onload=\"myTimer();\">\n";
		echo "Fichier suivi : ".$_GET["tail"]."<br />\n";
		echo "<div id=\"divtail\" style=\"width:100%;border:1px solid #EAEAEA;display:none;\"></div>\n";
		echo "<div id=\"divcontent\" style=\"margin:2px;padding:2px;width:80%;border:1px solid #666666;display:block;\"></div>\n</body></html>\n";
		echo "<a href=\"#\" onclick=\"updateTail('reset');return false;\">Reset</a><br/>\n";
		
	}
	
	function DL_DownloadBuffer($filename,$buffer)
	{
		Tools::DL_Downloadheaders($filename,strlen($buffer));
		echo $buffer;
	}

	function DL_DownloadProgressive($send_filename,$input_filename)
	{
		if(file_exists($input_filename) && is_readable($input_filename))
		{
			Tools::DL_Downloadheaders($send_filename,filesize($input_filename));
			$fh=fopen($input_filename,"rb");
			while(!feof($fh))
				echo fread($fh,8192);
			fclose($fh);
		}
		else
			return false;
		//echo "T�l�chargement progressif du fichier ".$filename." soit sur le disque : ".$file."(exsite : ".file_exists($file).")";
	}

    function DL_Downloadheaders($name,$filesize)
    {
		// get_contenttype.inc.php - PHProjekt Version 4.0
		// copyright  �  2000-2003 Albrecht Guenther  ag@phprojekt.com
		// www.phprojekt.com
		// Author: Albrecht Guenther
		
		header ("Expires: Mon, 10 Dec 2001 08:00:00 GMT");
		header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')
		{
		    header ("Cache-Control: no-cache, must-revalidate");
		    header ("Pragma: no-cache");
		}
		else
		{
		   // for SSL connections you have to replace the two previous lines with
		   header ("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		   header ("Pragma: public");
		}
		
		// fallback if no download type is set
		$file_download_type = "attachment";
		
		$contenttype = Tools::DL_Content_type($name);
		if(substr($contenttype,0,5)=="image")
		{
			header("Content-Type: ".$contenttype);
		}
		else
		{
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			//header("Content-Type: application/force-download");
			//header("Content-Type: application/octet-stream");
			//header("Content-Type: application/download");
			header("Content-Type: ".$contenttype);
			header("Content-Disposition: attachment; filename=\"".basename($name)."\";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$filesize);	
		}
		
		//echo "Fin de l'envoi des ent�tes";
	}

	function DL_Content_type($name)
	{
	   // Defines the content type based upon the extension of the file
	   //echo "Le nom est : ".$name."<br>\n";
	   $contenttype = "application/octet-stream";
	   $contenttypes = array("html" => "text/html",
	                      "htm" => "text/html",
	                      "txt" => "text/plain",
	                      "gif" => "image/gif",
	                      "jpeg" => "image/jpeg",
	                      "jpg" => "image/jpeg",
	                      "png" => "image/png",
	                      "sxw" => "application/vnd.sun.xml.writer",
	                      "sxg" => "application/vnd.sun.xml.writer.global",
	                      "sxd" => "application/vnd.sun.xml.draw",
	                      "sxc" => "application/vnd.sun.xml.calc",
	                      "sxi" => "application/vnd.sun.xml.impress",
	                      "xls" => "application/vnd.ms-excel",
	                      "ppt" => "application/vnd.ms-powerpoint",
	                      "doc" => "application/msword",
	                      "rtf" => "text/rtf",
	                      "zip" => "application/zip",
	                      "mp3" => "audio/mpeg",
	                      "pdf" => "application/pdf",
	                      "tgz" => "application/x-gzip",
	                      "gz"  => "application/x-gzip",
	                      "vcf" => "text/vcf");
		$path_parts = pathinfo($name);
		$myExtension=strtolower($path_parts["extension"]);
		if(isset($contenttypes[$myExtension]))
			$contenttype=$contenttypes[$myExtension];
	   /*
	   $name = ereg_replace("�"," ",$name);
	   foreach ($contenttypes as $type_ext => $type_name)
	   {
	     if (preg_match ("/$type_ext$/i", $name)) { $contenttype = $type_name; }
	   }
	   */
	   //echo "Contenu pour l'extension ".$myExtension." du fichier ".$name." : ".$contenttype."<br>\n";
	   return $contenttype;
	}

}
if(!class_exists("ImageManipulation"))
{
	require_once(dirname(__FILE__)."/imagemanipulation.class.php");
}
