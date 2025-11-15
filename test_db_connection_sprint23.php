<?php
/**
 * SPRINT 23 - Teste de Conexão Database
 * Upload este arquivo via FTP e acesse: https://clinfec.com.br/test_db_connection_sprint23.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== SPRINT 23 - TESTE DE CONEXÃO DATABASE ===\n\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// Carregar configuração
$dbConfigFile = __DIR__ . '/config/database.php';

if (!file_exists($dbConfigFile)) {
    echo "❌ ERRO: Arquivo config/database.php não encontrado!\n";
    echo "   Path esperado: $dbConfigFile\n";
    exit;
}

echo "✅ Arquivo config/database.php encontrado\n\n";

$dbConfig = require $dbConfigFile;

echo "📋 CONFIGURAÇÕES CARREGADAS:\n";
echo "   Host: " . $dbConfig['host'] . "\n";
echo "   Database: " . $dbConfig['database'] . "\n";
echo "   Username: " . $dbConfig['username'] . "\n";
echo "   Password: " . (isset($dbConfig['password']) ? '[DEFINIDA]' : '[NÃO DEFINIDA]') . "\n";
echo "   Charset: " . $dbConfig['charset'] . "\n\n";

// Tentar conectar
echo "🔗 TENTANDO CONECTAR...\n";

try {
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        $dbConfig['host'],
        $dbConfig['database'],
        $dbConfig['charset']
    );
    
    $pdo = new PDO(
        $dsn,
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options'] ?? []
    );
    
    echo "✅ CONEXÃO BEM-SUCEDIDA!\n\n";
    
    // Testar query simples
    echo "🧪 TESTANDO QUERY SIMPLES...\n";
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as current_db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   MySQL Version: " . $result['version'] . "\n";
    echo "   Current Database: " . $result['current_db'] . "\n\n";
    
    // Listar tabelas
    echo "📋 TABELAS NO DATABASE:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "   ⚠️  Nenhuma tabela encontrada! Database está vazio.\n";
    } else {
        echo "   Total de tabelas: " . count($tables) . "\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
    }
    
    echo "\n✅ TESTE COMPLETO - DATABASE FUNCIONANDO!\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO DE CONEXÃO!\n\n";
    echo "   Tipo: " . get_class($e) . "\n";
    echo "   Código: " . $e->getCode() . "\n";
    echo "   Mensagem: " . $e->getMessage() . "\n\n";
    
    echo "🔍 DIAGNÓSTICO:\n";
    
    $msg = $e->getMessage();
    
    if (strpos($msg, 'Access denied') !== false) {
        echo "   ❌ PROBLEMA: Credenciais incorretas (usuário ou senha)\n";
        echo "   SOLUÇÃO: Verificar user/pass no painel Hostinger\n";
    } elseif (strpos($msg, 'Unknown database') !== false) {
        echo "   ❌ PROBLEMA: Database não existe\n";
        echo "   SOLUÇÃO: Criar database '" . $dbConfig['database'] . "' no painel Hostinger\n";
    } elseif (strpos($msg, 'Connection refused') !== false || strpos($msg, 'Can\'t connect') !== false) {
        echo "   ❌ PROBLEMA: MySQL não está acessível\n";
        echo "   SOLUÇÃO: Verificar se MySQL está rodando ou se host está correto\n";
        echo "   DICA: Tente usar '127.0.0.1' em vez de 'localhost'\n";
    } else {
        echo "   ⚠️  PROBLEMA DESCONHECIDO\n";
        echo "   Contate o suporte do Hostinger\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Arquivo: " . __FILE__ . "\n";
echo "Executado em: " . date('Y-m-d H:i:s') . "\n";
?>
