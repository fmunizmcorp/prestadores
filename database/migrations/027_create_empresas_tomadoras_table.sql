-- ============================================================================
-- SPRINT 68.1 - Criação da tabela empresas_tomadoras
-- ============================================================================
-- Data: 2025-11-16
-- Objetivo: Resolver erro "Table 'db_prestadores.empresas_tomadoras' doesn't exist"
-- QA Report: 14 testes falharam / 18 total (77.8% failure rate)
-- ============================================================================

-- Verificar se a tabela já existe antes de criar
DROP TABLE IF EXISTS `empresas_tomadoras`;

CREATE TABLE `empresas_tomadoras` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  
  -- Dados da empresa
  `razao_social` VARCHAR(255) NOT NULL COMMENT 'Razão social da empresa',
  `nome_fantasia` VARCHAR(255) NOT NULL COMMENT 'Nome fantasia',
  `cnpj` VARCHAR(18) NOT NULL UNIQUE COMMENT 'CNPJ formatado',
  `inscricao_estadual` VARCHAR(20) NULL COMMENT 'Inscrição estadual',
  `inscricao_municipal` VARCHAR(20) NULL COMMENT 'Inscrição municipal',
  
  -- Endereço
  `cep` VARCHAR(9) NULL COMMENT 'CEP formatado',
  `logradouro` VARCHAR(255) NULL COMMENT 'Endereço',
  `numero` VARCHAR(20) NULL COMMENT 'Número',
  `complemento` VARCHAR(100) NULL COMMENT 'Complemento',
  `bairro` VARCHAR(100) NULL COMMENT 'Bairro',
  `cidade` VARCHAR(100) NULL COMMENT 'Cidade',
  `estado` CHAR(2) NULL COMMENT 'UF do estado',
  
  -- Contatos
  `email_principal` VARCHAR(255) NULL COMMENT 'Email principal',
  `email_financeiro` VARCHAR(255) NULL COMMENT 'Email do financeiro',
  `email_projetos` VARCHAR(255) NULL COMMENT 'Email de projetos',
  `telefone_principal` VARCHAR(20) NULL COMMENT 'Telefone principal',
  `telefone_secundario` VARCHAR(20) NULL COMMENT 'Telefone secundário',
  `celular` VARCHAR(20) NULL COMMENT 'Celular',
  `whatsapp` VARCHAR(20) NULL COMMENT 'WhatsApp',
  `site` VARCHAR(255) NULL COMMENT 'Website',
  
  -- Dados financeiros
  `dia_fechamento` INT UNSIGNED DEFAULT 30 COMMENT 'Dia do mês para fechamento',
  `dia_pagamento` INT UNSIGNED DEFAULT 5 COMMENT 'Dia do mês para pagamento',
  `forma_pagamento_preferencial` VARCHAR(50) NULL COMMENT 'Forma de pagamento preferida',
  `banco` VARCHAR(100) NULL COMMENT 'Banco',
  `agencia` VARCHAR(20) NULL COMMENT 'Agência',
  `conta` VARCHAR(30) NULL COMMENT 'Conta',
  `tipo_conta` ENUM('corrente', 'poupanca') NULL COMMENT 'Tipo de conta',
  
  -- Outros
  `logo` VARCHAR(255) NULL COMMENT 'Caminho da logo',
  `observacoes` TEXT NULL COMMENT 'Observações gerais',
  `ativo` TINYINT(1) DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  
  -- Auditoria
  `created_by` INT NULL COMMENT 'ID do usuário que criou',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_by` INT NULL COMMENT 'ID do usuário que atualizou',
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  
  -- Índices para performance
  INDEX `idx_cnpj` (`cnpj`),
  INDEX `idx_nome_fantasia` (`nome_fantasia`),
  INDEX `idx_razao_social` (`razao_social`),
  INDEX `idx_ativo` (`ativo`),
  INDEX `idx_cidade` (`cidade`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_email_principal` (`email_principal`),
  INDEX `idx_created_at` (`created_at`),
  
  -- Foreign keys
  CONSTRAINT `fk_empresas_tomadoras_created_by` 
    FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_empresas_tomadoras_updated_by` 
    FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Empresas tomadoras de serviços (clientes)';

-- ============================================================================
-- Tabela auxiliar: empresa_tomadora_responsaveis
-- ============================================================================
-- Armazena responsáveis/contatos de cada empresa tomadora

DROP TABLE IF EXISTS `empresa_tomadora_responsaveis`;

CREATE TABLE `empresa_tomadora_responsaveis` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `empresa_tomadora_id` INT UNSIGNED NOT NULL COMMENT 'ID da empresa tomadora',
  
  -- Dados do responsável
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome completo',
  `cargo` VARCHAR(100) NULL COMMENT 'Cargo/função',
  `departamento` VARCHAR(100) NULL COMMENT 'Departamento',
  
  -- Contatos
  `email` VARCHAR(255) NULL COMMENT 'Email',
  `telefone` VARCHAR(20) NULL COMMENT 'Telefone',
  `celular` VARCHAR(20) NULL COMMENT 'Celular',
  `whatsapp` VARCHAR(20) NULL COMMENT 'WhatsApp',
  
  -- Preferências
  `recebe_notificacoes` TINYINT(1) DEFAULT 1 COMMENT 'Recebe notificações?',
  `tipo_responsavel` ENUM('principal', 'financeiro', 'projetos', 'operacional', 'outro') 
    DEFAULT 'operacional' COMMENT 'Tipo de responsável',
  
  -- Status
  `ativo` TINYINT(1) DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `observacoes` TEXT NULL COMMENT 'Observações',
  
  -- Auditoria
  `created_by` INT NULL COMMENT 'ID do usuário que criou',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_by` INT NULL COMMENT 'ID do usuário que atualizou',
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  
  -- Índices
  INDEX `idx_empresa_tomadora_id` (`empresa_tomadora_id`),
  INDEX `idx_nome` (`nome`),
  INDEX `idx_email` (`email`),
  INDEX `idx_ativo` (`ativo`),
  INDEX `idx_tipo_responsavel` (`tipo_responsavel`),
  
  -- Foreign keys
  CONSTRAINT `fk_resp_empresa_tomadora` 
    FOREIGN KEY (`empresa_tomadora_id`) REFERENCES `empresas_tomadoras` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_created_by` 
    FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_updated_by` 
    FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Responsáveis/contatos das empresas tomadoras';

-- ============================================================================
-- Dados de exemplo (OPCIONAL - comentado por padrão)
-- ============================================================================

-- INSERT INTO `empresas_tomadoras` (
--   `razao_social`, `nome_fantasia`, `cnpj`, 
--   `email_principal`, `telefone_principal`, 
--   `cidade`, `estado`, `ativo`, `created_by`
-- ) VALUES (
--   'Empresa Exemplo LTDA', 'Empresa Exemplo', '00.000.000/0001-00',
--   'contato@exemplo.com.br', '(11) 1234-5678',
--   'São Paulo', 'SP', 1, 1
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
  AND TABLE_NAME = 'empresas_tomadoras';

-- Verificar colunas da tabela
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME = 'empresas_tomadoras'
ORDER BY ORDINAL_POSITION;

-- ============================================================================
-- FIM DA MIGRATION
-- ============================================================================
-- Status: PRONTO PARA DEPLOY
-- Testing: Pendente
-- Refs: SPRINT 68.1, QA Report 2025-11-16
-- ============================================================================
