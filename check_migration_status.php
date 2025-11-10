<?php
$config = ['host' => 'localhost', 'database' => 'u673902663_prestadores', 'username' => 'u673902663_admin', 'password' => ';>?I4dtn~2Ga'];

try {
    $conn = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar versão atual
    $stmt = $conn->query("SELECT db_version, system_version, updated_at FROM system_version WHERE id = 1");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Status da Migration</h2>\n";
    echo "<table border='1'>\n";
    echo "<tr><th>DB Version</th><th>System Version</th><th>Updated At</th></tr>\n";
    echo "<tr><td>{$version['db_version']}</td><td>{$version['system_version']}</td><td>{$version['updated_at']}</td></tr>\n";
    echo "</table>\n";
    
    // Verificar se migration 015 rodou (verificar se colunas foram adicionadas)
    echo "<h3>Verificação de Colunas Adicionadas:</h3>\n";
    
    $checks = [
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'u673902663_prestadores' AND TABLE_NAME = 'projetos' AND COLUMN_NAME = 'empresa_tomadora_id'" => "projetos.empresa_tomadora_id",
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'u673902663_prestadores' AND TABLE_NAME = 'projetos' AND COLUMN_NAME = 'prioridade'" => "projetos.prioridade",
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'u673902663_prestadores' AND TABLE_NAME = 'atividades' AND COLUMN_NAME = 'titulo'" => "atividades.titulo",
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'u673902663_prestadores' AND TABLE_NAME = 'atividades' AND COLUMN_NAME = 'responsavel_id'" => "atividades.responsavel_id",
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'u673902663_prestadores' AND TABLE_NAME = 'projeto_categorias'" => "table:projeto_categorias",
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'u673902663_prestadores' AND TABLE_NAME = 'projeto_etapas'" => "table:projeto_etapas",
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'u673902663_prestadores' AND TABLE_NAME = 'projeto_equipe'" => "table:projeto_equipe"
    ];
    
    foreach ($checks as $sql => $label) {
        $result = $conn->query($sql)->fetchColumn();
        $status = $result > 0 ? "✅ OK" : "❌ FALTA";
        echo "$status - $label<br>\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
