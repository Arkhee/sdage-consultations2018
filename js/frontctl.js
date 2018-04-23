var frontCtl = {
	init:function()
	{
		if($("#tabs").length)
		{
			$( "#tabs" ).tabs();
		}
		$(".blocfiltre").each(function(){
			var ph=$(this).find("label").attr("placeholder");
			var selectel=$(this).find("select");
			$(selectel).select2({placeholder:ph,width: 'resolve' });
		});
		$("a.fancybox").fancybox();
	},
	testChampVide:function(fieldId,fieldName)
	{
		if($("#"+fieldId).val()=="")
		{
			$("#"+fieldId).addClass("erreur");
			return "Le champ "+fieldName+" est vide<br />";
		}
		return "";
	},
	testFormulaireInscription:function(el)
	{
		$(el).find(".erreur").each(function(){$(this).removeClass("erreur");});
		var msgErreur="";
		$("#msg_erreur_inscription").hide();
		if($("#password1").val() != $("#password2").val())
		{
			$("#password1").addClass("erreur");
			$("#password2").addClass("erreur");
			msgErreur+="Les deux mots de passe saisis ne sont pas identiques<br />";
		}
		if(!frontCtl.validateEmail($("#user_email").val()))
		{
			$("#user_email").addClass("erreur");
			msgErreur+="Email incorrect<br />";
		}
		if($(el).attr("id")!="formMiseAJour") msgErreur+=frontCtl.testChampVide("password1","Mot de passe");
		msgErreur+=frontCtl.testChampVide("user_name","Nom");
		msgErreur+=frontCtl.testChampVide("user_firstname","Prénom");
		msgErreur+=frontCtl.testChampVide("type_structure","Type de structure");
		msgErreur+=frontCtl.testChampVide("user_nomstructure","Nom de la structure");
		if(msgErreur!="")
		{
			$("#msg_erreur_inscription").html(msgErreur);
			$("#msg_erreur_inscription").show();
			return false;
		}
		return true;
	},
	validateEmail:function(email) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String(email).toLowerCase());
	},
	testFormulaireAvis:function(el)
	{
		var ok=true;
		var message="";
		var justification="";
		$(el).find("label").removeClass("erreur");
		if(!$(el).find("#justification").length) message+=("Champ 'Justification' manquant ...");
		if(!$(el).find("#impact_estime").length) message+=("Champ 'Impact' manquant  ...");
		if(!$(el).find("#pression_cause_du_risque").length) message=("Champ 'Pression' manquant  ...");
		
		if($(el).find("#impact_estime").val()=="")
		{
			message+=("Champ 'Impact' vide  ...");
			$(el).find("#impact_estime").prev("label").addClass("erreur");
		}
		if($(el).find("#pression_cause_du_risque").val()=="")
		{
			message+=("Champ 'Pression cause du risque' vide  ...");
			$(el).find("#pression_cause_du_risque").prev("label").addClass("erreur");
		}
		justification=$(el).find("#justification").val();
		if(justification.trim()=="")
		{
			message+=("Champ 'Justification' vide  ...");
			$(el).find("#justification").prev("label").addClass("erreur");
		}
		if(message!="") ok=false;
		if(!ok)
		{
			alert(message);
		}
		return ok;
	},
	toggleLigneResultat:function(el)
	{
		$(el).find(".icon").toggle();
		$(el).closest("tr").next().toggle();
		if($(el).closest("tr").find(".icon-on").css("display")!="none")
		{
			$(el).closest("tr").addClass("ligne-active");
		}
		else
		{
			$(el).closest("tr").removeClass("ligne-active");
		}
	}
};
$(document).ready(function(){frontCtl.init();});

