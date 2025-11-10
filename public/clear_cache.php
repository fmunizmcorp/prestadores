<?php
/**
 * Clear OPcache Script
 * Direct access to force cache clearing
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== CLEARING OPCACHE ===\n\n";

// Check if OPcache is enabled
if (function_exists('opcache_get_status')) {
    $status = @opcache_get_status();
    echo "OPcache Status: " . ($status ? "ENABLED" : "DISABLED") . "\n";
    
    if ($status) {
        echo "Cache Full: " . ($status['cache_full'] ? "YES" : "NO") . "\n";
        echo "Cached Scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "Memory Used: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n\n";
    }
} else {
    echo "OPcache: NOT AVAILABLE\n\n";
}

// Try to reset OPcache
echo "Attempting to reset OPcache...\n";
if (function_exists('opcache_reset')) {
    $result = @opcache_reset();
    echo "opcache_reset(): " . ($result ? "SUCCESS" : "FAILED") . "\n";
} else {
    echo "opcache_reset(): NOT AVAILABLE\n";
}

// Try to invalidate specific files
echo "\nInvalidating key files...\n";
$files = [
    __DIR__ . '/index.php',
    dirname(__DIR__) . '/src/Controllers/ProjetoController.php',
    dirname(__DIR__) . '/src/Controllers/AtividadeController.php',
    dirname(__DIR__) . '/src/Controllers/FinanceiroController.php',
    dirname(__DIR__) . '/src/Controllers/NotaFiscalController.php',
];

if (function_exists('opcache_invalidate')) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            $result = @opcache_invalidate($file, true);
            echo "  " . basename($file) . ": " . ($result ? "✓" : "✗") . "\n";
        }
    }
} else {
    echo "opcache_invalidate(): NOT AVAILABLE\n";
}

echo "\n=== DONE ===\n";
echo "\nNow try accessing the routes again.\n";
