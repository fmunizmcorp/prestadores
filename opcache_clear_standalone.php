<?php
/**
 * OPcache Clear Standalone - Sprint 26
 * Script standalone que NÃO carrega nenhum arquivo do sistema
 * Apenas limpa OPcache e testa
 */

// NÃO carregar nada do sistema para evitar erro

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>OPcache Clear - Sprint 26</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        .info { color: #00aaff; }
        pre { background: #000; padding: 10px; border: 1px solid #333; }
    </style>
</head>
<body>
    <h1>🚀 SPRINT 26 - OPCACHE CLEAR</h1>
    <pre>
<?php

echo "=== DIAGNÓSTICO OPCACHE ===\n\n";

// 1. Verificar se OPcache está habilitado
if (function_exists('opcache_get_status')) {
    $status = @opcache_get_status();
    if ($status) {
        echo "<span class='success'>✅ OPcache HABILITADO</span>\n";
        echo "   Versão: " . phpversion('Zend OPcache') . "\n";
        echo "   Memory: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB usado\n";
        echo "   Scripts em cache: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "   Hits: " . number_format($status['opcache_statistics']['hits']) . "\n";
        echo "   Misses: " . number_format($status['opcache_statistics']['misses']) . "\n";
        echo "   Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
    } else {
        echo "<span class='warning'>⚠️  OPcache status não disponível</span>\n";
    }
} else {
    echo "<span class='error'>❌ OPcache NÃO está disponível</span>\n";
}

echo "\n=== TENTANDO LIMPAR CACHE ===\n\n";

// 2. Tentar reset COMPLETO
if (function_exists('opcache_reset')) {
    $result = @opcache_reset();
    if ($result) {
        echo "<span class='success'>✅ opcache_reset() SUCESSO!</span>\n";
    } else {
        echo "<span class='error'>❌ opcache_reset() FALHOU (precisa restart PHP-FPM)</span>\n";
    }
} else {
    echo "<span class='warning'>⚠️  opcache_reset() não disponível</span>\n";
}

echo "\n";

// 3. Tentar invalidar arquivos específicos
$files_to_invalidate = [
    __DIR__ . '/src/Database.php',
    __DIR__ . '/src/DatabaseMigration.php',
    __DIR__ . '/public/index.php'
];

echo "=== INVALIDANDO ARQUIVOS ESPECÍFICOS ===\n\n";

foreach ($files_to_invalidate as $file) {
    $basename = basename($file);
    echo "Arquivo: $basename\n";
    
    if (file_exists($file)) {
        echo "  ✅ Existe\n";
        echo "  📏 Tamanho: " . filesize($file) . " bytes\n";
        echo "  🕐 Modificado: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
        
        if (function_exists('opcache_invalidate')) {
            $invalidated = @opcache_invalidate($file, true);
            if ($invalidated) {
                echo "  <span class='success'>✅ Cache invalidado!</span>\n";
            } else {
                echo "  <span class='error'>❌ Falha ao invalidar</span>\n";
            }
        } else {
            echo "  <span class='warning'>⚠️  opcache_invalidate() não disponível</span>\n";
        }
    } else {
        echo "  <span class='error'>❌ Arquivo não encontrado!</span>\n";
    }
    echo "\n";
}

echo "=== VERIFICANDO Database.php ===\n\n";

$db_file = __DIR__ . '/src/Database.php';
if (file_exists($db_file)) {
    $content = file_get_contents($db_file);
    
    // Verificar se métodos proxy existem
    if (strpos($content, 'public function exec(') !== false) {
        echo "<span class='success'>✅ Método exec() EXISTE no arquivo!</span>\n";
    } else {
        echo "<span class='error'>❌ Método exec() NÃO ENCONTRADO!</span>\n";
    }
    
    if (strpos($content, 'public function query(') !== false) {
        echo "<span class='success'>✅ Método query() EXISTE no arquivo!</span>\n";
    } else {
        echo "<span class='error'>❌ Método query() NÃO ENCONTRADO!</span>\n";
    }
    
    if (strpos($content, 'public function prepare(') !== false) {
        echo "<span class='success'>✅ Método prepare() EXISTE no arquivo!</span>\n";
    } else {
        echo "<span class='error'>❌ Método prepare() NÃO ENCONTRADO!</span>\n";
    }
    
    // Contar linhas
    $lines = substr_count($content, "\n");
    echo "\n📊 Total de linhas: $lines\n";
    echo "📏 Tamanho: " . strlen($content) . " bytes\n";
}

echo "\n=== INFORMAÇÕES DO SISTEMA ===\n\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

echo "\n=== PRÓXIMOS PASSOS ===\n\n";
echo "1. Aguarde 5-10 segundos\n";
echo "2. Teste: <a href='/' style='color: #00ff00;'>https://prestadores.clinfec.com.br/</a>\n";
echo "3. Se erro persistir:\n";
echo "   - OPcache está em nível de infraestrutura\n";
echo "   - Solução: Reiniciar PHP via hPanel\n";
echo "   - Ou aguardar expiração natural (24-48h)\n";

?>
    </pre>
    
    <hr style="border-color: #333;">
    
    <h2>🧪 TESTE RÁPIDO</h2>
    <p><a href="/" style="font-size: 20px; color: #00ff00; background: #003300; padding: 10px 20px; text-decoration: none; border: 2px solid #00ff00;">
        ▶ TESTAR SISTEMA AGORA
    </a></p>
    
</body>
</html>
<?php
// Tentar limpar buffer de saída PHP
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
?>
