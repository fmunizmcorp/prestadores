<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

echo "=== PHP VERSION CHECK ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Clear cache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "[OK] OPcache reset executed\n\n";
}

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

echo "=== PATHS CHECK ===\n";
echo "ROOT_PATH: " . ROOT_PATH . "\n";
echo "SRC exists: " . (is_dir(SRC_PATH) ? 'YES' : 'NO') . "\n";
echo "CONFIG exists: " . (is_dir(CONFIG_PATH) ? 'YES' : 'NO') . "\n\n";

// Autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) $class = substr($class, 4);
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

try {
    require_once CONFIG_PATH . '/database.php';
    require_once SRC_PATH . '/Database.php';
    echo "[OK] Core files loaded\n\n";
} catch (\Throwable $e) {
    die("❌ Load error: " . $e->getMessage() . "\n");
}

// Test Database
echo "=== DATABASE CONNECTION ===\n";
try {
    $db = \App\Database::getInstance();
    if ($db instanceof PDO) {
        echo "✅ Connected (PDO instance)\n";
        $ver = $db->query("SELECT VERSION()")->fetchColumn();
        echo "MySQL Version: " . $ver . "\n\n";
    } else {
        echo "❌ getInstance() returned: " . gettype($db) . "\n\n";
    }
} catch (\Throwable $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

// Test Projeto
echo "=== PROJETO MODEL ===\n";
try {
    $projeto = new \App\Models\Projeto();
    $results = $projeto->all([], 1, 3);
    echo "✅ SUCCESS: Found " . count($results) . " projects\n";
    if (!empty($results)) {
        echo "First project ID: " . $results[0]['id'] . "\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== ATIVIDADE MODEL ===\n";
try {
    $atividade = new \App\Models\Atividade();
    $results = $atividade->all([], 1, 3);
    echo "✅ SUCCESS: Found " . count($results) . " activities\n";
    if (!empty($results)) {
        echo "First activity ID: " . $results[0]['id'] . "\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== NOTAFISCAL MODEL ===\n";
try {
    $nota = new \App\Models\NotaFiscal();
    $results = $nota->all([], 1, 3);
    echo "✅ SUCCESS: Found " . count($results) . " invoices\n";
    if (!empty($results)) {
        echo "First invoice ID: " . $results[0]['id'] . "\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
