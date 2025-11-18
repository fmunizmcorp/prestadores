<?php
// Direct database export - sends SQL to stdout
set_time_limit(300);
ini_set('memory_limit', '512M');

$host = 'localhost';
$database = 'u673902663_prestadores';
$username = 'u673902663_admin';
$password = ';>?I4dtn~2Ga';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL header
    echo "-- Database Export\n";
    echo "-- Database: $database\n";
    echo "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    echo "SET NAMES utf8mb4;\n";
    echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    // Get tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        // Drop table
        echo "-- Table: $table\n";
        echo "DROP TABLE IF EXISTS `$table`;\n\n";
        
        // Create table
        $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        echo $create['Create Table'] . ";\n\n";
        
        // Get data
        $rows = $conn->query("SELECT * FROM `$table`");
        $count = 0;
        $batch = [];
        $first_row = null;
        
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            if ($first_row === null) $first_row = $row;
            
            $values = array_map(function($v) use ($conn) {
                return $v === null ? 'NULL' : $conn->quote($v);
            }, array_values($row));
            
            $batch[] = '(' . implode(',', $values) . ')';
            
            if (count($batch) >= 50) {
                $cols = '`' . implode('`,`', array_keys($first_row)) . '`';
                echo "INSERT INTO `$table` ($cols) VALUES\n" . implode(",\n", $batch) . ";\n\n";
                $batch = [];
            }
        }
        
        if (!empty($batch) && $first_row) {
            $cols = '`' . implode('`,`', array_keys($first_row)) . '`';
            echo "INSERT INTO `$table` ($cols) VALUES\n" . implode(",\n", $batch) . ";\n\n";
        }
    }
    
    echo "SET FOREIGN_KEY_CHECKS = 1;\n";
    
} catch (Exception $e) {
    echo "-- ERROR: " . $e->getMessage() . "\n";
}
