<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * AtividadeFinanceiro Model
 * 
 * Métodos de integração financeira para atividades
 * 
 * Funcionalidades:
 * - Custos da atividade (estimado vs realizado)
 * - Horas trabalhadas e custo/hora
 * - Análise de variação de custos
 * - Alocação de recursos
 * 
 * @package App\Models
 * @since Sprint 7 - Fase 3
 */
class AtividadeFinanceiro
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter custos consolidados da atividade
     * 
     * @param int $atividadeId ID da atividade
     * @return array Custos detalhados
     */
    public function getCustos($atividadeId)
    {
        $sql = "SELECT 
                    a.custo_estimado,
                    a.custo_realizado,
                    a.horas_estimadas,
                    a.horas_realizadas,
                    a.custo_hora,
                    
                    -- Custos por Contas a Pagar
                    COALESCE(SUM(CASE WHEN cp.status = 'pago' 
                        THEN cp.valor_final ELSE 0 END), 0) as custo_pago,
                    COALESCE(SUM(CASE WHEN cp.status = 'pendente' 
                        THEN cp.valor_final ELSE 0 END), 0) as custo_pendente,
                    
                    -- Quantidade de contas
                    COUNT(DISTINCT cp.id) as total_contas,
                    
                    -- Variação
                    (a.custo_realizado - a.custo_estimado) as variacao_custo,
                    CASE 
                        WHEN a.custo_estimado > 0 THEN 
                            ((a.custo_realizado - a.custo_estimado) / a.custo_estimado * 100)
                        ELSE 0 
                    END as variacao_percentual
                    
                FROM atividades a
                LEFT JOIN contas_pagar cp ON cp.atividade_id = a.id
                WHERE a.id = :atividade_id
                GROUP BY a.id, a.custo_estimado, a.custo_realizado, 
                         a.horas_estimadas, a.horas_realizadas, a.custo_hora";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':atividade_id' => $atividadeId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular custo por hora da atividade
     * 
     * @param int $atividadeId ID da atividade
     * @return array Análise de custo/hora
     */
    public function getCustoHora($atividadeId)
    {
        $custos = $this->getCustos($atividadeId);
        
        $custoRealizado = $custos['custo_realizado'];
        $horasRealizadas = $custos['horas_realizadas'];
        $custoEstimado = $custos['custo_estimado'];
        $horasEstimadas = $custos['horas_estimadas'];
        
        $custoHoraRealizado = ($horasRealizadas > 0) ? ($custoRealizado / $horasRealizadas) : 0;
        $custoHoraEstimado = ($horasEstimadas > 0) ? ($custoEstimado / $horasEstimadas) : 0;
        $variacaoCustoHora = $custoHoraRealizado - $custoHoraEstimado;
        
        return [
            'custo_hora_estimado' => round($custoHoraEstimado, 2),
            'custo_hora_realizado' => round($custoHoraRealizado, 2),
            'variacao_custo_hora' => round($variacaoCustoHora, 2),
            'horas_estimadas' => $horasEstimadas,
            'horas_realizadas' => $horasRealizadas,
            'horas_restantes' => max(0, $horasEstimadas - $horasRealizadas),
            'percentual_horas_usado' => ($horasEstimadas > 0) 
                ? round(($horasRealizadas / $horasEstimadas) * 100, 2) 
                : 0
        ];
    }
    
    /**
     * Analisar variação de custos da atividade
     * 
     * @param int $atividadeId ID da atividade
     * @return array Análise de variação
     */
    public function analisarVariacao($atividadeId)
    {
        $custos = $this->getCustos($atividadeId);
        
        $variacaoAbsoluta = $custos['variacao_custo'];
        $variacaoPercentual = $custos['variacao_percentual'];
        
        // Classificar performance
        $performance = 'no_prazo';
        if ($variacaoPercentual > 10) {
            $performance = 'acima_orcamento';
        } elseif ($variacaoPercentual < -10) {
            $performance = 'abaixo_orcamento';
        }
        
        // Status
        $status = 'ok';
        if ($custos['custo_realizado'] > $custos['custo_estimado']) {
            $status = 'atencao'; // Estourou orçamento
        } elseif ($custos['custo_realizado'] > ($custos['custo_estimado'] * 0.9)) {
            $status = 'alerta'; // Próximo do limite
        }
        
        return [
            'custo_estimado' => $custos['custo_estimado'],
            'custo_realizado' => $custos['custo_realizado'],
            'variacao_absoluta' => round($variacaoAbsoluta, 2),
            'variacao_percentual' => round($variacaoPercentual, 2),
            'performance' => $performance,
            'status' => $status,
            'custo_pendente' => $custos['custo_pendente']
        ];
    }
    
    /**
     * Obter alocação de recursos da atividade
     * 
     * @param int $atividadeId ID da atividade
     * @return array Recursos alocados
     */
    public function getAlocacaoRecursos($atividadeId)
    {
        // Buscar membros da equipe alocados
        $sqlEquipe = "SELECT 
                        u.nome as membro_nome,
                        ae.funcao,
                        ae.horas_alocadas,
                        ae.custo_hora_membro,
                        (ae.horas_alocadas * ae.custo_hora_membro) as custo_total_membro
                    FROM atividade_equipe ae
                    INNER JOIN usuarios u ON ae.usuario_id = u.id
                    WHERE ae.atividade_id = :atividade_id
                    ORDER BY custo_total_membro DESC";
        
        $stmtEquipe = $this->db->prepare($sqlEquipe);
        $stmtEquipe->execute([':atividade_id' => $atividadeId]);
        $equipe = $stmtEquipe->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar materiais/recursos
        $sqlRecursos = "SELECT 
                            cp.descricao,
                            cp.valor_final as custo,
                            cp.data_pagamento,
                            cp.status,
                            cat.nome as categoria
                        FROM contas_pagar cp
                        LEFT JOIN categorias_financeiras cat ON cp.categoria_id = cat.id
                        WHERE cp.atividade_id = :atividade_id
                        ORDER BY cp.valor_final DESC";
        
        $stmtRecursos = $this->db->prepare($sqlRecursos);
        $stmtRecursos->execute([':atividade_id' => $atividadeId]);
        $recursos = $stmtRecursos->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'equipe' => $equipe,
            'recursos' => $recursos,
            'total_equipe' => array_sum(array_column($equipe, 'custo_total_membro')),
            'total_recursos' => array_sum(array_column($recursos, 'custo'))
        ];
    }
    
    /**
     * Atualizar totalizadores financeiros da atividade
     * 
     * @param int $atividadeId ID da atividade
     * @return bool Sucesso da operação
     */
    public function atualizarTotalizadores($atividadeId)
    {
        $custos = $this->getCustos($atividadeId);
        $custoHora = $this->getCustoHora($atividadeId);
        
        $sql = "UPDATE atividades 
                SET custo_realizado = :custo_realizado,
                    custo_hora = :custo_hora,
                    atualizado_em = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':custo_realizado' => $custos['custo_pago'],
            ':custo_hora' => $custoHora['custo_hora_realizado'],
            ':id' => $atividadeId
        ]);
    }
    
    /**
     * Gerar relatório financeiro completo da atividade
     * 
     * @param int $atividadeId ID da atividade
     * @return array Relatório completo
     */
    public function gerarRelatorioCompleto($atividadeId)
    {
        return [
            'custos' => $this->getCustos($atividadeId),
            'custo_hora' => $this->getCustoHora($atividadeId),
            'variacao' => $this->analisarVariacao($atividadeId),
            'alocacao_recursos' => $this->getAlocacaoRecursos($atividadeId),
            'gerado_em' => date('Y-m-d H:i:s')
        ];
    }
}
