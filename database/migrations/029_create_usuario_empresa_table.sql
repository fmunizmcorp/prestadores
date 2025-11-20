-- ============================================================================
-- SPRINT 68.3 - Criação da tabela usuario_empresa
-- ============================================================================
-- Data: 2025-11-16
-- Objetivo: Resolver erro "Table 'db_prestadores.usuario_empresa' doesn't exist"
-- QA Report: Erro ao criar atividades - tabela de vínculo faltando
-- ============================================================================

-- Verificar se a tabela já existe antes de criar
DROP TABLE IF EXISTS `usuario_empresa`;

CREATE TABLE `usuario_empresa` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  
  -- Relacionamentos
  `usuario_id` INT NOT NULL COMMENT 'ID do usuário',
  `empresa_id` INT NOT NULL COMMENT 'ID da empresa (prestadora ou tomadora)',
  
  -- Dados do vínculo
  `cargo` VARCHAR(100) NULL COMMENT 'Cargo do usuário na empresa',
  `tipo_empresa` ENUM('prestadora', 'tomadora') DEFAULT 'prestadora' COMMENT 'Tipo de empresa',
  
  -- Permissões específicas (opcional)
  `pode_editar` TINYINT(1) DEFAULT 1 COMMENT 'Pode editar dados da empresa',
  `pode_criar_projetos` TINYINT(1) DEFAULT 1 COMMENT 'Pode criar projetos',
  `pode_gerenciar_equipe` TINYINT(1) DEFAULT 0 COMMENT 'Pode gerenciar equipe',
  
  -- Status
  `ativo` TINYINT(1) DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  
  -- Auditoria
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  
  -- Índices para performance
  INDEX `idx_usuario_id` (`usuario_id`),
  INDEX `idx_empresa_id` (`empresa_id`),
  INDEX `idx_tipo_empresa` (`tipo_empresa`),
  INDEX `idx_ativo` (`ativo`),
  
  -- Constraint única: um usuário não pode estar vinculado duas vezes à mesma empresa
  UNIQUE KEY `uk_usuario_empresa` (`usuario_id`, `empresa_id`),
  
  -- Foreign keys
  CONSTRAINT `fk_usuario_empresa_usuario` 
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
  
  -- NOTA: Foreign key para empresa_id será adicionada após criar as tabelas empresas
  -- Se já existirem, descomentar abaixo:
  -- ,CONSTRAINT `fk_usuario_empresa_empresa_prestadora` 
  --   FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) 
  --   ON DELETE CASCADE ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Vínculo entre usuários e empresas (prestadoras e tomadoras)';

-- ============================================================================
-- Dados de exemplo (OPCIONAL - vincular usuários de teste às empresas)
-- ============================================================================

-- Exemplo: Vincular master user a uma empresa se ela existir
-- INSERT INTO `usuario_empresa` (`usuario_id`, `empresa_id`, `cargo`, `tipo_empresa`, `ativo`) 
-- SELECT 1, e.id, 'Administrador', 'prestadora', 1
-- FROM (SELECT id FROM empresas LIMIT 1) e
-- WHERE NOT EXISTS (
--     SELECT 1 FROM usuario_empresa WHERE usuario_id = 1 AND empresa_id = e.id
-- );

-- ============================================================================
-- Validação
-- ============================================================================

-- Verificar se a tabela foi criada
SELECT 
    TABLE_NAME, 
    ENGINE, 
    TABLE_ROWS, 
    CREATE_TIME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME = 'usuario_empresa';

-- Verificar colunas da tabela
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME = 'usuario_empresa'
ORDER BY ORDINAL_POSITION;

-- Verificar índices
SELECT 
    INDEX_NAME, 
    COLUMN_NAME, 
    SEQ_IN_INDEX, 
    NON_UNIQUE
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME = 'usuario_empresa'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- ============================================================================
-- FIM DA MIGRATION
-- ============================================================================
-- Status: PRONTO PARA DEPLOY
-- Testing: Pendente
-- Refs: SPRINT 68.3, QA Report 2025-11-16
-- IMPORTANTE: Após criar tabelas 'empresas', adicionar foreign key para empresa_id
-- ============================================================================
