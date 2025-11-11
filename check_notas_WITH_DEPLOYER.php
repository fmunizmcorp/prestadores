<?php
/**
 * INTEGRATED DIAGNOSTIC + DEPLOYER
 * This file combines diagnostics with deployment capability
 */
$pdo = new PDO("mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4", 
               'u673902663_admin', ';>?I4dtn~2Ga');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: text/plain; charset=utf-8');

$action = $_GET['action'] ?? 'diagnostic';

if ($action === 'diagnostic') {
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
    echo "\nℹ️  Use ?action=deploy to deploy corrected Models from GitHub main\n";
}

elseif ($action === 'deploy') {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "DEPLOYING CORRECTED MODELS FROM GITHUB MAIN - SPRINT 14\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    set_time_limit(300);
    
    $base_path = getcwd();
    echo "Working Directory: $base_path\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    $files = [
        [
            'target' => 'src/Models/NotaFiscal.php',
            'source' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/NotaFiscal.php',
            'desc' => 'NotaFiscal Model (30KB+ complete rewrite)'
        ],
        [
            'target' => 'src/Models/Projeto.php',
            'source' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Projeto.php',
            'desc' => 'Projeto Model (schema corrected)'
        ],
        [
            'target' => 'src/Models/Atividade.php',
            'source' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Atividade.php',
            'desc' => 'Atividade Model (schema corrected)'
        ]
    ];
    
    $success = 0;
    $failed = 0;
    
    foreach ($files as $idx => $f) {
        $num = $idx + 1;
        echo "───────────────────────────────────────────────────────────────\n";
        echo "[$num/3] {$f['desc']}\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        // Download
        echo "Downloading from GitHub...\n";
        $content = @file_get_contents($f['source']);
        
        if (!$content || strlen($content) < 100) {
            echo "❌ DOWNLOAD FAILED\n\n";
            $failed++;
            continue;
        }
        
        echo "✅ Downloaded: " . number_format(strlen($content)) . " bytes\n";
        
        // Verify PHP
        if (strpos($content, '<?php') === false) {
            echo "❌ INVALID PHP FILE\n\n";
            $failed++;
            continue;
        }
        
        // Prepare directory
        $target_path = $base_path . '/' . $f['target'];
        $target_dir = dirname($target_path);
        
        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0755, true);
        }
        
        // Backup
        if (file_exists($target_path)) {
            $backup = $target_path . '.bak' . date('His');
            @copy($target_path, $backup);
            echo "✅ Backup: " . basename($backup) . "\n";
        }
        
        // Write
        $written = @file_put_contents($target_path, $content);
        
        if ($written === false || $written === 0) {
            echo "❌ WRITE FAILED\n\n";
            $failed++;
            continue;
        }
        
        echo "✅ DEPLOYED: " . number_format($written) . " bytes written\n\n";
        $success++;
    }
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "DEPLOYMENT SUMMARY\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ Success: $success files\n";
    echo "❌ Failed: $failed files\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    if ($success === 3) {
        echo "✅ DEPLOYMENT COMPLETE!\n\n";
        echo "Next steps:\n";
        echo "1. Clear cache: ?action=clear_cache\n";
        echo "2. Test routes: Expected 37/37 (100%)\n";
    } else {
        echo "⚠️  PARTIAL or FAILED deployment\n";
    }
}

elseif ($action === 'clear_cache') {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "CLEARING PHP OPCACHE\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    if (function_exists('opcache_reset')) {
        if (opcache_reset()) {
            echo "✅ OPcache cleared successfully!\n";
        } else {
            echo "⚠️  OPcache reset failed\n";
        }
    } else {
        echo "ℹ️  OPcache not available\n";
    }
    
    echo "\n✅ CACHE CLEAR COMPLETE\n";
}

else {
    echo "Invalid action\n";
    echo "Available: ?action=diagnostic, ?action=deploy, ?action=clear_cache\n";
}
