<?php
/**
 * SELF-DEPLOYER - Downloads and writes corrected Models from GitHub
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
    echo "DEPLOYING CORRECTED MODELS FROM GITHUB MAIN\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    set_time_limit(300);
    
    $base_path = getcwd();
    echo "Working directory: $base_path\n\n";
    
    $files = [
        ['src/Models/NotaFiscal.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/NotaFiscal.php'],
        ['src/Models/Projeto.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Projeto.php'],
        ['src/Models/Atividade.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Atividade.php']
    ];
    
    $success = 0;
    $failed = 0;
    
    foreach ($files as $f) {
        $target_path = $base_path . '/' . $f[0];
        echo "Deploying: {$f[0]}\n";
        echo "  Target: $target_path\n";
        echo "  Source: {$f[1]}\n";
        
        $content = @file_get_contents($f[1]);
        
        if ($content && strlen($content) > 100) {
            echo "  Downloaded: " . number_format(strlen($content)) . " bytes\n";
            
            $dir = dirname($target_path);
            if (!is_dir($dir)) {
                echo "  Creating directory: $dir\n";
                @mkdir($dir, 0755, true);
            }
            
            if (file_exists($target_path)) {
                $old_size = filesize($target_path);
                echo "  Replacing existing file ($old_size bytes)\n";
            }
            
            if (file_put_contents($target_path, $content)) {
                echo "  ✅ DEPLOYED SUCCESSFULLY\n\n";
                $success++;
            } else {
                echo "  ❌ WRITE FAILED (check permissions)\n\n";
                $failed++;
            }
        } else {
            echo "  ❌ DOWNLOAD FAILED\n\n";
            $failed++;
        }
    }
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "DEPLOYMENT SUMMARY:\n";
    echo "  ✅ Success: $success files\n";
    echo "  ❌ Failed: $failed files\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    if ($success === 3) {
        echo "✅ DEPLOYMENT COMPLETE!\n\n";
        echo "Next steps:\n";
        echo "1. Clear OPcache: ?action=clear_cache\n";
        echo "2. Test routes: run test_all_routes.sh\n";
        echo "3. Expected result: 37/37 routes functional (100%)\n";
    } elseif ($success > 0) {
        echo "⚠️  PARTIAL DEPLOYMENT\n";
        echo "Some files were deployed but others failed.\n";
        echo "Check file permissions and try again.\n";
    } else {
        echo "❌ DEPLOYMENT FAILED!\n";
        echo "Check:\n";
        echo "  - File write permissions\n";
        echo "  - Network connectivity to GitHub\n";
        echo "  - Working directory path\n";
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
    echo "\nNow test the routes to verify 100% functionality.\n";
}

else {
    echo "Invalid action. Available actions:\n";
    echo "  ?action=diagnostic   - Show database schemas\n";
    echo "  ?action=deploy       - Deploy corrected Models from GitHub main\n";
    echo "  ?action=clear_cache  - Clear PHP OPcache\n";
}
