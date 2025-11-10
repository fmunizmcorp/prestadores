<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['usuario_id'] = 1;
$_SESSION['user_role'] = 'master';
$_SESSION['usuario_perfil'] = 'master';

define('ROOT_PATH', dirname(__FILE__));
define('BASE_URL', 'https://prestadores.clinfec.com.br');
define('BASE_PATH', '');

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require ROOT_PATH . '/config/database.php';

echo "<h2>Test ProjetoController::index()</h2>\n";

try {
    $controller = new App\Controllers\ProjetoController();
    echo "✅ Controller created<br>\n";
    
    echo "Calling index()...<br>\n";
    
    // Capture output
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    echo "✅ index() executed successfully<br>\n";
    echo "Output length: " . strlen($output) . " bytes<br>\n";
    
    if (strlen($output) > 0) {
        echo "<h3>Output Preview:</h3>\n";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre>\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Error in index(): " . htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
    echo "<h3>Stack Trace:</h3>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}
