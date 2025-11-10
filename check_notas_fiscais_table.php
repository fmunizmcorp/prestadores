<?php
$pdo = new PDO("mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4", 
               'u673902663_admin', ';>?I4dtn~2Ga');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: text/plain; charset=utf-8');

echo "VERIFICANDO TABELA notas_fiscais\n";
echo "═══════════════════════════════════════════\n\n";

// Verificar se tabela existe
$result = $pdo->query("SHOW TABLES LIKE 'notas_fiscais'");
if ($result->rowCount() == 0) {
    echo "✗ TABELA notas_fiscais NÃO EXISTE!\n";
    echo "\nTabelas existentes:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    exit(0);
}

echo "✓ Tabela notas_fiscais existe\n\n";

// Mostrar estrutura
echo "ESTRUTURA ATUAL:\n";
echo "═══════════════════════════════════════════\n";

$columns = $pdo->query("SHOW COLUMNS FROM notas_fiscais")->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo sprintf("%-30s %-25s %s\n", 
                 $col['Field'], 
                 $col['Type'], 
                 $col['Null'] == 'YES' ? 'NULL' : 'NOT NULL');
}

echo "\nTotal de colunas: " . count($columns) . "\n";
