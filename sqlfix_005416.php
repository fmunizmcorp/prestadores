<?php
// Ultra simple SQL fixer - no dependencies
header('Content-Type: text/plain; charset=utf-8');
echo "SQL Fixer Sprint 16\n\n";

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4',
        'u673902663_prestadores',
        'Genspark1@',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ DB Connected\n\n";
    
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Update
    $stmt = $pdo->prepare("UPDATE usuarios SET senha=?, ativo=1, updated_at=NOW() WHERE email LIKE '%@clinfec.com%'");
    $stmt->execute([$hash]);
    echo "Updated: " . $stmt->rowCount() . "\n\n";
    
    // Insert/Update test users
    $users = [
        ['Master User', 'master@clinfec.com.br', 'master'],
        ['Admin User', 'admin@clinfec.com.br', 'admin'],
        ['Gestor User', 'gestor@clinfec.com.br', 'gestor']
    ];
    
    foreach ($users as $u) {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, perfil, ativo, created_at, updated_at)
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE senha=VALUES(senha), ativo=1, updated_at=NOW()
        ");
        $stmt->execute([$u[0], $u[1], $hash, $u[2]]);
        echo "✓ {$u[1]}\n";
    }
    
    echo "\n=== USERS ===\n";
    $stmt = $pdo->query("SELECT email, perfil, ativo FROM usuarios WHERE email LIKE '%@clinfec.com%'");
    foreach ($stmt as $row) {
        echo "{$row['email']} | {$row['perfil']} | " . ($row['ativo'] ? 'ATIVO' : 'INATIVO') . "\n";
    }
    
    echo "\nSUCCESS! Login: master@clinfec.com.br / password\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
