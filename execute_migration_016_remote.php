<?php
/**
 * Executor Remoto de Migration 016
 * 
 * Este script executa a migration 016 diretamente no banco de produção
 * Adiciona 16 colunas na tabela notas_fiscais para compatibilidade com Controller
 */

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'u673902663_prestadores';
$username = 'u673902663_admin';
$password = ';>?I4dtn~2Ga';

header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     EXECUTANDO MIGRATION 016 EM PRODUÇÃO                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

try {
    // Conectar ao banco
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Conectado ao banco de dados\n";
    echo "  Database: $dbname\n\n";
    
    // Verificar se migration já foi executada
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_version WHERE version = 16");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "⚠ Migration 016 já foi executada anteriormente\n";
        echo "  Pulando execução\n\n";
        exit(0);
    }
    
    echo "→ Iniciando execução da migration 016...\n\n";
    
    // Ler o arquivo da migration
    $migrationFile = __DIR__ . '/database/migrations/016_adicionar_colunas_notafiscal_controller.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Arquivo de migration não encontrado: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    echo "✓ Migration carregada (" . number_format(strlen($sql)) . " bytes)\n\n";
    
    // Executar a migration
    echo "→ Executando SQL...\n";
    
    // Dividir em statements individuais e executar
    $statements = explode(';', $sql);
    $executedCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executedCount++;
            
            // Mostrar progresso a cada 5 statements
            if ($executedCount % 5 == 0) {
                echo "  → $executedCount statements executados...\n";
            }
        } catch (PDOException $e) {
            // Ignorar erros de "já existe" - pode ser re-execução
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                throw $e;
            }
        }
    }
    
    echo "\n✓ Migration 016 executada com sucesso!\n";
    echo "  Total de statements: $executedCount\n\n";
    
    // Verificar colunas adicionadas
    echo "→ Verificando colunas adicionadas...\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM notas_fiscais WHERE Field IN (
        'valor_produtos', 'valor_servicos', 'valor_total', 'valor_frete',
        'valor_seguro', 'valor_outras_despesas', 'valor_base_calculo', 'valor_icms_st',
        'pdf_danfe', 'informacoes_adicionais', 'criado_por', 'atualizado_por',
        'deleted_at', 'data_autorizacao', 'created_at', 'updated_at'
    )");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n✓ Colunas verificadas: " . count($columns) . "/16\n";
    
    foreach ($columns as $col) {
        echo "  ✓ " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n╔══════════════════════════════════════════════════════════════╗\n";
    echo "║     MIGRATION 016 COMPLETA COM SUCESSO                       ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    
} catch (Exception $e) {
    echo "\n✗ ERRO ao executar migration:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
