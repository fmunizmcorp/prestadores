<?php
header('Content-Type: text/plain');
echo "Extracting deployer...\n";
exec("tar -xzf deploy_deployer.tar.gz 2>&1", $o, $r);
echo ($r === 0 ? "OK!\n" : "FAIL\n");
if ($r === 0) {
    echo "Access: /check_notas_fiscais_table.php?action=deploy\n";
    unlink('deploy_deployer.tar.gz');
}
print_r($o);
