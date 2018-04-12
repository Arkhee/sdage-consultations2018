var frontCtl = {
	init:function()
	{
		$(".blocfiltre").each(function(){
			var ph=$(this).find("label").attr("placeholder");
			var selectel=$(this).find("select");
			$(selectel).select2({placeholder:ph,width: 'resolve' });
			$("a.fancybox").fancybox();
		});
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

