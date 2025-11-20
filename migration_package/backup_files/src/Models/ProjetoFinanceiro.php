<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * ProjetoFinanceiro Model
 * 
 * Métodos de integração financeira para projetos
 * 
 * Funcionalidades:
 * - Custos do projeto (diretos, indiretos, mão de obra, materiais)
 * - Receitas do projeto (faturadas e a faturar)
 * - Margem de lucro
 * - Análise de performance financeira
 * - Consolidação mensal de custos
 * 
 * @package App\Models
 * @since Sprint 7 - Fase 3
 */
class ProjetoFinanceiro
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter custos consolidados do projeto
     * 
     * @param int $projetoId ID do projeto
     * @param string|null $dataInicio Data início filtro (opcional)
     * @param string|null $dataFim Data fim filtro (opcional)
     * @return array Custos detalhados
     */
    public function getCustos($projetoId, $dataInicio = null, $dataFim = null)
    {
        $sql = "SELECT 
                    -- Custos Diretos
                    COALESCE(SUM(CASE WHEN cp.tipo = 'fornecedor' AND cp.status = 'pago' 
                        THEN cp.valor_final ELSE 0 END), 0) as custos_diretos,
                    
                    -- Custos Indiretos
                    COALESCE(SUM(CASE WHEN cp.tipo = 'outras' AND cp.status = 'pago' 
                        THEN cp.valor_final ELSE 0 END), 0) as custos_indiretos,
                    
                    -- Custos Mão de Obra
                    COALESCE(SUM(CASE WHEN cp.tipo = 'funcionario' AND cp.status = 'pago' 
                        THEN cp.valor_final ELSE 0 END), 0) as custos_mao_obra,
                    
                    -- Custos Materiais
                    COALESCE(SUM(CASE WHEN cp.tipo = 'fornecedor' AND cat.nome LIKE '%Material%' AND cp.status = 'pago' 
                        THEN cp.valor_final ELSE 0 END), 0) as custos_materiais,
                    
                    -- Custos Serviços
                    COALESCE(SUM(CASE WHEN cp.tipo = 'prestador' AND cp.status = 'pago' 
                        THEN cp.valor_final ELSE 0 END), 0) as custos_servicos,
                    
                    -- Custos Pendentes
                    COALESCE(SUM(CASE WHEN cp.status = 'pendente' 
                        THEN cp.valor_final ELSE 0 END), 0) as custos_pendentes,
                    
                    -- Total Geral
                    COALESCE(SUM(CASE WHEN cp.status = 'pago' 
                        THEN cp.valor_final ELSE 0 END), 0) as custo_total_realizado,
                    
                    -- Quantidade de contas
                    COUNT(DISTINCT cp.id) as total_contas
                    
                FROM contas_pagar cp
                LEFT JOIN categorias_financeiras cat ON cp.categoria_id = cat.id
                WHERE cp.projeto_id = :projeto_id";
        
        $params = [':projeto_id' => $projetoId];
        
        if ($dataInicio) {
            $sql .= " AND cp.data_pagamento >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND cp.data_pagamento <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter receitas consolidadas do projeto
     * 
     * @param int $projetoId ID do projeto
     * @param string|null $dataInicio Data início filtro (opcional)
     * @param string|null $dataFim Data fim filtro (opcional)
     * @return array Receitas detalhadas
     */
    public function getReceitas($projetoId, $dataInicio = null, $dataFim = null)
    {
        $sql = "SELECT 
                    -- Receitas Faturadas
                    COALESCE(SUM(CASE WHEN cr.status = 'recebido' 
                        THEN cr.valor_final ELSE 0 END), 0) as receitas_faturadas,
                    
                    -- Receitas a Faturar
                    COALESCE(SUM(CASE WHEN cr.status = 'pendente' 
                        THEN cr.valor_final ELSE 0 END), 0) as receitas_a_faturar,
                    
                    -- Receitas Vencidas
                    COALESCE(SUM(CASE WHEN cr.status = 'pendente' AND cr.data_vencimento < CURDATE() 
                        THEN cr.valor_final ELSE 0 END), 0) as receitas_vencidas,
                    
                    -- Total Geral
                    COALESCE(SUM(cr.valor_final), 0) as receita_total,
                    
                    -- Quantidade de contas
                    COUNT(DISTINCT cr.id) as total_contas,
                    
                    -- Notas Fiscais
                    COUNT(DISTINCT cr.nota_fiscal_id) as total_notas_fiscais
                    
                FROM contas_receber cr
                WHERE cr.projeto_id = :projeto_id";
        
        $params = [':projeto_id' => $projetoId];
        
        if ($dataInicio) {
            $sql .= " AND cr.data_emissao >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND cr.data_emissao <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular margem de lucro do projeto
     * 
     * @param int $projetoId ID do projeto
     * @return array Resultado financeiro e margem
     */
    public function getMargemLucro($projetoId)
    {
        $custos = $this->getCustos($projetoId);
        $receitas = $this->getReceitas($projetoId);
        
        $custoTotal = $custos['custo_total_realizado'];
        $receitaTotal = $receitas['receitas_faturadas'];
        
        $resultado = $receitaTotal - $custoTotal;
        $margemPercentual = ($receitaTotal > 0) ? (($resultado / $receitaTotal) * 100) : 0;
        
        return [
            'custo_total' => $custoTotal,
            'receita_total' => $receitaTotal,
            'resultado' => $resultado,
            'margem_percentual' => round($margemPercentual, 2),
            'situacao' => $resultado >= 0 ? 'lucro' : 'prejuizo',
            'receitas_pendentes' => $receitas['receitas_a_faturar'],
            'custos_pendentes' => $custos['custos_pendentes']
        ];
    }
    
    /**
     * Obter performance financeira do projeto
     * 
     * @param int $projetoId ID do projeto
     * @return array Análise completa de performance
     */
    public function getPerformanceFinanceira($projetoId)
    {
        // Buscar orçamento do projeto
        $sql = "SELECT orcamento_total, custo_realizado, receita_realizada, margem_lucro 
                FROM projetos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $projetoId]);
        $projeto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$projeto) {
            return null;
        }
        
        $orcamento = $projeto['orcamento_total'];
        $custoRealizado = $projeto['custo_realizado'];
        $receitaRealizada = $projeto['receita_realizada'];
        
        // Calcular variações
        $variacaoOrcamento = $orcamento - $custoRealizado;
        $variacaoPercentual = ($orcamento > 0) ? (($variacaoOrcamento / $orcamento) * 100) : 0;
        
        // Performance
        $performance = 'bom';
        if ($custoRealizado > $orcamento) {
            $performance = 'ruim'; // Estourou orçamento
        } elseif ($custoRealizado > ($orcamento * 0.9)) {
            $performance = 'atencao'; // Próximo do limite
        }
        
        // ROI (Return on Investment)
        $roi = ($custoRealizado > 0) ? ((($receitaRealizada - $custoRealizado) / $custoRealizado) * 100) : 0;
        
        return [
            'orcamento_total' => $orcamento,
            'custo_realizado' => $custoRealizado,
            'receita_realizada' => $receitaRealizada,
            'variacao_orcamento' => $variacaoOrcamento,
            'variacao_percentual' => round($variacaoPercentual, 2),
            'performance' => $performance,
            'roi' => round($roi, 2),
            'percentual_orcamento_usado' => round(($custoRealizado / $orcamento) * 100, 2)
        ];
    }
    
    /**
     * Obter consolidação mensal de custos
     * 
     * @param int $projetoId ID do projeto
     * @param int $meses Número de meses para buscar (padrão: 12)
     * @return array Array com custos mensais
     */
    public function getConsolidacaoMensal($projetoId, $meses = 12)
    {
        $sql = "SELECT 
                    DATE_FORMAT(cp.data_pagamento, '%Y-%m-01') as mes_referencia,
                    DATE_FORMAT(cp.data_pagamento, '%m/%Y') as mes_formatado,
                    COALESCE(SUM(cp.valor_final), 0) as custo_mes,
                    COUNT(cp.id) as quantidade_contas
                FROM contas_pagar cp
                WHERE cp.projeto_id = :projeto_id
                    AND cp.status = 'pago'
                    AND cp.data_pagamento >= DATE_SUB(CURDATE(), INTERVAL :meses MONTH)
                GROUP BY mes_referencia, mes_formatado
                ORDER BY mes_referencia ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':projeto_id' => $projetoId,
            ':meses' => $meses
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter top fornecedores do projeto
     * 
     * @param int $projetoId ID do projeto
     * @param int $limit Número de fornecedores (padrão: 10)
     * @return array Top fornecedores com valores
     */
    public function getTopFornecedores($projetoId, $limit = 10)
    {
        $sql = "SELECT 
                    f.nome as fornecedor_nome,
                    f.cpf_cnpj,
                    COUNT(cp.id) as quantidade_contas,
                    COALESCE(SUM(cp.valor_final), 0) as valor_total,
                    COALESCE(SUM(CASE WHEN cp.status = 'pago' THEN cp.valor_final ELSE 0 END), 0) as valor_pago,
                    COALESCE(SUM(CASE WHEN cp.status = 'pendente' THEN cp.valor_final ELSE 0 END), 0) as valor_pendente
                FROM contas_pagar cp
                INNER JOIN fornecedores f ON cp.fornecedor_id = f.id
                WHERE cp.projeto_id = :projeto_id
                GROUP BY f.id, f.nome, f.cpf_cnpj
                ORDER BY valor_total DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter contas pendentes do projeto
     * 
     * @param int $projetoId ID do projeto
     * @param string $tipo 'pagar' ou 'receber'
     * @return array Contas pendentes
     */
    public function getContasPendentes($projetoId, $tipo = 'pagar')
    {
        $table = ($tipo === 'pagar') ? 'contas_pagar' : 'contas_receber';
        
        $sql = "SELECT 
                    c.*,
                    cat.nome as categoria_nome,
                    DATEDIFF(CURDATE(), c.data_vencimento) as dias_atraso
                FROM {$table} c
                LEFT JOIN categorias_financeiras cat ON c.categoria_id = cat.id
                WHERE c.projeto_id = :projeto_id
                    AND c.status = 'pendente'
                ORDER BY c.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':projeto_id' => $projetoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualizar totalizadores financeiros do projeto
     * 
     * @param int $projetoId ID do projeto
     * @return bool Sucesso da operação
     */
    public function atualizarTotalizadores($projetoId)
    {
        $custos = $this->getCustos($projetoId);
        $receitas = $this->getReceitas($projetoId);
        $margem = $this->getMargemLucro($projetoId);
        
        $sql = "UPDATE projetos 
                SET custo_realizado = :custo_realizado,
                    receita_realizada = :receita_realizada,
                    margem_lucro = :margem_lucro,
                    atualizado_em = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':custo_realizado' => $custos['custo_total_realizado'],
            ':receita_realizada' => $receitas['receitas_faturadas'],
            ':margem_lucro' => $margem['margem_percentual'],
            ':id' => $projetoId
        ]);
    }
    
    /**
     * Gerar relatório financeiro completo do projeto
     * 
     * @param int $projetoId ID do projeto
     * @return array Relatório completo
     */
    public function gerarRelatorioCompleto($projetoId)
    {
        return [
            'custos' => $this->getCustos($projetoId),
            'receitas' => $this->getReceitas($projetoId),
            'margem' => $this->getMargemLucro($projetoId),
            'performance' => $this->getPerformanceFinanceira($projetoId),
            'consolidacao_mensal' => $this->getConsolidacaoMensal($projetoId, 12),
            'top_fornecedores' => $this->getTopFornecedores($projetoId, 10),
            'contas_pendentes_pagar' => $this->getContasPendentes($projetoId, 'pagar'),
            'contas_pendentes_receber' => $this->getContasPendentes($projetoId, 'receber'),
            'gerado_em' => date('Y-m-d H:i:s')
        ];
    }
}
