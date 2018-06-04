<style>
	.crea .valeur_forcee { display:none;}
</style>
<div class="{user_rank}">
	<table class="detailPression"  style="width:1000px;">
		<tr><td colspan="2">
				<table class="detailMdoEtPression" style="border:1px solid #666; margin-bottom:5px;width:1000px;">
					<tr><td style="font-weight:bold">Masse d'eau</td><td>{code_me} - {libelle_me} - {categorie_me}</td></tr>
					<tr><td style="font-weight:bold">Sous-bassin</td><td>{code_ssbv}&nbsp;-&nbsp;{libelle_ssbv}</td></tr>
					<tr><td style="font-weight:bold">Pression</td><td>{libelle_pression}</td></tr>
				</table>
		</td></tr>
		<tr>
			<td valign="top" style="width:50%;">
				<table class="detailGroupe" style="border:1px solid #666; margin-bottom:5px;width:100%;">
					<tr><td style="font-weight:bold">Classe d'impact 2016</td><td>{impact_2016}</td></tr>
					<tr><td style="font-weight:bold">RNABE 2021</td><td>{rnaoe_2021}</td></tr>
					<tr><td style="font-weight:bold">Pression à l'origine du risque 2021</td><td>{pression_origine_2021}</td></tr>
				</table>
			</td>
			<td valign="top" style="width:50%;">
				<table class="detailGroupe" style="border:1px solid #666; margin-bottom:5px;width:100%;">
					<tr><td style="font-weight:bold">Classe d'impact 2019</td><td>{impact_2019}</td></tr>
					<tr><td style="font-weight:bold">RNABE 2027</td><td>{rnaoe_2027}</td></tr>
					<tr><td style="font-weight:bold">Pression à l'origine du risque 2027</td><td>{pression_origine_2027}</td></tr>
				</table>
			</td>
		</tr>
	</table>
	<div class="formulaire_avis {avis_valide}" style="width:1000px;">
		<div class='avis_lecture'>
			<h3>Votre avis validé sur le risque estimé</h3>
			<label for="date_de_validation" style="font-weight:bold">Date de validation</label>
			<p>{date_validation}</p>
			<label for="pression_cause_du_risque" style="font-weight:bold">Pression cause du risque</label>
			<p>{pression_cause_du_risque}</p>
			<label for="impact_estime" style="font-weight:bold">Impact estimé</label>
			<p>{impact_estime}</p>
			<label for="justification" style="font-weight:bold">Justification</label>
			<p width="90%">{justification}</p>
			<label for="documents" style="font-weight:bold">Documents joints</label>
			<p>{lien_documents}</p>
		</div>

	</div>
</div>