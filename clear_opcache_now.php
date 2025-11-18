<?php
header('Content-Type: text/plain');

echo "Limpando OPcache...\n\n";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ opcache_reset() executado!\n";
} else {
    echo "❌ opcache_reset() não disponível\n";
}

if (function_exists('opcache_invalidate')) {
    $files_to_clear = [
        __DIR__ . '/index.php',
        __DIR__ . '/src/Controllers/AuthController.php',
        __DIR__ . '/src/Models/Usuario.php'
    ];
    
    foreach ($files_to_clear as $file) {
        if (file_exists($file)) {
            opcache_invalidate($file, true);
            echo "✅ Invalidado: " . basename($file) . "\n";
        }
    }
}

clearstatcache(true);
echo "✅ clearstatcache() executado!\n";

echo "\nCache limpo! Agora teste o sistema.\n";
