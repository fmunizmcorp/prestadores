<?php
/**
 * SPRINT 62 - Database Export Script V2
 * Sends SQL directly to browser
 */

// Set headers for download
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="prestadores_db_backup_' . date('Y-m-d_His') . '.sql"');
header('Cache-Control: no-cache, must-revalidate');

// Database credentials
$host = 'localhost';
$database = 'u673902663_prestadores';
$username = 'u673902663_admin';
$password = ';>?I4dtn~2Ga';

// Connect
try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("-- ERROR: Connection failed: " . $e->getMessage() . "\n");
}

// SQL header
echo "-- =====================================================\n";
echo "-- Database: $database\n";
echo "-- Export Date: " . date('Y-m-d H:i:s') . "\n";
echo "-- Sprint: 62 - Migration to VPS\n";
echo "-- =====================================================\n\n";
echo "SET NAMES utf8mb4;\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// Get tables
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Export each table
foreach ($tables as $table) {
    echo "-- =====================================================\n";
    echo "-- Table: $table\n";
    echo "-- =====================================================\n\n";
    echo "DROP TABLE IF EXISTS `$table`;\n\n";
    
    // Create table
    $create_table = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    echo $create_table['Create Table'] . ";\n\n";
    
    // Get row count
    $row_count = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    
    if ($row_count > 0) {
        // Export data
        $rows = $conn->query("SELECT * FROM `$table`");
        
        $batch = [];
        $batch_size = 50;
        $first_row = null;
        
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            if ($first_row === null) {
                $first_row = $row;
            }
            
            $values = array_map(function($value) use ($conn) {
                if ($value === null) {
                    return 'NULL';
                }
                return $conn->quote($value);
            }, array_values($row));
            
            $batch[] = "(" . implode(", ", $values) . ")";
            
            if (count($batch) >= $batch_size) {
                $columns = '`' . implode('`, `', array_keys($first_row)) . '`';
                echo "INSERT INTO `$table` ($columns) VALUES\n";
                echo implode(",\n", $batch) . ";\n\n";
                $batch = [];
            }
        }
        
        // Write remaining
        if (!empty($batch) && $first_row) {
            $columns = '`' . implode('`, `', array_keys($first_row)) . '`';
            echo "INSERT INTO `$table` ($columns) VALUES\n";
            echo implode(",\n", $batch) . ";\n\n";
        }
    }
}

echo "SET FOREIGN_KEY_CHECKS = 1;\n";
echo "-- Export complete!\n";
