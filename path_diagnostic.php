<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== PATH DIAGNOSTIC ===\n\n";
echo "Current file: " . __FILE__ . "\n";
echo "Current dir: " . __DIR__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";

// Try to execute SQL
try {
    $pdo = new PDO('mysql:host=localhost;dbname=u673902663_prestadores', 'u673902663_prestadores', 'Genspark1@');
    echo "\n=== DATABASE ===\n";
    echo "✓ Connected\n\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios");
    $cols = [];
    while ($r = $stmt->fetch()) $cols[] = $r['Field'];
    echo "Columns: " . implode(', ', $cols) . "\n\n";
    
    if (!in_array('perfil', $cols)) {
        $pdo->exec("ALTER TABLE usuarios ADD perfil VARCHAR(50) DEFAULT 'gestor' AFTER email");
        echo "✓ Added perfil\n\n";
    }
    
    $h = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $pdo->exec("UPDATE usuarios SET senha='$h', ativo=1 WHERE email LIKE '%@clinfec.com%'");
    
    foreach([['Master','master@clinfec.com.br','master'],['Admin','admin@clinfec.com.br','admin']] as $u) {
        $pdo->exec("INSERT INTO usuarios (nome,email,senha,perfil,ativo,created_at,updated_at) VALUES ('{$u[0]}','{$u[1]}','$h','{$u[2]}',1,NOW(),NOW()) ON DUPLICATE KEY UPDATE senha='$h'");
    }
    
    echo "✅ SQL EXECUTED!\n";
    echo "Login: master@clinfec.com.br / password\n";
    
} catch (Exception $e) {
    echo "\n✗ DB ERROR: " . $e->getMessage();
}
