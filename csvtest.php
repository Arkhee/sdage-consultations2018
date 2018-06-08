<html>
	<head>
		<title>Test CSV vers table</title>
	</head>
	<body>
		<form method="post" action="#" 
							  onsubmit="return frontCtl.testFormulaireAvis(this);"
							  id="formAvisPression_{id_pression}_{id_massedeau}" 
							  enctype="multipart/form-data" target="targetSauvegarde"
							  class="formAvisMassedeau">
			<input type="file" name="documents" />
			<input type="submit" name="test">
		</form>
		<?php
			if(isset($_FILES) && is_array($_FILES) && isset($_FILES["documents"]) && isset($_FILES["documents"]["tmp_name"]))
			{
				$fh=fopen($_FILES["documents"]["tmp_name"]);
				echo "<table>";
				while($ligne=fgetcsv($fh,0,";"))
				{
					echo "<tr><td>".implode("</td><td>",$ligne)."</td></tr>";
				}
				echo "</table>";
			}
		?>
		
	</body>