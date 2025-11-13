<?php
// Clear OPcache after public/index.php update - Sprint 19
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "OPcache Reset: " . ($result ? "SUCCESS" : "FAILED") . "\n";
} else {
    echo "OPcache not available\n";
}

// Also invalidate specific files
$files_to_invalidate = [
    __DIR__ . '/public/index.php',
    __DIR__ . '/index.php',
    __DIR__ . '/config/config.php',
];

foreach ($files_to_invalidate as $file) {
    if (file_exists($file) && function_exists('opcache_invalidate')) {
        opcache_invalidate($file, true);
        echo "Invalidated: $file\n";
    }
}

echo "\nCache cleared successfully at " . date('Y-m-d H:i:s');
?>
