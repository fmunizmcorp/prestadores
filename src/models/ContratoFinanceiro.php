<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * ContratoFinanceiro Model
 * 
 * Métodos de integração financeira para contratos
 * 
 * Funcionalidades:
 * - Faturamento recorrente
 * - Geração automática de faturas
 * - Consolidação de faturamento
 * - Análise de inadimplência
 * - Projeções de receita
 * 
 * @package App\Models
 * @since Sprint 7 - Fase 3
 */
class ContratoFinanceiro
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obter faturamento consolidado do contrato
     * 
     * @param int $contratoId ID do contrato
     * @param string|null $dataInicio Data início filtro (opcional)
     * @param string|null $dataFim Data fim filtro (opcional)
     * @return array Faturamento detalhado
     */
    public function getFaturamento($contratoId, $dataInicio = null, $dataFim = null)
    {
        $sql = "SELECT 
                    -- Valor Total do Contrato
                    c.valor_total as valor_contrato,
                    
                    -- Faturamento Realizado
                    COALESCE(SUM(CASE WHEN cr.status = 'recebido' 
                        THEN cr.valor_final ELSE 0 END), 0) as faturamento_realizado,
                    
                    -- Faturamento Pendente
                    COALESCE(SUM(CASE WHEN cr.status = 'pendente' 
                        THEN cr.valor_final ELSE 0 END), 0) as faturamento_pendente,
                    
                    -- Faturamento Atrasado
                    COALESCE(SUM(CASE WHEN cr.status = 'pendente' AND cr.data_vencimento < CURDATE() 
                        THEN cr.valor_final ELSE 0 END), 0) as faturamento_atrasado,
                    
                    -- Quantidade de Faturas
                    COUNT(DISTINCT cr.id) as total_faturas,
                    COUNT(DISTINCT CASE WHEN cr.status = 'recebido' THEN cr.id END) as faturas_pagas,
                    COUNT(DISTINCT CASE WHEN cr.status = 'pendente' THEN cr.id END) as faturas_pendentes,
                    
                    -- Notas Fiscais
                    COUNT(DISTINCT cr.nota_fiscal_id) as total_notas_fiscais,
                    
                    -- Saldo a Faturar
                    (c.valor_total - COALESCE(SUM(cr.valor_final), 0)) as saldo_faturar
                    
                FROM contratos c
                LEFT JOIN contas_receber cr ON cr.contrato_id = c.id
                WHERE c.id = :contrato_id";
        
        $params = [':contrato_id' => $contratoId];
        
        if ($dataInicio) {
            $sql .= " AND cr.data_emissao >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND cr.data_emissao <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }
        
        $sql .= " GROUP BY c.id, c.valor_total";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calcular percentuais
        if ($resultado) {
            $valorContrato = $resultado['valor_contrato'];
            $resultado['percentual_faturado'] = ($valorContrato > 0) 
                ? round(($resultado['faturamento_realizado'] / $valorContrato) * 100, 2) 
                : 0;
            $resultado['percentual_recebido'] = ($valorContrato > 0) 
                ? round(($resultado['faturamento_realizado'] / $valorContrato) * 100, 2) 
                : 0;
        }
        
        return $resultado;
    }
    
    /**
     * Obter histórico de faturamento mensal
     * 
     * @param int $contratoId ID do contrato
     * @param int $meses Número de meses para buscar (padrão: 12)
     * @return array Array com faturamento mensal
     */
    public function getHistoricoMensal($contratoId, $meses = 12)
    {
        $sql = "SELECT 
                    DATE_FORMAT(cr.data_emissao, '%Y-%m-01') as mes_referencia,
                    DATE_FORMAT(cr.data_emissao, '%m/%Y') as mes_formatado,
                    COALESCE(SUM(CASE WHEN cr.status = 'recebido' THEN cr.valor_final ELSE 0 END), 0) as valor_recebido,
                    COALESCE(SUM(CASE WHEN cr.status = 'pendente' THEN cr.valor_final ELSE 0 END), 0) as valor_pendente,
                    COALESCE(SUM(cr.valor_final), 0) as valor_total,
                    COUNT(cr.id) as quantidade_faturas
                FROM contas_receber cr
                WHERE cr.contrato_id = :contrato_id
                    AND cr.data_emissao >= DATE_SUB(CURDATE(), INTERVAL :meses MONTH)
                GROUP BY mes_referencia, mes_formatado
                ORDER BY mes_referencia ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':contrato_id' => $contratoId,
            ':meses' => $meses
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Gerar fatura recorrente do contrato
     * 
     * @param int $contratoId ID do contrato
     * @param string $mesReferencia Mês de referência (YYYY-MM-01)
     * @return int|false ID da conta criada ou false em caso de erro
     */
    public function gerarFaturaRecorrente($contratoId, $mesReferencia = null)
    {
        if (!$mesReferencia) {
            $mesReferencia = date('Y-m-01');
        }
        
        // Buscar dados do contrato
        $sql = "SELECT * FROM contratos WHERE id = :id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $contratoId]);
        $contrato = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$contrato || !$contrato['gerar_fatura_automatica']) {
            return false;
        }
        
        // Verificar se já existe fatura para este mês
        $sqlCheck = "SELECT COUNT(*) as total 
                     FROM contas_receber 
                     WHERE contrato_id = :contrato_id 
                     AND DATE_FORMAT(data_emissao, '%Y-%m') = :mes";
        
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute([
            ':contrato_id' => $contratoId,
            ':mes' => date('Y-m', strtotime($mesReferencia))
        ]);
        
        $check = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if ($check['total'] > 0) {
            return false; // Já existe fatura para este mês
        }
        
        // Calcular valor da fatura (pode ser valor fixo ou proporcional)
        $valorFatura = $contrato['valor_mensal'] ?? ($contrato['valor_total'] / 12);
        
        // Calcular data de vencimento
        $diaVencimento = $contrato['dia_vencimento_fatura'] ?? 10;
        $dataEmissao = $mesReferencia;
        $dataVencimento = date('Y-m-d', strtotime($mesReferencia . " +{$diaVencimento} days"));
        
        // Criar conta a receber
        $sqlInsert = "INSERT INTO contas_receber (
                        descricao, tipo, cliente_id, contrato_id, categoria_id,
                        valor_original, valor_final, data_emissao, data_vencimento,
                        forma_recebimento, status, observacoes, criado_em, criado_por
                      ) VALUES (
                        :descricao, 'contrato', :cliente_id, :contrato_id, :categoria_id,
                        :valor, :valor, :data_emissao, :data_vencimento,
                        'boleto', 'pendente', :observacoes, NOW(), :criado_por
                      )";
        
        $stmtInsert = $this->db->prepare($sqlInsert);
        
        $descricao = "Fatura {$contrato['numero']} - " . date('m/Y', strtotime($mesReferencia));
        $observacoes = "Fatura gerada automaticamente pelo sistema";
        
        $resultado = $stmtInsert->execute([
            ':descricao' => $descricao,
            ':cliente_id' => $contrato['empresa_tomadora_id'],
            ':contrato_id' => $contratoId,
            ':categoria_id' => 1, // Categoria padrão de receita de contrato
            ':valor' => $valorFatura,
            ':data_emissao' => $dataEmissao,
            ':data_vencimento' => $dataVencimento,
            ':observacoes' => $observacoes,
            ':criado_por' => $_SESSION['user_id'] ?? 1
        ]);
        
        if ($resultado) {
            $contaId = $this->db->lastInsertId();
            
            // Registrar na tabela de faturamento
            $sqlFaturamento = "INSERT INTO contrato_faturamento (
                                contrato_id, mes_referencia, valor_faturado, 
                                fatura_gerada, criado_em
                               ) VALUES (
                                :contrato_id, :mes_referencia, :valor_faturado,
                                1, NOW()
                               )";
            
            $stmtFaturamento = $this->db->prepare($sqlFaturamento);
            $stmtFaturamento->execute([
                ':contrato_id' => $contratoId,
                ':mes_referencia' => $mesReferencia,
                ':valor_faturado' => $valorFatura
            ]);
            
            return $contaId;
        }
        
        return false;
    }
    
    /**
     * Analisar inadimplência do contrato
     * 
     * @param int $contratoId ID do contrato
     * @return array Análise de inadimplência
     */
    public function analisarInadimplencia($contratoId)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT cr.id) as total_faturas_atrasadas,
                    COALESCE(SUM(cr.valor_final), 0) as valor_total_atrasado,
                    MIN(cr.data_vencimento) as vencimento_mais_antigo,
                    MAX(DATEDIFF(CURDATE(), cr.data_vencimento)) as dias_atraso_maximo,
                    AVG(DATEDIFF(CURDATE(), cr.data_vencimento)) as dias_atraso_medio
                FROM contas_receber cr
                WHERE cr.contrato_id = :contrato_id
                    AND cr.status = 'pendente'
                    AND cr.data_vencimento < CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':contrato_id' => $contratoId]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Classificar risco
        $diasAtrasoMaximo = $resultado['dias_atraso_maximo'] ?? 0;
        if ($diasAtrasoMaximo == 0) {
            $risco = 'sem_risco';
        } elseif ($diasAtrasoMaximo <= 30) {
            $risco = 'baixo';
        } elseif ($diasAtrasoMaximo <= 60) {
            $risco = 'medio';
        } else {
            $risco = 'alto';
        }
        
        $resultado['classificacao_risco'] = $risco;
        
        return $resultado;
    }
    
    /**
     * Projetar receita futura do contrato
     * 
     * @param int $contratoId ID do contrato
     * @param int $meses Número de meses para projetar (padrão: 12)
     * @return array Projeção de receita
     */
    public function projetarReceita($contratoId, $meses = 12)
    {
        // Buscar dados do contrato
        $sql = "SELECT valor_total, data_inicio, data_fim, valor_mensal 
                FROM contratos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $contratoId]);
        $contrato = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$contrato) {
            return null;
        }
        
        $valorMensal = $contrato['valor_mensal'] ?? ($contrato['valor_total'] / 12);
        $dataInicio = new \DateTime();
        $projecao = [];
        
        for ($i = 1; $i <= $meses; $i++) {
            $mesReferencia = clone $dataInicio;
            $mesReferencia->modify("+{$i} month");
            
            // Verificar se está dentro do período do contrato
            $dataFim = new \DateTime($contrato['data_fim']);
            if ($mesReferencia > $dataFim) {
                break;
            }
            
            $projecao[] = [
                'mes' => $mesReferencia->format('Y-m-01'),
                'mes_formatado' => $mesReferencia->format('m/Y'),
                'valor_projetado' => $valorMensal,
                'status' => 'projetado'
            ];
        }
        
        return $projecao;
    }
    
    /**
     * Obter faturas pendentes do contrato
     * 
     * @param int $contratoId ID do contrato
     * @return array Faturas pendentes
     */
    public function getFaturasPendentes($contratoId)
    {
        $sql = "SELECT 
                    cr.*,
                    DATEDIFF(CURDATE(), cr.data_vencimento) as dias_atraso,
                    CASE 
                        WHEN cr.data_vencimento < CURDATE() THEN 'vencida'
                        WHEN DATEDIFF(cr.data_vencimento, CURDATE()) <= 7 THEN 'vencendo'
                        ELSE 'a_vencer'
                    END as situacao
                FROM contas_receber cr
                WHERE cr.contrato_id = :contrato_id
                    AND cr.status = 'pendente'
                ORDER BY cr.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':contrato_id' => $contratoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualizar totalizadores financeiros do contrato
     * 
     * @param int $contratoId ID do contrato
     * @return bool Sucesso da operação
     */
    public function atualizarTotalizadores($contratoId)
    {
        $faturamento = $this->getFaturamento($contratoId);
        
        $sql = "UPDATE contratos 
                SET faturamento_realizado = :faturamento_realizado,
                    saldo_faturar = :saldo_faturar,
                    atualizado_em = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':faturamento_realizado' => $faturamento['faturamento_realizado'],
            ':saldo_faturar' => $faturamento['saldo_faturar'],
            ':id' => $contratoId
        ]);
    }
    
    /**
     * Gerar relatório financeiro completo do contrato
     * 
     * @param int $contratoId ID do contrato
     * @return array Relatório completo
     */
    public function gerarRelatorioCompleto($contratoId)
    {
        return [
            'faturamento' => $this->getFaturamento($contratoId),
            'historico_mensal' => $this->getHistoricoMensal($contratoId, 12),
            'inadimplencia' => $this->analisarInadimplencia($contratoId),
            'projecao_receita' => $this->projetarReceita($contratoId, 12),
            'faturas_pendentes' => $this->getFaturasPendentes($contratoId),
            'gerado_em' => date('Y-m-d H:i:s')
        ];
    }
}
