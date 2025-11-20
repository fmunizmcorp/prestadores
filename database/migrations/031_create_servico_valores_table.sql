-- Migration 031: Create servico_valores table
-- SPRINT 68.3.3: Tabela para armazenar valores de serviços por período
-- Data: 2025-11-17
-- Autor: SCRUM Sprint 68
-- FIX: Usar INT ao invés de INT UNSIGNED para compatibilidade com servicos.id e projetos.id

CREATE TABLE IF NOT EXISTS `servico_valores` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `servico_id` INT NOT NULL COMMENT 'FK para servicos.id',
  `projeto_id` INT NULL COMMENT 'FK para projetos.id (opcional - se nulo, valor padrão do serviço)',
  `valor_unitario` DECIMAL(10,2) NOT NULL COMMENT 'Valor unitário do serviço',
  `quantidade` DECIMAL(10,2) DEFAULT 1.00 COMMENT 'Quantidade contratada',
  `valor_total` DECIMAL(10,2) GENERATED ALWAYS AS (valor_unitario * quantidade) STORED COMMENT 'Valor total calculado',
  `data_vigencia_inicio` DATE NOT NULL COMMENT 'Início da vigência do valor',
  `data_vigencia_fim` DATE NULL COMMENT 'Fim da vigência (NULL = vigente)',
  `observacoes` TEXT NULL COMMENT 'Observações sobre o valor',
  `ativo` TINYINT(1) DEFAULT 1 COMMENT '1 = ativo, 0 = inativo',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL COMMENT 'Soft delete',
  
  -- Índices
  INDEX `idx_servico_id` (`servico_id`),
  INDEX `idx_projeto_id` (`projeto_id`),
  INDEX `idx_deleted_at` (`deleted_at`),
  INDEX `idx_vigencia` (`data_vigencia_inicio`, `data_vigencia_fim`),
  INDEX `idx_ativo` (`ativo`),
  
  -- Foreign Keys
  CONSTRAINT `fk_servico_valores_servico` 
    FOREIGN KEY (`servico_id`) 
    REFERENCES `servicos` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
    
  CONSTRAINT `fk_servico_valores_projeto` 
    FOREIGN KEY (`projeto_id`) 
    REFERENCES `projetos` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Valores de serviços por período';

-- Inserir alguns valores de exemplo (opcional - comentado)
/*
INSERT INTO `servico_valores` 
  (`servico_id`, `projeto_id`, `valor_unitario`, `quantidade`, `data_vigencia_inicio`, `observacoes`)
VALUES
  (1, NULL, 150.00, 1, '2025-01-01', 'Valor padrão do serviço'),
  (2, NULL, 200.00, 1, '2025-01-01', 'Valor padrão do serviço');
*/
