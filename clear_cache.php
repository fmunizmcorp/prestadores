<?php
/**
 * CLEAR PHP OPCACHE
 * Force PHP to reload all changed files
 */
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "CLEARING PHP OPCACHE\n";
echo "═══════════════════════════════════════════════════════════\n\n";

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
echo "✅ COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n";
