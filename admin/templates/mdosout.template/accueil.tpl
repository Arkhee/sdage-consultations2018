<p align="justify">Ce référentiel a été réalisé pour l'état des lieux du SDAGE 2022-2027.</p>

<form id="formRecherche" name="formRecherche" method="post" action="{FORM_PAGE}">
	<div class='texteRecherche'>
		<input type="text" name="txtRecherche" placeholder="Saisissez un code ou libellé de masse d'eau" id="txtRecherche" value="{texte_recherche}" /><br />
		<input type="submit" name="cmdOk" value="Recherche" />
	</div>
	<div class="blocfiltre typesmdo"><label placeholder="Sélectionnez une catégorie de masse d'eau">Catégorie de masse d'eau :</label>{CMB_TYPEMDO}</div>
	<!-- <span onclick="jQuery('#rechercheavancee').toggle();" style="cursor:pointer;text-decoration: underline;"><i class="fas fa-plus-square"></i>Recherche avancée</span> -->
	<div id="rechercheavancee" style="display:flex;">
		<div class="blocfiltre"><label placeholder="Sélectionnez une sous-unité">Sous-unité territoriale :</label>{CMB_SS_UT}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez un sous-bassin">Sous-bassin versant :</label>{CMB_SSBV}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez une pression">Pressions :</label>{CMB_PRESSIONS}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez un niveau d'impact">Impact :</label>{CMB_IMPACT}</div>
	</div>
  <input type="hidden" name="section" id="section" value="search" />
</form>
