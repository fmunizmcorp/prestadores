<?php
/**
 * Debug Script for Route Testing
 * Can be accessed directly: https://prestadores.clinfec.com.br/public/debug_route.php?route=projetos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "==========================================\n";
echo "DEBUG ROUTE TESTER\n";
echo "==========================================\n\n";

// Get route from query parameter
$route = $_GET['route'] ?? 'projetos';
echo "Testing route: /$route\n\n";

// Define paths
define('ROOT_PATH', dirname(__DIR__));

echo "ROOT_PATH: " . ROOT_PATH . "\n\n";

// Start session
session_start();

// Mock user session for testing
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Debug User';
$_SESSION['usuario_email'] = 'debug@test.com';
$_SESSION['usuario_perfil'] = 'master';
$_SESSION['perfil'] = 'master';

echo "Session initialized with master user\n\n";

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
        echo "✓ Loaded: $class\n";
    } else {
        echo "✗ NOT FOUND: $class\n";
        echo "  Expected at: $file\n";
    }
});

echo "\n--- Testing Database Connection ---\n";
try {
    $db = App\Database::getInstance();
    echo "✓ Database singleton created\n";
    
    $conn = $db->getConnection();
    echo "✓ Connection obtained\n";
    
    $stmt = $conn->query("SELECT DATABASE() as db");
    $result = $stmt->fetch();
    echo "✓ Connected to database: " . $result['db'] . "\n";
} catch (Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n--- Testing Controller ---\n";

try {
    $controllerMap = [
        'projetos' => 'App\\Controllers\\ProjetoController',
        'atividades' => 'App\\Controllers\\AtividadeController',
        'financeiro' => 'App\\Controllers\\FinanceiroController',
        'notas-fiscais' => 'App\\Controllers\\NotaFiscalController'
    ];
    
    if (!isset($controllerMap[$route])) {
        echo "✗ Unknown route: $route\n";
        exit(1);
    }
    
    $controllerClass = $controllerMap[$route];
    echo "Controller class: $controllerClass\n\n";
    
    echo "Step 1: Instantiating controller...\n";
    $controller = new $controllerClass();
    echo "✓ Controller instantiated\n\n";
    
    echo "Step 2: Calling index() method...\n";
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    echo "✓ index() method executed\n";
    echo "Output length: " . strlen($output) . " bytes\n";
    
    if (strlen($output) > 0) {
        echo "\n--- First 500 chars of output ---\n";
        echo substr($output, 0, 500) . "\n";
    }
    
    echo "\n✓✓✓ SUCCESS: Route /$route is working! ✓✓✓\n";
    
} catch (PDOException $e) {
    echo "\n✗✗✗ DATABASE ERROR ✗✗✗\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "\n✗✗✗ ERROR ✗✗✗\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n✗✗✗ PHP ERROR ✗✗✗\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n==========================================\n";
echo "END OF DEBUG\n";
echo "==========================================\n";
