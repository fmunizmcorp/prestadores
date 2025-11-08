-- ============================================
-- MIGRATION 010: INSERIR USUÁRIO MASTER
-- Versão: 10
-- Data: 2024-11-08
-- Descrição: Cria usuário master padrão para acesso inicial ao sistema
-- ============================================

-- Verificar se já existe usuário master
-- Se não existir, inserir

SET @master_count = (SELECT COUNT(*) FROM usuarios WHERE email = 'master@clinfec.com.br');

-- Inserir usuário master apenas se não existir
INSERT INTO usuarios (
    nome, 
    email, 
    senha, 
    role, 
    ativo, 
    email_verificado,
    created_at,
    updated_at
)
SELECT
    'Administrador Master',
    'master@clinfec.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'master',
    TRUE,
    TRUE,
    NOW(),
    NOW()
WHERE @master_count = 0;

-- Inserir usuário admin de teste apenas se não existir
SET @admin_count = (SELECT COUNT(*) FROM usuarios WHERE email = 'admin@clinfec.com.br');

INSERT INTO usuarios (
    nome, 
    email, 
    senha, 
    role, 
    ativo, 
    email_verificado,
    created_at,
    updated_at
)
SELECT
    'Administrador',
    'admin@clinfec.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'admin',
    TRUE,
    TRUE,
    NOW(),
    NOW()
WHERE @admin_count = 0;

-- Inserir usuário gestor de teste apenas se não existir
SET @gestor_count = (SELECT COUNT(*) FROM usuarios WHERE email = 'gestor@clinfec.com.br');

INSERT INTO usuarios (
    nome, 
    email, 
    senha, 
    role, 
    ativo, 
    email_verificado,
    created_at,
    updated_at
)
SELECT
    'Gestor',
    'gestor@clinfec.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'gestor',
    TRUE,
    TRUE,
    NOW(),
    NOW()
WHERE @gestor_count = 0;

-- ============================================
-- CREDENCIAIS DE ACESSO PADRÃO:
-- ============================================
-- 
-- MASTER:
-- E-mail: master@clinfec.com.br
-- Senha: password
-- 
-- ADMIN:
-- E-mail: admin@clinfec.com.br
-- Senha: password
-- 
-- GESTOR:
-- E-mail: gestor@clinfec.com.br
-- Senha: password
-- 
-- ============================================
-- IMPORTANTE: Altere as senhas após o primeiro acesso!
-- ============================================
