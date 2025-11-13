<?php
/**
 * Debug Models - Test instantiation and basic methods
 */

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "MODEL DEBUG SCRIPT\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Require autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "[1/3] Testing NotaFiscal Model\n";
echo "─────────────────────────────────────\n";
try {
    $model = new \App\Models\NotaFiscal();
    echo "✅ Model instantiated successfully\n";
    
    // Try calling all() method
    $result = $model->all([], 1, 1);
    echo "✅ all() method executed: " . count($result) . " results\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n[2/3] Testing Projeto Model\n";
echo "─────────────────────────────────────\n";
try {
    $model = new \App\Models\Projeto();
    echo "✅ Model instantiated successfully\n";
    
    $result = $model->all([], 1, 1);
    echo "✅ all() method executed: " . count($result) . " results\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n[3/3] Testing Atividade Model\n";
echo "─────────────────────────────────────\n";
try {
    $model = new \App\Models\Atividade();
    echo "✅ Model instantiated successfully\n";
    
    $result = $model->all([], 1, 1);
    echo "✅ all() method executed: " . count($result) . " results\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "DEBUG COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n";
