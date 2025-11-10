<?php
/**
 * CHECK ALL TABLES - Comprehensive diagnostic
 * Based on working check_notas_fiscais_table.php pattern
 */
$pdo = new PDO("mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4", 
               'u673902663_admin', ';>?I4dtn~2Ga');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "COMPREHENSIVE TABLE DIAGNOSTICS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$tables_to_check = ['projetos', 'atividades', 'notas_fiscais'];

foreach ($tables_to_check as $table_name) {
    echo "───────────────────────────────────────────────────────────────\n";
    echo "TABLE: $table_name\n";
    echo "───────────────────────────────────────────────────────────────\n";
    
    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE '$table_name'");
    if ($result->rowCount() == 0) {
        echo "✗ TABLE DOES NOT EXIST!\n\n";
        continue;
    }
    
    echo "✓ Table exists\n\n";
    
    // Show structure
    echo "STRUCTURE:\n";
    $columns = $pdo->query("SHOW COLUMNS FROM $table_name")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        printf("  %-30s %-25s %s %s\n", 
               $col['Field'], 
               $col['Type'], 
               $col['Null'] == 'YES' ? 'NULL' : 'NOT NULL',
               $col['Key'] ? "KEY:{$col['Key']}" : '');
    }
    
    echo "\nTotal columns: " . count($columns) . "\n";
    
    // Count records
    $count = $pdo->query("SELECT COUNT(*) FROM $table_name")->fetchColumn();
    echo "Total records: " . number_format($count) . "\n\n";
    
    // Sample data (just first record, limited fields)
    if ($count > 0) {
        echo "SAMPLE RECORD (first row):\n";
        $sample = $pdo->query("SELECT * FROM $table_name LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        foreach ($sample as $key => $value) {
            $display_value = $value ?? 'NULL';
            if (strlen($display_value) > 50) {
                $display_value = substr($display_value, 0, 47) . '...';
            }
            echo "  $key: $display_value\n";
        }
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "ALL TABLES IN DATABASE:\n";
echo "═══════════════════════════════════════════════════════════\n";
$all_tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($all_tables as $tbl) {
    $count = $pdo->query("SELECT COUNT(*) FROM $tbl")->fetchColumn();
    echo sprintf("  %-30s %s rows\n", $tbl, number_format($count));
}

echo "\n✅ DIAGNOSTIC COMPLETE\n";
