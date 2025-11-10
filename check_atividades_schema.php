<?php
$config = ['host' => 'localhost', 'database' => 'u673902663_prestadores', 'username' => 'u673902663_admin', 'password' => ';>?I4dtn~2Ga'];
try {
    $conn = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
    $stmt = $conn->query("DESCRIBE atividades");
    echo "<h3>Colunas da tabela 'atividades':</h3>\n";
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "{$col['Field']} ({$col['Type']})<br>\n";
    }
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
