<?php
$output = "=== MODELS TEST - PHP " . PHP_VERSION . " ===\n";
$output .= date('Y-m-d H:i:s') . "\n\n";

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
    $output .= "✅ DB: " . $db->query("SELECT VERSION()")->fetchColumn() . "\n\n";
    
    $output .= "=== PROJETO ===\n";
    $p = new \App\Models\Projeto();
    $r = $p->all([], 1, 2);
    $output .= "✅ " . count($r) . " found\n\n";
    
    $output .= "=== ATIVIDADE ===\n";
    $a = new \App\Models\Atividade();
    $r = $a->all([], 1, 2);
    $output .= "✅ " . count($r) . " found\n\n";
    
    $output .= "=== NOTAFISCAL ===\n";
    $n = new \App\Models\NotaFiscal();
    $r = $n->all([], 1, 2);
    $output .= "✅ " . count($r) . " found\n\n";
    
} catch (\Throwable $e) {
    $output .= "❌ ERROR: " . $e->getMessage() . "\n";
    $output .= $e->getFile() . ":" . $e->getLine() . "\n";
}

$output .= "=== COMPLETE ===\n";

file_put_contents(__DIR__ . '/TEST_RESULTS.txt', $output);

header('Content-Type: text/plain');
echo "Results written to TEST_RESULTS.txt\n";
echo "Access: https://prestadores.clinfec.com.br/TEST_RESULTS.txt\n";
