<tr>
	<td>{libelle_pression}</td>
	<td align='center'>{impact_2016}</td>
	<td align='center' class='valeur_forcee'>{impact_valeur_forcee}</td>
	<td align='center'>{pression_origine_2021}</td>
	<td align='center'>{impact_2019}</td>
	<td align='center'>{pression_origine_2027}</td>
	<td align='center'><span class="icone_avis"><a title="{tooltip_avis}" href='#avis_pression_{id_pression}_{id_massedeau}' class='fancybox'>
			<i class="fas {icone_avis}"></i>
		</a></span>
		<div style='display:none;'>
			<div class='formAvis' id='avis_pression_{id_pression}_{id_massedeau}'>
				<table class="detailPression">
					<tr><td colspan="2">
							<table class="detailMdoEtPression">
								<tr><td>Masse d'eau</td><td>{code_me} - {libelle_me} - {categorie_me}</td></tr>
								<tr><td>Sous-bassin</td><td>{code_ssbv}&nbsp;-&nbsp;{libelle_ssbv}</td></tr>
								<tr><td>Pression</td><td>{libelle_pression}</td></tr>
							</table>
					</td></tr>
					<tr>
						<td valign="top">
							<table class="detailGroupe">
								<tr><td>Classe d'impact 2016</td><td>{impact_2016}</td></tr>
								<tr class='valeur_forcee'><td>Valeur forcée impact SDAGE 2016</td><td>{impact_valeur_forcee}</td></tr>
								<tr><td>RNABE 2021</td><td>{rnaoe_2021}</td></tr>
								<tr><td>Pression à l'origine du risque 2021</td><td>{pression_origine_2021}</td></tr>
							</table>
						</td>
						<td valign="top">
							<table class="detailGroupe">
								<tr><td class="enteteimportant">Classe d'impact 2019</td><td>{impact_2019}</td></tr>
								<tr><td class="enteteimportant">RNABE 2027</td><td>{rnaoe_2027}</td></tr>
								<tr><td class="enteteimportant">Pression à l'origine du risque 2027</td><td>{pression_origine_2027}</td></tr>
							</table>
						</td>
					</tr>
				</table>
				<div class="formulaire_avis {avis_valide}">
					<div class='avis_lecture'>
						<h3>Donnez votre avis sur le niveau d'impact estimé - Validé</h3>
						<label for="pression_cause_du_risque">Pression cause du risque</label>
						<p>{pression_cause_du_risque}</p>
						<label for="impact_estime">Impact estimé</label>
						<p>{impact_estime}</p>
						<label for="justification">Justification</label>
						<p>{justification}</p>
						<label for="documents">Documents joints</label>
						<p>{lien_documents}</p>
					</div>
					<div class='avis_ecriture'>
						<h3>Donnez votre avis sur le niveau d'impact estimé</h3>
						<div class='notemethode'>{MESSAGE_SAISIE_NOTE_METHODE}</div>
						<form method="post" action="#" 
							  onsubmit="return frontCtl.testFormulaireAvis(this);"
							  id="formAvisPression_{id_pression}_{id_massedeau}" 
							  enctype="multipart/form-data" target="targetSauvegarde"
							  class="formAvisMassedeau">
							<input type='hidden' name="section" value="avis"/>
							<input type='hidden' name="id_pression" value="{id_pression}"/>
							<input type='hidden' name="id_massedeau" value="{id_massedeau}"/>
							<input type='hidden' name="id_form_avis" value="formAvisPression_{id_pression}_{id_massedeau}"/>

							<label for="pression_cause_du_risque">Pression cause du risque</label>
							{CMB_PRESSION_CAUSE_DU_RISQUE}<br />

							<label for="impact_estime">Impact estimé</label>
							{CMB_IMPACT_ESTIME}<br />

							<label for="justification">Justification</label>
							<textarea onchange="frontCtl.checkLengthOnChange(this);" onkeyup="frontCtl.checkLengthOnChange(this);" onkeydown="frontCtl.checkLengthOnKeyPress(this);"  name="justification" style="width:100%; height:250px">{justification}</textarea><br />
							<div class="compteurschars"><span class="nbchars">{justification_length}</span> / 3000</div>
							<label for="documents">Documents complémentaires (un seul fichier autorisé, formats : pdf ou zip)</label>
							{lien_documents}
							<input type="file" name="documents"> (taille max. 2Mo)
							<input type="hidden" name="MAX_FILE_SIZE" value="2097152" /> 
							<div class="blocValidationFormulaire">
								<label class="sauvegarde sauvegardeok">Votre avis a bien été sauvegardé</label>
								<label class="sauvegarde sauvegardeerreur">Une erreur s'est produite à l'enregistrement de votre avis</label>
								<label class="sauvegarde validationok">Votre avis a bien été validé</label>
								<input type="submit" name="sauverAvis" class='boutonaction' value="Sauvegarder mon avis"/>
								<input type="submit" name="validerAvis" class='boutonaction' value="Valider mon avis" onclick='return confirm("Etes-vous sûr de valider cet avis ? La validation est définitive et ne peut être annulée")'/>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</td>
	<td class='avis'>{nbavis}</td>
</tr>
