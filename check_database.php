<?php
require_once __DIR__ . '/config/database.php';

echo "=== DIAGNÓSTICO COMPLETO DO BANCO DE DADOS ===\n\n";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
    // Listar todas as tabelas
    echo "📋 TABELAS EXISTENTES:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n";
    echo "Total de tabelas: " . count($tables) . "\n\n";
    
    // Verificar tabelas críticas
    $critical_tables = [
        'usuarios',
        'empresas_tomadoras',
        'empresas_prestadoras',
        'servicos',
        'contratos',
        'projetos',
        'atividades'
    ];
    
    echo "🔍 VERIFICAÇÃO DE TABELAS CRÍTICAS:\n";
    foreach ($critical_tables as $table) {
        $exists = in_array($table, $tables);
        $status = $exists ? "✅ EXISTE" : "❌ FALTANDO";
        echo "  $status - $table\n";
        
        // Se tabela existe, verificar estrutura
        if ($exists) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Verificar se tem deleted_at
            $has_deleted_at = in_array('deleted_at', $columns);
            if ($has_deleted_at) {
                echo "    ✓ Tem deleted_at (soft deletes)\n";
            } else {
                echo "    ⚠ SEM deleted_at\n";
            }
        }
    }
    
    echo "\n=== FIM DO DIAGNÓSTICO ===\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
