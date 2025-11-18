<?php
/**
 * Gerador de Hashes de Senha
 * Sprint 66 - Bug #7 Fix
 */

echo "=== GERADOR DE HASHES DE SENHA ===\n\n";

$passwords = [
    'password' => 'password',
    'admin123' => 'admin123',
    'gestor123' => 'gestor123',
    'usuario123' => 'usuario123',
];

foreach ($passwords as $label => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "{$label}:\n";
    echo "  Senha: {$password}\n";
    echo "  Hash: {$hash}\n";
    echo "  Verificação: " . (password_verify($password, $hash) ? '✅ OK' : '❌ FALHOU') . "\n\n";
}

echo "=== SQL PARA INSERIR USUÁRIOS ===\n\n";

$users = [
    [
        'nome' => 'Master User',
        'email' => 'master@clinfec.com.br',
        'senha' => 'password',
        'role' => 'master'
    ],
    [
        'nome' => 'Admin User',
        'email' => 'admin@clinfec.com.br',
        'senha' => 'admin123',
        'role' => 'admin'
    ],
    [
        'nome' => 'Gestor User',
        'email' => 'gestor@clinfec.com.br',
        'senha' => 'gestor123',
        'role' => 'gestor'
    ],
    [
        'nome' => 'Usuario Basico',
        'email' => 'usuario@clinfec.com.br',
        'senha' => 'usuario123',
        'role' => 'usuario'
    ],
];

foreach ($users as $user) {
    $hash = password_hash($user['senha'], PASSWORD_DEFAULT);
    echo "INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES\n";
    echo "('{$user['nome']}', '{$user['email']}', '{$hash}', '{$user['role']}', 1, NOW(), NOW())\n";
    echo "ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);\n\n";
}
