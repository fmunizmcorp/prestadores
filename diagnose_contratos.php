<?php
/**
 * Diagnose Contratos Module Specifically
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/app.php';

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['perfil'] = 'master';
$_SESSION['csrf_token'] = 'test_token';

echo "<h1>Contratos Module Diagnostic</h1><hr>";

// Test 1: Load Contrato Model
echo "<h2>Test 1: Load Contrato Model</h2>";
try {
    require_once ROOT_PATH . '/src/Models/Contrato.php';
    $model = new \App\Models\Contrato();
    echo "<p style='color:green'>✓ Model loaded successfully</p>";
    
    // Test all() method
    $_GET['page'] = '1';
    $_GET['limit'] = '20';
    $result = $model->all([], 1, 20);
    echo "<p style='color:green'>✓ all() method works</p>";
    echo "<p>Returned: " . count($result) . " records</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 2: Load ContratoController
echo "<h2>Test 2: Load ContratoController</h2>";
try {
    require_once ROOT_PATH . '/src/Controllers/ContratoController.php';
    echo "<p style='color:green'>✓ Controller class loaded</p>";
    
    // Try to instantiate
    $controller = new \App\Controllers\ContratoController();
    echo "<p style='color:green'>✓ Controller instantiated</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 3: Check related models
echo "<h2>Test 3: Check Related Models</h2>";
$related_models = [
    'EmpresaTomadora',
    'EmpresaPrestadora', 
    'Servico',
    'ContratoFinanceiro'
];

foreach ($related_models as $model_name) {
    echo "<h3>{$model_name}</h3>";
    try {
        $class = "\\App\\Models\\{$model_name}";
        require_once ROOT_PATH . "/src/Models/{$model_name}.php";
        $instance = new $class();
        echo "<p style='color:green'>✓ {$model_name} OK</p>";
    } catch (\Throwable $e) {
        echo "<p style='color:red'>✗ {$model_name} ERROR: " . $e->getMessage() . "</p>";
    }
}

echo "<hr><h2>Diagnostic Complete</h2>";
