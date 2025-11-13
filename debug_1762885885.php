<?php
/**
 * Standalone Debug Script - Never Cached
 * Direct execution bypasses index.php routing
 */

// Disable all caching
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

echo "=== PRESTADORES DEBUG - MODELS TEST ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Script: " . __FILE__ . "\n\n";

// Define paths
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

// Setup autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// Load database config
$dbConfig = require __DIR__ . '/config/database.php';

// Load Database class
require_once SRC_PATH . '/Database.php';

echo "=== Environment Setup Complete ===\n\n";

// Test 1: Database Connection
try {
    echo "[0] Testing Database Connection...\n";
    $db = \App\Database::getInstance();
    if ($db instanceof PDO) {
        echo "✅ Database connected (returned PDO object)\n";
        $stmt = $db->query("SELECT VERSION()");
        $version = $stmt->fetchColumn();
        echo "   MySQL Version: $version\n\n";
    } else {
        echo "❌ Database getInstance() did not return PDO\n";
        echo "   Returned type: " . gettype($db) . "\n\n";
    }
} catch (\Throwable $e) {
    echo "❌ Database ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

// Test 2: Projeto Model
try {
    echo "[1] Testing Projeto Model...\n";
    $projeto = new \App\Models\Projeto();
    echo "   ✓ Projeto instantiated\n";
    
    $result = $projeto->all([], 1, 5);
    echo "✅ SUCCESS: Projeto->all() returned " . count($result) . " results\n";
    
    if (!empty($result)) {
        $first = $result[0];
        echo "   First project: #" . $first['id'] . " - " . ($first['nome'] ?? $first['codigo'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n";
    $trace = $e->getTraceAsString();
    $lines = explode("\n", $trace);
    foreach (array_slice($lines, 0, 5) as $line) {
        echo "     " . $line . "\n";
    }
    echo "\n";
}

// Test 3: Atividade Model
try {
    echo "[2] Testing Atividade Model...\n";
    $atividade = new \App\Models\Atividade();
    echo "   ✓ Atividade instantiated\n";
    
    $result = $atividade->all([], 1, 5);
    echo "✅ SUCCESS: Atividade->all() returned " . count($result) . " results\n";
    
    if (!empty($result)) {
        $first = $result[0];
        echo "   First activity: #" . $first['id'] . " - " . ($first['nome'] ?? $first['descricao'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n";
    $trace = $e->getTraceAsString();
    $lines = explode("\n", $trace);
    foreach (array_slice($lines, 0, 5) as $line) {
        echo "     " . $line . "\n";
    }
    echo "\n";
}

// Test 4: NotaFiscal Model
try {
    echo "[3] Testing NotaFiscal Model...\n";
    $nota = new \App\Models\NotaFiscal();
    echo "   ✓ NotaFiscal instantiated\n";
    
    $result = $nota->all([], 1, 5);
    echo "✅ SUCCESS: NotaFiscal->all() returned " . count($result) . " results\n";
    
    if (!empty($result)) {
        $first = $result[0];
        echo "   First invoice: #" . $first['id'] . " - Numero: " . ($first['numero'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n";
    $trace = $e->getTraceAsString();
    $lines = explode("\n", $trace);
    foreach (array_slice($lines, 0, 5) as $line) {
        echo "     " . $line . "\n";
    }
    echo "\n";
}

echo "=== DEBUG COMPLETE ===\n";
echo "This output proves the Models are working or shows exact errors.\n";

