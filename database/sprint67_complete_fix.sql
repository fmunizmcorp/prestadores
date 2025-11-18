-- ========================================================================
-- SPRINT 67 - CORREÇÃO COMPLETA DO SISTEMA DE LOGIN
-- ========================================================================
-- Data: 2025-11-16
-- Autor: GenSpark AI Developer
-- Objetivo: Corrigir ENUM roles e garantir login funcional
-- 
-- Este script deve ser executado em PRODUÇÃO para corrigir os problemas
-- identificados no relatório QA após deployment do Sprint 66.
-- ========================================================================

USE db_prestadores;

-- ========================================================================
-- PARTE 1: CORREÇÃO DO ENUM ROLES
-- ========================================================================

SELECT '========================================' AS '';
SELECT 'SPRINT 67 - INICIANDO CORREÇÕES' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';

SELECT 'PARTE 1: Verificando estrutura atual da tabela usuarios...' AS '';
SHOW COLUMNS FROM usuarios LIKE 'role';
SELECT '' AS '';

SELECT 'PARTE 2: Corrigindo ENUM role (adicionando todos os valores necessários)...' AS '';

-- Alterar ENUM para suportar todos os perfis
ALTER TABLE usuarios 
MODIFY COLUMN role ENUM(
    'master',      -- Super admin (acesso total)
    'admin',       -- Administrador (gestão geral)
    'gerente',     -- Gerente (equivalente a gestor - compatibilidade produção)
    'gestor',      -- Gestor (migration original)
    'usuario',     -- Usuário básico
    'financeiro'   -- Financeiro (específico - compatibilidade produção)
) DEFAULT 'usuario' 
COMMENT 'Perfil de acesso do usuário - Sprint 67';

SELECT '✅ ENUM atualizado com sucesso!' AS '';
SELECT '' AS '';

SELECT 'PARTE 3: Verificando estrutura APÓS alteração...' AS '';
SHOW COLUMNS FROM usuarios LIKE 'role';
SELECT '' AS '';

-- ========================================================================
-- PARTE 2: CRIAÇÃO/ATUALIZAÇÃO DOS USUÁRIOS DE TESTE
-- ========================================================================

SELECT '========================================' AS '';
SELECT 'PARTE 4: Criando/Atualizando usuários de teste...' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';

-- Usuário 1: MASTER
SELECT '1. Criando/Atualizando usuário MASTER...' AS '';
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
SELECT '   ✅ Master criado/atualizado' AS '';

-- Usuário 2: ADMIN
SELECT '2. Criando/Atualizando usuário ADMIN...' AS '';
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
SELECT '   ✅ Admin criado/atualizado' AS '';

-- Usuário 3: GESTOR
SELECT '3. Criando/Atualizando usuário GESTOR...' AS '';
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
SELECT '   ✅ Gestor criado/atualizado' AS '';

-- Usuário 4: USUARIO BÁSICO
SELECT '4. Criando/Atualizando usuário BÁSICO...' AS '';
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
SELECT '   ✅ Usuario Básico criado/atualizado' AS '';

SELECT '' AS '';

-- ========================================================================
-- PARTE 3: VALIDAÇÃO E RELATÓRIO FINAL
-- ========================================================================

SELECT '========================================' AS '';
SELECT 'PARTE 5: VALIDAÇÃO FINAL' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';

-- Verificar usuários teste criados
SELECT '✅ USUÁRIOS DE TESTE:' AS '';
SELECT 
    id,
    nome,
    email,
    role,
    CASE 
        WHEN ativo = 1 THEN '✅ Ativo'
        ELSE '❌ Inativo'
    END AS status,
    created_at
FROM usuarios 
WHERE email IN (
    'master@clinfec.com.br', 
    'admin@clinfec.com.br', 
    'gestor@clinfec.com.br', 
    'usuario@clinfec.com.br'
)
ORDER BY 
    CASE role
        WHEN 'master' THEN 1
        WHEN 'admin' THEN 2
        WHEN 'gestor' THEN 3
        WHEN 'gerente' THEN 4
        WHEN 'usuario' THEN 5
    END;

SELECT '' AS '';

-- Estatísticas gerais
SELECT '📊 ESTATÍSTICAS:' AS '';
SELECT 
    COUNT(*) AS total_usuarios,
    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) AS usuarios_ativos,
    GROUP_CONCAT(DISTINCT role ORDER BY role) AS roles_em_uso
FROM usuarios;

SELECT '' AS '';

-- Distribuição por role
SELECT '📈 DISTRIBUIÇÃO POR ROLE:' AS '';
SELECT 
    role,
    COUNT(*) AS quantidade,
    GROUP_CONCAT(email ORDER BY email SEPARATOR ', ') AS usuarios
FROM usuarios
GROUP BY role
ORDER BY 
    CASE role
        WHEN 'master' THEN 1
        WHEN 'admin' THEN 2
        WHEN 'gerente' THEN 3
        WHEN 'gestor' THEN 4
        WHEN 'financeiro' THEN 5
        WHEN 'usuario' THEN 6
    END;

SELECT '' AS '';
SELECT '========================================' AS '';
SELECT '✅ SPRINT 67 - CORREÇÕES CONCLUÍDAS' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';
SELECT 'PRÓXIMO PASSO: Testar login com os 4 usuários' AS '';
SELECT '' AS '';
SELECT 'CREDENCIAIS DE TESTE:' AS '';
SELECT '  1. master@clinfec.com.br / password' AS '';
SELECT '  2. admin@clinfec.com.br / admin123' AS '';
SELECT '  3. gestor@clinfec.com.br / password' AS '';
SELECT '  4. usuario@clinfec.com.br / password' AS '';
SELECT '' AS '';

-- ========================================================================
-- FIM DO SCRIPT SPRINT 67
-- ========================================================================
