<script>
	var donneesListeSS_UT={DONNEES_LISTE_SS_UT};
	var donneesListeSSBV={DONNEES_LISTE_SSBV};
</script>
<form id="formRecherche" name="formRecherche" method="post" action="{FORM_PAGE}">
	<div class='texteRecherche'>
		<input type="text" name="txtRecherche" placeholder="Saisissez un code ou libellé de masse d'eau" id="txtRecherche" value="{texte_recherche}" /><br />
	</div>
	<div class="blocfiltre typesmdo"><label placeholder="Sélectionnez une catégorie de masse d'eau">Catégorie de masse d'eau :</label>{CMB_TYPEMDO}</div>
	<!-- <span onclick="jQuery('#rechercheavancee').toggle();" style="cursor:pointer;text-decoration: underline;"><i class="fas fa-plus-square"></i>Recherche avancée</span> -->
	<div id="rechercheavancee" style="display:flex;">
		<div class="blocfiltre matcher" data='ss_ut' data-lien="liste_ssbv"><label placeholder="Sélectionnez une sous-unité">Sous-unité territoriale :</label>{CMB_SS_UT}</div>
		<div class="blocfiltre matcher" data='ssbv' data-lien="liste_ss_ut"><label placeholder="Sélectionnez un sous-bassin">Sous-bassin :</label>{CMB_SSBV}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez une pression">Pressions :</label>{CMB_PRESSIONS}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez un niveau d'impact">Impact :</label>{CMB_IMPACT}</div>
	</div>
	<div class="conteneurBoutonAffiner"><input type="submit" name="cmdOk" value="Recherche" /></div>
	<input type="hidden" name="section" id="section" value="search" />
	<input type="hidden" name="field_sort" id="field_sort" value="{field_sort}" />
	<input type="hidden" name="field_order" id="field_order" value="{field_order}" />
	{CMB_PAGINATION}
</form>
