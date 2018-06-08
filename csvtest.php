<html>
	<head>
		<title>Test CSV vers table</title>
	</head>
	<body>
		<form method="post" action="#"  enctype="multipart/form-data" class="formAvisMassedeau">
			<input type="file" name="documents" />
			<input type="submit" name="test">
		</form>
		<?php
			if(isset($_FILES))
			{
				echo "Fichiers : ".print_r($_FILES,true)."<br />";
			}
			if(isset($_FILES) && is_array($_FILES) && isset($_FILES["documents"]) && isset($_FILES["documents"]["tmp_name"]))
			{
				$fh=fopen($_FILES["documents"]["tmp_name"],"rt");
				echo "<table>";
				while($ligne=fgetcsv($fh,0,";"))
				{
					echo "<tr><td>".implode("</td><td>",$ligne)."</td></tr>";
				}
				echo "</table>";
			}
		?>
		
	</body>