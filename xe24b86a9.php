<?php
// Força reset total
if (function_exists('opcache_reset')) opcache_reset();
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}
if (function_exists('apc_clear_cache')) apc_clear_cache();

// Headers para evitar cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/plain; charset=utf-8');

// Database
$dsn = "mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, 'u673902663_prestadores', 'Genspark1@', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "DB OK\n\n";
    
    // Update passwords
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $stmt = $pdo->prepare("UPDATE usuarios SET senha=?, ativo=1, updated_at=NOW() WHERE email LIKE '%@clinfec.com%'");
    $stmt->execute([$hash]);
    echo "Updated: " . $stmt->rowCount() . "\n\n";
    
    // Insert test users
    $users = [
        ['Master User', 'master@clinfec.com.br', 'master'],
        ['Admin User', 'admin@clinfec.com.br', 'admin'],
        ['Gestor User', 'gestor@clinfec.com.br', 'gestor']
    ];
    
    foreach ($users as $u) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE senha=VALUES(senha), ativo=1, updated_at=NOW()");
        $stmt->execute([$u[0], $u[1], $hash, $u[2]]);
        echo "OK: {$u[1]}\n";
    }
    
    echo "\n=== USERS ===\n";
    $stmt = $pdo->query("SELECT email, perfil, ativo FROM usuarios WHERE email LIKE '%@clinfec.com%'");
    while ($row = $stmt->fetch()) {
        echo "{$row['email']} | {$row['perfil']} | " . ($row['ativo'] ? 'ATIVO' : 'INATIVO') . "\n";
    }
    
    echo "\nSUCESS! Login: master@clinfec.com.br / password\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
