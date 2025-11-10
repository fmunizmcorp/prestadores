<?php
$config = ['host' => 'localhost', 'database' => 'u673902663_prestadores', 'username' => 'u673902663_admin', 'password' => ';>?I4dtn~2Ga'];

try {
    $conn = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Atualizando Versão do Banco</h2>\n";
    
    // Atualizar para versão 15
    $sql = "UPDATE system_version SET db_version = 15, system_version = '1.9.0' WHERE id = 1";
    $conn->exec($sql);
    
    echo "✅ Versão atualizada para 15<br>\n";
    
    // Executar migration 015 manualmente
    echo "<h3>Executando Migration 015...</h3>\n";
    $migrationSql = file_get_contents('/home/u673902663/domains/clinfec.com.br/public_html/prestadores/database/migrations/015_corrigir_schema_projetos_atividades.sql');
    
    // Split por ; e executar
    $statements = explode(';', $migrationSql);
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || substr($statement, 0, 2) == '--') continue;
        
        try {
            $conn->exec($statement);
            $executed++;
            echo ".";
            flush();
        } catch (PDOException $e) {
            // Ignora "já existe"
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), 'already exists') !== false) {
                echo "i";
            } else {
                $errors++;
                echo "E";
                error_log("SQL Error: " . $e->getMessage());
            }
        }
    }
    
    echo "<br>\n✅ Migration 015: $executed statements executados, $errors erros<br>\n";
    
    // Verificar resultado
    $stmt = $conn->query("SELECT db_version FROM system_version WHERE id = 1");
    $version = $stmt->fetchColumn();
    echo "<h3>Versão Final: $version</h3>\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
