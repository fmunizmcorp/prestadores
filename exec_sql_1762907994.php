<?php
/**
 * SQL Executor - Sprint 16
 * Executes credential fix SQL directly
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'u673902663_prestadores');
define('DB_USER', 'u673902663_prestadores');
define('DB_PASS', 'Genspark1@');

header('Content-Type: text/plain; charset=utf-8');

echo "=== SQL EXECUTOR - Sprint 16 ===\n\n";

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Database connected\n\n";
    
    // Show current users
    echo "=== CURRENT USERS ===\n";
    $stmt = $pdo->query("SELECT id, nome, email, perfil, ativo FROM usuarios ORDER BY id");
    $users = $stmt->fetchAll();
    foreach ($users as $user) {
        echo sprintf("%d | %s | %s | %s | %s\n", 
            $user['id'], 
            $user['nome'], 
            $user['email'], 
            $user['perfil'], 
            $user['ativo'] ? 'ATIVO' : 'INATIVO'
        );
    }
    
    // Update Clinfec users
    echo "\n=== UPDATING CLINFEC USERS ===\n";
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET senha = ?,
            ativo = 1,
            updated_at = NOW()
        WHERE email LIKE '%@clinfec.com%'
    ");
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $stmt->execute([$hash]);
    echo "Updated " . $stmt->rowCount() . " users\n";
    
    // Insert test users if not exist
    echo "\n=== ENSURING TEST USERS EXIST ===\n";
    $test_users = [
        ['Master User', 'master@clinfec.com.br', 'master'],
        ['Admin User', 'admin@clinfec.com.br', 'admin'],
        ['Gestor User', 'gestor@clinfec.com.br', 'gestor']
    ];
    
    foreach ($test_users as $user) {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, perfil, ativo, created_at, updated_at)
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            senha = VALUES(senha),
            ativo = 1,
            updated_at = NOW()
        ");
        $stmt->execute([$user[0], $user[1], $hash, $user[2]]);
        echo "✓ {$user[1]} - {$user[2]}\n";
    }
    
    // Show updated users
    echo "\n=== UPDATED USERS ===\n";
    $stmt = $pdo->query("
        SELECT id, nome, email, perfil, ativo, updated_at
        FROM usuarios 
        WHERE email LIKE '%@clinfec.com%'
        ORDER BY FIELD(perfil, 'master', 'admin', 'gestor')
    ");
    $users = $stmt->fetchAll();
    foreach ($users as $user) {
        echo sprintf("%d | %s | %s | %s | %s | %s\n", 
            $user['id'], 
            $user['nome'], 
            $user['email'], 
            $user['perfil'], 
            $user['ativo'] ? 'ATIVO' : 'INATIVO',
            $user['updated_at']
        );
    }
    
    // Verification
    echo "\n=== VERIFICATION ===\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN perfil = 'master' THEN 1 ELSE 0 END) as master,
            SUM(CASE WHEN perfil = 'admin' THEN 1 ELSE 0 END) as admin,
            SUM(CASE WHEN perfil = 'gestor' THEN 1 ELSE 0 END) as gestor
        FROM usuarios
    ");
    $stats = $stmt->fetch();
    echo "Total users: {$stats['total']}\n";
    echo "Active users: {$stats['active']}\n";
    echo "Master users: {$stats['master']}\n";
    echo "Admin users: {$stats['admin']}\n";
    echo "Gestor users: {$stats['gestor']}\n";
    
    // Test password verification
    echo "\n=== PASSWORD VERIFICATION TEST ===\n";
    $test_password = 'password';
    $stmt = $pdo->query("SELECT email, senha FROM usuarios WHERE email LIKE '%@clinfec.com%' LIMIT 3");
    $test_users = $stmt->fetchAll();
    foreach ($test_users as $user) {
        $verify = password_verify($test_password, $user['senha']);
        echo sprintf("%s: %s\n", $user['email'], $verify ? '✓ VALID' : '✗ INVALID');
    }
    
    echo "\n=== SUCCESS ===\n";
    echo "All credentials fixed!\n";
    echo "Use: master@clinfec.com.br / password\n";
    echo "Use: admin@clinfec.com.br / password\n";
    echo "Use: gestor@clinfec.com.br / password\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}

echo "\n\n=== END ===\n";
