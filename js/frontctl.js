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

