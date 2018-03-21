{FORM_BEGIN}
<table class="mdtb_form_table" cellspacing="0" cellpadding="0" border="0" align="center">
	<!-- BEGIN formfields -->
	<tr>
		<td class="lightbordercolor fieldcolumn" {formfields.FIELD_NAME_STYLE}>
			{formfields.FIELD_NAME}
		</td>
		<td class="mdtb_form_fieldvalue"  {formfields.FIELD_CONTENT_STYLE}>
			{formfields.FIELD_CONTENT}
		</td>
	</tr>
	<!-- END subject -->
	<tr>
		<td class="lightbordercolor">
			{ACTION_SPECIAL_LABEL}&nbsp;
		</td>
		<td class="mdtb_form_fieldvalue">
			{ACTION_SPECIAL}&nbsp;
		</td>
	</tr>
	<tr>
		<td class="lightbordercolor">&nbsp;</td>
		<td class="mdtb_form_fieldvalue">
			<!-- BEGIN formhidden -->
			<input type="hidden" name="{formhidden.HIDDEN_FIELD_NAME}" id="{formhidden.HIDDEN_FIELD_NAME}" value="{formhidden.HIDDEN_FIELD_VALUE}" />
			<!-- END formhidden -->
			{FORM_ACTIONS}
		</td>
	</tr>
</table>

{FORM_END}