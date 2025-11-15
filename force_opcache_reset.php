<?php
/**
 * Force OPcache Reset - Sprint 26
 * Força reset do OPcache após deploy do Database.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== FORCE OPCACHE RESET - SPRINT 26 ===\n\n";

// Verifica se OPcache está habilitado
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "✅ OPcache está HABILITADO\n";
    echo "   - Versão: " . phpversion('Zend OPcache') . "\n";
    echo "   - Memory usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    echo "   - Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "   - Hits: " . $status['opcache_statistics']['hits'] . "\n";
    echo "   - Misses: " . $status['opcache_statistics']['misses'] . "\n\n";
} else {
    echo "⚠️  OPcache não está disponível via PHP\n\n";
}

// Tenta resetar OPcache
echo "Tentando resetar OPcache...\n";

if (function_exists('opcache_reset')) {
    $reset = opcache_reset();
    if ($reset) {
        echo "✅ opcache_reset() executado com SUCESSO!\n";
    } else {
        echo "❌ opcache_reset() FALHOU (requer restart do PHP-FPM)\n";
    }
} else {
    echo "⚠️  opcache_reset() não disponível\n";
}

echo "\n";

// Tenta invalidar arquivo específico
$database_file = __DIR__ . '/src/Database.php';
echo "Tentando invalidar Database.php...\n";
echo "Path: $database_file\n";

if (file_exists($database_file)) {
    echo "✅ Arquivo existe\n";
    echo "   - Tamanho: " . filesize($database_file) . " bytes\n";
    echo "   - Modificado: " . date('Y-m-d H:i:s', filemtime($database_file)) . "\n";
    
    if (function_exists('opcache_invalidate')) {
        $invalidated = opcache_invalidate($database_file, true);
        if ($invalidated) {
            echo "✅ opcache_invalidate() SUCESSO para Database.php!\n";
        } else {
            echo "❌ opcache_invalidate() FALHOU\n";
        }
    } else {
        echo "⚠️  opcache_invalidate() não disponível\n";
    }
} else {
    echo "❌ Arquivo Database.php NÃO ENCONTRADO!\n";
}

echo "\n";

// Tenta invalidar DatabaseMigration.php também
$migration_file = __DIR__ . '/src/DatabaseMigration.php';
echo "Tentando invalidar DatabaseMigration.php...\n";

if (file_exists($migration_file)) {
    if (function_exists('opcache_invalidate')) {
        $invalidated = opcache_invalidate($migration_file, true);
        if ($invalidated) {
            echo "✅ opcache_invalidate() SUCESSO para DatabaseMigration.php!\n";
        } else {
            echo "❌ opcache_invalidate() FALHOU\n";
        }
    }
}

echo "\n=== PRÓXIMO PASSO ===\n";
echo "Aguarde 5-10 segundos e teste:\n";
echo "https://prestadores.clinfec.com.br/\n\n";

echo "Se o erro persistir, OPcache está em nível de infraestrutura.\n";
echo "Solução: Aguardar cache expirar OU reiniciar PHP via hPanel.\n";
