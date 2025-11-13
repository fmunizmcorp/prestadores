<?php
/**
 * FORCE CLEAR OPCACHE - Sprint 23
 * Script de emergência para limpar cache
 */

// Disable all caching for this script
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: text/plain; charset=utf-8');

echo "SPRINT 23 - FORCE CLEAR OPCACHE\n";
echo str_repeat("=", 80) . "\n\n";

$success = false;

// Method 1: opcache_reset()
if (function_exists('opcache_reset')) {
    echo "[1] Tentando opcache_reset()...\n";
    $result = @opcache_reset();
    if ($result) {
        echo "    ✅ SUCESSO!\n";
        $success = true;
    } else {
        echo "    ❌ Falhou\n";
    }
} else {
    echo "[1] opcache_reset() não disponível\n";
}

// Method 2: Invalidate specific files
$files_to_invalidate = [
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/public/index.php',
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/src/DatabaseMigration.php',
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/src/Database.php',
];

if (function_exists('opcache_invalidate')) {
    echo "\n[2] Invalidando arquivos específicos...\n";
    foreach ($files_to_invalidate as $file) {
        $result = @opcache_invalidate($file, true);
        $basename = basename($file);
        if ($result) {
            echo "    ✅ $basename\n";
            $success = true;
        } else {
            echo "    ⚠️  $basename (pode não estar em cache)\n";
        }
    }
} else {
    echo "\n[2] opcache_invalidate() não disponível\n";
}

// Method 3: Touch files to force reload
echo "\n[3] Modificando timestamps dos arquivos...\n";
foreach ($files_to_invalidate as $file) {
    if (file_exists($file)) {
        @touch($file);
        echo "    ✅ " . basename($file) . "\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
if ($success) {
    echo "✅ CACHE LIMPO COM SUCESSO!\n\n";
    echo "Aguarde 10-30 segundos e teste novamente:\n";
    echo "  https://clinfec.com.br/prestadores/\n";
} else {
    echo "⚠️  NÃO FOI POSSÍVEL LIMPAR O CACHE AUTOMATICAMENTE\n\n";
    echo "SOLUÇÃO MANUAL:\n";
    echo "1. Acesse: https://hpanel.hostinger.com\n";
    echo "2. Advanced → PHP Configuration\n";
    echo "3. Clear OPcache\n";
}

echo "\n" . date('Y-m-d H:i:s') . "\n";
