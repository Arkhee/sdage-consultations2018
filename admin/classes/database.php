<?php
// $Id: database.php,v 1.8 2007/03/06 02:34:05 yb Exp $
/**
* Content code
* @package Mambo Open Source
* @Copyright (C) 2000 - 2003 Miro International Pty Ltd
* @ All rights reserved
* @ Mambo Open Source is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision: 1.8 $
**/

//defined( '_VALID_EXT' ) or die( 'Direct Access to this location is not allowed.' );

/**
* @package MOS
* @copyright 2000-2003 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html. GNU Public License
* @version 2.5 $Revision: 1.8 $
* @author Andrew Eddie <eddieajau@user.sourceforge.net>
*/

/**
* Database connector class
*
* <b>Example:</b>
* <code>
* $db = new database( 'localhost', 'root', '', 'mambo', 'mos_' );
* $db->setQuery( "SELECT * FROM #__mdtb_users" );
* if ($db->query()) {
*	echo 'ok';
* } else {
*	echo $db->stderr();
* }
* </code>
*
* @package MOS
* @subpackage LoudMouth
* @author Andrew Eddie <eddieajau@user.sourceforge.net>
*/
class database {
/** @var string Internal variable to hold the query sql */
	var $_sql='';
/** @var int Internal variable to hold the database error number */
	var $_errorNum=0;
/** @var string Internal variable to hold the database error message */
	var $_errorMsg='';
/** @var string Internal variable to hold the prefix used on all database tables */
	var $_table_prefix='';
/** @var Internal variable to hold the connector resource */
	var $_resource='';
/** @var Internal variable to hold the last query cursor */
	var $_cursor=null;
	
	var $latest_insert_id=null; // Modif YB du 18/03/2006 - Ajout propriété pour stocker l'ID d'insertion, en raison du logger

/**
* Database object constructor
* @param string Database host
* @param string Database user name
* @param string Database user password
* @param string Database name
* @param string Common prefix for all tables
*/
	function database( $host='localhost', $user, $pass, $db, $table_prefix,$newconnect=false ) {
	// perform a number of fatality checks, then die gracefully
		function_exists( 'mysql_connect' )
			or die( 'FATAL ERROR: MySQL support not available.  Please check your configuration.' );
		$this->_resource = mysql_connect( $host, $user, $pass,$newconnect )
			or die( 'FATAL ERROR: Connection to database server failed.' );
		mysql_select_db($db,$this->_resource)
			or die( "FATAL ERROR: Database not found. Operation failed with error: ".mysql_error());
		$this->_table_prefix = $table_prefix;
		
		
		mysql_query($this->_resource,"SET NAMES 'utf8'"); 
		mysql_query($this->_resource,"SET CHARACTER SET utf8");  
		mysql_query($this->_resource,"SET SESSION collation_connection = 'utf8_unicode_ci'"); 
	}
/**
* Execute a database query and returns the result
* @param string The SQL query
* @return resource Database resource identifier.  Refer to the PHP manual for more information.
* @deprecated This function is included for tempoary backward compatibility
*/
	function openConnectionWithReturn($query){
		$result=mysql_query($query,$this->_resource) or die("Query failed with error: ".mysql_error());
		return $result;
	}
/**
* Execute a database query
* @param string The SQL query
* @deprecated This function is included for temporary backward compatibility
*/
	function openConnectionNoReturn($query){
		mysql_query($query,$this->_resource) or die("Query failed with error: ".mysql_error());
	}
/**
* @return int The error number for the most recent query
*/
	function getErrorNum() {
		return $this->_errorNum;
	}
/**
* @return string The error message for the most recent query
*/
	function getErrorMsg() {
		return str_replace( array( "\n", "'" ), array( '\n', "\'" ), $this->_errorMsg );
	}
/**
* Get a database escaped string
* @return string
*/
	function getEscaped( $text ) {
		return mysql_escape_string( $text );
	}
/**
* Sets the SQL query string for later execution.
*
* This function replaces a string identifier <var>$prefix</var> with the
* string held is the <var>_table_prefix</var> class variable.
*
* @param string The SQL query
* @param string The common table prefix
*/
	function setQuery( $sql, $prefix='#__' ) {
		$this->_sql = str_replace( $prefix, $this->_table_prefix, $sql );
	}
/**
* @return string The current value of the internal SQL vairable
*/
	function getQuery() {
		return "<pre>$this->_sql</pre>";
	}
	
/**
 * Escapes the characters of a query to protect if from intrusion
 * @return string containing the protected content
 */	

	function escape($string)
	{
		return mysql_real_escape_string($string, $this->_resource);
	} 

/**
* Execute the query
* @return mixed A database resource if successful, FALSE if not.
*/
	function query() {
		$debug=false;
		//global $debugspecif;
		//$debug=$debugspecif;
		
		if($debug) echo "query : début<br>\n";
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		
		if($debug) echo "query : avant<br>\n";
		//if(function_exists("mysql_logger")) mysql_logger($this->_sql,"before");
		$this->_cursor = mysql_query( $this->_sql, $this->_resource );
		$this->latest_insert_id=mysql_insert_id($this->_resource);
		//if(function_exists("mysql_logger")) mysql_logger($this->_sql,"after");
		if($debug) echo "query : après<br>\n";
		if( !$this->_cursor ) {
			$this->_errorNum = mysql_errno( $this->_resource );
			$this->_errorMsg = mysql_error( $this->_resource )." SQL=$this->_sql";
			return false;
		}
		if($debug) echo "query : fin<br>\n";
		return $this->_cursor;
	}
/**
* Diagnostic function
*/
	function explain() {
		$temp = $this->_sql;
		$this->_sql = "EXPLAIN $this->_sql";
		$this->query();

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buf = "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" bgcolor=\"#000000\" align=\"center\">";
		$buf .= $this->getQuery();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($first) {
				$buf .= "<tr>";
				foreach ($row as $k=>$v) {
					$buf .= "<th bgcolor=\"#ffffff\">$k</th>";
				}
				$buf .= "</tr>";
				$first = false;
			}
			$buf .= "<tr>";
			foreach ($row as $k=>$v) {
				$buf .= "<td bgcolor=\"#ffffff\">$v</td>";
			}
			$buf .= "</tr>";
		}
			$buf .= "</table><br />&nbsp;";
		mysql_free_result( $cur );

		$this->_sql = $temp;

		return "<div style=\"background-color:#FFFFCC\" align=\"left\">$buf</div>";
	}
/**
* @return int The number of rows returned from the most recent query.
*/
	function getNumRows( $cur=null ) {
		return mysql_num_rows( $cur ? $cur : $this->_cursor );
	}

/**
* This method loads the first field of the first row returned by the query.
*
* @return The value returned in the query or null if the query failed.
*/
	function loadResult() {
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row[0];
		}
		mysql_free_result( $cur );
		return $ret;
	}
/**
* Load an array of single field results into an array
*/
	function loadResultArray($numinarray = 0) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			$array[] = $row[$numinarray];
		}
		mysql_free_result( $cur );
		return $array;
	}
/**
* This global function loads the first row of a query into an object
*
* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
* If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
* @param string The SQL query
* @param object The address of variable
*/
	function loadObject( &$object,$thedebug=false ) {
		$debug=$thedebug;
		if($debug) echo "loadObject<br>\n";
		if ($object != null) {
			if($debug) echo "loadObject if true<br>\n";
			if (!($cur = $this->query())) {
				if($debug) echo "loadObject false<br>\n";
				return false;
			}
			if($debug) echo "loadObject query true<br>\n";
			if ($array = mysql_fetch_assoc( $cur )) {
				if($debug) echo "loadObject mysql_fetch_assoc true<br>\n";
				mysql_free_result( $cur );
				if($debug) echo "loadObject mysql_free_result<br>\n";
				mosBindArrayToObject( $array, $object );
				if($debug) echo "loadObject ok<br>\n";
				return true;
			} else {
				if($debug) echo "loadObject false<br>\n";
				return false;
			}
		} else {
			if($debug) echo "loadObject if false<br>\n";
			if ($cur = $this->query()) {
				if ($object = mysql_fetch_object( $cur )) {
					mysql_free_result( $cur );
					if($debug) echo "loadObject ok<br>\n";
					return true;
				} else {
					if($debug) echo "loadObject false : ".mysql_errno().":".mysql_error()."<pre>".print_r($object,true)."</pre>".$this->getQuery()."<br>\n";
					$object = null;
					return false;
				}
			} else {
				if($debug) echo "loadObject false<br>\n";
				return false;
			}
		}
	}
/**
* Load a list of database objects
* @param string The field name of a primary key
* @return array If <var>key</var> is empty as sequential list of returned records.
* If <var>key</var> is not empty then the returned array is indexed by the value
* the database key.  Returns <var>null</var> if the query fails.
*/
	function loadObjectList( $key='' ) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_object( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
/**
* @return The first row of the query.
*/
	function loadRow() {
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row;
		}
		mysql_free_result( $cur );
		return $ret;
	}
/**
* Load a list of database rows (numeric column indexing)
* @param string The field name of a primary key
* @return array If <var>key</var> is empty as sequential list of returned records.
* If <var>key</var> is not empty then the returned array is indexed by the value
* the database key.  Returns <var>null</var> if the query fails.
*/
	function loadRowList( $key='' ) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
/**
* Document::db_insertObject()
*
* { Description }
*
* @param [type] $keyName
* @param [type] $verbose
*/
	function insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
		$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v == NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = "`$k`";
			$values[] = "'" . $this->getEscaped( $v ) . "'";
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		if(!is_array($fields))
			echo "Requête : ".$this->getQuery();
		($verbose) && print "$sql<br />\n";
		if (!$this->query()) {
			return false;
		}
		//$id = mysql_insert_id(); // Modif YB du 18/03/2006 - Ajout propriété contenant le last id, définie dans le query()
		$id=$this->latest_insert_id;
		($verbose) && print "id=[$id]<br />\n";
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

/**
* Document::db_updateObject()
*
* { Description }
*
* @param [type] $updateNulls
*/
	function updateObject( $table, &$object, $keyName, $updateNulls=true ) {
		$fmtsql = "UPDATE $table SET %s WHERE %s";
		foreach (get_object_vars( $object ) as $k => $v) {
			if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
				continue;
			}
			if( $k == $keyName ) { // PK not to be updated
				$where = "$keyName='" . $this->getEscaped( $v ) . "'";
				continue;
			}
			if ($v === NULL && !$updateNulls) {
				continue;
			}
			if( $v == '' ) {
				$val = "''";
			} else {
				$val = "'" . $this->getEscaped( $v ) . "'";
			}
			$tmp[] = "`$k`=$val";
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		//echo "Requête Update :".$this->getQuery();
		$myReturn=$this->query();
		if(!$myReturn)
		{
			//echo "Erreur : n°".$this->_errorNum."=".$this->_errorMsg."<br>\n";
		}
		return $myReturn;
	}

	function getObjectList( $index=null, $maxrows=NULL ) {
		$this->_errorNum = 0;
		$this->_errorMsg = '';

		if (!($cur = mysql_query( $this->_sql,$this->_resource ))) {;
			$this->_errorNum = mysql_errno($this->_resource);
			$this->_errorMsg = mysql_error($this->_resource);
			return false;
		}
		$list = array();
		$cnt = 0;
		while ($obj = mysql_fetch_object( $cur )) {
			if ($index) {
				$list[$obj->$index] = $obj;
			} else {
				$list[] = $obj;
			}
			if( $maxrows && $maxrows == $cnt++ ) {
				break;
			}
		}
		mysql_free_result( $cur );
		return $list;
	}
/**
* @param boolean If TRUE, displays the last SQL statement sent to the database
* @return string A standised error message
*/
	function stderr( $showSQL = false ) {
		return "DB function failed with error number $this->_errorNum"
			."<br /><font color=\"red\">$this->_errorMsg</font>"
			.($showSQL ? "<br />SQL = <pre>$this->_sql</pre>" : '');
	}

	function insertid()
	{
		return $this->latest_insert_id;
		//return mysql_insert_id();
	}

	function getVersion()
	{
		return mysql_get_server_info($this->_resource);
	}
	
/**
* Fudge method for ADOdb compatibility
*/
	function GenID( $foo1=null, $foo2=null ) {
		return '0';
	}
	
	function testUnique($theTableName,$theTable,$theObject)
	{
		$myWhereString="";
		foreach($theTable as $key => $value)
		{
			if(isset($value["unique"]))
			{
				if($value["unique"]=="1")
				{
					$myWhereString.=($myWhereString=="")?($key."='".$theObject->$key."'"):(" AND ".$key."='".$theObject->$key."'");
				}
			}
		}
		if(trim($myWhereString)=="") return false;
		//echo "Requête where : ".$myWhereString."<br>\n";
		$myQuery="SELECT COUNT(*) FROM ".$theTableName." WHERE ".$myWhereString.";";
		$this->setQuery($myQuery);
		$myRes=$this->loadRow();
		if($myRes[0]>0) return true;
		return false;
	}
	function getFwPrice($theFwId,$theOrig,$theDest,$thePrice="sell",$theContainer="20")
	{
		$myQuery="SELECT * FROM #__priceperforwarder WHERE id_forwarder='".$theFwId."' AND pr_departureport='".$theOrig."' AND pr_arrivalport='".$theDest."' AND pr_container='".$theContainer."';";
		$this->setQuery($myQuery);
		$myObjList=$this->loadObjectList();
		if(count($myObjList)>0) $myRes=$myObjList[0];
		if(isset($myRes))
		{
			if($thePrice=="buy") if($myRes->pr_buying>0) return $myRes->pr_buying;
			if($thePrice=="sell") if($myRes->pr_selling>0) return $myRes->pr_selling;
		}
		return 0;
	}
	
	function showfromtype($theValue,$theType,$theTable="")
	{
		switch($theType)
		{
			case "hiddendate":
			case "hiddendatetime":
			case "date":
			case "datetime":
				return date("d/m/Y",strtotime($theValue));
			case "longtext": 
				return (substr($theValue,0,30)." ...");
			case "liste":
				if($theValue==-1) return "(aucun)";
				if($theTable["liste"]["table"]!="")
				{
					$this->setQuery("SELECT id,".$theTable["liste"]["label"]." FROM ".$theTable["liste"]["table"]." WHERE id=RIGHT('$theValue',(LENGTH('$theValue')-INSTR('$theValue',',')));");
					//echo $this->getQuery();
					$myResListe=$this->loadObjectList();
					if(count($myResListe)>0)
					{
						$myRes=$myResListe[0];
						return $myRes->$theTable["liste"]["label"];
					}
					else
					{
						return "";
					}
				}
				else
				{
					return $theValue;
				}
				//echo $this->getQuery();
			case "photo":
				$myDisp="<center><img align=\"absolutemiddle\" src=\"../images/cancel.gif\" border=\"0\"></center>";
				//echo "Test de ".$theTable["imagepath"].$theValue."\n";
				if(file_exists($theTable["imagepath"].$theValue))
				{
					$myDisp="<center><img src=\"../images/ok.gif\" border=\"0\"></center>";
					//echo " ... existe.<br>\n";
				}
				else
				{
					//echo " ... n'existe pas.<br>\n";
				}
				return $myDisp;
			case "text":
			case "mail":
			default :
				return $theValue;
		}
	}
	
	function showDataFromParent($theTableName="",$theLabel,$theCurID=-1,$theParents)
	{
		$myWhereClause="";
		foreach($theParents as $key=>$value)
		{
			$myWhereClause.=($myWhereClause=="")?" WHERE ":" AND ";
			$myWhereClause.=$key."='".$value."'";
		}
		//echo "Recherche d'un data pour ".$theTableName." avec l'ID : ".$theCurID."<br>\n";
		if($theCurID<=0) return "";
		$myTableName=$theTableName;
		$myLabel=$theLabel;
		//echo "Le label porte sur la table ".$myTableName." avec le champ ".$myLabel."<br>\n";
		$myQuery="SELECT ".$myLabel." FROM ".$myTableName.$myWhereClause.";";
		$this->setQuery($myQuery);
		//echo "Requête : ".$this->getQuery();
		$myRes=$this->loadObjectList();
		if(!isset($myRes[0])) $myResult="-"; else $myResult=$myRes[0]->$myLabel;
		return $myResult;
	}
	
	function showData($theTableName="",$theLabel,$theCurID=-1)
	{
		//echo "Recherche d'un data pour ".$theTableName." avec l'ID : ".$theCurID."<br>\n";
		if($theCurID<=0) return "";
		$myTableName=$theTableName;
		$myLabel=$theLabel;
		//echo "Le label porte sur la table ".$myTableName." avec le champ ".$myLabel."<br>\n";
		$myQuery="SELECT ".$myLabel." FROM ".$myTableName." WHERE id=".$theCurID.";";
		$this->setQuery($myQuery);
		//echo "Requête : ".$this->getQuery();
		$myRes=$this->loadObjectList();
		if(!isset($myRes[0])) $myResult="-"; else $myResult=$myRes[0]->$myLabel;
		return $myResult;
	}
	function showRecordName($theTable="",$theCurID=-1)
	{
		//echo "Recherche d'un label pour ".$theTable." avec l'ID : ".$theCurID."<br>\n";
		if($theCurID<=0) return "";
		$myTableName=$theTable["table"];
		$myLabel=$theTable["label"];
		//echo "Le label porte sur la table ".$myTableName." avec le champ ".$myLabel."<br>\n";
		$myQuery="SELECT ".$myLabel." FROM ".$myTableName." WHERE id=".$theCurID.";";
		$this->setQuery($myQuery);
		//echo "Requête : ".$this->getQuery();
		$myRes=$this->loadObjectList();
		return $myRes[0]->$myLabel;		
	}
	function showLabel($theTable="",$theCurID=-1)
	{
		//echo "Recherche d'un label pour ".$theTable." avec l'ID : ".$theCurID."<br>\n";
		if($theCurID<=0) return "";
		$myTableName=$theTable["liste"]["table"];
		$myLabel=$theTable["liste"]["label"];
		//echo "Le label porte sur la table ".$myTableName." avec le champ ".$myLabel."<br>\n";
		$myQuery="SELECT ".$myLabel." FROM ".$myTableName." WHERE id=".$theCurID.";";
		//echo "Requête : ".$myQuery;
		$this->setQuery($myQuery);
		$myRes=$this->loadObjectList();
		return $myRes[0]->$myLabel;
	}
	function getFirstObject($theTable)
	{
		$this->setQuery("SELECT * FROM ".$theTable.";");
		$myResList=$this->loadObjectList();
		if(count($myResList)>0) return $myResList[0]; else return FALSE;
	}
	
	function getObjectFromTable($theTable,$theId,$theField="ID")
	{
		$myQuery="SELECT * FROM ".$theTable." WHERE ".$theField."='$theId';";
		$this->setQuery($myQuery);
		// echo "Requête : ".$this->getQuery();
		$myResList=$this->loadObjectList();
		if(count($myResList)>0) $myRes=$myResList[0];
		else $myRes=FALSE;
		return $myRes;
	}
	
	function createEvent($theAut,$theDest,$theTitle,$thePlace,$theRmq,$theDate,$theBegin,$theEnd)
	{
		$myRDV=new stdClass();
		$myRDV->von=$theAut;
		$myRDV->an=$theDest;
		$myRDV->event=$theTitle;
		$myRDV->note=$thePlace;
		$myRDV->note2=$theRmq;
		$myRDV->datum=date("Y-m-d",strtotime($theDate));
		$myRDV->anfang=$theBegin;
		$myRDV->ende=$theEnd;
		$myRDV->erstellt=date("YmdHis");
		$this->insertObject("termine",$myRDV,"ID");
		return $myRDV->ID;
	}
	function checkEvent($theAut,$theDest,$theTitle,$thePlace,$theRmq,$theDate,$theBegin,$theEnd)
	{
		if(strtotime($theDate)<0) return false;
		$myDate=date("Y-m-d",strtotime($theDate));
		$this->setQuery("SELECT * FROM termine WHERE von=".$theAut." AND an=".$theDest." AND event='".$theTitle."' AND note='".$thePlace."' AND note2='".$theRmq."' AND datum='".$myDate."' AND anfang='".$theBegin."' AND ende='".$theEnd."';");
		$myListEvents=$this->loadObjectList();
		if(count($myListEvents)>0) return true;
		return false;
	}
	function deleteEvent($theAut,$theDest,$theTitle,$thePlace,$theRmq,$theDate,$theBegin,$theEnd)
	{
		if(strtotime($theDate)<0) return false;
		$myDate=date("Y-m-d",strtotime($theDate));
		$this->setQuery("DELETE FROM termine WHERE von=".$theAut." AND an=".$theDest." AND event='".$theTitle."' AND note='".$thePlace."' AND note2='".$theRmq."' AND datum='".$myDate."' AND anfang='".$theBegin."' AND ende='".$theEnd."';");
		return $this->query();
	}
	
	function getTablesList()
	{
		$this->setQuery("SHOW TABLES;");
		$myListTables=$this->loadObjectList();
		$listHeader="";
		$myTablesList=array();
		if(is_array($myListTables) && count($myListTables)>0)
		{
			foreach($myListTables as $curtable)
			{
				if($listHeader=="")
				{
					$myArrayTable=get_object_vars($curtable);
					foreach($myArrayTable as $headername => $headerval)
						$listHeader=$headername;
				}
				$myTablesList[]=$curtable->$listHeader;
			}
		}
		return $myTablesList;
	}

}

/**
* Copy the named array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
*/
function mosBindArrayToObject( $array, &$obj, $ignore='', $prefix=NULL, $checkSlashes=true ) {
	if (!is_array( $array ) || !is_object( $obj )) {
		return (false);
	}

	foreach (get_object_vars($obj) as $k => $v) {
		if( substr( $k, 0, 1 ) != '_' ) {			// internal attributes of an object are ignored
			if (strpos( $ignore, $k) === false) {
				if ($prefix) {
					$ak = $prefix . $k;
				} else {
					$ak = $k;
				}
				if (isset($array[$ak])) {
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? mosStripslashes( $array[$ak] ) : $array[$ak];
				}
			}
		}
	}

	return true;
}



/**
 * Strip slashes from strings or arrays of strings
 * @param mixed The input string or array
 * @return mixed String or array stripped of slashes
 */
function mosStripslashes( &$value ) {
	$ret = '';
	if (is_string( $value )) {
		$ret = stripslashes( $value );
	} else {
		if (is_array( $value )) {
			$ret = array();
			foreach ($value as $key => $val) {
				$ret[$key] = mosStripslashes( $val );
			}
		} else {
			$ret = $value;
		}
	}
	return $ret;
}

/**
* mosDBTable Abstract Class.
* @abstract
* @package Joomla
* @subpackage Database
*
* Parent classes to all database derived objects.  Customisation will generally
* not involve tampering with this object.
* @package Joomla
* @author Andrew Eddie <eddieajau@users.sourceforge.net
*/
class mosDBTable {
	/** @var string Name of the table in the db schema relating to child class */
	var $_tbl 		= '';
	/** @var string Name of the primary key field in the table */
	var $_tbl_key 	= '';
	/** @var string Error message */
	var $_error 	= '';
	/** @var mosDatabase Database connector */
	var $_db 		= null;

	/**
	*	Object constructor to set table and key field
	*
	*	Can be overloaded/supplemented by the child class
	*	@param string $table name of the table in the db schema relating to child class
	*	@param string $key name of the primary key field in the table
	*/
	function mosDBTable( $table, $key, &$db ) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_db =& $db;
	}

	/**
	 * Returns an array of public properties
	 * @return array
	 */
	function getPublicProperties() {
		static $cache = null;
		if (is_null( $cache )) {
			$cache = array();
			foreach (get_class_vars( get_class( $this ) ) as $key=>$val) {
				if (substr( $key, 0, 1 ) != '_') {
					$cache[] = $key;
				}
			}
		}
		return $cache;
	}
	/**
	 * Filters public properties
	 * @access protected
	 * @param array List of fields to ignore
	 */
	function filter( $ignoreList=null ) {
		$ignore = is_array( $ignoreList );

		$iFilter = new InputFilter();
		foreach ($this->getPublicProperties() as $k) {
			if ($ignore && in_array( $k, $ignoreList ) ) {
				continue;
			}
			$this->$k = $iFilter->process( $this->$k );
		}
	}
	/**
	 *	@return string Returns the error message
	 */
	function getError() {
		return $this->_error;
	}
	/**
	* Gets the value of the class variable
	* @param string The name of the class variable
	* @return mixed The value of the class var (or null if no var of that name exists)
	*/
	function get( $_property ) {
		if(isset( $this->$_property )) {
			return $this->$_property;
		} else {
			return null;
		}
	}

	/**
	* Set the value of the class variable
	* @param string The name of the class variable
	* @param mixed The value to assign to the variable
	*/
	function set( $_property, $_value ) {
		$this->$_property = $_value;
	}

	/**
	 * Resets public properties
	 * @param mixed The value to set all properties to, default is null
	 */
	function reset( $value=null ) {
		$keys = $this->getPublicProperties();
		foreach ($keys as $k) {
			$this->$k = $value;
		}
	}
	/**
	*	binds a named array/hash to this object
	*
	*	can be overloaded/supplemented by the child class
	*	@param array $hash named array
	*	@return null|string	null is operation was satisfactory, otherwise returns an error
	*/
	function bind( $array, $ignore='' ) {
		if (!is_array( $array )) {
			$this->_error = strtolower(get_class( $this ))."::bind failed.";
			return false;
		} else {
			return mosBindArrayToObject( $array, $this, $ignore );
		}
	}

	/**
	*	binds an array/hash to this object
	*	@param int $oid optional argument, if not specifed then the value of current key is used
	*	@return any result from the database operation
	*/
	function load( $oid=null ) {
		$k = $this->_tbl_key;
		
		if ($oid !== null) {
			$this->$k = $oid;
		}
		
		$oid = $this->$k;
		
		if ($oid === null) {
			return false;
		}
		//Note: Prior to PHP 4.2.0, Uninitialized class variables will not be reported by get_class_vars().
		/*
		$class_vars = $this->getPublicProperties();
		foreach ($class_vars as $name => $value) {
			if ($name != $k) {
				$this->$name = $value;
			}
		}
		*/
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
			if (($name != $k) and ($name != "_db") and ($name != "_tbl") and ($name != "_tbl_key") && (substr($name,0,1)!="_") )
			{
				$this->$name = $value;
			}
		}

		$this->reset();
		
		$query = "SELECT *"
		. "\n FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '$oid'"
		;
		$this->_db->setQuery( $query );
		
		return $this->_db->loadObject( $this );
	}

	/**
	*	generic check method
	*
	*	can be overloaded/supplemented by the child class
	*	@return boolean True if the object is ok
	*/
	function check() {
		return true;
	}

	/**
	* Inserts a new row if id is zero or updates an existing row in the database table
	*
	* Can be overloaded/supplemented by the child class
	* @param boolean If false, null object variables are not updated
	* @return null|string null if successful otherwise returns and error message
	*/
	function store( $updateNulls=false ) {
		$k = $this->_tbl_key;

		if (isset($this->$k) && !is_null($this->$k)) {
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}
	/**
	*/
	function move( $dirn, $where='' ) {
		$k = $this->_tbl_key;

		$sql = "SELECT $this->_tbl_key, ordering FROM $this->_tbl";

		if ($dirn < 0) {
			$sql .= "\n WHERE ordering < $this->ordering";
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\n ORDER BY ordering DESC";
			$sql .= "\n LIMIT 1";
		} else if ($dirn > 0) {
			$sql .= "\n WHERE ordering > $this->ordering";
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\n ORDER BY ordering";
			$sql .= "\n LIMIT 1";
		} else {
			$sql .= "\nWHERE ordering = $this->ordering";
			$sql .= ($where ? "\n AND $where" : '');
			$sql .= "\n ORDER BY ordering";
			$sql .= "\n LIMIT 1";
		}

		$this->_db->setQuery( $sql );
//echo 'A: ' . $this->_db->getQuery();


		$row = null;
		if ($this->_db->loadObject( $row )) {
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$row->ordering'"
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}
//echo 'B: ' . $this->_db->getQuery();

			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$this->ordering'"
			. "\n WHERE $this->_tbl_key = '". $row->$k. "'"
			;
			$this->_db->setQuery( $query );
//echo 'C: ' . $this->_db->getQuery();

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}

			$this->ordering = $row->ordering;
		} else {
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$this->ordering'"
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );
//echo 'D: ' . $this->_db->getQuery();


			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}
		}
	}
	/**
	* Compacts the ordering sequence of the selected records
	* @param string Additional where query to limit ordering to a particular subset of records
	*/
	function updateOrder( $where='' ) {
		$k = $this->_tbl_key;

		if (!array_key_exists( 'ordering', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support ordering.";
			return false;
		}

		if ($this->_tbl == "#__content_frontpage") {
			$order2 = ", content_id DESC";
		} else {
			$order2 = '';
		}

		$query = "SELECT $this->_tbl_key, ordering"
		. "\n FROM $this->_tbl"
		. ( $where ? "\n WHERE $where" : '' )
		. "\n ORDER BY ordering$order2 "
		;
		$this->_db->setQuery( $query );
		if (!($orders = $this->_db->loadObjectList())) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
		// first pass, compact the ordering numbers
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {
			if ($orders[$i]->ordering >= 0) {
				$orders[$i]->ordering = $i+1;
			}
		}

		$shift = 0;
		$n=count( $orders );
		for ($i=0; $i < $n; $i++) {
			//echo "i=$i id=".$orders[$i]->$k." order=".$orders[$i]->ordering;
			if ($orders[$i]->$k == $this->$k) {
				// place 'this' record in the desired location
				$orders[$i]->ordering = min( $this->ordering, $n );
				$shift = 1;
			} else if ($orders[$i]->ordering >= $this->ordering && $this->ordering > 0) {
				$orders[$i]->ordering++;
			}
		}
	//echo '<pre>';print_r($orders);echo '</pre>';
		// compact once more until I can find a better algorithm
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {
			if ($orders[$i]->ordering >= 0) {
				$orders[$i]->ordering = $i+1;
				$query = "UPDATE $this->_tbl"
				. "\n SET ordering = '". $orders[$i]->ordering ."'"
				. "\n WHERE $k = '". $orders[$i]->$k ."'"
				;
				$this->_db->setQuery( $query);
				$this->_db->query();
	//echo '<br />'.$this->_db->getQuery();
			}
		}

		// if we didn't reorder the current record, make it last
		if ($shift == 0) {
			$order = $n+1;
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$order'"
			. "\n WHERE $k = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );
			$this->_db->query();
	//echo '<br />'.$this->_db->getQuery();
		}
		return true;
	}
	/**
	*	Generic check for whether dependancies exist for this object in the db schema
	*
	*	can be overloaded/supplemented by the child class
	*	@param string $msg Error message returned
	*	@param int Optional key index
	*	@param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
	*	@return true|false
	*/
	function canDelete( $oid=null, $joins=null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (is_array( $joins )) {
			$select = $k;
			$join = '';
			foreach( $joins as $table ) {
				$select .= ",\n COUNT(DISTINCT {$table['idfield']}) AS {$table['idfield']}";
				$join .= "\n LEFT JOIN {$table['name']} ON {$table['joinfield']} = $k";
			}

			$query = "SELECT $select"
			. "\n FROM $this->_tbl"
			. $join
			. "\n WHERE $k = ". $this->$k
			. "\n GROUP BY $k"
			;
			$this->_db->setQuery( $query );

			if ($obj = $this->_db->loadObject()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			$msg = array();
			foreach( $joins as $table ) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_( $table['label'] );
				}
			}

			if (count( $msg )) {
				$this->_error = "noDeleteRecord" . ": " . implode( ', ', $msg );
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	/**
	*	Default delete method
	*
	*	can be overloaded/supplemented by the child class
	*	@return true if successful otherwise returns and error message
	*/
	function delete( $oid=null ) {
		//if (!$this->canDelete( $msg )) {
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
		;
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			return true;
		} else {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
	}

	/**
	 * Checks out an object
	 * @param int User id
	 * @param int Object id
	 */
	function checkout( $user_id, $oid=null ) {
		if (!array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support checkouts.";
			return false;
		}
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}

		$time = date( 'Y-m-d H:i:s' );
		if (intval( $user_id )) {
			$user_id = intval( $user_id );
			// new way of storing editor, by id
			$query = "UPDATE $this->_tbl"
			. "\n SET checked_out = $user_id, checked_out_time = '$time'"
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );

            $this->checked_out = $user_id;
            $this->checked_out_time = $time;
		} else {
			$user_id = $this->_db->Quote( $user_id );
			// old way of storing editor, by name
			$query = "UPDATE $this->_tbl"
			. "\n SET checked_out = 1, checked_out_time = '$time', editor = $user_id"
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );

			$this->checked_out = 1;
			$this->checked_out_time = $time;
			$this->checked_out_editor = $user_id;
		}

		return $this->_db->query();
	}

	/**
	 * Checks in an object
	 * @param int Object id
	 */
	function checkin( $oid=null ) {
		if (!array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support checkin.";
			return false;
		}
		
		$k 			= $this->_tbl_key;
		$nullDate 	= $this->_db->getNullDate();

		if ($oid !== null) {
			$this->$k = intval( $oid );
		}
		if ($this->$k == NULL) {
			return false;
		}		
		
		$query = "UPDATE $this->_tbl"
		. "\n SET checked_out = 0, checked_out_time = '$nullDate'"
		. "\n WHERE $this->_tbl_key = ". $this->$k
		;
		$this->_db->setQuery( $query );

		$this->checked_out = 0;
		$this->checked_out_time = '';

		return $this->_db->query();
	}

	/**
	 * Increments the hit counter for an object
	 * @param int Object id
	 */
	function hit( $oid=null ) {
		global $mosConfig_enable_log_items;

		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = intval( $oid );
		}

		$query = "UPDATE $this->_tbl"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE $this->_tbl_key = '$this->id'"
		;
		$this->_db->setQuery( $query );
		$this->_db->query();

		if (@$mosConfig_enable_log_items) {
			$now = date( 'Y-m-d' );
			$query = "SELECT hits"
			. "\n FROM #__core_log_items"
			. "\n WHERE time_stamp = '$now'"
			. "\n AND item_table = '$this->_tbl'"
			. "\n AND item_id = ". $this->$k
			;
			$this->_db->setQuery( $query );
			$hits = intval( $this->_db->loadResult() );
			if ($hits) {
				$query = "UPDATE #__core_log_items"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE time_stamp = '$now'"
				. "\n AND item_table = '$this->_tbl'"
				. "\n AND item_id = " . $this->$k
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
			} else {
				$query = "INSERT INTO #__core_log_items"
				. "\n VALUES ( '$now', '$this->_tbl', ". $this->$k .", 1 )"
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
			}
		}
	}

	/**
	 * Tests if item is checked out
	 * @param int A user id
	 * @return boolean
	 */
	function isCheckedOut( $user_id=0 ) {
		if ($user_id) {
			return ($this->checked_out && $this->checked_out != $user_id);
		} else {
			return $this->checked_out;
		}
	}

	/**
	* Generic save function
	* @param array Source array for binding to class vars
	* @param string Filter for the order updating
	* @returns TRUE if completely successful, FALSE if partially or not succesful
	* NOTE: Filter will be deprecated in verion 1.1
	*/
	function save( $source, $order_filter='' ) {
		if (!$this->bind( $source )) {
			return false;
		}
		if (!$this->check()) {
			return false;
		}
		if (!$this->store()) {
			return false;
		}
		if (!$this->checkin()) {
			return false;
		}
		
		if ($order_filter) {
			$filter_value = $this->$order_filter;
			$this->updateOrder( $order_filter ? "`$order_filter` = '$filter_value'" : '' );
		}
		$this->_error = '';
		return true;
	}

	/**
	 * @deprecated As of 1.0.3, replaced by publish
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}

	/**
	 * Generic Publish/Unpublish function
	 * @param array An array of id numbers
	 * @param integer 0 if unpublishing, 1 if publishing
	 * @param integer The id of the user performnig the operation
	 * @since 1.0.4
	 */
	function publish( $cid=null, $publish=1, $user_id=0 ) {
		mosArrayToInts( $cid, array() );
		$user_id = intval( $user_id );
		$publish = intval( $publish );

		if (count( $cid ) < 1) {
			$this->_error = "No items selected.";
			return false;
		}

		$cids = 'id=' . implode( ' OR id=', $cid );

		$query = "UPDATE $this->_tbl"
		. "\n SET published = " . intval( $publish )
		. "\n WHERE ($cids)"
		. "\n AND (checked_out = 0 OR checked_out = $user_id)"
		;
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}

		if (count( $cid ) == 1) {
			$this->checkin( $cid[0] );
		}
		$this->_error = '';
		return true;
	}

	/**
	* Export item list to xml
	* @param boolean Map foreign keys to text values
	*/
	function toXML( $mapKeysToText=false ) {
		$xml = '<record table="' . $this->_tbl . '"';

		if ($mapKeysToText) {
			$xml .= ' mapkeystotext="true"';
		}
		$xml .= '>';
		foreach (get_object_vars( $this ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
		}
		$xml .= '</record>';

		return $xml;
	}
}
?>