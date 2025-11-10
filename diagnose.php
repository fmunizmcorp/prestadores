<?php
session_start();
define('ROOT_PATH', dirname(__FILE__));
define('BASE_URL', 'https://prestadores.clinfec.com.br');

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

echo "<h2>Diagnóstico do Sistema</h2>\n\n";

// Test 1: Database connection
echo "<h3>1. Database Connection</h3>\n";
try {
    require ROOT_PATH . '/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "✅ Database connected<br>\n";
    
    // Check tables
    $tables = ['projetos', 'atividades', 'notas_fiscais'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo ($exists ? "✅" : "❌") . " Table: $table<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
}

// Test 2: Models
echo "<h3>2. Models Loading</h3>\n";
$models = ['Projeto', 'Atividade', 'NotaFiscal'];
foreach ($models as $model) {
    try {
        $class = "App\\Models\\$model";
        $instance = new $class();
        echo "✅ Model $model loaded<br>\n";
    } catch (Throwable $e) {
        echo "❌ Model $model error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
    }
}

// Test 3: Controllers
echo "<h3>3. Controllers Loading</h3>\n";
$controllers = ['ProjetoController', 'AtividadeController', 'NotaFiscalController'];
foreach ($controllers as $ctrl) {
    try {
        $class = "App\\Controllers\\$ctrl";
        $instance = new $class();
        echo "✅ Controller $ctrl loaded<br>\n";
    } catch (Throwable $e) {
        echo "❌ Controller $ctrl error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
    }
}

// Test 4: BaseController
echo "<h3>4. BaseController</h3>\n";
try {
    require_once ROOT_PATH . '/src/Controllers/BaseController.php';
    echo "✅ BaseController exists<br>\n";
} catch (Throwable $e) {
    echo "❌ BaseController error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
}
