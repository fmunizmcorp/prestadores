<?php
$config = ['host' => 'localhost', 'database' => 'u673902663_prestadores', 'username' => 'u673902663_admin', 'password' => ';>?I4dtn~2Ga'];

try {
    $conn = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Corrigindo Colunas Faltantes</h2>\n";
    
    $alters = [
        "ALTER TABLE projetos ADD COLUMN empresa_tomadora_id INT(11) NULL" => "projetos.empresa_tomadora_id",
        "ALTER TABLE projetos ADD COLUMN categoria_id INT(11) NULL" => "projetos.categoria_id",
        "ALTER TABLE projetos ADD COLUMN gerente_id INT(11) NULL" => "projetos.gerente_id",
        "ALTER TABLE projetos ADD COLUMN created_by INT(11) NULL" => "projetos.created_by",
        "ALTER TABLE atividades ADD COLUMN titulo VARCHAR(255) NULL" => "atividades.titulo",
        "UPDATE atividades SET titulo = nome WHERE titulo IS NULL OR titulo = ''" => "populate_titulo",
        "CREATE TABLE IF NOT EXISTS projeto_etapas (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            projeto_id INT(11) NOT NULL,
            nome VARCHAR(200) NOT NULL,
            status ENUM('pendente','em_andamento','concluida','cancelada') DEFAULT 'pendente',
            ativo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )" => "table:projeto_etapas",
        "CREATE TABLE IF NOT EXISTS projeto_equipe (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            projeto_id INT(11) NOT NULL,
            usuario_id INT(11) NOT NULL,
            papel VARCHAR(100) NULL,
            ativo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )" => "table:projeto_equipe"
    ];
    
    foreach ($alters as $sql => $label) {
        try {
            $conn->exec($sql);
            echo "✅ $label<br>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'exists') !== false) {
                echo "⚠️ $label (já existe)<br>\n";
            } else {
                echo "❌ $label: " . $e->getMessage() . "<br>\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erro fatal: " . $e->getMessage();
}
