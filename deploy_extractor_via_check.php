<?php
// ESTE ARQUIVO VAI SUBSTITUIR check_notas_fiscais_table.php TEMPORARIAMENTE
// PARA EXECUTAR O DEPLOYMENT

header('Content-Type: text/plain; charset=utf-8');

$action = $_GET['action'] ?? 'check';

if ($action === 'deploy') {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "EXECUTING DEPLOYMENT - SPRINT 14\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    $file = 'deploy_sprint14_diagnostics.tar.gz';
    
    if (file_exists($file)) {
        echo "✓ Found: $file (" . number_format(filesize($file)) . " bytes)\n";
        echo "✓ Current dir: " . getcwd() . "\n\n";
        
        echo "[1/3] Creating backup...\n";
        $backup = 'backup_' . date('YmdHis') . '.tar.gz';
        exec("tar -czf $backup --exclude='*.tar.gz' --exclude='backup_*' . 2>&1", $out1, $ret1);
        echo ($ret1 === 0 ? "✓" : "⚠") . " Backup: $backup\n\n";
        
        echo "[2/3] Extracting...\n";
        exec("tar -xzf $file 2>&1", $out2, $ret2);
        
        if ($ret2 === 0) {
            echo "✓ Extraction SUCCESS!\n\n";
            
            echo "[3/3] Permissions...\n";
            exec("chmod -R 755 . 2>&1");
            exec("chmod -R 777 public/uploads 2>&1");
            echo "✓ Done!\n\n";
            
            echo "═══════════════════════════════════════════════════════════\n";
            echo "✅ DEPLOYMENT COMPLETE!\n";
            echo "═══════════════════════════════════════════════════════════\n\n";
            
            echo "Access diagnostic scripts:\n";
            echo "  /check_projetos_table.php\n";
            echo "  /check_atividades_table.php\n";
            echo "  /check_notas_fiscais_table.php\n\n";
            
            unlink($file);
            echo "✓ Deployment file removed\n";
        } else {
            echo "✗ EXTRACTION FAILED!\n";
            print_r($out2);
        }
    } else {
        echo "✗ File NOT FOUND: $file\n\n";
        echo "Files present:\n";
        foreach (scandir('.') as $f) {
            if ($f[0] != '.') echo "  - $f\n";
        }
    }
    exit;
}

// DEFAULT ACTION - CHECK NOTAS FISCAIS
$pdo = new PDO("mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4", 
               'u673902663_admin', ';>?I4dtn~2Ga');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "VERIFICANDO TABELA notas_fiscais\n";
echo "═══════════════════════════════════════════\n\n";

$result = $pdo->query("SHOW TABLES LIKE 'notas_fiscais'");
if ($result->rowCount() == 0) {
    echo "✗ TABELA notas_fiscais NÃO EXISTE!\n";
    exit(0);
}

echo "✓ Tabela notas_fiscais existe\n\n";
echo "ESTRUTURA ATUAL:\n";
echo "═══════════════════════════════════════════\n";

$columns = $pdo->query("SHOW COLUMNS FROM notas_fiscais")->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo sprintf("%-30s %-25s %s\n", 
                 $col['Field'], 
                 $col['Type'], 
                 $col['Null'] == 'YES' ? 'NULL' : 'NOT NULL');
}

echo "\nTotal de colunas: " . count($columns) . "\n";
echo "\n💡 Para fazer deploy: ?action=deploy\n";
