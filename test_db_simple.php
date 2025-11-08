<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database and models...\n\n";

define('ROOT_PATH', __DIR__);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

try {
    echo "1. Database getInstance()...";
    $db = App\Database::getInstance();
    echo " OK\n";
    
    echo "2. Get connection...";
    $conn = $db->getConnection();
    echo " OK\n";
    
    echo "3. Test query...";
    $stmt = $conn->query("SELECT 1");
    echo " OK\n";
    
    echo "4. Create Projeto model...";
    $projeto = new App\Models\Projeto();
    echo " OK\n";
    
    echo "\n✓ ALL TESTS PASSED\n";
    
} catch (Exception $e) {
    echo " FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
