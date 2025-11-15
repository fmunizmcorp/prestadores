<?php
/**
 * LIMPEZA AGRESSIVA DE TODOS OS CACHES
 * Execute este arquivo DIRETAMENTE no navegador
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== LIMPEZA AGRESSIVA DE CACHE ===\n\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// 1. OPcache Reset
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "✅ opcache_reset(): " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} else {
    echo "⚠️  opcache_reset() não disponível\n";
}

// 2. OPcache Invalidate em TODOS os arquivos PHP
if (function_exists('opcache_invalidate')) {
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            opcache_invalidate($file->getPathname(), true);
            $count++;
        }
    }
    echo "✅ opcache_invalidate(): $count arquivos invalidados\n";
}

// 3. Clear stat cache
clearstatcache(true);
echo "✅ clearstatcache()\n";

// 4. Limpar sessões antigas
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}
session_start();
session_regenerate_id(true);
echo "✅ Sessão regenerada\n";

// 5. Forçar headers de no-cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

echo "\n=== CACHE LIMPO! ===\n";
echo "\nAGORA TESTE:\n";
echo "1. https://prestadores.clinfec.com.br/?page=login\n";
echo "2. Login: master@clinfec.com.br / password\n";
