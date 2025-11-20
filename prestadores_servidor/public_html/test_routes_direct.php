<?php
/**
 * Direct Route Test - Bypasses .htaccess
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "==========================================\n";
echo "DIRECT ROUTE TEST\n";
echo "==========================================\n\n";

session_start();

// Mock session
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Test User';
$_SESSION['usuario_email'] = 'test@test.com';
$_SESSION['usuario_perfil'] = 'master';
$_SESSION['perfil'] = 'master';

define('ROOT_PATH', __DIR__);
define('BASE_URL', 'https://prestadores.clinfec.com.br');
define('BASE_PATH', '');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
        echo "✓ Loaded: $class\n";
    }
});

$route = $_GET['route'] ?? 'projetos';

echo "\nTesting route: /$route\n";
echo "==========================================\n\n";

try {
    $controllerMap = [
        'projetos' => 'ProjetoController',
        'atividades' => 'AtividadeController',
        'financeiro' => 'FinanceiroController',
        'notas-fiscais' => 'NotaFiscalController'
    ];
    
    if (!isset($controllerMap[$route])) {
        die("Unknown route: $route\n");
    }
    
    $controllerClass = 'App\\Controllers\\' . $controllerMap[$route];
    
    echo "Step 1: Testing database connection...\n";
    $db = App\Database::getInstance();
    echo "✓ Database connected\n\n";
    
    echo "Step 2: Creating controller instance...\n";
    $controller = new $controllerClass();
    echo "✓ Controller created: $controllerClass\n\n";
    
    echo "Step 3: Calling index() method...\n";
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    echo "✓ index() executed successfully!\n";
    echo "Output length: " . strlen($output) . " bytes\n";
    
    if (strlen($output) > 0) {
        echo "\n--- First 500 chars ---\n";
        echo substr($output, 0, 500) . "...\n";
    }
    
    echo "\n✓✓✓ SUCCESS ✓✓✓\n";
    
} catch (PDOException $e) {
    echo "\n✗✗✗ DATABASE ERROR ✗✗✗\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "SQL State: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nQuery that failed:\n";
    if (isset($e->errorInfo)) {
        print_r($e->errorInfo);
    }
    echo "\nStack:\n" . $e->getTraceAsString() . "\n";
    
} catch (Exception $e) {
    echo "\n✗✗✗ ERROR ✗✗✗\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack:\n" . $e->getTraceAsString() . "\n";
    
} catch (Error $e) {
    echo "\n✗✗✗ PHP ERROR ✗✗✗\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack:\n" . $e->getTraceAsString() . "\n";
}

echo "\n==========================================\n";
echo "END OF TEST\n";
echo "==========================================\n";
