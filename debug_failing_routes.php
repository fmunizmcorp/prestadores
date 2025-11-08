<?php
// Debug script for failing routes
// Test: projetos, atividades, financeiro, notas-fiscais

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "========================================\n";
echo "DEBUG: ROTAS COM ERRO 500\n";
echo "========================================\n\n";

// Definir diretório raiz
define('ROOT_PATH', __DIR__);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    echo "Tentando carregar: $class\n";
    echo "→ $file\n";
    
    if (file_exists($file)) {
        echo "✓ Arquivo encontrado!\n\n";
        require $file;
    } else {
        echo "✗ Arquivo não encontrado!\n\n";
    }
});

// Testar cada controller que está falhando
$controllers = [
    'ProjetoController',
    'AtividadeController', 
    'FinanceiroController',
    'NotaFiscalController'
];

foreach ($controllers as $controllerName) {
    $fullClassName = "App\\Controllers\\{$controllerName}";
    echo "-------------------------------------------\n";
    echo "Testando: {$fullClassName}\n";
    echo "-------------------------------------------\n";
    
    try {
        if (class_exists($fullClassName)) {
            echo "✓ Classe existe!\n";
            
            // Tentar instanciar
            $controller = new $fullClassName();
            echo "✓ Instanciação bem-sucedida!\n";
            
            // Verificar se tem método index
            if (method_exists($controller, 'index')) {
                echo "✓ Método index() existe!\n";
            } else {
                echo "✗ Método index() NÃO existe!\n";
            }
        } else {
            echo "✗ Classe NÃO existe!\n";
        }
    } catch (Exception $e) {
        echo "✗ ERRO: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "\n";
}

echo "========================================\n";
echo "FIM DO DEBUG\n";
echo "========================================\n";
echo "</pre>";
