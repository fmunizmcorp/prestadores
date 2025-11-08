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
