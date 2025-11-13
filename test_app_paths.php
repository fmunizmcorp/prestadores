<?php
/**
 * Simple Path Diagnostic - No dependencies
 */

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');

while (ob_get_level()) {
    ob_end_clean();
}

echo "=== PATH DIAGNOSTIC ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

echo "=== Current Script Info ===\n";
echo "__FILE__: " . __FILE__ . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "getcwd(): " . getcwd() . "\n\n";

echo "=== Directory Contents (current dir) ===\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = __DIR__ . '/' . $file;
    $type = is_dir($path) ? '[DIR]' : '[FILE]';
    $size = is_file($path) ? filesize($path) : 0;
    echo "$type  $file" . (is_file($path) ? " ($size bytes)" : "") . "\n";
}

echo "\n=== Checking for Application Directories ===\n";
$check_dirs = ['src', 'config', 'database', 'public', '../src', '../config', '../database'];
foreach ($check_dirs as $dir) {
    $full_path = realpath(__DIR__ . '/' . $dir);
    if ($full_path && is_dir($full_path)) {
        echo "✅ FOUND: $dir => $full_path\n";
    } else {
        echo "❌ NOT FOUND: $dir\n";
    }
}

echo "\n=== Checking index.php Size ===\n";
if (file_exists(__DIR__ . '/index.php')) {
    echo "index.php: " . filesize(__DIR__ . '/index.php') . " bytes\n";
    
    // Check if it defines ROOT_PATH
    $content = file_get_contents(__DIR__ . '/index.php', false, null, 0, 2000);
    if (strpos($content, "define('ROOT_PATH'") !== false) {
        echo "✅ index.php contains ROOT_PATH definition\n";
        
        // Try to extract the ROOT_PATH value
        if (preg_match("/define\('ROOT_PATH',\s*([^)]+)\)/", $content, $matches)) {
            echo "ROOT_PATH defined as: " . $matches[1] . "\n";
        }
    }
}

echo "\n=== PHP include_path ===\n";
echo ini_get('include_path') . "\n";

echo "\n=== SERVER Variables ===\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'not set') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";

echo "\n=== END DIAGNOSTIC ===\n";

