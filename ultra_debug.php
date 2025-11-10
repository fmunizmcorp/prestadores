<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['usuario_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['usuario_perfil'] = 'admin';
$_SESSION['user_name'] = 'Admin Test';

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
    }
});

echo "<h2>Ultra Debug - Step by Step</h2>\n";

// Load database config
require ROOT_PATH . '/config/database.php';

echo "<h3>Step 1: Instantiate Projeto Model</h3>\n";
try {
    $projeto = new App\Models\Projeto();
    echo "✅ Projeto model created<br>\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
    die();
}

echo "<h3>Step 2: Instantiate BaseController</h3>\n";
try {
    // Can't instantiate abstract class, so skip this
    echo "⚠️ BaseController is abstract, skipping<br>\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Step 3: Check ProjetoController class definition</h3>\n";
try {
    $reflection = new ReflectionClass('App\Controllers\ProjetoController');
    echo "✅ Class exists<br>\n";
    echo "Is instantiable: " . ($reflection->isInstantiable() ? 'YES' : 'NO') . "<br>\n";
    
    $constructor = $reflection->getConstructor();
    if ($constructor) {
        echo "Has constructor: YES<br>\n";
        echo "Constructor is public: " . ($constructor->isPublic() ? 'YES' : 'NO') . "<br>\n";
    }
} catch (Throwable $e) {
    echo "❌ Reflection error: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Step 4: Try manual construction</h3>\n";
try {
    // Try without calling parent constructor
    echo "Creating instance...<br>\n";
    
    // Use reflection to bypass constructor
    $reflection = new ReflectionClass('App\Controllers\ProjetoController');
    $instance = $reflection->newInstanceWithoutConstructor();
    echo "✅ Instance created WITHOUT constructor<br>\n";
    
    // Now try normal instantiation
    $instance2 = new App\Controllers\ProjetoController();
    echo "✅ Instance created WITH constructor<br>\n";
    
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}
