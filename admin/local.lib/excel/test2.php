<?php
require_once 'Writer.php';
$workbook = new Spreadsheet_Excel_Writer('php_excel2.xls');
$workbook->setVersion(8);
$worksheet =& $workbook->addWorksheet('Titre');
$format_Arial =& $workbook->addFormat();
$format_Arial->setFontFamily('Arial');
$format_Arial->setBold();
$format_Arial->setSize(10);
$format_Ar =& $workbook->addFormat();
$format_Ar->setFontFamily('Arial');
$format_Ar->setSize(10);
for ($i = 0; $i < 10; $i++)
	for($j=0;$j<10;$j++)
		$worksheet->write($i, $j,"Label ".$i."x".$j,$format_Arial);
$worksheet->write(10, 0,"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec aliquam ullamcorper lorem. Phasellus eu sem aliquam sapien consequat malesuada. Vivamus nibh sem, vulputate non, egestas eget, pretium quis, tortor. Cras diam. Proin arcu sapien, laoreet quis, consequat et, tincidunt at, lacus. Aliquam at dolor. Donec varius eros. Vivamus vitae est in pede cursus tincidunt. Aliquam id nisi eget mi sagittis adipiscing. Duis sit amet ante. Nulla bibendum. Sed urna neque, ultrices vel, rutrum ut, tincidunt sit amet, nisi.

Integer id purus. Fusce et leo eget neque imperdiet eleifend. Praesent vestibulum tincidunt sem. Quisque sapien velit, dictum ac, porta non, semper eu, arcu. Donec varius diam in magna. Fusce vel tortor id nulla rutrum laoreet. Fusce suscipit. Vestibulum sed pede nec orci ornare elementum. Etiam et elit. Nunc ut orci.

Aenean tellus enim, vestibulum vel, feugiat sit amet, volutpat vitae, leo. Curabitur dolor odio, tempus sit amet, eleifend sit amet, ultricies sit amet, augue. Duis diam magna, ornare at, feugiat in, tincidunt sed, neque. Sed at dui. Duis ante neque, porta vitae, malesuada tempus, dapibus ac, enim. Donec eget purus quis diam rutrum tempor. Aliquam pulvinar luctus sapien. In semper nulla. Aliquam eleifend risus et elit. Suspendisse sed neque sit amet odio posuere sodales. Suspendisse potenti. Praesent scelerisque lorem ac ipsum. Integer volutpat ultrices ipsum. Mauris eleifend bibendum libero.

Nunc eu turpis at tortor venenatis imperdiet. Nam in enim. Ut mattis nisi nec leo. Nunc eget libero. Donec mauris. Cras vel tellus eget metus lobortis pretium. Ut sit amet diam. Maecenas posuere tristique nulla. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aenean ultrices ligula eget enim.

Praesent non enim vitae dui posuere pharetra. Aliquam ligula justo, volutpat nec, tincidunt eu, laoreet in, massa. Maecenas nec libero. Nulla semper. Donec mi. Nulla vitae ante eget massa eleifend varius. Mauris varius sapien convallis turpis. Suspendisse fringilla. Phasellus tincidunt urna non ante. Pellentesque tincidunt tellus ac mi. Etiam risus ligula, congue sed, tristique ut, mattis et, ligula. Donec rhoncus tincidunt nulla. Integer mattis commodo massa. ",$format_Arial);
$workbook->close();
?>