<?php
/**
 * Executor de TODAS as Migrations - Sprint 13
 * Executa migration consolidada 013 que contém TODAS as tabelas necessárias
 */

set_time_limit(300); // 5 minutos
ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = require __DIR__ . '/config/database.php';

echo "<html><head><meta charset='UTF-8'><title>Migrations Sprint 13</title></head><body><pre>";
echo "================================================================\n";
echo "SPRINT 13: EXECUÇÃO DE TODAS AS MIGRATIONS\n";
echo "================================================================\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Conectado ao banco: {$config['database']}\n\n";
    
    // Ler arquivo SQL consolidado
    $sql_file = __DIR__ . '/database/migrations/013_executar_todas_migrations.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("Arquivo SQL não encontrado: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    $file_size = number_format(strlen($sql));
    echo "✅ Migration consolidada carregada: {$file_size} bytes\n";
    echo "   Arquivo: 013_executar_todas_migrations.sql\n\n";
    
    // Dividir em statements individuais
    // Remove comentários de linha inteira
    $lines = explode("\n", $sql);
    $cleaned_lines = [];
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        // Pular linhas vazias ou comentários
        if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
            continue;
        }
        $cleaned_lines[] = $line;
    }
    
    $cleaned_sql = implode("\n", $cleaned_lines);
    
    // Dividir por ponto-e-vírgula
    $statements = array_filter(
        array_map('trim', explode(';', $cleaned_sql)),
        function($stmt) {
            return !empty($stmt);
        }
    );
    
    echo "📋 Total de comandos SQL: " . count($statements) . "\n\n";
    echo "Executando statements...\n\n";
    
    $executed = 0;
    $skipped = 0;
    $errors = 0;
    $tables_created = [];
    $tables_altered = [];
    
    foreach ($statements as $index => $statement) {
        try {
            // Extrair informação do statement
            if (preg_match('/CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                $table = $matches[2];
                echo "  📝 CREATE TABLE: $table ... ";
                $pdo->exec($statement);
                $tables_created[] = $table;
                echo "✅\n";
                $executed++;
                
            } elseif (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                $table = $matches[1];
                
                // Verificar se tabela existe antes de alterar
                $check = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($check->rowCount() == 0) {
                    echo "  ⚠️  SKIP ALTER: $table (tabela não existe)\n";
                    $skipped++;
                    continue;
                }
                
                echo "  🔧 ALTER TABLE: $table ... ";
                $pdo->exec($statement);
                $tables_altered[] = $table;
                echo "✅\n";
                $executed++;
                
            } elseif (preg_match('/CREATE\s+INDEX/i', $statement)) {
                echo "  📇 CREATE INDEX ... ";
                $pdo->exec($statement);
                echo "✅\n";
                $executed++;
                
            } elseif (preg_match('/INSERT\s+(IGNORE\s+)?INTO/i', $statement)) {
                echo "  📥 INSERT ... ";
                $pdo->exec($statement);
                echo "✅\n";
                $executed++;
                
            } else {
                // Outros comandos (executar silenciosamente se não for longo)
                if (strlen($statement) < 100) {
                    $pdo->exec($statement);
                    $executed++;
                }
            }
            
        } catch (PDOException $e) {
            $error_msg = $e->getMessage();
            
            // Ignorar erros de "já existe"
            if (stripos($error_msg, 'already exists') !== false ||
                stripos($error_msg, 'Duplicate') !== false) {
                echo "⚠️  (já existe)\n";
                $skipped++;
            } elseif (stripos($error_msg, "doesn't exist") !== false &&
                      preg_match('/ALTER|INDEX/', $statement)) {
                echo "⚠️  (tabela não existe)\n";
                $skipped++;
            } else {
                echo "❌\n";
                echo "     ERRO: $error_msg\n";
                $errors++;
            }
        }
    }
    
    echo "\n================================================================\n";
    echo "RESULTADO DA EXECUÇÃO:\n";
    echo "================================================================\n";
    echo "✅ Comandos executados: $executed\n";
    echo "⚠️  Comandos ignorados: $skipped\n";
    echo "❌ Erros: $errors\n\n";
    
    if (!empty($tables_created)) {
        echo "📊 Tabelas criadas (" . count($tables_created) . "):\n";
        foreach (array_unique($tables_created) as $table) {
            echo "   - $table\n";
        }
        echo "\n";
    }
    
    if (!empty($tables_altered)) {
        echo "🔧 Tabelas alteradas (" . count(array_unique($tables_altered)) . "):\n";
        foreach (array_unique($tables_altered) as $table) {
            echo "   - $table\n";
        }
        echo "\n";
    }
    
    // Verificar tabelas críticas
    echo "================================================================\n";
    echo "VERIFICAÇÃO DE TABELAS CRÍTICAS:\n";
    echo "================================================================\n\n";
    
    $critical = [
        'usuarios',
        'empresas_tomadoras',
        'empresas_prestadoras',
        'servicos',
        'contratos',
        'projetos',
        'atividades',
        'notas_fiscais'
    ];
    
    foreach ($critical as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            // Verificar deleted_at
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_at'");
            $has_deleted = $stmt->rowCount() > 0;
            $deleted_status = $has_deleted ? "[✓ deleted_at]" : "[⚠ sem deleted_at]";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$table`");
            $count = $stmt->fetch()['total'];
            
            echo "  ✅ $table $deleted_status - $count registro(s)\n";
        } else {
            echo "  ❌ $table - NÃO EXISTE\n";
        }
    }
    
    echo "\n================================================================\n";
    echo "✅ MIGRATIONS CONCLUÍDAS!\n";
    echo "================================================================\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre></body></html>";
