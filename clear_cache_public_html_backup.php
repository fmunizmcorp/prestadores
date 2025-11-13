<?php
/**
 * CLEAR PHP OPCACHE
 * Force PHP to reload all changed files
 */
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "CLEARING PHP OPCACHE\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Try to invalidate specific critical files
$critical_files = [
    __DIR__ . '/index.php',
    __DIR__ . '/src/Models/Projeto.php',
    __DIR__ . '/src/Models/Atividade.php',
    __DIR__ . '/src/Models/NotaFiscal.php',
];

if (function_exists('opcache_invalidate')) {
    echo "Invalidating critical files:\n";
    foreach ($critical_files as $file) {
        if (file_exists($file)) {
            opcache_invalidate($file, true);
            echo "  ✅ " . basename($file) . "\n";
        }
    }
    echo "\n";
}

// Touch index.php to force cache miss
if (file_exists(__DIR__ . '/index.php')) {
    touch(__DIR__ . '/index.php');
    echo "✅ Touched index.php (modified time updated)\n\n";
}

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache cleared successfully!\n\n";
        
        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status(false);
            if ($status) {
                echo "OPcache Status:\n";
                echo "  Enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
                echo "  Cache full: " . ($status['cache_full'] ? 'Yes' : 'No') . "\n";
                echo "  Restart pending: " . ($status['restart_pending'] ? 'Yes' : 'No') . "\n";
                echo "  Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
            }
        }
    } else {
        echo "⚠️  OPcache reset failed!\n";
        echo "This may happen if:\n";
        echo "  - Running from CLI (OPcache only works in web context)\n";
        echo "  - PHP-FPM needs restart\n";
        echo "  - Insufficient permissions\n";
    }
} else {
    echo "ℹ️  OPcache is not available or not enabled\n";
    echo "Check php.ini for opcache.enable settings\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "✅ CACHE CLEANUP COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
