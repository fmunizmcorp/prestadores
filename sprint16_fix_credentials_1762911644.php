<?php
header('Content-Type: text/plain; charset=utf-8');
echo "Sprint 16 - Credential Fixer\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u673902663_prestadores', 'u673902663_prestadores', 'Genspark1@');
    echo "DB OK\n\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios");
    $cols = [];
    while ($r = $stmt->fetch()) $cols[] = $r['Field'];
    
    $rc = in_array('perfil', $cols) ? 'perfil' : 'tipo';
    if (!in_array($rc, $cols)) {
        $pdo->exec("ALTER TABLE usuarios ADD perfil VARCHAR(50) DEFAULT 'gestor' AFTER email");
        $rc = 'perfil';
    }
    
    $h = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $pdo->exec("UPDATE usuarios SET senha='$h', ativo=1 WHERE email LIKE '%@clinfec.com%'");
    
    foreach([['Master','master@clinfec.com.br','master'],['Admin','admin@clinfec.com.br','admin'],['Gestor','gestor@clinfec.com.br','gestor']] as $u) {
        $pdo->exec("INSERT INTO usuarios (nome,email,senha,$rc,ativo,created_at,updated_at) VALUES ('{$u[0]}','{$u[1]}','$h','{$u[2]}',1,NOW(),NOW()) ON DUPLICATE KEY UPDATE senha='$h',ativo=1");
    }
    
    echo "SUCCESS!\nLogin: master@clinfec.com.br / password\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
