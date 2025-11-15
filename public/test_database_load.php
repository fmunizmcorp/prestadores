<?php
/**
 * TESTE: Verificar se Database.php é encontrado e carregado
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h1>TESTE DE CARREGAMENTO - Sprint 30</h1>\n\n";

// Teste 1: Arquivo existe?
$db_file = SRC_PATH . '/Database.php';
echo "<h2>1. Arquivo Database.php existe?</h2>\n";
echo "Path: $db_file<br>\n";
echo "Exists: " . (file_exists($db_file) ? "✅ SIM" : "❌ NÃO") . "<br>\n";
echo "Size: " . (file_exists($db_file) ? filesize($db_file) . " bytes" : "N/A") . "<br>\n";
echo "Modified: " . (file_exists($db_file) ? date('Y-m-d H:i:s', filemtime($db_file)) : "N/A") . "<br>\n\n";

// Teste 2: Conteúdo tem método exec?
if (file_exists($db_file)) {
    $content = file_get_contents($db_file);
    echo "<h2>2. Arquivo contém método exec()?</h2>\n";
    echo "Has 'public function exec': " . (strpos($content, 'public function exec') !== false ? "✅ SIM" : "❌ NÃO") . "<br>\n\n";
    
    // Mostrar linha do método exec
    $lines = explode("\n", $content);
    foreach ($lines as $num => $line) {
        if (strpos($line, 'public function exec') !== false) {
            $line_num = $num + 1;
            echo "Linha $line_num: <code>" . htmlspecialchars($line) . "</code><br>\n";
        }
    }
}

// Teste 3: Carregar classe e verificar métodos
echo "<h2>3. Tentando carregar classe Database</h2>\n";

try {
    require_once $db_file;
    echo "✅ require_once executado<br>\n";
    
    if (class_exists('App\\Database', false)) {
        echo "✅ Classe App\\Database existe<br>\n";
        
        $reflection = new ReflectionClass('App\\Database');
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        echo "<h3>Métodos públicos encontrados:</h3>\n";
        echo "<ul>\n";
        foreach ($methods as $method) {
            echo "<li>" . $method->getName() . "()</li>\n";
        }
        echo "</ul>\n";
        
        // Verificar especificamente exec
        if ($reflection->hasMethod('exec')) {
            echo "<h3>✅ MÉTODO exec() ENCONTRADO!</h3>\n";
            $exec_method = $reflection->getMethod('exec');
            echo "Linha: " . $exec_method->getStartLine() . "<br>\n";
            echo "Arquivo: " . $exec_method->getFileName() . "<br>\n";
        } else {
            echo "<h3>❌ MÉTODO exec() NÃO ENCONTRADO!</h3>\n";
        }
        
    } else {
        echo "❌ Classe App\\Database NÃO existe após require<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>\n";
}

echo "\n<hr>\n<p>Teste concluído: " . date('Y-m-d H:i:s') . "</p>\n";
