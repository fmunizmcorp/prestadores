<?php
/**
 * Sprint 61: Ultra-Simple Status Check
 * No requires, no includes, just pure PHP checks
 */

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

echo "SPRINT 61: CACHE STATUS\n";
echo "=========================\n";
echo "Time: " . date('Y-m-d H:i:s') . " UTC\n\n";

// 1. OPcache
echo "[OPcache]\n";
if (function_exists('opcache_get_status')) {
    $status = @opcache_get_status(false);
    if ($status && isset($status['opcache_enabled']) && $status['opcache_enabled']) {
        echo "Status: ⚠️  ACTIVE (caching)\n";
        echo "Cached: " . ($status['opcache_statistics']['num_cached_scripts'] ?? 0) . " scripts\n";
    } else {
        echo "Status: ✅ INACTIVE\n";
    }
} else {
    echo "Status: ✅ NOT AVAILABLE\n";
}

echo "\n[Database.php]\n";
$db_file = $_SERVER['DOCUMENT_ROOT'] . '/prestadores/src/Database.php';
if (file_exists($db_file)) {
    echo "Exists: ✅ YES\n";
    echo "Size: " . filesize($db_file) . " bytes\n";
    echo "Modified: " . date('H:i:s', filemtime($db_file)) . " UTC\n";
    echo "Age: " . round((time() - filemtime($db_file)) / 60) . " min\n";
    
    if (filesize($db_file) == 4522) {
        echo "✅ SIZE MATCHES Sprint 58\n";
    }
} else {
    echo "Exists: ❌ NO (path: $db_file)\n";
}

echo "\n[System]\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";

echo "\n=========================\n";
echo "Generated: " . date('H:i:s') . " UTC\n";
