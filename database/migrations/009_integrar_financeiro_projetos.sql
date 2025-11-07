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
