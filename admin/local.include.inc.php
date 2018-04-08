<?php
/*
 * diren-pcb
 * Created on 8 janv. 2009
 * Copyright  @  2007 Yannick Bétemps yannick@alternetic.com
 * Author : Yannick Bétemps
 * 
 * File : local.config.inc.php
 * Description : 
 * 
 */

@include_once($path_pre."local.classes/mdtb_ae_massesdeau.class.php");
@include_once($path_pre."local.classes/mdtb_ae_ss_ut.class.php");
@include_once($path_pre."local.classes/mdtb_ae_ssbv.class.php");
@include_once($path_pre."local.classes/mdtb_ae_avis.class.php");
@include_once($path_pre."local.classes/mdtb_ae_edl_massesdeau.class.php");
@include_once($path_pre."local.classes/mdtb_ae_pressions.class.php");
@require_once($path_pre."local.classes/sdage_metier.class.php");
@require_once($path_pre."classes/CSV.class.php");
/*
@include_once($path_pre."local.classes/mdtb_caracteristiques_me.class.php");
@include_once($path_pre."local.classes/mdtb_code_comgeo_nom_comgeo.class.php");
@include_once($path_pre."local.classes/mdtb_code_comgeo__nom_comgeo.class.php");
@include_once($path_pre."local.classes/mdtb_code_mes_code_comgeo.class.php");
@include_once($path_pre."local.classes/mdtb_code_mes__code_comgeo.class.php");
@include_once($path_pre."local.classes/mdtb_couche_sig.class.php");
@include_once($path_pre."local.classes/mdtb_departements_me.class.php");
@include_once($path_pre."local.classes/mdtb_entites_hydrogeologiques_me.class.php");
@include_once($path_pre."local.classes/mdtb_extraction_requete_carte.class.php");
@include_once($path_pre."local.classes/mdtb_extraction_requete_me_risques.class.php");
@include_once($path_pre."local.classes/mdtb_extraction__requete_me_risques.class.php");
@include_once($path_pre."local.classes/mdtb_grille_nabe.class.php");
@include_once($path_pre."local.classes/mdtb_lexique_departements_rmc.class.php");
@include_once($path_pre."local.classes/mdtb_lexique_lithologie.class.php");
@include_once($path_pre."local.classes/mdtb_lexique_me_plan_eau.class.php");
@include_once($path_pre."local.classes/mdtb_lexique_me_sup_rmc.class.php");
@include_once($path_pre."local.classes/mdtb_mdtb_groupes.class.php");
@include_once($path_pre."local.classes/mdtb_mdtb_users.class.php");
@include_once($path_pre."local.classes/mdtb_mdtb_users_rights.class.php");
@include_once($path_pre."local.classes/mdtb_me_plan_eau_sout.class.php");
@include_once($path_pre."local.classes/mdtb_me_strategiques_aep.class.php");
@include_once($path_pre."local.classes/mdtb_me_sup_sout.class.php");
@include_once($path_pre."local.classes/mdtb_notice.class.php");
@include_once($path_pre."local.classes/mdtb_occupation_sols.class.php");
@include_once($path_pre."local.classes/mdtb_synthese_prelevement_mdo.class.php");
@include_once($path_pre."local.classes/mdtb_tablecorrection.class.php");
?>
 *
 */