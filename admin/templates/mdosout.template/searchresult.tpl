<div style="display:none;">{FORM_PARAMS}</div>
<form id="formRecherche" name="formRecherche" method="post" action="{FORM_PAGE}">
	<h3>Affinez votre recherche :</h3>
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
				
{TABLE_BEGIN}
<table class="mdtb_form_table_searchresult" cellspacing="0" cellpadding="0" border="0" align="center">
<thead>
    <tr>
		<th style="padding:2px" valign="middle">
			&nbsp;
		</th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Code ME
            <a href="{FORM_PAGE}?section=search&ssfield=code_me&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=code_me&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Libellé
            <a href="{FORM_PAGE}?section=search&ssfield=libelle_me&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=libelle_me&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            Sous-bassin versant
            <a href="{FORM_PAGE}?section=search&ssfield=code_ssbv&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=code_ssbv&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            Sous-unité Territoriale
            <a href="{FORM_PAGE}?section=search&ssfield=code_ss_ut&ssorder=ASC&{QUERY_PARAMS}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=code_ss_ut&ssorder=DESC&{QUERY_PARAMS}"><img border="0" src="images/desc.gif" /></a>
        </th>
    </tr>
</thead>
<tbody>
    <!-- BEGIN tablecontent -->
		<!-- onclick="location.href='{FORM_PAGE}?section=fiche&{QUERY_PARAMS}&code_me={tablecontent.code_me}';" -->
        <tr class="mdtb_content_row">
            <td class="mdtb_displaymore_cell" onclick="frontCtl.toggleLigneResultat(this);">
				<i class="fas fa-angle-right icon"></i>
				<i class="fas fa-angle-down icon icon-on" style="display:none;"></i>
			</td>
			<td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&{QUERY_PARAMS}&code_me={tablecontent.code_me}">{tablecontent.code_me}</a>
            </td>
            <td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&{QUERY_PARAMS}&code_me={tablecontent.code_me}">{tablecontent.libelle_me}</a>
            </td>
            <td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&{QUERY_PARAMS}&code_me={tablecontent.code_me}">{tablecontent.code_ssbv}</a>
            </td>
            <td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&{QUERY_PARAMS}&code_me={tablecontent.code_me}">{tablecontent.code_ss_ut}</a>
            </td>
        </tr>
        <tr style="display:none;">
			<td colspan="5">
				{tablecontent.detail_pressions}
			</td>
        </tr>
    <!-- END tablecontent -->
</tbody>
</table>
<iframe id="targetSauvegarde" name="targetSauvegarde" style="display:none;width:100%;height:20px;"></iframe>
{TABLE_END}