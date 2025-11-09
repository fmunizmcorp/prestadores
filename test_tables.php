<?php
/**
 * Test database tables
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

echo "=== DATABASE TABLES TEST ===\n\n";

define('ROOT_PATH', __DIR__);
require ROOT_PATH . '/config/database.php';

try {
    $db = \App\Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "✓ Database connected\n\n";
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\nTotal: " . count($tables) . " tables\n";
    
    // Check for specific tables needed by failing controllers
    $required = [
        'projetos', 
        'projeto_categorias',
        'atividades',
        'lancamentos_financeiros',
        'notas_fiscais',
        'fornecedores',
        'clientes'
    ];
    
    echo "\nRequired tables check:\n";
    foreach ($required as $table) {
        $exists = in_array($table, $tables);
        echo "  " . ($exists ? "✓" : "✗ MISSING") . " $table\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
