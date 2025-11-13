<?php
/**
 * Diagnóstico de Erro - Força exibição de erro para debugging
 * Este arquivo será acessado diretamente via URL especial
 */

// FORÇAR exibição de erros (sobrescreve configuração)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Configurar para exibir como texto plano
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "DIAGNÓSTICO COMPLETO - PROJETOS MODEL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Informações do ambiente
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OPcache Enabled: " . (function_exists('opcache_get_status') && opcache_get_status() ? 'Sim' : 'Não') . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "Script: " . __FILE__ . "\n\n";

// Tentar carregar autoloader
echo "─────────────────────────────────────\n";
echo "[1] Carregando Autoloader\n";
echo "─────────────────────────────────────\n";

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "✅ Autoloader encontrado: $autoloadPath\n";
    require_once $autoloadPath;
    echo "✅ Autoloader carregado\n\n";
} else {
    echo "❌ ERRO: Autoloader não encontrado\n";
    echo "   Procurado em: $autoloadPath\n";
    exit(1);
}

// Verificar se classe Database existe
echo "─────────────────────────────────────\n";
echo "[2] Verificando classe Database\n";
echo "─────────────────────────────────────\n";

if (class_exists('App\Database')) {
    echo "✅ Classe App\\Database encontrada\n";
    
    try {
        $db = \App\Database::getInstance();
        echo "✅ Database::getInstance() executado\n";
        echo "   Tipo: " . gettype($db) . "\n";
        if (is_object($db)) {
            echo "   Classe: " . get_class($db) . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ ERRO ao instanciar Database: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Classe App\\Database NÃO encontrada\n";
}

echo "\n";

// Verificar se BaseModel existe (NÃO deveria existir)
echo "─────────────────────────────────────\n";
echo "[3] Verificando BaseModel (NÃO deve existir)\n";
echo "─────────────────────────────────────\n";

if (class_exists('App\Models\BaseModel')) {
    echo "⚠️  PROBLEMA: BaseModel EXISTE (não deveria!)\n";
} else {
    echo "✅ CORRETO: BaseModel não existe\n";
}

echo "\n";

// Tentar instanciar Projeto Model
echo "─────────────────────────────────────\n";
echo "[4] Instanciando Projeto Model\n";
echo "─────────────────────────────────────\n";

try {
    echo "Tentando: new \\App\\Models\\Projeto()\n";
    $projeto = new \App\Models\Projeto();
    echo "✅ Projeto instanciado com sucesso!\n";
    echo "   Classe: " . get_class($projeto) . "\n";
    
    // Verificar propriedades
    $reflection = new ReflectionClass($projeto);
    echo "   Propriedades:\n";
    foreach ($reflection->getProperties() as $prop) {
        echo "     - " . $prop->getName() . "\n";
    }
    
    // Tentar chamar método all()
    echo "\n   Tentando chamar método all()...\n";
    $result = $projeto->all([], 1, 1);
    echo "   ✅ Método all() executado!\n";
    echo "   Retornou: " . count($result) . " registro(s)\n";
    
} catch (\Error $e) {
    echo "❌ ERRO FATAL:\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . "\n";
    echo "   Linha: " . $e->getLine() . "\n";
    echo "\n   Stack Trace:\n";
    foreach ($e->getTrace() as $i => $trace) {
        echo "   #$i ";
        if (isset($trace['file'])) {
            echo $trace['file'] . ':' . $trace['line'];
        }
        if (isset($trace['function'])) {
            echo ' - ' . $trace['function'] . '()';
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO:\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . "\n";
    echo "   Linha: " . $e->getLine() . "\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "FIM DO DIAGNÓSTICO\n";
echo "═══════════════════════════════════════════════════════════\n";
