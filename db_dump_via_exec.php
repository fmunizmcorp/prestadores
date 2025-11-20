<?php
$file = 'backup_' . date('Ymd_His') . '.sql';
$cmd = "mysqldump -h localhost -u u673902663_admin -p';>?I4dtn~2Ga' u673902663_prestadores > $file 2>&1";
exec($cmd, $output, $return);
echo "Command: $cmd\n";
echo "Return: $return\n";
echo "Output: " . implode("\n", $output) . "\n";
if (file_exists($file)) {
    echo "File created: $file (" . filesize($file) . " bytes)\n";
} else {
    echo "File NOT created\n";
}
