<p align="justify">Ce référentiel a été réalisé pour l'état des lieux du SDAGE 2016-2021.</p>

<form id="formRecherche" name="formRecherche" method="post" action="{FORM_PAGE}">
	<label>
		<input type="text" name="txtRecherche" placeholder="Saisissez un code ou libellé de masse d'eau" id="txtRecherche" style="width:80%;" value="{texte_recherche}" />
		<input type="submit" name="cmdOk" value="Recherche" />
	</label>
	<!-- <span onclick="jQuery('#rechercheavancee').toggle();" style="cursor:pointer;text-decoration: underline;"><i class="fas fa-plus-square"></i>Recherche avancée</span> -->
	<div id="rechercheavancee" style="display:flex;">
		<div class="blocfiltre"><label placeholder="Sélectionnez une sous-unité">Sous-unité territoriale :</label>{CMB_SS_UT}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez un sous-bassin">Sous-bassin versant :</label>{CMB_SSBV}</div>
	</div>
  <input type="hidden" name="section" id="section" value="search" />
</form>
