<?php
/**
 * Test Contratos module specifically
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

// Setup autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/app.php';

// Start session
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['perfil'] = 'master';
$_SESSION['csrf_token'] = 'test_token';

echo "<h1>Contratos Diagnostic</h1><hr>";

// Test Contrato Model
echo "<h2>Test 1: Contrato Model</h2>";
try {
    require_once SRC_PATH . '/Models/Contrato.php';
    $model = new \App\Models\Contrato();
    echo "<p style='color:green'>✓ Model loaded</p>";
    
    $result = $model->all([], 1, 20);
    echo "<p style='color:green'>✓ all() executed successfully</p>";
    echo "<p>Returned " . count($result) . " records</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test ContratoController
echo "<h2>Test 2: ContratoController</h2>";
try {
    require_once SRC_PATH . '/Controllers/BaseController.php';
    require_once SRC_PATH . '/Controllers/ContratoController.php';
    
    // Capture output
    ob_start();
    $controller = new \App\Controllers\ContratoController();
    $output = ob_get_clean();
    
    echo "<p style='color:green'>✓ Controller instantiated</p>";
    
    if (!empty($output)) {
        echo "<p style='color:orange'>⚠️  Controller produced output during instantiation:</p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
    // Try to call index method
    ob_start();
    $controller->index();
    $indexOutput = ob_get_clean();
    
    echo "<p style='color:green'>✓ index() method executed</p>";
    
    if (strlen($indexOutput) > 0) {
        echo "<p>Output length: " . strlen($indexOutput) . " bytes</p>";
    }
    
} catch (\Throwable $e) {
    $output = ob_get_clean();
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    
    if (!empty($output)) {
        echo "<p>Output before error:</p>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
    }
}

echo "<h2>Diagnostic Complete</h2>";
