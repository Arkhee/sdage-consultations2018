<?php
/*
 * diren-pcb
 * Created on 16 janv. 2009
 * Copyright  �  2009 Yannick B�temps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick B�temps
 * 
 * File : index.php
 * Description : 
 * Facilite l'acc�s au script d'import des donn�es poisson
 * Fournit la proc�dure de release des donn�es
 */
/*
 * Correctifs locaux � la base
ALTER TABLE `commune` ADD INDEX ( `com_numero` ) ;
ALTER TABLE `commune` ADD INDEX ( `departement_num_departement` ) ;
ALTER TABLE `departement` ADD INDEX ( `num_departement` ) ;
ALTER TABLE `departement` ADD INDEX ( `region_num_region` ) ;
ALTER TABLE `region` ADD INDEX ( `num_region` ) ;
ALTER TABLE `station_de_mesure` ADD INDEX ( `cea_code_entite` ) ;
ALTER TABLE `station_de_mesure` ADD INDEX ( `sta_com_code_situation` ) ;
ALTER TABLE `station_de_mesure` ADD INDEX ( `sta_com_code_limitrophe` ) ;
ALTER TABLE `entite_hydrographique` ADD INDEX ( `cea_code_entite` ) ;
 */
$path_pre="../";
require_once($path_pre."config.inc.php");

?>
<html>
<head>
<title>Gestion des proc�dures d'administration</title>
<style>
body,html { font-size:14px;}
fieldset { margin-top:10px; }
fieldset legend { font-size:16px; font-weight:bold; }
</style>
</head>
<body>
<h1>Proc�dures d'administration - PCB</h1>
<fieldset>
<legend>Proc�dures de mise en place de la base</legend>
<ul>
<li>
	<a href="sqlparser/parsesql.php">R�cup�ration de la structure et des donn�es de la base Access de r�f�rence</a>
	<blockquote>
 * Description : <br />
 * Extraire les donn�es issues de la base Access MDO Sout<br />
	</blockquote>
</li>
<li>
	<a href="import_croisement_ehv2_me.php">Int�gration du fichier de r�f�rence crois�e : EHV2 <=> ME</a>
	<blockquote>
 * Description : <br />
 * Analyse et extraction des donn�es crois�es entre les entit�s hydro en v2 et les masses d'eau<br />
 * Le fichier doit contenir les noms des documents de fiches de synth�ses<br />
	</blockquote>
</li>
</ul>
</fieldset>
<fieldset>
<legend>Proc�dures de mise en place de la base</legend>
<ul>
<li>
	<a href="sqlparser/parsesql.php">Extraction et analyse des requ�tes de cr�ation de base de donn�es</a>
	<blockquote>
 * Description : <br />
 * Utilise la classe sqlparse.lib.php de phpMyAdmin pour parser des fichiers SQL de cr�ation <br />
 * de base de donn�es, et sauve le r�sultat de ces analyses en objet Json dans un chemin adapt�
	</blockquote>
</li>
<li>
	<a href="generate_classfromdatabase.php">Actualiser les fichiers de classe sur base des tables pr�sentes en base de donn�es</a>
	<blockquote>
 * Description : <br />
 * Propose de s�lectionner n tables pr�sentes dans la base, les analyse et permet leur exploitation dans le cadre de l'administration des donn�es<br />
	</blockquote>
</li>
<li>
	<a href="generate_tplfiles.php?pass=reset_tpl_files">G�n�ration des fichiers de base d'acc�s aux tables (pages)</a> - 
	<a href="generate_tplfiles.php?pass=reset_tpl_files&overwrite=1">G�n�ration des fichiers de base d'acc�s aux tables (pages) - Remplacement Forc�</a>
	<blockquote>
 * Description : <br />
 * Fabrique les fichier tpl_ qui sont les pages permettant l'acc�s aux tables de la base de donn�es <br />
 * La liste des fichiers tpl est construite � partir du dossier importdata, et des fichiers json qu'il contient
	</blockquote>
</li>
<li>
	<a href="generate_classfiles.php?pass=class">G�n�ration des fichiers de CLASSE d'acc�s aux tables (pages)</a>
	<a href="generate_classfiles.php?pass=class&overwrite=1">G�n�ration des fichiers de CLASSE d'acc�s aux tables (pages) - Remplacement Forc�</a>
	<blockquote>
 * Description :  <br />
 * G�n�re les fichiers de classe, d�crivant le fonctionnement des formulaires de MDTB <br />
 * Ces fichiers de classe sont bas�s sur la description SQL des tables <br />
 * La description SQL est fournie dans les fichiers json g�n�r�s par parsesql.php
	</blockquote>
</li>
<li>
	<a href="generate_includefile.php?pass=include">Cr�ation du fichier d'includes</a>
	<blockquote>
 * Description : <br/>
 * G�n�re le fichier d'include qui sera appel� pour g�n�rer toutes les classes <br/>
 * Ce fichier fait appel aux fichiers json du dossier d'import <br/>
	</blockquote>
</li>

<li>
	<a href="generate_menu.php?pass=menu">Cr�ation du menu</a>
	<blockquote>
 * Description : G�n�re automatiquement le menu � afficher en colonne de gauche
	</blockquote>
</li>
</ul>
</fieldset>
<fieldset>
<legend>Gestion des lexiques et des associations avec les donn�es externes</legend>
<ul>
<li>
	<a href="generate_lexiquescrossref.php">R�f�rence crois�e lexique <=> fichier d'import csv</a>
	<blockquote>
 * Description :<br /> 
 * Cette page permet de r�aliser le param�trage de croisement des donn�es entre un fichier d'import<br />
 * et son �quivalent en base de donn�es de Lexique
	</blockquote>
</li>

<li>
	<a href="generate_lexiqueimport.php">Import d'un lexique en base</a>
	<blockquote>
 * Description : <br /> 
 * Importe un lexique en base, se basant sur le fichier de concordance base <=> csv d�fini pr�c�demment<br /> 
 * L'import se fait en mettant � jour les enregistrements pr�-existants
	</blockquote>
</li>
</ul>
</fieldset>
<a href="../">Retour</a>
</body>
</html>