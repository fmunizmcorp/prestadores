<?php
// FORCE OPcache reset by touching file timestamp
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}
if (function_exists('opcache_reset')) {
    opcache_reset();
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== CREDENTIAL FIXER - FINAL ATTEMPT ===\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4', 'u673902663_prestadores', 'Genspark1@', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✓ DB Connected\n\n";
    
    // Get columns
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(', ', $cols) . "\n\n";
    
    $rc = in_array('perfil', $cols) ? 'perfil' : (in_array('tipo', $cols) ? 'tipo' : null);
    
    if (!$rc) {
        echo "Adding perfil column...\n";
        $pdo->exec("ALTER TABLE usuarios ADD perfil VARCHAR(50) DEFAULT 'gestor' AFTER email");
        $rc = 'perfil';
        echo "✓ Added\n\n";
    }
    
    $h = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Update all Clinfec users
    $stmt = $pdo->prepare("UPDATE usuarios SET senha=?, ativo=1, updated_at=NOW() WHERE email LIKE '%@clinfec.com%'");
    $stmt->execute([$h]);
    echo "Updated {$stmt->rowCount()} users\n\n";
    
    // Ensure test users
    $users = [
        ['Master', 'master@clinfec.com.br', 'master'],
        ['Admin', 'admin@clinfec.com.br', 'admin'],
        ['Gestor', 'gestor@clinfec.com.br', 'gestor']
    ];
    
    echo "=== Ensuring test users ===\n";
    foreach ($users as $u) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email=?");
        $stmt->execute([$u[1]]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, senha=?, $rc=?, ativo=1, updated_at=NOW() WHERE email=?");
            $stmt->execute([$u[0], $h, $u[2], $u[1]]);
            echo "✓ Updated: {$u[1]}\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, $rc, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$u[0], $u[1], $h, $u[2]]);
            echo "✓ Created: {$u[1]}\n";
        }
    }
    
    echo "\n✅✅✅ SUCCESS! ✅✅✅\n\n";
    echo "LOGIN CREDENTIALS:\n";
    echo "  master@clinfec.com.br / password\n";
    echo "  admin@clinfec.com.br / password\n";
    echo "  gestor@clinfec.com.br / password\n\n";
    echo "Login at: https://prestadores.clinfec.com.br/\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n=== END ===\n";

// Clear OPcache again after execution
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "\nOPcache cleared after execution\n";
}
