<?php
/*
 * Toutateam Groupware
 * Created on 4 janv. 08
 * Copyright  ©  2008 Yannick Bétemps yannick@alternetic.com
 * www.toutateam.com
 * Author : Yannick Bétemps
 * 
 * Toutateam is a fork project of PHProjekt v4.0
 * 
 * File : lib.functions.inc.php
 * Description : 
 * 
 */

function hidden_fields($hid){
  reset($hid);
  foreach($hid as $key=>$value) {
        echo "<input type=hidden name=$key value=$value>\n";
   }
}

// routine to check which access status the user has concerning a module and according to his role
function check_role($module) {
  global $user_ID;
  $result = db_query("select roles.ID, $module from roles, users where users.role = roles.ID and users.ID = '$user_ID'");
  $row = db_fetch_row($result);
  // is there a role for this user?
  if ($row[0] > 0) {
    // return the numeric value of the status: 0 = no access, 1 = read, 2 = write
    return $row[1];
  }
  // otherwise give him the full rights
  // Modif YB du 26/11/2003 - Ne pas donner de droits si les droits ne sont pas définis !!!
  	else { return "2"; }  // Original
 // Fin Modif YB du 26/11/2003 - Ne pas donner de droits si les droits ne sont pas définis !!!
}    

function getUserFullName($theUserID=-1)
{
	if($theUserID>0)
	{
		$myQuery="SELECT ID,nachname,vorname FROM projekte WHERE ID=".$theUserID.";";
		$myRes=db_query($myQuery);
		$myProjList=db_fetch_row($myRes);
		return $myProjList[1]." ".$myProjList[2];
	}
	return "";
}

function getProjektName($theProjID=-1)
{
	if($theProjID>0)
	{
		$myQuery="SELECT ID,name FROM projekte WHERE ID=".$theProjID.";";
		$myRes=db_query($myQuery);
		$myProjList=db_fetch_row($myRes);
		return $myProjList[1];
	}
	return "";
}

// simple select form for export options
function show_export_form($file,$target="") { 
  global $img_path,$img_los, $print, $exp1, $keyword, $filter, $sort, $up, $month, $year, $anfang, $ende, $pdf_support, $PHPSESSID;
  $hidden = array('file'=>$file,'PHPSESSID'=>$PHPSESSID,'filter'=>$filter,'keyword'=>$keyword,'up'=>$up,'sort'=>$sort,'month'=>$month,'year'=>$year);
  if ($file == "project_stat") {  $hidden = array_merge(array('anfang'=>$anfang,'ende'=>$ende), $hidden); }
  echo "<form action='../misc/export.php' method=post ".( ($target!="")?("target=\"".$target."\""):"").">\n";
  hidden_fields($hidden);  
  echo "$exp1: <select name=medium>\n";   
  if ($pdf_support) echo "<option value=pdf>PDF</option>\n";
  echo "<option value=xml>XML</option>\n";
  echo "<option value=html selected>HTML</option>\n";  
  echo "<option value=csv>CSV</option>\n";
  //echo "<option value=xls>XLS</option>\n";    
  //echo "<option value=rtf>RTF</option>\n";
  //echo "<option value=doc>DOC</option>\n";  
  //echo "<option value=print>$print</option>\n";          
  echo "</select> <input type=image src='".$img_los."' border=0 id=tr></form>&nbsp;";
}

// Modif YB du 27/02/2007 - export des temps par utilisateur
function show_per_user_export_form($file,$target="")
{
  global $img_los,$img_path,$gru, $print, $exp1, $keyword, $filter, $sort, $up, $month, $year, $anfang, $ende, $pdf_support, $PHPSESSID,$user_group,$user_ID;
  $hidden = array('file'=>$file,'PHPSESSID'=>$PHPSESSID,'filter'=>$filter,'keyword'=>$keyword,'up'=>$up,'sort'=>$sort,'month'=>$month,'year'=>$year,"user_group"=>$user_group);
  if ($file == "project_stat") {  $hidden = array_merge(array('anfang'=>$anfang,'ende'=>$ende), $hidden); }
  echo "<table border=0>\n";
  echo "<form action='../misc/export.php' method=post ".( ($target!="")?("target=\"".$target."\""):"").">\n";
  hidden_fields($hidden);  
  echo "<tr><td>$exp1:</td>\n";
  echo "<td><select name=medium>\n";   
  if ($pdf_support) echo "<option value=pdf>PDF</option>\n";
  echo "<option value=xml>XML</option>\n";
  echo "<option value=html>HTML</option>\n";  
  echo "<option value=csv selected>CSV</option>\n";
  //echo "<option value=xls>XLS</option>\n";    
  //echo "<option value=rtf>RTF</option>\n";
  //echo "<option value=doc>DOC</option>\n";  
  //echo "<option value=print>$print</option>\n";          
  echo "</select></td>\n";
  echo "<td>Utilisateur:</td>\n";
  echo "<td colspan=\"3\"><select name=\"exportuser\" style=\"width:150px;\">\n";
  echo "<option value=\"-1\" selected>".getTranslation("(choisir un utilisateur)")."</option>\n";
	$resultprofile = db_query("select users.ID,users.vorname,users.nachname from users,grup_user WHERE users.ID=grup_user.user_ID AND grup_user.grup_ID=".$user_group." ORDER BY users.nachname ASC;");
	while ($row = db_fetch_row($resultprofile))
	{
		$myCurUserName=$row[1].(($row[1]!="" && $row[2]!="")?", ":"").$row[2];
		echo "<option value=\"".$row[0]."\">".$myCurUserName."</option>\n";
	}

  echo "</select></td></tr>\n";

  echo "<tr><td nowrap>".getTranslation("Du Mois:")."</td><td><select name=exportmonth>\n";   
  for($i=1;$i<=12;$i++) echo "<option value=\"".( ($i<10)?("0".$i):$i )."\" ".(($i==intval($month))?"selected":"").">".$i."</option>\n";
  echo "</select></td>\n";

  echo "<td nowrap>".getTranslation("Au Mois:")."</td><td><select name=exporttomonth>\n";   
  for($i=1;$i<=12;$i++) echo "<option value=\"".( ($i<10)?("0".$i):$i )."\" ".(($i==intval($month))?"selected":"").">".$i."</option>\n";
  echo "</select></td>\n";

  echo "<td nowrap>".getTranslation("Année:")."</td><td><select name=exportyear>\n";   
  $myIndD=intval(date("Y"))-4;
  $myIndF=intval(date("Y"));
  for($i=$myIndD;$i<=$myIndF;$i++) echo "<option value=\"".$i."\" ".(($i==$myIndF)?"selected":"").">".$i."</option>\n";
  echo "</select></td></tr>\n";
  
  echo "<tr><td colspan=\"6\"><input type=image src='".$img_los."' border=0 id=tr></td></tr></form></table>";
}
// Fin Modif YB du 27/02/2007 - export des temps par utilisateur


// Modif YB du 21/09/2004 - export des temps pour tous les utilisateurs
function show_all_export_form($file,$target="")
{
  global $img_los,$img_path,$gru, $print, $exp1, $keyword, $filter, $sort, $up, $month, $year, $anfang, $ende, $pdf_support, $PHPSESSID,$user_group,$user_ID;
  $hidden = array('file'=>$file,'PHPSESSID'=>$PHPSESSID,'filter'=>$filter,'keyword'=>$keyword,'up'=>$up,'sort'=>$sort,'month'=>$month,'year'=>$year,"user_group"=>$user_group);
  if ($file == "project_stat") {  $hidden = array_merge(array('anfang'=>$anfang,'ende'=>$ende), $hidden); }
  echo "<table border=0>\n";
  echo "<form action='../misc/export.php' method=post ".( ($target!="")?("target=\"".$target."\""):"").">\n";
  hidden_fields($hidden);  
  echo "<tr><td>$exp1:</td>\n";
  echo "<td nowrap><select name=medium>\n";   
  if ($pdf_support) echo "<option value=pdf>PDF</option>\n";
  echo "<option value=xml>XML</option>\n";
  echo "<option value=html>HTML</option>\n";  
  echo "<option value=csv selected>CSV</option>\n";
  //echo "<option value=xls>XLS</option>\n";    
  //echo "<option value=rtf>RTF</option>\n";
  //echo "<option value=doc>DOC</option>\n";  
  //echo "<option value=print>$print</option>\n";          
  echo "</select></td>\n";
  echo "<td nowrap>Profil:</td>\n";
  echo "<td colspan=\"3\" nowrap><select name=\"exportprofil\" style=\"width:150px;\">\n";
  echo "<option value=\"-1\" selected>".getTranslation("(tous les utilisateurs)")."</option>\n";
	$resultprofile = db_query("select * from profile where von = '$user_ID' or von=-".$user_group." order by bezeichnung");
	while ($row = db_fetch_row($resultprofile))
	{
		if ($gru == $row[0])
		{
			$row[2] = html_out($row[2]);
			echo "<option value=$row[0] selected>$row[2]</option>\n";
		}
		else
		{
			echo "<option value=$row[0]>$row[2]</option>\n";
		}
	}

  echo "</select></td></tr>\n";

  echo "<tr><td nowrap>".getTranslation("Du Mois:")."</td><td><select name=exportmonth>\n";   
  for($i=1;$i<=12;$i++) echo "<option value=\"".( ($i<10)?("0".$i):$i )."\" ".(($i==intval($month))?"selected":"").">".$i."</option>\n";
  echo "</select></td>\n";

  echo "<td nowrap>".getTranslation("Au Mois:")."</td><td><select name=exporttomonth>\n";   
  for($i=1;$i<=12;$i++) echo "<option value=\"".( ($i<10)?("0".$i):$i )."\" ".(($i==intval($month))?"selected":"").">".$i."</option>\n";
  echo "</select></td>\n";

  echo "<td nowrap>".getTranslation("Année:")."</td><td><select name=exportyear>\n";   
  $myIndD=intval(date("Y"))-4;
  $myIndF=intval(date("Y"));
  for($i=$myIndD;$i<=$myIndF;$i++) echo "<option value=\"".$i."\" ".(($i==$myIndF)?"selected":"").">".$i."</option>\n";
  echo "</select></td><td colspan=2>&nbsp;</td></tr>\n";
  
  echo "<tr><td colspan=\"6\" nowrap><input type=image src='".$img_los."' border=0 id=tr></td></tr></form></table>";
}
// Fin Modif YB du 21/09/2004 - export des temps pour tous les utilisateurs

// this function gets the OS of the browser and chooses the appropiate css file
function def_style() {
// Modif YB du 02/12/2005 - Modification des styles : avec l'avènement des nouveaux navigateurs, on laisse tomber ces fichues feuilles de style multiples !!!!

//  global $HTTP_USER_AGENT, $path_pre, $skin;
//  // mac platform ...
//  if (eregi("mac", $HTTP_USER_AGENT)) { return $path_pre."layout/".$skin."/css/mac.css"; }
//  // windows OS ...
//  elseif (eregi("win", $HTTP_USER_AGENT)) {
//    // special css for 4.x NN browsers
//    if (eregi("4.7|4.6|4.5", $HTTP_USER_AGENT)) { return $path_pre."layout/".$skin."/css/nn4.css"; }
//    // css for IE and opera
//    else  { return $path_pre."layout/".$skin."/css/win.css"; }
//  }
//  // default layout - not very nice but could fit a bit at least
//  else { return $path_pre."layout/".$skin."/css/common.css"; }
	global $path_pre,$skin;
	if(file_exists($path_pre."templates/".$skin."/css/styles.css.php"))
		return ($path_pre."templates/".$skin."/css/styles.css.php");
	elseif(file_exists($path_pre."templates/".$skin."/css/common.css"))
		return ($path_pre."templates/".$skin."/css/common.css");
	else
		return ($path_pre."layout/".$skin."/css/common.css");

	//return $path_pre."templates/".$skin."/css/common.css";  // on ne retourne PLUS QUE le fichier de style commun !!
	//return $path_pre."layout/".$skin."/css/common.css";  // on ne retourne PLUS QUE le fichier de style commun !!
// Fin Modif YB du 02/12/2005 - Modification des styles : avec l'avènement des nouveaux navigateurs, on laisse tomber ces fichues feuilles de style multiples !!!!

} // end find style sheet

// for ldap
function logit($message) {
	openlog("toutateam", LOG_NDELAY|LOG_PID, LOG_USER);
	syslog(LOG_DEBUG, $message);
	closelog();
}

// Modif YB du 15/09/2004 - Adding a few functions to make it easier to check user rank
function isChief()
{
	global $user_access;
	//echo "Is chief $user_access ?<br>\n"; 
	$chief = strpos($user_access,"c");
	if($chief!==FALSE) { return true; }
	return false;
}

function isAdmin()
{
	global $user_access;
	//echo "Is admin $user_access ?<br>\n"; 
	$admin = strpos($user_access,"a");
	if($admin!==FALSE) { return true; }
	return false;
}

function isProxy()
{
	global $user_ID;
	$myUser=db_query("SELECT proxy FROM users WHERE ID=".$user_ID.";");
	$myProxyList=db_fetch_row($myUser);
	$myProxy=$myProxyList[0];
	if($myProxy==(".".$user_ID.".")) return true;
	return false;
}


// Fin Modif YB du 15/09/2004 - Adding a few functions to make it easier to check user rank

// Modif YB du 06/02/2005 - Mise en place du quota dans les fichiers et mails 
// Il faut tout d'abord connaître la taille réelle occupée par un utilisateur sur disque
// Deux tailles autorisées : mails et fichiers. Il faut les considérer différemment 
// car un utilisateur appartient à plusieurs groupes, potentiellement. Or le quota concerne UN groupe.
function getFileSizeForUser($theUser,$theGroup)
{
    $result = db_query("select SUM(filesize) from dateien where gruppe=".$theGroup.";");
    $row = db_fetch_row($result);
	$myFileSize=(intval($row[0])<=0)?0:intval($row[0]);
	$myFileSize=intval(100*$myFileSize/(1024*1024))/100;
	return $myFileSize;
}

function getMailSizeForUser($theUser,$theGroup)
{
	$myQuery="select SUM(mail_attach.filesize) from mail_attach,mail_client where mail_client.von=".$theUser." AND mail_attach.parent=mail_client.ID;";
	$result = db_query($myQuery);
    $row = db_fetch_row($result);
	$myMailSize=(intval($row[0])<=0)?0:intval($row[0]);
	$myMailSize=intval(100*$myMailSize/(1024*1024))/100;
	return $myMailSize;
}
function isMailQuotaOk($theUser,$theGroup)
{
	global $user_mail_quota;
	$mySize=getMailSizeForUser($theUser,$theGroup);
	if(intval($user_mail_quota)<=0) return true;
	if($mySize>=intval($user_mail_quota)) return false;
	return true;
}
function isFileQuotaOk($theUser,$theGroup)
{
	global $user_file_quota;
	if(!isset($user_file_quota)) $user_file_quota="0";
	if($user_file_quota=="") $user_file_quota="0";
	if(intval($user_file_quota)==0) $user_file_quota="0";
	
	$mySize=getFileSizeForUser($theUser,$theGroup);
	if(intval($user_file_quota)<=0) return true;
	if($mySize>=intval($user_file_quota)) return false;
	return true;
}

// Modif YB du 24/05/2005 - Fonction de vérification des droits sur un projet donné
function checkProjectAccess($theProjectRights,$theProjectGroup,$theProjectChief)
{
	global $user_ID,$user_kurz,$user_group;
	//echo "Vérification avec : $theProjectRights,$theProjectGroup,$theProjectChief<br>\n";
	if($theProjectRights=="public") return true;
	if($theProjectRights=="group" && $theProjectGroup==$user_group) return true;
	if($theProjectRights=="chief")
	{
		if($user_kurz==$theProjectChief) return true;
		else return false;
	}
	$myRights=str_replace("profile:","",$theProjectRights);
	if($myRights!="")
	{
		//echo "Test du profil $myRights ...<br>\n";
		$myProfileResult=db_query("SELECT * FROM profile WHERE ID=".$myRights.";");
		$myProfileList=db_fetch_row($myProfileResult);
		$myProfileRights=unserialize($myProfileList[3]);
		if(in_array($user_kurz,$myProfileRights,true))
			return true;
		else
			return false;
	}
	return true; 
}
// Fin Modif YB du 24/05/2005 - Fonction de vérification des droits sur un projet donné


// Modif YB du 26/05/2005 - Ajout d'une fonction de log des accès mysql pour préparer l'accès hors ligne
function getReplaceString($theIndex)
{
	return "@#'replace'#@".$theIndex."#";
}

function mysql_logger($query,$position)
{
	$debug=false;
  global $set_query_logger,$link,$db_host, $db_user, $db_pass,$db_name;
  if($debug) echo "mysql_logger : $query , $position<br>\n";
  //$linklog = mysql_connect($db_host, $db_user, $db_pass) or mysql_error();
  //$conn = mysql_select_db($db_name,$linklog);
  $linklog=$link;
  if(!$linklog or (isset($conn) and !$conn)) die("<b>Database connection failed!</b><br>Call admin, please.");

  if(!isset($set_query_logger)) $set_query_logger="0";
  if(trim($set_query_logger)=="") $set_query_logger="0";
  if(class_exists("Tools"))
  	Tools::Trace(date("d/m/Y H:i:s")." ".$position.", Requête ==> ".$query);
  if($set_query_logger=="0") return false;
  $myQueryAnalysis=sscanf(trim($query),"%s %s %s");
  if(count($myQueryAnalysis)>1)
  {
	  //echo "La requête contient ".count($myQueryAnalysis)." enregistrements ... : ".print_r($myQueryAnalysis)."<br>\n";
	  if($myQueryAnalysis[0]=="insert" || $myQueryAnalysis[0]=="INSERT")
	  {
	  	if($position!="after") return false;
	  	//echo "Insert requête ... $position<br>\n";
	  	$myTableName=$myQueryAnalysis[2];
		$myWhere="WHERE ID=".mysql_insert_id($link).";";
	  }
	  elseif($myQueryAnalysis[0]=="update" || $myQueryAnalysis[0]=="UPDATE")
	  {
	  	if($position=="after") return false;
	  	//echo "Update requête ...$position<br>\n";
	  	$myTableName=$myQueryAnalysis[1];
		$myWhere="";
		
		$myTabOccWhere=array();
		// This regular expression makes sure there is no "where" into a string, hidden somewhere ...
		// Two side effects : the character just before the string will be replacer after, but it's not a big deal since the 'where' keyword can not be stuck to this char
		// Second side effect : empty strings ('') will not be replaced, but not a big deal too because in that case it will not contain the where keyword either|[^\\][\'].*[^\\][\']|U

		$myTempQuery=str_replace("''","#emptystring#",$query);
		//echo "Recherche pour update sur la chaine : ".$myTempQuery."<br>$query\n";
		$myCountOccWhere=preg_match_all("|\'.*[^\\\']\'|U",$myTempQuery,$myTabOccWhere);
		$myReplaceTable=array();
		$i=0;
		if($myCountOccWhere>0)
		{		
			if(count($myTabOccWhere[0])>0) foreach($myTabOccWhere[0] as $key => $myCurMatch)
			{
				$myReplaceTable["original"][$i]=$myCurMatch;
				$myReplaceTable["replace"][$i]=getReplaceString($i);
				$i++;
			}
		}
		//echo "Les chaines suivantes seront remplacées : ".implode( " <br>\n ",$myReplaceTable["original"])."<br>\n";
		for($i=0;$i<count($myReplaceTable["original"]);$i++) $myTempQuery=str_replace($myReplaceTable["original"][$i],$myReplaceTable["replace"][$i],$myTempQuery);
		//echo "String :" .$myTempQuery."<br>\n";
		$myWhere=strstr($myTempQuery,"where").strstr($myTempQuery,"WHERE");
		//echo "Where avant : ".$myWhere."<br>\n";
		$myWhere=str_replace("#emptystring#","''",$myWhere);
		
		for($i=0;$i<count($myReplaceTable["original"]);$i++) $myWhere=str_replace($myReplaceTable["replace"][$i],$myReplaceTable["original"][$i],$myWhere);
		//echo "Where après : ".$myWhere."<br>\n";
		
	  }
	  elseif($myQueryAnalysis[0]=="delete" || $myQueryAnalysis[0]=="DELETE")
	  {
	  	if($position=="after") return false;
	  	//echo "Delete requête ...$position<br>\n";
	  	$myTableName=$myQueryAnalysis[2];
		$myWhere="";
		$myWhere=strstr($query,"where").strstr($query,"WHERE");
	  }
	  else return false;
	  $myAction=$myQueryAnalysis[0];
	  $myNewQuery=("SELECT ID FROM ".$myTableName." ".$myWhere);
	  $myReturnQuery=mysql_query($myNewQuery,$linklog);
	  $myListId="";
	  //echo "Nombre de réponses : ".count($myResLst)."<br>\n";
	  //echo "Requête : ".$myNewQuery."<br>\n";
	  while($myResLst=mysql_fetch_row($myReturnQuery))
	  {
	  	//echo "Ajout de l'ID : ".$myResLst[0]."<br>\n";
	  	$myListId.=(($myListId!="")?",":"").$myResLst[0];
	  }
	  
	  $myNewQuery=addslashes("SELECT ID FROM ".$myTableName." ".$myWhere);
	  $myQuery="INSERT INTO mysqlsync_log (table_name,table_action,table_id,table_query,table_query_hist,modificationdate) VALUES ('".$myTableName."','".$myAction."','".$myListId."','".$myNewQuery."','".addslashes($query)."/".$myQueryAnalysis[0]."/".$position."',NOW());";
	  //echo "Ajout de la requête <pre>".$myQuery."</pre>...<br>\n";
	  mysql_query($myQuery,$linklog);
  }
  //mysql_close($linklog);
}
// Fin Modif YB du 26/05/2005 - Ajout d'une fonction de log des accès mysql pour préparer l'accès hors ligne


// Modif YB du 26/05/2005 - Ajout d'une fonction pour simplifier l'ajout d'un lien "calendarpopup"
function getCalendarPopupLink($theItem,$theFormat="yyyy-MM-dd")
{
	return "<a href=\"#\" onClick=\"cal1x.select(document.getElementById('".$theItem."'),'anchor1x','".$theFormat."'); return false;\" TITLE=\"".getTranslation("Sélectionner une date")."\" NAME=\"anchor1x\" ID=\"anchor1x\">".getTranslation("Sélectionner une date")."</a>";
}

function getGeneralHeaders()
{
	global $path_pre,$css_style,$lang_cfg;
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" >\n";
	echo $lang_cfg;
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$path_pre."layout/commonstyles.css\">\n";
	include $path_pre."layout/intranet_styles.php";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$css_style."\">\n";
}

function boxRegister($theModule,$theBoxName,$theBoxType)
{
	global 	$TheBoxList,$TheTemplateBoxTitleStyle,$TheTemplateBoxContentStyle,$bgcolor2,$bgcolor3,$bgcolor4,$roundcorners,$textbackground,
			$cornerscolortitle,$cornerscolorcontent,$cornerscolortext,$TheTemplateBoxTopMenuInActiveStyle,$TheTemplateBoxTopMenuActiveStyle;
			
	if(!isset($cornerscolortitle)) $cornerscolortitle=$bgcolor4;
	if(!isset($cornerscolorcontent)) $cornerscolorcontent=$bgcolor4;
	if(!isset($cornerscolortext)) $cornerscolortext=$bgcolor4;
	if(!isset($TheTemplateBoxTitleStyle)) $TheTemplateBoxTitleStyle= "{corners:'$roundcorners',blend:true,bgColor:'$bgcolor3',border:'$cornerscolortitle'}";
	if(!isset($TheTemplateBoxContentStyle)) $TheTemplateBoxContentStyle="{corners:'$roundcorners',blend:true,bgColor:'$textbackground',border:'$cornerscolorcontent'}";
	if(!isset($TheTemplateBoxTopMenuActiveStyle)) $TheTemplateBoxTopMenuActiveStyle="{corners:'$roundcorners',blend:true,bgColor:'$bgcolor3',border:'$cornerscolorcontent'}";
	if(!isset($TheTemplateBoxTopMenuInActiveStyle)) $TheTemplateBoxTopMenuInActiveStyle="{corners:'$roundcorners',blend:true,bgColor:'$bgcolor2',border:'$cornerscolorcontent'}";
	if(!isset($TheTemplateBoxTextStyle)) $TheTemplateBoxTextStyle="{corners:'$roundcorners',blend:true,bgColor:'$bgcolor3',border:'$cornerscolortext'}";
	
	switch($theBoxType)
	{
		case "title":
			$myLocalType= $TheTemplateBoxTitleStyle;
			$myLocalClass=" class=\"mod_title\"";
			break;
		case "content":
			$myLocalType=$TheTemplateBoxContentStyle;
			$myLocalClass="";
			break;
		case "text":
			$myLocalType=$TheTemplateBoxTextStyle;
			$myLocalClass="";
			break;
		case "menuon":
			$myLocalType=$TheTemplateBoxTopMenuActiveStyle;
			$myLocalClass=" class=\"topmenuon\"";
			break;
		case "menuoff":
			$myLocalType=$TheTemplateBoxTopMenuInActiveStyle;
			$myLocalClass=" class=\"topmenuoff\"";
			break;
		
	}
	
	if(!isset($TheBoxList)) $TheBoxList=array();
	if(!isset($myObjBox)) $myObjBox=new stdClass();
	$myObjBox->name=$theBoxName;
	$myObjBox->type=$myLocalType;
	
	$TheBoxList[$theModule][]=$myObjBox;
	
	return " id=\"$theBoxName\" ".$myLocalClass." ";
}

function boxInit_Function($theModule)
{
	global $TheBoxList;
	if(isset($TheBoxList[$theModule]->name) && isset($TheBoxList[$theModule]->type))
		return "	Rico.Corner.round( '".$TheBoxList[$theModule]->name."', ".$TheBoxList[$theModule]->type.");\n";
	return "";
}

function boxInit($theModule)
{
	static $arrayinit=array();
	if(!in_array($theModule,$arrayinit))
	{
		$arrayinit[]=$theModule;
		global $TheBoxList,$roundcorners;
		if(!isset($TheBoxList[$theModule]) || !isset($roundcorners) ) return false;
		
		if(count($TheBoxList[$theModule])<=0) return false;
		echo "<script type=\"text/javascript\">\n";
		foreach($TheBoxList[$theModule] as $myBox)
		{
			echo "	Rico.Corner.round( '".$myBox->name."', ".$myBox->type.");\n";
		}
		//echo "new Rico.Corner.round( this.div, 'mod_frame',".$myBox->type.");\n";
		echo "</script>\n";
	}
}

function boxIncludes()
{
	global $path_pre,$ThePrefs;
	if(!isset($ThePrefs->AjaxIncluded) || !$ThePrefs->AjaxIncluded)
	{
		$ThePrefs->AjaxIncluded=true;
		$myReturn=
		"<link href=\"".$path_pre."lib/js/rico.css\" media=\"all\" rel=\"Stylesheet\" type=\"text/css\" >\n".
		"<script type=\"text/javascript\" src=\"".$path_pre."lib/js/prototype.js\" type=\"text/javascript\"></script>\n".
		"<script type=\"text/javascript\" src=\"".$path_pre."lib/js/rico.js\" type=\"text/javascript\"></script>\n".
		"<script type=\"text/javascript\" src=\"".$path_pre."lib/js/scriptaculous.js\" type=\"text/javascript\"></script>\n".
		"<script type=\"text/javascript\" src=\"".$path_pre."lib/js/moo.ajax.js\"></script>\n";
	}
	return $myReturn;
}

function getContentTypes()
{
   global 	$img_file_img,$img_file_pdf,$img_file_doc,$img_file_ppt,$img_file_xls,$img_file_arc,$img_file_htm,
   			$img_file_unkn,$img_file_txt,$img_file_exe,$img_file_vid,$img_file_aud;
	   $contenttypes = array(
							"html" => $img_file_htm,
							"htm" => $img_file_htm,
							"php" => $img_file_htm,
							"lnk" => $img_file_htm,
							"txt" => $img_file_txt,
							
							"gif" => $img_file_img,
							"jpg" => $img_file_img,
							"jpeg" => $img_file_img,
							"png" => $img_file_img,
							"svg" => $img_file_img,
							
							"sxw" => $img_file_doc,
							"sxg" => $img_file_doc,
							"sxd" => $img_file_doc,
							"sxi" => $img_file_doc,
							"doc" => $img_file_doc,
							"rtf" => $img_file_doc,

							"ppt" => $img_file_ppt,
							
							"sxc" => $img_file_xls,
							"xls" => $img_file_xls,
							
							"zip" => $img_file_arc,
							"rar" => $img_file_arc,
							"arc" => $img_file_arc,
							"bz2" => $img_file_arc,
							"tgz" => $img_file_arc,
							"gz"  => $img_file_arc,
							
							"mp3" => $img_file_aud,
							"aiff" => $img_file_aud,
							"wav" => $img_file_aud,
							"ogg" => $img_file_aud,
							"aac" => $img_file_aud,
							
							"mpg" => $img_file_vid,
							"ogm" => $img_file_vid,
							"wmv" => $img_file_vid,
							"mpeg" => $img_file_vid,
							"avi" => $img_file_vid,
							"divx" => $img_file_vid,
							"rm" => $img_file_vid,
							
							"pdf" => $img_file_pdf,
							
							"exe" => $img_file_exe,
							"com" => $img_file_exe,
							"bat" => $img_file_exe
					  );
	return $contenttypes;
}

function getDefaultContentType()
{
	global $img_file_unkn;
	return $img_file_unkn;
}

function getFileIconLinkFromName($name,$path="")
{
   // Defines the content type based upon the extension of the file
   //echo "Le nom est : ".$name."<br>\n";
   global $path_pre;
   if($path=="") $myUrlPath=$path_pre;
   else $myUrlPath=$path;
   $contenttype = "application/octet-stream";
	$contenttypes=getContentTypes();
	$contenttype=getDefaultContentType();
	$path_parts = pathinfo($name);
	if(isset($contenttypes[$path_parts["extension"]]))
		$contenttype = $contenttypes[$path_parts["extension"]];
	
	//echo "Contenu : ".$contenttype."<br>\n";
	return $myUrlPath.$contenttype;
}

function getFileIconFromName($name,$path="",$width="14")
{
	$myImageLink=getFileIconLinkFromName($name,$path);
	$width=intval($width);
	return "<img id=\"fileicon\" border=\"0\" width=\"".$width."\" src=\"".$myImageLink."\" />";
}

function getAddonDisplayName($theAddon)
{
	global $path_pre,$user_ID;
	if(file_exists($path_pre."admin/modules/addonmanager.php"))
	{
		include_once($path_pre."admin/modules/addonmanager.php");
		$myClsAddonManager=new addonmanager($theAddon);
		return $myClsAddonManager->getAddonDisplayName($theAddon);
	}
	else
	{
		return $theAddon;
	}
	
}
function checkAddonRights($theAddon)
{
	$debug=false;
	if($debug) echo "checkAddonRights : vérification addonmanager<br>\n";
	global $path_pre,$user_ID;
	if(file_exists($path_pre."admin/modules/addonmanager.php"))
	{
		if($debug) echo "checkAddonRights : inclusion addonmanager<br>\n";
		include_once($path_pre."admin/modules/addonmanager.php");
		if($debug) echo "checkAddonRights : instanciation classe addonmanager<br>\n";
		$myClsAddonManager=new addonmanager($theAddon);
		if($debug) echo "checkAddonRights : vérification addon $theAddon pour l'utilisateur $user_ID<br>\n";
		$myReturn=$myClsAddonManager->canAccess($theAddon,$user_ID,0);
		if($debug) echo "checkAddonRights : retour : ".$myReturn."<br>\n";
		return $myReturn;
	}
	else
	{
		return true;
	}
}

function libDBGetCurrentTablesArray()
{
	global $database;
	$database->setQuery("SHOW TABLES;");
	$myTablesList=$database->loadRowList();
	$myArrTables=array();
	if(count($myTablesList)>0)
		foreach($myTablesList as $myCurTable) $myArrTables[]=$myCurTable[0];
	return $myArrTables;
}

function libGetCurrentVersion()
{
	global $database;
	$database->setQuery("SELECT * FROM patchmanagement_history ORDER BY fld_version_number DESC LIMIT 1,1;");
	$myListPatches=$database->loadObjectList();
	if(count($myListPatches)>0)
		$myToUpdateVersion=$myListPatches[0]->fld_version_number;
	else
		$myToUpdateVersion="1.5.1";
	return $myToUpdateVersion;
}	

function libGetCurrentBuild()
{
	global $database;
	$database->setQuery("SELECT * FROM patchmanagement_history ORDER BY CAST( fld_patch_number AS UNSIGNED ) DESC LIMIT 0,1;");
	$myListPatches=$database->loadObjectList();
	if(count($myListPatches)>0)
		$myToUpdateVersion=$myListPatches[0]->fld_patch_number;
	else
		$myToUpdateVersion="0";
	return $myToUpdateVersion;
}	

function text_translation($data = '')
{
	$entities = array(
		"&nbsp;" =>chr(160),
		"&iexcl;" =>chr(161),
		"&cent;" =>chr(162),
		"&pound;" =>chr(163),
		"&curren;" =>chr(164),
		"&yen;" =>chr(165),
		"&brvbar;" =>chr(166),
		"&sect;" =>chr(167),
		"&uml;" =>chr(168),
		"&copy;" =>chr(169),
		"&ordf;" =>chr(170),
		"&laquo;" =>chr(171),
		"&not;" =>chr(172),
		"&shy;" =>chr(173),
		"&reg;" =>chr(174),
		"&macr;" =>chr(175),
		"&deg;" =>chr(176),
		"&plusmn;" =>chr(177),
		"&sup2;" =>chr(178),
		"&sup3;" =>chr(179),
		"&acute;" =>chr(180),
		"&micro;" =>chr(181),
		"&para;" =>chr(182),
		"&middot;" =>chr(183),
		"&cedil;" =>chr(184),
		"&sup1;" =>chr(185),
		"&ordm;" =>chr(186),
		"&raquo;" =>chr(187),
		"&frac14;" =>chr(188),
		"&frac12;" =>chr(189),
		"&frac34;" =>chr(190),
		"&iquest;" =>chr(191),
		"&Agrave;" =>chr(192),
		"&Aacute;" =>chr(193),
		"&Acirc;" =>chr(194),
		"&Atilde;" =>chr(195),
		"&Auml;" =>chr(196),
		"&Aring;" =>chr(197),
		"&AElig;" =>chr(198),
		"&Ccedil;" =>chr(199),
		"&Egrave;" =>chr(200),
		"&Eacute;" =>chr(201),
		"&Ecirc;" =>chr(202),
		"&Euml;" =>chr(203),
		"&Igrave;" =>chr(204),
		"&Iacute;" =>chr(205),
		"&Icirc;" =>chr(206),
		"&Iuml;" =>chr(207),
		"&ETH;" =>chr(208),
		"&Ntilde;" =>chr(209),
		"&Ograve;" =>chr(210),
		"&Oacute;" =>chr(211),
		"&Ocirc;" =>chr(212),
		"&Otilde;" =>chr(213),
		"&Ouml;" =>chr(214),
		"&times;" =>chr(215),
		"&Oslash;" =>chr(216),
		"&Ugrave;" =>chr(217),
		"&Uacute;" =>chr(218),
		"&Ucirc;" =>chr(219),
		"&Uuml;" =>chr(220),
		"&Yacute;" =>chr(221),
		"&THORN;" =>chr(222),
		"&szlig;" =>chr(223),
		"&agrave;" =>chr(224),
		"&aacute;" =>chr(225),
		"&acirc;" =>chr(226),
		"&atilde;" =>chr(227),
		"&auml;" =>chr(228),
		"&aring;" =>chr(229),
		"&aelig;" =>chr(230),
		"&ccedil;" =>chr(231),
		"&egrave;" =>chr(232),
		"&eacute;" =>chr(233),
		"&ecirc;" =>chr(234),
		"&euml;" =>chr(235),
		"&igrave;" =>chr(236),
		"&iacute;" =>chr(237),
		"&icirc;" =>chr(238),
		"&iuml;" =>chr(239),
		"&eth;" =>chr(240),
		"&ntilde;" =>chr(241),
		"&ograve;" =>chr(242),
		"&oacute;" =>chr(243),
		"&ocirc;" =>chr(244),
		"&otilde;" =>chr(245),
		"&ouml;" =>chr(246),
		"&divide;" =>chr(247),
		"&oslash;" =>chr(248),
		"&ugrave;" =>chr(249),
		"&uacute;" =>chr(250),
		"&ucirc;" =>chr(251),
		"&uuml;" =>chr(252),
		"&yacute;" =>chr(253),
		"&thorn;" =>chr(254),
		"&yuml;" =>chr(255),
		"&OElig;" =>chr(338),
		"&oelig;" =>chr(339),
		"&Scaron;" =>chr(352),
		"&scaron;" =>chr(353),
		"&Yuml;" =>chr(376),
		"&fnof;" =>chr(402),
		"&circ;" =>chr(710),
		"&tilde;" =>chr(732),
		"&Alpha;" =>chr(913),
		"&Beta;" =>chr(914),
		"&Gamma;" =>chr(915),
		"&Delta;" =>chr(916),
		"&Epsilon;" =>chr(917),
		"&Zeta;" =>chr(918),
		"&Eta;" =>chr(919),
		"&Theta;" =>chr(920),
		"&Iota;" =>chr(921),
		"&Kappa;" =>chr(922),
		"&Lambda;" =>chr(923),
		"&Mu;" =>chr(924),
		"&Nu;" =>chr(925),
		"&Xi;" =>chr(926),
		"&Omicron;" =>chr(927),
		"&Pi;" =>chr(928),
		"&Rho;" =>chr(929),
		"&Sigma;" =>chr(931),
		"&Tau;" =>chr(932),
		"&Upsilon;" =>chr(933),
		"&Phi;" =>chr(934),
		"&Chi;" =>chr(935),
		"&Psi;" =>chr(936),
		"&Omega;" =>chr(937),
		"&beta;" =>chr(946),
		"&gamma;" =>chr(947),
		"&delta;" =>chr(948),
		"&epsilon;" =>chr(949),
		"&zeta;" =>chr(950),
		"&eta;" =>chr(951),
		"&theta;" =>chr(952),
		"&iota;" =>chr(953),
		"&kappa;" =>chr(954),
		"&lambda;" =>chr(955),
		"&mu;" =>chr(956),
		"&nu;" =>chr(957),
		"&xi;" =>chr(958),
		"&omicron;" =>chr(959),
		"&pi;" =>chr(960),
		"&rho;" =>chr(961),
		"&sigmaf;" =>chr(962),
		"&sigma;" =>chr(963),
		"&tau;" =>chr(964),
		"&upsilon;" =>chr(965),
		"&phi;" =>chr(966),
		"&chi;" =>chr(967),
		"&psi;" =>chr(968),
		"&omega;" =>chr(969),
		"&thetasym;" =>chr(977),
		"&upsih;" =>chr(978),
		"&piv;" =>chr(982),
		"&ensp;" =>chr(8194),
		"&emsp;" =>chr(8195),
		"&thinsp;" =>chr(8201),
		"&zwnj;" =>chr(8204),
		"&zwj;" =>chr(8205),
		"&lrm;" =>chr(8206),
		"&rlm;" =>chr(8207),
		"&ndash;" =>chr(8211),
		"&mdash;" =>chr(8212),
		"&lsquo;" =>chr(8216),
		"&rsquo;" =>chr(8217),
		"&sbquo;" =>chr(8218),
		"&ldquo;" =>chr(8220),
		"&rdquo;" =>chr(8221),
		"&bdquo;" =>chr(8222),
		"&dagger;" =>chr(8224),
		"&Dagger;" =>chr(8225),
		"&bull;" =>chr(8226),
		"&hellip;" =>chr(8230),
		"&permil;" =>chr(8240),
		"&prime;" =>chr(8242),
		"&Prime;" =>chr(8243),
		"&lsaquo;" =>chr(8249),
		"&rsaquo;" =>chr(8250),
		"&oline;" =>chr(8254),
		"&frasl;" =>chr(8260),
		"&euro;" =>chr(8364),
		"&weierp;" =>chr(8472),
		"&image;" =>chr(8465),
		"&real;" =>chr(8476),
		"&trade;" =>chr(8482),
		"&alefsym;" =>chr(8501),
		"&larr;" =>chr(8592),
		"&uarr;" =>chr(8593),
		"&rarr;" =>chr(8594),
		"&darr;" =>chr(8595),
		"&harr;" =>chr(8596),
		"&crarr;" =>chr(8629),
		"&lArr;" =>chr(8656),
		"&uArr;" =>chr(8657),
		"&rArr;" =>chr(8658),
		"&dArr;" =>chr(8659),
		"&hArr;" =>chr(8660),
		"&forall;" =>chr(8704),
		"&part;" =>chr(8706),
		"&exist;" =>chr(8707),
		"&empty;" =>chr(8709),
		"&nabla;" =>chr(8711),
		"&isin;" =>chr(8712),
		"&notin;" =>chr(8713),
		"&ni;" =>chr(8715),
		"&prod;" =>chr(8719),
		"&sum;" =>chr(8721),
		"&minus;" =>chr(8722),
		"&lowast;" =>chr(8727),
		"&radic;" =>chr(8730),
		"&prop;" =>chr(8733),
		"&infin;" =>chr(8734),
		"&ang;" =>chr(8736),
		"&and;" =>chr(8743),
		"&or;" =>chr(8744),
		"&cap;" =>chr(8745),
		"&cup;" =>chr(8746),
		"&int;" =>chr(8747),
		"&there4;" =>chr(8756),
		"&sim;" =>chr(8764),
		"&cong;" =>chr(8773),
		"&asymp;" =>chr(8776),
		"&ne;" =>chr(8800),
		"&equiv;" =>chr(8801),
		"&le;" =>chr(8804),
		"&ge;" =>chr(8805),
		"&sub;" =>chr(8834),
		"&sup;" =>chr(8835),
		"&nsub;" =>chr(8836),
		"&sube;" =>chr(8838),
		"&supe;" =>chr(8839),
		"&oplus;" =>chr(8853),
		"&otimes;" =>chr(8855),
		"&perp;" =>chr(8869),
		"&sdot;" =>chr(8901),
		"&lceil;" =>chr(8968),
		"&rceil;" =>chr(8969),
		"&lfloor;" =>chr(8970),
		"&rfloor;" =>chr(8971),
		"&lang;" =>chr(9001),
		"&rang;" =>chr(9002),
		"&loz;" =>chr(9674),
		"&spades;" =>chr(9824),
		"&clubs;" =>chr(9827),
		"&hearts;" =>chr(9829),
		"&diams;" =>chr(9830) );
	return strtr($data, $entities);
}


function time_start_counter()
{
	global $starttime;
	$starttime = microtime();
	$startarray = explode(" ", $starttime);
	$starttime = $startarray[1] + $startarray[0];	
}

function time_get_counter()
{
	global $starttime;
	$endtime = microtime();
	$endarray = explode(" ", $endtime);
	$endtime = $endarray[1] + $endarray[0];
	$totaltime = $endtime - $starttime;
	$totaltime = round($totaltime,5);
	//echo "This page loaded in $totaltime seconds.";
	return $totaltime;
}

function showToutateamVersionAndBuild()
{
	global $version,$buildnb;
	echo "<strong>\n";
	echo getTranslation("Version Toutateam")."&nbsp;:&nbsp;v".$version."<br />\n";
	echo getTranslation("Build Nb")."&nbsp;:&nbsp;v".$buildnb."<br />\n";
	echo "</strong>";
}

function tt_glob($mask)
{
	global $safe_mode;
	$debug=false;
	if(function_exists("glob") && !$safe_mode)
		return glob($mask);
	if($debug) echo __LINE__." Appel fonction TT<br>\n";
	$myFolderName=dirname($mask);
	$myFilter=basename($mask);
	if($debug) echo __LINE__." Ouverture répertoire : ".$myFolderName." avec le masque : ".$myFilter."<br>\n";
	$d = dir($myFolderName."/");
	$myFilesList=array();
	//preg_match('|\.php$|', $file)
	$myFilter=str_replace(".","\\.",$myFilter);
	$myFilter=str_replace("_","",$myFilter);
	$myFilter=str_replace("*","(.+?)",$myFilter);
	$myFiltrePreg="/".$myFilter."$/i";
	//echo "Filtre preg : ".$myFiltrePreg."<br>\n";
	while (false !== ($value = $d->read()))
	{
		if($debug) echo __LINE__." ".$value." / ".$myFiltrePreg;
		if($value!="." && $value!=".." && preg_match($myFiltrePreg, $value) )
		{
			if($debug) echo " pass";
			$myFilesList[]=$myFolderName."/".$value;
		}
		if($debug) echo "<br>\n";
	}
	if($debug) echo __LINE__." ".print_r($myFilesList,true);
	return $myFilesList;	
}

function TT_GetEnv()
{
	global $TTEnv;
	return $TTEnv;
}


class TT_Env
{

	var $DatePref;
	var $ResourceRd;
	var $ResourceRdMult;
	var $ResourceWr;
	var $Tools;
	var $User;
	var $FullDayLimits;
	var $DatabaseTables;
	var $Prefs;
	var $GroupOptions;
	var $Colors;
	
	var $database;
	var $user_ID;
	var $user_firstname;
	var $user_name;
	var $user_group;
	var $user_email;
	var $user_kurz;
	var $user_loginname;
	var $user_smsnr;
	var $path_pre;
	
	function TT_Env()
	{
		global $TheDatePref,$TheResourceRd,$TheResourceRdMult,$TheResourceWr,$TheTools;
		global $TheClsUser,$TheFullDayLimits,$TheDatabaseTables,$database,$ThePrefs,$TheGroupOptions;
		
		$this->DatePref=$TheDatePref;
		$this->ResourceRd=$TheResourceRd;
		$this->ResourceRdMult=$TheResourceRdMult;
		$this->ResourceWr=$TheResourceWr;
		$this->Tools=$TheTools;
		$this->User=$TheClsUser;
		$this->FullDayLimits=$TheFullDayLimits;
		$this->DatabaseTables=$TheDatabaseTables;
		$this->Prefs=$ThePrefs;
		$this->GroupOptions=$TheGroupOptions;
		
		global $bgcolor1,$bgcolor2,$bgcolor3,$bgcolor4;
		if(!isset($this->Colors)) $this->Colors=new stdClass();
		$this->Colors->bgcolor1=$bgcolor1;
		$this->Colors->bgcolor2=$bgcolor2;
		$this->Colors->bgcolor3=$bgcolor3;
		$this->Colors->bgcolor4=$bgcolor4;
		
		global $tagesanfang,$tagesende;
		if(!isset($this->Calendar)) $this->Calendar=new stdClass();
		$this->Calendar->DayBegin=$tagesanfang;
		$this->Calendar->DayEnd=$tagesende;
		
		global $user_ID,$user_kurz,$user_group,$user_name,$user_firstname,$user_email,$user_loginname,$user_smsnr,$path_pre;
		$this->database=$database;
		$this->user_ID=$user_ID;
		$this->user_firstname=$user_firstname;
		$this->user_name=$user_name;
		$this->user_group=$user_group;
		$this->user_email=$user_email;
		$this->user_kurz=$user_kurz;
		$this->user_loginname=$user_loginname;
		$this->user_smsnr=$user_smsnr;
		$this->path_pre=$path_pre;
	}
}
