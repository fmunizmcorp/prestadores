<?php
/**
 * Verify uploaded files - Sprint 28
 */

header('Content-Type: text/plain; charset=utf-8');

echo "="*70 . "\n";
echo "VERIFICATION REPORT - SPRINT 28\n";
echo "="*70 . "\n\n";

echo "[1] PHP Version\n";
echo "   PHP: " . PHP_VERSION . "\n";
echo "   SAPI: " . php_sapi_name() . "\n\n";

echo "[2] Database.php\n";
$db_file = __DIR__ . '/src/Database.php';
if (file_exists($db_file)) {
    echo "   Exists: YES\n";
    echo "   Size: " . filesize($db_file) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($db_file)) . "\n";
    
    $content = file_get_contents($db_file);
    $has_exec = strpos($content, 'public function exec(') !== false;
    $has_query = strpos($content, 'public function query(') !== false;
    $has_prepare = strpos($content, 'public function prepare(') !== false;
    
    echo "   Has exec(): " . ($has_exec ? "YES ✅" : "NO ❌") . "\n";
    echo "   Has query(): " . ($has_query ? "YES ✅" : "NO ❌") . "\n";
    echo "   Has prepare(): " . ($has_prepare ? "YES ✅" : "NO ❌") . "\n";
    
    if ($has_exec) {
        // Count proxy methods
        $proxy_count = 0;
        $methods = ['exec', 'query', 'prepare', 'beginTransaction', 'commit', 'rollBack', 'lastInsertId', 'getAttribute', 'setAttribute'];
        foreach ($methods as $method) {
            if (strpos($content, "public function $method(") !== false) {
                $proxy_count++;
            }
        }
        echo "   Proxy methods: $proxy_count/9\n";
    }
} else {
    echo "   ❌ FILE NOT FOUND!\n";
}

echo "\n[3] DatabaseMigration.php\n";
$mig_file = __DIR__ . '/src/DatabaseMigration.php';
if (file_exists($mig_file)) {
    echo "   Exists: YES\n";
    echo "   Size: " . filesize($mig_file) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($mig_file)) . "\n";
    
    $content = file_get_contents($mig_file);
    $has_getConnection = strpos($content, '->getConnection()') !== false;
    echo "   Uses ->getConnection(): " . ($has_getConnection ? "YES ✅" : "NO ❌") . "\n";
    
    // Show line 17
    $lines = explode("\n", $content);
    if (isset($lines[16])) {
        echo "   Line 17: " . trim($lines[16]) . "\n";
    }
} else {
    echo "   ❌ FILE NOT FOUND!\n";
}

echo "\n[4] OPcache Status\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "   Enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
    echo "   Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    
    // Check if our files are cached
    if (isset($status['scripts'])) {
        $our_files = [$db_file, $mig_file];
        foreach ($our_files as $file) {
            $cached = isset($status['scripts'][$file]);
            echo "   " . basename($file) . " cached: " . ($cached ? "YES" : "NO") . "\n";
            if ($cached) {
                echo "      Timestamp: " . date('Y-m-d H:i:s', $status['scripts'][$file]['timestamp']) . "\n";
            }
        }
    }
} else {
    echo "   ❌ opcache_get_status() not available\n";
}

echo "\n[5] Try loading Database class\n";
try {
    require_once $db_file;
    echo "   Loaded: YES ✅\n";
    
    $reflect = new ReflectionClass('App\\Database');
    $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
    
    echo "   Public methods (" . count($methods) . "):\n";
    foreach ($methods as $method) {
        if (!$method->isStatic() && $method->name !== '__construct' && $method->name !== '__destruct') {
            echo "      - " . $method->name . "()\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n" . "="*70 . "\n";
echo "END OF REPORT\n";
echo "="*70 . "\n";
