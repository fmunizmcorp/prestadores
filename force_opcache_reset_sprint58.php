<?php
/**
 * FORCE OPCACHE RESET - SPRINT 58
 * Limpeza agressiva de TODOS os caches
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== FORCE OPCACHE RESET SPRINT 58 ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Método 1: Reset OPcache
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "1. opcache_reset(): " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
} else {
    echo "1. opcache_reset(): ⚠️  Function not available\n";
}

// Método 2: Invalidar todos os scripts em cache
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(true);
    if ($status && isset($status['scripts'])) {
        $count = 0;
        foreach ($status['scripts'] as $script => $info) {
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($script, true);
                $count++;
            }
        }
        echo "2. Invalidated scripts: ✅ $count files\n";
    } else {
        echo "2. Invalidated scripts: ⚠️  No scripts in cache\n";
    }
} else {
    echo "2. Invalidated scripts: ⚠️  Function not available\n";
}

// Método 3: Invalidar Database.php especificamente
$database_paths = [
    __DIR__ . '/src/Database.php',
    '/public_html/src/Database.php',
    realpath(__DIR__ . '/src/Database.php')
];

echo "3. Invalidating Database.php:\n";
foreach ($database_paths as $path) {
    if ($path && file_exists($path)) {
        if (function_exists('opcache_invalidate')) {
            $result = opcache_invalidate($path, true);
            echo "   ✅ $path: " . ($result ? "SUCCESS" : "FAILED") . "\n";
        }
    } else {
        echo "   ⚠️  $path: NOT FOUND\n";
    }
}

// Método 4: Tocar arquivo para forçar reload
$database_file = __DIR__ . '/src/Database.php';
if (file_exists($database_file)) {
    touch($database_file);
    echo "4. touch(Database.php): ✅ File touched\n";
    clearstatcache(true, $database_file);
    echo "5. clearstatcache(): ✅ Stat cache cleared\n";
} else {
    echo "4. touch(Database.php): ❌ File not found\n";
}

// Método 5: Limpar stat cache de TODOS os arquivos PHP
clearstatcache(true);
echo "6. clearstatcache(all): ✅ All stat caches cleared\n";

// Método 6: Desabilitar OPcache temporariamente (se possível)
if (function_exists('ini_set')) {
    @ini_set('opcache.enable', '0');
    echo "7. Disable OPcache: ✅ Attempted (may require restart)\n";
}

echo "\n=== STATUS PÓS-LIMPEZA ===\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "OPcache Status:\n";
        echo "- Enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
        echo "- Memory Used: " . number_format($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "- Cached Scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "- Hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "- Misses: " . $status['opcache_statistics']['misses'] . "\n";
    }
}

echo "\n=== TESTE DE CARREGAMENTO ===\n";
try {
    // Forçar recarregamento sem cache
    $file = __DIR__ . '/src/Database.php';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo "✅ Database.php lido: " . strlen($content) . " bytes\n";
        
        // Verificar métodos
        if (strpos($content, 'function prepare') !== false) {
            echo "✅ Método prepare() PRESENTE no arquivo\n";
        } else {
            echo "❌ Método prepare() AUSENTE no arquivo\n";
        }
        
        // Tentar carregar classe
        require_once $file;
        if (class_exists('App\\Database')) {
            echo "✅ Classe App\\Database carregada\n";
            
            $methods = get_class_methods('App\\Database');
            if (in_array('prepare', $methods)) {
                echo "✅ Método prepare() DISPONÍVEL na classe\n";
            } else {
                echo "❌ Método prepare() NÃO DISPONÍVEL na classe\n";
                echo "   Métodos disponíveis: " . implode(', ', $methods) . "\n";
            }
        } else {
            echo "❌ Classe App\\Database NÃO CARREGADA\n";
        }
    } else {
        echo "❌ Arquivo Database.php não encontrado\n";
    }
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUSÃO ===\n";
echo "Cache limpo com TODOS os métodos disponíveis.\n";
echo "Se ainda houver erro, pode ser:\n";
echo "1. PHP-FPM precisa ser restartado (servidor precisa fazer isso)\n";
echo "2. Há múltiplas versões do PHP rodando\n";
echo "3. Arquivo está sendo carregado de outro local\n";
echo "\nAguarde 60 segundos e teste os módulos novamente.\n";
