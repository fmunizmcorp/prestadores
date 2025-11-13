-- =========================================
-- FIX CREDENTIALS V7 - Sprint 16
-- =========================================
-- Purpose: Reset user passwords to known values for testing
-- Date: 2025-11-12
--
-- This script ensures all test users have the correct password
-- Password: "password" for all users
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
--
-- =========================================

-- First, let's see what users exist
SELECT 'CURRENT USERS:' as info;
SELECT id, nome, email, perfil, ativo, created_at 
FROM usuarios 
ORDER BY id;

SELECT '\n\n' as separator;

-- Update all Clinfec users to have password = "password"
UPDATE usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    ativo = 1,
    updated_at = NOW()
WHERE email LIKE '%@clinfec.com%';

SELECT 'UPDATED USERS:' as info;
SELECT id, nome, email, perfil, ativo, updated_at
FROM usuarios 
WHERE email LIKE '%@clinfec.com%';

-- Ensure we have the primary test users
-- If they don't exist, create them

INSERT IGNORE INTO usuarios (nome, email, senha, perfil, ativo, created_at, updated_at)
VALUES 
('Master User', 'master@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master', 1, NOW(), NOW()),
('Admin User', 'admin@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW(), NOW()),
('Gestor User', 'gestor@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gestor', 1, NOW(), NOW());

SELECT '\n\nFINAL USER LIST:' as info;
SELECT id, nome, email, perfil, ativo 
FROM usuarios 
ORDER BY id;

SELECT '\n\nCREDENTIALS FOR TESTING:' as info;
SELECT 
    email,
    perfil as role,
    'password' as senha_para_teste,
    ativo as active
FROM usuarios
WHERE email LIKE '%@clinfec.com%'
ORDER BY FIELD(perfil, 'master', 'admin', 'gestor');

-- =========================================
-- VERIFICATION
-- =========================================
SELECT '\n\nVERIFICATION:' as info;
SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN perfil = 'master' THEN 1 ELSE 0 END) as master_users,
    SUM(CASE WHEN perfil = 'admin' THEN 1 ELSE 0 END) as admin_users,
    SUM(CASE WHEN perfil = 'gestor' THEN 1 ELSE 0 END) as gestor_users
FROM usuarios;
