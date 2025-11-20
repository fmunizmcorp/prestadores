<?php
/**
 * SPRINT 62 - Database Export Script
 * Exports database from Hostinger to SQL file
 */

// Database credentials
$host = 'localhost';
$database = 'u673902663_prestadores';
$username = 'u673902663_admin';
$password = ';>?I4dtn~2Ga';

// Output file
$output_file = 'prestadores_db_backup_' . date('Y-m-d_His') . '.sql';

echo "🗄️  SPRINT 62 - Database Export\n";
echo str_repeat("=", 70) . "\n\n";

// Connect to database
try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database: $database\n\n";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage() . "\n");
}

// Open output file
$fp = fopen($output_file, 'w');
if (!$fp) {
    die("❌ Cannot create output file: $output_file\n");
}

// Write SQL header
fwrite($fp, "-- =====================================================\n");
fwrite($fp, "-- Database: $database\n");
fwrite($fp, "-- Export Date: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- Sprint: 62 - Migration to VPS\n");
fwrite($fp, "-- =====================================================\n\n");
fwrite($fp, "SET NAMES utf8mb4;\n");
fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

// Get all tables
echo "📋 Fetching tables...\n";
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "   Found " . count($tables) . " tables\n\n";

$total_rows = 0;

// Export each table
foreach ($tables as $table) {
    echo "📦 Exporting table: $table\n";
    
    // Drop table statement
    fwrite($fp, "-- =====================================================\n");
    fwrite($fp, "-- Table: $table\n");
    fwrite($fp, "-- =====================================================\n\n");
    fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n\n");
    
    // Create table statement
    $create_table = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    fwrite($fp, $create_table['Create Table'] . ";\n\n");
    
    // Get row count
    $row_count = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo "   Rows: $row_count\n";
    $total_rows += $row_count;
    
    if ($row_count > 0) {
        // Export data
        $rows = $conn->query("SELECT * FROM `$table`");
        
        $batch = [];
        $batch_size = 100;
        $count = 0;
        
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $values = array_map(function($value) use ($conn) {
                if ($value === null) {
                    return 'NULL';
                }
                return $conn->quote($value);
            }, array_values($row));
            
            $batch[] = "(" . implode(", ", $values) . ")";
            $count++;
            
            if (count($batch) >= $batch_size) {
                $columns = '`' . implode('`, `', array_keys($row)) . '`';
                fwrite($fp, "INSERT INTO `$table` ($columns) VALUES\n");
                fwrite($fp, implode(",\n", $batch) . ";\n\n");
                $batch = [];
            }
        }
        
        // Write remaining batch
        if (!empty($batch)) {
            $columns = '`' . implode('`, `', array_keys($row)) . '`';
            fwrite($fp, "INSERT INTO `$table` ($columns) VALUES\n");
            fwrite($fp, implode(",\n", $batch) . ";\n\n");
        }
    }
    
    echo "   ✅ Exported\n\n";
}

// Write footer
fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");

fclose($fp);

// Summary
$file_size = filesize($output_file);
echo str_repeat("=", 70) . "\n";
echo "✅ EXPORT COMPLETE!\n";
echo "   Tables: " . count($tables) . "\n";
echo "   Total rows: $total_rows\n";
echo "   File: $output_file\n";
echo "   Size: " . number_format($file_size) . " bytes (" . number_format($file_size / 1024 / 1024, 2) . " MB)\n";
echo str_repeat("=", 70) . "\n";
