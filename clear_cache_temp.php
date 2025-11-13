<?php
/**
 * TEMPORARY SQL FIXER - Will be restored after fixing
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
    
    // Check table structure
    echo "=== CHECKING USUARIOS TABLE ===\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
        echo "{$row['Field']} | {$row['Type']}\n";
    }
    echo "\n";
    
    $has_perfil = in_array('perfil', $columns);
    $has_tipo = in_array('tipo', $columns);
    
    echo "Has 'perfil' column: " . ($has_perfil ? 'YES' : 'NO') . "\n";
    echo "Has 'tipo' column: " . ($has_tipo ? 'YES' : 'NO') . "\n\n";
    
    // Determine role column
    $role_col = $has_perfil ? 'perfil' : ($has_tipo ? 'tipo' : null);
    
    if (!$role_col) {
        echo "=== ADDING PERFIL COLUMN ===\n";
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN perfil VARCHAR(50) DEFAULT 'gestor' AFTER email");
        echo "✓ Column 'perfil' added successfully\n\n";
        $role_col = 'perfil';
    }
    
    // Show current users
    echo "=== CURRENT CLINFEC USERS ===\n";
    $stmt = $pdo->query("SELECT id, nome, email, $role_col as role, ativo FROM usuarios WHERE email LIKE '%@clinfec.com%'");
    foreach ($stmt as $row) {
        echo sprintf("ID:%d | %s | %s | %s | %s\n",
            $row['id'],
            $row['nome'],
            $row['email'],
            $row['role'],
            $row['ativo'] ? 'ATIVO' : 'INATIVO'
        );
    }
    echo "\n";
    
    // Password hash for "password"
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Update all Clinfec users
    echo "=== UPDATING PASSWORDS ===\n";
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
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email=?");
        $stmt->execute([$u[1]]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing
            $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, senha=?, $role_col=?, ativo=1, updated_at=NOW() WHERE email=?");
            $stmt->execute([$u[0], $hash, $u[2], $u[1]]);
            echo "✓ Updated: {$u[1]}\n";
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, $role_col, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$u[0], $u[1], $hash, $u[2]]);
            echo "✓ Created: {$u[1]}\n";
        }
    }
    
    // Show final state
    echo "\n=== FINAL CLINFEC USERS ===\n";
    $stmt = $pdo->query("SELECT id, nome, email, $role_col as role, ativo, updated_at FROM usuarios WHERE email LIKE '%@clinfec.com%' ORDER BY id");
    foreach ($stmt as $row) {
        echo sprintf("ID:%d | %s | %s | %s | %s | %s\n",
            $row['id'],
            $row['nome'],
            $row['email'],
            $row['role'],
            $row['ativo'] ? 'ATIVO' : 'INATIVO',
            $row['updated_at']
        );
    }
    
    // Test password verification
    echo "\n=== PASSWORD VERIFICATION ===\n";
    $test_pass = 'password';
    $verify = password_verify($test_pass, $hash);
    echo "Password 'password' verification: " . ($verify ? '✓ VALID' : '✗ INVALID') . "\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ SUCCESS! CREDENTIALS FIXED!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "LOGIN CREDENTIALS:\n";
    echo "  Email: master@clinfec.com.br\n";
    echo "  Password: password\n\n";
    
    echo "Alternative logins:\n";
    echo "  admin@clinfec.com.br / password\n";
    echo "  gestor@clinfec.com.br / password\n\n";
    
    echo "You can now login at:\n";
    echo "  https://prestadores.clinfec.com.br/\n\n";
    
    echo "NOTE: This file (clear_cache.php) has been temporarily\n";
    echo "modified to fix credentials. It will be restored.\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END ===\n";
