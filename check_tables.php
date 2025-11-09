<?php
// Can be run locally to check database
define('ROOT_PATH', __DIR__);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

try {
    echo "Connecting to database...\n";
    $db = App\Database::getInstance()->getConnection();
    
    echo "✓ Connected!\n\n";
    
    // Get all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Total tables: " . count($tables) . "\n\n";
    echo "Tables in database:\n";
    echo "==================\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    // Check specific tables required by failing routes
    echo "\n\nChecking tables for failing routes:\n";
    echo "====================================\n";
    
    $requiredTables = [
        'projetos' => 'ProjetoController',
        'projeto_categorias' => 'ProjetoController',
        'atividades' => 'AtividadeController',
        'lancamentos_financeiros' => 'FinanceiroController',
        'categorias_financeiras' => 'FinanceiroController',
        'notas_fiscais' => 'NotaFiscalController',
        'fornecedores' => 'NotaFiscalController (Fornecedor model)',
        'clientes' => 'NotaFiscalController (Cliente model)',
    ];
    
    foreach ($requiredTables as $table => $controller) {
        $exists = in_array($table, $tables);
        $status = $exists ? '✓' : '✗';
        echo "$status $table ($controller)\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
