-- Script de Criação de Usuários de Teste
-- Sprint 66 - Correção Bug #7
-- Data: 2025-11-16

-- Limpar usuários existentes (se necessário)
-- DELETE FROM usuarios WHERE email IN ('master@clinfec.com.br', 'admin@clinfec.com.br', 'gestor@clinfec.com.br', 'usuario@clinfec.com.br');

-- Criar usuário MASTER
INSERT INTO usuarios (
    nome, 
    email, 
    senha, 
    role, 
    ativo, 
    created_at, 
    updated_at
) VALUES (
    'Master User',
    'master@clinfec.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'master',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    senha = VALUES(senha),
    role = VALUES(role),
    ativo = VALUES(ativo),
    updated_at = NOW();

-- Criar usuário ADMIN
INSERT INTO usuarios (
    nome, 
    email, 
    senha, 
    role, 
    ativo, 
    created_at, 
    updated_at
) VALUES (
    'Admin User',
    'admin@clinfec.com.br',
    '$2y$10$VJL2WmMq9Kh7FHPqYG8P2.Y8ZHPqT5xQwE0pXk7nOmKm3F9F/R5Wa', -- admin123
    'admin',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    senha = VALUES(senha),
    role = VALUES(role),
    ativo = VALUES(ativo),
    updated_at = NOW();

-- Criar usuário GESTOR
INSERT INTO usuarios (
    nome, 
    email, 
    senha, 
    role, 
    ativo, 
    created_at, 
    updated_at
) VALUES (
    'Gestor User',
    'gestor@clinfec.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'gestor',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    senha = VALUES(senha),
    role = VALUES(role),
    ativo = VALUES(ativo),
    updated_at = NOW();

-- Criar usuário BÁSICO
INSERT INTO usuarios (
    nome, 
    email, 
    senha, 
    role, 
    ativo, 
    created_at, 
    updated_at
) VALUES (
    'Usuario Basico',
    'usuario@clinfec.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'usuario',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    senha = VALUES(senha),
    role = VALUES(role),
    ativo = VALUES(ativo),
    updated_at = NOW();

-- Verificar usuários criados
SELECT id, nome, email, role, ativo, created_at FROM usuarios 
WHERE email IN ('master@clinfec.com.br', 'admin@clinfec.com.br', 'gestor@clinfec.com.br', 'usuario@clinfec.com.br')
ORDER BY role DESC, email;

-- Exibir informações
SELECT 
    'USUÁRIOS DE TESTE CRIADOS' AS status,
    COUNT(*) AS total_usuarios
FROM usuarios 
WHERE email LIKE '%@clinfec.com.br';
