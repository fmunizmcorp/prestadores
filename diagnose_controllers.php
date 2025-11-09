<?php
/**
 * Controller Error Diagnostics
 * Catches ALL errors including Fatal errors
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '0');

// Set up error handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "\n\n=== FATAL ERROR CAUGHT ===\n";
        echo "Type: " . $error['type'] . "\n";
        echo "Message: " . $error['message'] . "\n";
        echo "File: " . $error['file'] . "\n";
        echo "Line: " . $error['line'] . "\n";
    }
});

header('Content-Type: text/plain; charset=utf-8');
echo "=== CONTROLLER DIAGNOSTICS ===\n\n";

// Setup  
define('ROOT_PATH', __DIR__);
define('BASE_URL', '');
define('BASE_PATH', '');

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
    
    echo "Autoloader: Looking for $class at $file... ";
    if (file_exists($file)) {
        echo "FOUND\n";
        require $file;
    } else {
        echo "MISSING\n";
    }
});

echo "Loading config...\n";
require ROOT_PATH . '/config/database.php';
echo "✓ database.php loaded\n";

$controllers = [
    'ProjetoController',
    'AtividadeController',
    'FinanceiroController',
    'NotaFiscalController',
];

foreach ($controllers as $name) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "Testing: $name\n";
    echo str_repeat('=', 60) . "\n";
    
    $class = "App\\Controllers\\$name";
    
    try {
        echo "Instantiating...\n";
        $controller = new $class();
        echo "✓ Constructor SUCCESS\n";
        
        // Try calling index
        echo "Calling index()...\n";
        ob_start();
        $controller->index();
        $output = ob_get_clean();
        echo "✓ index() SUCCESS (output: " . strlen($output) . " bytes)\n";
        
    } catch (Error $e) {
        echo "✗ FATAL ERROR\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    } catch (Exception $e) {
        echo "✗ EXCEPTION\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "DIAGNOSTICS COMPLETE\n";
