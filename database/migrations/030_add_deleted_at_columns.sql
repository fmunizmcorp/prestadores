-- ============================================================================
-- SPRINT 68 - Adicionar coluna deleted_at para soft delete
-- ============================================================================
-- Data: 2025-11-17
-- Objetivo: Resolver erro "Unknown column 'deleted_at'"
-- Implementa soft delete pattern em todas as tabelas necessárias
-- ============================================================================

-- Verificar tabelas existentes
SELECT TABLE_NAME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME IN ('empresas_prestadoras', 'servicos', 'projetos', 'atividades', 'contratos');

-- ============================================================================
-- Adicionar deleted_at em empresas_prestadoras
-- ============================================================================

ALTER TABLE `empresas_prestadoras` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão lógica (soft delete)' 
AFTER `updated_at`;

-- ============================================================================
-- Adicionar deleted_at em servicos  
-- ============================================================================

ALTER TABLE `servicos` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão lógica (soft delete)' 
AFTER `updated_at`;

-- ============================================================================
-- Adicionar deleted_at em projetos
-- ============================================================================

ALTER TABLE `projetos` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão lógica (soft delete)' 
AFTER `updated_at`;

-- ============================================================================
-- Adicionar deleted_at em atividades
-- ============================================================================

ALTER TABLE `atividades` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão lógica (soft delete)' 
AFTER `updated_at`;

-- ============================================================================
-- Adicionar deleted_at em contratos (se não existir)
-- ============================================================================

ALTER TABLE `contratos` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão lógica (soft delete)' 
AFTER `updated_at`;

-- ============================================================================
-- Criar índices para performance
-- ============================================================================

ALTER TABLE `empresas_prestadoras` ADD INDEX `idx_deleted_at` (`deleted_at`);
ALTER TABLE `servicos` ADD INDEX `idx_deleted_at` (`deleted_at`);
ALTER TABLE `projetos` ADD INDEX `idx_deleted_at` (`deleted_at`);
ALTER TABLE `atividades` ADD INDEX `idx_deleted_at` (`deleted_at`);
ALTER TABLE `contratos` ADD INDEX `idx_deleted_at` (`deleted_at`);

-- ============================================================================
-- Validação
-- ============================================================================

-- Verificar colunas adicionadas
SELECT 
    TABLE_NAME, 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE,
    COLUMN_COMMENT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME IN ('empresas_prestadoras', 'servicos', 'projetos', 'atividades', 'contratos')
  AND COLUMN_NAME = 'deleted_at'
ORDER BY TABLE_NAME;

-- Verificar índices criados
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME IN ('empresas_prestadoras', 'servicos', 'projetos', 'atividades', 'contratos')
  AND INDEX_NAME = 'idx_deleted_at'
ORDER BY TABLE_NAME;

-- ============================================================================
-- FIM DA MIGRATION
-- ============================================================================
-- Status: PRONTO PARA DEPLOY
-- Testing: Pendente
-- Refs: SPRINT 68, QA Report 2025-11-16
-- IMPORTANTE: Soft delete pattern - NULL = ativo, NOT NULL = deletado
-- ============================================================================
