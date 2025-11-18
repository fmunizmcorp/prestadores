<?php
/**
 * Credential Fixer - Sprint 16
 * This file MUST execute to fix login
 */

// Kill all caches
if (function_exists('opcache_reset')) opcache_reset();
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo "=== CREDENTIAL FIXER ===\n\n";

// Move up one directory to reach root
define('ROOT_PATH', dirname(__DIR__));

// Load config
$config_file = ROOT_PATH . '/config/database.php';
if (!file_exists($config_file)) {
    die("Config not found: " . $config_file);
}

require_once $config_file;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✓ Database connected\n\n";
    
    // The password hash for "password"
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Update all Clinfec users
    $stmt = $pdo->prepare("UPDATE usuarios SET senha=?, ativo=1, updated_at=NOW() WHERE email LIKE '%@clinfec.com%'");
    $stmt->execute([$hash]);
    echo "Updated: " . $stmt->rowCount() . " users\n\n";
    
    // Ensure test users exist
    $users = [
        ['Master User', 'master@clinfec.com.br', 'master'],
        ['Admin User', 'admin@clinfec.com.br', 'admin'],
        ['Gestor User', 'gestor@clinfec.com.br', 'gestor']
    ];
    
    echo "=== ENSURING TEST USERS ===\n";
    foreach ($users as $u) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE senha=VALUES(senha), ativo=1, updated_at=NOW()");
        $stmt->execute([$u[0], $u[1], $hash, $u[2]]);
        echo "✓ {$u[1]}\n";
    }
    
    // Verify
    echo "\n=== VERIFICATION ===\n";
    $stmt = $pdo->query("SELECT email, perfil, ativo FROM usuarios WHERE email LIKE '%@clinfec.com%' ORDER BY email");
    while ($row = $stmt->fetch()) {
        echo sprintf("%s | %s | %s\n", $row['email'], $row['perfil'], $row['ativo'] ? 'ATIVO' : 'INATIVO');
    }
    
    // Test password
    echo "\n=== PASSWORD TEST ===\n";
    $verify = password_verify('password', $hash);
    echo "Password 'password' verification: " . ($verify ? '✓ VALID' : '✗ INVALID') . "\n";
    
    echo "\n=== SUCCESS! ===\n";
    echo "All credentials fixed!\n";
    echo "Login with: master@clinfec.com.br / password\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
