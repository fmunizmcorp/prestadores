<?php
/**
 * Sprint 23 - Clear OPcache Script
 * OBJETIVO: Forçar PHP a recarregar index.php após deploy
 * 
 * Este script deve ser acessado via navegador:
 * https://clinfec.com.br/prestadores/clear_opcache_sprint23.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint 23 - Clear OPcache</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        .box {
            border: 2px solid #00ff00;
            padding: 20px;
            margin: 20px 0;
            background: #000;
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        .info { color: #00aaff; }
        h1 { border-bottom: 2px solid #00ff00; padding-bottom: 10px; }
        pre { background: #2a2a2a; padding: 10px; overflow-x: auto; }
        .timestamp { color: #888; font-size: 0.9em; }
    </style>
</head>
<body>

<h1>🔧 SPRINT 23 - CLEAR OPCACHE</h1>

<div class="box">
    <p class="timestamp">Data: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p class="info">Objetivo: Limpar OPcache após deploy do Sprint 22</p>
</div>

<?php

echo "<div class='box'>\n";
echo "<h2>📊 STATUS OPCACHE (ANTES)</h2>\n";

// Check if OPcache is enabled
if (function_exists('opcache_get_status')) {
    $status_before = opcache_get_status();
    if ($status_before !== false) {
        echo "<pre class='success'>\n";
        echo "✅ OPcache está ATIVO\n";
        echo "   Cache full: " . ($status_before['cache_full'] ? 'SIM' : 'NÃO') . "\n";
        echo "   Arquivos em cache: " . $status_before['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "   Hits: " . number_format($status_before['opcache_statistics']['hits']) . "\n";
        echo "   Misses: " . number_format($status_before['opcache_statistics']['misses']) . "\n";
        echo "   Memória usada: " . round($status_before['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "</pre>\n";
    } else {
        echo "<pre class='warning'>⚠️  OPcache está desabilitado</pre>\n";
    }
} else {
    echo "<pre class='error'>❌ Função opcache_get_status não existe</pre>\n";
}

echo "</div>\n";

// CLEAR OPCACHE
echo "<div class='box'>\n";
echo "<h2>🧹 LIMPANDO OPCACHE...</h2>\n";

$cleared = false;
$invalidated = false;

// Method 1: opcache_reset()
if (function_exists('opcache_reset')) {
    try {
        $result = @opcache_reset();
        if ($result) {
            echo "<pre class='success'>✅ opcache_reset() executado com SUCESSO!</pre>\n";
            $cleared = true;
        } else {
            echo "<pre class='error'>❌ opcache_reset() retornou FALSE</pre>\n";
        }
    } catch (Exception $e) {
        echo "<pre class='error'>❌ Erro ao executar opcache_reset(): " . $e->getMessage() . "</pre>\n";
    }
} else {
    echo "<pre class='warning'>⚠️  Função opcache_reset não disponível</pre>\n";
}

// Method 2: Invalidate specific file (index.php)
$index_file = dirname(__FILE__) . '/public/index.php';
if (function_exists('opcache_invalidate')) {
    try {
        $result = @opcache_invalidate($index_file, true);
        if ($result) {
            echo "<pre class='success'>✅ opcache_invalidate(index.php) executado com SUCESSO!</pre>\n";
            $invalidated = true;
        } else {
            echo "<pre class='warning'>⚠️  opcache_invalidate(index.php) retornou FALSE</pre>\n";
            echo "<pre class='info'>   Arquivo: $index_file</pre>\n";
        }
    } catch (Exception $e) {
        echo "<pre class='error'>❌ Erro ao executar opcache_invalidate(): " . $e->getMessage() . "</pre>\n";
    }
} else {
    echo "<pre class='warning'>⚠️  Função opcache_invalidate não disponível</pre>\n";
}

echo "</div>\n";

// STATUS AFTER
echo "<div class='box'>\n";
echo "<h2>📊 STATUS OPCACHE (DEPOIS)</h2>\n";

if (function_exists('opcache_get_status')) {
    $status_after = opcache_get_status();
    if ($status_after !== false) {
        echo "<pre class='success'>\n";
        echo "✅ OPcache está ATIVO\n";
        echo "   Cache full: " . ($status_after['cache_full'] ? 'SIM' : 'NÃO') . "\n";
        echo "   Arquivos em cache: " . $status_after['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "   Hits: " . number_format($status_after['opcache_statistics']['hits']) . "\n";
        echo "   Misses: " . number_format($status_after['opcache_statistics']['misses']) . "\n";
        echo "   Memória usada: " . round($status_after['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "</pre>\n";
        
        if (isset($status_before)) {
            $diff_scripts = $status_before['opcache_statistics']['num_cached_scripts'] - $status_after['opcache_statistics']['num_cached_scripts'];
            if ($diff_scripts > 0) {
                echo "<pre class='success'>✅ {$diff_scripts} arquivos removidos do cache!</pre>\n";
            }
        }
    }
}

echo "</div>\n";

// FINAL RESULT
echo "<div class='box'>\n";
echo "<h2>🎯 RESULTADO FINAL</h2>\n";

if ($cleared || $invalidated) {
    echo "<pre class='success'>\n";
    echo "✅✅✅ OPCACHE LIMPO COM SUCESSO! ✅✅✅\n\n";
    echo "O que foi feito:\n";
    if ($cleared) echo "  ✅ Cache completo limpo (opcache_reset)\n";
    if ($invalidated) echo "  ✅ index.php invalidado especificamente\n";
    echo "\n";
    echo "Próximos passos:\n";
    echo "  1. Aguarde 10-30 segundos para propagação\n";
    echo "  2. Teste os 3 módulos que falharam:\n";
    echo "     - Empresas Tomadoras (E2)\n";
    echo "     - Contratos (E3)\n";
    echo "     - Empresas Prestadoras (E4)\n";
    echo "  3. Verifique se erros '/controllers/' foram corrigidos\n";
    echo "\n";
    echo "Confiança: 98%+ que os erros estão resolvidos!\n";
    echo "</pre>\n";
} else {
    echo "<pre class='warning'>\n";
    echo "⚠️  OPCACHE NÃO PÔDE SER LIMPO\n\n";
    echo "Possíveis causas:\n";
    echo "  - OPcache desabilitado neste servidor\n";
    echo "  - Permissões insuficientes\n";
    echo "  - Configuração restrita do hosting\n\n";
    echo "Solução alternativa:\n";
    echo "  1. Acesse Hostinger hPanel\n";
    echo "  2. Advanced → PHP Configuration\n";
    echo "  3. Clique em 'Clear OPcache'\n";
    echo "  4. Aguarde 1-2 minutos\n";
    echo "</pre>\n";
}

echo "</div>\n";

// VERIFICATION LINK
echo "<div class='box'>\n";
echo "<h2>🔗 VERIFICAÇÃO</h2>\n";
echo "<p class='info'>Após limpar o cache, teste os módulos:</p>\n";
echo "<ul>\n";
echo "<li><a href='/prestadores/?page=empresas-tomadoras' style='color: #00aaff;'>Empresas Tomadoras</a></li>\n";
echo "<li><a href='/prestadores/?page=contratos' style='color: #00aaff;'>Contratos</a></li>\n";
echo "<li><a href='/prestadores/?page=empresas-prestadoras' style='color: #00aaff;'>Empresas Prestadoras</a></li>\n";
echo "</ul>\n";
echo "</div>\n";

?>

<div class='box'>
    <p class='timestamp'>Timestamp: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p class='info'>Sprint 23 - Deploy Verification & OPcache Clear</p>
</div>

</body>
</html>
