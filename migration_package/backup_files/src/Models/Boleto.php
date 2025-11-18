<?php
namespace App\Models;

use PDO;

/**
 * Class Boleto
 * 
 * Model para gerenciar boletos bancários com geração de código de barras,
 * registro bancário, cálculo de juros/multa e controle de status.
 * 
 * Funcionalidades:
 * - Geração de boleto com código de barras e linha digitável
 * - Cálculo automático de juros e multa após vencimento
 * - Registro bancário (remessa) e processamento de retorno
 * - Suporte a múltiplos bancos (BB, Bradesco, Itaú, Santander, Caixa)
 * - Gerenciamento de status (emitido, registrado, pago, cancelado)
 * - Geração de PDF do boleto
 * - Integração com contas a receber
 * 
 * @package App\Models
 */
class Boleto
{
    private $db;
    private $table = 'boletos';
    
    // Status válidos
    const STATUS_EMITIDO = 'emitido';
    const STATUS_REGISTRADO = 'registrado';
    const STATUS_PAGO = 'pago';
    const STATUS_CANCELADO = 'cancelado';
    const STATUS_VENCIDO = 'vencido';
    
    // Bancos suportados
    const BANCO_BB = '001';
    const BANCO_BRADESCO = '237';
    const BANCO_ITAU = '341';
    const BANCO_SANTANDER = '033';
    const BANCO_CAIXA = '104';
    
    /**
     * Construtor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Cria um novo boleto
     * 
     * @param array $data Dados do boleto
     * @return int|false ID do boleto criado ou false em caso de erro
     */
    public function create(array $data)
    {
        try {
            // Validação de dados obrigatórios
            if (empty($data['conta_receber_id'])) {
                throw new \Exception("Conta a receber é obrigatória");
            }
            
            if (empty($data['banco_codigo'])) {
                throw new \Exception("Código do banco é obrigatório");
            }
            
            if (empty($data['valor_original'])) {
                throw new \Exception("Valor do boleto é obrigatório");
            }
            
            if (empty($data['data_vencimento'])) {
                throw new \Exception("Data de vencimento é obrigatória");
            }
            
            // Validação de datas
            $dataVencimento = new \DateTime($data['data_vencimento']);
            $hoje = new \DateTime();
            $hoje->setTime(0, 0, 0);
            
            if ($dataVencimento < $hoje) {
                throw new \Exception("Data de vencimento não pode ser no passado");
            }
            
            // Busca dados da conta a receber
            $contaReceber = $this->getContaReceber($data['conta_receber_id']);
            if (!$contaReceber) {
                throw new \Exception("Conta a receber não encontrada");
            }
            
            // Busca dados do pagador (cliente)
            $pagador = $this->getPagador($contaReceber['cliente_id']);
            if (!$pagador) {
                throw new \Exception("Pagador não encontrado");
            }
            
            // Gera número do boleto se não fornecido
            if (empty($data['numero_boleto'])) {
                $data['numero_boleto'] = $this->gerarNumeroBoleto($data['banco_codigo']);
            }
            
            // Define valores padrão
            $data['status'] = self::STATUS_EMITIDO;
            $data['data_emissao'] = date('Y-m-d');
            $data['valor_documento'] = $data['valor_original'];
            
            // Calcula valor com desconto se houver
            if (!empty($data['valor_desconto'])) {
                $data['valor_documento'] = $data['valor_original'] - $data['valor_desconto'];
            }
            
            // Dados do pagador
            $data['pagador_nome'] = $pagador['nome'] ?? '';
            $data['pagador_documento'] = $pagador['cpf_cnpj'] ?? '';
            $data['pagador_endereco'] = $pagador['endereco'] ?? '';
            $data['pagador_cep'] = $pagador['cep'] ?? '';
            $data['pagador_cidade'] = $pagador['cidade'] ?? '';
            $data['pagador_estado'] = $pagador['estado'] ?? '';
            
            // Gera código de barras e linha digitável
            $barcodeData = $this->gerarCodigoBarras($data);
            $data['codigo_barras'] = $barcodeData['codigo_barras'];
            $data['linha_digitavel'] = $barcodeData['linha_digitavel'];
            
            // Valores de multa e juros
            $data['percentual_multa'] = $data['percentual_multa'] ?? 2.00;
            $data['percentual_juros_dia'] = $data['percentual_juros_dia'] ?? 0.033;
            
            $sql = "INSERT INTO {$this->table} (
                conta_receber_id, numero_boleto, banco_codigo, agencia, conta_corrente,
                carteira, nosso_numero, data_emissao, data_vencimento, data_limite_pagamento,
                valor_original, valor_documento, valor_desconto, codigo_barras, linha_digitavel,
                pagador_nome, pagador_documento, pagador_endereco, pagador_cep,
                pagador_cidade, pagador_estado, observacoes, instrucoes_banco,
                percentual_multa, percentual_juros_dia, valor_multa, valor_juros,
                dias_protesto, status, criado_por, criado_em
            ) VALUES (
                :conta_receber_id, :numero_boleto, :banco_codigo, :agencia, :conta_corrente,
                :carteira, :nosso_numero, :data_emissao, :data_vencimento, :data_limite_pagamento,
                :valor_original, :valor_documento, :valor_desconto, :codigo_barras, :linha_digitavel,
                :pagador_nome, :pagador_documento, :pagador_endereco, :pagador_cep,
                :pagador_cidade, :pagador_estado, :observacoes, :instrucoes_banco,
                :percentual_multa, :percentual_juros_dia, :valor_multa, :valor_juros,
                :dias_protesto, :status, :criado_por, NOW()
            )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'conta_receber_id' => $data['conta_receber_id'],
                'numero_boleto' => $data['numero_boleto'],
                'banco_codigo' => $data['banco_codigo'],
                'agencia' => $data['agencia'] ?? '',
                'conta_corrente' => $data['conta_corrente'] ?? '',
                'carteira' => $data['carteira'] ?? '',
                'nosso_numero' => $data['nosso_numero'] ?? $data['numero_boleto'],
                'data_emissao' => $data['data_emissao'],
                'data_vencimento' => $data['data_vencimento'],
                'data_limite_pagamento' => $data['data_limite_pagamento'] ?? null,
                'valor_original' => $data['valor_original'],
                'valor_documento' => $data['valor_documento'],
                'valor_desconto' => $data['valor_desconto'] ?? 0.00,
                'codigo_barras' => $data['codigo_barras'],
                'linha_digitavel' => $data['linha_digitavel'],
                'pagador_nome' => $data['pagador_nome'],
                'pagador_documento' => $data['pagador_documento'],
                'pagador_endereco' => $data['pagador_endereco'],
                'pagador_cep' => $data['pagador_cep'],
                'pagador_cidade' => $data['pagador_cidade'],
                'pagador_estado' => $data['pagador_estado'],
                'observacoes' => $data['observacoes'] ?? '',
                'instrucoes_banco' => $data['instrucoes_banco'] ?? '',
                'percentual_multa' => $data['percentual_multa'],
                'percentual_juros_dia' => $data['percentual_juros_dia'],
                'valor_multa' => $data['valor_multa'] ?? 0.00,
                'valor_juros' => $data['valor_juros'] ?? 0.00,
                'dias_protesto' => $data['dias_protesto'] ?? null,
                'status' => $data['status'],
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            if ($stmt->execute($params)) {
                $boletoId = $this->db->lastInsertId();
                
                // Atualiza conta a receber com o ID do boleto
                $this->vincularContaReceber($data['conta_receber_id'], $boletoId);
                
                return $boletoId;
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("Erro ao criar boleto: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca boleto por ID
     * 
     * @param int $id ID do boleto
     * @return array|false Dados do boleto ou false se não encontrado
     */
    public function findById($id)
    {
        $sql = "SELECT b.*,
                cr.numero as conta_receber_numero,
                cr.descricao as conta_receber_descricao,
                c.nome as cliente_nome,
                c.cpf_cnpj as cliente_documento,
                u.nome as criador_nome
                FROM {$this->table} b
                LEFT JOIN contas_receber cr ON b.conta_receber_id = cr.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                LEFT JOIN usuarios u ON b.criado_por = u.id
                WHERE b.id = :id AND b.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $boleto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($boleto) {
            // Calcula valores atualizados se vencido
            $boleto = $this->calcularValoresAtualizados($boleto);
        }
        
        return $boleto;
    }
    
    /**
     * Lista boletos com filtros e paginação
     * 
     * @param array $filtros Filtros a aplicar
     * @param int $page Página atual
     * @param int $limit Itens por página
     * @return array Array com 'data' e 'total'
     */
    public function all(array $filtros = [], $page = 1, $limit = 50)
    {
        $where = ["b.ativo = TRUE"];
        $params = [];
        
        // Filtro por status
        if (!empty($filtros['status'])) {
            $where[] = "b.status = :status";
            $params['status'] = $filtros['status'];
        }
        
        // Filtro por banco
        if (!empty($filtros['banco_codigo'])) {
            $where[] = "b.banco_codigo = :banco_codigo";
            $params['banco_codigo'] = $filtros['banco_codigo'];
        }
        
        // Filtro por data de vencimento
        if (!empty($filtros['data_vencimento_inicio'])) {
            $where[] = "b.data_vencimento >= :data_vencimento_inicio";
            $params['data_vencimento_inicio'] = $filtros['data_vencimento_inicio'];
        }
        
        if (!empty($filtros['data_vencimento_fim'])) {
            $where[] = "b.data_vencimento <= :data_vencimento_fim";
            $params['data_vencimento_fim'] = $filtros['data_vencimento_fim'];
        }
        
        // Filtro por cliente
        if (!empty($filtros['cliente_id'])) {
            $where[] = "cr.cliente_id = :cliente_id";
            $params['cliente_id'] = $filtros['cliente_id'];
        }
        
        // Filtro por número do boleto
        if (!empty($filtros['numero_boleto'])) {
            $where[] = "b.numero_boleto LIKE :numero_boleto";
            $params['numero_boleto'] = '%' . $filtros['numero_boleto'] . '%';
        }
        
        // Filtro por vencidos
        if (isset($filtros['vencidos']) && $filtros['vencidos']) {
            $where[] = "b.data_vencimento < CURRENT_DATE";
            $where[] = "b.status NOT IN ('pago', 'cancelado')";
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Query de contagem
        $sqlCount = "SELECT COUNT(*) as total
                    FROM {$this->table} b
                    LEFT JOIN contas_receber cr ON b.conta_receber_id = cr.id
                    WHERE {$whereClause}";
        
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query de dados
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT b.*,
                cr.numero as conta_receber_numero,
                cr.descricao as conta_receber_descricao,
                c.nome as cliente_nome,
                c.cpf_cnpj as cliente_documento
                FROM {$this->table} b
                LEFT JOIN contas_receber cr ON b.conta_receber_id = cr.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE {$whereClause}
                ORDER BY b.data_vencimento ASC, b.criado_em DESC
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
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcula valores atualizados para cada boleto
        foreach ($boletos as &$boleto) {
            $boleto = $this->calcularValoresAtualizados($boleto);
        }
        
        return [
            'data' => $boletos,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Atualiza um boleto
     * 
     * @param int $id ID do boleto
     * @param array $data Dados a atualizar
     * @return bool Sucesso da operação
     */
    public function update($id, array $data)
    {
        try {
            // Busca boleto atual
            $boletoAtual = $this->findById($id);
            if (!$boletoAtual) {
                throw new \Exception("Boleto não encontrado");
            }
            
            // Não permite alterar boleto pago ou cancelado
            if (in_array($boletoAtual['status'], [self::STATUS_PAGO, self::STATUS_CANCELADO])) {
                throw new \Exception("Não é possível alterar boleto {$boletoAtual['status']}");
            }
            
            // Monta SQL dinamicamente apenas com campos fornecidos
            $campos = [];
            $params = ['id' => $id];
            
            $camposPermitidos = [
                'data_vencimento', 'data_limite_pagamento', 'valor_original', 'valor_documento',
                'valor_desconto', 'observacoes', 'instrucoes_banco', 'percentual_multa',
                'percentual_juros_dia', 'dias_protesto', 'status'
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
            error_log("Erro ao atualizar boleto: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Registra boleto no banco (remessa)
     * 
     * @param int $id ID do boleto
     * @param array $dadosRegistro Dados do registro bancário
     * @return bool Sucesso da operação
     */
    public function registrar($id, array $dadosRegistro = [])
    {
        try {
            $boleto = $this->findById($id);
            if (!$boleto) {
                throw new \Exception("Boleto não encontrado");
            }
            
            if ($boleto['status'] !== self::STATUS_EMITIDO) {
                throw new \Exception("Apenas boletos emitidos podem ser registrados");
            }
            
            // Atualiza status e dados de registro
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    data_registro = :data_registro,
                    numero_remessa = :numero_remessa,
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'id' => $id,
                'status' => self::STATUS_REGISTRADO,
                'data_registro' => $dadosRegistro['data_registro'] ?? date('Y-m-d'),
                'numero_remessa' => $dadosRegistro['numero_remessa'] ?? null,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ];
            
            return $stmt->execute($params);
            
        } catch (\Exception $e) {
            error_log("Erro ao registrar boleto: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Marca boleto como pago
     * 
     * @param int $id ID do boleto
     * @param array $dadosPagamento Dados do pagamento
     * @return bool Sucesso da operação
     */
    public function pagar($id, array $dadosPagamento)
    {
        try {
            $this->db->beginTransaction();
            
            $boleto = $this->findById($id);
            if (!$boleto) {
                throw new \Exception("Boleto não encontrado");
            }
            
            if ($boleto['status'] === self::STATUS_PAGO) {
                throw new \Exception("Boleto já está pago");
            }
            
            if ($boleto['status'] === self::STATUS_CANCELADO) {
                throw new \Exception("Boleto cancelado não pode ser pago");
            }
            
            // Calcula valor total pago (pode incluir juros e multa)
            $valorPago = $dadosPagamento['valor_pago'] ?? $boleto['valor_documento'];
            
            // Atualiza boleto
            $sql = "UPDATE {$this->table} SET 
                    status = :status,
                    data_pagamento = :data_pagamento,
                    valor_pago = :valor_pago,
                    valor_juros = :valor_juros,
                    valor_multa = :valor_multa,
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'id' => $id,
                'status' => self::STATUS_PAGO,
                'data_pagamento' => $dadosPagamento['data_pagamento'] ?? date('Y-m-d'),
                'valor_pago' => $valorPago,
                'valor_juros' => $dadosPagamento['valor_juros'] ?? 0.00,
                'valor_multa' => $dadosPagamento['valor_multa'] ?? 0.00,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ];
            
            if (!$stmt->execute($params)) {
                throw new \Exception("Erro ao atualizar boleto");
            }
            
            // Cria registro de pagamento na conta a receber
            $this->registrarPagamentoContaReceber($boleto['conta_receber_id'], [
                'valor' => $valorPago,
                'data_pagamento' => $dadosPagamento['data_pagamento'] ?? date('Y-m-d'),
                'forma_pagamento' => 'boleto',
                'boleto_id' => $id,
                'observacoes' => $dadosPagamento['observacoes'] ?? "Pagamento via boleto {$boleto['numero_boleto']}"
            ]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao pagar boleto: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cancela um boleto
     * 
     * @param int $id ID do boleto
     * @param string $motivo Motivo do cancelamento
     * @return bool Sucesso da operação
     */
    public function cancelar($id, $motivo = '')
    {
        try {
            $boleto = $this->findById($id);
            if (!$boleto) {
                throw new \Exception("Boleto não encontrado");
            }
            
            if ($boleto['status'] === self::STATUS_PAGO) {
                throw new \Exception("Boleto pago não pode ser cancelado");
            }
            
            if ($boleto['status'] === self::STATUS_CANCELADO) {
                throw new \Exception("Boleto já está cancelado");
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
                'data_cancelamento' => date('Y-m-d'),
                'motivo_cancelamento' => $motivo,
                'atualizado_por' => $_SESSION['user_id'] ?? null
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao cancelar boleto: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Soft delete de um boleto
     * 
     * @param int $id ID do boleto
     * @return bool Sucesso da operação
     */
    public function delete($id)
    {
        try {
            $boleto = $this->findById($id);
            if (!$boleto) {
                throw new \Exception("Boleto não encontrado");
            }
            
            // Não permite excluir boleto pago
            if ($boleto['status'] === self::STATUS_PAGO) {
                throw new \Exception("Boleto pago não pode ser excluído");
            }
            
            // Apenas marca como inativo (soft delete)
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
            error_log("Erro ao excluir boleto: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Gera número único do boleto
     * 
     * @param string $bancocodigo Código do banco
     * @return string Número do boleto
     */
    private function gerarNumeroBoleto($bancoCodigo)
    {
        // Formato: BANCO + ANO + MÊS + SEQUENCIAL
        $ano = date('Y');
        $mes = date('m');
        
        // Busca último número do mês
        $sql = "SELECT numero_boleto 
                FROM {$this->table} 
                WHERE banco_codigo = :banco_codigo 
                AND numero_boleto LIKE :prefixo
                ORDER BY numero_boleto DESC 
                LIMIT 1";
        
        $prefixo = $bancoCodigo . $ano . $mes;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'banco_codigo' => $bancoCodigo,
            'prefixo' => $prefixo . '%'
        ]);
        
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ultimo) {
            // Incrementa sequencial
            $sequencial = (int)substr($ultimo['numero_boleto'], -6) + 1;
        } else {
            // Primeiro boleto do mês
            $sequencial = 1;
        }
        
        return $prefixo . str_pad($sequencial, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Gera código de barras e linha digitável do boleto
     * 
     * @param array $dados Dados do boleto
     * @return array Array com 'codigo_barras' e 'linha_digitavel'
     */
    private function gerarCodigoBarras(array $dados)
    {
        // Implementação básica - em produção usar biblioteca específica por banco
        // Cada banco tem suas regras específicas para geração do código de barras
        
        $bancoCodigo = str_pad($dados['banco_codigo'], 3, '0', STR_PAD_LEFT);
        $moeda = '9'; // 9 = Real
        
        // Calcula fator de vencimento (dias desde 07/10/1997)
        $dataBase = new \DateTime('1997-10-07');
        $dataVencimento = new \DateTime($dados['data_vencimento']);
        $fatorVencimento = $dataVencimento->diff($dataBase)->days;
        $fatorVencimento = str_pad($fatorVencimento, 4, '0', STR_PAD_LEFT);
        
        // Valor do documento (10 posições, sem vírgula)
        $valor = str_replace(['.', ','], '', number_format($dados['valor_original'], 2, '', ''));
        $valor = str_pad($valor, 10, '0', STR_PAD_LEFT);
        
        // Campo livre (25 posições) - varia por banco
        // Aqui usa uma implementação genérica
        $nossoNumero = str_pad($dados['nosso_numero'] ?? '', 11, '0', STR_PAD_LEFT);
        $agencia = str_pad($dados['agencia'] ?? '0000', 4, '0', STR_PAD_LEFT);
        $conta = str_pad($dados['conta_corrente'] ?? '00000', 8, '0', STR_PAD_LEFT);
        $carteira = str_pad($dados['carteira'] ?? '00', 2, '0', STR_PAD_LEFT);
        
        $campoLivre = $agencia . $carteira . $nossoNumero . $conta;
        $campoLivre = substr($campoLivre, 0, 25);
        
        // Monta código de barras (sem DV ainda)
        $codigoSemDV = $bancoCodigo . $moeda . $fatorVencimento . $valor . $campoLivre;
        
        // Calcula dígito verificador (módulo 11)
        $dv = $this->calcularDVModulo11($codigoSemDV);
        
        // Código de barras final (44 posições)
        $codigoBarras = $bancoCodigo . $moeda . $dv . $fatorVencimento . $valor . $campoLivre;
        
        // Gera linha digitável
        $linhaDigitavel = $this->gerarLinhaDigitavel($codigoBarras);
        
        return [
            'codigo_barras' => $codigoBarras,
            'linha_digitavel' => $linhaDigitavel
        ];
    }
    
    /**
     * Gera linha digitável a partir do código de barras
     * 
     * @param string $codigoBarras Código de barras (44 dígitos)
     * @return string Linha digitável formatada
     */
    private function gerarLinhaDigitavel($codigoBarras)
    {
        // Campo 1: Posições 1-4 e 20-24 do código de barras + DV
        $campo1 = substr($codigoBarras, 0, 4) . substr($codigoBarras, 19, 5);
        $campo1 .= $this->calcularDVModulo10($campo1);
        
        // Campo 2: Posições 25-34 do código de barras + DV
        $campo2 = substr($codigoBarras, 24, 10);
        $campo2 .= $this->calcularDVModulo10($campo2);
        
        // Campo 3: Posições 35-44 do código de barras + DV
        $campo3 = substr($codigoBarras, 34, 10);
        $campo3 .= $this->calcularDVModulo10($campo3);
        
        // Campo 4: DV geral (posição 5 do código de barras)
        $campo4 = substr($codigoBarras, 4, 1);
        
        // Campo 5: Fator de vencimento + valor (posições 6-19 do código de barras)
        $campo5 = substr($codigoBarras, 5, 14);
        
        // Formata linha digitável: XXXXX.XXXXX XXXXX.XXXXXX XXXXX.XXXXXX X XXXXXXXXXXXXXXX
        $linhaDigitavel = substr($campo1, 0, 5) . '.' . substr($campo1, 5) . ' ';
        $linhaDigitavel .= substr($campo2, 0, 5) . '.' . substr($campo2, 5) . ' ';
        $linhaDigitavel .= substr($campo3, 0, 5) . '.' . substr($campo3, 5) . ' ';
        $linhaDigitavel .= $campo4 . ' ';
        $linhaDigitavel .= $campo5;
        
        return $linhaDigitavel;
    }
    
    /**
     * Calcula dígito verificador módulo 11
     * 
     * @param string $numero Número para calcular DV
     * @return string Dígito verificador
     */
    private function calcularDVModulo11($numero)
    {
        $multiplicadores = [2, 3, 4, 5, 6, 7, 8, 9];
        $soma = 0;
        $posicao = 0;
        
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $soma += $numero[$i] * $multiplicadores[$posicao % 8];
            $posicao++;
        }
        
        $resto = $soma % 11;
        $dv = 11 - $resto;
        
        if ($dv == 0 || $dv == 10 || $dv == 11) {
            $dv = 1;
        }
        
        return (string)$dv;
    }
    
    /**
     * Calcula dígito verificador módulo 10
     * 
     * @param string $numero Número para calcular DV
     * @return string Dígito verificador
     */
    private function calcularDVModulo10($numero)
    {
        $multiplicador = 2;
        $soma = 0;
        
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $resultado = $numero[$i] * $multiplicador;
            
            if ($resultado > 9) {
                $resultado = (int)($resultado / 10) + ($resultado % 10);
            }
            
            $soma += $resultado;
            $multiplicador = $multiplicador == 2 ? 1 : 2;
        }
        
        $resto = $soma % 10;
        $dv = $resto == 0 ? 0 : 10 - $resto;
        
        return (string)$dv;
    }
    
    /**
     * Calcula valores atualizados do boleto (juros e multa)
     * 
     * @param array $boleto Dados do boleto
     * @return array Boleto com valores atualizados
     */
    private function calcularValoresAtualizados($boleto)
    {
        // Se já pago ou cancelado, não calcula
        if (in_array($boleto['status'], [self::STATUS_PAGO, self::STATUS_CANCELADO])) {
            return $boleto;
        }
        
        $dataVencimento = new \DateTime($boleto['data_vencimento']);
        $hoje = new \DateTime();
        $hoje->setTime(0, 0, 0);
        
        // Calcula dias de atraso
        $diasAtraso = 0;
        if ($hoje > $dataVencimento) {
            $diasAtraso = $hoje->diff($dataVencimento)->days;
            
            // Atualiza status para vencido se necessário
            if ($boleto['status'] != self::STATUS_VENCIDO) {
                $this->atualizarStatusVencido($boleto['id']);
                $boleto['status'] = self::STATUS_VENCIDO;
            }
        }
        
        $boleto['dias_atraso'] = $diasAtraso;
        
        // Calcula multa (apenas após vencimento)
        $valorMulta = 0;
        if ($diasAtraso > 0 && $boleto['percentual_multa'] > 0) {
            $valorMulta = $boleto['valor_documento'] * ($boleto['percentual_multa'] / 100);
        }
        
        // Calcula juros (por dia de atraso)
        $valorJuros = 0;
        if ($diasAtraso > 0 && $boleto['percentual_juros_dia'] > 0) {
            $valorJuros = $boleto['valor_documento'] * ($boleto['percentual_juros_dia'] / 100) * $diasAtraso;
        }
        
        $boleto['valor_multa_calculado'] = number_format($valorMulta, 2, '.', '');
        $boleto['valor_juros_calculado'] = number_format($valorJuros, 2, '.', '');
        $boleto['valor_total_atualizado'] = number_format(
            $boleto['valor_documento'] + $valorMulta + $valorJuros,
            2, '.', ''
        );
        
        return $boleto;
    }
    
    /**
     * Atualiza status do boleto para vencido
     * 
     * @param int $id ID do boleto
     * @return void
     */
    private function atualizarStatusVencido($id)
    {
        $sql = "UPDATE {$this->table} 
                SET status = :status 
                WHERE id = :id 
                AND status NOT IN ('pago', 'cancelado')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_VENCIDO
        ]);
    }
    
    /**
     * Busca dados da conta a receber
     * 
     * @param int $contaReceberId ID da conta a receber
     * @return array|false Dados da conta ou false
     */
    private function getContaReceber($contaReceberId)
    {
        $sql = "SELECT * FROM contas_receber WHERE id = :id AND ativo = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $contaReceberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca dados do pagador (cliente)
     * 
     * @param int $clienteId ID do cliente
     * @return array|false Dados do cliente ou false
     */
    private function getPagador($clienteId)
    {
        $sql = "SELECT * FROM clientes WHERE id = :id AND ativo = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $clienteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Vincula boleto à conta a receber
     * 
     * @param int $contaReceberId ID da conta a receber
     * @param int $boletoId ID do boleto
     * @return void
     */
    private function vincularContaReceber($contaReceberId, $boletoId)
    {
        $sql = "UPDATE contas_receber 
                SET boleto_id = :boleto_id 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'boleto_id' => $boletoId,
            'id' => $contaReceberId
        ]);
    }
    
    /**
     * Registra pagamento na conta a receber
     * 
     * @param int $contaReceberId ID da conta a receber
     * @param array $dadosPagamento Dados do pagamento
     * @return void
     */
    private function registrarPagamentoContaReceber($contaReceberId, array $dadosPagamento)
    {
        $sql = "INSERT INTO pagamentos (
                    origem_tipo, origem_id, valor, data_pagamento,
                    forma_pagamento, observacoes, criado_por, criado_em
                ) VALUES (
                    'conta_receber', :origem_id, :valor, :data_pagamento,
                    :forma_pagamento, :observacoes, :criado_por, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'origem_id' => $contaReceberId,
            'valor' => $dadosPagamento['valor'],
            'data_pagamento' => $dadosPagamento['data_pagamento'],
            'forma_pagamento' => $dadosPagamento['forma_pagamento'],
            'observacoes' => $dadosPagamento['observacoes'] ?? '',
            'criado_por' => $_SESSION['user_id'] ?? null
        ]);
    }
    
    /**
     * Obtém estatísticas de boletos
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
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'emitido' THEN 1 END) as emitidos,
                COUNT(CASE WHEN status = 'registrado' THEN 1 END) as registrados,
                COUNT(CASE WHEN status = 'pago' THEN 1 END) as pagos,
                COUNT(CASE WHEN status = 'vencido' THEN 1 END) as vencidos,
                COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
                SUM(CASE WHEN status NOT IN ('cancelado', 'pago') THEN valor_documento ELSE 0 END) as valor_pendente,
                SUM(CASE WHEN status = 'pago' THEN valor_pago ELSE 0 END) as valor_recebido,
                SUM(CASE WHEN status = 'vencido' THEN valor_documento ELSE 0 END) as valor_vencido
                FROM {$this->table}
                WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca boletos vencidos que precisam atualização
     * 
     * @return array Lista de boletos vencidos
     */
    public function getBoletosPendenteAtualizacao()
    {
        $sql = "SELECT id, numero_boleto, data_vencimento, valor_documento, status
                FROM {$this->table}
                WHERE ativo = TRUE
                AND status IN ('emitido', 'registrado')
                AND data_vencimento < CURRENT_DATE
                ORDER BY data_vencimento ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
