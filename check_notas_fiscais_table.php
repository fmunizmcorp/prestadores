<?php
/**
 * COMPREHENSIVE DIAGNOSTIC AND DEPLOYMENT TOOL - UPDATED
 * Now deploys from main branch using direct RAW file downloads
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
    echo "\nℹ️  Use ?action=deploy to deploy latest code from GitHub main branch\n";
}

elseif ($action === 'deploy') {
    // DEPLOY CORRECTED MODELS FROM GITHUB RAW
    echo "═══════════════════════════════════════════════════════════\n";
    echo "DEPLOYING CORRECTED MODELS FROM GITHUB MAIN\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    set_time_limit(300);
    
    $files = [
        ['src/Models/NotaFiscal.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/NotaFiscal.php'],
        ['src/Models/Projeto.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Projeto.php'],
        ['src/Models/Atividade.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Atividade.php']
    ];
    
    $success = 0;
    $failed = 0;
    
    foreach ($files as $f) {
        echo "Deploying: {$f[0]}...";
        
        $content = @file_get_contents($f[1]);
        
        if ($content && strlen($content) > 100) {
            $dir = dirname($f[0]);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            
            if (file_put_contents($f[0], $content)) {
                echo " ✅ OK (" . number_format(strlen($content)) . " bytes)\n";
                $success++;
            } else {
                echo " ❌ WRITE FAILED\n";
                $failed++;
            }
        } else {
            echo " ❌ DOWNLOAD FAILED\n";
            $failed++;
        }
    }
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "DEPLOYMENT SUMMARY:\n";
    echo "  ✅ Success: $success files\n";
    echo "  ❌ Failed: $failed files\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    if ($success > 0) {
        echo "✅ DEPLOYMENT COMPLETE!\n\n";
        echo "Next steps:\n";
        echo "1. Clear OPcache: access clear_cache.php\n";
        echo "2. Test routes: run test_all_routes.sh\n";
        echo "3. Expected result: 37/37 routes functional (100%)\n";
    } else {
        echo "❌ DEPLOYMENT FAILED!\n";
        echo "Check file permissions and network connectivity.\n";
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
            echo "⚠️  OPcache reset failed (may not have permission)\n";
        }
    } else {
        echo "ℹ️  OPcache not available or not enabled\n";
    }
    
    echo "\n✅ CACHE CLEAR COMPLETE\n";
}

else {
    echo "Invalid action. Available actions:\n";
    echo "  ?action=diagnostic   - Show database schemas\n";
    echo "  ?action=deploy       - Deploy corrected Models from GitHub\n";
    echo "  ?action=clear_cache  - Clear PHP OPcache\n";
}
