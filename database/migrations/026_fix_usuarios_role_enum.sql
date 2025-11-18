-- Sprint 67: Correção do ENUM role da tabela usuarios
-- Data: 2025-11-16
-- Problema: ENUM em produção difere da migration 001
-- Objetivo: Alinhar roles para suportar todos os perfis necessários

-- Migration original tinha: ENUM('master', 'admin', 'gestor', 'usuario')
-- Produção tem: ENUM('admin', 'gerente', 'usuario', 'financeiro')
-- Novo ENUM consolidado: ENUM('master', 'admin', 'gerente', 'gestor', 'usuario', 'financeiro')

USE db_prestadores;

-- Step 1: Verificar estrutura atual
SELECT 'Estrutura ANTES da alteração:' AS status;
SHOW COLUMNS FROM usuarios LIKE 'role';

-- Step 2: Alterar ENUM para incluir TODOS os valores necessários
-- IMPORTANTE: Incluir valores antigos + novos para não perder dados
ALTER TABLE usuarios 
MODIFY COLUMN role ENUM(
    'master',      -- Perfil super admin (acesso total)
    'admin',       -- Perfil administrador (gestão geral)
    'gerente',     -- Perfil gerente (equivalente a gestor)
    'gestor',      -- Perfil gestor (mantido para compatibilidade)
    'usuario',     -- Perfil usuário básico
    'financeiro'   -- Perfil financeiro (específico)
) DEFAULT 'usuario' 
COMMENT 'Perfil de acesso do usuário - Sprint 67 fix';

-- Step 3: Verificar estrutura após alteração
SELECT 'Estrutura APÓS alteração:' AS status;
SHOW COLUMNS FROM usuarios LIKE 'role';

-- Step 4: Verificar usuários existentes
SELECT 
    'Usuários cadastrados:' AS info,
    COUNT(*) AS total,
    GROUP_CONCAT(DISTINCT role ORDER BY role) AS roles_em_uso
FROM usuarios;

-- Step 5: Listar todos os usuários com suas roles
SELECT 
    id,
    nome,
    email,
    role,
    ativo,
    created_at
FROM usuarios
ORDER BY 
    CASE role
        WHEN 'master' THEN 1
        WHEN 'admin' THEN 2
        WHEN 'gerente' THEN 3
        WHEN 'gestor' THEN 4
        WHEN 'financeiro' THEN 5
        WHEN 'usuario' THEN 6
    END,
    nome;

-- Resultado esperado:
-- ✅ ENUM atualizado com 6 valores possíveis
-- ✅ Todos usuários mantidos com suas roles atuais
-- ✅ Compatibilidade com código antigo e novo

-- Notas:
-- 1. 'gerente' e 'gestor' são equivalentes (mantidos ambos por compatibilidade)
-- 2. Migration é idempotente (pode rodar múltiplas vezes sem erro)
-- 3. Não altera dados existentes, apenas expande opções
