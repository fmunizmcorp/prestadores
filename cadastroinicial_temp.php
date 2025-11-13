<?php
/**
 * TEMPORARY SQL FIXER - Sprint 16
 * This will be restored after fixing credentials
 */

header('Content-Type: text/plain; charset=utf-8');
echo "=== CREDENTIAL FIXER - Sprint 16 ===\n\n";

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4',
        'u673902663_prestadores',
        'Genspark1@',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Database Connected\n\n";
    
    // Password hash for "password"
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Update all Clinfec users
    echo "=== UPDATING USERS ===\n";
    $stmt = $pdo->prepare("UPDATE usuarios SET senha=?, ativo=1, updated_at=NOW() WHERE email LIKE '%@clinfec.com%'");
    $stmt->execute([$hash]);
    echo "Updated: " . $stmt->rowCount() . " users\n\n";
    
    // Ensure test users exist
    echo "=== ENSURING TEST USERS ===\n";
    $test_users = [
        ['Master User', 'master@clinfec.com.br', 'master'],
        ['Admin User', 'admin@clinfec.com.br', 'admin'],
        ['Gestor User', 'gestor@clinfec.com.br', 'gestor']
    ];
    
    foreach ($test_users as $u) {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, perfil, ativo, created_at, updated_at)
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE senha=VALUES(senha), ativo=1, updated_at=NOW()
        ");
        $stmt->execute([$u[0], $u[1], $hash, $u[2]]);
        echo "✓ {$u[1]}\n";
    }
    
    // Show all users
    echo "\n=== ALL CLINFEC USERS ===\n";
    $stmt = $pdo->query("
        SELECT id, nome, email, perfil, ativo, updated_at
        FROM usuarios
        WHERE email LIKE '%@clinfec.com%'
        ORDER BY FIELD(perfil, 'master', 'admin', 'gestor')
    ");
    foreach ($stmt as $row) {
        echo sprintf(
            "ID:%d | %s | %s | %s | %s | %s\n",
            $row['id'],
            $row['nome'],
            $row['email'],
            $row['perfil'],
            $row['ativo'] ? 'ATIVO' : 'INATIVO',
            $row['updated_at']
        );
    }
    
    // Test password verification
    echo "\n=== PASSWORD VERIFICATION ===\n";
    $test_pass = 'password';
    $verify = password_verify($test_pass, $hash);
    echo "Test password 'password': " . ($verify ? '✓ VALID' : '✗ INVALID') . "\n";
    
    echo "\n=== SUCCESS! ===\n";
    echo "Credentials fixed successfully!\n\n";
    echo "Login credentials:\n";
    echo "  Email: master@clinfec.com.br\n";
    echo "  Password: password\n\n";
    echo "Alternative logins:\n";
    echo "  admin@clinfec.com.br / password\n";
    echo "  gestor@clinfec.com.br / password\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

echo "\n\n=== END ===\n";
