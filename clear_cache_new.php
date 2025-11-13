<?php
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
    echo "=== USUARIOS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
        echo "{$row['Field']} | {$row['Type']}\n";
    }
    echo "\n";
    
    $has_perfil = in_array('perfil', $columns);
    $has_tipo = in_array('tipo', $columns);
    
    echo "Has perfil: " . ($has_perfil ? 'YES' : 'NO') . "\n";
    echo "Has tipo: " . ($has_tipo ? 'YES' : 'NO') . "\n\n";
    
    $role_col = $has_perfil ? 'perfil' : ($has_tipo ? 'tipo' : null);
    
    if (!$role_col) {
        echo "=== ADDING PERFIL COLUMN ===\n";
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN perfil VARCHAR(50) DEFAULT 'gestor' AFTER email");
        echo "✓ Added\n\n";
        $role_col = 'perfil';
    }
    
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Update all
    $stmt = $pdo->prepare("UPDATE usuarios SET senha=?, ativo=1 WHERE email LIKE '%@clinfec.com%'");
    $stmt->execute([$hash]);
    echo "=== UPDATED {$stmt->rowCount()} USERS ===\n\n";
    
    // Ensure test users
    $users = [
        ['Master', 'master@clinfec.com.br', 'master'],
        ['Admin', 'admin@clinfec.com.br', 'admin'],
        ['Gestor', 'gestor@clinfec.com.br', 'gestor']
    ];
    
    foreach ($users as $u) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email=?");
        $stmt->execute([$u[1]]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, senha=?, $role_col=?, ativo=1 WHERE email=?");
            $stmt->execute([$u[0], $hash, $u[2], $u[1]]);
            echo "✓ Updated: {$u[1]}\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, $role_col, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$u[0], $u[1], $hash, $u[2]]);
            echo "✓ Created: {$u[1]}\n";
        }
    }
    
    echo "\n=== CLINFEC USERS ===\n";
    $stmt = $pdo->query("SELECT email, $role_col as role, ativo FROM usuarios WHERE email LIKE '%@clinfec.com%'");
    foreach ($stmt as $row) {
        echo "{$row['email']} | {$row['role']} | " . ($row['ativo'] ? 'ATIVO' : 'INATIVO') . "\n";
    }
    
    echo "\n✅ SUCCESS!\n";
    echo "Login: master@clinfec.com.br / password\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
