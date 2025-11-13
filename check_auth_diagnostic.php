<?php
/**
 * Authentication Diagnostic Script
 * Checks database connection, users table, and password verification
 */

// Force clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
}

header('Content-Type: text/plain; charset=utf-8');

echo "==========================================\n";
echo "AUTHENTICATION DIAGNOSTIC - SPRINT 15\n";
echo "==========================================\n\n";

// Include database config
require __DIR__ . '/config/database.php';

try {
    // Test database connection
    echo "[1] Testing Database Connection...\n";
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "    ✓ Database connected successfully\n\n";
    
    // Check usuarios table
    echo "[2] Checking usuarios table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "    Total users: {$result['total']}\n\n";
    
    // List all users
    echo "[3] Listing all users...\n";
    $stmt = $pdo->query("SELECT id, nome, email, perfil, ativo FROM usuarios ORDER BY id");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $status = $user['ativo'] ? 'ATIVO' : 'INATIVO';
        echo "    ID {$user['id']}: {$user['email']}\n";
        echo "       Nome: {$user['nome']}\n";
        echo "       Perfil: {$user['perfil']}\n";
        echo "       Status: $status\n\n";
    }
    
    // Test specific users
    echo "[4] Testing specific test users...\n\n";
    $test_users = [
        'master@clinfec.com.br',
        'admin@clinfec.com.br',
        'gestor@clinfec.com.br'
    ];
    
    foreach ($test_users as $email) {
        echo "   Testing: $email\n";
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, perfil, ativo FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "     ✓ User exists\n";
            echo "     Nome: {$user['nome']}\n";
            echo "     Perfil: {$user['perfil']}\n";
            echo "     Ativo: " . ($user['ativo'] ? 'SIM' : 'NÃO') . "\n";
            echo "     Hash: " . substr($user['senha'], 0, 20) . "...\n";
            
            // Test password verification
            $test_password = 'password';
            if (password_verify($test_password, $user['senha'])) {
                echo "     ✓ Password 'password' VERIFIED\n";
            } else {
                echo "     ✗ Password 'password' FAILED\n";
                echo "     Hash starts with: " . substr($user['senha'], 0, 7) . "\n";
            }
        } else {
            echo "     ✗ User NOT FOUND\n";
        }
        echo "\n";
    }
    
    // Check AuthController exists
    echo "[5] Checking AuthController...\n";
    if (file_exists(__DIR__ . '/src/Controllers/AuthController.php')) {
        echo "    ✓ AuthController.php exists\n";
    } else {
        echo "    ✗ AuthController.php MISSING\n";
    }
    
    // Check Usuario Model exists
    echo "\n[6] Checking Usuario Model...\n";
    if (file_exists(__DIR__ . '/src/Models/Usuario.php')) {
        echo "    ✓ Usuario.php exists\n";
    } else {
        echo "    ✗ Usuario.php MISSING\n";
    }
    
    echo "\n==========================================\n";
    echo "DIAGNOSTIC COMPLETE\n";
    echo "==========================================\n";
    
} catch (PDOException $e) {
    echo "✗ DATABASE ERROR: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
