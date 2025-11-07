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
