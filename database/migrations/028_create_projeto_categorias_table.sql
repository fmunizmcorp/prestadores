-- ============================================================================
-- SPRINT 68.2 - Criação da tabela projeto_categorias
-- ============================================================================
-- Data: 2025-11-16
-- Objetivo: Resolver erro "Table 'db_prestadores.projeto_categorias' doesn't exist"
-- QA Report: Erro ao criar projetos - tabela faltando
-- ============================================================================

-- Verificar se a tabela já existe antes de criar
DROP TABLE IF EXISTS `projeto_categorias`;

CREATE TABLE `projeto_categorias` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  
  -- Dados da categoria
  `nome` VARCHAR(100) NOT NULL COMMENT 'Nome da categoria',
  `descricao` TEXT NULL COMMENT 'Descrição detalhada',
  `cor` VARCHAR(7) DEFAULT '#6c757d' COMMENT 'Cor hexadecimal para identificação visual',
  `icone` VARCHAR(50) DEFAULT 'folder' COMMENT 'Ícone FontAwesome ou Bootstrap Icons',
  
  -- Status
  `ativo` TINYINT(1) DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  
  -- Auditoria
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  
  -- Índices para performance
  INDEX `idx_nome` (`nome`),
  INDEX `idx_ativo` (`ativo`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Categorias de projetos para organização e classificação';

-- ============================================================================
-- Inserir categorias padrão
-- ============================================================================

INSERT INTO `projeto_categorias` (`nome`, `descricao`, `cor`, `icone`, `ativo`) VALUES
('Desenvolvimento', 'Projetos de desenvolvimento de software e aplicações', '#007bff', 'code', 1),
('Infraestrutura', 'Projetos de infraestrutura e servidores', '#28a745', 'server', 1),
('Consultoria', 'Projetos de consultoria e assessoria', '#17a2b8', 'briefcase', 1),
('Manutenção', 'Projetos de manutenção e suporte', '#ffc107', 'tools', 1),
('Treinamento', 'Projetos de treinamento e capacitação', '#6f42c1', 'graduation-cap', 1),
('Implantação', 'Projetos de implantação de sistemas', '#e83e8c', 'rocket', 1),
('Pesquisa', 'Projetos de pesquisa e desenvolvimento', '#fd7e14', 'flask', 1),
('Outros', 'Outros tipos de projetos', '#6c757d', 'folder', 1);

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
  AND TABLE_NAME = 'projeto_categorias';

-- Verificar colunas da tabela
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'db_prestadores' 
  AND TABLE_NAME = 'projeto_categorias'
ORDER BY ORDINAL_POSITION;

-- Verificar categorias inseridas
SELECT id, nome, cor, icone, ativo FROM projeto_categorias ORDER BY nome;

-- ============================================================================
-- FIM DA MIGRATION
-- ============================================================================
-- Status: PRONTO PARA DEPLOY
-- Testing: Pendente
-- Refs: SPRINT 68.2, QA Report 2025-11-16
-- ============================================================================
