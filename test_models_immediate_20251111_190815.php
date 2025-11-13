<?php
/**
 * IMMEDIATE TEST - Never cached before
 * Tests Models directly without routing
 */

// Force no cache
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
while (ob_get_level()) ob_end_clean();

echo "=== IMMEDIATE MODELS TEST ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Setup paths
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

echo "ROOT_PATH: " . ROOT_PATH . "\n";
echo "SRC_PATH exists: " . (is_dir(SRC_PATH) ? 'YES' : 'NO') . "\n";
echo "CONFIG_PATH exists: " . (is_dir(CONFIG_PATH) ? 'YES' : 'NO') . "\n\n";

// Autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($m) {
        return '/' . strtolower($m[1]) . '/';
    }, $file);
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Load config
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Database.php';

echo "=== Testing Database Connection ===\n";
try {
    $db = \App\Database::getInstance();
    if ($db instanceof PDO) {
        echo "✅ Database connected (PDO object returned)\n";
        $stmt = $db->query("SELECT VERSION()");
        echo "MySQL Version: " . $stmt->fetchColumn() . "\n\n";
    }
} catch (\Throwable $e) {
    echo "❌ Database ERROR: " . $e->getMessage() . "\n\n";
}

echo "=== Testing Projeto Model ===\n";
try {
    require_once SRC_PATH . '/models/Projeto.php';
    $projeto = new \App\Models\Projeto();
    $results = $projeto->all([], 1, 3);
    echo "✅ SUCCESS: " . count($results) . " projetos found\n";
    if (!empty($results)) {
        echo "First: #" . $results[0]['id'] . "\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Testing Atividade Model ===\n";
try {
    require_once SRC_PATH . '/models/Atividade.php';
    $atividade = new \App\Models\Atividade();
    $results = $atividade->all([], 1, 3);
    echo "✅ SUCCESS: " . count($results) . " atividades found\n";
    if (!empty($results)) {
        echo "First: #" . $results[0]['id'] . "\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Testing NotaFiscal Model ===\n";
try {
    require_once SRC_PATH . '/models/NotaFiscal.php';
    $nota = new \App\Models\NotaFiscal();
    $results = $nota->all([], 1, 3);
    echo "✅ SUCCESS: " . count($results) . " notas fiscais found\n";
    if (!empty($results)) {
        echo "First: #" . $results[0]['id'] . "\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
