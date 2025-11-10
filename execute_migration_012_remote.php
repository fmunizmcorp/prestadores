<?php
/**
 * Executor de Migration 012 - Sprint 13
 * Corrige todos os problemas identificados no banco de dados
 * 
 * ESTE ARQUIVO SERÁ ENVIADO PARA O SERVIDOR E EXECUTADO VIA WEB
 */

// Configurações do banco
$config = require __DIR__ . '/config/database.php';

$host = $config['host'];
$database = $config['database'];
$username = $config['username'];
$password = $config['password'];

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Migration 012</title></head><body>";
echo "<pre>";
echo "========================================\n";
echo "MIGRATION 012 - SPRINT 13\n";
echo "Correção Completa do Banco de Dados\n";
echo "========================================\n\n";

try {
    // Conectar ao banco
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Conectado ao banco: $database\n\n";
    
    // Ler arquivo SQL
    $sql_file = __DIR__ . '/database/migrations/012_corrigir_banco_completo_sprint13.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("Arquivo SQL não encontrado: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    echo "✅ Arquivo SQL carregado: " . number_format(strlen($sql)) . " bytes\n\n";
    
    // Executar SQL (dividir por ponto-e-vírgula)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    echo "📋 Total de comandos SQL: " . count($statements) . "\n\n";
    echo "Executando...\n\n";
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            // Pular comentários e comandos vazios
            if (empty(trim($statement))) continue;
            if (preg_match('/^\s*(--|#)/', $statement)) continue;
            
            $pdo->exec($statement);
            $executed++;
            
            // Mostrar progresso para comandos importantes
            if (preg_match('/(CREATE TABLE|ALTER TABLE|INSERT)/i', $statement)) {
                // Extrair nome da tabela
                preg_match('/(CREATE TABLE|ALTER TABLE|INSERT INTO)\s+`?(\w+)`?/i', $statement, $matches);
                $table = $matches[2] ?? 'desconhecido';
                $action = strtoupper($matches[1] ?? 'EXECUTADO');
                echo "  ✅ $action: $table\n";
            }
            
        } catch (PDOException $e) {
            $errors++;
            $error_msg = $e->getMessage();
            
            // Ignorar erros de "já existe"
            if (stripos($error_msg, 'already exists') !== false ||
                stripos($error_msg, 'Duplicate') !== false) {
                echo "  ⚠️  Ignorado (já existe): " . substr($statement, 0, 50) . "...\n";
            } else {
                echo "  ❌ ERRO: $error_msg\n";
                echo "     SQL: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n========================================\n";
    echo "RESULTADO:\n";
    echo "========================================\n";
    echo "✅ Comandos executados: $executed\n";
    echo "❌ Erros: $errors\n";
    echo "\n";
    
    // Verificar tabelas criadas
    echo "📋 TABELAS CRÍTICAS:\n\n";
    
    $critical_tables = [
        'usuarios',
        'empresas_tomadoras',
        'empresas_prestadoras',
        'servicos',
        'contratos',
        'projetos',
        'atividades',
        'notas_fiscais'
    ];
    
    foreach ($critical_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            // Verificar se tem deleted_at
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_at'");
            $has_deleted_at = $stmt->rowCount() > 0;
            
            $deleted_status = $has_deleted_at ? " [✓ deleted_at]" : " [⚠ sem deleted_at]";
            echo "  ✅ $table$deleted_status\n";
        } else {
            echo "  ❌ $table - NÃO EXISTE\n";
        }
    }
    
    echo "\n========================================\n";
    echo "✅ MIGRATION 012 CONCLUÍDA!\n";
    echo "========================================\n";
    
    // Contar registros
    echo "\n📊 CONTAGEM DE REGISTROS:\n\n";
    foreach ($critical_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$table`");
            $result = $stmt->fetch();
            $total = $result['total'];
            echo "  $table: $total registro(s)\n";
        } catch (PDOException $e) {
            echo "  $table: N/A (tabela não existe)\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n</pre></body></html>";
