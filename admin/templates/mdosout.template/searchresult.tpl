<div style="text-align:center">
	<h3>Effecture une nouvelle recherche de masse d'eau souterraine :</h3>
	<form id="formRecherche" name="formRecherche" method="post" action="{FORM_PAGE}">
		<label>
		<input type="text" name="txtRecherche" id="txtRecherche" style="width:300px;" value="{texte_recherche}" />
		<input type="submit" name="cmdOk" value="Recherche" />
		</label>
		<input type="hidden" name="section" id="section" value="search" />
	</form>
</div><br />
{TABLE_BEGIN}
<table class="mdtb_form_table" cellspacing="0" cellpadding="0" border="0" align="center">
<thead>
    <tr>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Code ME
            <a href="{FORM_PAGE}?section=search&ssfield=code_me&ssorder=ASC&txtRecherche={texte_recherche}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=code_me&ssorder=DESC&txtRecherche={texte_recherche}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
			Libellé
            <a href="{FORM_PAGE}?section=search&ssfield=libelle_me&ssorder=ASC&txtRecherche={texte_recherche}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=libelle_me&ssorder=DESC&txtRecherche={texte_recherche}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            N° Dép.
            <a href="{FORM_PAGE}?section=search&ssfield=n__departement&ssorder=ASC&txtRecherche={texte_recherche}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=n__departement&ssorder=DESC&txtRecherche={texte_recherche}"><img border="0" src="images/desc.gif" /></a>
        </th>
        <th nowrap="nowrap" style="padding:2px" valign="middle">
            Région
            <a href="{FORM_PAGE}?section=search&ssfield=nom__region&ssorder=ASC&txtRecherche={texte_recherche}"><img border="0" src="images/asc.gif" /></a>&nbsp;
			&nbsp;<a href="{FORM_PAGE}?section=search&ssfield=nom__region&ssorder=DESC&txtRecherche={texte_recherche}"><img border="0" src="images/desc.gif" /></a>
        </th>
    </tr>
</thead>
<tbody>
    <!-- BEGIN tablecontent -->
        <tr onclick="location.href='{FORM_PAGE}?section=fiche&txtRecherche={tablecontent.texte_recherche}&code_me={tablecontent.code_me}';" class="mdtb_content_row" onmouseover="this.className='mdtb_content_row_over';" onmouseout="this.className='mdtb_content_row';">
            <td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&txtRecherche={tablecontent.texte_recherche}&code_me={tablecontent.code_me}">{tablecontent.code_me}</a>
            </td>
            <td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&txtRecherche={tablecontent.texte_recherche}&code_me={tablecontent.code_me}">{tablecontent.libelle_me}</a>
            </td>
            <td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&txtRecherche={tablecontent.texte_recherche}&code_me={tablecontent.code_me}">{tablecontent.n__departement}</a>
            </td>
            <td class="mdtb_content_cell">
                <a href="{FORM_PAGE}?section=fiche&txtRecherche={tablecontent.texte_recherche}&code_me={tablecontent.code_me}">{tablecontent.nom__region}</a>
            </td>
        </tr>
    <!-- END tablecontent -->
</tbody>
</table>
{TABLE_END}