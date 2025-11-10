<?php
header('Content-Type: text/plain');
set_time_limit(300);
echo "DEPLOYING FROM GITHUB RAW\n\n";
$files=[['src/Models/NotaFiscal.php','https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/NotaFiscal.php'],['src/Models/Projeto.php','https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Projeto.php'],['src/Models/Atividade.php','https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Atividade.php']];
foreach($files as$f){echo$f[0]."...";$c=@file_get_contents($f[1]);if($c&&strlen($c)>100){@mkdir(dirname($f[0]),0755,true);file_put_contents($f[0],$c)?print"OK\n":print"FAIL\n";}else{echo"FAIL\n";}}
echo"\nDONE! Clear cache now.\n";
