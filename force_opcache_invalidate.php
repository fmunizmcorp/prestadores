<?php
/**
 * Force OPcache Invalidate - Touch files and invalidate cache
 */
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "FORCE OPCACHE INVALIDATE - Models\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$models = [
    __DIR__ . '/src/Models/Projeto.php',
    __DIR__ . '/src/Models/Atividade.php',
    __DIR__ . '/src/Models/NotaFiscal.php',
];

foreach ($models as $file) {
    echo "Processing: $file\n";
    
    if (!file_exists($file)) {
        echo "  ❌ File not found\n";
        continue;
    }
    
    echo "  📄 Current mtime: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    
    // Touch file to update modification time
    if (touch($file)) {
        echo "  ✅ Touched successfully\n";
        echo "  📄 New mtime: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    } else {
        echo "  ❌ Touch failed\n";
    }
    
    // Invalidate in OPcache
    if (function_exists('opcache_invalidate')) {
        if (opcache_invalidate($file, true)) {
            echo "  ✅ OPcache invalidated\n";
        } else {
            echo "  ⚠️  OPcache invalidate returned false\n";
        }
    }
    
    echo "\n";
}

// Full reset
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ Full OPcache RESET completed\n";
    } else {
        echo "❌ OPcache reset failed\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════\n";
