<?php
/**
 * NUCLEAR OPCACHE CLEAR - Sprint 23
 * Última tentativa de limpar OPcache
 */

header('Content-Type: text/plain; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

echo "SPRINT 23 - NUCLEAR OPCACHE CLEAR\n";
echo str_repeat("=", 80) . "\n\n";

// Method 1: Disable OPcache for this request
if (function_exists('ini_set')) {
    @ini_set('opcache.enable', '0');
    echo "[1] ✅ OPcache desabilitado para esta requisição\n";
}

// Method 2: Reset all
if (function_exists('opcache_reset')) {
    $result = @opcache_reset();
    echo "[2] opcache_reset(): " . ($result ? "✅ SUCESSO" : "❌ Falhou") . "\n";
}

// Method 3: Invalidate ALL PHP files in the system
$files = [
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/public/index.php',
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/src/DatabaseMigration.php',
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/src/Database.php',
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/src/Controllers/EmpresaTomadoraController.php',
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/src/Controllers/ContratoController.php',
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores/src/Controllers/EmpresaPrestadoraController.php',
];

echo "\n[3] Invalidando arquivos específicos...\n";
$invalidated = 0;
if (function_exists('opcache_invalidate')) {
    foreach ($files as $file) {
        if (@opcache_invalidate($file, true)) {
            echo "    ✅ " . basename($file) . "\n";
            $invalidated++;
        } else {
            echo "    ⚠️  " . basename($file) . "\n";
        }
    }
    echo "    Total invalidado: $invalidated/" . count($files) . "\n";
}

// Method 4: Get status
echo "\n[4] Status do OPcache:\n";
if (function_exists('opcache_get_status')) {
    $status = @opcache_get_status(false);
    if ($status) {
        echo "    Enabled: " . ($status['opcache_enabled'] ? 'SIM' : 'NÃO') . "\n";
        echo "    Cache full: " . ($status['cache_full'] ? 'SIM' : 'NÃO') . "\n";
        echo "    Arquivos em cache: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    } else {
        echo "    ⚠️  Status não disponível\n";
    }
}

// Method 5: Try to compile files WITHOUT cache
echo "\n[5] Forçando recompilação...\n";
if (function_exists('opcache_compile_file')) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            // First invalidate
            @opcache_invalidate($file, true);
            // Then compile fresh
            @opcache_compile_file($file);
            echo "    ✅ " . basename($file) . " recompilado\n";
        }
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ TODAS AS TENTATIVAS EXECUTADAS!\n\n";
echo "Se o erro persistir, é necessário:\n";
echo "1. Acesse hPanel Hostinger\n";
echo "2. Advanced → PHP Configuration\n";
echo "3. Clear OPcache manualmente\n";
echo "4. Ou aguarde ~5-10 minutos para cache expirar\n";
echo "\nTeste novamente: https://clinfec.com.br/prestadores/\n";
echo date('Y-m-d H:i:s') . "\n";
