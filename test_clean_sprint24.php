<?php
/**
 * Test Clean - Sprint 24
 * Arquivo de teste NOVO para verificar se cache foi limpo
 */

header('Content-Type: text/plain; charset=utf-8');

echo "SPRINT 24 - TEST CLEAN\n";
echo str_repeat("=", 80) . "\n\n";

echo "Data: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

// Test 1: Check if OPcache is enabled
echo "[1] OPcache Status:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status) {
        echo "    Enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
        echo "    Cached files: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    } else {
        echo "    OPcache disabled\n";
    }
} else {
    echo "    OPcache not available\n";
}

// Test 2: Try to load DatabaseMigration
echo "\n[2] Testing DatabaseMigration:\n";

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

try {
    require_once SRC_PATH . '/Database.php';
    echo "    ✅ Database.php loaded\n";
    
    require_once SRC_PATH . '/DatabaseMigration.php';
    echo "    ✅ DatabaseMigration.php loaded\n";
    
    $migration = new App\DatabaseMigration();
    echo "    ✅ DatabaseMigration instantiated\n";
    
    echo "\n✅✅✅ SUCCESS! Cache is clean!\n";
    
} catch (Throwable $e) {
    echo "    ❌ ERROR: " . $e->getMessage() . "\n";
    echo "    Line: " . $e->getLine() . "\n";
    echo "    File: " . $e->getFile() . "\n";
    echo "\n❌ Cache still serving old version!\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Test complete\n";
