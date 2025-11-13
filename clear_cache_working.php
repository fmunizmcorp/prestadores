<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo "=== CREDENTIAL FIXER - Sprint 16 ===\n\n";

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4',
        'u673902663_prestadores',
        'Genspark1@',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Database Connected\n\n";
    
    // Check structure
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $has_perfil = in_array('perfil', $columns);
    $role_col = $has_perfil ? 'perfil' : (in_array('tipo', $columns) ? 'tipo' : null);
    
    if (!$role_col) {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN perfil VARCHAR(50) DEFAULT 'gestor' AFTER email");
        $role_col = 'perfil';
        echo "✓ Added perfil column\n\n";
    }
    
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Update passwords
    $stmt = $pdo->prepare("UPDATE usuarios SET senha=?, ativo=1, updated_at=NOW() WHERE email LIKE '%@clinfec.com%'");
    $stmt->execute([$hash]);
    echo "Updated " . $stmt->rowCount() . " users\n\n";
    
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
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, $role_col, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$u[0], $u[1], $hash, $u[2]]);
        }
        echo "✓ {$u[1]}\n";
    }
    
    echo "\n✅ SUCCESS!\n\n";
    echo "Login: master@clinfec.com.br / password\n";
    echo "URL: https://prestadores.clinfec.com.br/\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
