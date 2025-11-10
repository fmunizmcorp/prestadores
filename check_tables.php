<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $tables = ['projetos', 'atividades', 'notas_fiscais', 'projeto_categorias', 'projeto_financeiro'];
    
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo ($exists ? "✅" : "❌") . " $table\n";
        
        if ($exists) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "   Rows: " . $result['count'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
