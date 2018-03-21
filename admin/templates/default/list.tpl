<!-- BEGIN linktype_caller -->
<script type="text/javascript">
	function setHref(theRefKeyValue,theRefLabelValue)
	{
		window.opener.document.getElementById('{linktype_caller.REF_KEY}').value=theRefKeyValue;
		window.opener.document.getElementById('{linktype_caller.REF_LABEL}').value=theRefLabelValue;
		window.close();
	}
</script>
<!-- END linktype_caller -->
{TABLE_BEGIN}

<table class="mdtb_form_table" cellspacing="0" cellpadding="0" border="0">
<thead>
	<tr class="lightbordercolor">
		<!-- BEGIN tableheader -->
		<th>
			{tableheader.HEADER_CELL}
		</th>
		<!-- END tableheader -->
		<th>{LABEL_ACTIONS}</th>
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
						<a href="{tablecontent.rowcontent.linktype_detail_open.LINK}" class="mdtb_cell_link">
					<!-- END linktype_detail_open -->
					<!-- BEGIN linktype_caller_open -->
						<a href="#" onclick="setHref('{tablecontent.rowcontent.linktype_caller_open.REF_KEY}','{tablecontent.rowcontent.linktype_caller_open.REF_LABEL}')" class="mdtb_cell_link">
					<!-- END linktype_caller_open -->
					{tablecontent.rowcontent.CONTENT_CELL}&nbsp;
					<!-- BEGIN linktype_detail_close -->
						</a>
					<!-- END linktype_detail_close -->
					<!-- BEGIN linktype_caller_close -->
						</a>
					<!-- END linktype_caller_close -->
				</td>
			<!-- END rowcontent -->
			<td class="mdtb_content_cell">
			{tablecontent.ACTION_SPECIAL}
			<a href="{tablecontent.ACTION_EDIT_LINK}" {tablecontent.ACTION_EDIT_MORE} class="mdtb_cell_link">{tablecontent.ACTION_EDIT_LABEL}</a>
			<a href="{tablecontent.ACTION_DEL_LINK}" {tablecontent.ACTION_DEL_MORE} class="mdtb_cell_link">{tablecontent.ACTION_DEL_LABEL}</a>
			{tablecontent.ACTION_DISSOCIATE}
			</td>
		</tr>
	<!-- END tablecontent -->
</tbody>
</table>
{TABLE_END}