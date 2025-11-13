<?php
/**
 * API Test - Diagnóstico de erro nas rotas de Projetos
 * Este script tenta replicar o que o Controller faz para identificar o erro exato
 */

// Habilitar exibição de erros APENAS para este script de diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "DIAGNÓSTICO DE ERRO - PROJETOS MODEL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Passo 1: Verificar autoloader
echo "[STEP 1] Verificando autoloader...\n";
$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo "✅ Autoloader encontrado: $autoload_path\n";
    require_once $autoload_path;
    echo "✅ Autoloader carregado\n";
} else {
    echo "❌ ERRO: Autoloader não encontrado em $autoload_path\n";
    exit(1);
}

echo "\n[STEP 2] Tentando instanciar Projeto Model...\n";
try {
    $projeto = new \App\Models\Projeto();
    echo "✅ Projeto Model instanciado com sucesso\n";
    echo "   Classe: " . get_class($projeto) . "\n";
} catch (\Error $e) {
    echo "❌ ERRO FATAL: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n[STEP 3] Verificando propriedade \$db...\n";
try {
    $reflection = new \ReflectionClass($projeto);
    $dbProperty = $reflection->getProperty('db');
    $dbProperty->setAccessible(true);
    $dbValue = $dbProperty->getValue($projeto);
    
    if ($dbValue === null) {
        echo "❌ ERRO: Propriedade \$db é NULL\n";
    } elseif ($dbValue instanceof \PDO) {
        echo "✅ \$db é uma instância de PDO válida\n";
        echo "   Tipo: " . get_class($dbValue) . "\n";
    } else {
        echo "⚠️  \$db não é NULL nem PDO, é: " . get_class($dbValue) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ ERRO ao verificar \$db: " . $e->getMessage() . "\n";
}

echo "\n[STEP 4] Testando método all() com filtros vazios...\n";
try {
    $resultado = $projeto->all([], 1, 5);
    echo "✅ Método all() executado com sucesso\n";
    echo "   Resultado: " . (is_array($resultado) ? count($resultado) . " registros" : "tipo: " . gettype($resultado)) . "\n";
    
    if (is_array($resultado) && count($resultado) > 0) {
        echo "\n   Primeiro registro (preview):\n";
        $primeiro = $resultado[0];
        foreach ($primeiro as $key => $value) {
            $preview = is_string($value) ? substr($value, 0, 50) : $value;
            echo "     - $key: $preview\n";
        }
    }
} catch (\Error $e) {
    echo "❌ ERRO FATAL no all(): " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} catch (\PDOException $e) {
    echo "❌ ERRO DE BANCO DE DADOS: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO no all(): " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n[STEP 5] Testando Database::getInstance() diretamente...\n";
try {
    $db = \App\Database::getInstance();
    echo "✅ Database::getInstance() executado\n";
    echo "   Tipo retornado: " . gettype($db) . "\n";
    
    if (is_object($db)) {
        echo "   Classe: " . get_class($db) . "\n";
        
        if ($db instanceof \PDO) {
            echo "   ✅ É uma instância de PDO\n";
            
            // Testar query simples
            $stmt = $db->query("SELECT 1 as test");
            $result = $stmt->fetch();
            echo "   ✅ Query de teste executada: " . print_r($result, true) . "\n";
        } else {
            echo "   ⚠️  NÃO é uma instância de PDO!\n";
        }
    }
} catch (\Error $e) {
    echo "❌ ERRO FATAL: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "DIAGNÓSTICO COMPLETO\n";
echo "═══════════════════════════════════════════════════════════\n";
