<?php
namespace App\Models;

use App\Database;
use PDO;

/**
 * Class ContaReceber
 * 
 * Model para gerenciar contas a receber com parcelamento, recorrência,
 * geração de boletos e controle de clientes.
 * 
 * Funcionalidades:
 * - Cadastro e gestão de contas a receber
 * - Parcelamento automático de vendas
 * - Contas recorrentes (assinaturas, mensalidades)
 * - Geração automática de boletos
 * - Cálculo de juros e multa por atraso
 * - Integração com clientes e projetos
 * - Vinculação com notas fiscais
 * - Histórico de recebimentos
 * - Controle de status (pendente, recebida parcialmente, recebida, vencida, cancelada)
 * - Relatórios de contas a receber
 * 
 * @package App\Models
 */
class ContaReceber
{
    private $db;
    private $table = 'contas_receber';
    
    // Status válidos
    const STATUS_PENDENTE = 'pendente';
    const STATUS_RECEBIDA_PARCIALMENTE = 'recebida_parcialmente';
    const STATUS_RECEBIDA = 'recebida';
    const STATUS_VENCIDA = 'vencida';
    const STATUS_CANCELADA = 'cancelada';
    
    /**
     * Construtor
     */
    public function __construct()
    {
        // SPRINT 73 FIX: Usar Database singleton ao invés de global $db
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria uma nova conta a receber
     * 
     * @param array $data Dados da conta
     * @return int|false ID da conta criada ou false em caso de erro
     */
    public function create(array $data)
    {
        try {
            // Validação de dados obrigatórios
            $this->validarDados($data);
            
            // Gera número da conta se não fornecido
            if (empty($data['numero'])) {
                $data['numero'] = $this->gerarNumero();
            }
            
            // Define valores padrão
            $data['status'] = self::STATUS_PENDENTE;
            $data['valor_pago'] = 0.00;
            $data['valor_pendente'] = $data['valor_original'];
            
            $sql = "INSERT INTO {$this->table} (
                numero, cliente_id, projeto_id, nota_fiscal_id, categoria_financeira_id,
                centro_custo_id, descricao, valor_original, valor_pago, valor_pendente,
                data_emissao, data_vencimento, data_pagamento, forma_pagamento,
                numero_documento, observacoes, gerar_boleto, boleto_id,
                recorrente, frequencia_recorrencia, dia_vencimento_recorrencia,
                parcela_numero, total_parcelas, status, criado_por, criado_em
            ) VALUES (
                :numero, :cliente_id, :projeto_id, :nota_fiscal_id, :categoria_financeira_id,
                :centro_custo_id, :descricao, :valor_original, :valor_pago, :valor_pendente,
                :data_emissao, :data_vencimento, :data_pagamento, :forma_pagamento,
                :numero_documento, :observacoes, :gerar_boleto, :boleto_id,
                :recorrente, :frequencia_recorrencia, :dia_vencimento_recorrencia,
                :parcela_numero, :total_parcelas, :status, :criado_por, NOW()
            )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'numero' => $data['numero'],
                'cliente_id' => $data['cliente_id'],
                'projeto_id' => $data['projeto_id'] ?? null,
                'nota_fiscal_id' => $data['nota_fiscal_id'] ?? null,
                'categoria_financeira_id' => $data['categoria_financeira_id'] ?? null,
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'descricao' => $data['descricao'],
                'valor_original' => $data['valor_original'],
                'valor_pago' => $data['valor_pago'],
                'valor_pendente' => $data['valor_pendente'],
                'data_emissao' => $data['data_emissao'] ?? date('Y-m-d'),
                'data_vencimento' => $data['data_vencimento'],
                'data_pagamento' => $data['data_pagamento'] ?? null,
                'forma_pagamento' => $data['forma_pagamento'] ?? null,
                'numero_documento' => $data['numero_documento'] ?? null,
                'observacoes' => $data['observacoes'] ?? '',
                'gerar_boleto' => $data['gerar_boleto'] ?? false,
                'boleto_id' => $data['boleto_id'] ?? null,
                'recorrente' => $data['recorrente'] ?? false,
                'frequencia_recorrencia' => $data['frequencia_recorrencia'] ?? null,
                'dia_vencimento_recorrencia' => $data['dia_vencimento_recorrencia'] ?? null,
                'parcela_numero' => $data['parcela_numero'] ?? null,
                'total_parcelas' => $data['total_parcelas'] ?? null,
                'status' => $data['status'],
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            if ($stmt->execute($params)) {
                $contaId = $this->db->lastInsertId();
                
                // Gera boleto se solicitado
                if (!empty($data['gerar_boleto'])) {
                    $this->gerarBoleto($contaId, $data);
                }
                
                // Se for conta recorrente, agenda próximas ocorrências
                if (!empty($data['recorrente']) && !empty($data['frequencia_recorrencia'])) {
                    $this->agendarRecorrencias($contaId, $data);
                }
                
                return $contaId;
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("Erro ao criar conta a receber: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cria conta a receber parcelada
     * 
     * @param array $data Dados da conta
     * @param int $numeroParcelas Número de parcelas
     * @param bool $gerarBoletos Gerar boleto para cada parcela
     * @return array IDs das contas criadas
     */
    public function createParcelada(array $data, $numeroParcelas, $gerarBoletos = false)
    {
        try {
            $this->db->beginTransaction();
            
            if ($numeroParcelas < 2) {
                throw new \Exception("Número de parcelas deve ser maior que 1");
            }
            
            if ($numeroParcelas > 48) {
                throw new \Exception("Número máximo de parcelas é 48");
            }
            
            $valorParcela = round($data['valor_original'] / $numeroParcelas, 2);
            $somaParcelasAnteriores = $valorParcela * ($numeroParcelas - 1);
            $valorUltimaParcela = $data['valor_original'] - $somaParcelasAnteriores;
            
            $contasIds = [];
            $dataBase = new \DateTime($data['data_vencimento']);
            
            for ($i = 1; $i <= $numeroParcelas; $i++) {
                $dataVencimento = clone $dataBase;
                if ($i > 1) {
                    $dataVencimento->modify('+' . ($i - 1) . ' month');
                }
                
                $valorAtual = ($i == $numeroParcelas) ? $valorUltimaParcela : $valorParcela;
                
                $dadosParcela = array_merge($data, [
                    'valor_original' => $valorAtual,
                    'data_vencimento' => $dataVencimento->format('Y-m-d'),
                    'descricao' => $data['descricao'] . " - Parcela {$i}/{$numeroParcelas}",
                    'parcela_numero' => $i,
                    'total_parcelas' => $numeroParcelas,
                    'gerar_boleto' => $gerarBoletos
                ]);
                
                // Remove campos que não devem ser herdados
                unset($dadosParcela['recorrente']);
                unset($dadosParcela['frequencia_recorrencia']);
                
                $contaId = $this->create($dadosParcela);
                $contasIds[] = $contaId;
            }
            
            $this->db->commit();
            return $contasIds;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar conta parcelada: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca conta a receber por ID
     * 
     * @param int $id ID da conta
     * @return array|false Dados da conta ou false se não encontrado
     */
    public function findById($id)
    {
        $sql = "SELECT cr.*,
                c.nome as cliente_nome,
                c.cpf_cnpj as cliente_documento,
                c.email as cliente_email,
                p.nome as projeto_nome,
                nf.numero as nota_fiscal_numero,
                cf.nome as categoria_nome,
                cc.nome as centro_custo_nome,
                b.numero_boleto as boleto_numero,
                b.status as boleto_status,
                u.nome as criador_nome,
                (SELECT COUNT(*) FROM pagamentos 
                 WHERE origem_tipo = 'conta_receber' 
                 AND origem_id = cr.id 
                 AND ativo = TRUE) as total_pagamentos
                FROM {$this->table} cr
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                LEFT JOIN projetos p ON cr.projeto_id = p.id
                LEFT JOIN notas_fiscais nf ON cr.nota_fiscal_id = nf.id
                LEFT JOIN categorias_financeiras cf ON cr.categoria_financeira_id = cf.id
                LEFT JOIN centros_custo cc ON cr.centro_custo_id = cc.id
                LEFT JOIN boletos b ON cr.boleto_id = b.id
                LEFT JOIN usuarios u ON cr.criado_por = u.id
                WHERE cr.id = :id AND cr.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista contas a receber com filtros e paginação
     * 
     * @param array $filtros Filtros a aplicar
     * @param int $page Página atual
     * @param int $limit Itens por página
     * @return array Array com 'data' e 'total'
     */
    public function all(array $filtros = [], $page = 1, $limit = 50)
    {
        $where = ["cr.ativo = TRUE"];
        $params = [];
        
        // Filtro por status
        if (!empty($filtros['status'])) {
            $where[] = "cr.status = :status";
            $params['status'] = $filtros['status'];
        }
        
        // Filtro por cliente
        if (!empty($filtros['cliente_id'])) {
            $where[] = "cr.cliente_id = :cliente_id";
            $params['cliente_id'] = $filtros['cliente_id'];
        }
        
        // Filtro por projeto
        if (!empty($filtros['projeto_id'])) {
            $where[] = "cr.projeto_id = :projeto_id";
            $params['projeto_id'] = $filtros['projeto_id'];
        }
        
        // Filtro por categoria
        if (!empty($filtros['categoria_financeira_id'])) {
            $where[] = "cr.categoria_financeira_id = :categoria_financeira_id";
            $params['categoria_financeira_id'] = $filtros['categoria_financeira_id'];
        }
        
        // Filtro por centro de custo
        if (!empty($filtros['centro_custo_id'])) {
            $where[] = "cr.centro_custo_id = :centro_custo_id";
            $params['centro_custo_id'] = $filtros['centro_custo_id'];
        }
        
        // Filtro por data de vencimento
        if (!empty($filtros['data_vencimento_inicio'])) {
            $where[] = "cr.data_vencimento >= :data_vencimento_inicio";
            $params['data_vencimento_inicio'] = $filtros['data_vencimento_inicio'];
        }
        
        if (!empty($filtros['data_vencimento_fim'])) {
            $where[] = "cr.data_vencimento <= :data_vencimento_fim";
            $params['data_vencimento_fim'] = $filtros['data_vencimento_fim'];
        }
        
        // Filtro por vencidas
        if (isset($filtros['vencidas']) && $filtros['vencidas']) {
            $where[] = "cr.data_vencimento < CURRENT_DATE";
            $where[] = "cr.status IN ('pendente', 'recebida_parcialmente')";
        }
        
        // Filtro por período de emissão
        if (!empty($filtros['data_emissao_inicio'])) {
            $where[] = "cr.data_emissao >= :data_emissao_inicio";
            $params['data_emissao_inicio'] = $filtros['data_emissao_inicio'];
        }
        
        if (!empty($filtros['data_emissao_fim'])) {
            $where[] = "cr.data_emissao <= :data_emissao_fim";
            $params['data_emissao_fim'] = $filtros['data_emissao_fim'];
        }
        
        // Filtro por número
        if (!empty($filtros['numero'])) {
            $where[] = "cr.numero LIKE :numero";
            $params['numero'] = '%' . $filtros['numero'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Query de contagem
        $sqlCount = "SELECT COUNT(*) as total
                    FROM {$this->table} cr
                    WHERE {$whereClause}";
        
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query de dados
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT cr.*,
                c.nome as cliente_nome,
                p.nome as projeto_nome,
                cf.nome as categoria_nome,
                cc.nome as centro_custo_nome,
                b.numero_boleto as boleto_numero
                FROM {$this->table} cr
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                LEFT JOIN projetos p ON cr.projeto_id = p.id
                LEFT JOIN categorias_financeiras cf ON cr.categoria_financeira_id = cf.id
                LEFT JOIN centros_custo cc ON cr.centro_custo_id = cc.id
                LEFT JOIN boletos b ON cr.boleto_id = b.id
                WHERE {$whereClause}
                ORDER BY cr.data_vencimento ASC, cr.criado_em DESC
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
        $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $contas,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Atualiza uma conta a receber
     * 
     * @param int $id ID da conta
     * @param array $data Dados a atualizar
     * @return bool Sucesso da operação
     */
    public function update($id, array $data)
    {
        try {
            // Busca conta atual
            $contaAtual = $this->findById($id);
            if (!$contaAtual) {
                throw new \Exception("Conta a receber não encontrada");
            }
            
            // Não permite alterar conta recebida
            if ($contaAtual['status'] === self::STATUS_RECEBIDA) {
                throw new \Exception("Conta já recebida não pode ser alterada");
            }
            
            // Monta SQL dinamicamente apenas com campos fornecidos
            $campos = [];
            $params = ['id' => $id];
            
            $camposPermitidos = [
                'cliente_id', 'projeto_id', 'nota_fiscal_id', 'categoria_financeira_id', 
                'centro_custo_id', 'descricao', 'valor_original', 'data_emissao',
                'data_vencimento', 'forma_pagamento', 'numero_documento', 'observacoes',
                'gerar_boleto', 'recorrente', 'frequencia_recorrencia', 'dia_vencimento_recorrencia'
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
            error_log("Erro ao atualizar conta a receber: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Recebe uma conta (total ou parcialmente)
     * 
     * @param int $id ID da conta
     * @param array $dadosRecebimento Dados do recebimento
     * @return int ID do pagamento criado
     */
    public function receber($id, array $dadosRecebimento)
    {
        try {
            $this->db->beginTransaction();
            
            $conta = $this->findById($id);
            if (!$conta) {
                throw new \Exception("Conta a receber não encontrada");
            }
            
            if ($conta['status'] === self::STATUS_RECEBIDA) {
                throw new \Exception("Conta já está recebida");
            }
            
            if ($conta['status'] === self::STATUS_CANCELADA) {
                throw new \Exception("Conta cancelada não pode ser recebida");
            }
            
            // Valida valor do recebimento
            $valorRecebimento = $dadosRecebimento['valor'];
            if ($valorRecebimento > $conta['valor_pendente']) {
                throw new \Exception("Valor do recebimento excede o valor pendente");
            }
            
            // Cria registro de pagamento (recebimento)
            $pagamentoModel = new Pagamento();
            $pagamentoId = $pagamentoModel->processar([
                'origem_tipo' => 'conta_receber',
                'origem_id' => $id,
                'valor' => $valorRecebimento,
                'forma_pagamento' => $dadosRecebimento['forma_pagamento'],
                'data_pagamento' => $dadosRecebimento['data_pagamento'] ?? date('Y-m-d'),
                'banco_id' => $dadosRecebimento['banco_id'] ?? null,
                'conta_bancaria_id' => $dadosRecebimento['conta_bancaria_id'] ?? null,
                'comprovante_url' => $dadosRecebimento['comprovante_url'] ?? null,
                'numero_documento' => $dadosRecebimento['numero_documento'] ?? null,
                'observacoes' => $dadosRecebimento['observacoes'] ?? '',
                'registrar_lancamento' => $dadosRecebimento['registrar_lancamento'] ?? true
            ]);
            
            // Atualiza valor pago e status da conta (trigger fará isso automaticamente)
            // Mas também atualiza data de pagamento se for recebimento total
            $novoValorPago = $conta['valor_pago'] + $valorRecebimento;
            $novoStatus = ($novoValorPago >= $conta['valor_original']) ? 
                         self::STATUS_RECEBIDA : self::STATUS_RECEBIDA_PARCIALMENTE;
            
            if ($novoStatus === self::STATUS_RECEBIDA) {
                $sql = "UPDATE {$this->table} 
                        SET data_pagamento = :data_pagamento,
                            status = :status
                        WHERE id = :id";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'id' => $id,
                    'data_pagamento' => $dadosRecebimento['data_pagamento'] ?? date('Y-m-d'),
                    'status' => $novoStatus
                ]);
                
                // Se houver boleto vinculado, marca como pago
                if (!empty($conta['boleto_id'])) {
                    $boletoModel = new Boleto();
                    $boletoModel->pagar($conta['boleto_id'], [
                        'valor_pago' => $conta['valor_original'],
                        'data_pagamento' => $dadosRecebimento['data_pagamento'] ?? date('Y-m-d')
                    ]);
                }
            }
            
            $this->db->commit();
            return $pagamentoId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao receber conta: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cancela uma conta a receber
     * 
     * @param int $id ID da conta
     * @param string $motivo Motivo do cancelamento
     * @return bool Sucesso da operação
     */
    public function cancelar($id, $motivo)
    {
        try {
            $this->db->beginTransaction();
            
            $conta = $this->findById($id);
            if (!$conta) {
                throw new \Exception("Conta a receber não encontrada");
            }
            
            if ($conta['status'] === self::STATUS_RECEBIDA) {
                throw new \Exception("Conta recebida não pode ser cancelada");
            }
            
            if ($conta['valor_pago'] > 0) {
                throw new \Exception("Conta com recebimentos não pode ser cancelada. Estorne os pagamentos primeiro.");
            }
            
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    observacoes = CONCAT(IFNULL(observacoes, ''), :observacao_cancelamento),
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt->execute([
                'id' => $id,
                'status' => self::STATUS_CANCELADA,
                'observacao_cancelamento' => "\n[CANCELADA] Motivo: {$motivo}",
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ])) {
                throw new \Exception("Erro ao cancelar conta");
            }
            
            // Se houver boleto vinculado, cancela também
            if (!empty($conta['boleto_id'])) {
                $boletoModel = new Boleto();
                $boletoModel->cancelar($conta['boleto_id'], $motivo);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao cancelar conta a receber: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Soft delete de uma conta a receber
     * 
     * @param int $id ID da conta
     * @return bool Sucesso da operação
     */
    public function delete($id)
    {
        try {
            $conta = $this->findById($id);
            if (!$conta) {
                throw new \Exception("Conta a receber não encontrada");
            }
            
            // Não permite excluir conta com recebimentos
            if ($conta['valor_pago'] > 0) {
                throw new \Exception("Conta com recebimentos não pode ser excluída");
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
            error_log("Erro ao excluir conta a receber: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca pagamentos (recebimentos) de uma conta
     * 
     * @param int $id ID da conta
     * @return array Lista de recebimentos
     */
    public function getPagamentos($id)
    {
        $sql = "SELECT p.*,
                b.nome as banco_nome,
                u.nome as criador_nome
                FROM pagamentos p
                LEFT JOIN bancos b ON p.banco_id = b.id
                LEFT JOIN usuarios u ON p.criado_por = u.id
                WHERE p.origem_tipo = 'conta_receber'
                AND p.origem_id = :id
                AND p.ativo = TRUE
                ORDER BY p.data_pagamento DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Gera boleto para a conta
     * 
     * @param int $contaId ID da conta
     * @param array $dadosConta Dados da conta
     * @return int|false ID do boleto gerado
     */
    private function gerarBoleto($contaId, array $dadosConta)
    {
        try {
            $boletoModel = new Boleto();
            
            $dadosBoleto = [
                'conta_receber_id' => $contaId,
                'banco_codigo' => $dadosConta['banco_codigo'] ?? '001',
                'agencia' => $dadosConta['agencia'] ?? '',
                'conta_corrente' => $dadosConta['conta_corrente'] ?? '',
                'carteira' => $dadosConta['carteira'] ?? '',
                'valor_original' => $dadosConta['valor_original'],
                'data_vencimento' => $dadosConta['data_vencimento'],
                'observacoes' => $dadosConta['descricao'],
                'instrucoes_banco' => $dadosConta['instrucoes_boleto'] ?? '',
                'criado_por' => $dadosConta['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            $boletoId = $boletoModel->create($dadosBoleto);
            
            // Atualiza conta com ID do boleto
            if ($boletoId) {
                $sql = "UPDATE {$this->table} SET boleto_id = :boleto_id WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'boleto_id' => $boletoId,
                    'id' => $contaId
                ]);
            }
            
            return $boletoId;
            
        } catch (\Exception $e) {
            error_log("Erro ao gerar boleto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gera número único para a conta
     * 
     * @return string Número da conta
     */
    private function gerarNumero()
    {
        // Formato: CR-AAAA-MM-NNNNN
        $ano = date('Y');
        $mes = date('m');
        
        $sql = "SELECT numero 
                FROM {$this->table} 
                WHERE numero LIKE :prefixo
                ORDER BY numero DESC 
                LIMIT 1";
        
        $prefixo = "CR-{$ano}-{$mes}-%";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefixo' => $prefixo]);
        
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ultimo) {
            $ultimoNumero = (int)substr($ultimo['numero'], -5);
            $novoNumero = $ultimoNumero + 1;
        } else {
            $novoNumero = 1;
        }
        
        return "CR-{$ano}-{$mes}-" . str_pad($novoNumero, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Valida dados obrigatórios
     * 
     * @param array $data Dados a validar
     * @throws \Exception Se validação falhar
     */
    private function validarDados(array $data)
    {
        if (empty($data['cliente_id'])) {
            throw new \Exception("Cliente é obrigatório");
        }
        
        if (empty($data['descricao'])) {
            throw new \Exception("Descrição é obrigatória");
        }
        
        if (empty($data['valor_original']) || $data['valor_original'] <= 0) {
            throw new \Exception("Valor da conta é obrigatório e deve ser maior que zero");
        }
        
        if (empty($data['data_vencimento'])) {
            throw new \Exception("Data de vencimento é obrigatória");
        }
    }
    
    /**
     * Agenda recorrências futuras de uma conta
     * 
     * @param int $contaId ID da conta principal
     * @param array $data Dados da conta
     * @return void
     */
    private function agendarRecorrencias($contaId, array $data)
    {
        // Cria próximas 12 ocorrências
        $dataBase = new \DateTime($data['data_vencimento']);
        $numeroRecorrencias = 12;
        
        for ($i = 1; $i <= $numeroRecorrencias; $i++) {
            $dataProxima = clone $dataBase;
            
            switch ($data['frequencia_recorrencia']) {
                case 'mensal':
                    $dataProxima->modify("+{$i} month");
                    break;
                case 'trimestral':
                    $dataProxima->modify('+' . ($i * 3) . ' month');
                    break;
                case 'semestral':
                    $dataProxima->modify('+' . ($i * 6) . ' month');
                    break;
                case 'anual':
                    $dataProxima->modify("+{$i} year");
                    break;
                default:
                    continue 2;
            }
            
            // Ajusta dia do vencimento se especificado
            if (!empty($data['dia_vencimento_recorrencia'])) {
                $dataProxima->setDate(
                    $dataProxima->format('Y'),
                    $dataProxima->format('m'),
                    min($data['dia_vencimento_recorrencia'], $dataProxima->format('t'))
                );
            }
            
            // Cria conta recorrente
            $dadosRecorrente = [
                'numero' => $this->gerarNumero(),
                'cliente_id' => $data['cliente_id'],
                'projeto_id' => $data['projeto_id'] ?? null,
                'categoria_financeira_id' => $data['categoria_financeira_id'] ?? null,
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'descricao' => $data['descricao'] . " (Recorrência {$i})",
                'valor_original' => $data['valor_original'],
                'valor_pago' => 0.00,
                'valor_pendente' => $data['valor_original'],
                'data_emissao' => $dataProxima->format('Y-m-d'),
                'data_vencimento' => $dataProxima->format('Y-m-d'),
                'observacoes' => $data['observacoes'] ?? '',
                'gerar_boleto' => $data['gerar_boleto'] ?? false,
                'recorrente' => false, // Não replica recorrência
                'status' => self::STATUS_PENDENTE,
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            $this->create($dadosRecorrente);
        }
    }
    
    /**
     * Obtém estatísticas de contas a receber
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
            $where[] = "data_vencimento >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "data_vencimento <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['cliente_id'])) {
            $where[] = "cliente_id = :cliente_id";
            $params['cliente_id'] = $filtros['cliente_id'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'recebida_parcialmente' THEN 1 END) as parcialmente_recebidas,
                COUNT(CASE WHEN status = 'recebida' THEN 1 END) as recebidas,
                COUNT(CASE WHEN status = 'vencida' THEN 1 END) as vencidas,
                SUM(CASE WHEN status IN ('pendente', 'recebida_parcialmente', 'vencida') THEN valor_pendente ELSE 0 END) as valor_pendente_total,
                SUM(CASE WHEN status = 'recebida' THEN valor_pago ELSE 0 END) as valor_recebido_total,
                SUM(valor_original) as valor_original_total
                FROM {$this->table}
                WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca contas vencidas
     * 
     * @return array Lista de contas vencidas
     */
    public function getVencidas()
    {
        $sql = "SELECT cr.*,
                c.nome as cliente_nome,
                c.email as cliente_email,
                DATEDIFF(CURRENT_DATE, cr.data_vencimento) as dias_atraso
                FROM {$this->table} cr
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE cr.ativo = TRUE
                AND cr.status IN ('pendente', 'recebida_parcialmente')
                AND cr.data_vencimento < CURRENT_DATE
                ORDER BY cr.data_vencimento ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca contas que vencem hoje
     * 
     * @return array Lista de contas
     */
    public function getVencemHoje()
    {
        $sql = "SELECT cr.*,
                c.nome as cliente_nome,
                c.email as cliente_email
                FROM {$this->table} cr
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE cr.ativo = TRUE
                AND cr.status = 'pendente'
                AND cr.data_vencimento = CURRENT_DATE
                ORDER BY cr.valor_original DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca contas que vencem nos próximos N dias
     * 
     * @param int $dias Número de dias
     * @return array Lista de contas
     */
    public function getVencemProximosDias($dias = 7)
    {
        $sql = "SELECT cr.*,
                c.nome as cliente_nome,
                c.email as cliente_email,
                DATEDIFF(cr.data_vencimento, CURRENT_DATE) as dias_para_vencimento
                FROM {$this->table} cr
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE cr.ativo = TRUE
                AND cr.status = 'pendente'
                AND cr.data_vencimento BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL :dias DAY)
                ORDER BY cr.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dias' => $dias]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
