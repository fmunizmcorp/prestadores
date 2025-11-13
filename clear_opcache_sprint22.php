<?php
/**
 * SPRINT 22 - Limpar OPcache
 * Executar após deploy: https://clinfec.com.br/clear_opcache_sprint22.php
 */
header('Content-Type: text/plain; charset=utf-8');

echo "=== SPRINT 22 - LIMPAR OPCACHE ===

";

if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "✅ opcache_reset(): " . ($result ? 'SUCCESS' : 'FAILED') . "
";
} else {
    echo "⚠️  opcache_reset() não disponível
";
}

if (function_exists('opcache_invalidate')) {
    $files = [
        '/home/u673902663/domains/clinfec.com.br/public_html/public/index.php'
    ];
    
    foreach ($files as $file) {
        $result = opcache_invalidate($file, true);
        echo "✅ opcache_invalidate($file): " . ($result ? 'SUCCESS' : 'FAILED') . "
";
    }
} else {
    echo "⚠️  opcache_invalidate() não disponível
";
}

echo "
🎯 OPcache limpo! Aguarde 30 segundos e teste:
";
echo "   https://clinfec.com.br/?page=empresas-tomadoras
";
echo "
Data/Hora: " . date('Y-m-d H:i:s') . "
";
?>