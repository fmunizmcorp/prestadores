<?php
/**
 * Verify uploaded files - Sprint 28 V17 Final
 * Direct access - NO autoloader, NO migrations
 */

header('Content-Type: text/plain; charset=utf-8');

echo str_repeat("=", 70) . "\n";
echo "VERIFICATION REPORT - SPRINT 28 V17 FINAL\n";
echo str_repeat("=", 70) . "\n\n";

echo "[1] PHP Version\n";
echo "   PHP: " . PHP_VERSION . "\n";
echo "   SAPI: " . php_sapi_name() . "\n";
echo "   Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n\n";

echo "[2] Database.php\n";
$db_file = __DIR__ . '/src/Database.php';
if (file_exists($db_file)) {
    echo "   Exists: YES ✅\n";
    echo "   Size: " . filesize($db_file) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($db_file)) . "\n";
    echo "   MD5: " . md5_file($db_file) . "\n";
    
    $content = file_get_contents($db_file);
    $has_exec = strpos($content, 'public function exec(') !== false;
    $has_query = strpos($content, 'public function query(') !== false;
    $has_prepare = strpos($content, 'public function prepare(') !== false;
    
    echo "   Has exec(): " . ($has_exec ? "YES ✅" : "NO ❌") . "\n";
    echo "   Has query(): " . ($has_query ? "YES ✅" : "NO ❌") . "\n";
    echo "   Has prepare(): " . ($has_prepare ? "YES ✅" : "NO ❌") . "\n";
    
    // Count all proxy methods
    $proxy_methods = ['exec', 'query', 'prepare', 'beginTransaction', 'commit', 'rollBack', 'lastInsertId', 'getAttribute', 'setAttribute'];
    $found = 0;
    foreach ($proxy_methods as $method) {
        if (strpos($content, "public function $method(") !== false) {
            $found++;
        }
    }
    echo "   Proxy methods: $found/9\n";
    
    // Show first 100 chars of exec method if found
    if ($has_exec) {
        $pos = strpos($content, 'public function exec(');
        $snippet = substr($content, $pos, 150);
        echo "   exec() snippet:\n";
        echo "      " . substr($snippet, 0, 80) . "...\n";
    }
} else {
    echo "   ❌ FILE NOT FOUND!\n";
}

echo "\n[3] DatabaseMigration.php\n";
$mig_file = __DIR__ . '/src/DatabaseMigration.php';
if (file_exists($mig_file)) {
    echo "   Exists: YES ✅\n";
    echo "   Size: " . filesize($mig_file) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($mig_file)) . "\n";
    echo "   MD5: " . md5_file($mig_file) . "\n";
    
    $lines = file($mig_file);
    
    // Show lines 15-20
    echo "   Lines 15-20:\n";
    for ($i = 14; $i < 20 && $i < count($lines); $i++) {
        $line_num = $i + 1;
        echo "      $line_num: " . rtrim($lines[$i]) . "\n";
    }
    
    // Check for getConnection()
    $content = file_get_contents($mig_file);
    $has_getConnection = strpos($content, '->getConnection()') !== false;
    echo "   Uses ->getConnection(): " . ($has_getConnection ? "YES ✅" : "NO ❌") . "\n";
} else {
    echo "   ❌ FILE NOT FOUND!\n";
}

echo "\n[4] public/index.php\n";
$index_file = __DIR__ . '/public/index.php';
if (file_exists($index_file)) {
    echo "   Exists: YES ✅\n";
    echo "   Size: " . filesize($index_file) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($index_file)) . "\n";
    echo "   MD5: " . md5_file($index_file) . "\n";
    
    $content = file_get_contents($index_file);
    
    // Check for Sprint 27 cache clearing code
    $has_opcache_reset = strpos($content, 'opcache_reset()') !== false;
    $has_clearstatcache = strpos($content, 'clearstatcache(true)') !== false;
    
    echo "   Has opcache_reset(): " . ($has_opcache_reset ? "YES ✅" : "NO ❌") . "\n";
    echo "   Has clearstatcache(): " . ($has_clearstatcache ? "YES ✅" : "NO ❌") . "\n";
} else {
    echo "   ❌ FILE NOT FOUND!\n";
}

echo "\n[5] OPcache Status\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "   Enabled: " . ($status['opcache_enabled'] ? 'YES ✅' : 'NO ❌') . "\n";
        echo "   Cache full: " . ($status['cache_full'] ? 'YES ⚠️' : 'NO ✅') . "\n";
        echo "   Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "   Hits: " . number_format($status['opcache_statistics']['hits']) . "\n";
        echo "   Misses: " . number_format($status['opcache_statistics']['misses']) . "\n";
        echo "   Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
        
        // Check if our specific files are cached
        if (isset($status['scripts'])) {
            echo "\n   Cached files check:\n";
            foreach ([$db_file, $mig_file, $index_file] as $file) {
                $is_cached = isset($status['scripts'][$file]);
                echo "      " . basename($file) . ": " . ($is_cached ? "CACHED" : "NOT CACHED") . "\n";
                if ($is_cached) {
                    $cache_time = $status['scripts'][$file]['timestamp'];
                    $file_time = filemtime($file);
                    $outdated = $cache_time < $file_time;
                    echo "         Cache time: " . date('Y-m-d H:i:s', $cache_time) . "\n";
                    echo "         File time:  " . date('Y-m-d H:i:s', $file_time) . "\n";
                    echo "         Status: " . ($outdated ? "OUTDATED ⚠️" : "UP TO DATE ✅") . "\n";
                }
            }
        }
    } else {
        echo "   Status: Not available\n";
    }
} else {
    echo "   ❌ opcache_get_status() not available\n";
}

echo "\n[6] Configuration\n";
$config_file = __DIR__ . '/config/database.php';
if (file_exists($config_file)) {
    echo "   database.php: EXISTS ✅\n";
} else {
    echo "   database.php: NOT FOUND ❌\n";
}

echo "\n[7] File Permissions\n";
$files_to_check = [$db_file, $mig_file, $index_file];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $perms_str = substr(sprintf('%o', $perms), -4);
        echo "   " . basename($file) . ": $perms_str\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DIAGNOSIS COMPLETE\n";
echo str_repeat("=", 70) . "\n";

echo "\n[RECOMMENDATION]\n";
if (file_exists($db_file)) {
    $content = file_get_contents($db_file);
    $has_all_methods = strpos($content, 'public function exec(') !== false;
    
    if (!$has_all_methods) {
        echo "❌ Database.php is MISSING proxy methods!\n";
        echo "   Action: Re-upload Database.php from Sprint 26\n";
    } else {
        echo "✅ Database.php has proxy methods\n";
        echo "   If error persists, the issue is OPcache serving old version\n";
        echo "   Action: Clear OPcache via hPanel or contact Hostinger\n";
    }
}
