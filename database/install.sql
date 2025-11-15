-- ============================================
-- INSTALAÇÃO MANUAL DO BANCO DE DADOS
-- Sistema: Clinfec Prestadores
-- Versão: Sprint 31
-- Data: 2024-11-14
-- ============================================
-- Este script cria APENAS as tabelas essenciais para o sistema funcionar
-- SEM usar DatabaseMigration.php (contorna problema de cache)
-- ============================================

-- Desabilitar verificação de foreign keys temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. TABELA: usuarios (ESSENCIAL - já deve existir)
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'gestor', 'operador') DEFAULT 'operador',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário master se não existir
INSERT IGNORE INTO usuarios (id, nome, email, senha, tipo, ativo) VALUES
(1, 'Administrador', 'admin@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);
-- Senha padrão: password

-- ============================================
-- 2. TABELA: empresas_prestadoras
-- ============================================
CREATE TABLE IF NOT EXISTS empresas_prestadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255),
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    tipo_prestador ENUM('pj', 'pf', 'mei') DEFAULT 'pj',
    cpf VARCHAR(14),
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    
    -- Endereço
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    -- Contatos
    email VARCHAR(255),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    site VARCHAR(255),
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABELA: empresas_tomadoras (CLIENTES)
-- ============================================
CREATE TABLE IF NOT EXISTS empresas_tomadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    
    -- Endereço
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    -- Contatos
    email_principal VARCHAR(255),
    email_financeiro VARCHAR(255),
    email_projetos VARCHAR(255),
    telefone_principal VARCHAR(20),
    telefone_secundario VARCHAR(20),
    celular VARCHAR(20),
    whatsapp VARCHAR(20),
    site VARCHAR(255),
    
    -- Financeiro
    dia_fechamento INT DEFAULT 30,
    dia_pagamento INT DEFAULT 5,
    forma_pagamento_preferencial VARCHAR(50),
    banco VARCHAR(100),
    agencia VARCHAR(20),
    conta VARCHAR(30),
    tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente',
    
    -- Logo
    logo VARCHAR(255),
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_nome_fantasia (nome_fantasia),
    INDEX idx_ativo (ativo),
    INDEX idx_cidade_estado (cidade, estado),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABELA: servicos
-- ============================================
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    tipo ENUM('hora', 'dia', 'mes', 'projeto') DEFAULT 'hora',
    valor_referencia DECIMAL(10,2),
    ativo BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_nome (nome),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABELA: contratos
-- ============================================
CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_contrato VARCHAR(100) UNIQUE NOT NULL,
    
    -- Partes
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    
    -- Dados do contrato
    descricao TEXT,
    objeto TEXT,
    
    -- Vigência
    data_inicio DATE NOT NULL,
    data_fim DATE,
    
    -- Valores
    valor_total DECIMAL(15,2),
    valor_executado DECIMAL(15,2) DEFAULT 0,
    
    -- Status
    status ENUM('rascunho', 'ativo', 'suspenso', 'encerrado', 'cancelado') DEFAULT 'rascunho',
    
    -- Arquivos
    arquivo_contrato VARCHAR(500),
    
    -- Observações
    observacoes TEXT,
    motivo_encerramento TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_by INT,
    
    INDEX idx_numero_contrato (numero_contrato),
    INDEX idx_status (status),
    INDEX idx_datas (data_inicio, data_fim),
    INDEX idx_tomadora (empresa_tomadora_id),
    INDEX idx_prestadora (empresa_prestadora_id),
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABELA: atestados
-- ============================================
CREATE TABLE IF NOT EXISTS atestados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    numero VARCHAR(50) UNIQUE NOT NULL,
    
    -- Período
    mes_referencia INT NOT NULL,
    ano_referencia INT NOT NULL,
    data_emissao DATE NOT NULL,
    
    -- Valores
    valor_bruto DECIMAL(15,2) NOT NULL,
    valor_descontos DECIMAL(15,2) DEFAULT 0,
    valor_liquido DECIMAL(15,2) NOT NULL,
    
    -- Status
    status ENUM('rascunho', 'emitido', 'aprovado', 'rejeitado', 'pago') DEFAULT 'rascunho',
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_contrato (contrato_id),
    INDEX idx_status (status),
    INDEX idx_mes_ano (mes_referencia, ano_referencia),
    
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABELA: faturas
-- ============================================
CREATE TABLE IF NOT EXISTS faturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atestado_id INT NOT NULL,
    numero_nf VARCHAR(50) UNIQUE NOT NULL,
    
    -- Dados da NF
    data_emissao DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    
    -- Valores
    valor_total DECIMAL(15,2) NOT NULL,
    valor_pago DECIMAL(15,2),
    
    -- Status
    status ENUM('emitida', 'enviada', 'aprovada', 'paga', 'cancelada') DEFAULT 'emitida',
    
    -- Arquivo
    arquivo_nf VARCHAR(500),
    
    -- Observações
    observacoes TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_atestado (atestado_id),
    INDEX idx_status (status),
    INDEX idx_data_vencimento (data_vencimento),
    
    FOREIGN KEY (atestado_id) REFERENCES atestados(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TABELA: documentos
-- ============================================
CREATE TABLE IF NOT EXISTS documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Vinculação polimórfica
    entidade_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'contrato', 'atestado', 'fatura') NOT NULL,
    entidade_id INT NOT NULL,
    
    -- Arquivo
    nome_arquivo VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    tipo_documento VARCHAR(100),
    tamanho_bytes BIGINT,
    caminho VARCHAR(500) NOT NULL,
    
    -- Metadados
    descricao TEXT,
    data_emissao DATE,
    data_validade DATE,
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    
    INDEX idx_entidade (entidade_tipo, entidade_id),
    INDEX idx_tipo_documento (tipo_documento),
    INDEX idx_validade (data_validade),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. TABELA: database_version (controle de versão)
-- ============================================
CREATE TABLE IF NOT EXISTS database_version (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version INT NOT NULL,
    description VARCHAR(255),
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir versão inicial
INSERT INTO database_version (version, description) VALUES
(31, 'Instalação manual Sprint 31 - Tabelas essenciais');

-- Reabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- DADOS INICIAIS
-- ============================================

-- Inserir alguns serviços básicos
INSERT IGNORE INTO servicos (nome, descricao, tipo, valor_referencia, ativo, created_by) VALUES
('Limpeza e Conservação', 'Serviços de limpeza e conservação predial', 'hora', 25.00, TRUE, 1),
('Manutenção Predial', 'Manutenção preventiva e corretiva', 'hora', 45.00, TRUE, 1),
('Segurança e Vigilância', 'Serviços de segurança patrimonial', 'mes', 2500.00, TRUE, 1),
('Jardinagem', 'Cuidados com áreas verdes', 'hora', 30.00, TRUE, 1),
('Serviços Administrativos', 'Apoio administrativo e gestão', 'hora', 35.00, TRUE, 1);

-- ============================================
-- FIM DA INSTALAÇÃO
-- ============================================

SELECT '✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!' as status;
SELECT COUNT(*) as total_tabelas FROM information_schema.tables WHERE table_schema = DATABASE();
