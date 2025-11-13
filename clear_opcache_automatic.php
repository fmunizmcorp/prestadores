<?php
/**
 * SPRINT 20 - CLEAR OPCACHE AUTOMÁTICO
 * Upload via FTP e acesse: https://clinfec.com.br/clear_opcache_automatic.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "🧹 SPRINT 20 - LIMPEZA AUTOMÁTICA DE OPCACHE\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar se OPcache está habilitado
if (!function_exists('opcache_reset')) {
    echo "❌ OPcache NÃO está habilitado neste servidor\n";
    echo "   Solução: Entre em contato com Hostinger para habilitar\n";
    exit;
}

// Status antes
echo "📊 STATUS ANTES DA LIMPEZA:\n";
echo "---\n";
$status_before = opcache_get_status();
if ($status_before) {
    echo "✓ OPcache habilitado: SIM\n";
    echo "✓ Scripts em cache: " . $status_before['num_cached_scripts'] . "\n";
    echo "✓ Memória usada: " . number_format($status_before['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
} else {
    echo "⚠️  Não foi possível obter status\n";
}
echo "\n";

// Tentar limpar OPcache
echo "🧹 LIMPANDO OPCACHE...\n";
$result = opcache_reset();

if ($result) {
    echo "✅ SUCESSO! OPcache foi limpo completamente\n\n";
    
    // Status depois
    echo "📊 STATUS APÓS LIMPEZA:\n";
    echo "---\n";
    sleep(1); // Pequena pausa
    $status_after = opcache_get_status();
    if ($status_after) {
        echo "✓ Scripts em cache: " . $status_after['num_cached_scripts'] . "\n";
        echo "✓ Memória usada: " . number_format($status_after['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    }
    echo "\n";
    
    echo "═══════════════════════════════════════════════════════════════════════════\n";
    echo "✅ PRÓXIMO PASSO: TESTAR O SISTEMA\n";
    echo "═══════════════════════════════════════════════════════════════════════════\n\n";
    echo "Acesse estas URLs e verifique se renderizam páginas (NÃO em branco):\n\n";
    echo "1. https://clinfec.com.br/prestadores/?page=empresas-tomadoras\n";
    echo "2. https://clinfec.com.br/prestadores/?page=contratos\n";
    echo "3. https://clinfec.com.br/prestadores/?page=projetos\n";
    echo "4. https://clinfec.com.br/prestadores/?page=empresas-prestadoras\n\n";
    echo "✅ ESPERADO: Todas as páginas devem mostrar listas com dados\n";
    echo "❌ SE AINDA ESTIVER EM BRANCO: Aguarde 5 minutos e teste novamente\n\n";
    
} else {
    echo "❌ ERRO: Não foi possível limpar OPcache\n";
    echo "   Possíveis razões:\n";
    echo "   - Função opcache_reset() desabilitada\n";
    echo "   - Permissões insuficientes\n";
    echo "   - OPcache em modo restrito\n\n";
    echo "💡 SOLUÇÃO ALTERNATIVA:\n";
    echo "   1. Acesse: https://hpanel.hostinger.com\n";
    echo "   2. Vá em: Advanced → PHP Configuration\n";
    echo "   3. Encontre seção 'OPcache'\n";
    echo "   4. Clique em 'Clear OPcache'\n";
    echo "   5. Aguarde 2-3 minutos\n";
    echo "   6. Teste as URLs acima\n\n";
}

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "📝 Arquivo gerado por: deploy_sprint20_complete.py\n";
echo "🚀 Sprint 20 - Fix ROOT_PATH (dirname(__DIR__))\n";
echo "═══════════════════════════════════════════════════════════════════════════\n";
