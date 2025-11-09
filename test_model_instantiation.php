<?php
define('ROOT_PATH', __DIR__);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

echo "Testing Model Instantiation\n\n";

$models = [
    'Projeto',
    'ProjetoCategoria',
    'Atividade',
    'LancamentoFinanceiro',
    'CategoriaFinanceira',
    'NotaFiscal',
    'Usuario'
];

foreach ($models as $model) {
    echo "Testing App\\Models\\$model...";
    try {
        $class = "App\\Models\\$model";
        $instance = new $class();
        echo " ✓\n";
    } catch (Exception $e) {
        echo " ✗ ERROR: " . $e->getMessage() . "\n";
    }
}
