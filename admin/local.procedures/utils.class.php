<?php

class ImportUtils {

    function ImportUtils()
    {
    }

	function getClassList($folder)
	{
		$json_path=$folder;
		$d = dir($json_path);
		$myClassListArray=array();
		while (false !== ($entry = $d->read()))
		{
			$curfile=$json_path.$entry;
			if($entry!="" && $entry!="." && $entry!="..")
			{
				if(!is_dir($curfile) && is_file($curfile) && is_readable($curfile))
				{
					$curfile_info=pathinfo($curfile);
					if($curfile_info["extension"]=="json" && $curfile_info["filename"]!="")
					{
						$curclass=$curfile_info["filename"];
						$myClassListArray[]=$curclass;
					}
				}
			}
		}
		$d->close();
		if(count($myClassListArray)<=0)
			$myClassListArray=false;
		return $myClassListArray;
	}
	
	
	function getLexiquesList($folder)
	{
		$json_path=$folder;
		$d = dir($json_path);
		$myClassListArray=array();
		while (false !== ($entry = $d->read()))
		{
			$curfile=$json_path.$entry;
			if($entry!="" && $entry!="." && $entry!="..")
			{
				if(!is_dir($curfile) && is_file($curfile) && is_readable($curfile))
				{
					$curfile_info=pathinfo($curfile);
					if($curfile_info["extension"]=="lexique")
					{
						$curclass=$curfile_info["filename"];
						list($lexname,$lexcode)=explode(".",$curclass);
						$myClassListArray[$curclass]=$lexname;
					}
				}
			}
		}
		$d->close();
		if(count($myClassListArray)<=0)
			$myClassListArray=false;
		return $myClassListArray;
	}
	
	function fieldFromDescription($curdescription,$curfieldname,$resetindex=false)
	{
		static $i=0;
		if($resetindex)
			$i=0;
		
		$isKey=false;
		//echo "Ajout du champ : ".Tools::Display($curdescription);
		if($curdescription->tablekey==$curfieldname)
			$isKey=true;
		$fieldPositionAll=",0,0,0);";
		if(!$isKey)
		{
			$fieldPositionAll=",".$i.",".$i.",".$i.");";
			$fieldPositionLongText=",0,".$i.",".$i.");";
		}
		if(isset($curdescription->create_table_fields->$curfieldname->options->pos))
		{
			if(strlen($curdescription->create_table_fields->$curfieldname->options->pos)==3)
			{
				$co=$curdescription->create_table_fields->$curfieldname->options->pos;
				$fieldPositionAll=",".(intval($co[0])*$i).",".(intval($co[1])*$i).",".(intval($co[2])*$i).");";
				$fieldPositionLongText=$fieldPositionAll;
			}
			
			//die("<pre>".print_r($curdescription->create_table_fields->$curfieldname,true)."</pre>");
		}
		$boolIsLexique=false;
		//echo "Recherche de lexique sur ".$curfieldname."<br>Test de ... \n";
		foreach($curdescription->foreign_keys as $keyforeignkey=>$curforeignkey)
		{
			if($curforeignkey->index_list[0]==$curfieldname)
			{
				$boolIsLexique=true;
				//echo "Lexique trouvé : ".$keyforeignkey."/".$curfieldname."<br>\n";
				break;
			}
		}
		
		$myTxtField="";
		//die("<pre>".print_r($curdescription->create_table_fields->$curfieldname,true)."</pre>");
		$curfield=$curdescription->create_table_fields->$curfieldname;
		if(isset($curdescription->create_table_fields->$curfieldname->options->type))
			$curfield->subtype=$curdescription->create_table_fields->$curfieldname->options->type;
		if($boolIsLexique) $curfield->type="REFERENCE";
		if(!isset($curfield->subtype))
			$curfield->subtype="";
		switch($curfield->type)
		{
			case "REFERENCE":
				$curref=$curdescription->foreign_keys[$keyforeignkey];
				$myRefTableLabelKey=$curref->ref_index_list[0];
				if(isset($curdescription->create_table_fields->$curfieldname->options->fklabel))
					$myRefTableLabelKey=$curdescription->create_table_fields->$curfieldname->options->fklabel;
				if(!isset($curfield->subtype) || $curfield->subtype=="")
					$curfield->subtype="reference";
				$myTxtField="\$this->add_field(\"".$curfieldname."\",\"".$curfield->subtype."\",\"".$curref->ref_table_name."\",\"".$curref->ref_index_list[0]."\",\"".$myRefTableLabelKey."\"".$fieldPositionAll;
				break;
			case "INT":
				if(isset($curfield->subtype) && $curfield->subtype=="checkbox")
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"checkbox\",\"\",\"\",\"\"".$fieldPositionAll;
				else
				$myTxtField="\$this->add_field(\"".$curfieldname."\",\"number\",\"\",\"\",\"\"".$fieldPositionAll;
				break;
			case "DECIMAL":
			case "FLOAT":
				$myTxtField="\$this->add_field(\"".$curfieldname."\",\"number\",\"\",\"\",\"\"".$fieldPositionAll;
				break;
			case "DATE":
				if(isset($curfield->subtype) && ($curfield->subtype=="dateauto_modification" || $curfield->subtype=="dateauto_creation"))
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"".$curfield->subtype."\",\"\",\"\",\"\"".$fieldPositionAll;
				else
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"date\",\"\",\"\",\"\"".$fieldPositionAll;
				break;
			case "DATETIME":
				if($curfield->subtype=="dateauto_modification" || $curfield->subtype=="dateauto_creation")
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"".$curfield->subtype."\",\"\",\"\",\"\"".$fieldPositionAll;
				else
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"date\",\"\",\"\",\"\"".$fieldPositionAll;
				break;
			case "TIME":
				$myTxtField="\$this->add_field(\"".$curfieldname."\",\"text\",\"\",\"\",\"\"".$fieldPositionAll;
				break;
			case "TEXT":
				if($curfield->subtype=="html")
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"html\",\"\",\"\",\"\"".$fieldPositionLongText;
				else
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"longtext\",\"\",\"\",\"\"".$fieldPositionLongText;
				break;
			case "VARCHAR":
				if($curfield->subtype=="combolist" && $curfield->options->data!="")
				{
					$myArrayDataCombo=explode("|",$curfield->options->data);
					$myTextArrayCombo="array(\"\"";
					if(is_array($myArrayDataCombo) && count($myArrayDataCombo)>0)
						foreach($myArrayDataCombo as $curdata)
							$myTextArrayCombo.=",\"".addslashes($curdata)."\"=>\"".addslashes($curdata)."\"";
					$myTextArrayCombo.=")";
					$myTxtField="\$this->add_field(\"".$curfieldname."\",\"combolist\",".$myTextArrayCombo.",\"\",\"\"".$fieldPositionAll;
				}
				else
				{
					if(isset($curfield->subtype) && $curfield->subtype=="password")
						$myTxtField="\$this->add_field(\"".$curfieldname."\",\"password\",\"\",\"\",\"\"".$fieldPositionAll;
					else
				$myTxtField="\$this->add_field(\"".$curfieldname."\",\"text\",\"\",\"\",\"\"".$fieldPositionAll;
				}
				break;
		}
		$i++;
		return $myTxtField;
	}



	function setRelationTemplate($theListTables,$theParent,$theClassPrefix)
	{
	
		$template_children=
		"
		\t\t\$this->_children[\"[CHILDCLASSNAME]\"]= array(	\"child_type\"=>\"[RELTYPE]\",
																		\"child_rel\"=>\"[RELTABLENAME]\",
																		\"parent_key\"=>\"[PARENTKEY]\",
																		\"child_key\"=>\"[CHILDKEY]\");
		";
		
	/*
	 * Exemples
		$this->_children["mdtb_analyse"]= array(	"child_type"=>"relative",
																	"child_rel"=>"rel_analyse_fraction_analysee",
																	"parent_key"=>"fraction_analysee_id_fraction_analysee",
																	"child_key"=>"analyse_id_analyse");
	                             [constraint] => fk_opb_int
	                            [index_list] => Array
	                                (
	                                    [0] => opb_code_intervenant_producteur
	                                )
	
	                            [ref_db_name] => pcb_proto
	                            [ref_table_name] => intervenant
	                            [ref_index_list] => Array
	                                (
	                                    [0] => int_code_intervenant
	                                )
	*/
	
		$myListChildren="";
		$debug=false;
		if($debug) echo __LINE__." => Début définition associations de tables<br />\n"; 
		
		foreach($theListTables as $curtable=>$curdescription)
		{
			if($debug) echo __LINE__." => Scan table $curtable<br />\n"; 
			if(is_array($curdescription->foreign_keys) && is_array($curdescription->foreign_keys) && count($curdescription->foreign_keys)>0)
			{
				if($debug) echo __LINE__." => Clefs distantes existent pour la table<br />\n"; 
				foreach($curdescription->foreign_keys as $key_foreignkey=>$def_curforeignkey)
				{
					if($debug) echo __LINE__." => Test clef distante : ".$key_foreignkey." => ".Tools::Display($def_curforeignkey)."<br />\n"; 
					if($def_curforeignkey->ref_table_name==$theParent)
					{
						if($debug) echo __LINE__." => Clef = au parent ( ".$def_curforeignkey->ref_table_name."== ".$theParent.")<br />\n"; 
						$myRelType=(substr($curtable,0,4)=="rel_")?"relative":"lexique";
						$myRelTableName=(substr($curtable,0,4)=="rel_")?$curtable:"";
						$tmpChildrenTemplate=$template_children;
						$tmpChildrenTemplate=str_replace("[RELTYPE]",$myRelType,$tmpChildrenTemplate);
						$tmpChildrenTemplate=str_replace("[RELTABLENAME]",$myRelTableName,$tmpChildrenTemplate);
						if($debug) echo __LINE__." => Reltype : ".$myRelType."<br />\n";
						if($myRelType=="normal" || $myRelType=="lexique")
						{
							$tmpChildrenTemplate=str_replace("[CHILDCLASSNAME]",$theClassPrefix.$curtable,$tmpChildrenTemplate);
							$tmpChildrenTemplate=str_replace("[PARENTKEY]",$def_curforeignkey->ref_index_list[0],$tmpChildrenTemplate);
							$tmpChildrenTemplate=str_replace("[CHILDKEY]",$def_curforeignkey->index_list[0],$tmpChildrenTemplate);
						}
						if($myRelType=="relative")
						{
							if($key_foreignkey==0)
								$otherIndex=1;
							else
								$otherIndex=0;
							if(isset($curdescription->foreign_keys[$otherIndex]))
							{
								$otherForeignKeys=$curdescription->foreign_keys[$otherIndex];
								$tmpChildrenTemplate=str_replace("[CHILDCLASSNAME]",$theClassPrefix.$otherForeignKeys->ref_table_name,$tmpChildrenTemplate);
								$tmpChildrenTemplate=str_replace("[PARENTKEY]",$def_curforeignkey->index_list[0],$tmpChildrenTemplate);
								$tmpChildrenTemplate=str_replace("[CHILDKEY]",$otherForeignKeys->index_list[0],$tmpChildrenTemplate);
							}
							else
							{
								$tmpChildrenTemplate=str_replace("[CHILDCLASSNAME]","",$tmpChildrenTemplate);
								$tmpChildrenTemplate=str_replace("[PARENTKEY]",$def_curforeignkey->index_list[0],$tmpChildrenTemplate);
								$tmpChildrenTemplate=str_replace("[CHILDKEY]","",$tmpChildrenTemplate);
							}
						}
						$myListChildren.=$tmpChildrenTemplate."\n";
					}
				}	
			}
		}
		return $myListChildren;
	}
	
	function getCombo($theCmbName,$theArray,$theArrayMode="kv",$theDefaultOptionLabel,$theDefault="",$theParams="")
	{
		$myHeadersSelect="<select name=\"".$theCmbName."\" ".$theParams." >\n\t<option value=\"\" ".(($theDefault=="")?"selected":"").">(".$theDefaultOptionLabel.")</option>\n";
		foreach($theArray as $key=>$val)
		{
			switch($theArrayMode)
			{
				case "k":
					if(trim($key)!="")
						$myHeadersSelect.="\t<option value=\"".addslashes(($key))."\" ".(($theDefault==addslashes(($key)))?"selected":"").">".($key)."</option>\n";
					break;
				case "v":
					if(trim($val)!="")
						$myHeadersSelect.="\t<option value=\"".addslashes(($val))."\" ".(($theDefault==addslashes(($val)))?"selected":"").">".($val)."</option>\n";
					break;
				case "kv":
					if(trim($key)!="")
						$myHeadersSelect.="\t<option value=\"".addslashes(($key))."\" ".(($theDefault==addslashes(($key)))?"selected":"").">".($val)."</option>\n";
					break;
			}
		}
		$myHeadersSelect.="</select>\n";
		return $myHeadersSelect;

	}
}
?>