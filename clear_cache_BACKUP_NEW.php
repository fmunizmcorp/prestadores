<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== MODELS TEST - PHP " . PHP_VERSION . " ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "[Cache cleared]\n\n";
}

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

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
    $db = \App\Database::getInstance();
    echo "✅ DB: " . $db->query("SELECT VERSION()")->fetchColumn() . "\n\n";
} catch (\Throwable $e) {
    echo "❌ DB: " . $e->getMessage() . "\n\n";
}

echo "=== PROJETO ===\n";
try {
    $p = new \App\Models\Projeto();
    $r = $p->all([], 1, 2);
    echo "✅ " . count($r) . " found\n";
} catch (\Throwable $e) {
    echo "❌ " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== ATIVIDADE ===\n";
try {
    $a = new \App\Models\Atividade();
    $r = $a->all([], 1, 2);
    echo "✅ " . count($r) . " found\n";
} catch (\Throwable $e) {
    echo "❌ " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== NOTAFISCAL ===\n";
try {
    $n = new \App\Models\NotaFiscal();
    $r = $n->all([], 1, 2);
    echo "✅ " . count($r) . " found\n";
} catch (\Throwable $e) {
    echo "❌ " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== COMPLETE ===\n";
