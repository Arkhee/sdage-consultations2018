<?php
/*
 * diren-pcb
 * Created on 16 janv. 2009
 * Copyright  ©  2009 Yannick Bétemps yannick@alternetic.com
 * www.alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : index.php
 * Description : 
 * Facilite l'accès au script d'import des données poisson
 * Fournit la procédure de release des données
 */
/*
 * Correctifs locaux à la base
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
<title>Gestion des procédures d'administration</title>
<style>
body,html { font-size:14px;}
fieldset { margin-top:10px; }
fieldset legend { font-size:16px; font-weight:bold; }
</style>
</head>
<body>
<h1>Procédures d'administration - PCB</h1>
<fieldset>
<legend>Procédures de mise en place de la base</legend>
<ul>
<li>
	<a href="sqlparser/parsesql.php">Récupération de la structure et des données de la base Access de référence</a>
	<blockquote>
 * Description : <br />
 * Extraire les données issues de la base Access MDO Sout<br />
	</blockquote>
</li>
<li>
	<a href="import_croisement_ehv2_me.php">Intégration du fichier de référence croisée : EHV2 <=> ME</a>
	<blockquote>
 * Description : <br />
 * Analyse et extraction des données croisées entre les entités hydro en v2 et les masses d'eau<br />
 * Le fichier doit contenir les noms des documents de fiches de synthèses<br />
	</blockquote>
</li>
</ul>
</fieldset>
<fieldset>
<legend>Procédures de mise en place de la base</legend>
<ul>
<li>
	<a href="sqlparser/parsesql.php">Extraction et analyse des requêtes de création de base de données</a>
	<blockquote>
 * Description : <br />
 * Utilise la classe sqlparse.lib.php de phpMyAdmin pour parser des fichiers SQL de création <br />
 * de base de données, et sauve le résultat de ces analyses en objet Json dans un chemin adapté
	</blockquote>
</li>
<li>
	<a href="generate_classfromdatabase.php">Actualiser les fichiers de classe sur base des tables présentes en base de données</a>
	<blockquote>
 * Description : <br />
 * Propose de sélectionner n tables présentes dans la base, les analyse et permet leur exploitation dans le cadre de l'administration des données<br />
	</blockquote>
</li>
<li>
	<a href="generate_tplfiles.php?pass=reset_tpl_files">Génération des fichiers de base d'accès aux tables (pages)</a> - 
	<a href="generate_tplfiles.php?pass=reset_tpl_files&overwrite=1">Génération des fichiers de base d'accès aux tables (pages) - Remplacement Forcé</a>
	<blockquote>
 * Description : <br />
 * Fabrique les fichier tpl_ qui sont les pages permettant l'accès aux tables de la base de données <br />
 * La liste des fichiers tpl est construite à partir du dossier importdata, et des fichiers json qu'il contient
	</blockquote>
</li>
<li>
	<a href="generate_classfiles.php?pass=class">Génération des fichiers de CLASSE d'accès aux tables (pages)</a>
	<a href="generate_classfiles.php?pass=class&overwrite=1">Génération des fichiers de CLASSE d'accès aux tables (pages) - Remplacement Forcé</a>
	<blockquote>
 * Description :  <br />
 * Génère les fichiers de classe, décrivant le fonctionnement des formulaires de MDTB <br />
 * Ces fichiers de classe sont basés sur la description SQL des tables <br />
 * La description SQL est fournie dans les fichiers json générés par parsesql.php
	</blockquote>
</li>
<li>
	<a href="generate_includefile.php?pass=include">Création du fichier d'includes</a>
	<blockquote>
 * Description : <br/>
 * Génère le fichier d'include qui sera appelé pour générer toutes les classes <br/>
 * Ce fichier fait appel aux fichiers json du dossier d'import <br/>
	</blockquote>
</li>

<li>
	<a href="generate_menu.php?pass=menu">Création du menu</a>
	<blockquote>
 * Description : Génère automatiquement le menu à afficher en colonne de gauche
	</blockquote>
</li>
</ul>
</fieldset>
<fieldset>
<legend>Gestion des lexiques et des associations avec les données externes</legend>
<ul>
<li>
	<a href="generate_lexiquescrossref.php">Référence croisée lexique <=> fichier d'import csv</a>
	<blockquote>
 * Description :<br /> 
 * Cette page permet de réaliser le paramétrage de croisement des données entre un fichier d'import<br />
 * et son équivalent en base de données de Lexique
	</blockquote>
</li>

<li>
	<a href="generate_lexiqueimport.php">Import d'un lexique en base</a>
	<blockquote>
 * Description : <br /> 
 * Importe un lexique en base, se basant sur le fichier de concordance base <=> csv défini précédemment<br /> 
 * L'import se fait en mettant à jour les enregistrements pré-existants
	</blockquote>
</li>
</ul>
</fieldset>
<a href="../">Retour</a>
</body>
</html>