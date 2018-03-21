{TABLE_BEGIN}
<table class="mdtb_form_table" cellspacing="0" cellpadding="2" border="0" width="100%>
<tbody>
	<!-- BEGIN tablecontent -->
		<tr class="mdtb_content_row">
			<td class="mdtb_content_cell">
				{tablecontent.ACTION_SPECIAL_ICON}
			</td>
			<!-- BEGIN rowcontent -->
				<td class="mdtb_content_cell">
					<!-- BEGIN linktype_detail_open -->
						<a href="{tablecontent.rowcontent.linktype_detail_open.LINK}" class="mdtb_cell_link">
					<!-- END linktype_detail_open -->
					<!-- BEGIN linktype_caller_open -->
						<a href="#" onclick="setHref('{tablecontent.rowcontent.linktype_caller_open.REF_KEY}','{tablecontent.rowcontent.linktype_caller_open.REF_LABEL}')" class="mdtb_cell_link">
					<!-- END linktype_caller_open -->
					{tablecontent.rowcontent.CONTENT_CELL}
					<!-- BEGIN linktype_detail_close -->
						</a>
					<!-- END linktype_detail_close -->
					<!-- BEGIN linktype_caller_close -->
						</a>
					<!-- END linktype_caller_close -->
				</td>
			<!-- END rowcontent -->
		</tr>
	<!-- END tablecontent -->
</tbody>
</table>
{TABLE_END}