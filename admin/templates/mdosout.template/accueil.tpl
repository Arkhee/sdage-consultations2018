<p align="justify">Ce r�f�rentiel a �t� r�alis� pour l'�tat des lieux du SDAGE 2016-2021.</p>
	<form id="formRecherche" name="formRecherche" method="post" action="{FORM_PAGE}">
		<label>
		<input type="text" name="txtRecherche" id="txtRecherche" style="width:300px;" />
	    <input type="submit" name="cmdOk" value="Recherche" />
	  </label>
		<span onclick="jQuery('#rechercheavancee').toggle();" style="cursor:pointer;text-decoration: underline;"><i class="fas fa-plus-square"></i>Recherche avanc�e</span>
		<div id="rechercheavancee" style="display:none;">
			Crit�res de recherche avanc�s
		</div>
	  <input type="hidden" name="section" id="section" value="search" />
  </form>
    
	<p align="justify"><strong>Pour la recherche</strong>, vous pouvez fournir un code de masse d'eau ou un intitul&eacute; de masse d'eau</p>
