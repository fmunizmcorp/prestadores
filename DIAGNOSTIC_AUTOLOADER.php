<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO COMPLETO DO AUTOLOADER ===\n\n";

// Definir caminhos
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

echo "1. CAMINHOS:\n";
echo "   ROOT_PATH: " . ROOT_PATH . "\n";
echo "   SRC_PATH: " . SRC_PATH . "\n\n";

echo "2. ESTRUTURA src/:\n";
$dirs = ['Controllers', 'Models', 'Views', 'Helpers'];
foreach ($dirs as $dir) {
    $path = SRC_PATH . '/' . $dir;
    echo "   $dir: " . (is_dir($path) ? '✅ EXISTE' : '❌ NÃO EXISTE') . "\n";
    
    if (is_dir($path)) {
        $files = array_filter(scandir($path), function($f) { return pathinfo($f, PATHINFO_EXTENSION) === 'php'; });
        echo "      Arquivos: " . count($files) . "\n";
        foreach (array_slice($files, 0, 3) as $f) {
            echo "      - $f\n";
        }
    }
}

echo "\n3. ARQUIVO Usuario.php:\n";
$usuario_path = SRC_PATH . '/Models/Usuario.php';
echo "   Caminho: $usuario_path\n";
echo "   Existe? " . (file_exists($usuario_path) ? '✅ SIM' : '❌ NÃO') . "\n";
if (file_exists($usuario_path)) {
    echo "   Tamanho: " . filesize($usuario_path) . " bytes\n";
    echo "   Legível? " . (is_readable($usuario_path) ? '✅ SIM' : '❌ NÃO') . "\n";
}

echo "\n4. TESTANDO AUTOLOADER:\n";
spl_autoload_register(function ($class) {
    echo "   → Tentando carregar: $class\n";
    
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
        echo "      Removido namespace App\\, ficou: $class\n";
    }
    
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    echo "      Arquivo esperado: $file\n";
    echo "      Existe? " . (file_exists($file) ? '✅ SIM' : '❌ NÃO') . "\n";
    
    if (file_exists($file)) {
        require_once $file;
        echo "      ✅ CARREGADO COM SUCESSO!\n";
        return true;
    }
    return false;
});

echo "\n5. TENTANDO INSTANCIAR Usuario:\n";
try {
    $usuario = new \App\Models\Usuario();
    echo "   ✅ SUCCESS! Usuario instanciado!\n";
    echo "   Classe: " . get_class($usuario) . "\n";
} catch (\Error $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . "\n";
    echo "   Linha: " . $e->getLine() . "\n";
}

echo "\n6. CLASSES CARREGADAS:\n";
$classes = get_declared_classes();
$app_classes = array_filter($classes, function($c) { return strpos($c, 'App\\') === 0; });
echo "   Total: " . count($app_classes) . "\n";
foreach ($app_classes as $c) {
    echo "   - $c\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
