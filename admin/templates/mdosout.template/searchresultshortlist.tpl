<p><a href="csv.php">Télécharger tous vos avis déposés au format CSV</a></p>
{TABLE_BEGIN}
<table class="mdtb_form_table_searchresult shortlist" cellspacing="0" cellpadding="0" border="0" align="center">
<thead>
    <tr>
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
			Type de masse d'eau
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
        <tr class="mdtb_content_row ligne-active">
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
        <tr>
			<td colspan="5">
				{tablecontent.detail_pressions}
			</td>
        </tr>
    <!-- END tablecontent -->
</tbody>
</table>
<iframe id="targetSauvegarde" name="targetSauvegarde" style="display:none;width:100%;height:20px;"></iframe>
{TABLE_END}