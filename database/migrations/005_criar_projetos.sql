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
