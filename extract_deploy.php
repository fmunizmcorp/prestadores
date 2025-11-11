<?php
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "DEPLOY SPRINT 14 - EXTRACTION\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$file = 'deploy_sprint14_diagnostics.tar.gz';

if (file_exists($file)) {
    echo "✓ Found deployment package: $file\n";
    echo "  Size: " . number_format(filesize($file)) . " bytes\n";
    echo "  Current directory: " . getcwd() . "\n\n";
    
    // Create backup first
    echo "[1/3] Creating backup...\n";
    $backup = 'backup_before_sprint14_' . date('YmdHis') . '.tar.gz';
    exec("tar -czf $backup --exclude='*.tar.gz' --exclude='backup_*' . 2>&1", $output1, $ret1);
    
    if ($ret1 === 0 && file_exists($backup)) {
        echo "✓ Backup created: $backup\n\n";
    } else {
        echo "⚠ Backup failed (continuing anyway)\n\n";
    }
    
    // Extract
    echo "[2/3] Extracting deployment package...\n";
    exec("tar -xzf $file 2>&1", $output2, $ret2);
    
    if ($ret2 === 0) {
        echo "✓ Extraction successful!\n\n";
        
        echo "[3/3] Adjusting permissions...\n";
        exec("chmod -R 755 . 2>&1", $output3, $ret3);
        exec("chmod -R 777 public/uploads 2>&1", $output4, $ret4);
        echo "✓ Permissions adjusted!\n\n";
        
        echo "═══════════════════════════════════════════════════════════\n";
        echo "DEPLOYMENT SUCCESSFUL!\n";
        echo "═══════════════════════════════════════════════════════════\n\n";
        
        echo "Diagnostic scripts available at:\n";
        echo "  https://prestadores.clinfec.com.br/check_projetos_table.php\n";
        echo "  https://prestadores.clinfec.com.br/check_atividades_table.php\n";
        echo "  https://prestadores.clinfec.com.br/check_notas_fiscais_table.php\n\n";
        
        echo "System available at:\n";
        echo "  https://prestadores.clinfec.com.br/\n\n";
        
        // Clean up deployment file
        unlink($file);
        echo "✓ Deployment package removed\n";
        
    } else {
        echo "✗ Extraction failed!\n";
        echo "Error output:\n";
        print_r($output2);
    }
} else {
    echo "✗ File not found: $file\n\n";
    echo "Files in current directory:\n";
    $files = scandir('.');
    foreach ($files as $f) {
        if ($f != '.' && $f != '..' && !is_dir($f)) {
            echo "  - $f (" . number_format(filesize($f)) . " bytes)\n";
        }
    }
}
