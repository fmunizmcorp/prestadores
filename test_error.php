<?php
// Ativar TODOS os erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

echo "<!DOCTYPE html><html><head><title>Debug Empresas Tomadoras</title></head><body>";
echo "<h1>Debug Detalhado - Empresas Tomadoras</h1>";

// Tentar simular exatamente o que public/index.php faz
echo "<h2>1. Definindo Constantes</h2>";
define('ROOT_PATH', __DIR__);
define('BASE_PATH', '');
define('BASE_URL', 'https://prestadores.clinfec.com.br');
echo "✓ ROOT_PATH: " . ROOT_PATH . "<br>";
echo "✓ BASE_URL: " . BASE_URL . "<br>";

// Autoloader
echo "<h2>2. Configurando Autoloader</h2>";
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    echo "Tentando carregar: $class → $file<br>";
    
    if (file_exists($file)) {
        require $file;
        echo "✓ Carregado com sucesso<br>";
    } else {
        echo "✗ Arquivo não encontrado!<br>";
    }
});
echo "✓ Autoloader configurado<br>";

// Carregar configurações
echo "<h2>3. Carregando Configurações</h2>";
try {
    $dbConfig = require ROOT_PATH . '/config/database.php';
    echo "✓ database.php carregado<br>";
    $versionConfig = require ROOT_PATH . '/config/version.php';
    echo "✓ version.php carregado<br>";
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "<br>";
}

// Testar Database
echo "<h2>4. Testando Database</h2>";
try {
    $db = App\Database::getInstance();
    echo "✓ Database::getInstance() OK<br>";
    $conn = $db->getConnection();
    echo "✓ getConnection() OK<br>";
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Testar Controller
echo "<h2>5. Testando EmpresaTomadoraController</h2>";
try {
    $controller = new App\Controllers\EmpresaTomadoraController();
    echo "✓ Controller instanciado<br>";
    
    // Tentar executar index()
    echo "<h3>Executando index()...</h3>";
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    echo "✓ index() executado<br>";
    echo "<details><summary>Ver output (clique)</summary><pre>" . htmlspecialchars($output) . "</pre></details>";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>6. Verificando Logs</h2>";
if (file_exists('/tmp/php_errors.log')) {
    echo "<pre>" . file_get_contents('/tmp/php_errors.log') . "</pre>";
} else {
    echo "Nenhum erro logado<br>";
}

echo "</body></html>";
