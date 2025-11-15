<?php
/**
 * Clear OPcache Sprint 57
 * Força invalidação após deploy do Database.php corrigido
 */
header('Content-Type: text/plain; charset=utf-8');

echo "=== CLEAR OPCACHE SPRINT 57 ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache resetado com sucesso!\n";
    } else {
        echo "❌ Falha ao resetar OPcache\n";
    }
    
    echo "\nStatus OPcache:\n";
    $status = opcache_get_status();
    if ($status) {
        echo "- Memory Used: " . number_format($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "- Cached Scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "- Hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "- Misses: " . $status['opcache_statistics']['misses'] . "\n";
    }
} else {
    echo "⚠️  OPcache não está habilitado\n";
}

echo "\n=== INVALIDAÇÃO MANUAL DE DATABASE.PHP ===\n";
$files_to_invalidate = [
    __DIR__ . '/src/Database.php',
    '/public_html/src/Database.php'
];

foreach ($files_to_invalidate as $file) {
    if (file_exists($file)) {
        if (function_exists('opcache_invalidate')) {
            if (opcache_invalidate($file, true)) {
                echo "✅ Invalidado: $file\n";
            } else {
                echo "⚠️  Não foi possível invalidar: $file\n";
            }
        }
    } else {
        echo "❌ Arquivo não existe: $file\n";
    }
}

echo "\n=== TESTE DE CARREGAMENTO DATABASE.PHP ===\n";
try {
    require_once __DIR__ . '/src/Database.php';
    echo "✅ Database.php carregado com sucesso\n";
    
    // Verificar se classe existe
    if (class_exists('App\\Database')) {
        echo "✅ Classe App\\Database existe\n";
        
        // Verificar métodos
        $methods = get_class_methods('App\\Database');
        $required_methods = ['prepare', 'query', 'exec', 'lastInsertId'];
        
        echo "\nMétodos disponíveis:\n";
        foreach ($required_methods as $method) {
            if (in_array($method, $methods)) {
                echo "✅ $method() - OK\n";
            } else {
                echo "❌ $method() - AUSENTE\n";
            }
        }
    } else {
        echo "❌ Classe App\\Database não encontrada\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao carregar Database.php: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUÍDO ===\n";
echo "Aguarde 30-60 segundos e teste os módulos manualmente.\n";
