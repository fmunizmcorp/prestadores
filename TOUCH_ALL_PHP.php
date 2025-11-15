<?php
header('Content-Type: text/plain');

echo "=== TOUCHING ALL PHP FILES ===\n\n";

$count = 0;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        touch($file->getPathname());
        $count++;
        if ($count <= 20) {
            echo "✅ " . str_replace(__DIR__, '', $file->getPathname()) . "\n";
        }
    }
}

echo "\n✅ Total: $count arquivos touched\n";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache reset\n";
}

clearstatcache(true);
echo "✅ Stat cache cleared\n";

echo "\n=== DONE! Try login now ===\n";
