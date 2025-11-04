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
