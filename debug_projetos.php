<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_debug.log');

echo "<pre>";
echo "=== DEBUG: Rota /projetos ===\n\n";

define('ROOT_PATH', __DIR__);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

try {
    echo "1. Testando Database connection...\n";
    $db = App\Database::getInstance();
    echo "✓ Database OK\n\n";
    
    echo "2. Testando Projeto Model...\n";
    $projeto = new App\Models\Projeto();
    echo "✓ Projeto Model OK\n\n";
    
    echo "3. Testando ProjetoController instantiation...\n";
    $controller = new App\Controllers\ProjetoController();
    echo "✓ ProjetoController OK\n\n";
    
    echo "4. Testando método index()...\n";
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    echo "✓ index() executou sem erros\n";
    echo "Output length: " . strlen($output) . " bytes\n";
    
} catch (Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fim do debug ===\n";
echo "</pre>";
