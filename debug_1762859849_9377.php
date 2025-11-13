<?php
/**
 * Teste Direto do Projeto Model - COM exibição de erros
 */

// FORÇAR exibição de todos os erros
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Output como texto
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "TESTE DIRETO - PROJETO MODEL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Script: " . __FILE__ . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Step 1: Load autoloader
echo "[1] Loading autoloader...\n";
$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    die("❌ Autoloader not found: $autoloader\n");
}
require_once $autoloader;
echo "✅ Autoloader loaded\n\n";

// Step 2: Instantiate Projeto
echo "[2] Instantiating Projeto Model...\n";
try {
    $projeto = new \App\Models\Projeto();
    echo "✅ SUCCESS: Projeto instantiated\n";
    echo "   Class: " . get_class($projeto) . "\n\n";
} catch (\Throwable $e) {
    echo "❌ FATAL ERROR during instantiation:\n";
    echo "   Type: " . get_class($e) . "\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "   Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    die("\n");
}

// Step 3: Test all() method
echo "[3] Calling all() method...\n";
try {
    $result = $projeto->all([], 1, 5);
    echo "✅ SUCCESS: all() executed\n";
    echo "   Results: " . count($result) . " records\n";
    
    if (count($result) > 0) {
        echo "\n   First record keys:\n";
        foreach (array_keys($result[0]) as $key) {
            echo "     - $key\n";
        }
    }
} catch (\Throwable $e) {
    echo "❌ ERROR in all() method:\n";
    echo "   Type: " . get_class($e) . "\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "   Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n";
