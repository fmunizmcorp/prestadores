-- ============================================
-- MIGRATION 012: CORREÇÃO COMPLETA DO BANCO - SPRINT 13
-- Data: 2025-11-09
-- Objetivo: Resolver TODOS os problemas identificados no relatório V3
-- Prioridade: 🔴 CRÍTICA
-- ============================================

-- PROBLEMA-003: Criar tabela empresas_prestadoras que está faltando
-- ============================================
CREATE TABLE IF NOT EXISTS empresas_prestadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados Cadastrais Básicos
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    
    -- Documentação
    cnpj VARCHAR(18) UNIQUE NULL COMMENT 'Para PJ',
    tipo_prestador ENUM('pj', 'pf', 'mei') DEFAULT 'pj',
    cpf VARCHAR(14) NULL COMMENT 'Para PF',
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    
    -- Endereço Completo
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    -- Contatos
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    celular VARCHAR(20),
    whatsapp VARCHAR(20),
    site VARCHAR(255),
    
    -- Dados Bancários
    banco VARCHAR(100),
    agencia VARCHAR(20),
    conta VARCHAR(30),
    tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente',
    pix VARCHAR(255) COMMENT 'Chave PIX',
    
    -- Logo/Arquivos
    logo VARCHAR(255),
    
    -- Observações
    observacoes TEXT,
    especialidades JSON COMMENT 'Array de especialidades/serviços',
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    status ENUM('ativa', 'inativa', 'suspensa') DEFAULT 'ativa',
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_cnpj (cnpj),
    INDEX idx_cpf (cpf),
    INDEX idx_razao_social (razao_social),
    INDEX idx_nome_fantasia (nome_fantasia),
    INDEX idx_tipo_prestador (tipo_prestador),
    INDEX idx_ativo (ativo),
    INDEX idx_status (status),
    INDEX idx_cidade_estado (cidade, estado),
    INDEX idx_deleted (deleted_at),
    
    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROBLEMA-004: Adicionar coluna deleted_at onde necessário
-- ============================================

-- Tabela: servicos
ALTER TABLE servicos 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete' AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_servicos_deleted ON servicos(deleted_at);

-- Tabela: contratos
ALTER TABLE contratos 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete' AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_contratos_deleted ON contratos(deleted_at);

-- Tabela: projetos
ALTER TABLE projetos 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete' AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_projetos_deleted ON projetos(deleted_at);

-- Tabela: atividades
ALTER TABLE atividades 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete' AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_atividades_deleted ON atividades(deleted_at);

-- Tabela: notas_fiscais (se existir)
ALTER TABLE notas_fiscais 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete' AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_notas_fiscais_deleted ON notas_fiscais(deleted_at);

-- ============================================
-- VERIFICAÇÃO FINAL: Popular tabelas vazias com dados de exemplo
-- ============================================

-- Inserir empresa prestadora de exemplo
INSERT IGNORE INTO empresas_prestadoras 
(razao_social, nome_fantasia, cnpj, tipo_prestador, email, telefone, cidade, estado, ativo, created_by)
VALUES 
('Prestadora Exemplo LTDA', 'Prestadora Exemplo', '00.000.000/0001-00', 'pj', 'prestadora@exemplo.com', '(11) 1234-5678', 'São Paulo', 'SP', TRUE, 1);

-- Inserir serviço de exemplo
INSERT IGNORE INTO servicos 
(nome, descricao, unidade, valor_base, ativo, created_by)
VALUES 
('Serviço Exemplo', 'Descrição do serviço de exemplo', 'hora', 150.00, TRUE, 1);

-- ============================================
-- AUDITORIA E VALIDAÇÃO
-- ============================================

-- Listar todas as tabelas para verificação
SELECT 
    'Tabelas Criadas/Verificadas' AS Status,
    TABLE_NAME as Tabela,
    CREATE_TIME as Criado_Em
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN (
    'usuarios',
    'empresas_tomadoras',
    'empresas_prestadoras',
    'servicos',
    'contratos',
    'projetos',
    'atividades',
    'notas_fiscais'
)
ORDER BY TABLE_NAME;

-- Verificar colunas deleted_at
SELECT 
    TABLE_NAME as Tabela,
    COLUMN_NAME as Coluna,
    COLUMN_TYPE as Tipo
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND COLUMN_NAME = 'deleted_at'
ORDER BY TABLE_NAME;

-- ============================================
-- FIM DA MIGRATION 012
-- ============================================
