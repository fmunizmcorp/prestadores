<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
} else {
    echo "⚠️ OPcache not available\n";
}

if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cache cleared\n";
}

echo "✅ Cache cleanup complete\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
