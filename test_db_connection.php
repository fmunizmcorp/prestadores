<?php
$host = 'localhost';
$database = 'u673902663_prestadores';
$username = 'u673902663_admin';
$password = ';>?I4dtn~2Ga';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "SUCCESS|" . count($tables) . "|" . implode(",", $tables);
} catch (Exception $e) {
    echo "ERROR|" . $e->getMessage();
}
