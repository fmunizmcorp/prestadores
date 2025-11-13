<?php
define('ROOT_PATH', dirname(__FILE__));
require_once ROOT_PATH . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');
echo "=== DATABASE MIGRATIONS CHECK ===\n\n";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Database Connected\n\n";
    
    // Check if schema_migrations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'schema_migrations'");
    if ($stmt->rowCount() > 0) {
        echo "=== MIGRATIONS TABLE EXISTS ===\n";
        $stmt = $pdo->query("SELECT * FROM schema_migrations ORDER BY version");
        while ($row = $stmt->fetch()) {
            echo "Version: {$row['version']} | Applied: {$row['applied_at']}\n";
        }
    } else {
        echo "⚠️ schema_migrations table does NOT exist\n";
    }
    
    echo "\n=== CHECKING CRITICAL TABLES ===\n";
    $critical_tables = [
        'usuarios',
        'empresas_tomadoras',
        'empresas_prestadoras',
        'projetos',
        'atividades',
        'servicos',
        'contratos',
        'notas_fiscais',
        'pagamentos',
        'financeiro'
    ];
    
    foreach ($critical_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
            $count = $stmt->fetch()['total'];
            echo "✓ $table (records: $count)\n";
        } else {
            echo "✗ $table - NOT EXISTS\n";
        }
    }
    
    echo "\n=== USUARIOS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    while ($row = $stmt->fetch()) {
        echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']}\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
