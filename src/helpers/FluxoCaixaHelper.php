<?php
namespace App\Helpers;

use App\Models\LancamentoFinanceiro;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use PDO;

/**
 * Class FluxoCaixaHelper
 * 
 * Helper para gerenciar projeções de fluxo de caixa, relatórios financeiros
 * e análises de liquidez.
 * 
 * Funcionalidades:
 * - Fluxo de caixa realizado
 * - Fluxo de caixa projetado
 * - Análise de liquidez
 * - Previsão de saldo
 * - Relatórios DRE
 * - Análise de receitas e despesas
 * - Gráficos e dashboards
 * 
 * @package App\Helpers
 */
class FluxoCaixaHelper
{
    private $db;
    
    /**
     * Construtor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtém fluxo de caixa realizado por período
     * 
     * @param string $dataInicio Data início
     * @param string $dataFim Data fim
     * @param int|null $contaBancariaId ID da conta bancária (opcional)
     * @return array Fluxo de caixa realizado
     */
    public function getFluxoRealizado($dataInicio, $dataFim, $contaBancariaId = null)
    {
        $lancamentoModel = new LancamentoFinanceiro();
        
        $filtros = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'status' => 'confirmado'
        ];
        
        if ($contaBancariaId) {
            $filtros['conta_bancaria_id'] = $contaBancariaId;
        }
        
        return $lancamentoModel->getFluxoCaixa($filtros);
    }
    
    /**
     * Obtém fluxo de caixa projetado
     * 
     * @param string $dataInicio Data início
     * @param string $dataFim Data fim
     * @return array Fluxo de caixa projetado
     */
    public function getFluxoProjetado($dataInicio, $dataFim)
    {
        $contaPagarModel = new ContaPagar();
        $contaReceberModel = new ContaReceber();
        
        // Busca contas a pagar pendentes
        $contasPagar = $contaPagarModel->all([
            'status' => 'pendente',
            'data_vencimento_inicio' => $dataInicio,
            'data_vencimento_fim' => $dataFim
        ])['data'];
        
        // Busca contas a receber pendentes
        $contasReceber = $contaReceberModel->all([
            'status' => 'pendente',
            'data_vencimento_inicio' => $dataInicio,
            'data_vencimento_fim' => $dataFim
        ])['data'];
        
        // Agrupa por data
        $fluxo = [];
        
        foreach ($contasPagar as $conta) {
            $data = $conta['data_vencimento'];
            if (!isset($fluxo[$data])) {
                $fluxo[$data] = ['entradas' => 0, 'saidas' => 0, 'saldo_dia' => 0];
            }
            $fluxo[$data]['saidas'] += $conta['valor_pendente'];
        }
        
        foreach ($contasReceber as $conta) {
            $data = $conta['data_vencimento'];
            if (!isset($fluxo[$data])) {
                $fluxo[$data] = ['entradas' => 0, 'saidas' => 0, 'saldo_dia' => 0];
            }
            $fluxo[$data]['entradas'] += $conta['valor_pendente'];
        }
        
        // Calcula saldo do dia
        foreach ($fluxo as $data => &$valores) {
            $valores['saldo_dia'] = $valores['entradas'] - $valores['saidas'];
        }
        
        // Ordena por data
        ksort($fluxo);
        
        return $fluxo;
    }
    
    /**
     * Obtém fluxo de caixa consolidado (realizado + projetado)
     * 
     * @param string $dataInicio Data início
     * @param string $dataFim Data fim
     * @param int|null $contaBancariaId ID da conta bancária
     * @return array Fluxo consolidado
     */
    public function getFluxoConsolidado($dataInicio, $dataFim, $contaBancariaId = null)
    {
        $hoje = date('Y-m-d');
        
        // Fluxo realizado até hoje
        $fluxoRealizado = [];
        if ($dataInicio <= $hoje) {
            $dataFimRealizado = min($hoje, $dataFim);
            $fluxoRealizado = $this->getFluxoRealizado($dataInicio, $dataFimRealizado, $contaBancariaId);
        }
        
        // Fluxo projetado a partir de amanhã
        $fluxoProjetado = [];
        if ($dataFim > $hoje) {
            $dataInicioProjetado = max($dataInicio, date('Y-m-d', strtotime($hoje . ' +1 day')));
            $fluxoProjetado = $this->getFluxoProjetado($dataInicioProjetado, $dataFim);
        }
        
        // Consolida os dois fluxos
        $fluxoConsolidado = [];
        
        foreach ($fluxoRealizado as $item) {
            $data = $item['data_lancamento'];
            $fluxoConsolidado[$data] = [
                'data' => $data,
                'entradas' => $item['entradas'],
                'saidas' => $item['saidas'],
                'saldo_dia' => $item['saldo_dia'],
                'tipo' => 'realizado'
            ];
        }
        
        foreach ($fluxoProjetado as $data => $valores) {
            if (!isset($fluxoConsolidado[$data])) {
                $fluxoConsolidado[$data] = [
                    'data' => $data,
                    'entradas' => 0,
                    'saidas' => 0,
                    'saldo_dia' => 0,
                    'tipo' => 'projetado'
                ];
            }
            
            $fluxoConsolidado[$data]['entradas'] += $valores['entradas'];
            $fluxoConsolidado[$data]['saidas'] += $valores['saidas'];
            $fluxoConsolidado[$data]['saldo_dia'] += $valores['saldo_dia'];
        }
        
        // Ordena por data
        ksort($fluxoConsolidado);
        
        // Calcula saldo acumulado
        $saldoAcumulado = $this->getSaldoInicial($dataInicio, $contaBancariaId);
        
        foreach ($fluxoConsolidado as &$item) {
            $saldoAcumulado += $item['saldo_dia'];
            $item['saldo_acumulado'] = $saldoAcumulado;
        }
        
        return array_values($fluxoConsolidado);
    }
    
    /**
     * Obtém saldo inicial de uma conta
     * 
     * @param string $dataReferencia Data de referência
     * @param int|null $contaBancariaId ID da conta bancária
     * @return float Saldo inicial
     */
    public function getSaldoInicial($dataReferencia, $contaBancariaId = null)
    {
        $where = ["l.ativo = TRUE", "l.status = 'confirmado'", "l.data_lancamento < :data_referencia"];
        $params = ['data_referencia' => $dataReferencia];
        
        if ($contaBancariaId) {
            $where[] = "l.conta_bancaria_id = :conta_bancaria_id";
            $params['conta_bancaria_id'] = $contaBancariaId;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                COALESCE(SUM(CASE WHEN l.tipo = 'entrada' THEN l.valor ELSE 0 END), 0) as total_entradas,
                COALESCE(SUM(CASE WHEN l.tipo = 'saida' THEN l.valor ELSE 0 END), 0) as total_saidas
                FROM lancamentos_financeiros l
                WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total_entradas'] - $resultado['total_saidas'];
    }
    
    /**
     * Analisa liquidez da empresa
     * 
     * @param string $dataReferencia Data de referência
     * @return array Indicadores de liquidez
     */
    public function analisarLiquidez($dataReferencia = null)
    {
        if (!$dataReferencia) {
            $dataReferencia = date('Y-m-d');
        }
        
        $contaPagarModel = new ContaPagar();
        $contaReceberModel = new ContaReceber();
        
        // Contas a pagar vencendo em 30 dias
        $dataFim30 = date('Y-m-d', strtotime($dataReferencia . ' +30 days'));
        $contasPagar30 = $contaPagarModel->all([
            'status' => 'pendente',
            'data_vencimento_inicio' => $dataReferencia,
            'data_vencimento_fim' => $dataFim30
        ])['data'];
        
        $totalPagar30 = array_sum(array_column($contasPagar30, 'valor_pendente'));
        
        // Contas a receber vencendo em 30 dias
        $contasReceber30 = $contaReceberModel->all([
            'status' => 'pendente',
            'data_vencimento_inicio' => $dataReferencia,
            'data_vencimento_fim' => $dataFim30
        ])['data'];
        
        $totalReceber30 = array_sum(array_column($contasReceber30, 'valor_pendente'));
        
        // Saldo atual
        $saldoAtual = $this->getSaldoInicial($dataReferencia);
        
        // Liquidez corrente = (Saldo + Receber) / Pagar
        $liquidezCorrente = $totalPagar30 > 0 ? 
            ($saldoAtual + $totalReceber30) / $totalPagar30 : 
            999; // Valor alto se não há contas a pagar
        
        // Liquidez imediata = Saldo / Pagar
        $liquidezImediata = $totalPagar30 > 0 ? 
            $saldoAtual / $totalPagar30 : 
            999;
        
        return [
            'saldo_atual' => $saldoAtual,
            'total_a_receber_30d' => $totalReceber30,
            'total_a_pagar_30d' => $totalPagar30,
            'liquidez_corrente' => round($liquidezCorrente, 2),
            'liquidez_imediata' => round($liquidezImediata, 2),
            'capital_giro' => $totalReceber30 - $totalPagar30,
            'situacao' => $this->classificarLiquidez($liquidezCorrente)
        ];
    }
    
    /**
     * Classifica situação de liquidez
     * 
     * @param float $liquidezCorrente Índice de liquidez corrente
     * @return string Classificação
     */
    private function classificarLiquidez($liquidezCorrente)
    {
        if ($liquidezCorrente >= 2.0) {
            return 'excelente';
        } elseif ($liquidezCorrente >= 1.5) {
            return 'boa';
        } elseif ($liquidezCorrente >= 1.0) {
            return 'regular';
        } elseif ($liquidezCorrente >= 0.5) {
            return 'ruim';
        } else {
            return 'critica';
        }
    }
    
    /**
     * Obtém DRE (Demonstração do Resultado do Exercício)
     * 
     * @param string $dataInicio Data início
     * @param string $dataFim Data fim
     * @return array DRE estruturado
     */
    public function getDRE($dataInicio, $dataFim)
    {
        $lancamentoModel = new LancamentoFinanceiro();
        
        $dre = $lancamentoModel->getDRE([
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
        
        // Calcula margens
        $receitas = $dre['receitas'];
        $despesas = $dre['despesas'];
        $resultado = $dre['resultado'];
        
        $margemBruta = $receitas > 0 ? ($resultado / $receitas) * 100 : 0;
        $margemOperacional = $receitas > 0 ? ($resultado / $receitas) * 100 : 0;
        
        return [
            'receitas_brutas' => $receitas,
            'despesas_operacionais' => $despesas,
            'resultado_operacional' => $resultado,
            'margem_bruta' => round($margemBruta, 2),
            'margem_operacional' => round($margemOperacional, 2)
        ];
    }
    
    /**
     * Analisa receitas por categoria
     * 
     * @param string $dataInicio Data início
     * @param string $dataFim Data fim
     * @return array Receitas por categoria
     */
    public function analisarReceitasPorCategoria($dataInicio, $dataFim)
    {
        $sql = "SELECT 
                cf.nome as categoria,
                cf.codigo,
                SUM(l.valor) as total,
                COUNT(*) as quantidade
                FROM lancamentos_financeiros l
                INNER JOIN categorias_financeiras cf ON l.categoria_financeira_id = cf.id
                WHERE l.ativo = TRUE
                AND l.status = 'confirmado'
                AND l.tipo = 'entrada'
                AND l.data_competencia BETWEEN :data_inicio AND :data_fim
                AND cf.tipo = 'receita'
                GROUP BY cf.id, cf.nome, cf.codigo
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Analisa despesas por categoria
     * 
     * @param string $dataInicio Data início
     * @param string $dataFim Data fim
     * @return array Despesas por categoria
     */
    public function analisarDespesasPorCategoria($dataInicio, $dataFim)
    {
        $sql = "SELECT 
                cf.nome as categoria,
                cf.codigo,
                SUM(l.valor) as total,
                COUNT(*) as quantidade
                FROM lancamentos_financeiros l
                INNER JOIN categorias_financeiras cf ON l.categoria_financeira_id = cf.id
                WHERE l.ativo = TRUE
                AND l.status = 'confirmado'
                AND l.tipo = 'saida'
                AND l.data_competencia BETWEEN :data_inicio AND :data_fim
                AND cf.tipo = 'despesa'
                GROUP BY cf.id, cf.nome, cf.codigo
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém resumo financeiro do dashboard
     * 
     * @return array Resumo financeiro
     */
    public function getDashboard()
    {
        $hoje = date('Y-m-d');
        $primeiroDiaMes = date('Y-m-01');
        $ultimoDiaMes = date('Y-m-t');
        
        // Saldo atual
        $saldoAtual = $this->getSaldoInicial(date('Y-m-d', strtotime($hoje . ' +1 day')));
        
        // Receitas e despesas do mês
        $lancamentoModel = new LancamentoFinanceiro();
        $estatisticas = $lancamentoModel->getEstatisticas([
            'data_inicio' => $primeiroDiaMes,
            'data_fim' => $ultimoDiaMes
        ]);
        
        // Contas a receber e pagar
        $contaReceberModel = new ContaReceber();
        $contaPagarModel = new ContaPagar();
        
        $estatisticasReceber = $contaReceberModel->getEstatisticas([
            'data_inicio' => $primeiroDiaMes,
            'data_fim' => $ultimoDiaMes
        ]);
        
        $estatisticasPagar = $contaPagarModel->getEstatisticas([
            'data_inicio' => $primeiroDiaMes,
            'data_fim' => $ultimoDiaMes
        ]);
        
        // Análise de liquidez
        $liquidez = $this->analisarLiquidez($hoje);
        
        return [
            'saldo_atual' => $saldoAtual,
            'receitas_mes' => $estatisticas['total_entradas'],
            'despesas_mes' => $estatisticas['total_saidas'],
            'resultado_mes' => $estatisticas['total_entradas'] - $estatisticas['total_saidas'],
            'contas_receber_pendentes' => $estatisticasReceber['valor_pendente_total'],
            'contas_pagar_pendentes' => $estatisticasPagar['valor_pendente_total'],
            'contas_vencidas_receber' => $estatisticasReceber['vencidas'],
            'contas_vencidas_pagar' => $estatisticasPagar['vencidas'],
            'liquidez' => $liquidez
        ];
    }
    
    /**
     * Projeta fluxo de caixa para os próximos N dias
     * 
     * @param int $dias Número de dias a projetar
     * @return array Projeção de saldo
     */
    public function projetarSaldo($dias = 30)
    {
        $hoje = date('Y-m-d');
        $dataFim = date('Y-m-d', strtotime($hoje . " +{$dias} days"));
        
        $fluxo = $this->getFluxoProjetado($hoje, $dataFim);
        
        $saldoAtual = $this->getSaldoInicial($hoje);
        $projecao = [];
        
        foreach ($fluxo as $data => $valores) {
            $saldoAtual += $valores['saldo_dia'];
            $projecao[] = [
                'data' => $data,
                'entradas' => $valores['entradas'],
                'saidas' => $valores['saidas'],
                'saldo_projetado' => $saldoAtual,
                'situacao' => $saldoAtual >= 0 ? 'positivo' : 'negativo'
            ];
        }
        
        return $projecao;
    }
    
    /**
     * Identifica dias com saldo negativo
     * 
     * @param int $dias Número de dias a analisar
     * @return array Dias com projeção negativa
     */
    public function identificarDiasNegativos($dias = 30)
    {
        $projecao = $this->projetarSaldo($dias);
        
        return array_filter($projecao, function($dia) {
            return $dia['saldo_projetado'] < 0;
        });
    }
}
