<?php
// MODELS TEST - Embedded in clear_cache.php
header('Content-Type: text/plain; charset=utf-8');

$timestamp = date('Y-m-d H:i:s');
$phpver = PHP_VERSION;

echo "=== MODELS TEST EXECUTED ===\n";
echo "Timestamp: $timestamp\n";
echo "PHP Version: $phpver\n";
echo "File: " . __FILE__ . "\n\n";

// Step 1: Test basic PHP functionality
echo "[1] PHP Working: YES\n";

// Step 2: Check paths
$root = __DIR__;
echo "[2] Root Path: $root\n";
echo "[3] SRC exists: " . (is_dir($root . '/src') ? 'YES' : 'NO') . "\n";
echo "[4] Config exists: " . (is_dir($root . '/config') ? 'YES' : 'NO') . "\n\n";

// Step 3: Try to load Database
try {
    define('ROOT_PATH', $root);
    define('SRC_PATH', $root . '/src');
    define('CONFIG_PATH', $root . '/config');
    
    // Custom autoloader
    spl_autoload_register(function ($class) {
        if (strpos($class, 'App\\') === 0) {
            $class = substr($class, 4);
        }
        $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
        
        // Transform path: Models -> models, Controllers -> controllers
        $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($m) {
            return '/' . strtolower($m[1]) . '/';
        }, $file);
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    });
    
    echo "[5] Autoloader registered\n";
    
    // Load database config and Database class
    if (file_exists(CONFIG_PATH . '/database.php')) {
        require_once CONFIG_PATH . '/database.php';
        echo "[6] Database config loaded\n";
    }
    
    if (file_exists(SRC_PATH . '/Database.php')) {
        require_once SRC_PATH . '/Database.php';
        echo "[7] Database class loaded\n";
    }
    
    // Test Database connection
    $db = \App\Database::getInstance();
    $version = $db->query("SELECT VERSION()")->fetchColumn();
    echo "[8] ✅ DB Connected: $version\n\n";
    
    // Test Projeto Model
    echo "=== TESTING PROJETO MODEL ===\n";
    $projeto = new \App\Models\Projeto();
    $projetos = $projeto->all([], 1, 5);
    echo "✅ Found " . count($projetos) . " projects\n";
    if (count($projetos) > 0) {
        echo "   First: " . ($projetos[0]['nome'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
    // Test Atividade Model
    echo "=== TESTING ATIVIDADE MODEL ===\n";
    $atividade = new \App\Models\Atividade();
    $atividades = $atividade->all([], 1, 5);
    echo "✅ Found " . count($atividades) . " activities\n";
    if (count($atividades) > 0) {
        echo "   First: " . ($atividades[0]['titulo'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
    // Test NotaFiscal Model
    echo "=== TESTING NOTAFISCAL MODEL ===\n";
    $nota = new \App\Models\NotaFiscal();
    $notas = $nota->all([], 1, 5);
    echo "✅ Found " . count($notas) . " notas fiscais\n";
    if (count($notas) > 0) {
        echo "   First: NF #" . ($notas[0]['numero'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
    echo "=== ALL TESTS PASSED ===\n";
    echo "Models are working correctly!\n";
    
} catch (\Throwable $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}
