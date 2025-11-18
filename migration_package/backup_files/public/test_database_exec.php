<?php
/**
 * TESTE ESPECÍFICO: Database::exec() existe?
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>TESTE Database::exec() - Sprint 30</h1>\n\n";

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Carregar Database diretamente
echo "<h2>1. Carregando Database.php...</h2>\n";
$db_file = SRC_PATH . '/Database.php';
echo "Path: $db_file<br>\n";

if (!file_exists($db_file)) {
    die("❌ Arquivo não encontrado!");
}

echo "Size: " . filesize($db_file) . " bytes<br>\n";
echo "Modified: " . date('Y-m-d H:i:s', filemtime($db_file)) . "<br>\n\n";

require_once $db_file;
echo "✅ require_once executado<br>\n\n";

// Verificar métodos
echo "<h2>2. Inspecionando classe App\\Database...</h2>\n";

try {
    $reflection = new ReflectionClass('App\\Database');
    
    echo "<h3>Métodos públicos:</h3>\n<ul>\n";
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
        $name = $method->getName();
        $line = $method->getStartLine();
        $file = basename($method->getFileName());
        
        if ($name === 'exec') {
            echo "<li><strong style='color:green'>✅ $name() - linha $line - $file</strong></li>\n";
        } else {
            echo "<li>$name() - linha $line</li>\n";
        }
    }
    echo "</ul>\n\n";
    
    // Teste final
    echo "<h2>3. Teste method_exists()</h2>\n";
    if (method_exists('App\\Database', 'exec')) {
        echo "✅ <strong>method_exists('App\\Database', 'exec') = TRUE</strong><br>\n";
    } else {
        echo "❌ <strong>method_exists('App\\Database', 'exec') = FALSE</strong><br>\n";
    }
    
    // Tentar instanciar
    echo "<h2>4. Tentando instanciar Database...</h2>\n";
    
    // Carregar config
    $dbConfig = require CONFIG_PATH . '/database.php';
    
    $db = App\Database::getInstance();
    echo "✅ getInstance() OK<br>\n";
    
    if (method_exists($db, 'exec')) {
        echo "✅ <strong>\$db->exec() EXISTE!</strong><br>\n";
    } else {
        echo "❌ <strong>\$db->exec() NÃO EXISTE!</strong><br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>\n";
}

echo "\n<hr>\n<p>Teste concluído: " . date('Y-m-d H:i:s') . "</p>\n";
