<?php
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
    
    echo "<h2>Estrutura da tabela 'projetos'</h2>\n";
    
    $stmt = $conn->query("DESCRIBE projetos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
