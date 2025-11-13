<?php
/**
 * Force Clear Cache - Aggressive OPcache Reset
 * 
 * This script forces a complete OPcache reset by:
 * 1. Resetting OPcache completely
 * 2. Invalidating specific files
 * 3. Clearing APCu cache
 * 4. Touching files to force recompilation
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== FORCE CACHE CLEAR ===\n\n";

// 1. Reset OPcache completely
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache RESET successful\n";
    } else {
        echo "❌ OPcache RESET failed\n";
    }
    
    // Get OPcache status
    $status = opcache_get_status();
    echo "   - Cache enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
    echo "   - Cache full: " . ($status['cache_full'] ? 'YES' : 'NO') . "\n";
    echo "   - Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
} else {
    echo "⚠️  OPcache not available\n";
}

echo "\n";

// 2. Invalidate specific Model files
$models = [
    __DIR__ . '/src/Models/NotaFiscal.php',
    __DIR__ . '/src/Models/Projeto.php',
    __DIR__ . '/src/Models/Atividade.php',
];

echo "Invalidating Model files:\n";
foreach ($models as $file) {
    if (file_exists($file)) {
        if (function_exists('opcache_invalidate')) {
            $result = opcache_invalidate($file, true);
            echo ($result ? "✅" : "❌") . " $file\n";
        }
        
        // Touch file to update modification time
        touch($file);
        echo "   ↻ Touched file (mtime updated)\n";
    } else {
        echo "❌ File not found: $file\n";
    }
}

echo "\n";

// 3. Clear APCu cache
if (function_exists('apcu_clear_cache')) {
    if (apcu_clear_cache()) {
        echo "✅ APCu cache cleared\n";
    } else {
        echo "❌ APCu cache clear failed\n";
    }
} else {
    echo "⚠️  APCu not available\n";
}

echo "\n";

// 4. Final OPcache reset
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ Final OPcache RESET\n";
}

echo "\n=== CACHE CLEAR COMPLETE ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
