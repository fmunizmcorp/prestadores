-- ============================================================================
-- Migration 015: Corrigir Schema de Projetos e Atividades (Versão Simplificada)
-- Data: 2025-11-10
-- Descrição: Adiciona colunas e tabelas necessárias (syntax MariaDB compatível)
-- ============================================================================

-- Colunas para projetos (ignora erros se já existirem)
ALTER TABLE projetos ADD COLUMN empresa_tomadora_id INT(11) NULL;
ALTER TABLE projetos ADD COLUMN categoria_id INT(11) NULL;
ALTER TABLE projetos ADD COLUMN gerente_id INT(11) NULL;
ALTER TABLE projetos ADD COLUMN created_by INT(11) NULL;
ALTER TABLE projetos ADD COLUMN prioridade ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media';

-- Tabela projeto_categorias
CREATE TABLE IF NOT EXISTS projeto_categorias (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    cor VARCHAR(7) DEFAULT '#6c757d',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO projeto_categorias (nome, descricao, cor) VALUES
('Infraestrutura', 'Projetos de infraestrutura de TI', '#007bff'),
('Desenvolvimento', 'Projetos de desenvolvimento de software', '#28a745'),
('Consultoria', 'Projetos de consultoria', '#17a2b8'),
('Suporte', 'Projetos de suporte técnico', '#ffc107');

-- Tabela projeto_etapas
CREATE TABLE IF NOT EXISTS projeto_etapas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT(11) NOT NULL,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT NULL,
    ordem INT(11) DEFAULT 0,
    status ENUM('pendente', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'pendente',
    progresso INT(11) DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela projeto_equipe
CREATE TABLE IF NOT EXISTS projeto_equipe (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    papel VARCHAR(100) NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Colunas para atividades
ALTER TABLE atividades ADD COLUMN titulo VARCHAR(255) NULL;
ALTER TABLE atividades ADD COLUMN responsavel_id INT(11) NULL;
ALTER TABLE atividades ADD COLUMN prioridade ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media';
UPDATE atividades SET titulo = nome WHERE titulo IS NULL OR titulo = '';
