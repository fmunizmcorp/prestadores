-- ============================================================================
-- SPRINT 70.2: Criar tabela custos para custos operacionais gerais
-- Data: 2024-11-18
-- Objetivo: Gerenciar custos operacionais, despesas administrativas e
--           gastos gerais da empresa (diferentes de projeto_custos)
-- ============================================================================

CREATE TABLE IF NOT EXISTS custos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Classificação
    tipo ENUM('fixo', 'variavel', 'operacional', 'administrativo', 'fornecedor') NOT NULL DEFAULT 'operacional',
    categoria VARCHAR(100) NULL COMMENT 'Categoria livre do custo',
    descricao VARCHAR(500) NOT NULL COMMENT 'Descrição detalhada do custo',
    
    -- Valor e Data
    valor DECIMAL(15,2) NOT NULL COMMENT 'Valor do custo',
    data_custo DATE NOT NULL COMMENT 'Data de referência do custo',
    
    -- Relações
    centro_custo_id INT UNSIGNED NULL COMMENT 'Centro de custo associado',
    fornecedor VARCHAR(200) NULL COMMENT 'Nome do fornecedor',
    numero_documento VARCHAR(100) NULL COMMENT 'Número do documento fiscal',
    
    -- Status
    status ENUM('pendente', 'aprovado', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    data_aprovacao DATETIME NULL,
    data_pagamento DATETIME NULL,
    
    -- Observações
    observacoes TEXT NULL,
    
    -- Soft Delete
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Auditoria
    criado_por INT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_data_custo (data_custo),
    INDEX idx_centro_custo (centro_custo_id),
    INDEX idx_ativo (ativo),
    INDEX idx_criado_em (criado_em)
    
    -- Foreign Keys removidas temporariamente para evitar erros de dependência
    -- FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id) ON DELETE SET NULL,
    -- FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Custos operacionais e despesas gerais da empresa';

-- Índice composto para relatórios
CREATE INDEX idx_data_status ON custos(data_custo, status);
CREATE INDEX idx_tipo_data ON custos(tipo, data_custo);
