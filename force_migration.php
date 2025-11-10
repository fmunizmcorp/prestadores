<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__FILE__));

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

require ROOT_PATH . '/config/database.php';

echo "<h2>Forçando Execução da Migration</h2>\n";

try {
    $migration = new App\DatabaseMigration();
    
    echo "<h3>Status Antes:</h3>\n";
    $statusBefore = $migration->getStatus();
    echo "DB Version: {$statusBefore['current_db_version']}<br>\n";
    echo "System Version: {$statusBefore['system_version']}<br>\n";
    echo "Up to date: " . ($statusBefore['up_to_date'] ? 'YES' : 'NO') . "<br>\n";
    
    echo "<h3>Executando Migrations...</h3>\n";
    $result = $migration->checkAndMigrate();
    
    if ($result['success']) {
        echo "✅ Migrations executadas com sucesso!<br>\n";
    } else {
        echo "❌ Erro: {$result['error']}<br>\n";
    }
    
    echo "<h3>Status Depois:</h3>\n";
    $statusAfter = $migration->getStatus();
    echo "DB Version: {$statusAfter['current_db_version']}<br>\n";
    echo "System Version: {$statusAfter['system_version']}<br>\n";
    echo "Up to date: " . ($statusAfter['up_to_date'] ? 'YES' : 'NO') . "<br>\n";
    
} catch (Exception $e) {
    echo "❌ Erro fatal: " . $e->getMessage() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}
