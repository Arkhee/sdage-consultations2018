<div class="table_menu">
    <div class="table_menu_filter">
    	<form action="{URL_DESTINATION}" method="post" onsubmit="return sendFormByAjax('{URL_DESTINATION}');">
	    	<label>{LABEL_FILTER}&nbsp;:&nbsp;<input type="text" id="searchfilter" name="filter" value="{TEXT_FILTER}" /></label>
			<input type="button" name="cmdFilter" value="{LABEL_CMD_FILTER}" onclick="javascript:sendFormByAjax('{URL_DESTINATION}');" />
		</form>
    </div>
</div>

<div id="menuend"></div>
{TABLE_BEGIN}
<table class="mdtb_form_table" cellspacing="0" cellpadding="0" border="0">
<thead>
	<tr class="lightbordercolor">
		<!-- BEGIN tableheader -->
		<th>
			{tableheader.HEADER_CELL}
		</th>
		<!-- END tableheader -->
	</tr>
</thead>
<tfoot>
	<tr class="lightbordercolor">
		<td colspan="{TABLE_COL_COUNT}"  class="mdtb_footer_cell" style="text-align:center;">
			{FOOTER_CELL}
		</td>
	</tr>
</tfoot>
<tbody>
	<!-- BEGIN tablecontent -->
		<tr class="mdtb_content_row">
			<!-- BEGIN rowcontent -->
				<td class="mdtb_content_cell">
					<!-- BEGIN linktype_detail_open -->
                        <a href="{tablecontent.rowcontent.linktype_detail_open.LINK}"  onclick="{tablecontent.rowcontent.linktype_detail_open.ONCLICK}" class="mdtb_cell_link">
					<!-- END linktype_detail_open -->
					{tablecontent.rowcontent.CONTENT_CELL}&nbsp;
					<!-- BEGIN linktype_detail_close -->
						</a>
					<!-- END linktype_detail_close -->
				</td>
			<!-- END rowcontent -->
		</tr>
	<!-- END tablecontent -->
</tbody>
</table>
{TABLE_END}