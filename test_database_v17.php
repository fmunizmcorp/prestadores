<?php
/**
 * Test Database V17 - Após limpeza cache
 * Teste direto sem passar pelo index.php
 */

// Desabilitar OPcache completamente
if (function_exists('opcache_reset')) {
    opcache_reset();
}

clearstatcache(true);

// Definir paths
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');
define('CONFIG_PATH', __DIR__ . '/config');

// Carregar Database diretamente
require_once SRC_PATH . '/Database.php';

echo "=== TESTE DATABASE V17 ===\n\n";

echo "[1] Arquivo Database.php carregado\n";
echo "    Path: " . SRC_PATH . "/Database.php\n";
echo "    Existe: " . (file_exists(SRC_PATH . "/Database.php") ? "SIM" : "NÃO") . "\n";
echo "    Tamanho: " . filesize(SRC_PATH . "/Database.php") . " bytes\n";
echo "    Modificado: " . date('Y-m-d H:i:s', filemtime(SRC_PATH . "/Database.php")) . "\n\n";

echo "[2] Verificar classe Database\n";
if (class_exists('App\\Database')) {
    echo "    ✅ Classe existe\n\n";
    
    $reflection = new ReflectionClass('App\\Database');
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    echo "[3] Métodos públicos (" . count($methods) . "):\n";
    foreach ($methods as $method) {
        if (!$method->isStatic()) {
            echo "    - " . $method->getName() . "()\n";
        }
    }
    
    echo "\n[4] Verificar método exec():\n";
    if ($reflection->hasMethod('exec')) {
        echo "    ✅ Método exec() EXISTS!\n";
        $execMethod = $reflection->getMethod('exec');
        echo "    - Linha: " . $execMethod->getStartLine() . "\n";
        echo "    - Arquivo: " . $execMethod->getFileName() . "\n";
    } else {
        echo "    ❌ Método exec() NÃO ENCONTRADO!\n";
    }
    
} else {
    echo "    ❌ Classe NÃO existe\n";
}

echo "\n[5] Tentar instanciar:\n";
try {
    $db = App\Database::getInstance();
    echo "    ✅ getInstance() funcionou\n";
    echo "    Tipo: " . get_class($db) . "\n";
    
    // Verificar se tem método exec
    if (method_exists($db, 'exec')) {
        echo "    ✅ Método exec() disponível no objeto\n";
    } else {
        echo "    ❌ Método exec() NÃO disponível no objeto\n";
        
        // Listar métodos disponíveis
        echo "    Métodos disponíveis:\n";
        $methods = get_class_methods($db);
        foreach ($methods as $m) {
            echo "        - $m()\n";
        }
    }
    
} catch (Exception $e) {
    echo "    ❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
