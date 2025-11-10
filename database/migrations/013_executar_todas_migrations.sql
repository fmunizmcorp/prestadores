-- Migration 002: Empresas e Contratos (Sprint 4)
-- Data: 2024-11-04
-- Descrição: Tabelas para gestão completa de empresas tomadoras, prestadoras, contratos e relacionamentos

-- ============================================
-- TABELA: empresas_tomadoras
-- ============================================
CREATE TABLE IF NOT EXISTS empresas_tomadoras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Dados Cadastrais
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    inscricao_estadual VARCHAR(20),
    inscricao_municipal VARCHAR(20),
    
    -- Endereço
    cep VARCHAR(10),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    
    -- Contatos
    email_principal VARCHAR(150),
    telefone_principal VARCHAR(20),
    telefone_secundario VARCHAR(20),
    celular VARCHAR(20),
    whatsapp VARCHAR(20),
    email_financeiro VARCHAR(150),
    email_projetos VARCHAR(150),
    site VARCHAR(255),
    
    -- Configurações Financeiras
    dia_fechamento INT DEFAULT 25,
    dia_pagamento INT DEFAULT 10,
    forma_pagamento_preferencial ENUM('transferencia', 'pix', 'boleto', 'cheque') DEFAULT 'transferencia',
    
    -- Dados Bancários
    banco VARCHAR(100),
    agencia VARCHAR(10),
    conta VARCHAR(20),
    tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente',
    
    -- Logo e Arquivos
    logo VARCHAR(255),
    
    -- Status e Controle
    ativo BOOLEAN DEFAULT 1,
    observacoes TEXT,
    
    -- Auditoria
    criado_por INT NOT NULL,
    atualizado_por INT,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_nome_fantasia (nome_fantasia),
    INDEX idx_cidade (cidade),
    INDEX idx_estado (estado),
    INDEX idx_ativo (ativo),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: empresa_tomadora_responsaveis
-- ============================================
CREATE TABLE IF NOT EXISTS empresa_tomadora_responsaveis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT NOT NULL,
    
    -- Dados Pessoais
    nome VARCHAR(150) NOT NULL,
    cargo VARCHAR(100),
    departamento VARCHAR(100),
    
    -- Contatos
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20),
    celular VARCHAR(20),
    ramal VARCHAR(10),
    
    -- Flags
    responsavel_principal BOOLEAN DEFAULT 0,
    recebe_notificacoes BOOLEAN DEFAULT 1,
    ativo BOOLEAN DEFAULT 1,
    
    -- Observações
    observacoes TEXT,
    foto VARCHAR(255),
    
    -- Auditoria
    criado_por INT NOT NULL,
    atualizado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (empresa_id) REFERENCES empresas_tomadoras(id) ON DELETE CASCADE,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id),
    
    INDEX idx_empresa (empresa_id),
    INDEX idx_email (email),
    INDEX idx_principal (responsavel_principal),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: empresa_tomadora_documentos
-- ============================================
CREATE TABLE IF NOT EXISTS empresa_tomadora_documentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT NOT NULL,
    
    -- Tipo de Documento
    tipo_documento ENUM('contrato_social', 'certidao_negativa', 'alvara', 'certificado', 'outro') NOT NULL,
    nome_documento VARCHAR(255) NOT NULL,
    descricao TEXT,
    
    -- Arquivo
    arquivo VARCHAR(255) NOT NULL,
    tamanho_bytes INT,
    mime_type VARCHAR(100),
    
    -- Validade
    data_emissao DATE,
    data_validade DATE,
    alertar_dias_antes INT DEFAULT 30,
    
    -- Status
    status ENUM('valido', 'vencido', 'a_vencer', 'pendente') DEFAULT 'valido',
    
    -- Auditoria
    upload_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (empresa_id) REFERENCES empresas_tomadoras(id) ON DELETE CASCADE,
    FOREIGN KEY (upload_por) REFERENCES usuarios(id),
    
    INDEX idx_empresa (empresa_id),
    INDEX idx_tipo (tipo_documento),
    INDEX idx_validade (data_validade),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: servico_categorias
-- ============================================
CREATE TABLE IF NOT EXISTS servico_categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    icone VARCHAR(50),
    cor VARCHAR(7),
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_ordem (ordem),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir categorias iniciais
INSERT INTO servico_categorias (nome, descricao, icone, cor, ordem) VALUES
('Limpeza e Conservação', 'Serviços de limpeza e conservação predial', 'fa-broom', '#28a745', 1),
('Manutenção Predial', 'Manutenção preventiva e corretiva', 'fa-tools', '#17a2b8', 2),
('Segurança e Vigilância', 'Serviços de segurança patrimonial', 'fa-shield-alt', '#dc3545', 3),
('Jardinagem e Paisagismo', 'Cuidados com áreas verdes', 'fa-leaf', '#28a745', 4),
('Serviços Administrativos', 'Apoio administrativo e gestão', 'fa-clipboard', '#6c757d', 5),
('Tecnologia da Informação', 'Suporte e desenvolvimento TI', 'fa-laptop-code', '#007bff', 6),
('Saúde e Enfermagem', 'Serviços de saúde ocupacional', 'fa-heartbeat', '#e83e8c', 7),
('Alimentação e Nutrição', 'Serviços de alimentação', 'fa-utensils', '#fd7e14', 8),
('Educação e Treinamento', 'Capacitação e treinamentos', 'fa-graduation-cap', '#6610f2', 9),
('Consultoria e Assessoria', 'Consultoria especializada', 'fa-user-tie', '#20c997', 10);

-- ============================================
-- TABELA: servico_requisitos
-- ============================================
CREATE TABLE IF NOT EXISTS servico_requisitos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    servico_id INT NOT NULL,
    tipo ENUM('obrigatorio', 'desejavel', 'diferencial') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    categoria ENUM('formacao', 'experiencia', 'certificacao', 'habilidade', 'equipamento') NOT NULL,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    
    INDEX idx_servico (servico_id),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: servico_valores_referencia
-- ============================================
CREATE TABLE IF NOT EXISTS servico_valores_referencia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    servico_id INT NOT NULL,
    tipo_valor ENUM('hora', 'dia', 'semana', 'mes', 'projeto') NOT NULL,
    valor_minimo DECIMAL(10,2) NOT NULL,
    valor_medio DECIMAL(10,2) NOT NULL,
    valor_maximo DECIMAL(10,2) NOT NULL,
    regiao VARCHAR(50),
    data_referencia DATE NOT NULL,
    fonte VARCHAR(255),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    
    INDEX idx_servico (servico_id),
    INDEX idx_tipo (tipo_valor),
    INDEX idx_data (data_referencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: contratos
-- ============================================
CREATE TABLE IF NOT EXISTS contratos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_contrato VARCHAR(50) UNIQUE NOT NULL,
    
    -- Relacionamentos
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    
    -- Datas e Vigência
    data_assinatura DATE NOT NULL,
    data_inicio_vigencia DATE NOT NULL,
    data_fim_vigencia DATE NOT NULL,
    prazo_meses INT NOT NULL,
    renovavel BOOLEAN DEFAULT 0,
    renovacoes_automaticas INT DEFAULT 0,
    aviso_renovacao_dias INT DEFAULT 60,
    
    -- Valores
    valor_total_contrato DECIMAL(15,2) NOT NULL,
    tipo_valor ENUM('fixo', 'variavel', 'misto') NOT NULL DEFAULT 'fixo',
    valor_mensal_estimado DECIMAL(15,2),
    
    -- Forma de Pagamento
    forma_pagamento ENUM('mensal', 'quinzenal', 'semanal', 'por_medicao') NOT NULL DEFAULT 'mensal',
    dia_faturamento INT,
    dia_pagamento INT,
    prazo_pagamento_dias INT DEFAULT 30,
    
    -- Reajuste
    clausula_reajuste TEXT,
    indice_reajuste ENUM('IGPM', 'INPC', 'IPCA', 'outro') DEFAULT 'IGPM',
    periodicidade_reajuste_meses INT DEFAULT 12,
    data_ultimo_reajuste DATE,
    data_proximo_reajuste DATE,
    
    -- Garantias e Multas
    valor_garantia DECIMAL(15,2),
    tipo_garantia ENUM('caucao', 'seguro', 'fianca_bancaria', 'nenhuma'),
    multa_rescisao_percentual DECIMAL(5,2),
    multa_atraso_percentual DECIMAL(5,2),
    
    -- Status e Controle
    status ENUM('rascunho', 'aguardando_assinatura', 'vigente', 'suspenso', 'encerrado', 'rescindido') NOT NULL DEFAULT 'rascunho',
    motivo_rescisao TEXT,
    data_rescisao DATE,
    
    -- Observações
    objeto_contrato TEXT NOT NULL,
    observacoes TEXT,
    
    -- Documentos
    arquivo_contrato VARCHAR(255),
    arquivo_aditivos TEXT,
    
    -- Contatos Responsáveis
    responsavel_tomadora_id INT,
    responsavel_prestadora_id INT,
    
    -- Auditoria
    criado_por INT NOT NULL,
    atualizado_por INT,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (responsavel_tomadora_id) REFERENCES empresa_tomadora_responsaveis(id),
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id),
    
    -- Índices
    INDEX idx_numero (numero_contrato),
    INDEX idx_tomadora (empresa_tomadora_id),
    INDEX idx_prestadora (empresa_prestadora_id),
    INDEX idx_vigencia (data_inicio_vigencia, data_fim_vigencia),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: contrato_aditivos
-- ============================================
CREATE TABLE IF NOT EXISTS contrato_aditivos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contrato_id INT NOT NULL,
    numero_aditivo VARCHAR(20) NOT NULL,
    tipo ENUM('prazo', 'valor', 'escopo', 'misto') NOT NULL,
    data_aditivo DATE NOT NULL,
    
    -- Alterações
    novo_valor_total DECIMAL(15,2),
    nova_data_fim_vigencia DATE,
    novo_escopo TEXT,
    justificativa TEXT NOT NULL,
    
    -- Documento
    arquivo_aditivo VARCHAR(255),
    
    -- Aprovação
    status ENUM('rascunho', 'aguardando_aprovacao', 'aprovado', 'rejeitado') DEFAULT 'rascunho',
    aprovado_por INT,
    data_aprovacao DATE,
    
    -- Auditoria
    criado_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id),
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    
    INDEX idx_contrato (contrato_id),
    INDEX idx_data (data_aditivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: contrato_servicos
-- ============================================
CREATE TABLE IF NOT EXISTS contrato_servicos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contrato_id INT NOT NULL,
    servico_id INT NOT NULL,
    
    -- Quantidades
    quantidade INT NOT NULL DEFAULT 1,
    unidade ENUM('hora', 'dia', 'mes', 'profissional', 'projeto') NOT NULL,
    
    -- Valores
    valor_unitario DECIMAL(10,2) NOT NULL,
    valor_total DECIMAL(15,2) NOT NULL,
    
    -- Observações
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    
    INDEX idx_contrato (contrato_id),
    INDEX idx_servico (servico_id),
    UNIQUE KEY unique_contrato_servico (contrato_id, servico_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: contrato_historico
-- ============================================
CREATE TABLE IF NOT EXISTS contrato_historico (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contrato_id INT NOT NULL,
    usuario_id INT NOT NULL,
    acao ENUM('criacao', 'edicao', 'status', 'aditivo', 'rescisao', 'renovacao') NOT NULL,
    descricao TEXT NOT NULL,
    dados_anteriores TEXT,
    dados_novos TEXT,
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    
    INDEX idx_contrato (contrato_id),
    INDEX idx_data (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: servico_valores_periodo
-- ============================================
CREATE TABLE IF NOT EXISTS servico_valores_periodo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Relacionamento
    servico_id INT NOT NULL,
    contrato_id INT NULL,
    empresa_prestadora_id INT NULL,
    
    -- Período
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    ativo BOOLEAN DEFAULT 1,
    
    -- Tipo de Cobrança
    tipo_cobranca ENUM('hora', 'dia', 'semana', 'mes', 'projeto', 'fixo') NOT NULL,
    
    -- Valores
    valor_minimo DECIMAL(10,2),
    valor_padrao DECIMAL(10,2) NOT NULL,
    valor_maximo DECIMAL(10,2),
    
    -- Adicionais e Descontos
    valor_hora_extra DECIMAL(10,2),
    percentual_hora_extra DECIMAL(5,2) DEFAULT 50.00,
    valor_feriado DECIMAL(10,2),
    valor_fim_semana DECIMAL(10,2),
    valor_noturno DECIMAL(10,2),
    
    -- Impostos e Encargos
    percentual_impostos DECIMAL(5,2),
    percentual_encargos DECIMAL(5,2),
    percentual_total DECIMAL(5,2),
    
    -- Observações
    observacoes TEXT,
    motivo_alteracao VARCHAR(255),
    
    -- Auditoria
    criado_por INT NOT NULL,
    atualizado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign Keys
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    FOREIGN KEY (atualizado_por) REFERENCIAS usuarios(id),
    
    -- Índices
    INDEX idx_servico (servico_id),
    INDEX idx_contrato (contrato_id),
    INDEX idx_empresa (empresa_prestadora_id),
    INDEX idx_periodo (data_inicio, data_fim),
    INDEX idx_ativo (ativo),
    INDEX idx_deleted (deleted_at),
    
    -- Constraint
    CONSTRAINT check_periodo CHECK (data_fim IS NULL OR data_fim >= data_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Atualizar system_version
-- ============================================
UPDATE system_version SET db_version = 2, updated_at = NOW() WHERE id = 1;
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
-- ============================================
-- MIGRATION 005: SISTEMA COMPLETO DE GESTÃO DE PROJETOS
-- Versão: 5
-- Data: 2024-11-06
-- Sprint: 5
-- Descrição: Sistema completo de gestão de projetos com cronograma,
--            equipe, orçamento, execução, avaliações, anexos, templates
-- ============================================

-- ============================================
-- 1. CATEGORIAS DE PROJETOS
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    cor VARCHAR(7) COMMENT 'Cor hexadecimal para UI',
    icone VARCHAR(50) COMMENT 'Ícone FontAwesome',
    
    ativo BOOLEAN DEFAULT TRUE,
    ordem INT DEFAULT 0 COMMENT 'Ordem de exibição',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_ativo (ativo),
    INDEX idx_ordem (ordem),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PROJETOS (Tabela Principal)
-- ============================================
CREATE TABLE IF NOT EXISTS projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código único do projeto',
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    categoria_id INT,
    
    -- Relacionamentos
    empresa_tomadora_id INT NOT NULL,
    contrato_id INT COMMENT 'Contrato vinculado ao projeto',
    projeto_pai_id INT COMMENT 'Para subprojetos',
    
    -- Datas e Prazos
    data_inicio DATE NOT NULL,
    data_fim_prevista DATE,
    data_fim_real DATE,
    duracao_prevista_dias INT,
    duracao_real_dias INT,
    
    -- Status e Workflow
    status ENUM('planejamento', 'aprovacao', 'em_andamento', 'pausado', 'cancelado', 'concluido', 'arquivado') DEFAULT 'planejamento',
    prioridade ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media',
    percentual_conclusao DECIMAL(5,2) DEFAULT 0 COMMENT '0 a 100',
    
    -- Orçamento
    orcamento_total DECIMAL(15,2) DEFAULT 0,
    custo_realizado DECIMAL(15,2) DEFAULT 0,
    margem_percentual DECIMAL(5,2) COMMENT 'Margem de lucro %',
    
    -- Horas
    horas_estimadas DECIMAL(10,2) DEFAULT 0,
    horas_executadas DECIMAL(10,2) DEFAULT 0,
    
    -- Gestão
    gerente_id INT COMMENT 'Usuário responsável pelo projeto',
    aprovador_id INT COMMENT 'Usuário que aprova entregas',
    
    -- Configurações
    permite_horas_extras BOOLEAN DEFAULT FALSE,
    exige_aprovacao_etapas BOOLEAN DEFAULT TRUE,
    notificar_atrasos BOOLEAN DEFAULT TRUE,
    prazo_alerta_dias INT DEFAULT 7 COMMENT 'Alerta X dias antes do prazo',
    
    -- Avaliação
    nota_final DECIMAL(3,2) COMMENT 'Nota de 0 a 10',
    feedback_cliente TEXT,
    data_avaliacao DATE,
    
    -- Arquivos e Links
    logo VARCHAR(255),
    link_drive VARCHAR(500),
    link_trello VARCHAR(500),
    link_jira VARCHAR(500),
    
    -- Observações
    observacoes TEXT,
    motivo_cancelamento TEXT,
    licoes_aprendidas TEXT COMMENT 'Lições aprendidas ao final',
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_codigo (codigo),
    INDEX idx_nome (nome),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade),
    INDEX idx_empresa_tomadora (empresa_tomadora_id),
    INDEX idx_contrato (contrato_id),
    INDEX idx_gerente (gerente_id),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_data_fim_prevista (data_fim_prevista),
    INDEX idx_categoria (categoria_id),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (categoria_id) REFERENCES projeto_categorias(id),
    FOREIGN KEY (projeto_pai_id) REFERENCES projetos(id),
    FOREIGN KEY (gerente_id) REFERENCES usuarios(id),
    FOREIGN KEY (aprovador_id) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. ETAPAS DO PROJETO (Cronograma)
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_etapas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    projeto_id INT NOT NULL,
    etapa_pai_id INT COMMENT 'Para sub-etapas',
    
    -- Identificação
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    ordem INT DEFAULT 0,
    
    -- Datas
    data_inicio_prevista DATE,
    data_fim_prevista DATE,
    data_inicio_real DATE,
    data_fim_real DATE,
    
    -- Progresso
    percentual_conclusao DECIMAL(5,2) DEFAULT 0,
    status ENUM('nao_iniciada', 'em_andamento', 'pausada', 'concluida', 'cancelada') DEFAULT 'nao_iniciada',
    
    -- Esforço
    horas_estimadas DECIMAL(10,2) DEFAULT 0,
    horas_executadas DECIMAL(10,2) DEFAULT 0,
    
    -- Orçamento
    orcamento_estimado DECIMAL(15,2) DEFAULT 0,
    custo_realizado DECIMAL(15,2) DEFAULT 0,
    
    -- Dependências
    dependencias JSON COMMENT 'Array de IDs de etapas predecessoras',
    tipo_dependencia ENUM('fim_inicio', 'inicio_inicio', 'fim_fim', 'inicio_fim') DEFAULT 'fim_inicio',
    
    -- Responsabilidade
    responsavel_id INT,
    
    -- Entrega
    entregavel TEXT COMMENT 'Descrição do entregável',
    entregavel_arquivo VARCHAR(500),
    aprovado BOOLEAN DEFAULT FALSE,
    aprovado_por INT,
    data_aprovacao DATETIME,
    
    -- Milestone
    e_milestone BOOLEAN DEFAULT FALSE COMMENT 'É um marco importante',
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_ordem (ordem),
    INDEX idx_status (status),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_data_inicio (data_inicio_prevista),
    INDEX idx_data_fim (data_fim_prevista),
    INDEX idx_milestone (e_milestone),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (etapa_pai_id) REFERENCES projeto_etapas(id),
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. EQUIPE DO PROJETO
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_equipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    projeto_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    usuario_id INT COMMENT 'Usuário específico se já cadastrado',
    
    -- Papel no Projeto
    papel ENUM('gerente', 'coordenador', 'analista', 'desenvolvedor', 'designer', 'qa', 'suporte', 'consultor', 'outro') NOT NULL,
    papel_descricao VARCHAR(255),
    
    -- Alocação
    data_inicio DATE NOT NULL,
    data_fim DATE,
    percentual_alocacao INT DEFAULT 100 COMMENT 'Percentual de dedicação ao projeto',
    horas_semanais DECIMAL(5,2) COMMENT 'Horas por semana',
    
    -- Valores
    valor_hora DECIMAL(10,2),
    custo_total_previsto DECIMAL(15,2),
    custo_total_realizado DECIMAL(15,2),
    
    -- Status
    status ENUM('alocado', 'em_atividade', 'pausado', 'desalocado', 'substituido') DEFAULT 'alocado',
    
    -- Permissões no Projeto
    pode_aprovar_horas BOOLEAN DEFAULT FALSE,
    pode_aprovar_etapas BOOLEAN DEFAULT FALSE,
    pode_editar_cronograma BOOLEAN DEFAULT FALSE,
    
    -- Avaliação
    nota_desempenho DECIMAL(3,2) COMMENT 'Nota de 0 a 10',
    feedback TEXT,
    data_avaliacao DATE,
    avaliado_por INT,
    
    -- Observações
    observacoes TEXT,
    motivo_desalocacao TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_prestadora (empresa_prestadora_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_papel (papel),
    INDEX idx_status (status),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (avaliado_por) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. ORÇAMENTO DO PROJETO
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_orcamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    projeto_id INT NOT NULL,
    
    -- Categorização
    categoria ENUM('pessoal', 'infraestrutura', 'licencas', 'consultorias', 'viagens', 'treinamento', 'equipamentos', 'outros') NOT NULL,
    subcategoria VARCHAR(100),
    descricao TEXT,
    
    -- Valores
    tipo ENUM('receita', 'despesa') DEFAULT 'despesa',
    valor_planejado DECIMAL(15,2) NOT NULL,
    valor_realizado DECIMAL(15,2) DEFAULT 0,
    variacao DECIMAL(15,2) GENERATED ALWAYS AS (valor_realizado - valor_planejado) STORED,
    variacao_percentual DECIMAL(5,2) GENERATED ALWAYS AS ((valor_realizado - valor_planejado) / valor_planejado * 100) STORED,
    
    -- Competência
    mes_ano VARCHAR(7) COMMENT 'Formato: YYYY-MM',
    data_prevista DATE,
    data_realizada DATE,
    
    -- Aprovação
    status ENUM('planejado', 'aprovado', 'rejeitado', 'pago', 'cancelado') DEFAULT 'planejado',
    aprovado_por INT,
    data_aprovacao DATETIME,
    motivo_rejeicao TEXT,
    
    -- Documentação
    numero_nf VARCHAR(50),
    arquivo_nf VARCHAR(500),
    fornecedor VARCHAR(255),
    
    -- Centro de Custo
    centro_custo VARCHAR(50),
    conta_contabil VARCHAR(50),
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_categoria (categoria),
    INDEX idx_tipo (tipo),
    INDEX idx_mes_ano (mes_ano),
    INDEX idx_status (status),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. EXECUÇÃO DO PROJETO (Apontamento de Horas)
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_execucao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    projeto_id INT NOT NULL,
    etapa_id INT,
    usuario_id INT NOT NULL,
    
    -- Período
    data_execucao DATE NOT NULL,
    hora_inicio TIME,
    hora_fim TIME,
    horas_trabalhadas DECIMAL(5,2) NOT NULL,
    
    -- Tipo de Hora
    tipo_hora ENUM('normal', 'extra', 'noturna', 'domingo_feriado') DEFAULT 'normal',
    
    -- Atividade
    atividade_realizada TEXT NOT NULL,
    entregavel TEXT COMMENT 'O que foi entregue',
    
    -- Valores
    valor_hora DECIMAL(10,2),
    valor_total DECIMAL(10,2) GENERATED ALWAYS AS (horas_trabalhadas * valor_hora) STORED,
    
    -- Aprovação
    status ENUM('pendente', 'aprovado', 'rejeitado', 'faturado') DEFAULT 'pendente',
    aprovado_por INT,
    data_aprovacao DATETIME,
    motivo_rejeicao TEXT,
    
    -- Localização (Remoto/Presencial)
    local_trabalho ENUM('remoto', 'presencial', 'hibrido') DEFAULT 'remoto',
    endereco_trabalho VARCHAR(255),
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_etapa (etapa_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_data_execucao (data_execucao),
    INDEX idx_status (status),
    INDEX idx_tipo_hora (tipo_hora),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (etapa_id) REFERENCES projeto_etapas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. AVALIAÇÕES DO PROJETO
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    projeto_id INT NOT NULL,
    
    -- Tipo de Avaliação
    tipo ENUM('qualidade', 'prazo', 'custo', 'cliente', 'equipe', 'geral') NOT NULL,
    
    -- Avaliação
    nota DECIMAL(3,2) NOT NULL COMMENT 'Nota de 0 a 10',
    comentario TEXT,
    
    -- Critérios (JSON com notas por critério)
    criterios JSON COMMENT 'Array de {criterio, nota, peso}',
    
    -- Avaliador
    avaliador_id INT NOT NULL,
    avaliado_id INT COMMENT 'ID do usuário avaliado (para avaliação de equipe)',
    
    -- Data
    data_avaliacao DATE NOT NULL,
    periodo_avaliado VARCHAR(50) COMMENT 'Ex: Sprint 1, Mês 01/2024',
    
    -- Recomendações
    pontos_positivos TEXT,
    pontos_melhoria TEXT,
    recomendacoes TEXT,
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_tipo (tipo),
    INDEX idx_avaliador (avaliador_id),
    INDEX idx_avaliado (avaliado_id),
    INDEX idx_data (data_avaliacao),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (avaliador_id) REFERENCES usuarios(id),
    FOREIGN KEY (avaliado_id) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. ANEXOS DO PROJETO
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    projeto_id INT NOT NULL,
    etapa_id INT COMMENT 'Anexo vinculado a uma etapa específica',
    
    -- Arquivo
    nome_original VARCHAR(255) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo no servidor',
    caminho_arquivo VARCHAR(500) NOT NULL,
    extensao VARCHAR(10),
    tamanho_bytes BIGINT,
    mime_type VARCHAR(100),
    
    -- Tipo de Documento
    tipo_documento ENUM('proposta', 'contrato', 'ata', 'relatorio', 'entregavel', 'apresentacao', 'planilha', 'imagem', 'video', 'outro') DEFAULT 'outro',
    descricao TEXT,
    
    -- Versionamento
    versao VARCHAR(20) DEFAULT '1.0',
    versao_anterior_id INT COMMENT 'ID do anexo que esta versão substitui',
    
    -- Visibilidade
    visivel_cliente BOOLEAN DEFAULT FALSE,
    requer_aprovacao BOOLEAN DEFAULT FALSE,
    aprovado BOOLEAN DEFAULT FALSE,
    aprovado_por INT,
    data_aprovacao DATETIME,
    
    -- Tags
    tags JSON COMMENT 'Array de tags para busca',
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_etapa (etapa_id),
    INDEX idx_tipo_documento (tipo_documento),
    INDEX idx_versao_anterior (versao_anterior_id),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (etapa_id) REFERENCES projeto_etapas(id),
    FOREIGN KEY (versao_anterior_id) REFERENCES projeto_anexos(id),
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. TEMPLATES DE PROJETO
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(100),
    
    -- Template
    estrutura_etapas JSON COMMENT 'Estrutura de etapas padrão',
    configuracoes JSON COMMENT 'Configurações padrão do projeto',
    
    -- Orçamento Padrão
    orcamento_base DECIMAL(15,2),
    duracao_padrao_dias INT,
    
    -- Equipe Padrão
    equipe_padrao JSON COMMENT 'Array de {papel, quantidade, horas_semanais}',
    
    -- Uso
    total_uso INT DEFAULT 0 COMMENT 'Quantas vezes foi usado',
    ultima_utilizacao DATE,
    
    -- Visibilidade
    publico BOOLEAN DEFAULT FALSE COMMENT 'Disponível para todos',
    criado_por_id INT,
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_categoria (categoria),
    INDEX idx_publico (publico),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (criado_por_id) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. RISCOS DO PROJETO
-- ============================================
CREATE TABLE IF NOT EXISTS projeto_riscos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    projeto_id INT NOT NULL,
    
    -- Identificação
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    
    -- Classificação
    categoria ENUM('tecnico', 'financeiro', 'recursos', 'prazo', 'qualidade', 'externo', 'outro') NOT NULL,
    
    -- Análise
    probabilidade ENUM('muito_baixa', 'baixa', 'media', 'alta', 'muito_alta') DEFAULT 'media',
    impacto ENUM('muito_baixo', 'baixo', 'medio', 'alto', 'muito_alto') DEFAULT 'medio',
    nivel_risco INT COMMENT 'Calculado: probabilidade * impacto (1-25)',
    
    -- Status
    status ENUM('identificado', 'analisado', 'planejado', 'mitigado', 'ocorrido', 'descartado') DEFAULT 'identificado',
    
    -- Plano de Resposta
    estrategia ENUM('evitar', 'transferir', 'mitigar', 'aceitar') DEFAULT 'mitigar',
    plano_acao TEXT,
    plano_contingencia TEXT,
    
    -- Responsabilidade
    responsavel_id INT,
    
    -- Datas
    data_identificacao DATE NOT NULL,
    data_revisao DATE,
    data_ocorrencia DATE,
    data_resolucao DATE,
    
    -- Custos
    custo_estimado DECIMAL(15,2) COMMENT 'Custo se o risco ocorrer',
    custo_real DECIMAL(15,2) COMMENT 'Custo real se ocorreu',
    
    -- Observações
    observacoes TEXT,
    licoes_aprendidas TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_categoria (categoria),
    INDEX idx_status (status),
    INDEX idx_nivel_risco (nivel_risco),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. ATUALIZAR TABELA DE VERSÃO DO SISTEMA
-- ============================================
UPDATE system_version SET version = 5 WHERE id = 1;

-- ============================================
-- 12. DADOS INICIAIS - CATEGORIAS DE PROJETOS
-- ============================================
INSERT INTO projeto_categorias (nome, descricao, cor, icone, ordem, created_by) VALUES
('Desenvolvimento de Software', 'Projetos de desenvolvimento de aplicações e sistemas', '#007bff', 'fa-code', 1, 1),
('Infraestrutura', 'Projetos de infraestrutura de TI e redes', '#28a745', 'fa-server', 2, 1),
('Consultoria', 'Projetos de consultoria e assessoria', '#17a2b8', 'fa-user-tie', 3, 1),
('Treinamento', 'Projetos de capacitação e treinamento', '#ffc107', 'fa-graduation-cap', 4, 1),
('Suporte', 'Projetos de suporte técnico e manutenção', '#6c757d', 'fa-headset', 5, 1),
('Implantação', 'Projetos de implantação de sistemas', '#fd7e14', 'fa-rocket', 6, 1),
('Pesquisa e Desenvolvimento', 'Projetos de P&D e inovação', '#6f42c1', 'fa-flask', 7, 1),
('Marketing Digital', 'Projetos de marketing e comunicação', '#e83e8c', 'fa-bullhorn', 8, 1),
('Design', 'Projetos de design gráfico e UX/UI', '#20c997', 'fa-palette', 9, 1),
('Outros', 'Outros tipos de projetos', '#6c757d', 'fa-folder', 10, 1);

-- ============================================
-- 13. TRIGGERS PARA CÁLCULO AUTOMÁTICO
-- ============================================

-- Trigger para atualizar percentual de conclusão do projeto baseado nas etapas
DELIMITER //
CREATE TRIGGER after_projeto_etapa_update
AFTER UPDATE ON projeto_etapas
FOR EACH ROW
BEGIN
    DECLARE total_etapas INT;
    DECLARE soma_percentuais DECIMAL(10,2);
    
    SELECT COUNT(*), COALESCE(SUM(percentual_conclusao), 0)
    INTO total_etapas, soma_percentuais
    FROM projeto_etapas
    WHERE projeto_id = NEW.projeto_id AND ativo = TRUE;
    
    IF total_etapas > 0 THEN
        UPDATE projetos
        SET percentual_conclusao = soma_percentuais / total_etapas
        WHERE id = NEW.projeto_id;
    END IF;
END//
DELIMITER ;

-- Trigger para atualizar horas executadas do projeto
DELIMITER //
CREATE TRIGGER after_projeto_execucao_insert
AFTER INSERT ON projeto_execucao
FOR EACH ROW
BEGIN
    UPDATE projetos
    SET horas_executadas = (
        SELECT COALESCE(SUM(horas_trabalhadas), 0)
        FROM projeto_execucao
        WHERE projeto_id = NEW.projeto_id AND ativo = TRUE
    )
    WHERE id = NEW.projeto_id;
END//
DELIMITER ;

-- Trigger para atualizar custo realizado do projeto
DELIMITER //
CREATE TRIGGER after_projeto_orcamento_update
AFTER UPDATE ON projeto_orcamento
FOR EACH ROW
BEGIN
    UPDATE projetos
    SET custo_realizado = (
        SELECT COALESCE(SUM(valor_realizado), 0)
        FROM projeto_orcamento
        WHERE projeto_id = NEW.projeto_id AND tipo = 'despesa' AND ativo = TRUE
    )
    WHERE id = NEW.projeto_id;
END//
DELIMITER ;

-- Trigger para calcular nível de risco
DELIMITER //
CREATE TRIGGER before_projeto_risco_insert
BEFORE INSERT ON projeto_riscos
FOR EACH ROW
BEGIN
    DECLARE prob_valor INT;
    DECLARE imp_valor INT;
    
    -- Converter probabilidade para valor numérico
    SET prob_valor = CASE NEW.probabilidade
        WHEN 'muito_baixa' THEN 1
        WHEN 'baixa' THEN 2
        WHEN 'media' THEN 3
        WHEN 'alta' THEN 4
        WHEN 'muito_alta' THEN 5
    END;
    
    -- Converter impacto para valor numérico
    SET imp_valor = CASE NEW.impacto
        WHEN 'muito_baixo' THEN 1
        WHEN 'baixo' THEN 2
        WHEN 'medio' THEN 3
        WHEN 'alto' THEN 4
        WHEN 'muito_alto' THEN 5
    END;
    
    -- Calcular nível de risco
    SET NEW.nivel_risco = prob_valor * imp_valor;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER before_projeto_risco_update
BEFORE UPDATE ON projeto_riscos
FOR EACH ROW
BEGIN
    DECLARE prob_valor INT;
    DECLARE imp_valor INT;
    
    SET prob_valor = CASE NEW.probabilidade
        WHEN 'muito_baixa' THEN 1
        WHEN 'baixa' THEN 2
        WHEN 'media' THEN 3
        WHEN 'alta' THEN 4
        WHEN 'muito_alta' THEN 5
    END;
    
    SET imp_valor = CASE NEW.impacto
        WHEN 'muito_baixo' THEN 1
        WHEN 'baixo' THEN 2
        WHEN 'medio' THEN 3
        WHEN 'alto' THEN 4
        WHEN 'muito_alto' THEN 5
    END;
    
    SET NEW.nivel_risco = prob_valor * imp_valor;
END//
DELIMITER ;

-- ============================================
-- FIM DA MIGRATION 005
-- ============================================
-- ============================================
-- MIGRATION 006: SISTEMA DE ATIVIDADES
-- Versão: 6
-- Data: 2024-11-07
-- Sprint: 6
-- Descrição: Sistema completo de gerenciamento de atividades vinculadas a projetos
--            com controle de tempo, recursos, equipe, e rastreamento de custos
-- ============================================

-- ============================================
-- 1. ATIVIDADES (Tabela Principal)
-- ============================================
CREATE TABLE IF NOT EXISTS atividades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    
    -- Relacionamentos
    projeto_id INT NOT NULL COMMENT 'Projeto ao qual a atividade pertence',
    responsavel_id INT COMMENT 'Usuário responsável pela atividade',
    
    -- Datas e Prazos
    data_inicio DATE,
    data_fim DATE,
    data_conclusao DATE COMMENT 'Data real de conclusão',
    
    -- Status e Prioridade
    status ENUM('pendente', 'em_andamento', 'em_revisao', 'concluida', 'cancelada') DEFAULT 'pendente',
    prioridade ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media',
    progresso INT DEFAULT 0 COMMENT 'Percentual de conclusão (0-100)',
    
    -- Estimativas e Realizações
    horas_estimadas DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Horas estimadas para conclusão',
    horas_realizadas DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Horas efetivamente trabalhadas',
    custo_estimado DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custo estimado da atividade',
    custo_realizado DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custo realizado da atividade',
    custo_hora DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Custo médio por hora',
    
    -- Dependências
    atividade_predecessor_id INT UNSIGNED COMMENT 'Atividade que deve ser concluída antes',
    tipo_dependencia ENUM('fim_inicio', 'inicio_inicio', 'fim_fim', 'inicio_fim') DEFAULT 'fim_inicio',
    
    -- Observações e Notas
    observacoes TEXT,
    resultado TEXT COMMENT 'Resultado/entrega da atividade',
    
    -- Metadados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
    
    -- Índices
    INDEX idx_projeto (projeto_id),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_data_fim (data_fim),
    INDEX idx_predecessor (atividade_predecessor_id),
    INDEX idx_deleted (deleted_at),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (atividade_predecessor_id) REFERENCES atividades(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. EQUIPE DA ATIVIDADE (Alocação de Membros)
-- ============================================
CREATE TABLE IF NOT EXISTS atividade_equipe (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    atividade_id INT UNSIGNED NOT NULL,
    usuario_id INT NOT NULL,
    
    -- Função e Papel
    funcao VARCHAR(100) COMMENT 'Função do membro na atividade',
    papel ENUM('responsavel', 'executor', 'revisor', 'observador') DEFAULT 'executor',
    
    -- Alocação
    percentual_alocacao INT DEFAULT 100 COMMENT 'Percentual de dedicação (0-100)',
    horas_alocadas DECIMAL(10,2) DEFAULT 0.00,
    horas_trabalhadas DECIMAL(10,2) DEFAULT 0.00,
    custo_hora_membro DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Custo/hora deste membro',
    custo_total_membro DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custo total deste membro na atividade',
    
    -- Datas
    data_inicio DATE,
    data_fim DATE,
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Metadados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    
    -- Índices
    INDEX idx_atividade (atividade_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_ativo (ativo),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    
    -- Constraint: Um usuário não pode ser alocado duas vezes na mesma atividade ativa
    UNIQUE KEY uk_atividade_usuario_ativo (atividade_id, usuario_id, ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. RECURSOS DA ATIVIDADE (Materiais, Equipamentos)
-- ============================================
CREATE TABLE IF NOT EXISTS atividade_recursos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    atividade_id INT UNSIGNED NOT NULL,
    
    -- Identificação do Recurso
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('material', 'equipamento', 'servico', 'outro') DEFAULT 'material',
    descricao TEXT,
    
    -- Quantidades
    quantidade DECIMAL(10,2) DEFAULT 1.00,
    unidade VARCHAR(20) DEFAULT 'un' COMMENT 'Unidade de medida',
    
    -- Custos
    custo_unitario DECIMAL(15,2) DEFAULT 0.00,
    custo_total DECIMAL(15,2) DEFAULT 0.00,
    
    -- Fornecedor
    fornecedor VARCHAR(255),
    
    -- Status
    status ENUM('solicitado', 'aprovado', 'adquirido', 'utilizado', 'devolvido') DEFAULT 'solicitado',
    
    -- Datas
    data_solicitacao DATE,
    data_aquisicao DATE,
    data_utilizacao DATE,
    
    -- Observações
    observacoes TEXT,
    
    -- Metadados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    
    -- Índices
    INDEX idx_atividade (atividade_id),
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. REGISTRO DE TEMPO (Timesheet)
-- ============================================
CREATE TABLE IF NOT EXISTS atividade_tempo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    atividade_id INT UNSIGNED NOT NULL,
    usuario_id INT NOT NULL,
    
    -- Data e Período
    data DATE NOT NULL,
    hora_inicio TIME,
    hora_fim TIME,
    horas_trabalhadas DECIMAL(10,2) NOT NULL,
    
    -- Descrição do Trabalho
    descricao TEXT NOT NULL COMMENT 'O que foi realizado',
    tipo_trabalho ENUM('desenvolvimento', 'analise', 'reuniao', 'teste', 'documentacao', 'outro') DEFAULT 'desenvolvimento',
    
    -- Status
    status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
    observacoes_aprovacao TEXT,
    aprovado_por INT,
    data_aprovacao TIMESTAMP NULL,
    
    -- Metadados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_atividade (atividade_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (data),
    INDEX idx_status (status),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. ANEXOS DA ATIVIDADE
-- ============================================
CREATE TABLE IF NOT EXISTS atividade_anexos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    atividade_id INT UNSIGNED NOT NULL,
    
    -- Arquivo
    nome_arquivo VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(50),
    tamanho_bytes BIGINT,
    caminho VARCHAR(500) NOT NULL,
    
    -- Categorização
    categoria ENUM('documento', 'imagem', 'planilha', 'apresentacao', 'outro') DEFAULT 'documento',
    descricao TEXT,
    
    -- Versão (para controle de versões)
    versao INT DEFAULT 1,
    arquivo_anterior_id INT UNSIGNED COMMENT 'ID do arquivo que este substitui',
    
    -- Metadados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    
    -- Índices
    INDEX idx_atividade (atividade_id),
    INDEX idx_categoria (categoria),
    INDEX idx_versao (versao),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (arquivo_anterior_id) REFERENCES atividade_anexos(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. COMENTÁRIOS/ATUALIZAÇÕES DA ATIVIDADE
-- ============================================
CREATE TABLE IF NOT EXISTS atividade_comentarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    atividade_id INT UNSIGNED NOT NULL,
    usuario_id INT NOT NULL,
    
    -- Conteúdo
    comentario TEXT NOT NULL,
    tipo ENUM('comentario', 'atualizacao', 'observacao', 'bloqueio') DEFAULT 'comentario',
    
    -- Menções (@usuario)
    mencoes_json JSON COMMENT 'IDs dos usuários mencionados',
    
    -- Metadados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    editado BOOLEAN DEFAULT FALSE,
    
    -- Índices
    INDEX idx_atividade (atividade_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo),
    INDEX idx_created (created_at),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. CHECKLIST DA ATIVIDADE
-- ============================================
CREATE TABLE IF NOT EXISTS atividade_checklist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    atividade_id INT UNSIGNED NOT NULL,
    
    -- Item do Checklist
    descricao VARCHAR(500) NOT NULL,
    ordem INT DEFAULT 0,
    
    -- Status
    concluido BOOLEAN DEFAULT FALSE,
    data_conclusao TIMESTAMP NULL,
    concluido_por INT,
    
    -- Metadados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    
    -- Índices
    INDEX idx_atividade (atividade_id),
    INDEX idx_ordem (ordem),
    INDEX idx_concluido (concluido),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (concluido_por) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. HISTÓRICO DE ALTERAÇÕES DA ATIVIDADE
-- ============================================
CREATE TABLE IF NOT EXISTS atividade_historico (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    atividade_id INT UNSIGNED NOT NULL,
    usuario_id INT NOT NULL,
    
    -- Tipo de Evento
    tipo_evento VARCHAR(100) NOT NULL COMMENT 'Ex: status_alterado, equipe_adicionada, etc',
    descricao TEXT NOT NULL,
    
    -- Detalhes em JSON
    detalhes_json JSON COMMENT 'Detalhes estruturados da alteração',
    
    -- Metadados
    data_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_atividade (atividade_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo_evento (tipo_evento),
    INDEX idx_data (data_evento),
    
    -- Chaves Estrangeiras
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGERS: ATUALIZAÇÃO AUTOMÁTICA
-- ============================================

DELIMITER //

-- Trigger: Atualizar horas_realizadas quando tempo é registrado
CREATE TRIGGER trg_atividade_tempo_after_insert
AFTER INSERT ON atividade_tempo
FOR EACH ROW
BEGIN
    UPDATE atividades
    SET horas_realizadas = horas_realizadas + NEW.horas_trabalhadas
    WHERE id = NEW.atividade_id;
END//

CREATE TRIGGER trg_atividade_tempo_after_update
AFTER UPDATE ON atividade_tempo
FOR EACH ROW
BEGIN
    UPDATE atividades
    SET horas_realizadas = horas_realizadas - OLD.horas_trabalhadas + NEW.horas_trabalhadas
    WHERE id = NEW.atividade_id;
END//

CREATE TRIGGER trg_atividade_tempo_after_delete
AFTER DELETE ON atividade_tempo
FOR EACH ROW
BEGIN
    UPDATE atividades
    SET horas_realizadas = horas_realizadas - OLD.horas_trabalhadas
    WHERE id = OLD.atividade_id;
END//

-- Trigger: Calcular custo_hora automaticamente
CREATE TRIGGER trg_atividade_calcular_custo_hora
BEFORE UPDATE ON atividades
FOR EACH ROW
BEGIN
    IF NEW.horas_realizadas > 0 THEN
        SET NEW.custo_hora = NEW.custo_realizado / NEW.horas_realizadas;
    ELSE
        SET NEW.custo_hora = 0.00;
    END IF;
END//

-- Trigger: Calcular progresso baseado em checklist
CREATE TRIGGER trg_checklist_atualizar_progresso
AFTER UPDATE ON atividade_checklist
FOR EACH ROW
BEGIN
    DECLARE total_itens INT;
    DECLARE itens_concluidos INT;
    DECLARE percentual INT;
    
    SELECT COUNT(*) INTO total_itens
    FROM atividade_checklist
    WHERE atividade_id = NEW.atividade_id;
    
    SELECT COUNT(*) INTO itens_concluidos
    FROM atividade_checklist
    WHERE atividade_id = NEW.atividade_id AND concluido = TRUE;
    
    IF total_itens > 0 THEN
        SET percentual = ROUND((itens_concluidos / total_itens) * 100);
        
        UPDATE atividades
        SET progresso = percentual
        WHERE id = NEW.atividade_id;
    END IF;
END//

DELIMITER ;

-- ============================================
-- VIEWS: CONSULTAS COMUNS
-- ============================================

-- View: Dashboard de Atividades por Projeto
CREATE OR REPLACE VIEW vw_projeto_atividades_resumo AS
SELECT
    p.id AS projeto_id,
    p.nome AS projeto_nome,
    COUNT(a.id) AS total_atividades,
    SUM(CASE WHEN a.status = 'pendente' THEN 1 ELSE 0 END) AS pendentes,
    SUM(CASE WHEN a.status = 'em_andamento' THEN 1 ELSE 0 END) AS em_andamento,
    SUM(CASE WHEN a.status = 'concluida' THEN 1 ELSE 0 END) AS concluidas,
    ROUND(AVG(a.progresso), 2) AS progresso_medio,
    SUM(a.horas_estimadas) AS total_horas_estimadas,
    SUM(a.horas_realizadas) AS total_horas_realizadas,
    SUM(a.custo_estimado) AS total_custo_estimado,
    SUM(a.custo_realizado) AS total_custo_realizado
FROM projetos p
LEFT JOIN atividades a ON a.projeto_id = p.id AND a.deleted_at IS NULL
GROUP BY p.id, p.nome;

-- View: Atividades Atrasadas
CREATE OR REPLACE VIEW vw_atividades_atrasadas AS
SELECT
    a.id AS atividade_id,
    a.titulo AS atividade_titulo,
    a.status,
    a.prioridade,
    a.data_fim,
    DATEDIFF(CURDATE(), a.data_fim) AS dias_atraso,
    p.id AS projeto_id,
    p.nome AS projeto_nome,
    u.id AS responsavel_id,
    u.nome AS responsavel_nome
FROM atividades a
INNER JOIN projetos p ON p.id = a.projeto_id
LEFT JOIN usuarios u ON u.id = a.responsavel_id
WHERE a.data_fim < CURDATE()
  AND a.status NOT IN ('concluida', 'cancelada')
  AND a.deleted_at IS NULL
ORDER BY a.prioridade DESC, dias_atraso DESC;

-- View: Carga de Trabalho por Usuário
CREATE OR REPLACE VIEW vw_usuario_carga_trabalho AS
SELECT
    u.id AS usuario_id,
    u.nome AS usuario_nome,
    COUNT(DISTINCT ae.atividade_id) AS total_atividades,
    SUM(ae.horas_alocadas) AS horas_alocadas,
    SUM(ae.horas_trabalhadas) AS horas_trabalhadas,
    SUM(ae.custo_total_membro) AS custo_total,
    ROUND(AVG(ae.percentual_alocacao), 2) AS percentual_alocacao_medio
FROM usuarios u
INNER JOIN atividade_equipe ae ON ae.usuario_id = u.id AND ae.ativo = TRUE
INNER JOIN atividades a ON a.id = ae.atividade_id AND a.status IN ('pendente', 'em_andamento') AND a.deleted_at IS NULL
GROUP BY u.id, u.nome
ORDER BY total_atividades DESC;

-- ============================================
-- DADOS INICIAIS (Opcional)
-- ============================================

-- Nenhum dado inicial necessário para este módulo
-- As atividades serão criadas pelos usuários conforme necessário

-- ============================================
-- FIM DA MIGRATION 006
-- ============================================
/**
 * Migration 008 - Sistema Financeiro Completo
 * 
 * Cria estrutura completa para gestão financeira:
 * - Notas Fiscais Eletrônicas (NF-e/NFS-e)
 * - Boletos Bancários
 * - Pagamentos
 * - Contas a Pagar
 * - Contas a Receber
 * - Lançamentos Financeiros (Double-Entry Bookkeeping)
 * - Conciliação Bancária
 * - Categorias e Centro de Custos
 * - Fluxo de Caixa
 * 
 * Sprint 7 - Gestão Financeira
 * 
 * @version 1.0.0
 * @author Clinfec Prestadores
 * @date 2025-11-06
 */

-- ============================================================================
-- TABELA: categorias_financeiras
-- Categorias hierárquicas para classificação de lançamentos financeiros
-- ============================================================================

CREATE TABLE IF NOT EXISTS categorias_financeiras (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    codigo VARCHAR(20) UNIQUE NOT NULL COMMENT 'Código único da categoria (ex: 1.1.01)',
    nome VARCHAR(100) NOT NULL COMMENT 'Nome da categoria',
    descricao TEXT COMMENT 'Descrição detalhada',
    
    -- Hierarquia
    pai_id INT UNSIGNED NULL COMMENT 'Categoria pai para hierarquia',
    nivel INT UNSIGNED DEFAULT 1 COMMENT 'Nível na hierarquia (1=raiz, 2=subcategoria, etc)',
    caminho VARCHAR(500) COMMENT 'Caminho completo na hierarquia',
    
    -- Classificação
    tipo ENUM('receita', 'despesa', 'transferencia') NOT NULL COMMENT 'Tipo da categoria',
    natureza ENUM('operacional', 'financeira', 'investimento', 'outra') DEFAULT 'operacional',
    
    -- Controle
    centro_custo_id INT UNSIGNED NULL COMMENT 'Centro de custo associado',
    aceita_lancamento BOOLEAN DEFAULT TRUE COMMENT 'Se aceita lançamentos diretos',
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT UNSIGNED,
    
    -- Índices
    INDEX idx_pai (pai_id),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo),
    INDEX idx_centro_custo (centro_custo_id),
    
    -- Foreign Keys
    FOREIGN KEY (pai_id) REFERENCES categorias_financeiras(id) ON DELETE RESTRICT,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Categorias hierárquicas para classificação financeira';

-- ============================================================================
-- TABELA: centros_custo
-- Centros de custo para departamentalização de despesas
-- ============================================================================

CREATE TABLE IF NOT EXISTS centros_custo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    
    -- Hierarquia
    pai_id INT UNSIGNED NULL,
    nivel INT UNSIGNED DEFAULT 1,
    
    -- Responsabilidade
    responsavel_id INT UNSIGNED NULL COMMENT 'Usuário responsável pelo centro de custo',
    
    -- Orçamento
    orcamento_mensal DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Orçamento mensal do centro de custo',
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_pai (pai_id),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (pai_id) REFERENCES centros_custo(id) ON DELETE RESTRICT,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Centros de custo para departamentalização';

-- ============================================================================
-- TABELA: notas_fiscais
-- Notas Fiscais Eletrônicas (NF-e e NFS-e)
-- ============================================================================

CREATE TABLE IF NOT EXISTS notas_fiscais (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    numero VARCHAR(20) NOT NULL COMMENT 'Número da nota fiscal',
    serie VARCHAR(10) DEFAULT '1',
    tipo ENUM('nfe', 'nfse', 'nfce') NOT NULL DEFAULT 'nfe' COMMENT 'Tipo de nota fiscal',
    modelo VARCHAR(10) COMMENT 'Modelo da nota (55, 65, etc)',
    
    -- Chaves e Códigos
    chave_acesso VARCHAR(44) UNIQUE COMMENT 'Chave de acesso da NF-e (44 dígitos)',
    codigo_verificacao VARCHAR(20) COMMENT 'Código de verificação para NFS-e',
    numero_rps VARCHAR(20) COMMENT 'Número do RPS (NFS-e)',
    
    -- Emissão
    data_emissao DATE NOT NULL,
    data_saida_entrada DATETIME COMMENT 'Data/hora de saída ou entrada',
    
    -- Partes Envolvidas
    emitente_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'outro') NOT NULL,
    emitente_id INT UNSIGNED COMMENT 'ID da empresa emitente',
    
    destinatario_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'prestador', 'outro') NOT NULL,
    destinatario_id INT UNSIGNED COMMENT 'ID do destinatário',
    destinatario_nome VARCHAR(200),
    destinatario_cpf_cnpj VARCHAR(18),
    
    -- Valores
    valor_bruto DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor bruto dos serviços/produtos',
    valor_desconto DECIMAL(15,2) DEFAULT 0.00,
    valor_acrescimo DECIMAL(15,2) DEFAULT 0.00,
    valor_tributos DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor total de tributos',
    valor_liquido DECIMAL(15,2) AS (valor_bruto - valor_desconto + valor_acrescimo) STORED,
    
    -- Tributos Detalhados
    base_calculo_icms DECIMAL(15,2) DEFAULT 0.00,
    valor_icms DECIMAL(15,2) DEFAULT 0.00,
    valor_ipi DECIMAL(15,2) DEFAULT 0.00,
    valor_pis DECIMAL(15,2) DEFAULT 0.00,
    valor_cofins DECIMAL(15,2) DEFAULT 0.00,
    valor_iss DECIMAL(15,2) DEFAULT 0.00,
    aliquota_iss DECIMAL(5,2) DEFAULT 0.00,
    valor_inss DECIMAL(15,2) DEFAULT 0.00,
    valor_ir DECIMAL(15,2) DEFAULT 0.00,
    valor_csll DECIMAL(15,2) DEFAULT 0.00,
    
    -- Retenções
    valor_retencao_pis DECIMAL(15,2) DEFAULT 0.00,
    valor_retencao_cofins DECIMAL(15,2) DEFAULT 0.00,
    valor_retencao_csll DECIMAL(15,2) DEFAULT 0.00,
    valor_retencao_ir DECIMAL(15,2) DEFAULT 0.00,
    valor_retencao_inss DECIMAL(15,2) DEFAULT 0.00,
    
    -- Descrição
    natureza_operacao VARCHAR(100) COMMENT 'Natureza da operação',
    descricao_servicos TEXT COMMENT 'Descrição dos serviços prestados',
    observacoes TEXT,
    
    -- Status e Fluxo
    status ENUM('rascunho', 'emitida', 'autorizada', 'rejeitada', 'cancelada', 'denegada', 'inutilizada') 
           NOT NULL DEFAULT 'rascunho',
    status_sefaz VARCHAR(50) COMMENT 'Status retornado pela SEFAZ',
    protocolo_autorizacao VARCHAR(50) COMMENT 'Protocolo de autorização SEFAZ',
    
    -- Cancelamento
    data_cancelamento DATETIME NULL,
    motivo_cancelamento TEXT,
    protocolo_cancelamento VARCHAR(50),
    
    -- Vinculações
    projeto_id INT UNSIGNED NULL,
    contrato_id INT UNSIGNED NULL,
    atividade_id INT UNSIGNED NULL,
    
    -- XML e PDF
    xml_nfe LONGTEXT COMMENT 'XML completo da NF-e',
    xml_cancelamento LONGTEXT COMMENT 'XML do cancelamento',
    pdf_path VARCHAR(500) COMMENT 'Caminho do PDF gerado',
    
    -- Contingência
    tipo_emissao ENUM('normal', 'contingencia_fs', 'contingencia_scan', 'contingencia_dpec', 'contingencia_fsda') 
                 DEFAULT 'normal',
    justificativa_contingencia TEXT,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    emitido_por INT UNSIGNED,
    cancelado_por INT UNSIGNED NULL,
    
    -- Índices
    INDEX idx_numero_serie (numero, serie),
    INDEX idx_chave_acesso (chave_acesso),
    INDEX idx_data_emissao (data_emissao),
    INDEX idx_emitente (emitente_tipo, emitente_id),
    INDEX idx_destinatario (destinatario_tipo, destinatario_id),
    INDEX idx_status (status),
    INDEX idx_projeto (projeto_id),
    INDEX idx_contrato (contrato_id),
    INDEX idx_atividade (atividade_id),
    INDEX idx_destinatario_cpf_cnpj (destinatario_cpf_cnpj),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (atividade_id) REFERENCES atividades(id),
    FOREIGN KEY (emitido_por) REFERENCES usuarios(id),
    FOREIGN KEY (cancelado_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_nf_valores CHECK (valor_bruto >= 0 AND valor_liquido >= 0),
    CONSTRAINT chk_nf_numero UNIQUE (numero, serie, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Notas Fiscais Eletrônicas (NF-e e NFS-e)';

-- ============================================================================
-- TABELA: notas_fiscais_itens
-- Itens/serviços das notas fiscais
-- ============================================================================

CREATE TABLE IF NOT EXISTS notas_fiscais_itens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    nota_fiscal_id INT UNSIGNED NOT NULL,
    
    -- Identificação do Item
    numero_item INT UNSIGNED NOT NULL COMMENT 'Número sequencial do item',
    codigo_servico VARCHAR(20) COMMENT 'Código do serviço (LC 116/2003)',
    descricao TEXT NOT NULL,
    
    -- Quantidades
    quantidade DECIMAL(15,4) DEFAULT 1.0000,
    unidade VARCHAR(10) DEFAULT 'UN',
    
    -- Valores
    valor_unitario DECIMAL(15,2) NOT NULL,
    valor_total DECIMAL(15,2) AS (quantidade * valor_unitario) STORED,
    valor_desconto DECIMAL(15,2) DEFAULT 0.00,
    
    -- Tributos do Item
    aliquota_iss DECIMAL(5,2) DEFAULT 0.00,
    valor_iss DECIMAL(15,2) DEFAULT 0.00,
    
    -- Vinculação
    servico_id INT UNSIGNED NULL COMMENT 'Serviço do catálogo',
    
    -- Auditoria
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_nota_fiscal (nota_fiscal_id),
    INDEX idx_servico (servico_id),
    
    -- Foreign Keys
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    
    -- Constraints
    CONSTRAINT chk_nfi_valores CHECK (quantidade > 0 AND valor_unitario >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Itens das notas fiscais';

-- ============================================================================
-- TABELA: boletos
-- Boletos bancários para cobrança
-- ============================================================================

CREATE TABLE IF NOT EXISTS boletos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    nosso_numero VARCHAR(50) NOT NULL COMMENT 'Nosso número do boleto',
    numero_documento VARCHAR(50) COMMENT 'Número do documento/pedido',
    linha_digitavel VARCHAR(54) COMMENT 'Linha digitável do código de barras',
    codigo_barras VARCHAR(44) COMMENT 'Código de barras',
    
    -- Banco
    banco_codigo VARCHAR(3) NOT NULL COMMENT 'Código do banco (341, 237, etc)',
    banco_nome VARCHAR(100),
    agencia VARCHAR(10),
    agencia_dv VARCHAR(2),
    conta VARCHAR(20),
    conta_dv VARCHAR(2),
    carteira VARCHAR(10) COMMENT 'Carteira de cobrança',
    convenio VARCHAR(20) COMMENT 'Convênio/código do cedente',
    
    -- Cedente (Quem Recebe)
    cedente_tipo ENUM('empresa_tomadora', 'empresa_prestadora') NOT NULL,
    cedente_id INT UNSIGNED NOT NULL,
    cedente_nome VARCHAR(200),
    cedente_cpf_cnpj VARCHAR(18),
    
    -- Sacado (Quem Paga)
    sacado_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'prestador', 'outro') NOT NULL,
    sacado_id INT UNSIGNED NULL,
    sacado_nome VARCHAR(200) NOT NULL,
    sacado_cpf_cnpj VARCHAR(18) NOT NULL,
    sacado_endereco VARCHAR(500),
    sacado_bairro VARCHAR(100),
    sacado_cidade VARCHAR(100),
    sacado_estado VARCHAR(2),
    sacado_cep VARCHAR(9),
    
    -- Datas
    data_documento DATE NOT NULL COMMENT 'Data do documento',
    data_processamento DATE NOT NULL COMMENT 'Data de processamento',
    data_vencimento DATE NOT NULL COMMENT 'Data de vencimento',
    data_limite_pagamento DATE COMMENT 'Data limite para pagamento',
    
    -- Valores
    valor_documento DECIMAL(15,2) NOT NULL COMMENT 'Valor nominal do boleto',
    valor_desconto DECIMAL(15,2) DEFAULT 0.00,
    valor_abatimento DECIMAL(15,2) DEFAULT 0.00,
    valor_mora DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor de mora por dia',
    percentual_multa DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentual de multa',
    valor_multa DECIMAL(15,2) DEFAULT 0.00,
    valor_pago DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor efetivamente pago',
    
    -- Instruções
    instrucoes TEXT COMMENT 'Instruções para o caixa',
    local_pagamento VARCHAR(200) DEFAULT 'Pagável em qualquer banco até o vencimento',
    
    -- Aceite
    aceite ENUM('S', 'N') DEFAULT 'N',
    especie VARCHAR(20) DEFAULT 'R$',
    especie_documento VARCHAR(20) DEFAULT 'DM' COMMENT 'DM, DS, NP, etc',
    
    -- Status
    status ENUM('pendente', 'registrado', 'pago', 'vencido', 'cancelado', 'baixado') 
           NOT NULL DEFAULT 'pendente',
    data_status_mudanca DATETIME,
    
    -- Pagamento
    data_pagamento DATE NULL,
    data_credito DATE NULL COMMENT 'Data de crédito na conta',
    valor_tarifa_cobranca DECIMAL(15,2) DEFAULT 0.00,
    
    -- Registro Online
    registrado_online BOOLEAN DEFAULT FALSE COMMENT 'Se foi registrado via API bancária',
    data_registro DATETIME NULL,
    protocolo_registro VARCHAR(50),
    
    -- Remessa/Retorno
    arquivo_remessa VARCHAR(500) COMMENT 'Path do arquivo de remessa',
    data_remessa DATETIME NULL,
    arquivo_retorno VARCHAR(500) COMMENT 'Path do arquivo de retorno',
    data_retorno DATETIME NULL,
    
    -- Vinculações
    conta_receber_id INT UNSIGNED NULL,
    nota_fiscal_id INT UNSIGNED NULL,
    contrato_id INT UNSIGNED NULL,
    
    -- PDF
    pdf_path VARCHAR(500) COMMENT 'Caminho do PDF do boleto',
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT UNSIGNED,
    
    -- Índices
    INDEX idx_nosso_numero (nosso_numero),
    INDEX idx_numero_documento (numero_documento),
    INDEX idx_cedente (cedente_tipo, cedente_id),
    INDEX idx_sacado (sacado_tipo, sacado_id),
    INDEX idx_sacado_cpf_cnpj (sacado_cpf_cnpj),
    INDEX idx_data_vencimento (data_vencimento),
    INDEX idx_status (status),
    INDEX idx_conta_receber (conta_receber_id),
    INDEX idx_nota_fiscal (nota_fiscal_id),
    INDEX idx_contrato (contrato_id),
    
    -- Foreign Keys
    FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id),
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_boleto_valores CHECK (valor_documento > 0 AND valor_pago >= 0),
    CONSTRAINT chk_boleto_datas CHECK (data_vencimento >= data_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Boletos bancários para cobrança';

-- ============================================================================
-- TABELA: contas_receber
-- Contas a receber (receitas a receber)
-- ============================================================================

CREATE TABLE IF NOT EXISTS contas_receber (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    numero_documento VARCHAR(50) COMMENT 'Número do documento/pedido',
    descricao VARCHAR(500) NOT NULL,
    observacoes TEXT,
    
    -- Devedor
    devedor_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'prestador', 'outro') NOT NULL,
    devedor_id INT UNSIGNED NULL,
    devedor_nome VARCHAR(200) NOT NULL,
    devedor_cpf_cnpj VARCHAR(18),
    
    -- Datas
    data_emissao DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    data_vencimento_original DATE COMMENT 'Data de vencimento original (antes de prorrogações)',
    data_pagamento DATE NULL,
    
    -- Valores
    valor_original DECIMAL(15,2) NOT NULL,
    valor_desconto DECIMAL(15,2) DEFAULT 0.00,
    valor_acrescimo DECIMAL(15,2) DEFAULT 0.00,
    valor_juros DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Juros calculados',
    valor_multa DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Multa por atraso',
    valor_total DECIMAL(15,2) AS (valor_original - valor_desconto + valor_acrescimo + valor_juros + valor_multa) STORED,
    valor_pago DECIMAL(15,2) DEFAULT 0.00,
    valor_pendente DECIMAL(15,2) AS (valor_original - valor_desconto + valor_acrescimo + valor_juros + valor_multa - valor_pago) STORED,
    
    -- Juros e Multa
    percentual_juros_dia DECIMAL(5,4) DEFAULT 0.0000 COMMENT 'Percentual de juros ao dia',
    percentual_multa DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentual de multa após vencimento',
    dias_atraso INT AS (DATEDIFF(IFNULL(data_pagamento, CURRENT_DATE), data_vencimento)) STORED,
    
    -- Parcelamento
    numero_parcela INT UNSIGNED DEFAULT 1,
    total_parcelas INT UNSIGNED DEFAULT 1,
    parcela_principal_id INT UNSIGNED NULL COMMENT 'ID da parcela principal (1ª parcela)',
    
    -- Categoria
    categoria_id INT UNSIGNED NOT NULL,
    centro_custo_id INT UNSIGNED NULL,
    
    -- Status
    status ENUM('pendente', 'pago', 'parcial', 'vencido', 'cancelado', 'renegociado') 
           NOT NULL DEFAULT 'pendente',
    
    -- Vinculações
    projeto_id INT UNSIGNED NULL,
    contrato_id INT UNSIGNED NULL,
    atividade_id INT UNSIGNED NULL,
    nota_fiscal_id INT UNSIGNED NULL,
    
    -- Forma de Recebimento
    forma_recebimento ENUM('dinheiro', 'cheque', 'transferencia', 'boleto', 'cartao_credito', 'cartao_debito', 'pix', 'outra') NULL,
    
    -- Recorrência
    recorrente BOOLEAN DEFAULT FALSE,
    frequencia_recorrencia ENUM('semanal', 'quinzenal', 'mensal', 'bimestral', 'trimestral', 'semestral', 'anual') NULL,
    proxima_recorrencia DATE NULL,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT UNSIGNED,
    recebido_por INT UNSIGNED NULL,
    
    -- Índices
    INDEX idx_numero_documento (numero_documento),
    INDEX idx_devedor (devedor_tipo, devedor_id),
    INDEX idx_data_vencimento (data_vencimento),
    INDEX idx_data_pagamento (data_pagamento),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria_id),
    INDEX idx_centro_custo (centro_custo_id),
    INDEX idx_projeto (projeto_id),
    INDEX idx_contrato (contrato_id),
    INDEX idx_atividade (atividade_id),
    INDEX idx_nota_fiscal (nota_fiscal_id),
    INDEX idx_parcela_principal (parcela_principal_id),
    
    -- Foreign Keys
    FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id),
    FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id),
    FOREIGN KEY (projeto_id) REFERENCES projetos(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (atividade_id) REFERENCES atividades(id),
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id),
    FOREIGN KEY (parcela_principal_id) REFERENCES contas_receber(id) ON DELETE SET NULL,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    FOREIGN KEY (recebido_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_cr_valores CHECK (valor_original > 0 AND valor_pago >= 0),
    CONSTRAINT chk_cr_datas CHECK (data_vencimento >= data_emissao),
    CONSTRAINT chk_cr_parcelas CHECK (numero_parcela <= total_parcelas AND numero_parcela > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contas a receber (receitas)';

-- ============================================================================
-- TABELA: contas_pagar
-- Contas a pagar (despesas a pagar)
-- ============================================================================

CREATE TABLE IF NOT EXISTS contas_pagar (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    numero_documento VARCHAR(50) COMMENT 'Número da nota fiscal ou documento',
    descricao VARCHAR(500) NOT NULL,
    observacoes TEXT,
    
    -- Credor (Fornecedor)
    credor_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'prestador', 'fornecedor', 'outro') NOT NULL,
    credor_id INT UNSIGNED NULL,
    credor_nome VARCHAR(200) NOT NULL,
    credor_cpf_cnpj VARCHAR(18),
    
    -- Datas
    data_emissao DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    data_vencimento_original DATE,
    data_pagamento DATE NULL,
    data_competencia DATE COMMENT 'Data de competência (mês/ano de referência)',
    
    -- Valores
    valor_original DECIMAL(15,2) NOT NULL,
    valor_desconto DECIMAL(15,2) DEFAULT 0.00,
    valor_acrescimo DECIMAL(15,2) DEFAULT 0.00,
    valor_juros DECIMAL(15,2) DEFAULT 0.00,
    valor_multa DECIMAL(15,2) DEFAULT 0.00,
    valor_total DECIMAL(15,2) AS (valor_original - valor_desconto + valor_acrescimo + valor_juros + valor_multa) STORED,
    valor_pago DECIMAL(15,2) DEFAULT 0.00,
    valor_pendente DECIMAL(15,2) AS (valor_original - valor_desconto + valor_acrescimo + valor_juros + valor_multa - valor_pago) STORED,
    
    -- Juros e Multa
    percentual_juros_dia DECIMAL(5,4) DEFAULT 0.0000,
    percentual_multa DECIMAL(5,2) DEFAULT 0.00,
    dias_atraso INT AS (DATEDIFF(IFNULL(data_pagamento, CURRENT_DATE), data_vencimento)) STORED,
    
    -- Parcelamento
    numero_parcela INT UNSIGNED DEFAULT 1,
    total_parcelas INT UNSIGNED DEFAULT 1,
    parcela_principal_id INT UNSIGNED NULL,
    
    -- Categoria
    categoria_id INT UNSIGNED NOT NULL,
    centro_custo_id INT UNSIGNED NULL,
    
    -- Status
    status ENUM('pendente', 'pago', 'parcial', 'vencido', 'cancelado', 'renegociado', 'agendado') 
           NOT NULL DEFAULT 'pendente',
    
    -- Vinculações
    projeto_id INT UNSIGNED NULL,
    contrato_id INT UNSIGNED NULL,
    nota_fiscal_id INT UNSIGNED NULL,
    
    -- Forma de Pagamento
    forma_pagamento ENUM('dinheiro', 'cheque', 'transferencia', 'boleto', 'cartao_credito', 'cartao_debito', 'pix', 'outra') NULL,
    
    -- Pagamento Agendado
    agendamento_automatico BOOLEAN DEFAULT FALSE,
    data_agendamento DATE NULL,
    
    -- Recorrência
    recorrente BOOLEAN DEFAULT FALSE,
    frequencia_recorrencia ENUM('semanal', 'quinzenal', 'mensal', 'bimestral', 'trimestral', 'semestral', 'anual') NULL,
    proxima_recorrencia DATE NULL,
    
    -- Documentos
    anexo_path VARCHAR(500) COMMENT 'Caminho do anexo (nota fiscal, boleto, etc)',
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT UNSIGNED,
    pago_por INT UNSIGNED NULL,
    
    -- Índices
    INDEX idx_numero_documento (numero_documento),
    INDEX idx_credor (credor_tipo, credor_id),
    INDEX idx_data_vencimento (data_vencimento),
    INDEX idx_data_pagamento (data_pagamento),
    INDEX idx_data_competencia (data_competencia),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria_id),
    INDEX idx_centro_custo (centro_custo_id),
    INDEX idx_projeto (projeto_id),
    INDEX idx_contrato (contrato_id),
    INDEX idx_nota_fiscal (nota_fiscal_id),
    INDEX idx_parcela_principal (parcela_principal_id),
    
    -- Foreign Keys
    FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id),
    FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id),
    FOREIGN KEY (projeto_id) REFERENCES projetos(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id),
    FOREIGN KEY (parcela_principal_id) REFERENCES contas_pagar(id) ON DELETE SET NULL,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    FOREIGN KEY (pago_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_cp_valores CHECK (valor_original > 0 AND valor_pago >= 0),
    CONSTRAINT chk_cp_datas CHECK (data_vencimento >= data_emissao),
    CONSTRAINT chk_cp_parcelas CHECK (numero_parcela <= total_parcelas AND numero_parcela > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contas a pagar (despesas)';

-- ============================================================================
-- TABELA: pagamentos
-- Registro de pagamentos efetuados
-- ============================================================================

CREATE TABLE IF NOT EXISTS pagamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    numero_comprovante VARCHAR(50) COMMENT 'Número do comprovante/recibo',
    descricao VARCHAR(500) NOT NULL,
    
    -- Tipo de Pagamento
    tipo ENUM('conta_pagar', 'conta_receber', 'transferencia', 'outro') NOT NULL,
    origem_tipo VARCHAR(50) COMMENT 'Tipo da origem (conta_pagar, conta_receber, etc)',
    origem_id INT UNSIGNED NULL COMMENT 'ID da origem',
    
    -- Partes Envolvidas
    pagador_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'outro') NOT NULL,
    pagador_id INT UNSIGNED NULL,
    
    beneficiario_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'prestador', 'fornecedor', 'outro') NOT NULL,
    beneficiario_id INT UNSIGNED NULL,
    beneficiario_nome VARCHAR(200),
    beneficiario_cpf_cnpj VARCHAR(18),
    
    -- Data e Valor
    data_pagamento DATE NOT NULL,
    data_compensacao DATE NULL COMMENT 'Data de compensação bancária',
    valor DECIMAL(15,2) NOT NULL,
    
    -- Forma de Pagamento
    forma_pagamento ENUM('dinheiro', 'cheque', 'transferencia', 'boleto', 'cartao_credito', 'cartao_debito', 'pix', 'outra') NOT NULL,
    
    -- Detalhes por Forma
    dados_pagamento JSON COMMENT 'Dados específicos da forma de pagamento (banco, agência, conta, etc)',
    
    -- Parcelamento Cartão
    parcelado BOOLEAN DEFAULT FALSE,
    numero_parcelas INT UNSIGNED DEFAULT 1,
    
    -- Status
    status ENUM('processando', 'aprovado', 'compensado', 'rejeitado', 'cancelado', 'estornado') 
           NOT NULL DEFAULT 'processando',
    
    -- Comprovante
    comprovante_path VARCHAR(500) COMMENT 'Caminho do comprovante digital',
    
    -- Conciliação
    conciliado BOOLEAN DEFAULT FALSE,
    data_conciliacao DATETIME NULL,
    conciliacao_bancaria_id INT UNSIGNED NULL,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    processado_por INT UNSIGNED,
    
    -- Índices
    INDEX idx_numero_comprovante (numero_comprovante),
    INDEX idx_data_pagamento (data_pagamento),
    INDEX idx_origem (origem_tipo, origem_id),
    INDEX idx_pagador (pagador_tipo, pagador_id),
    INDEX idx_beneficiario (beneficiario_tipo, beneficiario_id),
    INDEX idx_forma_pagamento (forma_pagamento),
    INDEX idx_status (status),
    INDEX idx_conciliado (conciliado),
    INDEX idx_conciliacao (conciliacao_bancaria_id),
    
    -- Foreign Keys
    FOREIGN KEY (processado_por) REFERENCES usuarios(id),
    FOREIGN KEY (conciliacao_bancaria_id) REFERENCES conciliacoes_bancarias(id),
    
    -- Constraints
    CONSTRAINT chk_pag_valor CHECK (valor > 0),
    CONSTRAINT chk_pag_parcelas CHECK (numero_parcelas > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de pagamentos efetuados';

-- ============================================================================
-- TABELA: lancamentos_financeiros
-- Lançamentos contábeis com partidas dobradas
-- ============================================================================

CREATE TABLE IF NOT EXISTS lancamentos_financeiros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    numero_lancamento VARCHAR(50) UNIQUE NOT NULL COMMENT 'Número sequencial do lançamento',
    descricao VARCHAR(500) NOT NULL,
    historico TEXT COMMENT 'Histórico detalhado do lançamento',
    
    -- Data
    data_lancamento DATE NOT NULL,
    data_competencia DATE COMMENT 'Mês/ano de competência',
    
    -- Tipo
    tipo ENUM('receita', 'despesa', 'transferencia') NOT NULL,
    natureza ENUM('debito', 'credito') NOT NULL COMMENT 'Natureza contábil',
    
    -- Valor
    valor DECIMAL(15,2) NOT NULL,
    
    -- Categoria e Centro de Custo
    categoria_id INT UNSIGNED NOT NULL,
    centro_custo_id INT UNSIGNED NULL,
    
    -- Conta Bancária
    conta_bancaria_id INT UNSIGNED NULL COMMENT 'Conta bancária associada',
    
    -- Partida Dobrada
    lancamento_contrapartida_id INT UNSIGNED NULL COMMENT 'ID do lançamento de contrapartida',
    lote_lancamento VARCHAR(50) COMMENT 'Lote de lançamentos relacionados',
    
    -- Origem do Lançamento
    origem_tipo VARCHAR(50) COMMENT 'Tipo da origem (conta_pagar, conta_receber, pagamento, etc)',
    origem_id INT UNSIGNED NULL,
    
    -- Vinculações
    projeto_id INT UNSIGNED NULL,
    contrato_id INT UNSIGNED NULL,
    nota_fiscal_id INT UNSIGNED NULL,
    
    -- Conciliação
    conciliado BOOLEAN DEFAULT FALSE,
    data_conciliacao DATETIME NULL,
    conciliacao_bancaria_id INT UNSIGNED NULL,
    
    -- Status
    status ENUM('pendente', 'confirmado', 'cancelado') NOT NULL DEFAULT 'confirmado',
    
    -- Documento
    numero_documento VARCHAR(50),
    anexo_path VARCHAR(500),
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    lancado_por INT UNSIGNED,
    
    -- Índices
    INDEX idx_numero_lancamento (numero_lancamento),
    INDEX idx_data_lancamento (data_lancamento),
    INDEX idx_data_competencia (data_competencia),
    INDEX idx_tipo (tipo),
    INDEX idx_natureza (natureza),
    INDEX idx_categoria (categoria_id),
    INDEX idx_centro_custo (centro_custo_id),
    INDEX idx_conta_bancaria (conta_bancaria_id),
    INDEX idx_contrapartida (lancamento_contrapartida_id),
    INDEX idx_lote (lote_lancamento),
    INDEX idx_origem (origem_tipo, origem_id),
    INDEX idx_projeto (projeto_id),
    INDEX idx_contrato (contrato_id),
    INDEX idx_conciliado (conciliado),
    INDEX idx_status (status),
    
    -- Foreign Keys
    FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id),
    FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id),
    FOREIGN KEY (lancamento_contrapartida_id) REFERENCES lancamentos_financeiros(id) ON DELETE SET NULL,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id),
    FOREIGN KEY (conciliacao_bancaria_id) REFERENCES conciliacoes_bancarias(id),
    FOREIGN KEY (lancado_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_lf_valor CHECK (valor > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lançamentos financeiros com partidas dobradas';

-- ============================================================================
-- TABELA: contas_bancarias
-- Contas bancárias da empresa
-- ============================================================================

CREATE TABLE IF NOT EXISTS contas_bancarias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    nome VARCHAR(100) NOT NULL COMMENT 'Nome identificador da conta',
    descricao TEXT,
    
    -- Banco
    banco_codigo VARCHAR(3) NOT NULL COMMENT 'Código FEBRABAN do banco',
    banco_nome VARCHAR(100) NOT NULL,
    agencia VARCHAR(10) NOT NULL,
    agencia_dv VARCHAR(2),
    conta VARCHAR(20) NOT NULL,
    conta_dv VARCHAR(2) NOT NULL,
    
    -- Tipo
    tipo ENUM('corrente', 'poupanca', 'aplicacao', 'investimento') NOT NULL DEFAULT 'corrente',
    
    -- Proprietário
    empresa_tipo ENUM('empresa_tomadora', 'empresa_prestadora') NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    
    -- Saldos
    saldo_inicial DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Saldo inicial ao cadastrar',
    saldo_atual DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Saldo atual (calculado)',
    data_ultimo_saldo DATE COMMENT 'Data do último saldo calculado',
    
    -- Limites
    limite_credito DECIMAL(15,2) DEFAULT 0.00,
    limite_disponivel DECIMAL(15,2) AS (limite_credito + saldo_atual) STORED,
    
    -- Configurações
    conta_padrao BOOLEAN DEFAULT FALSE COMMENT 'Se é a conta padrão para operações',
    permite_saldo_negativo BOOLEAN DEFAULT FALSE,
    
    -- API Bancária (Open Banking)
    api_habilitada BOOLEAN DEFAULT FALSE,
    api_token TEXT COMMENT 'Token de acesso à API do banco',
    api_ultima_sincronizacao DATETIME NULL,
    
    -- Conciliação
    ultimo_extrato_importado DATE NULL,
    
    -- Auditoria
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT UNSIGNED,
    
    -- Índices
    INDEX idx_empresa (empresa_tipo, empresa_id),
    INDEX idx_banco (banco_codigo),
    INDEX idx_tipo (tipo),
    INDEX idx_conta_padrao (conta_padrao),
    INDEX idx_ativo (ativo),
    
    -- Foreign Keys
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_cb_conta UNIQUE (banco_codigo, agencia, conta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contas bancárias da empresa';

-- ============================================================================
-- TABELA: conciliacoes_bancarias
-- Conciliações bancárias
-- ============================================================================

CREATE TABLE IF NOT EXISTS conciliacoes_bancarias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificação
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    
    -- Conta Bancária
    conta_bancaria_id INT UNSIGNED NOT NULL,
    
    -- Período
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    mes_referencia DATE COMMENT 'Mês de referência (YYYY-MM-01)',
    
    -- Saldos
    saldo_inicial_banco DECIMAL(15,2) NOT NULL COMMENT 'Saldo inicial no extrato bancário',
    saldo_final_banco DECIMAL(15,2) NOT NULL COMMENT 'Saldo final no extrato bancário',
    saldo_inicial_sistema DECIMAL(15,2) NOT NULL COMMENT 'Saldo inicial no sistema',
    saldo_final_sistema DECIMAL(15,2) NOT NULL COMMENT 'Saldo final no sistema',
    
    -- Diferenças
    diferenca DECIMAL(15,2) AS (saldo_final_banco - saldo_final_sistema) STORED,
    
    -- Totalizadores
    total_entradas_banco DECIMAL(15,2) DEFAULT 0.00,
    total_saidas_banco DECIMAL(15,2) DEFAULT 0.00,
    total_entradas_sistema DECIMAL(15,2) DEFAULT 0.00,
    total_saidas_sistema DECIMAL(15,2) DEFAULT 0.00,
    
    -- Contadores
    lancamentos_conciliados INT UNSIGNED DEFAULT 0,
    lancamentos_pendentes INT UNSIGNED DEFAULT 0,
    lancamentos_apenas_banco INT UNSIGNED DEFAULT 0 COMMENT 'Lançamentos que existem apenas no banco',
    lancamentos_apenas_sistema INT UNSIGNED DEFAULT 0 COMMENT 'Lançamentos que existem apenas no sistema',
    
    -- Status
    status ENUM('em_andamento', 'concluida', 'aprovada', 'cancelada') NOT NULL DEFAULT 'em_andamento',
    
    -- Arquivo de Importação
    arquivo_ofx_path VARCHAR(500) COMMENT 'Path do arquivo OFX importado',
    data_importacao DATETIME NULL,
    
    -- Observações
    observacoes TEXT,
    ajustes_realizados TEXT COMMENT 'Descrição dos ajustes realizados',
    
    -- Auditoria
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    concluida_em DATETIME NULL,
    conciliado_por INT UNSIGNED,
    aprovado_por INT UNSIGNED NULL,
    
    -- Índices
    INDEX idx_conta_bancaria (conta_bancaria_id),
    INDEX idx_mes_referencia (mes_referencia),
    INDEX idx_periodo (data_inicio, data_fim),
    INDEX idx_status (status),
    
    -- Foreign Keys
    FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id),
    FOREIGN KEY (conciliado_por) REFERENCES usuarios(id),
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_concil_datas CHECK (data_fim >= data_inicio),
    CONSTRAINT chk_concil_unica UNIQUE (conta_bancaria_id, mes_referencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Conciliações bancárias';

-- ============================================================================
-- TABELA: extrato_bancario
-- Extratos bancários importados (OFX, CSV, etc)
-- ============================================================================

CREATE TABLE IF NOT EXISTS extrato_bancario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Conta Bancária
    conta_bancaria_id INT UNSIGNED NOT NULL,
    conciliacao_bancaria_id INT UNSIGNED NULL,
    
    -- Data e Identificação
    data_movimento DATE NOT NULL,
    data_valor DATE COMMENT 'Data de valor (D+1, D+2, etc)',
    numero_documento VARCHAR(50),
    historico VARCHAR(500) NOT NULL,
    
    -- Valores
    tipo_movimento ENUM('debito', 'credito') NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    saldo DECIMAL(15,2) COMMENT 'Saldo após o movimento',
    
    -- Categoria Automática
    categoria_sugerida_id INT UNSIGNED NULL COMMENT 'Categoria sugerida automaticamente',
    
    -- Conciliação
    conciliado BOOLEAN DEFAULT FALSE,
    data_conciliacao DATETIME NULL,
    lancamento_financeiro_id INT UNSIGNED NULL COMMENT 'Lançamento correspondente no sistema',
    
    -- Importação
    arquivo_origem VARCHAR(500) COMMENT 'Arquivo de origem da importação',
    linha_arquivo INT UNSIGNED COMMENT 'Linha do arquivo onde estava o registro',
    hash_unico VARCHAR(64) COMMENT 'Hash único para evitar duplicação',
    
    -- Auditoria
    importado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    importado_por INT UNSIGNED,
    
    -- Índices
    INDEX idx_conta_bancaria (conta_bancaria_id),
    INDEX idx_conciliacao (conciliacao_bancaria_id),
    INDEX idx_data_movimento (data_movimento),
    INDEX idx_tipo_movimento (tipo_movimento),
    INDEX idx_conciliado (conciliado),
    INDEX idx_lancamento (lancamento_financeiro_id),
    INDEX idx_hash_unico (hash_unico),
    
    -- Foreign Keys
    FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id),
    FOREIGN KEY (conciliacao_bancaria_id) REFERENCES conciliacoes_bancarias(id) ON DELETE SET NULL,
    FOREIGN KEY (categoria_sugerida_id) REFERENCES categorias_financeiras(id),
    FOREIGN KEY (lancamento_financeiro_id) REFERENCES lancamentos_financeiros(id),
    FOREIGN KEY (importado_por) REFERENCES usuarios(id),
    
    -- Constraints
    CONSTRAINT chk_eb_valor CHECK (valor > 0),
    CONSTRAINT chk_eb_duplicado UNIQUE (conta_bancaria_id, hash_unico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Extratos bancários importados';

-- ============================================================================
-- DADOS INICIAIS: Categorias Financeiras Padrão
-- ============================================================================

INSERT INTO categorias_financeiras (codigo, nome, tipo, natureza, nivel, caminho, aceita_lancamento) VALUES
-- Receitas (1.x.xx)
('1', 'RECEITAS', 'receita', 'operacional', 1, '1', FALSE),
('1.1', 'Receitas Operacionais', 'receita', 'operacional', 2, '1.1', FALSE),
('1.1.01', 'Prestação de Serviços', 'receita', 'operacional', 3, '1.1.01', TRUE),
('1.1.02', 'Venda de Produtos', 'receita', 'operacional', 3, '1.1.02', TRUE),
('1.1.03', 'Assinaturas e Recorrências', 'receita', 'operacional', 3, '1.1.03', TRUE),
('1.2', 'Receitas Financeiras', 'receita', 'financeira', 2, '1.2', FALSE),
('1.2.01', 'Rendimento de Aplicações', 'receita', 'financeira', 3, '1.2.01', TRUE),
('1.2.02', 'Juros Recebidos', 'receita', 'financeira', 3, '1.2.02', TRUE),
('1.3', 'Outras Receitas', 'receita', 'outra', 2, '1.3', FALSE),
('1.3.01', 'Descontos Obtidos', 'receita', 'outra', 3, '1.3.01', TRUE),
('1.3.02', 'Multas Recebidas', 'receita', 'outra', 3, '1.3.02', TRUE),

-- Despesas (2.x.xx)
('2', 'DESPESAS', 'despesa', 'operacional', 1, '2', FALSE),
('2.1', 'Despesas Operacionais', 'despesa', 'operacional', 2, '2.1', FALSE),
('2.1.01', 'Salários e Encargos', 'despesa', 'operacional', 3, '2.1.01', TRUE),
('2.1.02', 'Pró-labore', 'despesa', 'operacional', 3, '2.1.02', TRUE),
('2.1.03', 'Prestadores de Serviço', 'despesa', 'operacional', 3, '2.1.03', TRUE),
('2.1.04', 'Impostos e Taxas', 'despesa', 'operacional', 3, '2.1.04', TRUE),
('2.1.05', 'Aluguel', 'despesa', 'operacional', 3, '2.1.05', TRUE),
('2.1.06', 'Energia Elétrica', 'despesa', 'operacional', 3, '2.1.06', TRUE),
('2.1.07', 'Água e Esgoto', 'despesa', 'operacional', 3, '2.1.07', TRUE),
('2.1.08', 'Telefonia e Internet', 'despesa', 'operacional', 3, '2.1.08', TRUE),
('2.1.09', 'Material de Escritório', 'despesa', 'operacional', 3, '2.1.09', TRUE),
('2.1.10', 'Material de Limpeza', 'despesa', 'operacional', 3, '2.1.10', TRUE),
('2.1.11', 'Manutenção e Reparos', 'despesa', 'operacional', 3, '2.1.11', TRUE),
('2.1.12', 'Combustível', 'despesa', 'operacional', 3, '2.1.12', TRUE),
('2.1.13', 'Transporte e Frete', 'despesa', 'operacional', 3, '2.1.13', TRUE),
('2.1.14', 'Alimentação', 'despesa', 'operacional', 3, '2.1.14', TRUE),
('2.1.15', 'Viagens e Hospedagem', 'despesa', 'operacional', 3, '2.1.15', TRUE),
('2.1.16', 'Treinamentos e Cursos', 'despesa', 'operacional', 3, '2.1.16', TRUE),
('2.1.17', 'Marketing e Publicidade', 'despesa', 'operacional', 3, '2.1.17', TRUE),
('2.1.18', 'Softwares e Licenças', 'despesa', 'operacional', 3, '2.1.18', TRUE),
('2.1.19', 'Contabilidade e Consultoria', 'despesa', 'operacional', 3, '2.1.19', TRUE),
('2.1.20', 'Seguros', 'despesa', 'operacional', 3, '2.1.20', TRUE),
('2.2', 'Despesas Financeiras', 'despesa', 'financeira', 2, '2.2', FALSE),
('2.2.01', 'Juros Pagos', 'despesa', 'financeira', 3, '2.2.01', TRUE),
('2.2.02', 'Tarifas Bancárias', 'despesa', 'financeira', 3, '2.2.02', TRUE),
('2.2.03', 'IOF', 'despesa', 'financeira', 3, '2.2.03', TRUE),
('2.2.04', 'Multas e Juros de Mora', 'despesa', 'financeira', 3, '2.2.04', TRUE),
('2.3', 'Outras Despesas', 'despesa', 'outra', 2, '2.3', FALSE),
('2.3.01', 'Perdas Diversas', 'despesa', 'outra', 3, '2.3.01', TRUE),
('2.3.02', 'Despesas Eventuais', 'despesa', 'outra', 3, '2.3.02', TRUE),

-- Transferências (3.x.xx)
('3', 'TRANSFERÊNCIAS', 'transferencia', 'financeira', 1, '3', FALSE),
('3.1', 'Transferências Entre Contas', 'transferencia', 'financeira', 2, '3.1', FALSE),
('3.1.01', 'Transferência Bancária', 'transferencia', 'financeira', 3, '3.1.01', TRUE),
('3.1.02', 'Aplicação Financeira', 'transferencia', 'financeira', 3, '3.1.02', TRUE),
('3.1.03', 'Resgate de Aplicação', 'transferencia', 'financeira', 3, '3.1.03', TRUE);

-- Atualizar hierarquia (pai_id)
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '1') AS t) WHERE codigo LIKE '1.%' AND codigo != '1';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '1.1') AS t) WHERE codigo LIKE '1.1.%' AND codigo != '1.1';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '1.2') AS t) WHERE codigo LIKE '1.2.%' AND codigo != '1.2';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '1.3') AS t) WHERE codigo LIKE '1.3.%' AND codigo != '1.3';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '2') AS t) WHERE codigo LIKE '2.%' AND codigo != '2';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '2.1') AS t) WHERE codigo LIKE '2.1.%' AND codigo != '2.1';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '2.2') AS t) WHERE codigo LIKE '2.2.%' AND codigo != '2.2';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '2.3') AS t) WHERE codigo LIKE '2.3.%' AND codigo != '2.3';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '3') AS t) WHERE codigo LIKE '3.%' AND codigo != '3';
UPDATE categorias_financeiras SET pai_id = (SELECT id FROM (SELECT id FROM categorias_financeiras WHERE codigo = '3.1') AS t) WHERE codigo LIKE '3.1.%' AND codigo != '3.1';

-- ============================================================================
-- DADOS INICIAIS: Centros de Custo Padrão
-- ============================================================================

INSERT INTO centros_custo (codigo, nome, descricao, ativo) VALUES
('ADMIN', 'Administrativo', 'Despesas administrativas gerais', TRUE),
('VENDAS', 'Vendas e Marketing', 'Despesas comerciais e marketing', TRUE),
('TI', 'Tecnologia da Informação', 'Despesas de TI e infraestrutura', TRUE),
('RH', 'Recursos Humanos', 'Despesas com pessoal e RH', TRUE),
('FINANC', 'Financeiro', 'Despesas do departamento financeiro', TRUE),
('OPERAC', 'Operacional', 'Despesas operacionais de projetos', TRUE);

-- ============================================================================
-- TRIGGERS: Atualização Automática de Saldos e Cálculos
-- ============================================================================

-- Trigger: Atualizar valor_pago em contas_receber ao registrar pagamento
DELIMITER $$
CREATE TRIGGER atualizar_valor_pago_conta_receber
AFTER INSERT ON pagamentos
FOR EACH ROW
BEGIN
    IF NEW.tipo = 'conta_receber' AND NEW.origem_tipo = 'conta_receber' AND NEW.origem_id IS NOT NULL THEN
        UPDATE contas_receber 
        SET valor_pago = valor_pago + NEW.valor,
            status = CASE 
                WHEN (valor_pago + NEW.valor) >= valor_total THEN 'pago'
                WHEN (valor_pago + NEW.valor) > 0 THEN 'parcial'
                ELSE status
            END,
            data_pagamento = CASE 
                WHEN (valor_pago + NEW.valor) >= valor_total THEN NEW.data_pagamento
                ELSE data_pagamento
            END,
            recebido_por = NEW.processado_por
        WHERE id = NEW.origem_id;
    END IF;
END$$
DELIMITER ;

-- Trigger: Atualizar valor_pago em contas_pagar ao registrar pagamento
DELIMITER $$
CREATE TRIGGER atualizar_valor_pago_conta_pagar
AFTER INSERT ON pagamentos
FOR EACH ROW
BEGIN
    IF NEW.tipo = 'conta_pagar' AND NEW.origem_tipo = 'conta_pagar' AND NEW.origem_id IS NOT NULL THEN
        UPDATE contas_pagar 
        SET valor_pago = valor_pago + NEW.valor,
            status = CASE 
                WHEN (valor_pago + NEW.valor) >= valor_total THEN 'pago'
                WHEN (valor_pago + NEW.valor) > 0 THEN 'parcial'
                ELSE status
            END,
            data_pagamento = CASE 
                WHEN (valor_pago + NEW.valor) >= valor_total THEN NEW.data_pagamento
                ELSE data_pagamento
            END,
            pago_por = NEW.processado_por
        WHERE id = NEW.origem_id;
    END IF;
END$$
DELIMITER ;

-- Trigger: Criar lançamento financeiro automático ao registrar pagamento de conta a receber
DELIMITER $$
CREATE TRIGGER criar_lancamento_recebimento
AFTER INSERT ON pagamentos
FOR EACH ROW
BEGIN
    IF NEW.tipo = 'conta_receber' AND NEW.status = 'aprovado' THEN
        INSERT INTO lancamentos_financeiros (
            numero_lancamento,
            descricao,
            data_lancamento,
            tipo,
            natureza,
            valor,
            categoria_id,
            origem_tipo,
            origem_id,
            status,
            lancado_por
        )
        SELECT 
            CONCAT('REC-', LPAD(NEW.id, 8, '0')),
            CONCAT('Recebimento: ', NEW.descricao),
            NEW.data_pagamento,
            'receita',
            'credito',
            NEW.valor,
            cr.categoria_id,
            'pagamento',
            NEW.id,
            'confirmado',
            NEW.processado_por
        FROM contas_receber cr
        WHERE cr.id = NEW.origem_id AND NEW.origem_tipo = 'conta_receber'
        LIMIT 1;
    END IF;
END$$
DELIMITER ;

-- Trigger: Criar lançamento financeiro automático ao registrar pagamento de conta a pagar
DELIMITER $$
CREATE TRIGGER criar_lancamento_pagamento
AFTER INSERT ON pagamentos
FOR EACH ROW
BEGIN
    IF NEW.tipo = 'conta_pagar' AND NEW.status = 'aprovado' THEN
        INSERT INTO lancamentos_financeiros (
            numero_lancamento,
            descricao,
            data_lancamento,
            tipo,
            natureza,
            valor,
            categoria_id,
            origem_tipo,
            origem_id,
            status,
            lancado_por
        )
        SELECT 
            CONCAT('PAG-', LPAD(NEW.id, 8, '0')),
            CONCAT('Pagamento: ', NEW.descricao),
            NEW.data_pagamento,
            'despesa',
            'debito',
            NEW.valor,
            cp.categoria_id,
            'pagamento',
            NEW.id,
            'confirmado',
            NEW.processado_por
        FROM contas_pagar cp
        WHERE cp.id = NEW.origem_id AND NEW.origem_tipo = 'conta_pagar'
        LIMIT 1;
    END IF;
END$$
DELIMITER ;

-- Trigger: Atualizar status para 'vencido' em contas a receber vencidas
DELIMITER $$
CREATE TRIGGER atualizar_status_vencido_receber
BEFORE UPDATE ON contas_receber
FOR EACH ROW
BEGIN
    IF NEW.status = 'pendente' AND NEW.data_vencimento < CURRENT_DATE AND NEW.valor_pago < NEW.valor_total THEN
        SET NEW.status = 'vencido';
    END IF;
END$$
DELIMITER ;

-- Trigger: Atualizar status para 'vencido' em contas a pagar vencidas
DELIMITER $$
CREATE TRIGGER atualizar_status_vencido_pagar
BEFORE UPDATE ON contas_pagar
FOR EACH ROW
BEGIN
    IF NEW.status = 'pendente' AND NEW.data_vencimento < CURRENT_DATE AND NEW.valor_pago < NEW.valor_total THEN
        SET NEW.status = 'vencido';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- VIEWS: Views úteis para relatórios financeiros
-- ============================================================================

-- View: Fluxo de caixa consolidado
CREATE OR REPLACE VIEW vw_fluxo_caixa AS
SELECT 
    DATE(data_lancamento) as data,
    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as total_receitas,
    SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as total_despesas,
    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END) as saldo_dia
FROM lancamentos_financeiros
WHERE status = 'confirmado' AND ativo = TRUE
GROUP BY DATE(data_lancamento)
ORDER BY data;

-- View: Contas a receber em aberto
CREATE OR REPLACE VIEW vw_contas_receber_aberto AS
SELECT 
    cr.*,
    DATEDIFF(CURRENT_DATE, cr.data_vencimento) as dias_atraso,
    CASE 
        WHEN cr.data_vencimento < CURRENT_DATE THEN 'vencido'
        WHEN DATEDIFF(cr.data_vencimento, CURRENT_DATE) <= 3 THEN 'vence_hoje_ou_amanha'
        WHEN DATEDIFF(cr.data_vencimento, CURRENT_DATE) <= 7 THEN 'vence_semana'
        ELSE 'a_vencer'
    END as situacao,
    cat.nome as categoria_nome,
    cc.nome as centro_custo_nome
FROM contas_receber cr
LEFT JOIN categorias_financeiras cat ON cr.categoria_id = cat.id
LEFT JOIN centros_custo cc ON cr.centro_custo_id = cc.id
WHERE cr.status IN ('pendente', 'parcial', 'vencido') AND cr.ativo = TRUE;

-- View: Contas a pagar em aberto
CREATE OR REPLACE VIEW vw_contas_pagar_aberto AS
SELECT 
    cp.*,
    DATEDIFF(CURRENT_DATE, cp.data_vencimento) as dias_atraso,
    CASE 
        WHEN cp.data_vencimento < CURRENT_DATE THEN 'vencido'
        WHEN DATEDIFF(cp.data_vencimento, CURRENT_DATE) <= 3 THEN 'vence_hoje_ou_amanha'
        WHEN DATEDIFF(cp.data_vencimento, CURRENT_DATE) <= 7 THEN 'vence_semana'
        ELSE 'a_vencer'
    END as situacao,
    cat.nome as categoria_nome,
    cc.nome as centro_custo_nome
FROM contas_pagar cp
LEFT JOIN categorias_financeiras cat ON cp.categoria_id = cat.id
LEFT JOIN centros_custo cc ON cp.centro_custo_id = cc.id
WHERE cp.status IN ('pendente', 'parcial', 'vencido', 'agendado') AND cp.ativo = TRUE;

-- View: Resumo financeiro por categoria
CREATE OR REPLACE VIEW vw_resumo_por_categoria AS
SELECT 
    cat.id as categoria_id,
    cat.codigo,
    cat.nome as categoria_nome,
    cat.tipo,
    COUNT(lf.id) as total_lancamentos,
    SUM(lf.valor) as total_valor,
    DATE_FORMAT(lf.data_competencia, '%Y-%m') as mes_ano
FROM lancamentos_financeiros lf
INNER JOIN categorias_financeiras cat ON lf.categoria_id = cat.id
WHERE lf.status = 'confirmado' AND lf.ativo = TRUE
GROUP BY cat.id, cat.codigo, cat.nome, cat.tipo, DATE_FORMAT(lf.data_competencia, '%Y-%m');

-- ============================================================================
-- FIM DA MIGRATION 008
-- ============================================================================
/**
 * Migration 009 - Integração Financeiro com Projetos/Contratos
 * 
 * Adiciona campos de integração entre o módulo financeiro e:
 * - Projetos (custos e receitas por projeto)
 * - Contratos (faturamento recorrente)
 * - Atividades (alocação de custos)
 * 
 * Sprint 7 - Fase 3: Integração
 * 
 * @version 1.0.0
 * @author Clinfec Prestadores
 * @date 2025-11-07
 */

-- ============================================================================
-- ADICIONAR CAMPOS DE INTEGRAÇÃO - CONTAS A PAGAR
-- ============================================================================

ALTER TABLE contas_pagar 
ADD COLUMN projeto_id INT UNSIGNED NULL COMMENT 'Projeto associado a esta despesa',
ADD COLUMN contrato_id INT UNSIGNED NULL COMMENT 'Contrato associado a esta despesa',
ADD COLUMN atividade_id INT UNSIGNED NULL COMMENT 'Atividade associada a esta despesa',
ADD INDEX idx_projeto (projeto_id),
ADD INDEX idx_contrato (contrato_id),
ADD INDEX idx_atividade (atividade_id),
ADD CONSTRAINT fk_contas_pagar_projeto 
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_contas_pagar_contrato 
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_contas_pagar_atividade 
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE SET NULL;

-- ============================================================================
-- ADICIONAR CAMPOS DE INTEGRAÇÃO - CONTAS A RECEBER
-- ============================================================================

ALTER TABLE contas_receber 
ADD COLUMN projeto_id INT UNSIGNED NULL COMMENT 'Projeto associado a esta receita',
ADD COLUMN contrato_id INT UNSIGNED NULL COMMENT 'Contrato associado a esta receita',
ADD COLUMN atividade_id INT UNSIGNED NULL COMMENT 'Atividade associada a esta receita',
ADD INDEX idx_projeto (projeto_id),
ADD INDEX idx_contrato (contrato_id),
ADD INDEX idx_atividade (atividade_id),
ADD CONSTRAINT fk_contas_receber_projeto 
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_contas_receber_contrato 
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_contas_receber_atividade 
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE SET NULL;

-- ============================================================================
-- ADICIONAR CAMPOS DE INTEGRAÇÃO - LANÇAMENTOS FINANCEIROS
-- ============================================================================

ALTER TABLE lancamentos_financeiros 
ADD COLUMN projeto_id INT UNSIGNED NULL COMMENT 'Projeto associado a este lançamento',
ADD COLUMN contrato_id INT UNSIGNED NULL COMMENT 'Contrato associado a este lançamento',
ADD COLUMN atividade_id INT UNSIGNED NULL COMMENT 'Atividade associada a este lançamento',
ADD INDEX idx_projeto (projeto_id),
ADD INDEX idx_contrato (contrato_id),
ADD INDEX idx_atividade (atividade_id),
ADD CONSTRAINT fk_lancamentos_projeto 
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_lancamentos_contrato 
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_lancamentos_atividade 
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE SET NULL;

-- ============================================================================
-- ADICIONAR CAMPOS DE INTEGRAÇÃO - NOTAS FISCAIS
-- ============================================================================

ALTER TABLE notas_fiscais 
ADD COLUMN projeto_id INT UNSIGNED NULL COMMENT 'Projeto associado a esta nota fiscal',
ADD COLUMN contrato_id INT UNSIGNED NULL COMMENT 'Contrato associado a esta nota fiscal',
ADD INDEX idx_projeto (projeto_id),
ADD INDEX idx_contrato (contrato_id),
ADD CONSTRAINT fk_notas_fiscais_projeto 
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_notas_fiscais_contrato 
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE SET NULL;

-- ============================================================================
-- ADICIONAR CAMPOS FINANCEIROS AOS PROJETOS
-- ============================================================================

ALTER TABLE projetos 
ADD COLUMN orcamento_total DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Orçamento total do projeto',
ADD COLUMN custo_realizado DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custo realizado até o momento',
ADD COLUMN receita_realizada DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Receita realizada até o momento',
ADD COLUMN margem_lucro DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Margem de lucro em percentual',
ADD INDEX idx_financeiro (orcamento_total, custo_realizado, receita_realizada);

-- ============================================================================
-- ADICIONAR CAMPOS FINANCEIROS AOS CONTRATOS
-- ============================================================================

ALTER TABLE contratos 
ADD COLUMN valor_total DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor total do contrato',
ADD COLUMN faturamento_realizado DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Faturamento realizado até o momento',
ADD COLUMN saldo_faturar DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Saldo a faturar',
ADD COLUMN dia_vencimento_fatura INT DEFAULT 10 COMMENT 'Dia do vencimento das faturas recorrentes',
ADD COLUMN gerar_fatura_automatica BOOLEAN DEFAULT FALSE COMMENT 'Se deve gerar faturas automaticamente',
ADD INDEX idx_financeiro (valor_total, faturamento_realizado, saldo_faturar);

-- ============================================================================
-- ADICIONAR CAMPOS FINANCEIROS ÀS ATIVIDADES
-- ============================================================================

ALTER TABLE atividades 
ADD COLUMN custo_estimado DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custo estimado da atividade',
ADD COLUMN custo_realizado DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custo realizado da atividade',
ADD COLUMN horas_estimadas DECIMAL(8,2) DEFAULT 0.00 COMMENT 'Horas estimadas',
ADD COLUMN horas_realizadas DECIMAL(8,2) DEFAULT 0.00 COMMENT 'Horas realizadas',
ADD COLUMN custo_hora DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Custo por hora',
ADD INDEX idx_custos (custo_estimado, custo_realizado);

-- ============================================================================
-- TABELA AUXILIAR: projeto_custos
-- Consolidação de custos por projeto
-- ============================================================================

CREATE TABLE IF NOT EXISTS projeto_custos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Referências
    projeto_id INT UNSIGNED NOT NULL,
    
    -- Totalizadores
    custos_diretos DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custos diretamente vinculados ao projeto',
    custos_indiretos DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custos rateados',
    custos_mao_obra DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custos com mão de obra',
    custos_materiais DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custos com materiais',
    custos_servicos DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custos com serviços terceirizados',
    custos_outros DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Outros custos',
    custo_total DECIMAL(15,2) GENERATED ALWAYS AS 
        (custos_diretos + custos_indiretos + custos_mao_obra + custos_materiais + custos_servicos + custos_outros) STORED,
    
    -- Receitas
    receitas_faturadas DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Receitas já faturadas',
    receitas_a_faturar DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Receitas a faturar',
    receita_total DECIMAL(15,2) GENERATED ALWAYS AS 
        (receitas_faturadas + receitas_a_faturar) STORED,
    
    -- Resultado
    resultado DECIMAL(15,2) GENERATED ALWAYS AS 
        ((receitas_faturadas + receitas_a_faturar) - (custos_diretos + custos_indiretos + custos_mao_obra + custos_materiais + custos_servicos + custos_outros)) STORED,
    margem_percentual DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Margem de lucro em %',
    
    -- Período
    mes_referencia DATE NOT NULL COMMENT 'Mês de referência (YYYY-MM-01)',
    
    -- Auditoria
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    UNIQUE KEY uk_projeto_mes (projeto_id, mes_referencia),
    INDEX idx_mes (mes_referencia),
    
    -- Foreign Keys
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Consolidação mensal de custos e receitas por projeto';

-- ============================================================================
-- TABELA AUXILIAR: contrato_faturamento
-- Consolidação de faturamento por contrato
-- ============================================================================

CREATE TABLE IF NOT EXISTS contrato_faturamento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Referências
    contrato_id INT UNSIGNED NOT NULL,
    
    -- Faturamento
    valor_faturado DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor faturado no período',
    valor_recebido DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor recebido no período',
    valor_pendente DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor pendente de recebimento',
    
    -- Período
    mes_referencia DATE NOT NULL COMMENT 'Mês de referência (YYYY-MM-01)',
    
    -- Status
    fatura_gerada BOOLEAN DEFAULT FALSE COMMENT 'Se a fatura foi gerada',
    fatura_enviada BOOLEAN DEFAULT FALSE COMMENT 'Se a fatura foi enviada',
    data_envio DATE NULL COMMENT 'Data de envio da fatura',
    
    -- Notas Fiscais
    nota_fiscal_id INT UNSIGNED NULL COMMENT 'Nota fiscal vinculada',
    
    -- Auditoria
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    UNIQUE KEY uk_contrato_mes (contrato_id, mes_referencia),
    INDEX idx_mes (mes_referencia),
    INDEX idx_nota_fiscal (nota_fiscal_id),
    
    -- Foreign Keys
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Consolidação mensal de faturamento por contrato';

-- ============================================================================
-- TRIGGERS PARA ATUALIZAÇÃO AUTOMÁTICA DE TOTALIZADORES
-- ============================================================================

-- Trigger: Atualizar custo_realizado do projeto ao inserir conta a pagar
DELIMITER //
CREATE TRIGGER trg_contas_pagar_insert_projeto 
AFTER INSERT ON contas_pagar
FOR EACH ROW
BEGIN
    IF NEW.projeto_id IS NOT NULL AND NEW.status = 'pago' THEN
        UPDATE projetos 
        SET custo_realizado = custo_realizado + NEW.valor_final 
        WHERE id = NEW.projeto_id;
    END IF;
END//

-- Trigger: Atualizar custo_realizado do projeto ao atualizar conta a pagar
CREATE TRIGGER trg_contas_pagar_update_projeto 
AFTER UPDATE ON contas_pagar
FOR EACH ROW
BEGIN
    IF OLD.projeto_id IS NOT NULL AND OLD.status != 'pago' AND NEW.status = 'pago' THEN
        UPDATE projetos 
        SET custo_realizado = custo_realizado + NEW.valor_final 
        WHERE id = OLD.projeto_id;
    END IF;
END//

-- Trigger: Atualizar receita_realizada do projeto ao inserir conta a receber
CREATE TRIGGER trg_contas_receber_insert_projeto 
AFTER INSERT ON contas_receber
FOR EACH ROW
BEGIN
    IF NEW.projeto_id IS NOT NULL AND NEW.status = 'recebido' THEN
        UPDATE projetos 
        SET receita_realizada = receita_realizada + NEW.valor_final 
        WHERE id = NEW.projeto_id;
    END IF;
END//

-- Trigger: Atualizar receita_realizada do projeto ao atualizar conta a receber
CREATE TRIGGER trg_contas_receber_update_projeto 
AFTER UPDATE ON contas_receber
FOR EACH ROW
BEGIN
    IF OLD.projeto_id IS NOT NULL AND OLD.status != 'recebido' AND NEW.status = 'recebido' THEN
        UPDATE projetos 
        SET receita_realizada = receita_realizada + NEW.valor_final 
        WHERE id = OLD.projeto_id;
    END IF;
END//

-- Trigger: Atualizar faturamento_realizado do contrato
CREATE TRIGGER trg_contas_receber_insert_contrato 
AFTER INSERT ON contas_receber
FOR EACH ROW
BEGIN
    IF NEW.contrato_id IS NOT NULL AND NEW.status = 'recebido' THEN
        UPDATE contratos 
        SET faturamento_realizado = faturamento_realizado + NEW.valor_final,
            saldo_faturar = valor_total - (faturamento_realizado + NEW.valor_final)
        WHERE id = NEW.contrato_id;
    END IF;
END//

-- Trigger: Atualizar custo_realizado da atividade
CREATE TRIGGER trg_contas_pagar_insert_atividade 
AFTER INSERT ON contas_pagar
FOR EACH ROW
BEGIN
    IF NEW.atividade_id IS NOT NULL AND NEW.status = 'pago' THEN
        UPDATE atividades 
        SET custo_realizado = custo_realizado + NEW.valor_final 
        WHERE id = NEW.atividade_id;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- VIEWS PARA FACILITAR CONSULTAS
-- ============================================================================

-- View: Custos por projeto
CREATE OR REPLACE VIEW vw_projeto_custos AS
SELECT 
    p.id AS projeto_id,
    p.nome AS projeto_nome,
    p.orcamento_total,
    p.custo_realizado,
    p.receita_realizada,
    (p.receita_realizada - p.custo_realizado) AS resultado,
    CASE 
        WHEN p.receita_realizada > 0 THEN 
            ((p.receita_realizada - p.custo_realizado) / p.receita_realizada * 100)
        ELSE 0 
    END AS margem_percentual,
    COUNT(DISTINCT cp.id) AS total_contas_pagar,
    COUNT(DISTINCT cr.id) AS total_contas_receber,
    SUM(CASE WHEN cp.status = 'pendente' THEN cp.valor_final ELSE 0 END) AS custos_pendentes,
    SUM(CASE WHEN cr.status = 'pendente' THEN cr.valor_final ELSE 0 END) AS receitas_pendentes
FROM projetos p
LEFT JOIN contas_pagar cp ON cp.projeto_id = p.id
LEFT JOIN contas_receber cr ON cr.projeto_id = p.id
GROUP BY p.id, p.nome, p.orcamento_total, p.custo_realizado, p.receita_realizada;

-- View: Faturamento por contrato
CREATE OR REPLACE VIEW vw_contrato_faturamento AS
SELECT 
    c.id AS contrato_id,
    c.numero AS contrato_numero,
    c.valor_total,
    c.faturamento_realizado,
    c.saldo_faturar,
    CASE 
        WHEN c.valor_total > 0 THEN 
            (c.faturamento_realizado / c.valor_total * 100)
        ELSE 0 
    END AS percentual_faturado,
    COUNT(DISTINCT cr.id) AS total_contas_receber,
    SUM(CASE WHEN cr.status = 'recebido' THEN cr.valor_final ELSE 0 END) AS valor_recebido,
    SUM(CASE WHEN cr.status = 'pendente' THEN cr.valor_final ELSE 0 END) AS valor_pendente
FROM contratos c
LEFT JOIN contas_receber cr ON cr.contrato_id = c.id
GROUP BY c.id, c.numero, c.valor_total, c.faturamento_realizado, c.saldo_faturar;

-- View: Custos por atividade
CREATE OR REPLACE VIEW vw_atividade_custos AS
SELECT 
    a.id AS atividade_id,
    a.titulo AS atividade_titulo,
    a.custo_estimado,
    a.custo_realizado,
    (a.custo_estimado - a.custo_realizado) AS variacao_custo,
    CASE 
        WHEN a.custo_estimado > 0 THEN 
            ((a.custo_realizado - a.custo_estimado) / a.custo_estimado * 100)
        ELSE 0 
    END AS variacao_percentual,
    a.horas_estimadas,
    a.horas_realizadas,
    CASE 
        WHEN a.horas_realizadas > 0 THEN 
            (a.custo_realizado / a.horas_realizadas)
        ELSE 0 
    END AS custo_hora_real,
    COUNT(cp.id) AS total_contas_pagar
FROM atividades a
LEFT JOIN contas_pagar cp ON cp.atividade_id = a.id
GROUP BY a.id, a.titulo, a.custo_estimado, a.custo_realizado, a.horas_estimadas, a.horas_realizadas;

-- ============================================================================
-- COMENTÁRIOS E DOCUMENTAÇÃO
-- ============================================================================

-- Esta migration cria a estrutura completa de integração entre:
-- 1. Módulo Financeiro (Contas, Lançamentos, Notas Fiscais)
-- 2. Módulo de Projetos (Custos, Receitas, Margem)
-- 3. Módulo de Contratos (Faturamento Recorrente)
-- 4. Módulo de Atividades (Alocação de Custos)

-- Funcionalidades principais:
-- - Vinculação de contas a pagar/receber com projetos
-- - Vinculação de contas com contratos
-- - Vinculação de contas com atividades
-- - Atualização automática de totalizadores via triggers
-- - Consolidação mensal de custos e faturamento
-- - Views para facilitar consultas e relatórios
-- - Campos financeiros nos projetos (orçamento, custo, receita, margem)
-- - Campos financeiros nos contratos (valor total, faturamento, saldo)
-- - Campos financeiros nas atividades (custos, horas)

-- ============================================================================
-- FIM DA MIGRATION 009
-- ============================================================================
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
