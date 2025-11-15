<?php
/**
 * DIAGNOSTIC SCRIPT - Check actual errors for modules
 * This script simulates loading each problematic module to capture real errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set up environment
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

// Setup PSR-4 Autoloader (same as index.php)
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

session_start();

// Fake authentication for testing
$_SESSION['usuario_id'] = 1;
$_SESSION['perfil'] = 'master';

echo "<h1>500 Error Diagnostics</h1>";
echo "<p>Testing modules that returned 500 errors...</p><hr>";

// Test 1: Empresas Prestadoras
echo "<h2>Test 1: Empresas Prestadoras</h2>";
try {
    require_once ROOT_PATH . '/src/Models/EmpresaPrestadora.php';
    $model = new \App\Models\EmpresaPrestadora();
    echo "<p style='color:green'>✓ Model loaded successfully</p>";
    
    // Try to call all() method
    $_GET['page'] = '2';
    $_GET['limit'] = '20';
    $result = $model->all([], 2, 20);
    echo "<p style='color:green'>✓ all() method executed successfully</p>";
    echo "<p>Returned " . count($result) . " records</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 2: Serviços
echo "<h2>Test 2: Serviços</h2>";
try {
    require_once ROOT_PATH . '/src/Models/Servico.php';
    $model = new \App\Models\Servico();
    echo "<p style='color:green'>✓ Model loaded successfully</p>";
    
    $_GET['page'] = '2';
    $_GET['limit'] = '20';
    $result = $model->all([], 2, 20);
    echo "<p style='color:green'>✓ all() method executed successfully</p>";
    echo "<p>Returned " . count($result) . " records</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 3: Projetos
echo "<h2>Test 3: Projetos</h2>";
try {
    require_once ROOT_PATH . '/src/Models/Projeto.php';
    $model = new \App\Models\Projeto();
    echo "<p style='color:green'>✓ Model loaded successfully</p>";
    
    $_GET['page'] = '2';
    $_GET['limit'] = '20';
    $result = $model->all([], 2, 20);
    echo "<p style='color:green'>✓ all() method executed successfully</p>";
    echo "<p>Returned " . count($result) . " records</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 4: ProjetoController
echo "<h2>Test 4: ProjetoController</h2>";
try {
    require_once ROOT_PATH . '/src/Controllers/BaseController.php';
    require_once ROOT_PATH . '/src/Controllers/ProjetoController.php';
    $controller = new \App\Controllers\ProjetoController();
    echo "<p style='color:green'>✓ Controller loaded successfully</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>✗ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

echo "<h2>Diagnostic Complete</h2>";
echo "<p>Check above for specific errors in each module.</p>";
