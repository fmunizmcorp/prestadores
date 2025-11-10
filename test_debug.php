<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);
define('BASE_URL', 'https://prestadores.clinfec.com.br');

require_once ROOT_PATH . '/vendor/autoload.php';

try {
    $controller = new App\Controllers\ProjetoController();
    echo "✅ ProjetoController loaded successfully\n";
    
    $model = new App\Models\Projeto();
    echo "✅ Projeto model loaded successfully\n";
    
    $projetos = $model->all();
    echo "✅ Query executed: " . count($projetos) . " projetos found\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
