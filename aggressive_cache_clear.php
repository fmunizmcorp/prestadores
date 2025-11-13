<?php
/**
 * Aggressive OPcache Clearing Script
 * Tries multiple methods to force cache invalidation
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== AGGRESSIVE CACHE CLEARING ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Method 1: opcache_reset()
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ opcache_reset() executed\n";
} else {
    echo "❌ opcache_reset() not available\n";
}

// Method 2: opcache_invalidate() for specific files
$files_to_invalidate = [
    __DIR__ . '/index.php',
    __DIR__ . '/src/Models/Projeto.php',
    __DIR__ . '/src/Models/Atividade.php',
    __DIR__ . '/src/Models/NotaFiscal.php',
    __DIR__ . '/src/Models/BaseModel.php',
];

if (function_exists('opcache_invalidate')) {
    foreach ($files_to_invalidate as $file) {
        if (file_exists($file)) {
            opcache_invalidate($file, true);
            echo "✅ opcache_invalidate($file)\n";
        }
    }
} else {
    echo "❌ opcache_invalidate() not available\n";
}

// Method 3: Touch files to change modification time
echo "\nTouching files to change mtime...\n";
foreach ($files_to_invalidate as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "✅ touch($file) - new mtime: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    }
}

// Method 4: Clear all OPcache statistics
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "\n=== OPcache Status ===\n";
        echo "Enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
        echo "Cache Full: " . ($status['cache_full'] ? 'YES' : 'NO') . "\n";
        echo "Cached Scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "Hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "Misses: " . $status['opcache_statistics']['misses'] . "\n";
    }
}

echo "\n=== Cache Clearing Complete ===\n";
echo "Wait 5-10 seconds before testing\n";
echo "Test URL: /?page=debug-models-test\n";

