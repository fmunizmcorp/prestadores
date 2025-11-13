<?php
// BRAND NEW FILE - Created Nov 11 23:10 - Never cached
header('Content-Type: text/plain; charset=utf-8');

echo "=== PHP 8.1 MODELS TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP: " . PHP_VERSION . "\n\n";

define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');
define('CONFIG_PATH', __DIR__ . '/config');

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

try {
    echo "[1] Loading config...\n";
    require_once CONFIG_PATH . '/database.php';
    
    echo "[2] Loading Database class...\n";
    require_once SRC_PATH . '/Database.php';
    
    echo "[3] Connecting to database...\n";
    $db = \App\Database::getInstance();
    $version = $db->query("SELECT VERSION()")->fetchColumn();
    echo "✅ DB Connected: $version\n\n";
    
    echo "[4] Testing Projeto Model...\n";
    $projeto = new \App\Models\Projeto();
    $projetos = $projeto->all([], 1, 3);
    echo "✅ Projeto: " . count($projetos) . " records\n";
    if (count($projetos) > 0) {
        echo "    First: " . ($projetos[0]['nome'] ?? 'N/A') . "\n";
    }
    
    echo "\n[5] Testing Atividade Model...\n";
    $atividade = new \App\Models\Atividade();
    $atividades = $atividade->all([], 1, 3);
    echo "✅ Atividade: " . count($atividades) . " records\n";
    if (count($atividades) > 0) {
        echo "    First: " . ($atividades[0]['titulo'] ?? 'N/A') . "\n";
    }
    
    echo "\n[6] Testing NotaFiscal Model...\n";
    $nota = new \App\Models\NotaFiscal();
    $notas = $nota->all([], 1, 3);
    echo "✅ NotaFiscal: " . count($notas) . " records\n";
    if (count($notas) > 0) {
        echo "    First: NF #" . ($notas[0]['numero'] ?? 'N/A') . "\n";
    }
    
    echo "\n=== ✅ ALL MODELS WORKING! ===\n";
    echo "Database pattern is correct.\n";
    echo "All 3 models load and query successfully.\n";
    
} catch (\Throwable $e) {
    echo "\n❌ ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack:\n" . $e->getTraceAsString() . "\n";
}
