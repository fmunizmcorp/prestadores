<?php
// Test models and WRITE results to file
// Then we read the file separately

$logFile = __DIR__ . '/test_results_' . date('YmdHis') . '.txt';

ob_start();

echo "=== MODELS TEST ===\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) $class = substr($class, 4);
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($m) {
        return '/' . strtolower($m[1]) . '/';
    }, $file);
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

try {
    require_once CONFIG_PATH . '/database.php';
    require_once SRC_PATH . '/Database.php';
    
    $db = \App\Database::getInstance();
    echo "✅ Database connected\n\n";
    
    // Test Projeto
    echo "=== PROJETO ===\n";
    try {
        $p = new \App\Models\Projeto();
        $r = $p->all([], 1, 2);
        echo "✅ " . count($r) . " found\n\n";
    } catch (\Throwable $e) {
        echo "❌ " . $e->getMessage() . "\n\n";
    }
    
    // Test Atividade
    echo "=== ATIVIDADE ===\n";
    try {
        $a = new \App\Models\Atividade();
        $r = $a->all([], 1, 2);
        echo "✅ " . count($r) . " found\n\n";
    } catch (\Throwable $e) {
        echo "❌ " . $e->getMessage() . "\n\n";
    }
    
    // Test NotaFiscal
    echo "=== NOTAFISCAL ===\n";
    try {
        $n = new \App\Models\NotaFiscal();
        $r = $n->all([], 1, 2);
        echo "✅ " . count($r) . " found\n\n";
    } catch (\Throwable $e) {
        echo "❌ " . $e->getMessage() . "\n\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ FATAL: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
file_put_contents($logFile, $output);

echo "Test completed. Results written to: " . basename($logFile);
