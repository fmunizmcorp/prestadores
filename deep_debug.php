<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

define('ROOT_PATH', dirname(__FILE__));
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
        echo "✅ Loaded: $class<br>\n";
    } else {
        echo "❌ Not found: $file<br>\n";
    }
});

echo "<h2>Deep Debug - Projetos Route</h2>\n\n";

// Test 1: Load Database
echo "<h3>1. Database</h3>\n";
try {
    require ROOT_PATH . '/config/database.php';
    $db = App\Database::getInstance();
    echo "✅ Database instance created<br>\n";
    $conn = $db->getConnection();
    echo "✅ Connection obtained<br>\n";
} catch (Throwable $e) {
    echo "❌ Database error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "Trace: " . htmlspecialchars($e->getTraceAsString()) . "<br>\n";
}

// Test 2: Session
echo "<h3>2. Session State</h3>\n";
echo "Session ID: " . session_id() . "<br>\n";
echo "User ID in session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "<br>\n";
echo "User role in session: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET') . "<br>\n";

// Set a test session
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin Test';
echo "✅ Test session set (user_id=1, role=admin)<br>\n";

// Test 3: Load ProjetoController
echo "<h3>3. ProjetoController Instantiation</h3>\n";
try {
    $controller = new App\Controllers\ProjetoController();
    echo "✅ ProjetoController instantiated<br>\n";
    
    // Test 4: Call index method
    echo "<h3>4. ProjetoController::index()</h3>\n";
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    if (strlen($output) > 0) {
        echo "✅ index() executed, output length: " . strlen($output) . " bytes<br>\n";
        echo "<details><summary>Show Output</summary><pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre></details>\n";
    } else {
        echo "⚠️ index() executed but no output<br>\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Controller error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
    echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>\n";
}

// Test 5: BaseController
echo "<h3>5. BaseController Methods</h3>\n";
try {
    $reflection = new ReflectionClass('App\Controllers\BaseController');
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    echo "Public methods:<br>\n";
    foreach ($methods as $method) {
        echo "- " . $method->getName() . "<br>\n";
    }
} catch (Throwable $e) {
    echo "❌ Reflection error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
}
