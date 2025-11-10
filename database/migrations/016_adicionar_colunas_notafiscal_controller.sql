-- ============================================
-- MIGRATION 016: Adicionar Colunas Compatíveis com NotaFiscalController
-- Versão: 16
-- Data: 2025-11-10
-- Descrição: Adiciona colunas esperadas pelo Controller mantendo compatibilidade
--            com schema original. Total: 16 colunas adicionadas.
-- ============================================

-- Verificar se migration já foi aplicada
SET @migration_exists = (SELECT COUNT(*) FROM system_version WHERE version = 16);

-- Só executar se ainda não foi aplicada
-- Adicionar colunas se não existirem

-- VALORES DETALHADOS
SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_produtos'),
    'SELECT "Column valor_produtos already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_produtos DECIMAL(15,2) DEFAULT 0.00 COMMENT "Valor de produtos" AFTER valor_bruto'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_servicos'),
    'SELECT "Column valor_servicos already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_servicos DECIMAL(15,2) DEFAULT 0.00 COMMENT "Valor de serviços" AFTER valor_produtos'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_total'),
    'SELECT "Column valor_total already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_total DECIMAL(15,2) DEFAULT 0.00 COMMENT "Valor total" AFTER valor_servicos'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_frete'),
    'SELECT "Column valor_frete already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_frete DECIMAL(15,2) DEFAULT 0.00 COMMENT "Valor de frete" AFTER valor_total'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_seguro'),
    'SELECT "Column valor_seguro already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_seguro DECIMAL(15,2) DEFAULT 0.00 COMMENT "Valor de seguro" AFTER valor_frete'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_outras_despesas'),
    'SELECT "Column valor_outras_despesas already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_outras_despesas DECIMAL(15,2) DEFAULT 0.00 COMMENT "Outras despesas" AFTER valor_seguro'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- IMPOSTOS ADICIONAIS
SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_base_calculo'),
    'SELECT "Column valor_base_calculo already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_base_calculo DECIMAL(15,2) DEFAULT 0.00 COMMENT "Base de cálculo geral" AFTER base_calculo_icms'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'valor_icms_st'),
    'SELECT "Column valor_icms_st already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN valor_icms_st DECIMAL(15,2) DEFAULT 0.00 COMMENT "Valor ICMS ST" AFTER valor_icms'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- DOCUMENTOS
SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'pdf_danfe'),
    'SELECT "Column pdf_danfe already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN pdf_danfe VARCHAR(500) COMMENT "Caminho do PDF DANFE (alias de pdf_path)" AFTER pdf_path'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- INFORMAÇÕES ADICIONAIS
SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'informacoes_adicionais'),
    'SELECT "Column informacoes_adicionais already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN informacoes_adicionais TEXT COMMENT "Informações adicionais da nota" AFTER observacoes'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- METADADOS DE AUDITORIA
SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'criado_por'),
    'SELECT "Column criado_por already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN criado_por INT UNSIGNED COMMENT "Usuário que criou (alias de emitido_por)" AFTER emitido_por'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'atualizado_por'),
    'SELECT "Column atualizado_por already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN atualizado_por INT UNSIGNED COMMENT "Último usuário que atualizou" AFTER criado_por'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'deleted_at'),
    'SELECT "Column deleted_at already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN deleted_at TIMESTAMP NULL COMMENT "Data de exclusão lógica" AFTER atualizado_em'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'data_autorizacao'),
    'SELECT "Column data_autorizacao already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN data_autorizacao DATETIME COMMENT "Data/hora de autorização SEFAZ" AFTER protocolo_autorizacao'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- TIMESTAMPS PADRONIZADOS (aliases)
SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'created_at'),
    'SELECT "Column created_at already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT "Data de criação (alias)" AFTER criado_em'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND COLUMN_NAME = 'updated_at'),
    'SELECT "Column updated_at already exists"',
    'ALTER TABLE notas_fiscais ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT "Data de atualização (alias)" AFTER created_at'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- INDICES ADICIONAIS (verificar se já existem)
SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND INDEX_NAME = 'idx_deleted_at'),
    'SELECT "Index idx_deleted_at already exists"',
    'CREATE INDEX idx_deleted_at ON notas_fiscais(deleted_at)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND INDEX_NAME = 'idx_criado_por'),
    'SELECT "Index idx_criado_por already exists"',
    'CREATE INDEX idx_criado_por ON notas_fiscais(criado_por)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(
    EXISTS(SELECT NULL FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() 
           AND TABLE_NAME = 'notas_fiscais' AND INDEX_NAME = 'idx_atualizado_por'),
    'SELECT "Index idx_atualizado_por already exists"',
    'CREATE INDEX idx_atualizado_por ON notas_fiscais(atualizado_por)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar versão do sistema
INSERT INTO system_version (version, description, applied_at)
SELECT 16, 'Adicionar colunas compatíveis com NotaFiscalController', NOW()
WHERE NOT EXISTS (SELECT 1 FROM system_version WHERE version = 16);

-- ============================================
-- MIGRATION 016 COMPLETA
-- 16 colunas adicionadas + 3 índices
-- Schema 100% compatível com NotaFiscalController
-- ============================================
