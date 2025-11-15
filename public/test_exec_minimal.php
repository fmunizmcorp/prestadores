<?php
/**
 * TESTE MINIMALISTA - Database::exec()
 */

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

echo "<h1>TESTE EXEC() - Sprint 30</h1>\n\n";

// Carregar Database
echo "<h2>1. Carregando Database.php...</h2>\n";
require_once SRC_PATH . '/Database.php';
echo "✅ require_once executado<br>\n\n";

// Verificar métodos
echo "<h2>2. Métodos da classe:</h2>\n";
$methods = get_class_methods('App\\Database');
echo "<ul>\n";
foreach ($methods as $m) {
    if ($m === 'exec') {
        echo "<li><strong style='color:green'>✅ $m()</strong></li>\n";
    } else {
        echo "<li>$m()</li>\n";
    }
}
echo "</ul>\n\n";

// Testar instância
echo "<h2>3. Testando instância:</h2>\n";

try {
    $dbConfig = require CONFIG_PATH . '/database.php';
    $db = App\Database::getInstance();
    
    echo "✅ getInstance() OK<br>\n";
    
    if (method_exists($db, 'exec')) {
        echo "✅ <strong>\$db->exec() EXISTE!</strong><br>\n";
    } else {
        echo "❌ <strong>\$db->exec() NÃO EXISTE!</strong><br>\n";
    }
    
    // Tentar chamar exec
    echo "<h3>Tentando executar SQL simples:</h3>\n";
    $result = $db->exec("SELECT 1");
    echo "✅ exec() FUNCIONOU! Result: $result<br>\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>\n";
    echo "Stack trace:<br>\n<pre>" . $e->getTraceAsString() . "</pre>\n";
}
