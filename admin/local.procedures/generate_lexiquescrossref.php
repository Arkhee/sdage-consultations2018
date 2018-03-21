<?php
/*
 * diren-pcb
 * Created on 26 mars 2009
 * Copyright  �  2009 Yannick B�temps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick B�temps
 * 
 * File : generate_lexiquescrossref.php
 * Description : 
 * Cette page permet de r�aliser le param�trage de croisement des donn�es entre un fichier d'import
 * et son �quivalent en base de donn�es de Lexique
 */
require_once("utils.class.php");
require_once("parametre.class.php");
require_once("../classes/tools.class.php");
$json_path="importdata/";
$tpl_path="codetemplates/";
$error_msg="";
$display_msg="";
$action="";
if(isset($_POST["action"]))
	$action=$_POST["action"];
if($action=="sendcsv")
{
	if(!isset($_POST["txtLexCode"]) || $_POST["txtLexCode"]=="")
		$myCodeLexique="def";
	else
		$myCodeLexique=$_POST["txtLexCode"];
	
	if(isset($_POST["lexique"]) && $_POST["lexique"]!="" && strstr($_POST["lexique"],".")!==false)
	{
		//echo __LINE__." => def lexique<br />";
		list($_POST["lexique"],$myCodeLexique)=explode(".",$_POST["lexique"]);
	}

	if(isset($_FILES["userfile"]) && isset($_FILES["userfile"]["name"]) && $_FILES["userfile"]["name"]!="")
	{
		$fileinfo=pathinfo($_FILES["userfile"]["name"]);
		if($fileinfo["extension"]=="csv")
		{
			//$display_msg="Extension du fichier : csv<br/>";
			$myDataFile=new clsDataFile($_FILES["userfile"]["tmp_name"],"csv");
			$myHeadersUnc=$myDataFile->getHeaders();
			$myHeaders=array();
			$myHeadersSelect="<select name=\"headers_%s\">\n\t<option value=\"\">(S�lectionner une colonne)</option>\n";
			foreach($myHeadersUnc as $key=>$val)
				$myHeaders[$key]=htmlentities($val);
			foreach($myHeaders as $key=>$val)
				if(trim($val)!="")
					$myHeadersSelect.="\t<option value=\"".addslashes(($val))."\">".($val)."</option>\n";
			$myHeadersSelect.="</select>\n";
			//$display_msg="Ent�tes : ".(Tools::Display($myHeaders));
		}
		else
			$error_msg="Erreur : mauvaise extension pour le fichier";
	}
	
	if(isset($_POST["lexique"]))
	{
		if(file_exists($json_path.$_POST["lexique"].".json"))
		{
			$myCurLexique=null;
			$myClassContent=file_get_contents($json_path.$_POST["lexique"].".json");
			$myCurLexique=json_decode($myClassContent);
			$myCurClassName="";
			if(is_object($myCurLexique))
				$myCurClassName=$_POST["lexique"];
		}
		$myLexDefFile=$json_path.$_POST["lexique"].".".$myCodeLexique.".lexique";
		if(file_exists($myLexDefFile) && !isset($myHeaders))
		{
			$myLoadedLexiqueAssociation=unserialize(file_get_contents($myLexDefFile));
			$myHeaders=$myLoadedLexiqueAssociation->headers_csv;
			$myCurClassName=$_POST["lexique"];
		}
	}
}

if($action=="save")
{
	if(!isset($_POST["hidCodeLexique"]) || $_POST["hidCodeLexique"]=="")
		$myCodeLexique="def";
	else
		$myCodeLexique=$_POST["hidCodeLexique"];
		
	$display_msg .=__LINE__." => Sauvegarde de l'association de lexique<br>\n";
	if(!isset($_POST["classname"]))
		$error_msg="La classe n'a pas �t� transmise correctement, impossible de sauver l'association<br />\n";
	else
	{
		$display_msg .=__LINE__." => Classe actuelle : ".$_POST["classname"]."<br>\n";
		$classname=$_POST["classname"];
		$myLexFile=$json_path.$classname.".json";
		$myLexDefFile=$json_path.$classname.".".$myCodeLexique.".lexique";
		if(!file_exists($myLexFile) || !is_readable($myLexFile))
			$error_msg="Le lexique n'est pas d�fini<br />\n";
		else
		{
			$display_msg .=__LINE__." => Le lexique existe ...<br>\n";
			$myClassContent=file_get_contents($myLexFile);
			$myCurLexique=json_decode($myClassContent);
			if(!is_object($myCurLexique))
				$error_msg="La d�finition du lexique n'est pas valide (non lisible)";
			else
			{
				$display_msg .=__LINE__." => La d�finition du lexique est valide<br>\n";
				if(!isset($_POST["external_key"]) || $_POST["external_key"]=="")
					$error_msg="Aucune clef de liaison d�finie, ce param�tre est obligatoire";
				else
				{
					$display_msg .=__LINE__." => La clef de liaison d�finie est : ".$_POST["external_key"]."<br>\n";
					$myObj=new stdClass();
					$myObj->external_key=$_POST["external_key"];
					$myObj->headers_csv=unserialize(urldecode($_POST["headers_list"]));
					$myFields=get_object_vars($myCurLexique->create_table_fields);
					foreach($myFields as $key=>$curfield)
					{
						$curprop="headers_".$key;
						if(isset($_POST[$curprop]) && $_POST[$curprop]!="")
							$myObj->headers_associations[$key]=htmlentities($_POST[$curprop]);
					}
					file_put_contents($myLexDefFile,serialize($myObj));
					$display_msg .=__LINE__." => Sauvegarde lexique avec objet : ".Tools::Display($myObj)."<br>\n";
					$display_msg .=__LINE__." => Le POST �tait : ".Tools::Display($_POST);
					$display_msg = "Les associations ont �t� sauv�es<br />\n";
					$myLoadedLexiqueAssociation=unserialize(file_get_contents($myLexDefFile));
					//$display_msg .= "Association : ".Tools::Display($myLoadedLexiqueAssociation);
					//$display_msg .= "Obj : ".Tools::Display($myObj);
					$myHeaders=$myLoadedLexiqueAssociation->headers_csv;
					$myCurClassName=$classname;
				}
			}
		}
	}	
}

$myClassList=ImportUtils::getClassList($json_path);
$myLexiquesList=ImportUtils::getLexiquesList($json_path);


if($action!="" && (!isset($myHeaders) || !is_array($myHeaders) || count($myHeaders)<=0))
	$error_msg="Aucun fichier CSV transmis, ou aucun lexique d�fini.<br />\n"; // Files : ".Tools::Display($_FILES);
$display_msg=trim($display_msg);
$error_msg=trim($error_msg);
?>
<html>
<head><title>Croisement base de donn�es lexique <=> fichier source CSV</title></head>
<body>

	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>
	
	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
	
	<?php if(isset($myCurLexique) && is_object($myCurLexique) && isset($myHeaders) && is_array($myHeaders) && count($myHeaders)>0) { ?>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<?php
			// Liste avec les champs en clef unique du json, pour s�lection de la clef de r�f�rence
			$myFields=get_object_vars($myCurLexique->create_table_fields);
			//echo "Champs fields : ".Tools::Display($myFields);
			$myCurExternalKey="";
			if(isset($myLoadedLexiqueAssociation->external_key))
				$myCurExternalKey=$myLoadedLexiqueAssociation->external_key;
			$myExternalKeyCombo="<select name=\"external_key\">\n\t<option value=\"\" ".($myCurExternalKey==""?"selected":"").">(S�lectionner une colonne comme clef de liaison)</option>\n";
			foreach($myFields as $key=>$curfield)
				if(isset($curfield->type))
					$myExternalKeyCombo.="\t<option value=\"".addslashes($key)."\" ".(($myCurExternalKey==addslashes($key))?"selected":"").">".$key."</option>\n";
			$myExternalKeyCombo.="</select>\n";
			if(isset($_POST["showobject"]) && $_POST["showobject"]==1 && isset($myLoadedLexiqueAssociation))
			{
				echo "<div id='showobj' style='float:right'>".Tools::Display($myLoadedLexiqueAssociation)."</div>";
			}
			echo "<table>";
			echo "<caption style=\"font-weight:bold; font-size:18px;\">D�finition du lexique : ".$myCurClassName."</caption>\n";
			echo "<tr><td>Code lexique : </td><td>&nbsp;=&gt;&nbsp;</td><td>".$myCodeLexique."<input type='hidden' name='hidCodeLexique' value='".addslashes($myCodeLexique)."' /></td></tr>\n";
			echo "<tr><td>Clef de liaison :</td><td>&nbsp;=&gt;&nbsp;</td><td>".$myExternalKeyCombo."</td></tr>";
			foreach($myFields as $key=>$curfield)
				if(isset($curfield->type))
				{
					echo "<tr><td>$key</td><td>&nbsp;=&gt;&nbsp;</td><td>";
					$myCurCol="";
					if(isset($myLoadedLexiqueAssociation->headers_associations))
					{
						if(is_array($myLoadedLexiqueAssociation->headers_associations))
							if(isset($myLoadedLexiqueAssociation->headers_associations[$key]))
								$myCurCol=$myLoadedLexiqueAssociation->headers_associations[$key];
						else
							if(isset($myLoadedLexiqueAssociation->headers_associations->$key))
								$myCurCol=$myLoadedLexiqueAssociation->headers_associations->$key;						
					}
					//echo "Col pour clef : ".$key." : ".$myCurCol."<br>\n";
					echo ImportUtils::getCombo("headers_".$key,$myHeaders,"v","S�lectionner une colonne",$myCurCol); //.sprintf($myHeadersSelect,$key)
					echo "</td></tr>\n";
					
				}
			echo "</table>\n";
			// Boucle sur les champs de la base selon le json / class
			// Pour chaque, liste d�roulante avec les ent�tes du CSV
			
			// Ok : on sauve => mod�le pr�t � l'emploi
		?>
		<input type="hidden" name="headers_list" value="<?php echo urlencode(serialize($myHeaders)); ?>" />
		<input type="hidden" name="classname" value="<?php echo $myCurClassName; ?>" />
		<input type="hidden" name="action" value="save" />
		<input type="submit" name="cmdSauver" value="Sauver les associations" />
	</form>
	<br /><hr style="clear:both;" />
	<?php } ?>
	
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label><strong>Code lexique (vide : d�faut) :</strong><input type="text" name="txtLexCode" value="<?php if(isset($myCodeLexique)) echo $myCodeLexique; ?>" /></label><br />
		<label><strong>S�lectionner le lexique de r�f�rence : </strong><select name="lexique"><option value="" selected>S�lectionner un lexique</option>
		<?php
			if($myClassList!==false)
			{
				echo "<optgroup label=\"Lexiques d�finis\">\n";
				foreach($myClassList as $curclass)
					if($lexkeyarray=array_keys($myLexiquesList,$curclass))
					{
						foreach($lexkeyarray as $lexkey)
						{
							list($myClassName,$myLexCode)=explode(".",$lexkey);						
							echo "<option value=\"".$lexkey."\" ".(($curclass==$myCurClassName)?"selected":"")." >".$myClassName." ==> ".$myLexCode."</option>\n";	
						}
					}
				echo "</optgroup>\n";
				echo "<optgroup label=\"Tous les Lexiques\">\n";
				foreach($myClassList as $curclass)
					//if(!in_array($curclass,$myLexiquesList))
						echo "<option value=\"".$curclass."\" ".(($curclass==$myCurClassName)?"selected":"")." >".$curclass."</option>\n";	
				echo "</optgroup>\n";
			}
		?>
		</select></label><br />
		<label><strong>S�lectionner le fichier CSV qui servira de r�f�rence : </strong><input type="file" name="userfile"  /></label><br />
		<label><input type="checkbox" name="showobject" value="1" <?php echo isset($_POST["showobject"])?"checked":""; ?> />Afficher l'objet si d�fini</label><br />
		<input type="hidden" name="action" value="sendcsv" />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>
	<a href="index.php">Retour</a>

</body>
</html>