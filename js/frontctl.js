var frontCtl = {
	max_longueur_textarea:3000,
	init:function()
	{
		if($("#tabs").length)
		{
			$( "#tabs" ).tabs();
		}
		$(document).on("valideravis",frontCtl.validerAvis);
		$(".blocfiltre").each(function(){
			var selectel=$(this).find("select");
			var ph=$(this).find("label").attr("placeholder");
			if(!$(this).hasClass("matcher"))
			{
				$(selectel).select2({placeholder:ph,width: 'resolve' });
			}
			else
			{
				$(selectel).select2({placeholder:ph,width: 'resolve',
					matcher: function(params,data) {
						if(typeof(params.term)=="undefined" /*  && $(data.element).closest("div").attr("data")=="ss_ut" */)
						{
							var arrListeSSUT=new Array();
							if($(data.element).closest("div").attr("data")=="ssbv")
							{
								$("#liste_ss_ut option:selected").each(function(){arrListeSSUT.push($(this).val());});
							}
							if(arrListeSSUT.length==0) return data;
							var found=false;
							for(var i=0;i<arrListeSSUT.length;i++)
							{
								for(var j=0;j<donneesListeSSBV.length;j++)
								{
									if(data.element.value==donneesListeSSBV[j].code_ssbv && donneesListeSSBV[j].code_ss_ut==arrListeSSUT[i])
									{
										return data;
									}
								}
							}
							return null;
						}
						else
						{
							var arrListeSSUT=new Array();
							if($(data.element).closest("div").attr("data")=="ssbv")
							{
								$("#liste_ss_ut option:selected").each(function(){arrListeSSUT.push($(this).val());});
							}
							if(arrListeSSUT.length==0) return data;
							var found=false;
							for(var i=0;i<arrListeSSUT.length;i++)
							{
								for(var j=0;j<donneesListeSSBV.length;j++)
								{
									if(data.element.value==donneesListeSSBV[j].code_ssbv && donneesListeSSBV[j].code_ss_ut==arrListeSSUT[i])
									{
										found=true;
										break;
									}
								}
								if(found)
								{
									break;
								}
							}
							if(!found) return null;
						}
						var curDataStr = data.text.toLowerCase();
						var curTerm=params.term.toLowerCase();
						if (curDataStr.indexOf(curTerm) > -1) {
							var modifiedData = $.extend({}, data, true);
							//modifiedData.text += ' (matched)';

							// You can return modified objects from here
							// This includes matching the `children` how you want in nested data sets
							return modifiedData;
						  }
						return null;
					}});
				/*
				$(selectel).on("change",function(){
					console.log("liste "+$(this).attr("id")+" a changé");
					if(typeof($(this).closest("div.blocfiltre").attr("data-lien"))!=="undefined" && $(this).closest("div.blocfiltre").attr("data-lien")!="")
					{
						var dataLien=$(this).closest("div.blocfiltre").attr("data-lien");
						if($("#"+dataLien).length)
						{
							frontCtl.renduListe($("#"+dataLien));
						}
					}
				});
				frontCtl.renduListe(selectel);
				*/
			}
		});
		
		/*
		 * Initialisation manuelles des listes ssbv et ssut
		 *  var sampleArray = [{id:0,text:'enhancement'}, {id:1,text:'bug'}
                       ,{id:2,text:'duplicate'},{id:3,text:'invalid'}
                       ,{id:4,text:'wontfix'}];
			donneesListeSS_UT=[{"id_ss_ut":"1","code_ss_ut":"FRD_COCA","libelle_ss_ut":"C\u00f4tiers C\u00f4te d'Azur"}];
		var donneesListeSSBV=[{"id_ssbv":"1","code_ssbv":"AG_14_01","libelle_ssbv":"Ard\u00e8che","code_ss_ut":"FRD_GARD"}];
			$("#e10").select2({ data: sampleArray });
		 */
		
		
		$("a.fancybox").fancybox();
		/*
		$('.formAvisMassedeau textarea').keypress(function(e) {
			$(this).closest(".formAvisMassedeau").find("span.nbchars").first().text(this.value.length);
		});
		$('.formAvisMassedeau textarea').keypress(function(e) {
			$(this).closest(".formAvisMassedeau").find("span.nbchars").first().text(this.value.length);
			if (e.which < 0x20 && e.which!=0x13) {
				// e.which < 0x20, then it's not a printable character
				// e.which === 0 - Not a character
				return;     // Do nothing
			}
			if (this.value.length >= frontCtl.max_longueur_textarea) {
				e.preventDefault();
			} else if (this.value.length > frontCtl.max_longueur_textarea) {
				// Maximum exceeded
				this.value = this.value.substring(0, frontCtl.max_longueur_textarea);
			}
		});
		*/
		
	},
	triggerHasPJ:function(avisId)
	{
		if($(avisId+" button.boutonSupprimerPJ").length) $(avisId+" button.boutonSupprimerPJ").show();
		///$(document).trigger("sauvegardeok",avisId);
	},
	triggerEventsValidation:function(avisId)
	{
		///$(document).trigger("sauvegardeok",avisId);
		$(document).trigger("valideravis",avisId);
	},
	triggerEventsSauvegarde:function(avisId)
	{
		///$(document).trigger("sauvegardeok",avisId);
		console.log("Trigger : sauvegardeOk avec avis "+avisId);
		$(avisId).addClass("sauvegardeok");
		$(avisId+" label.sauvegarde").hide();
		$(avisId+" label.sauvegardeok").show();
		var divAvisId=$(avisId).closest("div.formAvis").attr("id");
		$("a[href='#"+divAvisId+"']").closest("tr.ligneavis").addClass("edition");
		//$(document).trigger("sauvegardeok",avisId);
	},
	triggerEventsSauvegardeErreur:function(formAvisId)
	{
		//$('#".$this->params["id_form_avis"]." label.sauvegardeerreur', window.parent.document).show();
		///$(document).trigger("sauvegardeok",avisId);
		$(formAvisId+" label.sauvegarde").hide();
		$(formAvisId+" label.sauvegardeerreur").show();
	},
	triggerEventsErreurSuppressionPJ:function()
	{
		alert("Erreur lors de la suppression de la pièce jointe");
	},
	triggerEventsPJSupprimee:function(formAvisId)
	{
		$(formAvisId+" span.document").remove();
	},
	validerAvis:function(event,formAvisId)
	{
		console.log("Trigger : validerAvis avec avis "+avisId);
		$(formAvisId+" label.sauvegarde").hide();
		$(formAvisId+" label.validationok").show();
		$(formAvisId+" input.boutonaction").remove();
		$(formAvisId+" input").prop('disabled', true);
		$(formAvisId+" select").prop('disabled', true);
		$(formAvisId+" textarea").prop('disabled', true);
		var avisId=$(formAvisId).closest("div.formAvis").attr("id");
		$("a[href='#"+avisId+"']").closest("tr.ligneavis").addClass("valide");
		setTimeout(function(){ $.fancybox.close(); },400);
		//$action="$('#".$this->params["id_form_avis"]." label.validationok', window.parent.document).show();";
		//$action.="$('#".$this->params["id_form_avis"]." input.boutonaction', window.parent.document).remove();";
		//$action.="$('#".$this->params["id_form_avis"]." input,#".$this->params["id_form_avis"]." textarea,#".$this->params["id_form_avis"]." select,', window.parent.document).disable();";
	},	
	renduListe:function(el)
	{
		var donneesListe=new Array();
		var ph=$(el).prev("label").attr("placeholder");
		if($(el).closest("div.blocfiltre").attr("data")=="ssbv") donneesListe=frontCtl.prepareDonneesSSBV();
		if($(el).closest("div.blocfiltre").attr("data")=="ss_ut") donneesListe=frontCtl.prepareDonneesSSUT();
		$(el).select2({placeholder:ph,width: 'resolve',data: donneesListe });
	},
	
	prepareDonneesSSBV:function()
	{
		var arrListeSSUT=new Array();
		$("#liste_ss_ut option:selected").each(function(){arrListeSSUT.push($(this).val());});
		var arrSSBV=new Array();
		for(var i=0;i<donneesListeSSBV.length;i++)
		{
			var objSSBV=donneesListeSSBV[i];
			if(arrListeSSUT.length==0 || arrListeSSUT.indexOf(objSSBV.code_ss_ut)>=0)
			{
				var curSSBV={"id":objSSBV.code_ssbv,text:objSSBV.code_ssbv+" - "+objSSBV.libelle_ssbv};
				arrSSBV.push(curSSBV);
			}
		}
		return arrSSBV;
	},

	prepareDonneesSSUT:function()
	{
		var arrSSUT=new Array();
		
		for(var i=0;i<donneesListeSS_UT.length;i++)
		{
			var objSSUT=donneesListeSS_UT[i];
			var curSSUT={"id":objSSUT.code_ss_ut,text:objSSUT.code_ss_ut+" - "+objSSUT.libelle_ss_ut};
			arrSSUT.push(curSSUT);
		}
		return arrSSUT;
	},
	
	checkLengthOnChange:function(el)
	{
		$(el).closest(".formAvisMassedeau").find("span.nbchars").first().text(el.value.length);
		if (el.value.length >= frontCtl.max_longueur_textarea) {
			$(el).closest(".formAvisMassedeau").find("div.compteurschars").first().addClass("max");
		}
		else
		{
			$(el).closest(".formAvisMassedeau").find("div.compteurschars").first().removeClass("max");
		}
	},
	checkLengthOnKeyPress:function(el)
	{
		$(el).closest(".formAvisMassedeau").find("span.nbchars").first().text(el.value.length);
		
		if(window.event) { // IE                    
			var keynum = window.event.keyCode;
			var curevent=window.event;
		  } else if(event.which){ // Netscape/Firefox/Opera                   
			var keynum = event.which;
			var curevent=event;
		  }
		
		if (keynum < 0x20 && keynum!=0x13 && keynum!=0x09) {
			if (el.value.length >= frontCtl.max_longueur_textarea) {
				$(el).closest(".formAvisMassedeau").find("div.compteurschars").first().addClass("max");
			}
			else
			{
				$(el).closest(".formAvisMassedeau").find("div.compteurschars").first().removeClass("max");
			}
			// e.which < 0x20, then it's not a printable character
			// e.which === 0 - Not a character
			return;     // Do nothing
		}
		if (el.value.length == frontCtl.max_longueur_textarea) {
			$(el).closest(".formAvisMassedeau").find("div.compteurschars").first().addClass("max");
			curevent.preventDefault();
		} else if (el.value.length > frontCtl.max_longueur_textarea) {
			// Maximum exceeded
			$(el).closest(".formAvisMassedeau").find("div.compteurschars").first().addClass("max");
			el.value = el.value.substring(0, frontCtl.max_longueur_textarea);
		}
		else
		{
			$(el).closest(".formAvisMassedeau").find("div.compteurschars").first().removeClass("max");
		}
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
		$(el).find("label.sauvegarde").hide();
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

