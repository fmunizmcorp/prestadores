-- Migration 011: Criar tabelas de Fornecedores e Clientes
-- Data: 2025-11-08
-- Descrição: Tabelas necessárias para o módulo de Notas Fiscais

-- ============================================
-- TABELA: fornecedores
-- ============================================
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados Básicos
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255),
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    inscricao_estadual VARCHAR(20),
    
    -- Endereço
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    cep VARCHAR(10),
    
    -- Contato
    telefone VARCHAR(20),
    email VARCHAR(255),
    contato VARCHAR(255),
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de Fornecedores';

-- ============================================
-- TABELA: clientes
-- ============================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Tipo
    tipo ENUM('pf', 'pj') DEFAULT 'pj' COMMENT 'Pessoa Física ou Jurídica',
    
    -- Dados Básicos
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255),
    cpf_cnpj VARCHAR(18) NOT NULL UNIQUE,
    inscricao_estadual VARCHAR(20),
    
    -- Endereço
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    cep VARCHAR(10),
    
    -- Contato
    telefone VARCHAR(20),
    email VARCHAR(255),
    contato VARCHAR(255),
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_cpf_cnpj (cpf_cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de Clientes';

-- ============================================
-- Inserir alguns dados de teste (opcional)
-- ============================================

-- Fornecedor de exemplo
INSERT IGNORE INTO fornecedores (razao_social, nome_fantasia, cnpj, email, telefone, ativo) VALUES
('Fornecedor Exemplo LTDA', 'Fornecedor Exemplo', '12.345.678/0001-90', 'contato@fornecedor.com.br', '(11) 3456-7890', TRUE);

-- Cliente de exemplo
INSERT IGNORE INTO clientes (tipo, razao_social, nome_fantasia, cpf_cnpj, email, telefone, ativo) VALUES
('pj', 'Cliente Exemplo LTDA', 'Cliente Exemplo', '98.765.432/0001-10', 'contato@cliente.com.br', '(11) 9876-5432', TRUE),
('pf', 'João Silva', NULL, '123.456.789-00', 'joao.silva@email.com', '(11) 91234-5678', TRUE);
