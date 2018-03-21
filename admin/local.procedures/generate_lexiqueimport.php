<?php
/*
 * diren-pcb
 * Created on 26 mars 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : generate_lexiqueimport.php
 * Description : 
 * Importe un lexique en base, se basant sur le fichier de concordance base <=> csv défini précédemment
 * L'import se fait en mettant à jour les enregistrements pré-existants
 */
$path_pre="../";
require_once("utils.class.php");
require_once("parametre.class.php");
require_once("../config.inc.php");
$json_path="importdata/";
$tpl_path="codetemplates/";
$myClassList=ImportUtils::getClassList($json_path);
$myLexiquesList=ImportUtils::getLexiquesList($json_path);

$error_msg="";
$display_msg="";
$action="";
if(isset($_POST["action"]))
	$action=$_POST["action"];
	
$display_msg.="<h3>Action ".$action.", sur le fichier de classe ".(isset($_POST["lexique"])?$_POST["lexique"]:"(aucun)")."</h3>\n";
if($action=="sendcsv")
{
	$myCodeLexique="";
	if(isset($_POST["lexique"]) && $_POST["lexique"]!="" && strstr($_POST["lexique"],".")!==false)
	{
		//echo __LINE__." => def lexique<br />";
		list($_POST["lexique"],$myCodeLexique)=explode(".",$_POST["lexique"]);
	}

	$display_msg.="Traitement du fichier csv <br />\n";
	if(isset($_FILES["userfile"]) && isset($_FILES["userfile"]["name"]) && $_FILES["userfile"]["name"]!="")
	{
		$display_msg.="Fichier existant<br />\n";
		$fileinfo=pathinfo($_FILES["userfile"]["name"]);
		if($fileinfo["extension"]=="csv")
		{
			$display_msg.="Fichier  csv <br />\n";
			//$display_msg="Extension du fichier : csv<br/>";
			$myDataFile=new clsDataFile($_FILES["userfile"]["tmp_name"],"csv");
			$myHeadersUnc=$myDataFile->getHeaders();
			$myHeaders=array();
			$myHeadersSelect="<select name=\"headers_%s\">\n\t<option value=\"\">(Sélectionner une colonne)</option>\n";
			foreach($myHeadersUnc as $key=>$val)
				$myHeaders[$key]=htmlentities($val);
			foreach($myHeaders as $key=>$val)
				if(trim($val)!="")
					$myHeadersSelect.="\t<option value=\"".addslashes(($val))."\">".($val)."</option>\n";
			$myHeadersSelect.="</select>\n";
			$myCSVHeadersList=urlencode(serialize($myHeaders));
			//$display_msg.="Entêtes : ".(Tools::Display($myHeaders));
			//$display_msg.="Contenu : ".(Tools::Display($myDataFile->data));
		}
		else
			$error_msg="Erreur : mauvaise extension pour le fichier";
	}
	
	if(isset($_POST["lexique"]))
	{
		$display_msg.="Lexique : ".$_POST["lexique"]."<br />\n";
		if(file_exists($json_path.$_POST["lexique"].".json"))
		{
			$display_msg.="Fichier json existe<br />\n";
			$myCurLexique=null;
			$myClassContent=file_get_contents($json_path.$_POST["lexique"].".json");
			$myCurLexique=json_decode($myClassContent);
			$myCurClassName="";
			if(is_object($myCurLexique))
				$myCurClassName=$_POST["lexique"];
		}
		$myLexDefFile=$json_path.$_POST["lexique"].".".$myCodeLexique.".lexique";
		$display_msg.="Fichier lexique ".$myLexDefFile."<br />\n";
		if(file_exists($myLexDefFile))
		{
			$display_msg.="Fichier lexique existe<br />\n";
			$myLoadedLexiqueAssociation=unserialize(file_get_contents($myLexDefFile));
		}
	}
	if(serialize($myLoadedLexiqueAssociation->headers_csv)!=(urldecode($myCSVHeadersList)))
	{
		//echo "Objet : ".Tools::Display($myLoadedLexiqueAssociation);
		$error_msg="Les entêtes ne correspondent pas, veuillez choisir le bon fichier, ou réaliser à nouveau l'association des colonnes<br>\n"; //.serialize($myLoadedLexiqueAssociation->headers_csv)."<br />\nvs<br>\n".(urldecode($myCSVHeadersList))."<br>\n, veuillez choisir le bon fichier, ou réaliser à nouveau l'association des colonnes<br>\n";
		unset($myDataFile);
	}
}

if($action!="" && (!isset($myHeaders) || !is_array($myHeaders) || count($myHeaders)<=0))
	$error_msg="Aucun fichier CSV transmis, ou aucun lexique défini. Files : ".Tools::Display($_FILES);


$display_msg=trim($display_msg);
$error_msg=trim($error_msg);

if($error_msg=="" && isset($myDataFile))
{
	$display_msg.="Traitement du fichier envoyé<br>\n";
	foreach($myHeaders as $key=>$val)
	{
		$myReverseHeaders[$val]=$key;
	}
	//$display_msg.="Headers invers : ".Tools::Display($myReverseHeaders);
	//echo "Lexique : ".Tools::Display($myCurLexique);
	//$display_msg.="Associations : ".Tools::Display($myLoadedLexiqueAssociation->headers_associations);
	$NbLignes=$myDataFile->rowcount();
	$display_msg.="Nb de lignes : ".$NbLignes." dont 1 ligne d'entête soit ".intval($NbLignes-1)." ligne(s) de données<br>\n";
	//$display_msg.="Lexique : ".Tools::Display($myCurLexique)."<br>\n";
	$count_upd=0; $count_add=0;
	$count_upd_err=0; $count_add_err=0;
	$myExtKey=$myLoadedLexiqueAssociation->external_key;
	$myKeyValuesList=array();
	for($i=1;$i<$NbLignes;$i++)
	{
		$myObj=new stdClass();
		$lex_has_data=false;
		foreach($myLoadedLexiqueAssociation->headers_associations as $key_base=>$key_csv)
		{
			$myObj->$key_base=$myDataFile->val($i,$myReverseHeaders[$key_csv]);
			if($key_csv==$myExtKey)
				$myLexKey=$key_base;			
		}
		$myObjectVars=get_object_vars($myObj);
		foreach($myObjectVars as $propkey=>$propvalue)
			if(trim($propvalue)!="" && !is_null($propvalue))
				$lex_has_data=true;
				
		//echo "Ajout (".(($lex_has_data)?"oui":"non").") : ".Tools::Display($myObj);
		if(!in_array($myObj->$myExtKey,$myKeyValuesList))
			$myKeyValuesList[]=$myObj->$myExtKey;
		//$display_msg.="Recherche d'un enregistrement avec clef ".$myExtKey." qui vaut ".$myObj->$myExtKey."<br>\n";
		$database->setQuery("SELECT * FROM ".$myCurLexique->tablename." WHERE ".$myExtKey."='".$myObj->$myExtKey."';");
		$myList=$database->loadObjectList();
		$result_upd=null;
		$result_add=null;
		$result_add_err=null;
		$result_upd_err=null;
		//$display_msg.="Traitement ligne ".$i."<br>\n";
		if(!isset($myObj->$myExtKey) || $myObj->$myExtKey=="" || is_null($myObj->$myExtKey))
			$lex_has_data=false;
		if($lex_has_data==true)
		{
			if(!is_array($myList) || count($myList)<=0)
			{
				//$display_msg.=" ajout ...";
				// DELETE FROM `entite_hydrographique` WHERE `id_cours_eau`>71594
				//echo "Ajout (".(($lex_has_data)?"oui":"non").") : ".Tools::Display($myObj);
				$result_add=$database->insertObject($myCurLexique->tablename,$myObj,$myCurLexique->tablekey);
			}
			else
			{
				//$display_msg.=" maj  ...";
				$myObj->$myExtKey=$myList[0]->$myExtKey;
				$result_upd=$database->updateObject($myCurLexique->tablename,$myObj,$myExtKey);
			}
		}
		else
		{
			$result_add=false;
			$result_upd=false;
			$result_add_err=true;
			$result_upd_err=false;
		}
		//$display_msg.="Requête : ".$database->getQuery();
		//echo "Objet pour la première ligne : ".Tools::Display($myObj)."<br>\n";
		if($result_add===true)
			$count_add++;
		if($result_upd===true)
			$count_upd++;
		if($result_add_err===false)
			$count_add_err++;
		if($result_upd_err===false)
			$count_upd_err++;
	}
	$display_msg.="Nombre de valeurs différentes trouvées pour la clef : ".count($myKeyValuesList)." <br />\n";
	$display_msg.="Nb de lignes ajoutée : ".$count_add." , mises à jour : ".$count_upd."<br>\n";
	$display_msg.="Erreurs d'ajout : ".$count_add_err." , de mises à jour : ".$count_upd_err."<br>\n";
}
?>
<html>
<head><title>Croisement base de données lexique <=> fichier source CSV</title></head>
<body>

	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>
	
	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
	
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label>Sélectionner le fichier CSV qui sera importé en base : <input type="file" name="userfile"  /></label><br />
		<label>Sélectionner le lexique de référence : <select name="lexique"><option value="" selected>Sélectionner un lexique</option>
		<?php
			if($myClassList!==false)
			{
				echo "<optgroup label=\"Lexiques définis\">\n";
				foreach($myClassList as $curclass)
					if($lexkeyarray=array_keys($myLexiquesList,$curclass))
					{
						foreach($lexkeyarray as $lexkey)
						{
							list($myClassName,$myLexCode)=explode(".",$lexkey);						
							echo "<option value=\"".$lexkey."\" ".(($curclass==$myClassName && $myCodeLexique==$myLexCode)?"selected":"")." >".$myClassName." ==> ".$myLexCode."</option>\n";	
						}
					}

				echo "</optgroup>\n";
			}
		?>
		</select></label><br />
		<input type="hidden" name="action" value="sendcsv" />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>
	<a href="index.php">Retour</a>
</body>
</html>