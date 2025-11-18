<?php
namespace App\Models;

use PDO;

/**
 * Class LancamentoFinanceiro
 * 
 * Model para gerenciar lançamentos financeiros com sistema de partidas dobradas,
 * controle de débito/crédito, rastreabilidade e relatórios contábeis.
 * 
 * Funcionalidades:
 * - Sistema de partidas dobradas (todo débito tem um crédito correspondente)
 * - Lançamentos contábeis completos
 * - Integração com plano de contas (categorias financeiras)
 * - Centros de custo
 * - Rastreabilidade de origem (pagamento, conta, nota fiscal, etc)
 * - Validação de saldo
 * - Conciliação bancária
 * - Relatórios contábeis (balancete, DRE, balanço)
 * - Lançamentos compostos (múltiplos débitos/créditos)
 * - Estorno de lançamentos
 * 
 * @package App\Models
 */
class LancamentoFinanceiro
{
    private $db;
    private $table = 'lancamentos_financeiros';
    
    // Tipos de lançamento
    const TIPO_ENTRADA = 'entrada';
    const TIPO_SAIDA = 'saida';
    const TIPO_TRANSFERENCIA = 'transferencia';
    
    // Natureza do lançamento
    const NATUREZA_DEBITO = 'debito';
    const NATUREZA_CREDITO = 'credito';
    
    // Status do lançamento
    const STATUS_RASCUNHO = 'rascunho';
    const STATUS_CONFIRMADO = 'confirmado';
    const STATUS_ESTORNADO = 'estornado';
    
    /**
     * Construtor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Cria um lançamento financeiro simples
     * 
     * @param array $data Dados do lançamento
     * @return int|false ID do lançamento criado ou false em caso de erro
     */
    public function create(array $data)
    {
        try {
            $this->db->beginTransaction();
            
            // Validação de dados obrigatórios
            $this->validarDados($data);
            
            // Define valores padrão
            $data['status'] = $data['status'] ?? self::STATUS_CONFIRMADO;
            $data['data_lancamento'] = $data['data_lancamento'] ?? date('Y-m-d');
            
            $sql = "INSERT INTO {$this->table} (
                tipo, natureza, valor, data_lancamento, data_competencia,
                categoria_financeira_id, centro_custo_id, conta_bancaria_id,
                origem_tipo, origem_id, descricao, historico, documento,
                complemento, status, criado_por, criado_em
            ) VALUES (
                :tipo, :natureza, :valor, :data_lancamento, :data_competencia,
                :categoria_financeira_id, :centro_custo_id, :conta_bancaria_id,
                :origem_tipo, :origem_id, :descricao, :historico, :documento,
                :complemento, :status, :criado_por, NOW()
            )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'tipo' => $data['tipo'],
                'natureza' => $data['natureza'],
                'valor' => $data['valor'],
                'data_lancamento' => $data['data_lancamento'],
                'data_competencia' => $data['data_competencia'] ?? $data['data_lancamento'],
                'categoria_financeira_id' => $data['categoria_financeira_id'] ?? null,
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
                'origem_tipo' => $data['origem_tipo'] ?? null,
                'origem_id' => $data['origem_id'] ?? null,
                'descricao' => $data['descricao'],
                'historico' => $data['historico'] ?? '',
                'documento' => $data['documento'] ?? null,
                'complemento' => $data['complemento'] ?? null,
                'status' => $data['status'],
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            if (!$stmt->execute($params)) {
                throw new \Exception("Erro ao criar lançamento financeiro");
            }
            
            $lancamentoId = $this->db->lastInsertId();
            
            // Se for lançamento confirmado, cria contrapartida automaticamente
            if ($data['status'] === self::STATUS_CONFIRMADO && !empty($data['criar_contrapartida'])) {
                $this->criarContrapartida($lancamentoId, $data);
            }
            
            $this->db->commit();
            return $lancamentoId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar lançamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cria lançamento com partidas dobradas completas
     * 
     * @param array $data Dados do lançamento
     * @return array IDs dos lançamentos criados (débito e crédito)
     */
    public function createPartidasDobradas(array $data)
    {
        try {
            $this->db->beginTransaction();
            
            // Validação
            if (empty($data['valor']) || $data['valor'] <= 0) {
                throw new \Exception("Valor do lançamento é obrigatório e deve ser maior que zero");
            }
            
            if (empty($data['conta_debito_id']) || empty($data['conta_credito_id'])) {
                throw new \Exception("Contas de débito e crédito são obrigatórias");
            }
            
            // Lançamento a débito
            $lancamentoDebito = [
                'tipo' => $data['tipo'],
                'natureza' => self::NATUREZA_DEBITO,
                'valor' => $data['valor'],
                'data_lancamento' => $data['data_lancamento'] ?? date('Y-m-d'),
                'data_competencia' => $data['data_competencia'] ?? $data['data_lancamento'] ?? date('Y-m-d'),
                'categoria_financeira_id' => $data['conta_debito_id'],
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
                'origem_tipo' => $data['origem_tipo'] ?? null,
                'origem_id' => $data['origem_id'] ?? null,
                'descricao' => $data['descricao'],
                'historico' => $data['historico'] ?? '',
                'documento' => $data['documento'] ?? null,
                'status' => self::STATUS_CONFIRMADO,
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            $debitoId = $this->create($lancamentoDebito);
            
            // Lançamento a crédito (contrapartida)
            $lancamentoCredito = [
                'tipo' => $data['tipo'],
                'natureza' => self::NATUREZA_CREDITO,
                'valor' => $data['valor'],
                'data_lancamento' => $data['data_lancamento'] ?? date('Y-m-d'),
                'data_competencia' => $data['data_competencia'] ?? $data['data_lancamento'] ?? date('Y-m-d'),
                'categoria_financeira_id' => $data['conta_credito_id'],
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
                'origem_tipo' => $data['origem_tipo'] ?? null,
                'origem_id' => $data['origem_id'] ?? null,
                'descricao' => $data['descricao'],
                'historico' => $data['historico'] ?? '',
                'documento' => $data['documento'] ?? null,
                'complemento' => json_encode(['lancamento_contrapartida_id' => $debitoId]),
                'status' => self::STATUS_CONFIRMADO,
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            $creditoId = $this->create($lancamentoCredito);
            
            // Vincula os lançamentos
            $this->vincularLancamentos($debitoId, $creditoId);
            
            $this->db->commit();
            
            return [
                'debito_id' => $debitoId,
                'credito_id' => $creditoId
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar partidas dobradas: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cria lançamento composto (múltiplos débitos e/ou créditos)
     * 
     * @param array $data Dados do lançamento composto
     * @return array IDs dos lançamentos criados
     */
    public function createComposto(array $data)
    {
        try {
            $this->db->beginTransaction();
            
            // Validação
            if (empty($data['lancamentos_debito']) || empty($data['lancamentos_credito'])) {
                throw new \Exception("Lançamentos de débito e crédito são obrigatórios");
            }
            
            // Calcula totais
            $totalDebito = 0;
            foreach ($data['lancamentos_debito'] as $debito) {
                $totalDebito += $debito['valor'];
            }
            
            $totalCredito = 0;
            foreach ($data['lancamentos_credito'] as $credito) {
                $totalCredito += $credito['valor'];
            }
            
            // Valida partidas dobradas
            if (abs($totalDebito - $totalCredito) > 0.01) {
                throw new \Exception("Total de débitos ({$totalDebito}) deve ser igual ao total de créditos ({$totalCredito})");
            }
            
            $lancamentosIds = [
                'debitos' => [],
                'creditos' => []
            ];
            
            // Cria lançamentos de débito
            foreach ($data['lancamentos_debito'] as $debito) {
                $lancamentoData = array_merge($debito, [
                    'tipo' => $data['tipo'] ?? self::TIPO_SAIDA,
                    'natureza' => self::NATUREZA_DEBITO,
                    'data_lancamento' => $data['data_lancamento'] ?? date('Y-m-d'),
                    'data_competencia' => $data['data_competencia'] ?? $data['data_lancamento'] ?? date('Y-m-d'),
                    'origem_tipo' => $data['origem_tipo'] ?? null,
                    'origem_id' => $data['origem_id'] ?? null,
                    'documento' => $data['documento'] ?? null,
                    'status' => self::STATUS_CONFIRMADO
                ]);
                
                $lancamentosIds['debitos'][] = $this->create($lancamentoData);
            }
            
            // Cria lançamentos de crédito
            foreach ($data['lancamentos_credito'] as $credito) {
                $lancamentoData = array_merge($credito, [
                    'tipo' => $data['tipo'] ?? self::TIPO_ENTRADA,
                    'natureza' => self::NATUREZA_CREDITO,
                    'data_lancamento' => $data['data_lancamento'] ?? date('Y-m-d'),
                    'data_competencia' => $data['data_competencia'] ?? $data['data_lancamento'] ?? date('Y-m-d'),
                    'origem_tipo' => $data['origem_tipo'] ?? null,
                    'origem_id' => $data['origem_id'] ?? null,
                    'documento' => $data['documento'] ?? null,
                    'status' => self::STATUS_CONFIRMADO
                ]);
                
                $lancamentosIds['creditos'][] = $this->create($lancamentoData);
            }
            
            $this->db->commit();
            return $lancamentosIds;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar lançamento composto: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca lançamento por ID
     * 
     * @param int $id ID do lançamento
     * @return array|false Dados do lançamento ou false se não encontrado
     */
    public function findById($id)
    {
        $sql = "SELECT l.*,
                cf.nome as categoria_nome,
                cf.codigo as categoria_codigo,
                cc.nome as centro_custo_nome,
                cb.numero_conta as conta_bancaria_numero,
                cb.banco_nome,
                u.nome as criador_nome
                FROM {$this->table} l
                LEFT JOIN categorias_financeiras cf ON l.categoria_financeira_id = cf.id
                LEFT JOIN centros_custo cc ON l.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON l.conta_bancaria_id = cb.id
                LEFT JOIN usuarios u ON l.criado_por = u.id
                WHERE l.id = :id AND l.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista lançamentos com filtros e paginação
     * 
     * @param array $filtros Filtros a aplicar
     * @param int $page Página atual
     * @param int $limit Itens por página
     * @return array Array com 'data' e 'total'
     */
    public function all(array $filtros = [], $page = 1, $limit = 50)
    {
        $where = ["l.ativo = TRUE"];
        $params = [];
        
        // Filtro por tipo
        if (!empty($filtros['tipo'])) {
            $where[] = "l.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        
        // Filtro por natureza
        if (!empty($filtros['natureza'])) {
            $where[] = "l.natureza = :natureza";
            $params['natureza'] = $filtros['natureza'];
        }
        
        // Filtro por status
        if (!empty($filtros['status'])) {
            $where[] = "l.status = :status";
            $params['status'] = $filtros['status'];
        }
        
        // Filtro por categoria
        if (!empty($filtros['categoria_financeira_id'])) {
            $where[] = "l.categoria_financeira_id = :categoria_financeira_id";
            $params['categoria_financeira_id'] = $filtros['categoria_financeira_id'];
        }
        
        // Filtro por centro de custo
        if (!empty($filtros['centro_custo_id'])) {
            $where[] = "l.centro_custo_id = :centro_custo_id";
            $params['centro_custo_id'] = $filtros['centro_custo_id'];
        }
        
        // Filtro por conta bancária
        if (!empty($filtros['conta_bancaria_id'])) {
            $where[] = "l.conta_bancaria_id = :conta_bancaria_id";
            $params['conta_bancaria_id'] = $filtros['conta_bancaria_id'];
        }
        
        // Filtro por data de lançamento
        if (!empty($filtros['data_lancamento_inicio'])) {
            $where[] = "l.data_lancamento >= :data_lancamento_inicio";
            $params['data_lancamento_inicio'] = $filtros['data_lancamento_inicio'];
        }
        
        if (!empty($filtros['data_lancamento_fim'])) {
            $where[] = "l.data_lancamento <= :data_lancamento_fim";
            $params['data_lancamento_fim'] = $filtros['data_lancamento_fim'];
        }
        
        // Filtro por data de competência
        if (!empty($filtros['data_competencia_inicio'])) {
            $where[] = "l.data_competencia >= :data_competencia_inicio";
            $params['data_competencia_inicio'] = $filtros['data_competencia_inicio'];
        }
        
        if (!empty($filtros['data_competencia_fim'])) {
            $where[] = "l.data_competencia <= :data_competencia_fim";
            $params['data_competencia_fim'] = $filtros['data_competencia_fim'];
        }
        
        // Filtro por origem
        if (!empty($filtros['origem_tipo'])) {
            $where[] = "l.origem_tipo = :origem_tipo";
            $params['origem_tipo'] = $filtros['origem_tipo'];
            
            if (!empty($filtros['origem_id'])) {
                $where[] = "l.origem_id = :origem_id";
                $params['origem_id'] = $filtros['origem_id'];
            }
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Query de contagem
        $sqlCount = "SELECT COUNT(*) as total
                    FROM {$this->table} l
                    WHERE {$whereClause}";
        
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query de dados
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT l.*,
                cf.nome as categoria_nome,
                cf.codigo as categoria_codigo,
                cc.nome as centro_custo_nome,
                cb.numero_conta as conta_bancaria_numero
                FROM {$this->table} l
                LEFT JOIN categorias_financeiras cf ON l.categoria_financeira_id = cf.id
                LEFT JOIN centros_custo cc ON l.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON l.conta_bancaria_id = cb.id
                WHERE {$whereClause}
                ORDER BY l.data_lancamento DESC, l.criado_em DESC
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
        $lancamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $lancamentos,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Atualiza um lançamento
     * 
     * @param int $id ID do lançamento
     * @param array $data Dados a atualizar
     * @return bool Sucesso da operação
     */
    public function update($id, array $data)
    {
        try {
            // Busca lançamento atual
            $lancamentoAtual = $this->findById($id);
            if (!$lancamentoAtual) {
                throw new \Exception("Lançamento não encontrado");
            }
            
            // Não permite alterar lançamento confirmado
            if ($lancamentoAtual['status'] === self::STATUS_CONFIRMADO) {
                throw new \Exception("Lançamento confirmado não pode ser alterado. Use estorno.");
            }
            
            // Monta SQL dinamicamente apenas com campos fornecidos
            $campos = [];
            $params = ['id' => $id];
            
            $camposPermitidos = [
                'tipo', 'natureza', 'valor', 'data_lancamento', 'data_competencia',
                'categoria_financeira_id', 'centro_custo_id', 'conta_bancaria_id',
                'descricao', 'historico', 'documento', 'complemento'
            ];
            
            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $data)) {
                    $campos[] = "{$campo} = :{$campo}";
                    $params[$campo] = $data[$campo];
                }
            }
            
            if (empty($campos)) {
                return true; // Nada para atualizar
            }
            
            // Adiciona campos de auditoria
            $campos[] = "atualizado_por = :atualizado_por";
            $campos[] = "atualizado_em = NOW()";
            $params['atualizado_por'] = $data['atualizado_por'] ?? $_SESSION['user_id'] ?? null;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar lançamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Confirma um lançamento em rascunho
     * 
     * @param int $id ID do lançamento
     * @return bool Sucesso da operação
     */
    public function confirmar($id)
    {
        try {
            $lancamento = $this->findById($id);
            if (!$lancamento) {
                throw new \Exception("Lançamento não encontrado");
            }
            
            if ($lancamento['status'] !== self::STATUS_RASCUNHO) {
                throw new \Exception("Apenas lançamentos em rascunho podem ser confirmados");
            }
            
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    data_confirmacao = NOW(),
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id' => $id,
                'status' => self::STATUS_CONFIRMADO,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao confirmar lançamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Estorna um lançamento
     * 
     * @param int $id ID do lançamento
     * @param string $motivo Motivo do estorno
     * @return int ID do lançamento de estorno criado
     */
    public function estornar($id, $motivo)
    {
        try {
            $this->db->beginTransaction();
            
            $lancamento = $this->findById($id);
            if (!$lancamento) {
                throw new \Exception("Lançamento não encontrado");
            }
            
            if ($lancamento['status'] !== self::STATUS_CONFIRMADO) {
                throw new \Exception("Apenas lançamentos confirmados podem ser estornados");
            }
            
            if ($lancamento['status'] === self::STATUS_ESTORNADO) {
                throw new \Exception("Lançamento já está estornado");
            }
            
            // Marca lançamento original como estornado
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    data_estorno = NOW(),
                    motivo_estorno = :motivo_estorno,
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt->execute([
                'id' => $id,
                'status' => self::STATUS_ESTORNADO,
                'motivo_estorno' => $motivo,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ])) {
                throw new \Exception("Erro ao estornar lançamento");
            }
            
            // Cria lançamento de estorno (inverso)
            $lancamentoEstorno = [
                'tipo' => $lancamento['tipo'],
                'natureza' => ($lancamento['natureza'] === self::NATUREZA_DEBITO) ? 
                             self::NATUREZA_CREDITO : self::NATUREZA_DEBITO,
                'valor' => $lancamento['valor'],
                'data_lancamento' => date('Y-m-d'),
                'data_competencia' => date('Y-m-d'),
                'categoria_financeira_id' => $lancamento['categoria_financeira_id'],
                'centro_custo_id' => $lancamento['centro_custo_id'],
                'conta_bancaria_id' => $lancamento['conta_bancaria_id'],
                'origem_tipo' => 'estorno_lancamento',
                'origem_id' => $id,
                'descricao' => "ESTORNO: " . $lancamento['descricao'],
                'historico' => "Estorno do lançamento #{$id}. Motivo: {$motivo}",
                'documento' => $lancamento['documento'],
                'status' => self::STATUS_CONFIRMADO,
                'criado_por' => $_SESSION['user_id'] ?? null
            ];
            
            $estornoId = $this->create($lancamentoEstorno);
            
            $this->db->commit();
            return $estornoId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao estornar lançamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Soft delete de um lançamento
     * 
     * @param int $id ID do lançamento
     * @return bool Sucesso da operação
     */
    public function delete($id)
    {
        try {
            $lancamento = $this->findById($id);
            if (!$lancamento) {
                throw new \Exception("Lançamento não encontrado");
            }
            
            // Não permite excluir lançamento confirmado
            if ($lancamento['status'] === self::STATUS_CONFIRMADO) {
                throw new \Exception("Lançamento confirmado não pode ser excluído. Use estorno.");
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
            error_log("Erro ao excluir lançamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Valida dados obrigatórios
     * 
     * @param array $data Dados a validar
     * @throws \Exception Se validação falhar
     */
    private function validarDados(array $data)
    {
        if (empty($data['tipo'])) {
            throw new \Exception("Tipo do lançamento é obrigatório");
        }
        
        if (empty($data['natureza'])) {
            throw new \Exception("Natureza do lançamento (débito/crédito) é obrigatória");
        }
        
        if (empty($data['valor']) || $data['valor'] <= 0) {
            throw new \Exception("Valor do lançamento é obrigatório e deve ser maior que zero");
        }
        
        if (empty($data['descricao'])) {
            throw new \Exception("Descrição do lançamento é obrigatória");
        }
        
        // Valida tipo
        $tiposValidos = [self::TIPO_ENTRADA, self::TIPO_SAIDA, self::TIPO_TRANSFERENCIA];
        if (!in_array($data['tipo'], $tiposValidos)) {
            throw new \Exception("Tipo de lançamento inválido");
        }
        
        // Valida natureza
        $naturezasValidas = [self::NATUREZA_DEBITO, self::NATUREZA_CREDITO];
        if (!in_array($data['natureza'], $naturezasValidas)) {
            throw new \Exception("Natureza de lançamento inválida");
        }
    }
    
    /**
     * Cria contrapartida de um lançamento
     * 
     * @param int $lancamentoId ID do lançamento original
     * @param array $data Dados do lançamento original
     * @return int ID da contrapartida criada
     */
    private function criarContrapartida($lancamentoId, array $data)
    {
        if (empty($data['conta_contrapartida_id'])) {
            return false;
        }
        
        $contrapartida = [
            'tipo' => $data['tipo'],
            'natureza' => ($data['natureza'] === self::NATUREZA_DEBITO) ? 
                         self::NATUREZA_CREDITO : self::NATUREZA_DEBITO,
            'valor' => $data['valor'],
            'data_lancamento' => $data['data_lancamento'],
            'data_competencia' => $data['data_competencia'] ?? $data['data_lancamento'],
            'categoria_financeira_id' => $data['conta_contrapartida_id'],
            'centro_custo_id' => $data['centro_custo_id'] ?? null,
            'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
            'origem_tipo' => $data['origem_tipo'] ?? null,
            'origem_id' => $data['origem_id'] ?? null,
            'descricao' => $data['descricao'],
            'historico' => $data['historico'] ?? '',
            'documento' => $data['documento'] ?? null,
            'complemento' => json_encode(['lancamento_origem_id' => $lancamentoId]),
            'status' => self::STATUS_CONFIRMADO,
            'criar_contrapartida' => false // Evita loop infinito
        ];
        
        return $this->create($contrapartida);
    }
    
    /**
     * Vincula dois lançamentos como contrapartida
     * 
     * @param int $debitoId ID do lançamento de débito
     * @param int $creditoId ID do lançamento de crédito
     * @return void
     */
    private function vincularLancamentos($debitoId, $creditoId)
    {
        // Atualiza complemento dos dois lançamentos
        $sql = "UPDATE {$this->table} 
                SET complemento = :complemento 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        // Vincula débito ao crédito
        $stmt->execute([
            'id' => $debitoId,
            'complemento' => json_encode(['lancamento_contrapartida_id' => $creditoId])
        ]);
        
        // Vincula crédito ao débito
        $stmt->execute([
            'id' => $creditoId,
            'complemento' => json_encode(['lancamento_contrapartida_id' => $debitoId])
        ]);
    }
    
    /**
     * Obtém balancete (saldos por conta)
     * 
     * @param array $filtros Filtros opcionais
     * @return array Balancete
     */
    public function getBalancete(array $filtros = [])
    {
        $where = ["l.ativo = TRUE", "l.status = 'confirmado'"];
        $params = [];
        
        // Aplica filtros de período
        if (!empty($filtros['data_inicio'])) {
            $where[] = "l.data_competencia >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "l.data_competencia <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                cf.id as categoria_id,
                cf.codigo as categoria_codigo,
                cf.nome as categoria_nome,
                cf.tipo as categoria_tipo,
                SUM(CASE WHEN l.natureza = 'debito' THEN l.valor ELSE 0 END) as total_debito,
                SUM(CASE WHEN l.natureza = 'credito' THEN l.valor ELSE 0 END) as total_credito,
                (SUM(CASE WHEN l.natureza = 'debito' THEN l.valor ELSE 0 END) - 
                 SUM(CASE WHEN l.natureza = 'credito' THEN l.valor ELSE 0 END)) as saldo
                FROM {$this->table} l
                INNER JOIN categorias_financeiras cf ON l.categoria_financeira_id = cf.id
                WHERE {$whereClause}
                GROUP BY cf.id, cf.codigo, cf.nome, cf.tipo
                ORDER BY cf.codigo";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém DRE (Demonstrativo de Resultado do Exercício)
     * 
     * @param array $filtros Filtros de período
     * @return array DRE estruturado
     */
    public function getDRE(array $filtros = [])
    {
        $where = ["l.ativo = TRUE", "l.status = 'confirmado'"];
        $params = [];
        
        if (!empty($filtros['data_inicio'])) {
            $where[] = "l.data_competencia >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "l.data_competencia <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Receitas
        $sqlReceitas = "SELECT 
                       SUM(l.valor) as total
                       FROM {$this->table} l
                       INNER JOIN categorias_financeiras cf ON l.categoria_financeira_id = cf.id
                       WHERE {$whereClause}
                       AND cf.tipo = 'receita'
                       AND l.natureza = 'credito'";
        
        // Despesas
        $sqlDespesas = "SELECT 
                       SUM(l.valor) as total
                       FROM {$this->table} l
                       INNER JOIN categorias_financeiras cf ON l.categoria_financeira_id = cf.id
                       WHERE {$whereClause}
                       AND cf.tipo = 'despesa'
                       AND l.natureza = 'debito'";
        
        $stmt = $this->db->prepare($sqlReceitas);
        $stmt->execute($params);
        $receitas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        $stmt = $this->db->prepare($sqlDespesas);
        $stmt->execute($params);
        $despesas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'resultado' => $receitas - $despesas
        ];
    }
    
    /**
     * Obtém fluxo de caixa por período
     * 
     * @param array $filtros Filtros de período
     * @return array Fluxo de caixa
     */
    public function getFluxoCaixa(array $filtros = [])
    {
        $where = ["l.ativo = TRUE", "l.status = 'confirmado'"];
        $params = [];
        
        if (!empty($filtros['data_inicio'])) {
            $where[] = "l.data_lancamento >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "l.data_lancamento <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                l.data_lancamento,
                SUM(CASE WHEN l.tipo = 'entrada' THEN l.valor ELSE 0 END) as entradas,
                SUM(CASE WHEN l.tipo = 'saida' THEN l.valor ELSE 0 END) as saidas,
                (SUM(CASE WHEN l.tipo = 'entrada' THEN l.valor ELSE 0 END) -
                 SUM(CASE WHEN l.tipo = 'saida' THEN l.valor ELSE 0 END)) as saldo_dia
                FROM {$this->table} l
                WHERE {$whereClause}
                GROUP BY l.data_lancamento
                ORDER BY l.data_lancamento";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém estatísticas de lançamentos
     * 
     * @param array $filtros Filtros opcionais
     * @return array Estatísticas
     */
    public function getEstatisticas(array $filtros = [])
    {
        $where = ["ativo = TRUE", "status = 'confirmado'"];
        $params = [];
        
        if (!empty($filtros['data_inicio'])) {
            $where[] = "data_lancamento >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "data_lancamento <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                COUNT(*) as total_lancamentos,
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as total_entradas,
                SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as total_saidas,
                SUM(CASE WHEN natureza = 'debito' THEN valor ELSE 0 END) as total_debitos,
                SUM(CASE WHEN natureza = 'credito' THEN valor ELSE 0 END) as total_creditos,
                (SUM(CASE WHEN natureza = 'debito' THEN valor ELSE 0 END) -
                 SUM(CASE WHEN natureza = 'credito' THEN valor ELSE 0 END)) as diferenca
                FROM {$this->table}
                WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
