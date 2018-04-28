<script type="text/javascript">
$(document).ready(function() {  $("#tabs").tabs(); } );
</script>
<h2>Fiche de caractérisation - Masse d'eau souterraine n°{code_me}</h2>
<em><a href="fiches/{code_me}_fic.pdf" target="_blank">Télécharger la fiche au format PDF</a></em>&nbsp; - &nbsp;
<a href="{FORM_PAGE}?section=search&{QUERY_PARAMS}">Retour à la recherche par critères</a>&nbsp; - &nbsp;
<a href="../syntheses/db_mesout.php" style="display:none;">Retour à la recherche cartographique</a>
<br/><br/>
<body>
<h2 class="titreBleu">{libelle_me}</h2>
<div id="tabs" class="tabs_me">
    <ul>
        <li><a href="#tabs-identification">Identification</a></li>
        <li><a href="#tabs-caracteristiques">Caract&eacute;ristiques</a></li>
        <li><a href="#tabs-pressions">Pressions</a></li>
    	<li><a href="#tabs-etatressources">Etat des ressources</a></li>
    	<li><a href="#tabs-autre">Autres informations et Sectorisation</a></li>
    </ul>
	<div  class="tabs_me">
		<div id="tabs-identification">
			<ul>
				<li><strong>Code de la masse d'eau : </strong>{code_me}</li>
				<li><strong>Libell&eacute; de la masse d'eau : </strong>{libelle_me}</li>
				<li><strong>Code entit&eacute;s aquif&egrave;res concern&eacute;es (V1) ou (V2) ou secteurs hydro &agrave; croiser :</strong>
				<blockquote>
					<table width="350" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
						<tr>
							<th scope="col"><div align="center">Code entit&eacute; V1</div></th>
							<th scope="col"><div align="center">Code entit&eacute; V2</div></th>
						</tr>
						<!-- BEGIN liste_entites -->
						<tr>
							<td><div align="center">{liste_entites.code_entite_v1}</div></td>
							<td><div align="center">{liste_entites.code_entite_v2}</div></td>
						</tr>
						<!-- END liste_entites -->
					</table>
				</blockquote>
				</li>
				<li><strong>Catégorie de masse d'eau souterraine :</strong> {type_me}</li>
				<li><strong>Superficie* de l'aide d'extension (km2) :</strong> (* surface estim&eacute;e)
					<blockquote>
						<table width="350" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
							<tr>
								<th scope="col" width="33%"><div align="center">Totale : </div></th>
								<th scope="col" width="33%"><div align="center">A l'affleurement :</div></th>
								<th scope="col" width="33%"><div align="center">Sous couverture : </div></th>
							</tr>
							<tr>
								<td><div align="center">{sup_me_total}</div></td>
								<td><div align="center">{sup_me_affleurement}</div></td>
								<td><p align="center">{sup_me_souscouverture}</p>							</td>
							</tr>
						</table>
					</blockquote>
					<br>
				</li>
				<li>
				<strong>Départements et régions concernés :</strong>
				<blockquote>
				<table width="350" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
							<tr>
								<th scope="col" width="33%"><div align="center">N&deg; D&eacute;partement</div></th>
								<th scope="col" width="33%"><div align="center">D&eacute;partement</div></th>
								<th scope="col" width="33%"><div align="center">R&eacute;gion</div></th>
							</tr>
							<!-- BEGIN liste_departements -->
							<tr>
								<td><div align="center">{liste_departements.n__departement}</div></td>
								<td><div align="center">{liste_departements.nom_departement}</div></td>
								<td><div align="center">{liste_departements.nom__region}</div></td>
							</tr>
							<!-- END liste_departements -->
						</table>
					</blockquote>
				</li>
				<li><strong>District gestionnaire :</strong> {district_gestionnaire}</li>
				<li>
				<div class="checkbox-background-{transfrontieres}">
					<strong>&nbsp;Trans-Fronti&egrave;res :</strong><br>
						<blockquote><table width="400" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
							<tr>
								<td width="22%"><strong>Etat membre :</strong></td>
								<td>{etat_membre}</td>
								<td width="22%"><strong>Autre &eacute;tat :</strong></td>
								<td>{etat_autre}</td>
							</tr>
						</table>
						</blockquote>
				</div>
				</li>
				<li>
				<div class="checkbox-background-{transdistrict}">
					<strong>&nbsp;Trans-districts :</strong><br>
						<blockquote><table width="400" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
							<tr>
								<td align="right" width="45%"><strong>Surface* dans le district (km2) :</strong></td>
								<td>{sup_district}</td>
								</tr>
							<tr>
								<td align="right"><strong>Surface* hors district (km2) :</strong></td>
								<td>{sup_hors_district}</td>
							</tr>
							<tr>
								<td align="right"><div align="right">District :</div></td>
								<td>{district_autre}</td>
							</tr>
							<tr>
								<td colspan="2">* : surface estim&eacute;e</td>

							</tr>
						</table>
						</blockquote>
				</div>
				</li>
				<li>
				<strong>Caractéristiques principales de la masse d'eau souterraine :</strong> 
				{etat_hydraulique}</li>
				<li><strong>Caract&eacute;ristique secondaires de la masse d'eau souterraines :</strong><br>
				<blockquote><table width="400"  border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
					<thead>
							<tr>
								<th><div align="center">Karst</div></th>
								<th><div align="center">Frange litorale avec risque d'intrusion saline</div></th>
								<th><div align="center">Regroupement d'entit&eacute;s disjointes</div></th>
								<th><div align="center">Pr&eacute;l&egrave;vements AEP sup&eacute;rieurs &agrave; 10m3/j </div></th>
							</tr>
					</thead>
						<tr>
							<td><div align="center"><img src="images/check-{karst}.png" /></div></td>
							<td><div align="center"><img src="images/check-{intrusion_saline}.png" /></div></td>
							<td><div align="center"><img src="images/check-{entites_disjointes}.png" /></div></td>
							<td><div align="center"><img src="images/check-{aep_10m3_j}.png" /></div></td>
						</tr>
					</table>
					</blockquote>
</li>
			</ul>			
		</div>
	</div>
    <div id="tabs-caracteristiques">
        <h1 class="titreBleu">2. DESCRIPTION DE LA MASSE D'EAU SOUTERRAINE<br /><br />CARACTERISTIQUES INTRINSEQUES</h1>
        <h2>2.1. DESCRIPTION DU SOUS-SOL</h2>
        <h3>2.1.1 DESCRIPTION DE LA ZONE SATUREE</h3>
        <h4>2.1.1.1 Limites g&eacute;ographiques de la masse d'eau</h4>
        <p>{contexte_geographique}</p>
        <h4>2.1.1.2 Caract&eacute;ristiques g&eacute;ologiques et g&eacute;om&egrave;triques des r&eacute;servoirs souterrains et sectorisation &eacute;ventuelle</h4>
        <p>{lithologie_structure}</p>
        <p>Lithologie dominante de la masse d'eau : {lithologie__dominante}</p>
        <h4>2.1.1.3 Caract&eacute;ristiques g&eacute;om&eacute;triques et hydrodynamiques des limites de la masse d'eau</h4>
        <p>{caract__geometriques__et__hydrodynamiques}</p>
        <h3>2.1.2 DESCRIPTION DES ECOULEMENTS</h3>
        <h4>2.1.2.1 Recharges naturelles, aire d'alimentation et exutoires</h4>
        <p>{commentaires_ba}</p>
        <p><strong>Types de recharges :</strong>			</p>
        <table width="500" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td align="right"><img src="images/check-{recharge__pluviale}.png" alt="recharge pluviale" /></td>
				<td align="left">Pluviale</td>
				<td align="right"><img src="images/check-{recharge__pertes}.png" alt="pertes par drainage" /></td>
				<td align="left">Pertes</td>
				<td align="right"><img src="images/check-{recharge__drainance}.png" alt="recharge par drainance" /></td>
				<td align="left">Drainance</td>
				<td align="right"><img src="images/check-{recharge__cours__d_eau}.png" alt="recharge cours d'eau" /></td>
				<td align="left">Cours d'eau</td>
			</tr>
		</table>
        <h4>2.1.2.2 Etat(s) hydraulique(s) et type(s) d'&eacute;coulement(s)</h4>
        <p>{etat__hydraulique}</p>
        <h2>2.2 DESCRIPTION DU SOL</h2>
        <p>{description_du_sol}</p>
        <h2>2.3 CONNECTIONS AVEC LES COURS D'EAU ET LES ZONES HUMIDES</h2>
        <table width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td width="50%" valign="top"><p><strong>Commentaire cours d'eau en relation avec la masse d'eau souterraine :</strong></p>
				<p>{rivieres__alimentees}</p></td>
				<td width="50%" valign="top"><p><strong>Masses d'eau superficielles en relation avec la masse d'eau souterraine :</strong></p>
					<blockquote>
						<table style="width:250px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
							<tr>
								<th scope="col"><div align="center">Code ME Euro</div></th>
								<th scope="col"><div align="center">N&deg; ME</div></th>
							</tr>
							<!-- BEGIN liste_mdosup -->
							<tr>
								<td><div align="center">{liste_mdosup.code_me_euro}</div></td>
								<td><div align="center">{liste_mdosup.id_mdo}</div></td>
							</tr>
							<!-- END liste_mdosup -->
						</table>
					</blockquote>
					<p><strong>Qualit&eacute; info cours d'eau :</strong> {qual_info_riv_alim}</p>
				<p><strong>Source :</strong> {source_info_riv_alim}</p></td>
			</tr>
			<tr>
				<td width="50%" valign="top"><p><strong>Commentaire plans d'eau en relation avec la masse d'eau souterraine :</strong></p>
				<p>{plans__eau__alimentes}</p></td>
				<td width="50%" valign="top"><p><strong>Plan d'eau en relation avec la masse d'eau souterraine :</strong></p>
				<blockquote>
						<table style="width:250px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table">
							<tr>
								<th scope="col"><div align="center">Code Euro</div></th>
								<th scope="col"><div align="center">N&deg; PdE</div></th>
							</tr>
							<!-- BEGIN liste_plandeau -->
							<tr>
								<td><div align="center">{liste_plandeau.code_me_euro}</div></td>
								<td><div align="center">{liste_plandeau.code__pe}</div></td>
							</tr>
							<!-- END liste_plandeau -->
						</table>
					</blockquote>
					<p><strong>Qualit&eacute; info plans d'eau : </strong>{qual_info_pe_alim} </p>
				<p><strong>Source :</strong> {source_info_pe_alim}</p></td>
			</tr>
			<tr>
				<td valign="top"><p><strong>Commentaire zones humides en relation avec la masse d'eau souterraine :</strong></p>
				<p>{zones_humides_alimentees}</p></td>
				<td valign="top"><p><strong>Qualit&eacute; info zones humides : </strong>{qual_info_zh_alim}</p>
				<p><strong>Source :</strong> {source_info_zh_alim}</p></td>
			</tr>
		</table>
        <p><strong>Liste des principales sources aliment&eacute;es :</strong></p>
        <p>{sources_alimentees}</p>
        <h2>2.4 ETAT DES CONNAISSANCES SUR LES CARACTERISTIQUES INTRINSEQUES</h2>
        <p>{etat__connaisances__caracteristiques}</p>
    </div>
    <div id="tabs-pressions">
        <h1 class="titreBleu">3 PRESSIONS</h1>
        <h2>3.1 OCCUPATION GENERALE DU SOL</h2>
        <p><strong>Surfaces  (d'apr&egrave;s Corine Land Cover 1989-94 et RGA 2000) en % de la surface totale :</strong></p>
        <table border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" align="center">
			<tr>
				<th scope="col"><div align="center">Date / Zone</div></th>
				<th scope="col"><div align="center">Urbaine</div></th>
				<th scope="col"><div align="center">Agricole</div></th>
				<th scope="col"><div align="center">Forestière</div></th>
				<th scope="col"><div align="center">Industrielle</div></th>
				<th scope="col"><div align="center">Autre</div></th>
			</tr>
			<!-- BEGIN liste_occupationsols -->
			<tr>
				<td><div align="center">{liste_occupationsols.date_zone}</div></td>
				<td><div align="center">{liste_occupationsols.urbaine}</div></td>
				<td><div align="center">{liste_occupationsols.agricole}</div></td>
				<td><div align="center">{liste_occupationsols.forestiere}</div></td>
				<td><div align="center">{liste_occupationsols.industrielle}</div></td>
				<td><div align="center">{liste_occupationsols.autre}</div></td>
			</tr>
			<!-- END liste_occupationsols -->
		</table>
        <p>&nbsp;</p>
        <h2>3.2 DETAIL DE L'OCCUPATION AGRICOLE DU SOL</h2>
        <p>{details__occupation__agricole}</p>
        <h2>3.3 ELEVAGE</h2>
        <p>{elevage}</p>
        <h2>3.4 EVALUSATION DES SURPLUS AGRICOLES</h2>
        <p>{evaluation_surplus_agricoles}</p>
        <h2>3.5 POLLUTIONS PONCTUELLES AVEREES ET AUTRES POLLUTIONS SIGNIFICATIVES</h2>
        <p>{pollutions_ponctuelles}</p>
        <h2>3.6 CAPTAGES</h2>
        <p><strong>Volumes pr&eacute;lev&eacute;s en 2001 r&eacute;partis par usages (donn&eacute;es Agence de l'Eau RMC) :</strong></p>
        <table width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td rowspan="3"><table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" align="center">
					<tr>
						<th scope="col"><div align="center">Usage</div></th>
						<th scope="col"><div align="center">Volume pr&eacute;lev&eacute; (milliers m3)</div></th>
					</tr>
					<!-- BEGIN liste_volumespreleves -->
					<tr>
						<td><div align="center">{liste_volumespreleves.usage}</div></td>
						<td><div align="center">{liste_volumespreleves.volume_total}</div></td>
					</tr>
					<!-- END liste_volumespreleves -->
				</table></td>
				<td colspan="2"><div align="center"><strong>Evolution temporelle des pr&eacute;l&egrave;vements</strong></div></td>
			</tr>
			<tr>
				<td align="center"><p><strong>AEP<br>
				</strong>{evolution_temporelle_aep}</p>				</td>
				<td align="center"><strong>Industriels<br>
				</strong>{evolution_temporelle_industrie}</td>
			</tr>
			<tr>
				<td align="center"><strong>Irrigation<br>
				</strong>{evolution_temporelle_irrigation}</td>
				<td align="center"><strong>Total<br>
				</strong>{evolution_temporelle_total}</td>
			</tr>
			<tr>
				<td><strong>Qualit&eacute; info &eacute;volution pr&eacute;l&egrave;vements : </strong>{qual_info_evol_temp_prelev}</td>
				<td colspan="2" align="center"><strong>Source : </strong>{source_info_evol_temp_prelev}</td>
			</tr>
		</table>
        <p>{commentaire__pression__captage}</p>
        <h2>3.7 RECHARGE ARTIFICIELLE</h2>
        <p><strong>Pratique de la recharge artificielle de l'aquif&egrave;re:</strong>&nbsp;<img src="images/check-{recharge_artificielle}.png" alt="Recharge_artificielle" /></p>
        <p>{commentaire__recharge__artificielle}</p>
        <h2>3.8 ETAT DES CONNAISSANCES SUR LES PRESSIONS</h2>
        <p>{commentaire__etat__pression}</p>
    </div>

    <div id="tabs-etatressources">
        <h1 class="titreBleu">4. ETAT DES MILIEUX</h1>
        <h2>4.1. RESEAUX DE SURVEILLANCE QUANTITATIF ET CHIMIQUE</h2>
        <p><strong>R&eacute;seaux connaissancesquantit&eacute; :</strong></p>
        <p>{reseaux__quantite__2003}</p>
        <p><strong>R&eacute;seaux connaissancesqualit&eacute; :</strong></p>
        <p>{Reseaux__qualite__2003}</p>
        <h2>4.2. ETAT QUANTITATIF</h2>
        <p>{equilibre_prel_renouvellement}</p>
        <table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" >
			<tr>
				<th colspan="2" align="left" scope="col"><div align="left"><strong>Informations :</strong></div></th>
			</tr>
			<tr>
				<td><div align="center"><em><strong>Qualit&eacute; :</strong></em> {qual_info_etat_quant}</div></td>
				<td><div align="center"><em><strong>Source :</strong></em> {source_info_etat_quant}</div></td>
			</tr>
		</table>
        <h2>4.3. ETAT QUALITATIF</h2>
        <h3>4.3.1 Fond hydrochimique naturel</h3>
        <p>{fond_geochimique_naturel}</p>
        <h2>4.4. ETAT DES CONNAISSANCES SUR L'ETAT DES MILIEUX</h2>
        <p>{etat__connaissance__etat__milieu}</p>
        <h3>4.3.2 Caract&eacute;ristiques hydrochimiques. situation actuelle et &eacute;volution tendancielle</h3>
        <h4><strong>Nitrates : </strong></h4>
        <p><strong>Teneur proche ou d&eacute;passement seuil AEP et/ou tendance  hausse :</strong> <img src="images/check-{nitates_____seuil}.png" alt="nitates_____seuil" /></p>
        <p>{nitates__commentaire}</p>
        <table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" >
			<tr>
				<th colspan="2" align="left" scope="col"><div align="left"><strong>Informations :</strong></div></th>
			</tr>
			<tr>
				<td><div align="center"><em><strong>Qualit&eacute; :</strong></em> {qual_info_etat_nitrates}</div></td>
				<td><div align="center"><em><strong>Source :</strong></em> {source_info_etat_nitrates}</div></td>
			</tr>
		</table>
    	<h4><strong>Pesticides : </strong></h4>
    	<p><strong>Teneur proche ou d&eacute;passement seuil AEP et/ou tendance  hausse : </strong><img src="images/check-{phyto_____seuil}.png" alt="phyto_____seuil" /></p>
    	<p>{phyto__commentaire}</p>
    	<table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" >
			<tr>
				<th colspan="2" align="left" scope="col"><div align="left"><strong>Informations :</strong></div></th>
			</tr>
			<tr>
				<td><div align="center"><em><strong>Qualit&eacute; :</strong></em> {qual_info_etat_phyto}</div></td>
				<td><div align="center"><em><strong>Source :</strong></em> {source_info_etat_phyto}</div></td>
			</tr>
		</table>
    	<h4><strong>Solvants chlor&eacute;s :</strong></h4>
    	<strong>Teneur proche ou d&eacute;passement seuil AEP et/ou tendance  hausse : </strong><img src="images/check-{solvants__cl_____seuil}.png" alt="solvants__cl_____seuil" />
    	<p>{solvants__cl__commentaire}</p>
    	<table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" >
			<tr>
				<th colspan="2" align="left" scope="col"><div align="left"><strong>Informations :</strong></div></th>
			</tr>
			<tr>
				<td><div align="center"><em><strong>Qualit&eacute; :</strong></em> {qual_info_etat_solvants_cl}</div></td>
				<td><div align="center"><em><strong>Source :</strong></em> {source_info_etat_solvants_cl}</div></td>
			</tr>
		</table>
    	<h4>Chlorures et sulfates :</h4>
    	<p><strong>Teneur proche ou d&eacute;passement seuil AEP et/ou tendance  hausse : </strong>Cl :<img src="images/check-{cl_____seuil}.png" alt="cl_____seuil" /> 
			SO4 : <img src="images/check-{so4_____seuil}.png" alt="so4_____seuil" />		</p>
    	<p>{cl__et__so4__commentaire}</p>
    	<table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" >
			<tr>
				<th colspan="2" align="left" scope="col"><div align="left"><strong>Informations :</strong></div></th>
			</tr>
			<tr>
				<td><div align="center"><em><strong>Qualit&eacute; :</strong></em> {qual_info_etat_cl_so4}</div></td>
				<td><div align="center"><em><strong>Source :</strong></em> {source_info_etat_cl_so4}</div></td>
			</tr>
		</table>
    	<h4>Ammonium :</h4>
    	<p><strong>Teneur proche ou d&eacute;passement seuil AEP et/ou tendance  hausse :</strong> <img src="images/check-{nh4_____seuil}.png" alt="nh4_____seuil" /></p>
    	<p>{nh4__commentaire}</p>
    	<table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" >
			<tr>
				<th colspan="2" align="left" scope="col"><div align="left"><strong>Informations :</strong></div></th>
			</tr>
			<tr>
				<td><div align="center"><em><strong>Qualit&eacute; :</strong></em> {qual_info_etat_nh4}</div></td>
				<td><div align="center"><em><strong>Source :</strong></em> {source_info_etat_nh4}</div></td>
			</tr>
		</table>
    	<h4>Autres polluants :</h4>
    	<p><strong>Teneur proche ou d&eacute;passement seuil AEP et/ou tendance  hausse :</strong> <img src="images/check-{nh4_____seuil}.png" alt="nh4_____seuil" /></p>
    	<p>{autres__poluants__commentaire}</p>
    	<table style="width:350px;" border="0" cellspacing="0" cellpadding="5" class="mdtb_form_table" >
			<tr>
				<th colspan="2" align="left" scope="col"><div align="left"><strong>Informations :</strong></div></th>
			</tr>
			<tr>
				<td><div align="center"><em><strong>Qualit&eacute; :</strong></em> {qual_info_etat_autres_pol}</div></td>
				<td><div align="center"><em><strong>Source :</strong></em> {source_info_etat_autres_pol}</div></td>
			</tr>
		</table>
    	</div>
    <div id="tabs-autre">
        <h1 class="titreBleu">5. INTERET ECONOMIQUE ET ECOLOGIQUE DE LA RESSOURCE EN EAU</h1>
        <p>Int&eacute;r&ecirc;t &eacute;cologique ressource et milieux aquatiques associ&eacute;s:</p>
        <p>{ecologie_milieux_aquatiques}</p>
        <p>Int&eacute;r&ecirc;t &eacute;conomique ressource et milieux aquatiques associ&eacute;s:</p>
        <p>{economie_ressources}</p>
        <h1 class="titreBleu">6. REGLEMENTATION ET OUTILS DE GESTION</h1>
        <h2>7.1. R&eacute;glementation sp&eacute;cifique existante :</h2>
        <p>{reglementation}</p>
        <h2>7.2. Outil de gestion existant :</h2>
        <p>{outils_gestion}</p>
        <h1 class="titreBleu">7. PROPOSITIONS D'ORIENTATIONS PRIORITAIRES D'ACTION</h1>
        <p>{propositions__orientations}</p>
        <h1 class="titreBleu">8. REFERENCES BIBLIOGRAPHIQUES PRINCIPALES</h1>
        <p>{References__biblio}</p>
        <h1 class="titreBleu">9. CRITERES D'IDENTIFICATION DES SECTEURS</h1>
        <p>{criteres_sectorisation}</p>
    </div>

</div>