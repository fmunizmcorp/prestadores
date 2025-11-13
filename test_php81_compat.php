<?php
/**
 * Teste de Compatibilidade PHP 8.1
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE PHP 8.1 COMPATIBILITY ===\n\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Script: " . __FILE__ . "\n\n";

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "[1] Testing Database Class\n";
try {
    $db = \App\Database::getInstance();
    echo "✅ Database::getInstance() works\n";
    echo "   Type: " . get_class($db) . "\n";
    
    // Test query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Database query works: " . print_r($result, true) . "\n";
} catch (\Throwable $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n[2] Testing Projeto Model\n";
try {
    $projeto = new \App\Models\Projeto();
    echo "✅ Projeto instantiated\n";
    
    $result = $projeto->all([], 1, 1);
    echo "✅ all() method works: " . count($result) . " results\n";
} catch (\Throwable $e) {
    echo "❌ Projeto Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n";
    foreach (explode("\n", $e->getTraceAsString()) as $line) {
        echo "   " . $line . "\n";
    }
}

echo "\n[3] Testing Atividade Model\n";
try {
    $atividade = new \App\Models\Atividade();
    echo "✅ Atividade instantiated\n";
    
    $result = $atividade->all([], 1, 1);
    echo "✅ all() method works: " . count($result) . " results\n";
} catch (\Throwable $e) {
    echo "❌ Atividade Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n[4] Testing NotaFiscal Model\n";
try {
    $nota = new \App\Models\NotaFiscal();
    echo "✅ NotaFiscal instantiated\n";
    
    $result = $nota->all([], 1, 1);
    echo "✅ all() method works: " . count($result) . " results\n";
} catch (\Throwable $e) {
    echo "❌ NotaFiscal Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
