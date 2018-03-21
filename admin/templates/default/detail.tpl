<table class="mdtb_form_table" cellspacing="0" cellpadding="0" border="0">
	<!-- BEGIN formfields -->
	<tr>
		<td class="lightbordercolor fieldcolumn" {formfields.FIELD_NAME_STYLE}>
			{formfields.FIELD_NAME}
		</td>
		<td class="mdtb_form_fieldvalue"  {formfields.FIELD_CONTENT_STYLE} >
			{formfields.FIELD_CONTENT}
		</td>
	</tr>
	<!-- END subject -->
	<tr {ACTION_SPECIAL_MORE}>
		<td class="lightbordercolor fieldcolumn">
			{ACTION_SPECIAL_LABEL}&nbsp;
		</td>
		<td class="mdtb_form_fieldvalue">
			{ACTION_SPECIAL}&nbsp;
		</td>
	</tr>
	<tr>
		<td class="lightbordercolor fieldcolumn">
			&nbsp;
		</td>
		<td class="mdtb_form_fieldvalue">
			<a href="{ACTION_EDIT_LINK}" {ACTION_EDIT_MORE} class="mdtb_cell_link stylebutton">{ACTION_EDIT_LABEL}</a>
			<a href="{ACTION_DEL_LINK}" {ACTION_DEL_MORE} class="mdtb_cell_link stylebutton">{ACTION_DEL_LABEL}</a>
			<a href="{ACTION_BACK_LINK}" {ACTION_BACK_MORE} class="mdtb_cell_link stylebutton">{ACTION_BACK_LABEL}</a>
		</td>
	</tr>
</table>