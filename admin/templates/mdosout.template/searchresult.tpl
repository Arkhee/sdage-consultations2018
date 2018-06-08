<div style="display:none;">{FORM_PARAMS}</div>
<h3>Affinez votre recherche :</h3>
{FORMULAIRE_RECHERCHE}
				
{TABLE_BEGIN}
<table class="mdtb_form_table_searchresult" cellspacing="0" cellpadding="0" border="0" align="center">
<thead>
    <tr>
		<th style="padding:2px" valign="middle">
			&nbsp;
		</th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Code Masse d'eau
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="code_me" data-order="ASC"><img border="0" src="images/asc.gif" /></button>
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="code_me" data-order="DESC"><img border="0" src="images/desc.gif" /></button>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Libellé
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="libelle_me" data-order="ASC"><img border="0" src="images/asc.gif" /></button>
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="libelle_me" data-order="DESC"><img border="0" src="images/desc.gif" /></button>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Catégorie de masse d'eau
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="categorie_me" data-order="ASC"><img border="0" src="images/asc.gif" /></button>
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="categorie_me" data-order="DESC"><img border="0" src="images/desc.gif" /></button>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            Sous-bassin
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="ssbv.code_ssbv" data-order="ASC"><img border="0" src="images/asc.gif" /></button>
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="ssbv.code_ssbv" data-order="DESC"><img border="0" src="images/desc.gif" /></button>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            Sous-unité Territoriale
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="code_ss_ut_sort" data-order="ASC"><img border="0" src="images/asc.gif" /></button>
			<button onclick="frontCtl.sortAndSearch(this);" data-sort="code_ss_ut_sort" data-order="DESC"><img border="0" src="images/desc.gif" /></button>
        </th>
    </tr>
</thead>
<tbody>
    <!-- BEGIN tablecontent -->
		<!-- onclick="location.href='{FORM_PAGE}?section=fiche&{QUERY_PARAMS}&code_me={tablecontent.code_me}';" -->
        <tr class="mdtb_content_row {tablecontent.line_odd_even}" onclick="frontCtl.toggleLigneResultat(this);">
            <td class="mdtb_displaymore_cell" >
				<i class="fas fa-angle-right icon"></i>
				<i class="fas fa-angle-down icon icon-on" style="display:none;"></i>
			</td>
			<td class="mdtb_content_cell">
                {tablecontent.code_me}{tablecontent.lbl_nb_avis_mdo}
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