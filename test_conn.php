<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = [
    'host' => 'localhost',
    'database' => 'u673902663_prestadores',
    'username' => 'u673902663_admin',
    'password' => ';>?I4dtn~2Ga',
    'charset' => 'utf8mb4',
];

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $conn = new PDO($dsn, $config['username'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão OK!<br>\n";
    
    // Testar tabelas
    $tables = ['projetos', 'atividades', 'notas_fiscais'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo ($exists ? "✅" : "❌") . " Tabela: $table<br>\n";
        
        if ($exists) {
            $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "   → $count registros<br>\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>\n";
}
