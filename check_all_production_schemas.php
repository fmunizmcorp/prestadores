<?php
/**
 * CHECK ALL PRODUCTION TABLE SCHEMAS
 * 
 * This script inspects the actual production database structure for:
 * - projetos table
 * - atividades table
 * - notas_fiscais table (for verification)
 * 
 * Purpose: Diagnose HTTP 500 errors by comparing actual production schemas
 *          with Model expectations
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "════════════════════════════════════════════════════════════════\n";
echo "CHECKING ALL PRODUCTION TABLE SCHEMAS\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Production database credentials
$host = 'localhost';
$dbname = 'u673902663_prestadores';
$username = 'u673902663_admin';
$password = ';>?I4dtn~2Ga';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to database: $dbname\n\n";
    
    // Tables to check
    $tables = ['projetos', 'atividades', 'notas_fiscais'];
    
    foreach ($tables as $tableName) {
        echo "────────────────────────────────────────────────────────────────\n";
        echo "TABLE: $tableName\n";
        echo "────────────────────────────────────────────────────────────────\n";
        
        // Check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        
        if ($stmt->rowCount() === 0) {
            echo "❌ Table '$tableName' DOES NOT EXIST in production!\n\n";
            continue;
        }
        
        echo "✓ Table exists\n\n";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE $tableName");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "COLUMNS (" . count($columns) . " total):\n";
        echo "───────────────────────────────────────────────────────────────────────────────\n";
        printf("%-5s %-30s %-20s %-8s %-10s %-20s\n", 
               "No.", "Field", "Type", "Null", "Key", "Default/Extra");
        echo "───────────────────────────────────────────────────────────────────────────────\n";
        
        foreach ($columns as $i => $col) {
            printf("%-5d %-30s %-20s %-8s %-10s %-20s\n",
                   $i + 1,
                   $col['Field'],
                   $col['Type'],
                   $col['Null'],
                   $col['Key'],
                   ($col['Default'] ?? '') . ' ' . ($col['Extra'] ?? '')
            );
        }
        
        echo "\n";
        
        // Get row count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tableName");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total rows: " . number_format($count['total']) . "\n";
        
        // Get sample data (first 3 rows)
        echo "\nSAMPLE DATA (first 3 rows):\n";
        echo "───────────────────────────────────────────────────────────────────────────────\n";
        $stmt = $pdo->query("SELECT * FROM $tableName LIMIT 3");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($samples)) {
            echo "  (No data in table)\n";
        } else {
            foreach ($samples as $idx => $row) {
                echo "Row " . ($idx + 1) . ":\n";
                foreach ($row as $key => $value) {
                    $displayValue = $value ?? 'NULL';
                    if (strlen($displayValue) > 60) {
                        $displayValue = substr($displayValue, 0, 57) . '...';
                    }
                    echo "  $key: $displayValue\n";
                }
                echo "\n";
            }
        }
        
        echo "\n";
    }
    
    echo "════════════════════════════════════════════════════════════════\n";
    echo "SCHEMA CHECK COMPLETED\n";
    echo "════════════════════════════════════════════════════════════════\n";
    
} catch (PDOException $e) {
    echo "\n❌ DATABASE ERROR:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    exit(1);
}
