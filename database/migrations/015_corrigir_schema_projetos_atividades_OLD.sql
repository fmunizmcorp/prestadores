-- ============================================================================
-- Migration 015: Corrigir Schema de Projetos e Atividades
-- Data: 2025-11-10
-- Descrição: Adiciona colunas faltantes e cria tabelas relacionadas
-- ============================================================================

-- ============================================================================
-- 1. CORRIGIR TABELA PROJETOS
-- ============================================================================

-- Adicionar colunas faltantes na tabela projetos (ignora se já existem)
SET @dbname = 'u673902663_prestadores';
SET @tablename = 'projetos';
SET @columnname = 'empresa_tomadora_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT(11) NULL AFTER contrato_id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'categoria_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT(11) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'gerente_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT(11) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'created_by';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT(11) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'prioridade';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM(''baixa'', ''media'', ''alta'', ''critica'') DEFAULT ''media''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Atualizar ENUM de status se necessário
ALTER TABLE projetos 
MODIFY COLUMN status ENUM('planejamento', 'em_andamento', 'concluido', 'cancelado', 'pausado') DEFAULT 'planejamento';

-- ============================================================================
-- 2. CRIAR TABELA PROJETO_CATEGORIAS
-- ============================================================================

CREATE TABLE IF NOT EXISTS projeto_categorias (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    cor VARCHAR(7) DEFAULT '#6c757d' COMMENT 'Cor em HEX para interface',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Categorias de projetos para organização';

-- Inserir categorias padrão se não existirem
INSERT IGNORE INTO projeto_categorias (nome, descricao, cor) VALUES
('Infraestrutura', 'Projetos de infraestrutura de TI', '#007bff'),
('Desenvolvimento', 'Projetos de desenvolvimento de software', '#28a745'),
('Consultoria', 'Projetos de consultoria', '#17a2b8'),
('Suporte', 'Projetos de suporte técnico', '#ffc107'),
('Implantação', 'Projetos de implantação de sistemas', '#dc3545'),
('Manutenção', 'Projetos de manutenção', '#6c757d'),
('Treinamento', 'Projetos de capacitação', '#fd7e14'),
('Segurança', 'Projetos de segurança da informação', '#e83e8c');

-- ============================================================================
-- 3. CRIAR TABELA PROJETO_ETAPAS
-- ============================================================================

CREATE TABLE IF NOT EXISTS projeto_etapas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT(11) NOT NULL,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT NULL,
    ordem INT(11) DEFAULT 0 COMMENT 'Ordem de execução',
    status ENUM('pendente', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'pendente',
    data_inicio DATE NULL,
    data_fim_prevista DATE NULL,
    data_fim_real DATE NULL,
    responsavel_id INT(11) NULL,
    horas_previstas DECIMAL(10,2) DEFAULT 0.00,
    horas_realizadas DECIMAL(10,2) DEFAULT 0.00,
    custo_previsto DECIMAL(15,2) DEFAULT 0.00,
    custo_real DECIMAL(15,2) DEFAULT 0.00,
    progresso INT(11) DEFAULT 0 COMMENT 'Percentual de 0-100',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_projeto (projeto_id),
    INDEX idx_status (status),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_ordem (ordem),
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Etapas/fases de cada projeto';

-- ============================================================================
-- 4. CRIAR TABELA PROJETO_EQUIPE
-- ============================================================================

CREATE TABLE IF NOT EXISTS projeto_equipe (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    papel VARCHAR(100) NULL COMMENT 'Gerente, Desenvolvedor, Analista, etc',
    horas_alocadas DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Horas alocadas por semana',
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    valor_hora DECIMAL(10,2) NULL COMMENT 'Valor/hora do profissional neste projeto',
    observacoes TEXT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_projeto (projeto_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_ativo (ativo),
    UNIQUE KEY uk_projeto_usuario (projeto_id, usuario_id, deleted_at),
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Membros da equipe de cada projeto';

-- ============================================================================
-- 5. CORRIGIR TABELA ATIVIDADES
-- ============================================================================

-- Adicionar coluna titulo se não existir (mantém nome por compatibilidade)
ALTER TABLE atividades 
ADD COLUMN IF NOT EXISTS titulo VARCHAR(255) NULL AFTER nome;

-- Popular titulo com nome onde título está vazio
UPDATE atividades SET titulo = nome WHERE titulo IS NULL OR titulo = '';

-- Adicionar outras colunas faltantes
ALTER TABLE atividades 
ADD COLUMN IF NOT EXISTS responsavel_id INT(11) NULL AFTER projeto_id,
ADD COLUMN IF NOT EXISTS prioridade ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media' AFTER status;

-- Atualizar ENUM de status se necessário
ALTER TABLE atividades 
MODIFY COLUMN status ENUM('pendente', 'em_andamento', 'concluida', 'cancelada', 'bloqueada') DEFAULT 'pendente';

-- Adicionar colunas de tags e dependências
ALTER TABLE atividades
ADD COLUMN IF NOT EXISTS tags JSON NULL COMMENT 'Tags/labels da atividade',
ADD COLUMN IF NOT EXISTS dependencias JSON NULL COMMENT 'IDs de atividades que devem ser concluídas antes';

-- ============================================================================
-- 6. CRIAR TABELA ATIVIDADE_ANEXOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS atividade_anexos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT(11) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    tipo_arquivo VARCHAR(50) NULL,
    tamanho_bytes INT(11) NULL,
    usuario_upload_id INT(11) NULL,
    descricao TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_atividade (atividade_id),
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Anexos de atividades';

-- ============================================================================
-- 7. CRIAR TABELA ATIVIDADE_COMENTARIOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS atividade_comentarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    comentario TEXT NOT NULL,
    tipo ENUM('comentario', 'atualizacao_status', 'mudanca_responsavel') DEFAULT 'comentario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_atividade (atividade_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo),
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Comentários e histórico de atividades';

-- ============================================================================
-- 8. CRIAR FOREIGN KEYS (se ainda não existirem)
-- ============================================================================

-- FKs para projetos (ignora se já existirem)
ALTER TABLE projetos 
ADD CONSTRAINT fk_projetos_tomadora 
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id) 
    ON DELETE SET NULL;

ALTER TABLE projetos 
ADD CONSTRAINT fk_projetos_contrato 
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) 
    ON DELETE SET NULL;

ALTER TABLE projetos 
ADD CONSTRAINT fk_projetos_categoria 
    FOREIGN KEY (categoria_id) REFERENCES projeto_categorias(id) 
    ON DELETE SET NULL;

-- FKs para atividades
ALTER TABLE atividades 
ADD CONSTRAINT fk_atividades_projeto 
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) 
    ON DELETE CASCADE;

-- ============================================================================
-- 9. CRIAR ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ============================================================================

ALTER TABLE projetos
ADD INDEX IF NOT EXISTS idx_empresa_tomadora (empresa_tomadora_id),
ADD INDEX IF NOT EXISTS idx_categoria (categoria_id),
ADD INDEX IF NOT EXISTS idx_gerente (gerente_id),
ADD INDEX IF NOT EXISTS idx_prioridade (prioridade),
ADD INDEX IF NOT EXISTS idx_status_prioridade (status, prioridade);

ALTER TABLE atividades
ADD INDEX IF NOT EXISTS idx_responsavel (responsavel_id),
ADD INDEX IF NOT EXISTS idx_prioridade (prioridade),
ADD INDEX IF NOT EXISTS idx_status_prioridade (status, prioridade),
ADD INDEX IF NOT EXISTS idx_data_fim (data_fim_prevista);

-- ============================================================================
-- 10. POPULAR DADOS DE TESTE (OPCIONAL)
-- ============================================================================

-- Inserir um projeto de exemplo se não houver nenhum
INSERT INTO projetos (codigo, nome, descricao, data_inicio, data_fim_prevista, orcamento_previsto, status, prioridade)
SELECT 'PROJ-001', 'Projeto de Exemplo', 'Projeto criado automaticamente pela migration 015', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 10000.00, 'planejamento', 'media'
WHERE NOT EXISTS (SELECT 1 FROM projetos LIMIT 1);

-- ============================================================================
-- FIM DA MIGRATION 015
-- ============================================================================
