<?php
/*
 * diren-pcb
 * Created on 15 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 *
 * Fichier : import_croisement_ehv2_me.php
 * Procédure d'import des croisements de données entre les entités hydro en v2 et les masses d'eau, en intégrant les noms des fichiers
 */
$path_pre="../";
require_once($path_pre."config.inc.php");
require_once($path_pre."local.classes/rel_entites_hydro_me.class.php");
require_once("parametre.class.php");
$myClsParam=new clsParametre($database);
$myXLSFileName="";
$myReferenceHeaders=array(
		"Entite_V2",
		"MS_CD",
		"ME code Européen",
		"Cartes .pdf",
		"Titre carte",
		"Fiches .pdf",
		"Titre fiche"
		);


$myHeaderToFieldReferenceForFiche=array(
		"Entite_V2"=>"code_entite",
		"MS_CD"=>"code_me_fr",
		"ME  code Européen"=>"code_me_eu",
		"Fiches .pdf"=>"nom_fichier",
		"Titre fiche"=>"titre_fichier"
		);
$myHeaderToFieldReferenceForCarte=array(
		"Entite_V2"=>"code_entite",
		"MS_CD"=>"code_me_fr",
		"ME  code Européen"=>"code_me_eu",
		"Cartes .pdf"=>"nom_fichier",
		"Titre carte"=>"titre_fichier"
		);
if(isset($_FILES["userfile"]["tmp_name"]))
{
	$myXLSFileName=$_FILES["userfile"]["tmp_name"];
	$myXLSName=$_FILES["userfile"]["name"];
}
$error_msg="";
$display_msg="";

function checkHeaders($theHeader,$theReferenceHeaders)
{
	foreach($theHeader as $curheader)
	{
		if(!in_array($curheader,$theReferenceHeaders))
			return false;
	}

	foreach($theReferenceHeaders as $curheader)
	{
		if(!in_array($curheader,$theHeader))
			return false;
	}
	return true;
}

if(file_exists($myXLSFileName) )
{
	if(isset($_POST["chkNormalizeCSV"]) && $_POST["chkNormalizeCSV"]=="1")
	{
		$display_msg.= "Normalisation du fichier ...";
		$myRetour=Tools::NormalizeCSV($myXLSFileName);
		$display_msg.= ($myRetour?"OK":"ERREUR")."<br/>\n";
	}

	$display_msg.= "Ouverture du fichier ".$myXLSName."<br>\n";
	if(is_readable($myXLSFileName))
	{
		$display_msg.= "Fichier lisible ...<br>\n";
		$xls=new clsDataFile($myXLSFileName,"csv");
/*
		$xls = new Spreadsheet_Excel_Reader($myXLSFileName);
*/
		$display_msg.= "Fichier ouvert ...<br>\n";
		$row=1;
		$col=1;
		if($xls->initok)
		{
			$display_msg.="Début de l'import des données";
			$myFileHeaders=array();

			for($i=0;$i<$xls->colcount();$i++)
			{
				$myTmpVal=$xls->val(0,$i);
				$myTmpVal=str_replace("\r","",$myTmpVal);
				$myTmpVal=str_replace("\n","",$myTmpVal);
				$myFileHeaders[$i]=$myTmpVal;
			}

			if(!checkHeaders($myFileHeaders,$myReferenceHeaders))
			{
				$myCrossRefForFiche=array();
				$myCrossRefForCarte=array();
				foreach($myFileHeaders as $col=>$curheader)
				{
					$curheader=trim(str_replace("\r","",str_replace("\n","",$curheader)));
					if(array_key_exists($curheader, $myHeaderToFieldReferenceForCarte))
					{
						$myCrossRefForCarte[$myHeaderToFieldReferenceForCarte[$curheader]]=$col;
					}

					if(array_key_exists($curheader, $myHeaderToFieldReferenceForFiche))
					{
						$myCrossRefForFiche[$myHeaderToFieldReferenceForFiche[$curheader]]=$col;
					}
				}

				for($line=1;$line<=$xls->rowcount();$line++)
				{
					$myCurRelEntiteFiche=new rel_entites_hydro_me($database,"fiche");
					foreach($myCrossRefForFiche as $property=>$col)
					{
						$myCurRelEntiteFiche->setProperty($property, $xls->val($line,$col));
					}
					if($myCurRelEntiteFiche->checkMinimalValues())
					{
						$myReturnFiche=$myCurRelEntiteFiche->store();
					}

					$myCurRelEntiteCarte=new rel_entites_hydro_me($database,"carte");
					foreach($myCrossRefForCarte as $property=>$col)
					{
						$myCurRelEntiteCarte->setProperty($property, $xls->val($line,$col));
					}
					if($myCurRelEntiteCarte->checkMinimalValues())
					{
						$myReturnCarte=$myCurRelEntiteCarte->store();
					}
					$display_msg.="Ligne ".$line." : Fiche enregistrée : ".($myReturnFiche?"oui":"non").", Carte enregistrée : ".($myReturnCarte?"oui":"non")." <br>\n";
				}
			}
			else
				$error_msg.= "Les entêtes ne correspondent pas, le fichier n'est pas au bon format<br>\n";
		}
	}
	else
		$error_msg.= "Fichier non lisible<br>\n";
}
else
	if($myXLSFileName!="")
		$error_msg.= "Fichier non existant<br>\n";
?>
<html><head><title>Import des données du fichier de croisement EHV2 <=> ME contenant les codes de fichiers</title></head>
<body>

	<?php if($error_msg!="") { ?>
			<div id="error_msg" style="border:1px solid #AA0000;padding:10px; font-size:16px;"><?php echo $error_msg;?></div><br />
	<?php } ?>

	<?php if($display_msg!="") { ?>
			<div id="display_msg" style="border:1px solid #00AA00;padding:10px; font-size:16px;"><?php echo $display_msg;?></div>
	<?php } ?>
	<h2>Import des données du fichier de croisement EHV2 <=> ME contenant les codes de fichiers</h2>
	<form action="http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data" >
		<label>Sélectionner le fichier CSV : <input type="file" name="userfile"  /></label><br />
		<label><input type="checkbox" name="chkNormalizeCSV" value="1" checked />Normaliser le fichier CSV : ôter mes retours chariot des contenus de cellules (\n seuls transformés en espaces)</label><br />
		<input type="submit" name="cmdOk" value="Ok" />
	</form>


<a href="index.php">Retour</a>