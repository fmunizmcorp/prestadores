<?php
// TIMESTAMP: 1762808436_358062754
/**
 * COMPREHENSIVE DIAGNOSTIC AND DEPLOYMENT TOOL
 * Substitui check_notas_fiscais_table.php temporariamente
 */
$pdo = new PDO("mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4", 
               'u673902663_admin', ';>?I4dtn~2Ga');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: text/plain; charset=utf-8');

$action = $_GET['action'] ?? 'diagnostic';

if ($action === 'diagnostic') {
    // COMPREHENSIVE DIAGNOSTICS
    echo "═══════════════════════════════════════════════════════════\n";
    echo "COMPREHENSIVE TABLE DIAGNOSTICS\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "Location Info:\n";
    echo "  CWD: " . getcwd() . "\n";
    echo "  Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
    echo "  Script: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n\n";
    
    $tables = ['projetos', 'atividades', 'notas_fiscais'];
    
    foreach ($tables as $table_name) {
        echo "───────────────────────────────────────────────────────────────\n";
        echo "TABLE: $table_name\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        $result = $pdo->query("SHOW TABLES LIKE '$table_name'");
        if ($result->rowCount() == 0) {
            echo "✗ TABLE DOES NOT EXIST!\n\n";
            continue;
        }
        
        echo "✓ Table exists\n\n";
        
        echo "STRUCTURE:\n";
        $columns = $pdo->query("SHOW COLUMNS FROM $table_name")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            printf("  %-30s %-25s %s\n", 
                   $col['Field'], 
                   $col['Type'], 
                   $col['Null'] == 'YES' ? 'NULL' : 'NOT NULL');
        }
        
        echo "\nTotal columns: " . count($columns) . "\n";
        
        $count = $pdo->query("SELECT COUNT(*) FROM $table_name")->fetchColumn();
        echo "Total records: " . number_format($count) . "\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "ALL TABLES IN DATABASE:\n";
    echo "═══════════════════════════════════════════════════════════\n";
    $all_tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($all_tables as $tbl) {
        $count = $pdo->query("SELECT COUNT(*) FROM $tbl")->fetchColumn();
        echo sprintf("  %-30s %s rows\n", $tbl, number_format($count));
    }
    
    echo "\n✅ DIAGNOSTIC COMPLETE\n";
    echo "\nℹ️  Use ?action=deploy to deploy latest code from GitHub\n";
}

elseif ($action === 'deploy') {
    // DEPLOY FROM GITHUB
    echo "═══════════════════════════════════════════════════════════\n";
    echo "DEPLOYING FROM GITHUB\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    $branch = 'genspark_ai_developer';
    $commit = 'e02141a'; // Latest commit
    $repo_url = "https://github.com/fmunizmcorp/prestadores/archive/refs/heads/{$branch}.zip";
    $zip_file = "deploy_github.zip";
    
    echo "[1/5] Downloading from GitHub...\n";
    exec("curl -L -o $zip_file '$repo_url' 2>&1", $output1, $ret1);
    
    if ($ret1 === 0 && file_exists($zip_file) && filesize($zip_file) > 1000) {
        echo "✓ Downloaded: " . number_format(filesize($zip_file)) . " bytes\n\n";
    } else {
        $content = @file_get_contents($repo_url);
        if ($content && strlen($content) > 1000) {
            file_put_contents($zip_file, $content);
            echo "✓ Downloaded via file_get_contents: " . number_format(strlen($content)) . " bytes\n\n";
        } else {
            echo "✗ Download failed!\n";
            exit(1);
        }
    }
    
    echo "[2/5] Creating backup...\n";
    $backup = 'backup_' . date('YmdHis') . '.tar.gz';
    exec("tar -czf $backup --exclude='*.tar.gz' --exclude='*.zip' --exclude='backup_*' . 2>&1", $output2, $ret2);
    echo ($ret2 === 0 ? "✓" : "⚠") . " Backup: $backup\n\n";
    
    echo "[3/5] Extracting...\n";
    exec("unzip -o $zip_file 2>&1", $output3, $ret3);
    
    if ($ret3 === 0) {
        echo "✓ Extraction successful!\n\n";
        
        $extracted_dir = "prestadores-{$branch}";
        
        if (is_dir($extracted_dir)) {
            echo "[4/5] Moving files...\n";
            exec("cp -rf $extracted_dir/* . 2>&1", $output4, $ret4);
            exec("cp -rf $extracted_dir/.htaccess . 2>&1", $output5, $ret5);
            echo "✓ Files moved!\n\n";
            
            exec("rm -rf $extracted_dir 2>&1");
            unlink($zip_file);
        }
    } else {
        echo "✗ Extraction failed!\n";
        print_r($output3);
        exit(1);
    }
    
    echo "[5/5] Setting permissions...\n";
    exec("chmod -R 755 . 2>&1");
    exec("chmod -R 777 public/uploads 2>&1");
    echo "✓ Permissions set!\n\n";
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ DEPLOYMENT COMPLETE!\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "Now access: ?action=diagnostic to verify\n";
}

else {
    echo "Invalid action. Use ?action=diagnostic or ?action=deploy\n";
}
