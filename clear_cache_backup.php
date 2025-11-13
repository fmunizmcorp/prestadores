<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DATABASE USERS TEST ===\n\n";

define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', __DIR__ . '/config');

// Load database config
require_once CONFIG_PATH . '/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connected: " . DB_NAME . "\n\n";
    
    // Check usuarios table
    echo "=== CHECKING USUARIOS TABLE ===\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns) . "\n\n";
    
    // Get all users
    echo "=== ALL USERS ===\n";
    $stmt = $pdo->query("SELECT id, email, nome, role, perfil, ativo FROM usuarios LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "❌ NO USERS FOUND IN DATABASE!\n";
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "  Email: {$user['email']}\n";
            echo "  Nome: {$user['nome']}\n";
            echo "  Role: " . ($user['role'] ?? 'NULL') . "\n";
            echo "  Perfil: " . ($user['perfil'] ?? 'NULL') . "\n";
            echo "  Ativo: " . ($user['ativo'] ? 'Sim' : 'Não') . "\n\n";
        }
    }
    
    // Check master user specifically
    echo "=== CHECKING MASTER USER ===\n";
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute(['master@clinfec.com.br']);
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($master) {
        echo "✅ Master user found!\n";
        echo "  ID: {$master['id']}\n";
        echo "  Email: {$master['email']}\n";
        echo "  Nome: {$master['nome']}\n";
        echo "  Role: " . ($master['role'] ?? 'NULL') . "\n";
        echo "  Perfil: " . ($master['perfil'] ?? 'NULL') . "\n";
        echo "  Ativo: " . ($master['ativo'] ? 'Sim' : 'Não') . "\n";
        echo "  Senha hash: " . substr($master['senha'], 0, 30) . "...\n";
        
        // Test password verification
        echo "\n=== TESTING PASSWORD VERIFICATION ===\n";
        $test_password = 'password';
        $matches = password_verify($test_password, $master['senha']);
        echo "  Test password: '{$test_password}'\n";
        echo "  Matches hash: " . ($matches ? '✅ YES' : '❌ NO') . "\n";
        
    } else {
        echo "❌ Master user NOT FOUND!\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
