<?php
namespace App\Models;

use PDO;

/**
 * Class Pagamento
 * 
 * Model para gerenciar pagamentos e recebimentos com suporte a múltiplas formas
 * de pagamento, parcelamento, e integração com contas a pagar/receber.
 * 
 * Funcionalidades:
 * - Processamento de pagamentos múltiplas formas (dinheiro, cartão, boleto, PIX, etc)
 * - Parcelamento de pagamentos com juros
 * - Estorno de pagamentos
 * - Integração com contas a pagar/receber
 * - Conciliação bancária
 * - Controle de comprovantes
 * - Registro de taxas e descontos
 * - Auditoria completa
 * 
 * @package App\Models
 */
class Pagamento
{
    private $db;
    private $table = 'pagamentos';
    
    // Formas de pagamento válidas
    const FORMA_DINHEIRO = 'dinheiro';
    const FORMA_DEBITO = 'debito';
    const FORMA_CREDITO = 'credito';
    const FORMA_PIX = 'pix';
    const FORMA_BOLETO = 'boleto';
    const FORMA_TRANSFERENCIA = 'transferencia';
    const FORMA_CHEQUE = 'cheque';
    const FORMA_DEPOSITO = 'deposito';
    
    // Status do pagamento
    const STATUS_PENDENTE = 'pendente';
    const STATUS_PROCESSADO = 'processado';
    const STATUS_CONFIRMADO = 'confirmado';
    const STATUS_ESTORNADO = 'estornado';
    const STATUS_CANCELADO = 'cancelado';
    
    // Tipos de origem
    const ORIGEM_CONTA_PAGAR = 'conta_pagar';
    const ORIGEM_CONTA_RECEBER = 'conta_receber';
    const ORIGEM_LANCAMENTO = 'lancamento';
    
    /**
     * Construtor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Processa um pagamento único
     * 
     * @param array $data Dados do pagamento
     * @return int|false ID do pagamento criado ou false em caso de erro
     */
    public function processar(array $data)
    {
        try {
            $this->db->beginTransaction();
            
            // Validação de dados obrigatórios
            $this->validarDadosPagamento($data);
            
            // Busca origem do pagamento
            $origem = $this->buscarOrigem($data['origem_tipo'], $data['origem_id']);
            if (!$origem) {
                throw new \Exception("Origem do pagamento não encontrada");
            }
            
            // Valida valor do pagamento
            if ($data['valor'] > $origem['valor_pendente']) {
                throw new \Exception("Valor do pagamento excede o valor pendente");
            }
            
            // Define valores padrão
            $data['status'] = $data['status'] ?? self::STATUS_PROCESSADO;
            $data['data_pagamento'] = $data['data_pagamento'] ?? date('Y-m-d');
            $data['data_processamento'] = date('Y-m-d H:i:s');
            
            // Calcula valor líquido (desconta taxas)
            $valorTaxa = $data['valor_taxa'] ?? 0.00;
            $valorDesconto = $data['valor_desconto'] ?? 0.00;
            $valorLiquido = $data['valor'] - $valorTaxa + $valorDesconto;
            
            $sql = "INSERT INTO {$this->table} (
                origem_tipo, origem_id, valor, valor_taxa, valor_desconto, valor_liquido,
                data_pagamento, data_processamento, forma_pagamento, banco_id,
                conta_bancaria_id, comprovante_url, numero_documento, numero_autorizacao,
                observacoes, status, criado_por, criado_em
            ) VALUES (
                :origem_tipo, :origem_id, :valor, :valor_taxa, :valor_desconto, :valor_liquido,
                :data_pagamento, :data_processamento, :forma_pagamento, :banco_id,
                :conta_bancaria_id, :comprovante_url, :numero_documento, :numero_autorizacao,
                :observacoes, :status, :criado_por, NOW()
            )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'origem_tipo' => $data['origem_tipo'],
                'origem_id' => $data['origem_id'],
                'valor' => $data['valor'],
                'valor_taxa' => $valorTaxa,
                'valor_desconto' => $valorDesconto,
                'valor_liquido' => $valorLiquido,
                'data_pagamento' => $data['data_pagamento'],
                'data_processamento' => $data['data_processamento'],
                'forma_pagamento' => $data['forma_pagamento'],
                'banco_id' => $data['banco_id'] ?? null,
                'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
                'comprovante_url' => $data['comprovante_url'] ?? null,
                'numero_documento' => $data['numero_documento'] ?? null,
                'numero_autorizacao' => $data['numero_autorizacao'] ?? null,
                'observacoes' => $data['observacoes'] ?? '',
                'status' => $data['status'],
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            if (!$stmt->execute($params)) {
                throw new \Exception("Erro ao registrar pagamento");
            }
            
            $pagamentoId = $this->db->lastInsertId();
            
            // Atualiza origem (conta a pagar/receber)
            $this->atualizarOrigem($data['origem_tipo'], $data['origem_id'], $data['valor']);
            
            // Registra lançamento financeiro (partidas dobradas)
            if (!empty($data['registrar_lancamento'])) {
                $this->registrarLancamentoFinanceiro($pagamentoId, $data);
            }
            
            $this->db->commit();
            return $pagamentoId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao processar pagamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Processa pagamento parcelado
     * 
     * @param array $data Dados do pagamento
     * @param int $numeroParcelas Número de parcelas
     * @param float $taxaJuros Taxa de juros mensal (opcional)
     * @return array IDs dos pagamentos criados
     */
    public function processarParcelado(array $data, $numeroParcelas, $taxaJuros = 0.00)
    {
        try {
            $this->db->beginTransaction();
            
            // Validação
            if ($numeroParcelas < 2) {
                throw new \Exception("Número de parcelas deve ser maior que 1");
            }
            
            if ($numeroParcelas > 48) {
                throw new \Exception("Número máximo de parcelas é 48");
            }
            
            // Calcula valor das parcelas
            $valorTotal = $data['valor'];
            
            if ($taxaJuros > 0) {
                // Aplica juros compostos
                $coeficiente = pow(1 + ($taxaJuros / 100), $numeroParcelas);
                $valorParcela = ($valorTotal * $coeficiente * ($taxaJuros / 100)) / 
                               ($coeficiente - 1);
            } else {
                // Sem juros
                $valorParcela = $valorTotal / $numeroParcelas;
            }
            
            $valorParcela = round($valorParcela, 2);
            
            // Ajusta última parcela para compensar arredondamentos
            $somaParcelasAnteriores = $valorParcela * ($numeroParcelas - 1);
            $valorUltimaParcela = $valorTotal - $somaParcelasAnteriores;
            
            if ($taxaJuros > 0) {
                $valorTotalComJuros = $valorParcela * $numeroParcelas;
                $valorUltimaParcela = $valorTotalComJuros - $somaParcelasAnteriores;
            }
            
            $pagamentosIds = [];
            $dataBase = new \DateTime($data['data_pagamento'] ?? 'now');
            
            // Cria parcelas
            for ($i = 1; $i <= $numeroParcelas; $i++) {
                $dataParcela = clone $dataBase;
                $dataParcela->modify('+' . ($i - 1) . ' month');
                
                $valorAtual = ($i == $numeroParcelas) ? $valorUltimaParcela : $valorParcela;
                
                $dadosParcela = array_merge($data, [
                    'valor' => $valorAtual,
                    'data_pagamento' => $dataParcela->format('Y-m-d'),
                    'observacoes' => ($data['observacoes'] ?? '') . 
                                    " - Parcela {$i}/{$numeroParcelas}",
                    'numero_parcela' => $i,
                    'total_parcelas' => $numeroParcelas,
                    'taxa_juros' => $taxaJuros,
                    'status' => ($i == 1) ? self::STATUS_PROCESSADO : self::STATUS_PENDENTE
                ]);
                
                // Remove registrar_lancamento para não duplicar
                unset($dadosParcela['registrar_lancamento']);
                
                $pagamentoId = $this->processar($dadosParcela);
                $pagamentosIds[] = $pagamentoId;
            }
            
            // Registra lançamento financeiro apenas uma vez para o total
            if (!empty($data['registrar_lancamento'])) {
                $this->registrarLancamentoFinanceiro($pagamentosIds[0], $data);
            }
            
            $this->db->commit();
            return $pagamentosIds;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao processar pagamento parcelado: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca pagamento por ID
     * 
     * @param int $id ID do pagamento
     * @return array|false Dados do pagamento ou false se não encontrado
     */
    public function findById($id)
    {
        $sql = "SELECT p.*,
                CASE 
                    WHEN p.origem_tipo = 'conta_pagar' THEN cp.numero
                    WHEN p.origem_tipo = 'conta_receber' THEN cr.numero
                    ELSE NULL
                END as origem_numero,
                CASE 
                    WHEN p.origem_tipo = 'conta_pagar' THEN cp.descricao
                    WHEN p.origem_tipo = 'conta_receber' THEN cr.descricao
                    ELSE NULL
                END as origem_descricao,
                b.nome as banco_nome,
                cb.numero_conta as conta_bancaria_numero,
                u.nome as criador_nome
                FROM {$this->table} p
                LEFT JOIN contas_pagar cp ON p.origem_tipo = 'conta_pagar' AND p.origem_id = cp.id
                LEFT JOIN contas_receber cr ON p.origem_tipo = 'conta_receber' AND p.origem_id = cr.id
                LEFT JOIN bancos b ON p.banco_id = b.id
                LEFT JOIN contas_bancarias cb ON p.conta_bancaria_id = cb.id
                LEFT JOIN usuarios u ON p.criado_por = u.id
                WHERE p.id = :id AND p.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista pagamentos com filtros e paginação
     * 
     * @param array $filtros Filtros a aplicar
     * @param int $page Página atual
     * @param int $limit Itens por página
     * @return array Array com 'data' e 'total'
     */
    public function all(array $filtros = [], $page = 1, $limit = 50)
    {
        $where = ["p.ativo = TRUE"];
        $params = [];
        
        // Filtro por tipo de origem
        if (!empty($filtros['origem_tipo'])) {
            $where[] = "p.origem_tipo = :origem_tipo";
            $params['origem_tipo'] = $filtros['origem_tipo'];
        }
        
        // Filtro por ID da origem
        if (!empty($filtros['origem_id'])) {
            $where[] = "p.origem_id = :origem_id";
            $params['origem_id'] = $filtros['origem_id'];
        }
        
        // Filtro por forma de pagamento
        if (!empty($filtros['forma_pagamento'])) {
            $where[] = "p.forma_pagamento = :forma_pagamento";
            $params['forma_pagamento'] = $filtros['forma_pagamento'];
        }
        
        // Filtro por status
        if (!empty($filtros['status'])) {
            $where[] = "p.status = :status";
            $params['status'] = $filtros['status'];
        }
        
        // Filtro por data de pagamento
        if (!empty($filtros['data_pagamento_inicio'])) {
            $where[] = "p.data_pagamento >= :data_pagamento_inicio";
            $params['data_pagamento_inicio'] = $filtros['data_pagamento_inicio'];
        }
        
        if (!empty($filtros['data_pagamento_fim'])) {
            $where[] = "p.data_pagamento <= :data_pagamento_fim";
            $params['data_pagamento_fim'] = $filtros['data_pagamento_fim'];
        }
        
        // Filtro por banco
        if (!empty($filtros['banco_id'])) {
            $where[] = "p.banco_id = :banco_id";
            $params['banco_id'] = $filtros['banco_id'];
        }
        
        // Filtro por conta bancária
        if (!empty($filtros['conta_bancaria_id'])) {
            $where[] = "p.conta_bancaria_id = :conta_bancaria_id";
            $params['conta_bancaria_id'] = $filtros['conta_bancaria_id'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Query de contagem
        $sqlCount = "SELECT COUNT(*) as total
                    FROM {$this->table} p
                    WHERE {$whereClause}";
        
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query de dados
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT p.*,
                CASE 
                    WHEN p.origem_tipo = 'conta_pagar' THEN cp.numero
                    WHEN p.origem_tipo = 'conta_receber' THEN cr.numero
                    ELSE NULL
                END as origem_numero,
                b.nome as banco_nome,
                cb.numero_conta as conta_bancaria_numero
                FROM {$this->table} p
                LEFT JOIN contas_pagar cp ON p.origem_tipo = 'conta_pagar' AND p.origem_id = cp.id
                LEFT JOIN contas_receber cr ON p.origem_tipo = 'conta_receber' AND p.origem_id = cr.id
                LEFT JOIN bancos b ON p.banco_id = b.id
                LEFT JOIN contas_bancarias cb ON p.conta_bancaria_id = cb.id
                WHERE {$whereClause}
                ORDER BY p.data_pagamento DESC, p.criado_em DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind dos parâmetros de filtro
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        // Bind dos parâmetros de paginação
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $pagamentos,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Confirma um pagamento pendente
     * 
     * @param int $id ID do pagamento
     * @param array $dadosConfirmacao Dados da confirmação
     * @return bool Sucesso da operação
     */
    public function confirmar($id, array $dadosConfirmacao = [])
    {
        try {
            $pagamento = $this->findById($id);
            if (!$pagamento) {
                throw new \Exception("Pagamento não encontrado");
            }
            
            if ($pagamento['status'] !== self::STATUS_PENDENTE) {
                throw new \Exception("Apenas pagamentos pendentes podem ser confirmados");
            }
            
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    data_confirmacao = :data_confirmacao,
                    numero_autorizacao = :numero_autorizacao,
                    observacoes = CONCAT(IFNULL(observacoes, ''), :obs_confirmacao),
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id' => $id,
                'status' => self::STATUS_CONFIRMADO,
                'data_confirmacao' => date('Y-m-d H:i:s'),
                'numero_autorizacao' => $dadosConfirmacao['numero_autorizacao'] ?? null,
                'obs_confirmacao' => "\nConfirmado em " . date('d/m/Y H:i:s') . 
                                    ($dadosConfirmacao['observacao'] ?? ''),
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao confirmar pagamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Estorna um pagamento
     * 
     * @param int $id ID do pagamento
     * @param string $motivo Motivo do estorno
     * @return bool Sucesso da operação
     */
    public function estornar($id, $motivo)
    {
        try {
            $this->db->beginTransaction();
            
            $pagamento = $this->findById($id);
            if (!$pagamento) {
                throw new \Exception("Pagamento não encontrado");
            }
            
            if ($pagamento['status'] === self::STATUS_ESTORNADO) {
                throw new \Exception("Pagamento já está estornado");
            }
            
            if (!in_array($pagamento['status'], [self::STATUS_PROCESSADO, self::STATUS_CONFIRMADO])) {
                throw new \Exception("Apenas pagamentos processados ou confirmados podem ser estornados");
            }
            
            // Atualiza status do pagamento
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    data_estorno = :data_estorno,
                    motivo_estorno = :motivo_estorno,
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt->execute([
                'id' => $id,
                'status' => self::STATUS_ESTORNADO,
                'data_estorno' => date('Y-m-d H:i:s'),
                'motivo_estorno' => $motivo,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ])) {
                throw new \Exception("Erro ao estornar pagamento");
            }
            
            // Reverte valor na origem (conta a pagar/receber)
            $this->reverterOrigem($pagamento['origem_tipo'], $pagamento['origem_id'], $pagamento['valor']);
            
            // Registra lançamento de estorno
            $this->registrarLancamentoEstorno($id, $pagamento);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao estornar pagamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cancela um pagamento pendente
     * 
     * @param int $id ID do pagamento
     * @param string $motivo Motivo do cancelamento
     * @return bool Sucesso da operação
     */
    public function cancelar($id, $motivo)
    {
        try {
            $pagamento = $this->findById($id);
            if (!$pagamento) {
                throw new \Exception("Pagamento não encontrado");
            }
            
            if ($pagamento['status'] !== self::STATUS_PENDENTE) {
                throw new \Exception("Apenas pagamentos pendentes podem ser cancelados");
            }
            
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    data_cancelamento = :data_cancelamento,
                    motivo_cancelamento = :motivo_cancelamento,
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id' => $id,
                'status' => self::STATUS_CANCELADO,
                'data_cancelamento' => date('Y-m-d H:i:s'),
                'motivo_cancelamento' => $motivo,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao cancelar pagamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Soft delete de um pagamento
     * 
     * @param int $id ID do pagamento
     * @return bool Sucesso da operação
     */
    public function delete($id)
    {
        try {
            $pagamento = $this->findById($id);
            if (!$pagamento) {
                throw new \Exception("Pagamento não encontrado");
            }
            
            // Não permite excluir pagamento processado ou confirmado
            if (in_array($pagamento['status'], [self::STATUS_PROCESSADO, self::STATUS_CONFIRMADO])) {
                throw new \Exception("Pagamento processado/confirmado não pode ser excluído. Use estorno.");
            }
            
            $sql = "UPDATE {$this->table} SET 
                    ativo = FALSE,
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id' => $id,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao excluir pagamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Valida dados obrigatórios do pagamento
     * 
     * @param array $data Dados a validar
     * @throws \Exception Se validação falhar
     */
    private function validarDadosPagamento(array $data)
    {
        if (empty($data['origem_tipo'])) {
            throw new \Exception("Tipo de origem é obrigatório");
        }
        
        if (empty($data['origem_id'])) {
            throw new \Exception("ID da origem é obrigatório");
        }
        
        if (empty($data['valor']) || $data['valor'] <= 0) {
            throw new \Exception("Valor do pagamento é obrigatório e deve ser maior que zero");
        }
        
        if (empty($data['forma_pagamento'])) {
            throw new \Exception("Forma de pagamento é obrigatória");
        }
        
        // Valida forma de pagamento
        $formasValidas = [
            self::FORMA_DINHEIRO, self::FORMA_DEBITO, self::FORMA_CREDITO,
            self::FORMA_PIX, self::FORMA_BOLETO, self::FORMA_TRANSFERENCIA,
            self::FORMA_CHEQUE, self::FORMA_DEPOSITO
        ];
        
        if (!in_array($data['forma_pagamento'], $formasValidas)) {
            throw new \Exception("Forma de pagamento inválida");
        }
        
        // Valida origem
        if (!in_array($data['origem_tipo'], [self::ORIGEM_CONTA_PAGAR, self::ORIGEM_CONTA_RECEBER, self::ORIGEM_LANCAMENTO])) {
            throw new \Exception("Tipo de origem inválido");
        }
    }
    
    /**
     * Busca dados da origem do pagamento
     * 
     * @param string $tipo Tipo da origem
     * @param int $id ID da origem
     * @return array|false Dados da origem ou false
     */
    private function buscarOrigem($tipo, $id)
    {
        switch ($tipo) {
            case self::ORIGEM_CONTA_PAGAR:
                $sql = "SELECT *, (valor_original - valor_pago) as valor_pendente 
                        FROM contas_pagar 
                        WHERE id = :id AND ativo = TRUE";
                break;
                
            case self::ORIGEM_CONTA_RECEBER:
                $sql = "SELECT *, (valor_original - valor_pago) as valor_pendente 
                        FROM contas_receber 
                        WHERE id = :id AND ativo = TRUE";
                break;
                
            default:
                return false;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualiza valor pago na origem
     * 
     * @param string $tipo Tipo da origem
     * @param int $id ID da origem
     * @param float $valor Valor do pagamento
     * @return void
     */
    private function atualizarOrigem($tipo, $id, $valor)
    {
        $table = ($tipo === self::ORIGEM_CONTA_PAGAR) ? 'contas_pagar' : 'contas_receber';
        
        $sql = "UPDATE {$table} 
                SET valor_pago = valor_pago + :valor,
                    atualizado_em = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'valor' => $valor
        ]);
    }
    
    /**
     * Reverte valor pago na origem (para estorno)
     * 
     * @param string $tipo Tipo da origem
     * @param int $id ID da origem
     * @param float $valor Valor a reverter
     * @return void
     */
    private function reverterOrigem($tipo, $id, $valor)
    {
        $table = ($tipo === self::ORIGEM_CONTA_PAGAR) ? 'contas_pagar' : 'contas_receber';
        
        $sql = "UPDATE {$table} 
                SET valor_pago = valor_pago - :valor,
                    atualizado_em = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'valor' => $valor
        ]);
    }
    
    /**
     * Registra lançamento financeiro (partidas dobradas)
     * 
     * @param int $pagamentoId ID do pagamento
     * @param array $data Dados do pagamento
     * @return void
     */
    private function registrarLancamentoFinanceiro($pagamentoId, array $data)
    {
        // Este método será implementado quando o model LancamentoFinanceiro existir
        // Por enquanto apenas registra a necessidade
        
        $lancamentoModel = new \App\Models\LancamentoFinanceiro();
        
        $tipoLancamento = ($data['origem_tipo'] === self::ORIGEM_CONTA_PAGAR) ? 'saida' : 'entrada';
        
        $lancamentoModel->create([
            'tipo' => $tipoLancamento,
            'valor' => $data['valor'],
            'data_lancamento' => $data['data_pagamento'] ?? date('Y-m-d'),
            'origem_tipo' => 'pagamento',
            'origem_id' => $pagamentoId,
            'categoria_financeira_id' => $data['categoria_financeira_id'] ?? null,
            'centro_custo_id' => $data['centro_custo_id'] ?? null,
            'descricao' => "Pagamento #{$pagamentoId} - " . ($data['observacoes'] ?? ''),
            'criado_por' => $_SESSION['user_id'] ?? null
        ]);
    }
    
    /**
     * Registra lançamento de estorno
     * 
     * @param int $pagamentoId ID do pagamento
     * @param array $pagamento Dados do pagamento
     * @return void
     */
    private function registrarLancamentoEstorno($pagamentoId, array $pagamento)
    {
        // Este método será implementado quando o model LancamentoFinanceiro existir
        
        $lancamentoModel = new \App\Models\LancamentoFinanceiro();
        
        $tipoLancamento = ($pagamento['origem_tipo'] === self::ORIGEM_CONTA_PAGAR) ? 'entrada' : 'saida';
        
        $lancamentoModel->create([
            'tipo' => $tipoLancamento,
            'valor' => $pagamento['valor'],
            'data_lancamento' => date('Y-m-d'),
            'origem_tipo' => 'estorno_pagamento',
            'origem_id' => $pagamentoId,
            'descricao' => "Estorno do pagamento #{$pagamentoId}",
            'criado_por' => $_SESSION['user_id'] ?? null
        ]);
    }
    
    /**
     * Obtém estatísticas de pagamentos
     * 
     * @param array $filtros Filtros opcionais
     * @return array Estatísticas
     */
    public function getEstatisticas(array $filtros = [])
    {
        $where = ["ativo = TRUE"];
        $params = [];
        
        // Aplica filtros se fornecidos
        if (!empty($filtros['data_inicio'])) {
            $where[] = "data_pagamento >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "data_pagamento <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['origem_tipo'])) {
            $where[] = "origem_tipo = :origem_tipo";
            $params['origem_tipo'] = $filtros['origem_tipo'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'processado' THEN 1 END) as processados,
                COUNT(CASE WHEN status = 'confirmado' THEN 1 END) as confirmados,
                COUNT(CASE WHEN status = 'estornado' THEN 1 END) as estornados,
                SUM(CASE WHEN status IN ('processado', 'confirmado') THEN valor ELSE 0 END) as valor_total,
                SUM(CASE WHEN status IN ('processado', 'confirmado') THEN valor_taxa ELSE 0 END) as total_taxas,
                SUM(CASE WHEN status IN ('processado', 'confirmado') THEN valor_liquido ELSE 0 END) as valor_liquido_total,
                COUNT(DISTINCT forma_pagamento) as formas_distintas
                FROM {$this->table}
                WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca pagamentos por forma de pagamento
     * 
     * @param array $filtros Filtros opcionais
     * @return array Estatísticas por forma de pagamento
     */
    public function getPorFormaPagamento(array $filtros = [])
    {
        $where = ["ativo = TRUE", "status IN ('processado', 'confirmado')"];
        $params = [];
        
        if (!empty($filtros['data_inicio'])) {
            $where[] = "data_pagamento >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "data_pagamento <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                forma_pagamento,
                COUNT(*) as quantidade,
                SUM(valor) as valor_total,
                SUM(valor_taxa) as total_taxas,
                AVG(valor) as valor_medio
                FROM {$this->table}
                WHERE {$whereClause}
                GROUP BY forma_pagamento
                ORDER BY valor_total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
