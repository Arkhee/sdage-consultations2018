				<table class='listepressions'>
					<tr>
						<th>Pression</th>
						<th align='center'>Impact 2016</th>
						<th align='center'>Valeur forcée impact SDAGE 2016</th>
						<th align='center'>Impact 2019</th>
						<th align='center'>RNAOE 2021</th>
						<th align='center'>Pression origine du risque 2021</th>
						<th align='center'>RNAOE 2027</th>
						<th align='center'>Pression origine du risque 2027</th>
						<th align='center'>&nbsp;</th>
					</tr>
					<!-- BEGIN pressions -->
					<tr>
						<td>{pressions.libelle_pression}</td>
						<td align='center'>{pressions.impact_2016}</td>
						<td align='center'>{pressions.impact_valeur_forcee}</td>
						<td align='center'>{pressions.impact_2019}</td>
						<td align='center'>{pressions.rnaoe_2021}</td>
						<td align='center'>{pressions.pression_origine_2021}</td>
						<td align='center'>{pressions.rnaoe_2027}</td>
						<td align='center'>{pressions.pression_origine_2027}</td>
						<td align='center'><a href='#avis_pression_{pressions.id_pression}' class='fancybox'>
								<i class="fas fa-plus-circle"></i>
							</a>
							<div  style='display:none;'>
								<div class='formAvis' id='avis_pression_{pressions.id_pression}'>
									<table  class="detailPression">
										<tr><td colspan="2">
												<table class="detailMdoEtPression">
													<tr><td>Masse d'eau</td><td>{pressions.code_me} - {pressions.libelle_me} - {pressions.categorie_me}</td></tr>
													<tr><td>Sous-bassin versant</td><td>{pressions.code_ssbv}</td></tr>
													<tr><td>Pression</td><td>{pressions.libelle_pression}</td></tr>
												</table>
										</td></tr>
										<tr>
											<td valign="top">
												<table class="detailGroupe">
													<tr><td>Classe d'impact SDAGE 2016</td><td>{pressions.impact_2016}</td></tr>
													<tr><td>Valeur forcée impact SDAGE 2016</td><td>{pressions.impact_valeur_forcee}</td></tr>
													<tr><td>RNAOE 2021</td><td>{pressions.rnaoe_2021}</td></tr>
													<tr><td>Pression à l'origine du risque 2021</td><td>{pressions.pression_origine_2021}</td></tr>
												</table>
											</td>
											<td valign="top">
												<table class="detailGroupe">
													<tr><td>Classe d'impact EdL2019</td><td>{pressions.impact_2019}</td></tr>
													<tr><td>RNAOE 2027</td><td>{pressions.rnaoe_2027}</td></tr>
													<tr><td>Pression à l'origine du risque 2027</td><td>{pressions.pression_origine_2027}</td></tr>
												</table>
											</td>
										</tr>
									</table>
									<div class="formulaire_avis">
										<h3>Donnez votre avis sur l'état de ce cours d'eau</h3>
										<form method="post" action="#" 
											  id="formAvisPression_{pressions.id_pression}_{pressions.code_me}" 
											  enctype="multipart/form-data" target="targetSauvegarde"
											  class="formAvisMassedeau">
											<input type='hidden' name="section" value="avis"/>
											<input type='hidden' name="id_form_avis" value="formAvisPression_{pressions.id_pression}_{pressions.code_me}"/>
											<label for="impact_estime">Impact estimé</label>
											<select name="impact_estime" id="impact_estime">
												<option value="1">1 - Impact faible</option>
												<option value="2">2 - Impact moyen</option>
												<option value="3">3 - Impact fort</option>
											</select><br />
											
											<label for="justification">Justification</label>
											<textarea name="justification" id="justification" style="width:100%; height:250px"></textarea><br />
											
											<label for="pression_cause_du_risque">Pression cause du risque</label>
											<select name="pression_cause_du_risque" id="pression_cause_du_risque">
												<option value="O">Oui</option>
												<option value="N">Non</option>
											</select><br />
											
											<label for="documents">Documents complémentaires</label>
											<input type="file" name="documents" id="documents">
											
											<div class="blocValidationFormulaire">
												<label class="sauvegarde sauvegardeok">Votre avis a bien été sauvegardé</label>
												<label class="sauvegarde sauvegardeerreur">Une erreur s'est produite à l'enregistrement de votre avis</label>
												<label class="sauvegarde validationok">Votre avis a bien été validé</label>
												<input type="submit" name="sauverAvis" id="sauverAvis" value="Sauver mon avis"/>
												<input type="submit" name="validerAvis" id="validerAvis" value="Valider mon avis"/>
											</div>
										</form>
									</div>
								</div>
							</div>
						</td>
					</tr>
					<!-- END pressions -->
					
				</table>
				
