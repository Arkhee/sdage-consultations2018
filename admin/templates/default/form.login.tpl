<div id="login_form">
	<div id="login_messages">
		<!-- BEGIN TABLE_MESSAGES -->
		<div id="login_message">
			{TABLE_MESSAGES.LOGIN_MESSAGES}
		</div>
		<!-- END TABLE_MESSAGES -->
	</div>
	<form action="{FORM_SCRIPT}" method="POST" name="frmLogin">
		<ul>
			<li><h3>{LABEL_INTRO}</h3></li>
		</ul>
		<ul>
			<li class="loginLabel">{LABEL_LOGIN}&nbsp;:&nbsp;</li>
			<li><input type="text" name="{FIELD_NAME_LOGIN}" id="{FIELD_NAME_LOGIN}" value=""></li>
		</ul>
		<ul>
			<li class="loginLabel">{LABEL_PASSWORD}&nbsp;:&nbsp;</li>
			<li><input type="password" name="{FIELD_NAME_PASSWORD}" id="{FIELD_NAME_PASSWORD}" value=""></li>
		</ul>
		<ul>
			<li class="loginLabel">{LABEL_SUBMIT}</li>
			<li><input type="submit" name="{FIELD_NAME_SUBMIT}" id="{FIELD_NAME_SUBMIT}" value="{FIELD_VALUE_SUBMIT}"></li>
		</ul>
		<input type="hidden" name="_tablename" id="_tablename" value="{TABLE_NAME}" /> 
	</form>
</div>
	<div id="cadreDivMailOublie">
<a href="javascript:" id='lienMailOublie' onclick="$('#divMailOublie').toggle();" >{LABEL_MAIL_OUBLIE}</a>
<div id="divMailOublie" style="display:none;">
	<form action="{FORM_SCRIPT}" method="POST" name="frmLogin">
		<input type="hidden" name="_tablename" id="_tablename" value="{TABLE_NAME}" /> 
		<input type="hidden" name="_action" value="password" />
		{LABEL_EXPLICATION_MAIL_OUBLIE}
		<input type="text" name="{FIELD_NAME_MAIL}"  value="">
		<input type="submit" name="{FIELD_NAME_SUBMIT_MAIL}" id="{FIELD_NAME_SUBMIT_MAIL}" value="{FIELD_VALUE_SUBMIT_MAIL}">
	</form>
	<a href="javascript:" onclick="$('#divMailOublie').toggle()" >{LABEL_MAIL_OUBLIE_FERMER}</a>
</div>
	</div>
<script type='text/javascript'>
	$(document).ready(function(){ $('#{FIELD_NAME_LOGIN}').focus(); })
</script>