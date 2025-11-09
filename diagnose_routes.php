<?php
/**
 * Route Diagnostics - Capture actual error messages
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain; charset=utf-8');

echo "=== ROUTE DIAGNOSTICS ===\n\n";

// Setup paths and autoloader
define('ROOT_PATH', __DIR__);
define('BASE_URL', '');

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/src/Core/Autoloader.php';

// Initialize autoloader
$autoloader = new App\Core\Autoloader();
$autoloader->register();

$routes = [
    'projetos' => 'App\Controllers\ProjetoController',
    'atividades' => 'App\Controllers\AtividadeController',
    'financeiro' => 'App\Controllers\FinanceiroController',
    'notas-fiscais' => 'App\Controllers\NotaFiscalController',
];

foreach ($routes as $route => $class) {
    echo "Testing: /$route\n";
    echo "Class: $class\n";
    
    // Check if class file exists
    $classPath = ROOT_PATH . '/src/Controllers/' . basename(str_replace('App\Controllers\\', '', $class)) . '.php';
    echo "File: " . (file_exists($classPath) ? "EXISTS" : "MISSING") . "\n";
    
    // Try to instantiate
    try {
        $controller = new $class();
        echo "Result: ✓ SUCCESS\n";
        
        // Try to call index method
        try {
            ob_start();
            $controller->index();
            $output = ob_get_clean();
            echo "Index method: ✓ SUCCESS (output: " . strlen($output) . " bytes)\n";
        } catch (Exception $e) {
            ob_end_clean();
            echo "Index method: ✗ FAILED\n";
            echo "Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
        
    } catch (Exception $e) {
        echo "Result: ✗ FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    } catch (Error $e) {
        echo "Result: ✗ FATAL ERROR\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

echo "=== DIAGNOSTICS COMPLETE ===\n";
