<div style="display:none;">{FORM_PARAMS}</div>
<form id="formRecherche" name="formRecherche" method="post" action="{FORM_PAGE}">
	<h3>Affinez votre recherche :</h3>
	<div class='texteRecherche'>
		<input type="text" name="txtRecherche" placeholder="Saisissez un code ou libellé de masse d'eau" id="txtRecherche" value="{texte_recherche}" /><br />
	</div>
	<div class="blocfiltre typesmdo"><label placeholder="Sélectionnez un catégorie de masse d'eau">Catégorie de masse d'eau :</label>{CMB_TYPEMDO}</div>
	<!-- <span onclick="jQuery('#rechercheavancee').toggle();" style="cursor:pointer;text-decoration: underline;"><i class="fas fa-plus-square"></i>Recherche avancée</span> -->
	<div id="rechercheavancee" style="display:flex;">
		<div class="blocfiltre"><label placeholder="Sélectionnez une sous-unité">Sous-unité territoriale :</label>{CMB_SS_UT}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez un sous-bassin">Sous-bassin versant :</label>{CMB_SSBV}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez une pression">Pressions :</label>{CMB_PRESSIONS}</div>
		<div class="blocfiltre"><label placeholder="Sélectionnez un niveau d'impact">Impact :</label>{CMB_IMPACT}</div>
	</div>
	<div class="conteneurBoutonAffiner"><input type="submit" name="cmdOk" value="Affiner la recherche" /></div>
	<div class="pagination_resultat">Page&nbsp;:&nbsp;{CMB_PAGINATION}&nbsp;/&nbsp;{nb_pages}</div>
  <input type="hidden" name="section" id="section" value="search" />
</form>
				
{TABLE_BEGIN}
<table class="mdtb_form_table_searchresult" cellspacing="0" cellpadding="0" border="0" align="center">
<thead>
    <tr>
		<th style="padding:2px" valign="middle">
			&nbsp;
		</th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Code Masse d'eau
            <a href="{FORM_PAGE}?section=search&ssfield=code_me&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=code_me&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Libellé
            <a href="{FORM_PAGE}?section=search&ssfield=libelle_me&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=libelle_me&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Catégorie de masse d'eau
            <a href="{FORM_PAGE}?section=search&ssfield=categorie_me&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=categorie_me&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            Sous-bassin versant
            <a href="{FORM_PAGE}?section=search&ssfield=ssbv.code_ssbv&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=ssbv.code_ssbv&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            Sous-unité Territoriale
            <a href="{FORM_PAGE}?section=search&ssfield=code_ss_ut_sort&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=code_ss_ut_sort&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
    </tr>
</thead>
<tbody>
    <!-- BEGIN tablecontent -->
		<!-- onclick="location.href='{FORM_PAGE}?section=fiche&{QUERY_PARAMS}&code_me={tablecontent.code_me}';" -->
        <tr class="mdtb_content_row" onclick="frontCtl.toggleLigneResultat(this);">
            <td class="mdtb_displaymore_cell" >
				<i class="fas fa-angle-right icon"></i>
				<i class="fas fa-angle-down icon icon-on" style="display:none;"></i>
			</td>
			<td class="mdtb_content_cell">
                {tablecontent.code_me}
            </td>
            <td class="mdtb_content_cell">
                {tablecontent.libelle_me}
            </td>
            <td class="mdtb_content_cell">
                {tablecontent.categorie_me}
            </td>
            <td class="mdtb_content_cell">
                {tablecontent.code_ssbv}&nbsp;-&nbsp;{tablecontent.libelle_ssbv}
            </td>
            <td class="mdtb_content_cell">
                {tablecontent.code_ss_ut}&nbsp;-&nbsp;{tablecontent.libelle_ss_ut}
            </td>
        </tr>
        <tr style="display:none;">
			<td colspan="6">
				{tablecontent.detail_pressions}
			</td>
        </tr>
    <!-- END tablecontent -->
</tbody>
</table>
<iframe id="targetSauvegarde" name="targetSauvegarde" style="display:none;width:100%;height:20px;"></iframe>
{TABLE_END}