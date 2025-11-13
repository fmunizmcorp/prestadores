<?php
// UNIQUE TEST FILE - Never cached before
$timestamp = date('Y-m-d H:i:s');
$phpver = PHP_VERSION;
$logfile = __DIR__ . '/MODELS_TEST_LOG.txt';

$output = "=== MODELS TEST EXECUTED ===\n";
$output .= "Timestamp: $timestamp\n";
$output .= "PHP Version: $phpver\n";
$output .= "File: " . __FILE__ . "\n\n";

// Step 1: Test basic PHP functionality
$output .= "[1] PHP Working: YES\n";

// Step 2: Check paths
$root = __DIR__;
$output .= "[2] Root Path: $root\n";
$output .= "[3] SRC exists: " . (is_dir($root . '/src') ? 'YES' : 'NO') . "\n";
$output .= "[4] Config exists: " . (is_dir($root . '/config') ? 'YES' : 'NO') . "\n\n";

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
    
    $output .= "[5] Autoloader registered\n";
    
    // Load database config and Database class
    if (file_exists(CONFIG_PATH . '/database.php')) {
        require_once CONFIG_PATH . '/database.php';
        $output .= "[6] Database config loaded\n";
    }
    
    if (file_exists(SRC_PATH . '/Database.php')) {
        require_once SRC_PATH . '/Database.php';
        $output .= "[7] Database class loaded\n";
    }
    
    // Test Database connection
    $db = \App\Database::getInstance();
    $version = $db->query("SELECT VERSION()")->fetchColumn();
    $output .= "[8] ✅ DB Connected: $version\n\n";
    
    // Test Projeto Model
    $output .= "=== TESTING PROJETO MODEL ===\n";
    $projeto = new \App\Models\Projeto();
    $projetos = $projeto->all([], 1, 5);
    $output .= "✅ Found " . count($projetos) . " projects\n";
    if (count($projetos) > 0) {
        $output .= "   First: " . ($projetos[0]['nome'] ?? 'N/A') . "\n";
    }
    $output .= "\n";
    
    // Test Atividade Model
    $output .= "=== TESTING ATIVIDADE MODEL ===\n";
    $atividade = new \App\Models\Atividade();
    $atividades = $atividade->all([], 1, 5);
    $output .= "✅ Found " . count($atividades) . " activities\n";
    if (count($atividades) > 0) {
        $output .= "   First: " . ($atividades[0]['titulo'] ?? 'N/A') . "\n";
    }
    $output .= "\n";
    
    // Test NotaFiscal Model
    $output .= "=== TESTING NOTAFISCAL MODEL ===\n";
    $nota = new \App\Models\NotaFiscal();
    $notas = $nota->all([], 1, 5);
    $output .= "✅ Found " . count($notas) . " notas fiscais\n";
    if (count($notas) > 0) {
        $output .= "   First: NF #" . ($notas[0]['numero'] ?? 'N/A') . "\n";
    }
    $output .= "\n";
    
    $output .= "=== ALL TESTS PASSED ===\n";
    $output .= "Models are working correctly!\n";
    
} catch (\Throwable $e) {
    $output .= "\n❌ ERROR: " . $e->getMessage() . "\n";
    $output .= "File: " . $e->getFile() . "\n";
    $output .= "Line: " . $e->getLine() . "\n";
    $output .= "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

// Write to log file
file_put_contents($logfile, $output);

// Output to browser
header('Content-Type: text/plain; charset=utf-8');
echo $output;
echo "\n=== Log saved to: MODELS_TEST_LOG.txt ===\n";
