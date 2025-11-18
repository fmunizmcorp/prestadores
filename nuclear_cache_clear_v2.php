<?php
/**
 * NUCLEAR CACHE CLEAR V2
 * Clear TUDO - OPcache, Stat, Realpath, Files
 * Sprint 28 - Deploy Definitivo
 */

header('Content-Type: text/plain; charset=utf-8');

echo "="*60 . "\n";
echo "NUCLEAR CACHE CLEAR V2 - SPRINT 28\n";
echo "="*60 . "\n\n";

// 1. Clear OPcache
echo "[1] Clearing OPcache...\n";
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo $result ? "✅ OPcache reset SUCCESS\n" : "❌ OPcache reset FAILED\n";
} else {
    echo "❌ opcache_reset() not available\n";
}

// 2. Clear stat cache
echo "\n[2] Clearing stat cache...\n";
clearstatcache(true);
echo "✅ Stat cache cleared\n";

// 3. Invalidate specific files
echo "\n[3] Invalidating critical files...\n";

$base_dir = dirname(__DIR__);
$critical_files = [
    $base_dir . '/src/Database.php',
    $base_dir . '/src/DatabaseMigration.php',
    $base_dir . '/public/index.php',
    __FILE__
];

if (function_exists('opcache_invalidate')) {
    foreach ($critical_files as $file) {
        if (file_exists($file)) {
            $result = opcache_invalidate($file, true);
            echo ($result ? "✅" : "❌") . " " . basename($file) . "\n";
        } else {
            echo "⚠️  File not found: " . basename($file) . "\n";
        }
    }
} else {
    echo "❌ opcache_invalidate() not available\n";
}

// 4. Touch files to force reload
echo "\n[4] Touching files to force reload...\n";
foreach ($critical_files as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "✅ Touched: " . basename($file) . "\n";
    }
}

// 5. Show OPcache status
echo "\n[5] OPcache status:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    
    echo "  Enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
    echo "  Cache full: " . ($status['cache_full'] ? 'YES' : 'NO') . "\n";
    echo "  Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "  Hits: " . $status['opcache_statistics']['hits'] . "\n";
    echo "  Misses: " . $status['opcache_statistics']['misses'] . "\n";
    echo "  Memory used: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
} else {
    echo "  ❌ opcache_get_status() not available\n";
}

// 6. Force garbage collection
echo "\n[6] Forcing garbage collection...\n";
if (function_exists('gc_collect_cycles')) {
    $collected = gc_collect_cycles();
    echo "✅ Collected $collected cycles\n";
}

// 7. Verify Database.php methods
echo "\n[7] Verifying Database.php...\n";
$db_file = $base_dir . '/src/Database.php';
if (file_exists($db_file)) {
    echo "  File exists: YES\n";
    echo "  Size: " . filesize($db_file) . " bytes\n";
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($db_file)) . "\n";
    
    $content = file_get_contents($db_file);
    echo "  Contains 'public function exec': " . (strpos($content, 'public function exec') !== false ? 'YES ✅' : 'NO ❌') . "\n";
    echo "  Contains 'public function query': " . (strpos($content, 'public function query') !== false ? 'YES ✅' : 'NO ❌') . "\n";
    echo "  Contains 'public function prepare': " . (strpos($content, 'public function prepare') !== false ? 'YES ✅' : 'NO ❌') . "\n";
} else {
    echo "  ❌ File not found!\n";
}

echo "\n" . "="*60 . "\n";
echo "CACHE CLEAR COMPLETE!\n";
echo "="*60 . "\n";
echo "\nNow test: https://prestadores.clinfec.com.br/\n";
