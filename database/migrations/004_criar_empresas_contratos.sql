-- ============================================
-- MIGRATION 004: EMPRESAS TOMADORAS E CONTRATOS
-- Versão: 4
-- Data: 2024-11-05
-- Sprint: 4
-- ============================================

-- ============================================
-- 1. EMPRESAS TOMADORAS (CLIENTES)
-- ============================================
CREATE TABLE IF NOT EXISTS empresas_tomadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados cadastrais
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    
    -- Endereço completo
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    -- Contatos
    email_principal VARCHAR(255),
    email_financeiro VARCHAR(255) COMMENT 'E-mail do setor financeiro',
    email_projetos VARCHAR(255) COMMENT 'E-mail do setor de projetos',
    telefone_principal VARCHAR(20),
    telefone_secundario VARCHAR(20),
    celular VARCHAR(20),
    whatsapp VARCHAR(20),
    site VARCHAR(255),
    
    -- Informações financeiras
    dia_fechamento INT DEFAULT 30 COMMENT 'Dia do mês para fechamento de medição',
    dia_pagamento INT DEFAULT 5 COMMENT 'Dia do pagamento (após fechamento)',
    forma_pagamento_preferencial VARCHAR(50) COMMENT 'Forma de pagamento preferida',
    banco VARCHAR(100),
    agencia VARCHAR(20),
    conta VARCHAR(30),
    tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente',
    
    -- Logo
    logo VARCHAR(255) COMMENT 'Nome do arquivo da logo',
    
    -- Observações e status
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_nome_fantasia (nome_fantasia),
    INDEX idx_ativo (ativo),
    INDEX idx_cidade_estado (cidade, estado),
    
    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. ATUALIZAR EMPRESAS PRESTADORAS
-- ============================================
-- Adicionar campos que faltam
ALTER TABLE empresas_prestadoras 
ADD COLUMN IF NOT EXISTS tipo_prestador ENUM('pj', 'pf', 'mei') DEFAULT 'pj' AFTER cnpj,
ADD COLUMN IF NOT EXISTS cpf VARCHAR(14) NULL AFTER tipo_prestador COMMENT 'Para PF',
ADD COLUMN IF NOT EXISTS inscricao_estadual VARCHAR(50) AFTER cpf,
ADD COLUMN IF NOT EXISTS inscricao_municipal VARCHAR(50) AFTER inscricao_estadual,
ADD COLUMN IF NOT EXISTS created_by INT AFTER updated_at,
ADD COLUMN IF NOT EXISTS updated_by INT AFTER created_by;

-- Adicionar foreign keys se não existirem
ALTER TABLE empresas_prestadoras 
ADD CONSTRAINT fk_prestadora_created_by FOREIGN KEY (created_by) REFERENCES usuarios(id),
ADD CONSTRAINT fk_prestadora_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios(id);

-- Adicionar índices
CREATE INDEX IF NOT EXISTS idx_tipo_prestador ON empresas_prestadoras(tipo_prestador);
CREATE INDEX IF NOT EXISTS idx_cpf ON empresas_prestadoras(cpf);

-- ============================================
-- 3. CONTRATOS
-- ============================================
CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Partes do contrato
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    
    -- Dados do contrato
    numero_contrato VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    objeto TEXT COMMENT 'Objeto do contrato',
    
    -- Vigência
    data_inicio DATE NOT NULL,
    data_fim DATE NULL COMMENT 'NULL = indeterminado',
    
    -- Valores
    valor_total DECIMAL(15,2) COMMENT 'Valor total estimado',
    valor_executado DECIMAL(15,2) DEFAULT 0,
    
    -- Status
    status ENUM('rascunho', 'ativo', 'suspenso', 'encerrado', 'cancelado') DEFAULT 'rascunho',
    
    -- Arquivos
    arquivo_contrato VARCHAR(500) COMMENT 'Path do PDF do contrato',
    arquivo_aditivos JSON COMMENT 'Array de aditivos contratuais',
    
    -- Observações
    observacoes TEXT,
    motivo_encerramento TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_by INT,
    
    -- Foreign Keys
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_numero_contrato (numero_contrato),
    INDEX idx_status (status),
    INDEX idx_datas (data_inicio, data_fim),
    INDEX idx_tomadora (empresa_tomadora_id),
    INDEX idx_prestadora (empresa_prestadora_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. VALORES DE SERVIÇOS POR PERÍODO
-- ============================================
CREATE TABLE IF NOT EXISTS servico_valores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Vinculação
    contrato_id INT NOT NULL,
    servico_id INT NOT NULL,
    
    -- Período de validade
    data_inicio DATE NOT NULL,
    data_fim DATE NULL COMMENT 'NULL = vigente até novo valor',
    
    -- Tipo de remuneração
    tipo_remuneracao ENUM(
        'por_hora', 
        'por_dia', 
        'por_mes', 
        'por_semana_5dias', 
        'por_semana_6dias', 
        'por_semana_7dias',
        'por_entrega',
        'por_producao'
    ) NOT NULL DEFAULT 'por_hora',
    
    -- Valores principais
    valor_base DECIMAL(10,2) NOT NULL COMMENT 'Valor base do serviço',
    valor_hora_extra DECIMAL(10,2) COMMENT 'Valor para horas extras (opcional)',
    valor_jornada_curta DECIMAL(10,2) COMMENT 'Valor para jornadas até 6h (opcional)',
    valor_noturno DECIMAL(10,2) COMMENT 'Adicional noturno (opcional)',
    valor_domingo_feriado DECIMAL(10,2) COMMENT 'Adicional domingo/feriado (opcional)',
    
    -- Limites e regras
    horas_mes_limite INT COMMENT 'Limite de horas por mês',
    horas_dia_limite INT DEFAULT 12 COMMENT 'Limite de horas por dia',
    dias_semana_limite INT COMMENT 'Dias por semana',
    
    -- Observações
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_by INT,
    
    -- Foreign Keys
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_periodo (data_inicio, data_fim),
    INDEX idx_ativo (ativo),
    INDEX idx_contrato_servico (contrato_id, servico_id),
    INDEX idx_tipo_remuneracao (tipo_remuneracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. RESPONSÁVEIS DAS EMPRESAS TOMADORAS
-- ============================================
CREATE TABLE IF NOT EXISTS empresa_tomadora_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Vinculação
    empresa_tomadora_id INT NOT NULL,
    
    -- Dados do responsável
    nome VARCHAR(255) NOT NULL,
    cargo VARCHAR(100),
    departamento VARCHAR(100),
    
    -- Contatos
    email VARCHAR(255),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    
    -- Status
    principal BOOLEAN DEFAULT FALSE COMMENT 'Contato principal da empresa',
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Foreign Keys
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_empresa (empresa_tomadora_id),
    INDEX idx_principal (principal),
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. DOCUMENTOS DAS EMPRESAS
-- ============================================
CREATE TABLE IF NOT EXISTS empresa_documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Vinculação (polimórfica)
    empresa_id INT NOT NULL,
    tipo_empresa ENUM('tomadora', 'prestadora') NOT NULL,
    
    -- Tipo de documento
    tipo_documento ENUM(
        'contrato', 
        'certidao_negativa', 
        'certidao_regularidade',
        'licenca', 
        'alvara',
        'procuracao',
        'estatuto',
        'contrato_social',
        'outros'
    ) NOT NULL,
    
    -- Arquivo
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_path VARCHAR(500) NOT NULL,
    tamanho_bytes INT,
    
    -- Metadados
    descricao TEXT,
    numero_documento VARCHAR(100),
    data_emissao DATE,
    data_validade DATE COMMENT 'Para documentos que expiram',
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_empresa (empresa_id, tipo_empresa),
    INDEX idx_tipo_documento (tipo_documento),
    INDEX idx_validade (data_validade),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. HISTÓRICO DE ALTERAÇÕES DE CONTRATOS
-- ============================================
CREATE TABLE IF NOT EXISTS contrato_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Vinculação
    contrato_id INT NOT NULL,
    
    -- Tipo de alteração
    tipo_alteracao ENUM(
        'criacao',
        'ativacao',
        'suspensao',
        'encerramento',
        'cancelamento',
        'aditivo',
        'valor',
        'prazo',
        'outros'
    ) NOT NULL,
    
    -- Dados da alteração
    campo_alterado VARCHAR(100),
    valor_anterior TEXT,
    valor_novo TEXT,
    
    -- Descrição
    descricao TEXT NOT NULL,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    -- Foreign Keys
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_contrato (contrato_id),
    INDEX idx_tipo (tipo_alteracao),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. ADITIVOS CONTRATUAIS
-- ============================================
CREATE TABLE IF NOT EXISTS contrato_aditivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Vinculação
    contrato_id INT NOT NULL,
    
    -- Dados do aditivo
    numero_aditivo VARCHAR(50) NOT NULL,
    tipo_aditivo ENUM(
        'prazo',
        'valor',
        'escopo',
        'misto'
    ) NOT NULL,
    
    -- Datas
    data_aditivo DATE NOT NULL,
    nova_data_fim DATE COMMENT 'Se alterar prazo',
    
    -- Valores
    valor_acrescido DECIMAL(15,2) DEFAULT 0,
    valor_decrescido DECIMAL(15,2) DEFAULT 0,
    
    -- Descrição
    objeto TEXT NOT NULL COMMENT 'Objeto do aditivo',
    justificativa TEXT NOT NULL,
    
    -- Arquivo
    arquivo_path VARCHAR(500),
    
    -- Status
    status ENUM('rascunho', 'ativo', 'cancelado') DEFAULT 'ativo',
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_by INT,
    
    -- Foreign Keys
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_contrato (contrato_id),
    INDEX idx_numero_aditivo (numero_aditivo),
    INDEX idx_tipo (tipo_aditivo),
    INDEX idx_status (status),
    
    -- Unique constraint
    UNIQUE KEY unique_contrato_numero (contrato_id, numero_aditivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ATUALIZAR VERSÃO DO SISTEMA
-- ============================================
UPDATE system_info SET valor = '4', updated_at = NOW() WHERE chave = 'db_version';

-- ============================================
-- INSERIR DADOS INICIAIS (SE NECESSÁRIO)
-- ============================================

-- Exemplo de empresa tomadora (Clinfec)
INSERT IGNORE INTO empresas_tomadoras (
    razao_social, 
    nome_fantasia, 
    cnpj, 
    email_principal, 
    telefone_principal,
    cidade,
    estado,
    ativo,
    created_by
) VALUES (
    'Clinfec Ltda',
    'Clinfec',
    '00.000.000/0001-00',
    'contato@clinfec.com.br',
    '(11) 0000-0000',
    'São Paulo',
    'SP',
    TRUE,
    1
);

-- ============================================
-- FIM DA MIGRATION 004
-- ============================================
