<?php
$pdo = new PDO("mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4", 
               'u673902663_admin', ';>?I4dtn~2Ga');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: text/plain; charset=utf-8');

echo "VERIFICANDO TABELA atividades\n";
echo "═══════════════════════════════════════════\n\n";

// Verificar se tabela existe
$result = $pdo->query("SHOW TABLES LIKE 'atividades'");
if ($result->rowCount() == 0) {
    echo "✗ TABELA atividades NÃO EXISTE!\n";
    echo "\nTabelas existentes:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    exit(0);
}

echo "✓ Tabela atividades existe\n\n";

// Mostrar estrutura
echo "ESTRUTURA ATUAL:\n";
echo "═══════════════════════════════════════════\n";

$columns = $pdo->query("SHOW COLUMNS FROM atividades")->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo sprintf("%-30s %-25s %s\n", 
                 $col['Field'], 
                 $col['Type'], 
                 $col['Null'] == 'YES' ? 'NULL' : 'NOT NULL');
}

echo "\nTotal de colunas: " . count($columns) . "\n";

// Contar registros
$count = $pdo->query("SELECT COUNT(*) FROM atividades")->fetchColumn();
echo "Total de registros: " . $count . "\n";
