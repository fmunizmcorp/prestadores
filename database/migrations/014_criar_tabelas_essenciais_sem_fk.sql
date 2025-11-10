-- ============================================
-- MIGRATION 014: CRIAR TABELAS ESSENCIAIS SEM FOREIGN KEYS
-- Sprint 13 - Abordagem Simplificada
-- Objetivo: Criar tabelas principais primeiro, adicionar FKs depois
-- ============================================

-- 1. EMPRESAS PRESTADORAS (principal problema identificado)
CREATE TABLE IF NOT EXISTS empresas_prestadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NULL,
    tipo_prestador ENUM('pj', 'pf', 'mei') DEFAULT 'pj',
    cpf VARCHAR(14) NULL,
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    celular VARCHAR(20),
    whatsapp VARCHAR(20),
    site VARCHAR(255),
    banco VARCHAR(100),
    agencia VARCHAR(20),
    conta VARCHAR(30),
    tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente',
    pix VARCHAR(255),
    logo VARCHAR(255),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    status ENUM('ativa', 'inativa', 'suspensa') DEFAULT 'ativa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT NULL,
    updated_by INT NULL,
    INDEX idx_cnpj (cnpj),
    INDEX idx_cpf (cpf),
    INDEX idx_razao_social (razao_social),
    INDEX idx_ativo (ativo),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CONTRATOS
CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NULL,
    numero_contrato VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    objeto TEXT,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    valor_total DECIMAL(15,2),
    valor_executado DECIMAL(15,2) DEFAULT 0,
    status ENUM('rascunho', 'ativo', 'suspenso', 'encerrado', 'cancelado') DEFAULT 'rascunho',
    arquivo_contrato VARCHAR(500),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT NULL,
    updated_by INT NULL,
    INDEX idx_numero (numero_contrato),
    INDEX idx_status (status),
    INDEX idx_tomadora (empresa_tomadora_id),
    INDEX idx_prestadora (empresa_prestadora_id),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PROJETOS
CREATE TABLE IF NOT EXISTS projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NULL,
    empresa_tomadora_id INT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    objetivo TEXT,
    data_inicio DATE NOT NULL,
    data_fim_prevista DATE NULL,
    data_fim_real DATE NULL,
    orcamento_previsto DECIMAL(15,2),
    orcamento_executado DECIMAL(15,2) DEFAULT 0,
    status ENUM('planejamento', 'em_andamento', 'pausado', 'concluido', 'cancelado') DEFAULT 'planejamento',
    prioridade ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media',
    progresso INT DEFAULT 0,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT NULL,
    updated_by INT NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_contrato (contrato_id),
    INDEX idx_tomadora (empresa_tomadora_id),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ATIVIDADES
CREATE TABLE IF NOT EXISTS atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_inicio DATE NULL,
    data_fim_prevista DATE NULL,
    data_fim_real DATE NULL,
    horas_previstas DECIMAL(10,2),
    horas_executadas DECIMAL(10,2) DEFAULT 0,
    status ENUM('pendente', 'em_andamento', 'pausada', 'concluida', 'cancelada') DEFAULT 'pendente',
    prioridade ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media',
    progresso INT DEFAULT 0,
    custo_previsto DECIMAL(15,2),
    custo_executado DECIMAL(15,2) DEFAULT 0,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT NULL,
    updated_by INT NULL,
    responsavel_id INT NULL,
    INDEX idx_projeto (projeto_id),
    INDEX idx_status (status),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. NOTAS FISCAIS
CREATE TABLE IF NOT EXISTS notas_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NULL,
    projeto_id INT NULL,
    numero_nf VARCHAR(50) NOT NULL,
    serie VARCHAR(10),
    data_emissao DATE NOT NULL,
    data_vencimento DATE NULL,
    valor_bruto DECIMAL(15,2) NOT NULL,
    valor_liquido DECIMAL(15,2) NOT NULL,
    descricao TEXT,
    status ENUM('emitida', 'enviada', 'aprovada', 'paga', 'cancelada') DEFAULT 'emitida',
    arquivo_pdf VARCHAR(500),
    arquivo_xml VARCHAR(500),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT NULL,
    updated_by INT NULL,
    INDEX idx_numero (numero_nf),
    INDEX idx_status (status),
    INDEX idx_contrato (contrato_id),
    INDEX idx_projeto (projeto_id),
    INDEX idx_emissao (data_emissao),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Adicionar deleted_at em servicos (se não existir)
ALTER TABLE servicos 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_servicos_deleted ON servicos(deleted_at);

-- 7. Adicionar deleted_at em empresas_tomadoras (se não existir)
ALTER TABLE empresas_tomadoras 
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at;

CREATE INDEX IF NOT EXISTS idx_tomadoras_deleted ON empresas_tomadoras(deleted_at);

-- 8. Inserir dados de exemplo
INSERT IGNORE INTO empresas_prestadoras 
(razao_social, nome_fantasia, cnpj, tipo_prestador, email, telefone, cidade, estado, ativo, created_by)
VALUES 
('Prestadora Exemplo LTDA', 'Prestadora Exemplo', '00.000.000/0001-99', 'pj', 'contato@prestadora.exemplo.com', '(11) 98765-4321', 'São Paulo', 'SP', TRUE, 1);

-- ============================================
-- FIM DA MIGRATION 014
-- ============================================
