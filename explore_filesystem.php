<?php
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════\n";
echo "  FILESYSTEM EXPLORATION\n";
echo "═══════════════════════════════════════════\n\n";

echo "Current Directory: " . getcwd() . "\n\n";

echo "PHP Files in Current Directory:\n";
echo "───────────────────────────────────────────\n";
$files = glob("*.php");
foreach ($files as $file) {
    echo " - $file\n";
}

echo "\n\nParent Directory: " . dirname(getcwd()) . "\n";
echo "───────────────────────────────────────────\n";
$parentFiles = glob("../*.php");
foreach ($parentFiles as $file) {
    echo " - " . basename($file) . "\n";
}

echo "\n\nDocument Root (SERVER): " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";

// Check if database access works
try {
    $pdo = new PDO("mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4", 
                   'u673902663_admin', ';>?I4dtn~2Ga');
    echo "\n✓ Database connection successful\n";
    
    // List all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "\nTables in database (" . count($tables) . " total):\n";
    echo "───────────────────────────────────────────\n";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo sprintf("%-30s %s rows\n", $table, number_format($count));
    }
} catch (Exception $e) {
    echo "\n✗ Database error: " . $e->getMessage() . "\n";
}
