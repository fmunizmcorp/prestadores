<?php
/**
 * FORCE RELOAD - Clear all caches and restart
 */
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "FORCE RELOAD - ALL CACHES\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache reset\n";
}

// Clear APCu
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cleared\n";
}

// Check file timestamps
echo "\n📁 File timestamps:\n";
$files = [
    'src/Models/NotaFiscal.php',
    'src/Models/Projeto.php',
    'src/Models/Atividade.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $mtime = filemtime($file);
        $size = filesize($file);
        echo "  ✅ $file: " . date('Y-m-d H:i:s', $mtime) . " ({$size} bytes)\n";
    } else {
        echo "  ❌ $file: NOT FOUND\n";
    }
}

// Try to include and check class
echo "\n🔍 Loading NotaFiscal class...\n";
require_once 'config/database.php';
require_once 'src/Models/NotaFiscal.php';

$class = new ReflectionClass('App\\Models\\NotaFiscal');
$methods = $class->getMethods();
echo "✅ NotaFiscal class loaded\n";
echo "✅ Methods found: " . count($methods) . "\n";

// List some key methods
$key_methods = ['all', 'emitir', 'getItens', 'getTotalizadoresPorTipo'];
echo "\nKey methods:\n";
foreach ($key_methods as $method) {
    if ($class->hasMethod($method)) {
        echo "  ✅ $method()\n";
    } else {
        echo "  ❌ $method() NOT FOUND\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "✅ RELOAD COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n";
