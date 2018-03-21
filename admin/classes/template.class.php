<?php
include_once($path_pre."lib/lib.inc.php");
define("TEMPLATE_DEFAULT_ENCODING","iso-8859-1");
class TT_Template
{
    function TT_Template()
    {
    	
    }
	
	function HTML_getGeneralHeaders()
	{
		return "";
	}
	
    function HTMLHeader($theTitle,$theAdditionalHeaders="",$thePathPre)
    {
    	static $sent=0;
    	if($sent==0)
    	{
			echo TT_Template::HTMLHeaderBegin($theTitle,"",$thePathPre);
			echo TT_Template::HTML_getGeneralHeaders();
	    	echo $theAdditionalHeaders;
	    	echo TT_Template::HTMLHeaderEnd();
    	}
    	$sent=1;
    }

    function HTMLHeaderBegin($theTitle,$theDocType="",$thePathPre="")
    {
    	global $ThePrefs;
    	if(isset($ThePrefs) && is_object($ThePrefs) && isset($ThePrefs->encoding))
    		$myEncoding=$ThePrefs->encoding;
    	else
    		$myEncoding=TEMPLATE_DEFAULT_ENCODING;
    	
    	static $sent=0;
    	if($sent==0)
    	{
    		if($theDocType!="")
    			echo $theDocType;
    		else
    		
				echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
   			//echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">\n";
			//echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">\n";
	       	echo "<html>\n";
			echo "<head>\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".$myEncoding."\">\n";
			echo "<script src=\"".$thePathPre."lib/json2.js\"></script>\n";
			echo "<script src=\"".$thePathPre."lib/jquery/jquery.js\"></script>\n";
			echo "<script src=\"".$thePathPre."lib/jquery/thickbox/thickbox.js\"></script>\n";
			echo "<script src=\"".$thePathPre."lib/jquery/ui.datepicker.js\"></script>\n";
			echo "<script src=\"".$thePathPre."lib/functions.js\"></script>\n";
			echo "<script src=\"".$thePathPre."lib/fckeditor/fckeditor.js\"></script>\n";
			echo "<link rel=\"stylesheet\" href=\"".$thePathPre."lib/jquery/ui.datepicker.css\" type=\"text/css\" media=\"screen\">\n";
			echo "<link rel=\"stylesheet\" href=\"".$thePathPre."lib/jquery/thickbox/thickbox.css\" type=\"text/css\" media=\"screen\">\n";
			echo "<link rel=\"stylesheet\" href=\"".$thePathPre."styles.css\" type=\"text/css\" media=\"screen\">\n";
			//$myDatePickerOptionsArray[]="buttonImage: 'lib/jquery/images/calendar.gif'";
			echo "<title>".$theTitle."</title>\n";
			 
			echo TT_Template::HTML_getGeneralHeaders();
    	}
    	$sent=1;
    }

    function HTMLHeaderEnd()
    {
    	static $sent=0;
    	if($sent==0)
    	{
    		echo "</head>\n";
    	}
    }


    function HTMLBodyBegin($theOptionalBodyTags="",$theBGImage="",$theBGColor="")
    {
    	static $sent=0;
    	if($sent==0)
    	{
	    	global $backgroundimageflag,$bgcolor3;
	    	echo "<body ".$theOptionalBodyTags." ".(($theBGImage=="")?$backgroundimageflag:$theBGImage)."  bgcolor=\"".(($theBGColor=="")?$bgcolor3:$theBGColor)."\">\n";
    	}
    }
    function HTML_FormatEntry($theValue,$theFormat)
    {
    
    	$myEnv=new TT_Env();
		$TheDatePref=$myEnv->DatePref;
		if(!isset($TheDatePref->DispFormat))
    		$TheDatePref->DispFormat="Y-m-d";
    	switch($theFormat)
    	{
    		case "text":
    		default:
    			return stripslashes($theValue);
    			break;
    		case "date":
    			$myDate=strtotime($theValue);
    			if($myDate===false)
    				return $theValue;
    			return date($TheDatePref->DispFormat,$myDate);
    			break;
    		case "datetime":
    			$myDate=strtotime($theValue);
    			if($myDate===false)
    				return $theValue;
    			return date($TheDatePref->DispFormat." H:i:s",$myDate);
    			break;
    		case "currency":
    			$myVal=round($theValue,2);
    			return $myVal;
    			break;
    	}
    
    	return $theValue;
    }
    function HTML_TableFromList($theList,$theHeadersDisplay,$theUrl,$theKey="",$theClass="",$theParams="")
    {
    	$myTable="";
    	$debug=false;
    	if($debug) echo "Affichage de la liste<br>\n";
    	if(is_array($theList) && count($theList)>0)
    	{
    		if($debug) echo "La liste est un tableau non vide<br>\n";
    		$myTable.= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"".$theClass."\" ".$theParams.">\n";
    		$myTable.= "<thead><tr>\n";
    		foreach($theHeadersDisplay as $curheader)
    		{
    			$myTranslation=isset($curheader["text"])?$curheader["text"]:$curheader["key"];
    			if(isset($curheader["link"]) && $curheader["link"]!="")
    			{
    				$myLinkB="<a href=\"".$curheader["link"]."\">";
    				$myLinkE="</a>\n";
    			}
    			$myTable.= "<td class=\"lightbordercolor\">\n".$myLinkB.$myTranslation.$myLinkE."</td>\n";
    		}
    		$myTable.= "</tr></thead>\n";
    		if($debug) echo "Entête affiché<br>\n";
    		$myLinkOpen=""; $myLinkClose="";
    		foreach($theList as $curdata)
    		{
	    		$myTable.= "<tr>\n";
	    		foreach($theHeadersDisplay as $curheader)
	    		{
	    			$keyheader=$curheader["key"];
	    			$myData=isset($curdata->$keyheader)?$curdata->$keyheader:"&nbsp;";
	    			if($myData!="&nbsp;")
	    				$myData=TT_Template::HTML_FormatEntry($myData,$curheader["format"]);
	    				
	    			$myData=(trim($myData==""))?"&nbsp;":$myData;
	    			if($theUrl!="" && $theKey!="" && isset($curdata->$theKey))
	    			{
	    				$myLinkOpen="<a href=\"".$theUrl.$curdata->$theKey."\">";
	    				$myLinkClose="</a>";
	    			}
	    			$myTable.= "<td class=\"lightbordertext\">\n".$myLinkOpen.$myData.$myLinkClose."</td>\n";
	    		}
	    		$myTable.= "</tr>\n";
    		}
    		$myTable.= "</table>\n";
    		if($debug) echo "Contenu affiché<br>\n";
    		
    	}
    	return $myTable;
    }

    function HTML_TableFromArray($theList,$theCaption="",$theClass="",$theParams="")
    {
    	$myTable="";
    	$debug=false;
    	if($debug) echo "Affichage de la liste<br>\n";
    	if(is_array($theList) && count($theList)>0)
    	{
    		if($debug) echo "La liste est un tableau non vide<br>\n";
    		$myTable.= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"".$theClass."\" ".$theParams.">\n";
    		if($debug) echo "Entête affiché<br>\n";
    		$myLinkOpen=""; $myLinkClose="";
    		if($theCaption!="")
    		{
    			$myTable.="<caption>".$theCaption."</caption>\n";
    		}
    		foreach($theList as $curdata)
    		{
	    		$myTable.= "<tr>\n";
	    		if(is_array($curdata))
		    		foreach($curdata as $curvalue)
		    			$myTable.= "<td>".$curvalue."</td>\n";
		    	else
	    			$myTable.= "<td>".$curdata."</td>\n";
	    			
	    		$myTable.= "</tr>\n";
    		}
    		$myTable.= "</table>\n";
    		if($debug) echo "Contenu affiché<br>\n";
    		
    	}
    	return $myTable;
    }
    
    function HTML_GetUrlFromGet($theScript,$theGet,$theException=array())
    {
    	$myUrl=$theScript;
    	$myGet="";
    	$myLink="?";
    	if(!is_array($theException)) $theException=array();
    	foreach($theGet as $key=>$value)
    	{
    		if(!in_array($key,$theException))
    		{
	    		if(!is_array($value))
	    			$myGet.=$myLink.$key."=".urlencode($value);
	    		else
	    		{
	    			if(count($value)>0)
	    			{
	    				foreach($value as $curvalue)
	    				{
	    					$myGet.=$myLink.$key."[]=".urlencode($curvalue);
	    				}
	    			}
	    		}
	    		$myLink="&";
    		}
    	}
    	$myUrl.=$myGet;
    	return $myUrl;
    }
    
    function AJAX_AddSortableList($theListName,$theListDestination,$theHandle,$theListUpdateFunction)
    {
    	echo "<script type=\"text/javascript\" language=\"javascript\">\n";
    	echo "Sortable.create('".$theListName."',{ghosting:false,constraint:false,containment:[".$theListDestination."],handle:'".$theHandle."',dropOnEmpty:true, \"onUpdate\":".$theListUpdateFunction." });\n";
    	echo "</script>\n";
    }
    
    function AJAX_AddDroppableZone($theZoneName,$theClassAccepted)
    {
    	echo "<script type=\"text/javascript\" language=\"javascript\">\n";
    	echo "Droppables.add('".$theZoneName."', {accept:'".$theClassAccepted."', onDrop:function(element){Element.hide(element);}});\n";
    	echo "</script>\n";
    }
    
    function DATA_GetArrayFromObjectArray($theObj,$theKey,$theVal)
    {
    	$myRetArr=array();
    	if(is_array($theObj) && count($theObj)>0)
	    	foreach($theObj as $theCurObj)
	    	{
	    		$myRetArr[$theCurObj->$theKey]=$theCurObj->$theVal;
	    	}
    	return $myRetArr;
    }
    
    function DATE_GetDisplayDateTime($date,$format="short")
    {
    	$myEnv=new TT_Env();
		$TheDatePref=$myEnv->DatePref;
		//echo "Prefs : ".print_r($TheDatePref,true);
    	switch($format)
    	{
    		case "full":
		    	return date($TheDatePref->DispFormat." H:i:s",$date);
    			break;
    		default:
    		case "short":
		    	return date($TheDatePref->DispFormat." H:i",$date);
    			break;
    	}
    }

    function DATE_GetDisplayDate($date,$format="full")
    {
    	$myEnv=new TT_Env();
		$TheDatePref=$myEnv->DatePref;
		switch($format)
    	{
    		case "full":
    		default:
		    	return date($TheDatePref->DispFormat,$date);
    			break;
    	}
    }
    
    function FORM_GetHourCombo($theCmbName,$theHourBegin,$theHourEnd,$theCurVal)
    {
    	$myCmb ="<select name=\"".$theCmbName."\">";
    	for($i=intval($theHourBegin);$i<=intval($theHourEnd);$i++)
    	{
    		$myHour=(($i<10)?"0":"").$i;
    		$myCmb.="<option value=\"".$myHour."\" ".(($myHour==$theCurVal)?"selected":"").">".$myHour."</option>\n";
    	}
    	$myCmb.="</select>";
    	return $myCmb;
    }
    
    function FORM_GetMinutesCombo($theCmbName,$theMinutesStep,$theCurVal)
    {
    	$myCmb ="<select name=\"".$theCmbName."\">";
    	for($i=0;$i<60;$i+=$theMinutesStep)
    	{
    		$myMinute=(($i<10)?"0":"").$i;
    		$myCmb.="<option value=\"".$myMinute."\" ".(($myMinute==$theCurVal)?"selected":"").">".$myMinute."</option>\n";
    	}
    	
    	$myCmb.="</select>";
    	return $myCmb;
    }

	function FORM_GetSubmit($theName,$theValue,$theParams="",$theClass="")
	{
		return "<input type=\"submit\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theValue."\" class=\"".$theClass."\" ".$theParams.">\n";
	}

	function FORM_GetButton($theName,$theValue,$theParams="",$theClass="")
	{
		return "<input type=\"button\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theValue."\" class=\"".$theClass."\" ".$theParams.">\n";
	}
		
	
	function FORM_GetFile($theName,$theDefault="",$theParams="",$theClass="")
	{
		$myFileField = ($theDefault!="")?("<span class=\"".$theClass."_label\">".$theDefault."</span>\n"):"";
		$myFileField.= "<input type=\"file\" class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\" ".$theParams.">\n";
		return $myFileField;
	}
	
	function FORM_GetText($theName,$theDefault="",$theParams="",$theClass="")
	{
		return "<input type=\"text\" class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theDefault."\" ".$theParams." >\n";
	}

	function FORM_GetPass($theName,$theDefault="",$theParams="",$theClass="")
	{
		return "<input type=\"password\" class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theDefault."\" ".$theParams." >\n";
	}

	function FORM_GetCheckbox($theName,$theDefault="",$theValue="",$theParams="",$theClass="")
	{
		return "<input type=\"checkbox\" class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theDefault."\" ".(($theValue==$theDefault)?"checked":"")." ".$theParams." >\n";
	}
	
	function FORM_GetColor($theName,$theDefault="",$theParams="",$theClass="")
	{
		$myField = TT_Template::JS_ColorChoserInit();
		$myField.= TT_Template::JS_ColorChoserLink($theName,$theDefault,$theParams,$theClass);
		return $myField;
	}
	
	function FORM_GetDateInit()
	{
		static $initdone;
		global $path_pre;
		if(!isset($initdone))
			$initdone=false;
		if( $initdone) return "";
		global $ThePrefs;
    	if(isset($ThePrefs->DatePrefs->JSSelFormat))
    		$mySelFormat=$ThePrefs->DatePrefs->JSSelFormat;
		$myDatePickerOptionsArray=array();
		$myDatePickerOptions="";
		$myDatePickerOptionsArray[]="showOn: 'both'";
		$myDatePickerOptionsArray[]="buttonImageOnly: true";
		$myDatePickerOptionsArray[]="buttonImage: '".$path_pre."lib/jquery/images/calendar.gif'";
		$myDatePickerOptionsArray[]="buttonText: '".Tools::Translate("Choisir une date")."'";
		$myDatePickerOptionsArray[]="clearText: '".Tools::Translate("Effacer")."'";
		$myDatePickerOptionsArray[]="closeText: '".Tools::Translate("X")."'";
		$myDatePickerOptionsArray[]="prevText: '<&nbsp;".Tools::Translate("Avant")."'";
		$myDatePickerOptionsArray[]="nextText: '".Tools::Translate("Apres")."&nbsp;>'";
		$myDatePickerOptionsArray[]="currentText: '".Tools::Translate("Ce&nbsp;jour")."'";
		$myDatePickerOptionsArray[]="dayNamesMin: [".Tools::Translate("dayNamesMin")."]";
		$myDatePickerOptionsArray[]="dayNamesShort: [".Tools::Translate("dayNamesShort")."]";
		$myDatePickerOptionsArray[]="monthNames: [".Tools::Translate("monthNames")."]";
		$myDatePickerOptionsArray[]="monthNamesShort: [".Tools::Translate("monthNamesShort")."]";
		if($mySelFormat!="")
			$myDatePickerOptionsArray[]="dateFormat: '".$mySelFormat."'";
		if(is_array($myDatePickerOptionsArray) && count($myDatePickerOptionsArray)>0)
			$myDatePickerOptions="{".implode(", ",$myDatePickerOptionsArray)."}";
		return  "<script>\$(document).ready(function(){\$(\".jsdatepicker\").datepicker(".$myDatePickerOptions.");});</script>\n";
	}

	function FORM_GetDate($theName,$theDefault="",$theParams="",$theClass="",$theFormat="",$theDateInit=true,$theSpecificLinkName="")
	{
		$myEnv=new TT_Env();
		$TheDatePref=$myEnv->DatePref;
		$myFormat=$TheDatePref->SelFormat;
		if($theFormat!="")
			$myFormat=$theFormat;
		$myDate = "<input type=\"text\" class=\"jsdatepicker ".$theClass."\" style=\"width:80px;\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theDefault."\" ".$theParams.">\n";
		$myDate .= TT_Template::FORM_GetDateInit();
		return $myDate;
	}

	function FORM_GetHidden($theName,$theDefault="")
	{
		return "<input type=\"hidden\" name=\"".$theName."\" id=\"".$theName."\" value=\"".$theDefault."\" >\n";
	}
	
	function FORM_GetHtmlEditor($thePathPre,$theName,$theDefault="",$theParams="",$theClass="")
	{
		$myText=TT_Template::FORM_GetLongtext($theName,$theDefault,$theParams,$theClass);
		$myText.=
		"<script type='text/javascript'>
		$(document).ready(function() {
				// Automatically calculates the editor base path based on the _samples directory.
				// This is usefull only for these samples. A real application should use something like this:
				// oFCKeditor.BasePath = '/fckeditor/' ;	// '/fckeditor/' is the default value.
				//var sBasePath = document.location.href.substring(0,document.location.href.lastIndexOf('_samples')) ;
				var oFCKeditor = new FCKeditor( '".$theName."' ) ;
				oFCKeditor.Config['ToolbarStartExpanded'] = false ;
				oFCKeditor.BasePath	= '".$thePathPre."lib/fckeditor/' ;
				oFCKeditor.ReplaceTextarea() ;
		});
		</script>";
		return $myText;
	}
	
	function FORM_GetLongtext($theName,$theDefault="",$theParams="",$theClass="")
	{
		return "<textarea class=\"".$theClass."\" name=\"".$theName."\" id=\"".$theName."\" ".$theParams.">".$theDefault."</textarea>\n";
	}

	function FORM_Begin($theUrl,$theParams="",$theName="form",$theMethod="post")
	{
		//echo __LINE__."<br>\n";
		$myFormBegin = "<form action=\"".$theUrl."\" ".$theParams." id=\"".$theName."\" name=\"".$theName."\" method=\"".$theMethod."\" enctype='multipart/form-data' >\n";
		//echo __LINE__."=&gt;".$myFormBegin ."&lt;= <br>\n";
		return $myFormBegin;
	}

	function FORM_End()
	{
		//echo __LINE__."<br>\n";
		$myReturnForm = "</form>\n";
		//echo __LINE__."=&gt;".$myReturnForm."&lt;=<br>\n";
		return $myReturnForm;
	}

    function FORM_GetArrayComboList($theCmbName,$theArray,$theCurVal,$theSize="",$theMultiple=false,$theParams="",$theClass="")
    {
    	$myAddParams=(intval($theSize)>0)?" size=\"".$theSize."\" ":"";
    	if(intval($theSize)>0 && $theMultiple===true)
    		$myAddParams.=" multiple=\"multiple\" ";
    	$myMultipleSign=($theMultiple===true)?"[]":"";
    	$myCmb ="<select name=\"".$theCmbName.$myMultipleSign."\" ".$myAddParams." id=\"".$theCmbName."\" ".$theParams." class=\"".$theClass."\">";
    	foreach($theArray as $key=>$value)
    	{
    		$myCmb.="<option value=\"".$key."\" ".((in_array($key,$theCurVal))?"selected":"").">".$value."</option>\n";
    	}
    	
    	$myCmb.="</select>";
    	return $myCmb;
    	
    }

    function FORM_GetArrayCombo($theCmbName,$theArray,$theCurVal,$theParams="",$theClass="")
    {
    	$myCmb ="<select name=\"".$theCmbName."\" id=\"".$theCmbName."\" ".$theParams." class=\"".$theClass."\">";
    	foreach($theArray as $key=>$value)
    	{
    		$myCmb.="<option value=\"".$key."\" ".(($key==$theCurVal)?"selected":"").">".$value."</option>\n";
    	}
    	
    	$myCmb.="</select>";
    	return $myCmb;
    	
    }
    
    function FORM_GetListCombo($theName,$theList,$theDefault,$theParams="",$theClass="")
    {
		$myCombo = "<select name=\"".$theName."\" id=\"".$theName."\" class=\"".$theClass."\" >\n";
		if(count($theList)>0)
			foreach($theList as $curoption)
			{
				$myCombo.= "<option value=\"".$curoption["id"]."\" ".(($curoption["id"]==$theDefault)?"selected":"")." >".$curoption["value"]."</option>\n"; 
			}
		$myCombo.= "</select>";
		return $myCombo;
    }
    
    function JS_LightboxHeader($path_pre)
    {
    	static $called;
    	if(!isset($called))
    		$called=false;
		$myText="";
    	if(!$called)
    	{
			$myText .= "<link href=\"".$path_pre."lib/js/lightbox.css\" media=\"all\" rel=\"Stylesheet\" type=\"text/css\" >\n";
			$myText .= "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$path_pre."lib/js/lightbox.js\"></SCRIPT>\n";
			$called=true;
    	}
    	return $myText;
    }
    
    function JS_LightboxCloser($theBoxName,$theParent="")
   	{
   		$myCloser=$theParent."lb_hideBox('".$theBoxName."');";
   		return $myCloser;
   	}
    
    function JS_Lightbox_Action($theBoxName)
    {
    	$myCaller = "lb_showBox('".$theBoxName."');";
    	return $myCaller;
    }

    function JS_Lightbox_Overlay($theBoxName)
    {
    	$myCaller = "<div  class=\"lb_overlay\" id=\"overlay_".$theBoxName."\" onclick=\"lb_hideBox('".$theBoxName."');\" style=\"display:none\"></div>\n";
    	return $myCaller;
    }
    
    function JS_LightboxCaller($theBoxName,$theText,$theType="link")
    {
    	$myCaller = "";
    	if($theType=="link")
    		$myCaller .= "<a href=\"#\" onclick=\"".TT_Template::JS_Lightbox_Action($theBoxName)."\" class=\"screenonly\">".$theText."</a>\n";
    	if($theType=="button")
    		$myCaller .= "<input type=\"button\" onclick=\"".TT_Template::JS_Lightbox_Action($theBoxName)."\" name=\"callLightbox_".$theBoxName."\" value=\"".$theText."\" class=\"screenonly\">\n";

		$myCaller .= TT_Template::JS_Lightbox_Overlay($theBoxName);
    	return $myCaller;
    }
    
    function JS_LightboxBegin($theName,$path_pre,$styles="",$onclose="")
    {
    	$myText="";
		$myText .= "<div class=\"lb_box\" id=\"box_".$theName."\" style=\"display:none;".$styles."\">\n";
		$myText .= "    <img  class=\"lb_close\" id=\"close".$theName."\" src=\"".$path_pre."lib/js/lightbox/button_cancel.png\" onclick=\"lb_hideBox('".$theName."');".$onclose."\" alt=\"".Tools::Translate("Fermer")."\" title=\"".Tools::Translate("Fermer cette fenêtre")."\" />\n";
		return $myText;
    }
    
    function JS_LightboxEnd($theName)
    {
    	$myText="";
    	$myText.="		</div>\n";
    	return $myText;
    }
    
    function JS_ColorChoserInit()
    {
    	global $path_pre,$jsinit;
    	if(in_array(__FUNCTION__,$jsinit)) return "";
    	$jsinit[]=__FUNCTION__;
    	$myFormBody.= "
			<!-- Debut Formulaire -->
			<SCRIPT LANGUAGE=\"Javascript\" SRC=\"".$path_pre."lib/js/AnchorPosition.js\"></SCRIPT>
			<SCRIPT LANGUAGE=\"Javascript\" SRC=\"".$path_pre."lib/js/PopupWindow.js\"></SCRIPT>
			<SCRIPT LANGUAGE=\"Javascript\" SRC=\"".$path_pre."lib/js/ColorPicker2.js\"></SCRIPT>
			<SCRIPT LANGUAGE=\"JavaScript\">
				var cp2 = new ColorPicker(); // DIV style
			</SCRIPT>
		";
		$myFormBody.= "<SCRIPT LANGUAGE=\"JavaScript\">cp2.writeDiv()</SCRIPT>\n";
    	return $myFormBody;
    }
    
    function JS_ColorChoserLink($theItem,$theDefault,$theParams="",$theClass="")
    {
    	$myFormBody.= "
    			<A HREF=\"#\" onClick=\"cp2.select(document.getElementById('".$theItem."'),'pick2',document.getElementById('".$theItem."_colorex'));return false;\" NAME=\"pick2\" ID=\"pick2\" class=\"stylebutton\">
					".Tools::Translate("Choisir la couleur")."
				</a>
				<INPUT TYPE=\"text\" NAME=\"".$theItem."_colorex\" id=\"".$theItem."_colorex\" SIZE=\"10\" value=\"\" style=\"background-color:".$theDefault.";\">
				<INPUT TYPE=\"text\" NAME=\"".$theItem."\" SIZE=\"10\" onblur=\"document.getElementById('".$theItem."_colorex').style.backgroundColor=this.value;\" id=\"".$theItem."\" onclick=\"document.getElementById('".$theItem."_colorex').style.backgroundColor=this.value;\" value=\"".$theDefault."\" ".$theParams." ".$theClass.">
		";    	
		return $myFormBody;
    }
    
    function JS_CalendarPopupLink($theItem,$theFormat="yyyy-MM-dd",$theClass="",$theSpecificLinkName="")
	{
		$myLinkName=($theSpecificLinkName!="")?$theSpecificLinkName:Tools::Translate("Sélectionner une date");
		return "<a class=\"".$theClass."\" href=\"#\" onClick=\"cal1x.select(document.getElementById('".$theItem."'),'".$theItem."_anchor1x','".$theFormat."'); return false;\" TITLE=\"".$theSpecificLinkName."\" NAME=\"".$theItem."_anchor1x\" ID=\"".$theItem."_anchor1x\">".Tools::Translate("S&eacute;lectionner une date")."</a>";
	}
	
	function JS_CalendarPopupInit_Includes()
	{
		global $path_pre;
    	$myJSIinit="";
		$myJSIinit.= "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$path_pre."lib/calendarpopup/AnchorPosition.js\"></SCRIPT>\n";
		$myJSIinit.= "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$path_pre."lib/calendarpopup/date.js\"></SCRIPT>\n";
		$myJSIinit.= "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$path_pre."lib/calendarpopup/PopupWindow.js\"></SCRIPT>\n";
		$myJSIinit.= "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$path_pre."lib/calendarpopup/CalendarPopup.js\"></SCRIPT>\n";
		$myJSIinit.= "<DIV ID=\"divCalendarPopup\" STYLE=\"z-index:110; position:absolute; visibility:hidden; background-color:white; layer-background-color:white;\"></DIV>\n";
		return $myJSIinit;
	}
    
    function JS_CalendarPopupInit_Script()
    {
    	$myJSIinit="";
    	$myJSIinit.= "<SCRIPT LANGUAGE=\"JavaScript\" ID=\"jscal1x\">\n";
		$myJSIinit.= "	var cal1x = new CalendarPopup('divCalendarPopup');\n";
		$myJSIinit.= "	cal1x.showNavigationDropdowns();\n";
		$myJSIinit.= "	cal1x.setMonthNames('".Tools::Translate("Janvier")."','".Tools::Translate("Février")."','".Tools::Translate("Mars")."','".Tools::Translate("Avril")."','".Tools::Translate("Mai")."','".Tools::Translate("Juin")."','".Tools::Translate("Juillet")."','".Tools::Translate("Aout")."','".Tools::Translate("Septembre")."','".Tools::Translate("Octobre")."','".Tools::Translate("Novembre")."','".Tools::Translate("Décembre")."');\n";
		$myJSIinit.= "	cal1x.setDayHeaders('".Tools::Translate("dh_Di")."','".Tools::Translate("dh_Lu")."','".Tools::Translate("dh_Ma")."','".Tools::Translate("dh_Me")."','".Tools::Translate("dh_Je")."','".Tools::Translate("dh_Ve")."','".Tools::Translate("dh_Sa")."');\n";
		$myJSIinit.= "	cal1x.setWeekStartDay(1);\n";
		$myJSIinit.= "</SCRIPT>\n";
		return $myJSIinit;
    }
    
    function JS_CalendarPopupInit()
    {
    	global $jsinit;
    	if(!isset($jsinit) || !is_array($jsinit)) $jsinit=array();
    	if(in_array(__FUNCTION__,$jsinit)) return "";
    	$jsinit[]=__FUNCTION__;
    	$myJSIinit = TT_Template::JS_CalendarPopupInit_Includes();
    	$myJSIinit.= TT_Template::JS_CalendarPopupInit_Script();
		return $myJSIinit;
    }
    
    function BubbleIncludes()
    {
    	static $bubblesinclude=0;
    	if($bubblesinclude==0)
    	{
	    	$myEnv=new TT_Env();
	    	$path_pre=$myEnv->path_pre;
	    	return "<script type=\"text/javascript\" src=\"".$path_pre."lib/js/BubbleTooltips/BubbleTooltips.js\"></script><script type=\"text/javascript\">window.onload=function(){enableTooltips()}; setPath('".$path_pre."lib/js/BubbleTooltips/');</script>\n";
    		$bubblesinclude=1;	
    	}
    }
    
    function Box_boxIncludes()
    {
    	return "";
    }
    
    function BoxIncludes()
    {
    	static $sent=0;
    	if($sent==0)
    	{
    		echo TT_Template::Box_boxIncludes();
    	}
    	$sent=1;
    }
    
    function Box_boxInit($theModule)
    {
    	return boxInit($theModule);	
    }
    
    function BoxInit($theModule)
    {
    	static $arrayinit=array();
    	if(!in_array($theModule,$arrayinit))
    	{
    		echo TT_Template::Box_boxInit($theModule);
    		$arrayinit[]=$theModule;
    	}
    }
    
    function HTMLBodyEnd()
    {
    	static $sent=0;
    	if($sent==0)
    	{
	    	echo "</body>\n";
	    	echo "</html>\n";
    	}
    	$sent=1;
    }
    
    
    function HTMLBottom()
    {
    	static $sent=0;
    	if($sent==0)
    	{
	    	echo "</body>\n";
	    	echo "</html>\n";
    	}
    	$sent=1;
    }
    
    function GetBlockBegin($context,$boxtitle,$theTitle,$optionalclass="",$theDraggable=false)
    {
    	$myText = TT_Template::MOD_GetBlockBegin($context,$boxtitle,$optionalclass);
		$myText.= TT_Template::MOD_GetTitle($context,$boxtitle,$theTitle,$theDraggable);
		$myText.= TT_Template::MOD_GetContentBegin();
		return $myText;
    }

	function GetBlockEnd()
	{
	  	$myText = TT_Template::MOD_GetContentEnd();
	  	$myText.= TT_Template::MOD_GetBlockEnd();
	  	return $myText;		
	}
    
	function Box_boxRegister($theModule,$theBoxName,$theBoxType)
	{
		return boxRegister($theModule,$theBoxName,$theBoxType);
	}
	
    
    function MOD_GetBlockBegin($context,$boxtitle,$optionalclass="")
    {
    	$myEnv=new TT_Env();
    	$ThePrefs=$myEnv->Prefs;
    	
    	$myText="";
    	$myText.= "<center><div align=\"center\" class=\"".$optionalclass." mod_outbox\">\n";
		$myText.= "	<div ".TT_Template::Box_boxRegister($context,"box_".$boxtitle,"content")." ".$ThePrefs->style_box.">\n";
		$myText.= "	<div class=\"mod_inbox\">\n";
		return $myText;	  
    }
    
    function MOD_GetTitle($context,$boxtitle,$theTitle,$theLink="",$theDraggable=false)
    {
    	$myB_HRef="";
    	$myE_HRef="";
    	if($theLink!="")
    	{
	    	$myB_HRef="<a href=\"".$theLink."\" target=\"_top\">";
	    	$myE_HRef="</a>";
    	}
    	$myHandle="";
    	if($theDraggable) $myHandle="<div class=\"box_handle\">&nbsp;</div>";
		if($theTitle!="")
			return "<div align=\"center\" ".TT_Template::Box_boxRegister($context,"box_tit_".$boxtitle,"title").">".$myHandle."<b>".$myB_HRef.$theTitle.$myE_HRef."</b></div>";
		else
			return $myHandle;
    }
    
    function MOD_GetContentBegin()
    {
    	return "<div align=\"left\" class=\"mod_textcontent\" >\n";
    }
    
    function MOD_GetContentEnd()
    {
    	return "</div>\n";
    }
    
    function MOD_GetBlockEnd()
    {
    	return "</div></div></div></center>\n";
    }
    
    function BlockBegin_Get($context,$boxtitle,$theTitle,$optionalclass="",$theDraggable=false,$boxtype="content")
    {
    	$myBlock  = "";
    	$myBlock .= TT_Template::MOD_BlockBegin($context,$boxtitle,$optionalclass,$boxtype);
		$myBlock .= TT_Template::MOD_Title($context,$boxtitle,$theTitle,$theDraggable);
		$myBlock .= TT_Template::MOD_ContentBegin();
		return $myBlock;    	
    }
    function BlockBegin($context,$boxtitle,$theTitle,$optionalclass="",$theDraggable=false,$boxtype="content")
    {
    	echo TT_Template::BlockBegin_Get($context,$boxtitle,$theTitle,$optionalclass="",$theDraggable=false,$boxtype="content");
    }

	function BlockEnd_Get()
	{
	  	$myBlock  = "";
    	$myBlock .= TT_Template::MOD_ContentEnd();
    	$myBlock .= TT_Template::MOD_BlockEnd();
    	return $myBlock;	
	}
	
	function BlockEnd()
	{
	  	echo TT_Template::BlockEnd_Get();	
	}
	
    function MOD_BlockBegin($context,$boxtitle,$optionalclass="",$boxtype="content")
    {
    	$myEnv=new TT_Env();
    	$ThePrefs=$myEnv->Prefs;
    	
    	$myBlock  = "";
    	$myBlock .=  "<center><div align=\"center\" class=\"".$optionalclass." mod_outbox\">\n";
		$myBlock .=  "	<div ".TT_Template::Box_boxRegister($context,"box_".$boxtitle,$boxtype)." ".$ThePrefs->style_box.">\n";
		$myBlock .=  "	<div class=\"mod_inbox\">\n";
		return $myBlock; 
    }
    
    function MOD_Title($context,$boxtitle,$theTitle,$theLink="",$theDraggable=false)
    {
    	$myBlock  = "";
    	$myB_HRef="";
    	$myE_HRef="";
    	if($theLink!="")
    	{
	    	$myB_HRef="<a href=\"".$theLink."\" target=\"_top\">";
	    	$myE_HRef="</a>";
    	}
    	$myHandle="";
    	if($theDraggable) $myHandle="<div class=\"box_handle\">&nbsp;</div>";
		
		if($theTitle!="") $myBlock="<div align=\"center\" ".TT_Template::Box_boxRegister($context,"box_tit_".$boxtitle,"title").">".$myHandle."<b>".$myB_HRef.$theTitle.$myE_HRef."</b></div>";
		else $myBlock=$myHandle;
		
		return $myBlock;
    }
    
    function MOD_ContentBegin()
    {
    	return "<div align=\"left\" class=\"mod_textcontent\" >\n";
    }
    
    function MOD_ContentEnd()
    {
    	return "</div>\n";
    }
    
    function MOD_BlockEnd()
    {
    	return "</div></div></div></center>\n";
    }
    
    function TREE_AjaxInit(&$thePrefs,$thePath,$theTreeStruct)
    {
    	boxIncludes();
		
		echo "<style>
		ul.mailcolumn {
		  margin:0px;
		  padding:0px;
		  list-style-type:none;
		}
		
		table.maillayout { width:95%; }
		table.maillayout .leftcol { width:200px; padding-right:5px; }
		.framestyle h1 { margin-top:5px; margin-bottom:5px; }
		ul.mailcolumn li { width:100%; }
		.inlineblock { }
		div.listHover { background-color:#EAEAEA; border:1px dashed #AA3333; margin:0px !important;}
		span.listHover { background-color:#EAEAEA; border:1px dashed #AA3333; margin:0px !important;}
		</style>
		";
		echo "<script type=\"text/javascript\">
			// Set-up to use getMouseXY function onMouseMove
			document.onmousemove = getMouseXY;
			var IE = document.all?true:false;
			// Temporary variables to hold mouse x-y pos.s
			var posX = 0
			var posY = 0
			
			// Main function to retrieve mouse x-y pos.s
			
			function getMouseXY(e)
			{
				if(IE)
				{ 
				 posX = event.clientX + document.body.scrollLeft;
				 posY = event.clientY + document.body.scrollTop;
				}
				else
				{  // grab the x-y pos.s if browser is NS 
					posX = e.pageX;
					posY = e.pageY;
				}
			} 
			function showDiv(theDiv)
			{ 
				document.getElementById(theDiv).style.position='absolute';
				document.getElementById(theDiv).style.display='inline';
				document.getElementById(theDiv).style.left=posX-100+'px';
				document.getElementById(theDiv).style.top=posY+10+'px';
				//alert('Affichage en : '+posX+'.'+posY);
				//alert('Affichage de '+theDiv+' en : '+document.getElementById(theDiv).style.left+'.'+document.getElementById(theDiv).style.top);
			}


			function openListTree(listid,treeid,treestruct,thediv)
			{
				//alert('open');
				//document.getElementById('debug').innerHTML+='Appel de openListTree pour '+treeid+' vers div '+thediv+'<br>';
				new ajax ('".$thePath."lib/updateAjaxParam.php', {
		            postBody: 'section=tree&mode=listitems&action=open&listid='+listid+'&id='+treeid+'&treestruct='+treestruct,
					update: $(thediv)
					".(($theTreeStruct->sortable->listitemfunction!="")?(",onComplete:TREE_listitemUpdate"):"")."
		        });    
			}

			function closeListTree(listid,treeid,treestruct,thediv)
			{
				//alert('close');
				//document.getElementById('debug').innerHTML+='Appel de closeListTree pour '+treeid+' vers div '+thediv+'<br>';
				new ajax ('".$thePath."lib/updateAjaxParam.php', {
		            postBody: 'section=tree&mode=listitems&action=close&listid='+listid+'&id='+treeid+'&treestruct='+treestruct,
					update: $(thediv)
		        });    
			}
		
			function openLink(treestruct,theItemId,theHRef,theURLPath,theURLBack,thediv)
			{
				//alert('openlink');
				document.getElementById(thediv).innerHTML='<div class=\"tree_loading\">".Tools::Translate("Chargement ...")."</div>';
				new ajax ('".$thePath."lib/updateAjaxParam.php', {
		            postBody: 'section=tree&mode=url&action=show&itemid='+theItemId+'&url_back='+theURLBack+'&url_path='+theURLPath+'&href='+theHRef+'&treestruct='+treestruct,
					update: $(thediv)
					".(($theTreeStruct->sortable->contentitemfunction!="")?(",onComplete:".$theTreeStruct->sortable->contentitemfunction):"")."
		        });    
			}

		
			</script>";	  	
    }

    function TREE_ShowItem($theListId,$theDB,&$theTreeStruct,$theCurList="",$theNextAction="close")
    {
    	$debug=false;
    	if($debug) echo __LINE__."=> TREE_ShowItem : début<br>\n";
    	$myDefVal=(($theCurList=="")?$theTreeStruct->rootval:$theCurList);
    	if(!isset($theTreeStruct->handleopen)) $theTreeStruct->handleopen="+";
    	if(!isset($theTreeStruct->handleclose)) $theTreeStruct->handleclose="-";
    	if(!isset($theTreeStruct->sqlrestriction)) $theTreeStruct->sqlrestriction="";
		if($debug) echo __LINE__."=> TREE_ShowItem : restriction en effet : ".$theTreeStruct->sqlrestriction."<br>\n";
		if(!isset($theTreeStruct->showfield) || $theTreeStruct->showfield=="" || !isset($theTreeStruct->idfield) || $theTreeStruct->idfield =="")
			return false;
		
		$myIDField=$theTreeStruct->idfield;
		$myKeyIDField=$theTreeStruct->idfield;
		$tablekey="";
		if(strstr($theTreeStruct->idfield,".")!==false)
			list($tablekey,$myKeyIDField)=explode(".",$theTreeStruct->idfield);
		if($tablekey!="")
			$tablekey.=".";
		$myShowField=$theTreeStruct->showfield;
		$myKeyShowField=$theTreeStruct->showfield;
		if(strstr($theTreeStruct->showfield,".")!==false)
			list($key,$myKeyShowField)=explode(".",$theTreeStruct->showfield);
		if($debug) echo __LINE__."=> Champ ID : ".$myIDField."=>".$myKeyIDField.", champ Show : ".$myShowField."=>".$myKeyShowField."<br>\n";
    	if(isset($theTreeStruct->typefield))
    		if($theTreeStruct->typefield!="")
    			$myTypeSQL=$theTreeStruct->typefield." IN ('".implode(",",$theTreeStruct->typevalue)."') AND ";
    	if(isset($theTreeStruct->orderfield))
	    	if($theTreeStruct->orderfield!="")
				$myOrderSQL=" ORDER BY ".$theTreeStruct->orderfield." ASC";
		$myParentSQL=$myIDField . "='".$myDefVal."'";
    	if($debug) echo __LINE__."=> TREE_ShowItem : liste<br>\n";
    	$theDB->setQuery("SELECT ".$tablekey."* FROM ".$theTreeStruct->tablename." WHERE ".$myTypeSQL.$myParentSQL.$theTreeStruct->sqlrestriction.$myOrderSQL.";");
    	if($debug) echo __LINE__."=> TREE_ShowItem : requête avant load objet : ".$theDB->getQuery()."\n";
    	$myCurListItem=null;
    	if($theDB->loadObject($myCurListItem))
    	{
			$myCurDiv=$theTreeStruct->tablename;
    		if(strstr($theTreeStruct->tablename," ")!==FALSE)
    		{
    			$myArrTable=explode(" ",$theTreeStruct->tablename);
    			$myCurDiv=$myArrTable[0];
    		}
    		$myCurDiv.="_".$theListId."_".$myCurListItem->$myKeyIDField;
    		if($theNextAction=="open") $onclick="onclick=\"openListTree('".$theListId."','".$myCurListItem->$myKeyIDField."','".urlencode(serialize($theTreeStruct))."','".$myCurDiv."');\"";
    		if($theNextAction=="close") $onclick="onclick=\"closeListTree('".$theListId."','".$myCurListItem->$myKeyIDField."','".urlencode(serialize($theTreeStruct))."','".$myCurDiv."');\"";
			echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%;\"><tr><td>\n";
			echo "<div class=\"tree_handle\" style=\"float:left; cursor:pointer;\" ".$onclick."><div class=\"tree_handle_inside\">";
				if($theNextAction=="open") echo $theTreeStruct->handleopen;
				if($theNextAction=="close") echo $theTreeStruct->handleclose;
			echo "</div></div>\n";
			echo "</td><td style=\"width:100%;\">\n";
			//echo "Type : ".print_r($theTreeStruct->link,true);
			TT_Template::TREE_ShowItemLink($theListId,$myCurListItem->$myKeyIDField,$myCurListItem->$myKeyShowField,$theTreeStruct);
			echo "</td></tr></table>\n";
    	}
    }
    
    function TREE_GetItemLinkInfo($theListId,$theItemId,$theItemName,$theTreeStruct)
    {
    	global $path_pre;
    	switch($theTreeStruct->listtype)
    	{
    		case "selector":
    			$myTarget="";
    			$myHRef="#";
    			$myShowField=$theTreeStruct->showfieldtarget;
				$myIDField=$theTreeStruct->idfieldtarget;
	
    			$myLinkClick="document.getElementById('".$myIDField."').value='".$theItemId."';";
    			$myLinkClick.="document.getElementById('".$myShowField."').value='".addslashes($theItemName)."';";
    			$myLinkClick.="document.getElementById('".$theListId."').style.display='none';";
    			break;
    		case "standard":
				switch($theTreeStruct->link->type)
				{
					case "frame":
					case "popup":
						$myTarget="target=\"".$theTreeStruct->link->target."\"";
						$myHRef=$theTreeStruct->link->href.$theItemId;
						$myLinkClick="";
						break;
					case "ajaxurl":
					case "ajaxscript":
						$myTarget="";
						$myHRef="#";
						$myAjaxHRef=$theTreeStruct->link->href;
						$myUrlBack=urlencode($theTreeStruct->backurl.$theItemId);
						$myLinkClick="openLink('".urlencode(serialize($theTreeStruct))."','".$theItemId."','".$myAjaxHRef."','".$path_pre."','".$myUrlBack."','".$theTreeStruct->link->target."');";
						break;
					default:
						echo "LINK->TYPE NON RENSEIGNE";
						break;
				}
				break;
			default:
				echo "LISTTYPE UNKNOWN";
				break;
    		
    	}
		return array($myTarget,$myHRef,$myLinkClick);
    }
    
    function TREE_ShowItemLink($theListId,$theItemId,$theItemName,$theTreeStruct,$theCurRep=-1)
    {
		list($myTarget,$myHRef,$myLinkClick)=TT_Template::TREE_GetItemLinkInfo($theListId,$theItemId,$theItemName,$theTreeStruct);
		echo "<div class=\"tree_item\" ".(($myLinkClick!="")?(" onclick=\"".$myLinkClick."\""):"")." ".(($theCurRep==$theItemId)?"id=\"tree_item_active\"":"")." >";
		echo "<div class=\"tree_item_inside\">";
			echo "<span style=\"display:table;\" class=\"\" id=\"tree_item_".$theItemId."\"><a href=\"".$myHRef."\" onclick=\"".$myLinkClick."\" ".$myTarget.">\n";
			echo str_replace(" ","&nbsp;",$theItemName);
			echo "</a></span>\n";
			if(isset($theTreeStruct->sortable->listitemfunction) && $theTreeStruct->sortable->listitemfunction!="")
				echo "\n<script type=\"text/javascript\">".$theTreeStruct->sortable->listitemfunction."('".$theItemId."','tree_item_".$theItemId."');</script>\n";
		echo "</div></div>";
		
    	
    }
    
    function TREE_ShowListActivator($theListId,$theTreeStruct,$theActivatorName,$theParameters="")
    {
    	echo "<a href=\"#\" onclick=\"showDiv('".$theListId."');\" ".$theParameters." >".$theActivatorName."</a>";
    }
    
    function TREE_ShowListItem($theListId,$theDB,&$theTreeStruct,$theCurList="",$theNextAction="close",$theCurRep=-1)
    {
    	$debug=false;
    	if($debug) echo "TREE_ShowListItem : début<br>\n";
    	if(!isset($theTreeStruct->defaultdisplay))
    		$theTreeStruct->defaultdisplay=true;
    	$myStyleDisplay="";
    	if($theTreeStruct->listtype=="selector")
    	{
    		$myStyleDisplay="width:250px; display:".(($theTreeStruct->defaultdisplay)?"visible":"none").";";
    		if($theTreeStruct->defaultposition=="absolute")
				$myStyleDisplay.="position:absolute;";
    	}
    	$myDefVal=(($theCurList=="")?$theTreeStruct->rootval:$theCurList);
    	if(!isset($theTreeStruct->sqlrestriction)) $theTreeStruct->sqlrestriction="";
    	if($debug) echo "TREE_ShowListItem : restriction en effet : ".$theTreeStruct->sqlrestriction."<br>\n";
		if(!isset($theTreeStruct->showfield) || !isset($theTreeStruct->idfield))
			return false;
    	if(isset($theTreeStruct->typefield))
    		if($theTreeStruct->typefield!="")
    			$myTypeSQL=$theTreeStruct->typefield." IN ('".implode(",",$theTreeStruct->typevalue)."') AND ";
    	if(isset($theTreeStruct->orderfield))
	    	if($theTreeStruct->orderfield!="")
				$myOrderSQL=" ORDER BY ".$theTreeStruct->orderfield." ASC";
    	if(isset($theTreeStruct->parentfield))
	    	if($theTreeStruct->parentfield!="")
				$myParentSQL=$theTreeStruct->parentfield."='".$myDefVal."'";

		$myIDField=$theTreeStruct->idfield;
		$myKeyIDField=$theTreeStruct->idfield;
		if(strstr($theTreeStruct->idfield,".")!==false)
			list($key,$myKeyIDField)=explode(".",$theTreeStruct->idfield);
		
		$myShowField=($theTreeStruct->showfield);
    	if($debug) echo "TREE_ShowListItem : liste<br>\n";
    	$theDB->setQuery("SELECT ".$myIDField." FROM ".$theTreeStruct->tablename." WHERE ".$myTypeSQL.$myParentSQL.$theTreeStruct->sqlrestriction.$myOrderSQL.";");
    	$myListItems=$theDB->loadObjectList();
    	if($debug) echo "TREE_ShowListItem : requête : ".$theDB->getQuery()."\n";
    	if($myDefVal==$theTreeStruct->rootval)
    	{
			echo "<div class=\"tree_listitem\" style=\"".$myStyleDisplay."\" id=\"".$theListId."\">\n";
    		TT_Template::TREE_ShowItemLink($theListId,$theTreeStruct->rootval,$theTreeStruct->rootname,$theTreeStruct,$theCurRep);    		
    	}
    	else
    	{
    		if(count($myListItems)>0)
				echo "<div class=\"tree_listitem\">\n";
    	}
    	if(count($myListItems)>0)
    	{
    		foreach($myListItems as $myCurListItem)
    		{
				$myCurDiv=$theTreeStruct->tablename;
	    		if(strstr($theTreeStruct->tablename," ")!==FALSE)
	    		{
	    			$myArrTable=explode(" ",$theTreeStruct->tablename);
	    			$myCurDiv=$myArrTable[0];
	    		}
	    		$myCurDiv.="_".$theListId."_".$myCurListItem->$myKeyIDField;
    			echo "<div id=\"".$myCurDiv."\">";
	    		$myCurId=$myCurListItem->$myKeyIDField;
	    		if($theTreeStruct->storevisi && isset($_SESSION[$theListId."_".$myCurId]) && $_SESSION[$theListId."_".$myCurId])
	    		{
	    			TT_Template::TREE_ShowItem($theListId,$theDB,$theTreeStruct,$myCurId,"close");
	    			TT_Template::TREE_ShowListItem($theListId,$theDB,$theTreeStruct,$myCurId,"close");
	    		}
	    		else
		    		TT_Template::TREE_ShowItem($theListId,$theDB,$theTreeStruct,$myCurId,$theNextAction);
	    		echo "</div>";
    		}
    	}
    	if($theTreeStruct->listtype=="selector" && $myDefVal==$theTreeStruct->rootval)
    	{
    		echo "<center><input type=\"button\" onclick=\"document.getElementById('".$theListId."').style.display='none';\" value=\"".Tools::Translate("Fermer")."\" ></center>\n";
    	}
 		if($myDefVal==$theTreeStruct->rootval)
 		{
			echo "<script type=\"text/javascript\">\n";
			echo "function TREE_listitemUpdate(theReq)
					{
						myTxt=theReq.responseText;
						myArrTxt=myTxt.split('\\n');
						myReg=/<script type=\"text\\/javascript\">(.*?)<\\/script>/i;
						for(i=0;i<myArrTxt.length;i++)
						{
							if(myReg.test(myArrTxt[i]))
							{
								myArray = myReg.exec(myArrTxt[i]);
								if(myReg.lastMatch!='')
									eval(myArray[1]);
							}
						}
					}
					</script>\n";
 		}
 		if(count($myListItems)>0 || $myDefVal==$theTreeStruct->rootval)
			echo "</div>";
    }

	function EDITOR_Activate($theId,$theStyle,$thePath)
	{
		static $arrayid=array();
		if(!in_array($theId,$arrayid))
		{
			$fckeditor_config="
			<script type=\"text/javascript\" src=\"".$thePath."lib/FCKeditor/fckeditor.js\"></script>
			
			<script type=\"text/javascript\">
			  function editorinit()
			  {
				var oFCKeditor1 = new FCKeditor( '".$theId."' ) ;
				oFCKeditor1.BasePath = \"".$thePath."lib/FCKeditor/\" ;
				oFCKeditor1.ToolbarSet = '".$theStyle."' ;
				oFCKeditor1.Height = 500;
				oFCKeditor1.ReplaceTextarea() ;
				}
			  editorinit();
			</script>
			        
			";
			
			$tinymce_config= "<script language='JavaScript'>
			function insPlHold() {
			txt = document.frm.body.value + document.frm.placehold.value;
			document.frm.body.value = txt;
			document.frm.placehold.value = '';
			document.frm.body.focus();
			}
			</script>
			<script language=\"javascript\" type=\"text/javascript\" src=\"".$thePath."lib/tiny_mce/tiny_mce.js\"></script>
			<script language=\"javascript\" type=\"text/javascript\">
			tinyMCE.init({
				theme : \"advanced\",
				mode : \"textareas\",
				content_css : \"".$thePath."mailtemplates/styles.css\",
				extended_valid_elements : \"a[href|target|name]\",
				relative_urls : false,
				convert_urls : false,
				plugins : \"table,fullpage\",
				theme_advanced_buttons3_add_before : \"tablecontrols,separator\",
				theme_advanced_toolbar_location : \"top\",
				theme_advanced_styles : \"Header 1=header1;Header 2=header2;Header 3=header3;Header 4=header4;Table Row=tableRow1\", // Theme specific setting CSS classes
				debug : false
			});
			</script>
			
			\n";	
			
			
			echo $fckeditor_config;
			$arrayid[]=$theId;	
		}
		
	}

	function AJAX_Init()
	{
		echo "<script type=\"text/javascript\" src=\"".$this->_pathpre."lib/js/prototype.lite.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"".$this->_pathpre."lib/js/moo.ajax.js\"></script>\n";		
	}

	function AJAX_DirectCall($theScript,$theDest,$thePostParams)
	{
		$myPostParamsTxt="";
		if(is_array($thePostParams) && count($thePostParams)>0)
		{
			foreach($thePostParams as $key=>$val)
			{
				$myPostParamsTxt.=(($myPostParamsTxt!="")?"&":"").$key."=".urlencode($val);
			}
		}
		$myScript="new ajax ('".$theScript."', {\n";
		if($myPostParamsTxt!="")
			$myScript.="postBody:'".$myPostParamsTxt."'";
			if($theDest!="") $myScript.= ",update: '".$theDest."'";
			 $myScript.= "});";
		return $myScript;
	}

	function AJAX_InitFunction($theName,$theScript,$theDest,$theParamArray=array(),$theStaticParams="",$theOnComplete="")
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

	function AJAX_AddFunction($theName,$theScript,$theDest,$theParamArray=array(),$theStaticParams="",$theOnComplete="")
	{
		echo TT_Template::AJAX_InitFunction($theName,$theScript,$theDest,$theParamArray,$theStaticParams,$theOnComplete);
	}

	function AJAX_AddPrototypeFunction($theName,$theScript,$theDest,$theParamArray=array(),$theStaticParams="",$theOnComplete="")
	{
	    	echo "<script  language=\"JavaScript\" type=\"text/javascript\">\n";
	    	echo "
			    function ".$theName."(".implode(",",$theParamArray).")
			    {

					new ajax ('$theScript', {
						update: $('".$theDest."'),
			            postBody: '".$theStaticParams.(($theStaticParams!="")?"&":"");
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

	function AJAX_CallFunction($theFunction,$theParams)
	{
		$myTxtParams="";
		if(count($theParams)>0)
		{
			foreach($theParams as $myCurParam)
			{
				$myTxtParams.=(($myTxtParams!="")?",":"")."document.getElementById('".$myCurParam."').value";
			}
		}
		return $theFunction."(".$myTxtParams.");";
	}


}
?>