				<table class='listepressions'>
					<tr>
						<th>Pression</th>
						<th align='center'>Impact SDAGE 2016</th>
						<th align='center' class='valeur_forcee'>Valeur forcée impact SDAGE 2016</th>
						<th align='center'>Pression origine du risque 2021</th>
						<th class="enteteimportant" align='center'>Impact EDL 2019</th>
						<th class="enteteimportant" align='center'>Pression origine du risque 2027</th>
						<th align='center'>&nbsp;</th>
						<th align='center' class='avis'>Avis</th>
					</tr>
					<!-- BEGIN pressions -->
					<tr class='ligneavis {pressions.class_avis_valide}'>
						<td>{pressions.libelle_pression}</td>
						<td align='center'>{pressions.impact_2016}</td>
						<td align='center' class='valeur_forcee'>{pressions.impact_valeur_forcee}</td>
						<td align='center'>{pressions.pression_origine_2021}</td>
						<td align='center'>{pressions.impact_2019}</td>
						<td align='center'>{pressions.pression_origine_2027}</td>
						<td align='center'>
							<span class="icone_avis">
								<a class='avisacreer fancybox' title="Vous n'avez pas encore donné votre avis sur cette pression" href='#avis_pression_{pressions.id_pression}_{pressions.id_massedeau}'>
									<i class="fas fa-plus-circle"></i>
								</a>
								<a class='avisedition fancybox' title="Vous avez rédigé un avis sur cette pression mais il n'est pas encore validé" href='#avis_pression_{pressions.id_pression}_{pressions.id_massedeau}'>
									<i class="fas fa-edit"></i>
								</a>
								<a class='avisvalide fancybox' title="Vous avez validé l'avis donné sur cette pression" href='#avis_pression_{pressions.id_pression}_{pressions.id_massedeau}'>
									<i class="fas fa-check-circle"></i>
								</a>
							</span>
							<div style='display:none;'>
								<div class='formAvis' id='avis_pression_{pressions.id_pression}_{pressions.id_massedeau}'>
									<table class="detailPression">
										<tr><td colspan="2">
												<table class="detailMdoEtPression">
													<tr><td>Masse d'eau</td><td>{pressions.code_me} - {pressions.libelle_me} - {pressions.categorie_me}</td></tr>
													<tr><td>Sous-bassin</td><td>{pressions.code_ssbv}&nbsp;-&nbsp;{pressions.libelle_ssbv}</td></tr>
													<tr><td>Pression</td><td>{pressions.libelle_pression}</td></tr>
												</table>
										</td></tr>
										<tr>
											<td valign="top">
												<table class="detailGroupe">
													<tr><td>Classe d'impact 2016</td><td>{pressions.impact_2016}</td></tr>
													<tr class='valeur_forcee'><td>Valeur forcée impact SDAGE 2016</td><td>{pressions.impact_valeur_forcee}</td></tr>
													<tr><td>RNABE 2021</td><td>{pressions.rnaoe_2021}</td></tr>
													<tr><td>Pression à l'origine du risque 2021</td><td>{pressions.pression_origine_2021}</td></tr>
												</table>
											</td>
											<td valign="top">
												<table class="detailGroupe">
													<tr><td class="enteteimportant">Classe d'impact 2019</td><td>{pressions.impact_2019}</td></tr>
													<tr><td class="enteteimportant">RNABE 2027</td><td>{pressions.rnaoe_2027}</td></tr>
													<tr><td class="enteteimportant">Pression à l'origine du risque 2027</td><td>{pressions.pression_origine_2027}</td></tr>
												</table>
											</td>
										</tr>
									</table>
									<div class="formulaire_avis {pressions.avis_valide}">
										<div class='avis_lecture'>
											<h3>Donnez votre avis sur le niveau d'impact estimé - Validé</h3>
											<label for="pression_cause_du_risque">Pression cause du risque</label>
											<p>{pressions.pression_cause_du_risque}</p>
											<label for="impact_estime">Impact estimé</label>
											<p>{pressions.impact_estime}</p>
											<label for="justification">Justification</label>
											<p>{pressions.justification}</p>
											<label for="documents">Documents joints</label>
											<p>{pressions.lien_documents}</p>
										</div>
										<div class='avis_ecriture'>
											<h3>Donnez votre avis sur le niveau d'impact estimé</h3>
											<div class='notemethode'>{MESSAGE_SAISIE_NOTE_METHODE}</div>
											<form method="post" action="#" 
												  onsubmit="return frontCtl.testFormulaireAvis(this);"
												  id="formAvisPression_{pressions.id_pression}_{pressions.id_massedeau}" 
												  enctype="multipart/form-data" target="targetSauvegarde"
												  class="formAvisMassedeau">
												<input type='hidden' name="section" value="avis"/>
												<input type='hidden' name="id_pression" value="{pressions.id_pression}"/>
												<input type='hidden' name="id_massedeau" value="{pressions.id_massedeau}"/>
												<input type='hidden' name="id_form_avis" value="formAvisPression_{pressions.id_pression}_{pressions.id_massedeau}"/>

												<label for="pression_cause_du_risque">Pression cause du risque</label>
												{pressions.CMB_PRESSION_CAUSE_DU_RISQUE}<br />

												<label for="impact_estime">Impact estimé</label>
												{pressions.CMB_IMPACT_ESTIME}<br />

												<label for="justification">Justification</label>
												<textarea onchange="frontCtl.checkLengthOnChange(this);" onkeyup="frontCtl.checkLengthOnChange(this);" onkeydown="frontCtl.checkLengthOnKeyPress(this);" name="justification" id="justification" style="width:100%; height:250px">{pressions.justification}</textarea><br />
												<div class="compteurschars"><span class="longueur_max">Longueur maximale atteinte</span>&nbsp;<span class="nbchars">{pressions.justification_length}</span> / 3000</div>
												<label for="documents">Documents complémentaires (un seul fichier autorisé, formats : pdf ou zip)</label>
												<span class='{pressions.class_documents}'>
													<span class='liendocument'>
														{pressions.lien_documents}
													</span>
													<button type='submit' name='supprimerPJ' class='boutonSupprimerPJ' value='Supprimer le document'>
														<i class="fa fa-trash" aria-hidden="true"></i>
													</button>
												</span>
												<input type="file" name="documents" id="documents">

												<div class="blocValidationFormulaire">
													<label class="sauvegarde sauvegardeok">Votre avis a bien été sauvegardé</label>
													<label class="sauvegarde sauvegardeerreur">Une erreur s'est produite à l'enregistrement de votre avis</label>
													<label class="sauvegarde validationok">Votre avis a bien été validé</label>
													<input type="submit" name="sauverAvis" class='boutonaction' id="sauverAvis" value="Sauvegarder mon avis"/>
													<input type="submit" name="validerAvis" class='boutonaction' id="validerAvis" value="Valider mon avis" onclick='return confirm("Etes-vous sûr de valider cet avis ? La validation est définitive et ne peut être annulée")'/>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
						</td>
						<td class='avis'>{pressions.nbavis}</td>
					</tr>
					<!-- END pressions -->
					
				</table>
				
