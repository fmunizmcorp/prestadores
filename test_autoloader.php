<?php
header('Content-Type: text/plain');

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

echo "=== TESTE DE AUTOLOADER ===\n\n";

// Testar caminhos
echo "ROOT_PATH: " . ROOT_PATH . "\n";
echo "SRC_PATH: " . SRC_PATH . "\n\n";

// Registrar autoloader
spl_autoload_register(function ($class) {
    echo "Tentando carregar: $class\n";
    
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
        echo "  Sem namespace App\\: $class\n";
    }
    
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    echo "  Arquivo: $file\n";
    echo "  Existe? " . (file_exists($file) ? 'SIM' : 'NÃO') . "\n\n";
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Tentar carregar
echo "--- Tentando carregar App\\Models\\Usuario ---\n";
try {
    $user = new \App\Models\Usuario();
    echo "✅ SUCCESS! Usuario carregado!\n";
} catch (Error $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
