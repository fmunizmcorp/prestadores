<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== OPcache Clear Script ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "✅ opcache_reset(): " . ($result ? "SUCCESS" : "FAILED") . "\n";
} else {
    echo "⚠️ opcache_reset() not available\n";
}

if (function_exists('opcache_invalidate')) {
    $index = __DIR__ . '/index.php';
    $result = opcache_invalidate($index, true);
    echo "✅ opcache_invalidate(index.php): " . ($result ? "SUCCESS" : "FAILED") . "\n";
} else {
    echo "⚠️ opcache_invalidate() not available\n";
}

echo "\n✅ Cache clear requested!\n";

// Self-destruct for security
@unlink(__FILE__);
echo "🔥 Script self-destructed\n";
