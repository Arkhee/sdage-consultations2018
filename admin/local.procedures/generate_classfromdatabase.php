<?php
/*
 * diren-pcb
 * Created on 15 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : tpl_generate_importdescription.php
 * Description : 
 * Réalise l'import de la description de l'import en base
 * Cette description établit la correspondance entre les champs du fichier d'import et les champs de la table de destination
 */
 
/*
 * Initialisation du contexte, chargement des fichiers de paramètres
 */
$path_pre="../";

require_once($path_pre."config.inc.php");
require_once("utils.class.php");
require_once("parametre.class.php");
//$myImportParams["txtCodeImport"]="";

Tools::BindToGlobal($_GET);
Tools::BindToGlobal($_POST);
Tools::BindToGlobal($_FILES);
$error_msg="";
$display_msg="";
$json_path="importdata/";
//require_once ($path_pre."local.lib/excel_reader2.php");
//$xls = new Spreadsheet_Excel_Reader("importdata/analyse-donnees-poisson.xls");
//$xls=new clsDataFile("importdata/repartition-donnees-excel-mcd.csv","csv");

/*
 * Initialisation des variables locales
 */
//$myCodeImport=$txtCodeImport;
if(isset($cmdOk))
{
	if(isset($lstNomsTables) && is_array($lstNomsTables) && count($lstNomsTables)>0)
	{
		$_POST["action_on_table"]="sendtable";
		$myFileName=$json_path."createtable.sql";
		$_POST["sendtable_filename"]="createtable.sql";
		$myError=false;
		if(file_exists($myFileName)) unlink($myFileName);
		if(!$fh=fopen($myFileName,"wt"))
		{
			$myError=true;
			$error_msg="Impossible d'ouvrir le fichier de sauvegarde des requêtes".BR;
		}
		else
		{
			foreach($lstNomsTables as $curtable)
			{
				$database->setQuery("SHOW CREATE TABLE ".$curtable);
				$myCreateTable=$database->loadObjectList();
				if(is_array($myCreateTable) && count($myCreateTable)>0)
				{
					$myArrayCreateTable=get_object_vars($myCreateTable[0]);
					//echo "Retour requete : ".Tools::Display($myArrayCreateTable);
					$myCreateTableSQL=$myArrayCreateTable["Create Table"];
					$myCreateTableSQL = preg_replace('/AUTO_INCREMENT\s*=\s*([0-9])+/', '     ', $myCreateTableSQL);
					if(!fwrite($fh,$myCreateTableSQL.";\r\n"))
					{
						$myError=true;
						$error_msg="Erreur : impossible d'écrire le fichier de sauvegarde des requêtes".BR;
					}
					//echo "Ajout SQL : ".$myCreateTableSQL.BR;
				}
			}
			fclose($fh);
			//echo "Contenu du fichier de requêtes: ".Tools::Display(file_get_contents($myFileName));
		}
		if(!$myError)
		{
			//echo "Traitement de ".Tools::Display($lstNomsTables);
			unset($_SESSION["userconf"]);
			unset($_SESSION["PMA_Theme_Manager"]);
			unset($_SESSION["PMA_Config"]);
			unset($_SESSION[" PMA_token "]);
			unset($_SESSION["parent"]);
			chdir("sqlparser");
			require_once("parsesql.php");
			die();
		}
	}
	else
		$error_msg="Vous devez sélectionner au moins une table à importer".BR;
}
?>
<html><head><title>Actualiser les fichiers de classe sur base des tables présentes en base de données</title></head>
<body>
<?php

?>
	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>
	
	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
	
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<fieldset>
			<legend>Sélectionner les tables à importer :</legend>
			<?php
				$myListTables=$database->getTablesList();
				if(is_array($myListTables) && count($myListTables)>0)
				{
					foreach($myListTables as $curtable)
					{
						echo "<label><input type='checkbox' name='lstNomsTables[]' id='lstNomsTables_".$curtable."' value='".$curtable."' />".$curtable."</label>".BR;
					}
					?>
					<input type="submit" name="cmdOk" value="Ok" />
					<?php
				}
			?>
		</fieldset>
	</form>

<a href="index.php">Retour</a>
</body>
</html>