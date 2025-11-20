<?php
namespace App\Models;

use PDO;

/**
 * Class ConciliacaoBancaria
 * 
 * Model para gerenciar conciliação bancária com importação de extratos OFX,
 * matching automático de lançamentos e controle de saldo.
 * 
 * Funcionalidades:
 * - Importação de arquivos OFX (Open Financial Exchange)
 * - Matching automático de lançamentos
 * - Matching manual de lançamentos
 * - Controle de saldo conciliado
 * - Identificação de divergências
 * - Histórico de conciliações
 * - Suporte a múltiplas contas bancárias
 * - Validação de saldos
 * - Relatórios de conciliação
 * 
 * @package App\Models
 */
class ConciliacaoBancaria
{
    private $db;
    private $table = 'reconciliacoes_bancarias';
    
    // Status da conciliação
    const STATUS_PENDENTE = 'pendente';
    const STATUS_PARCIAL = 'parcial';
    const STATUS_CONCILIADA = 'conciliada';
    const STATUS_DIVERGENTE = 'divergente';
    
    // Tipo de transação
    const TIPO_CREDITO = 'credito';
    const TIPO_DEBITO = 'debito';
    
    /**
     * Construtor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Cria uma nova conciliação bancária
     * 
     * @param array $data Dados da conciliação
     * @return int|false ID da conciliação criada ou false em caso de erro
     */
    public function create(array $data)
    {
        try {
            // Validação de dados obrigatórios
            $this->validarDados($data);
            
            // Define valores padrão
            $data['status'] = self::STATUS_PENDENTE;
            $data['data_conciliacao'] = $data['data_conciliacao'] ?? date('Y-m-d');
            
            $sql = "INSERT INTO {$this->table} (
                conta_bancaria_id, data_conciliacao, data_inicio, data_fim,
                saldo_inicial, saldo_final, saldo_conciliado, total_creditos,
                total_debitos, total_divergencias, arquivo_ofx, status,
                observacoes, criado_por, criado_em
            ) VALUES (
                :conta_bancaria_id, :data_conciliacao, :data_inicio, :data_fim,
                :saldo_inicial, :saldo_final, :saldo_conciliado, :total_creditos,
                :total_debitos, :total_divergencias, :arquivo_ofx, :status,
                :observacoes, :criado_por, NOW()
            )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'conta_bancaria_id' => $data['conta_bancaria_id'],
                'data_conciliacao' => $data['data_conciliacao'],
                'data_inicio' => $data['data_inicio'],
                'data_fim' => $data['data_fim'],
                'saldo_inicial' => $data['saldo_inicial'] ?? 0.00,
                'saldo_final' => $data['saldo_final'] ?? 0.00,
                'saldo_conciliado' => $data['saldo_conciliado'] ?? 0.00,
                'total_creditos' => $data['total_creditos'] ?? 0.00,
                'total_debitos' => $data['total_debitos'] ?? 0.00,
                'total_divergencias' => $data['total_divergencias'] ?? 0.00,
                'arquivo_ofx' => $data['arquivo_ofx'] ?? null,
                'status' => $data['status'],
                'observacoes' => $data['observacoes'] ?? '',
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            if ($stmt->execute($params)) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("Erro ao criar conciliação: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Importa arquivo OFX e cria registros de conciliação
     * 
     * @param int $contaBancariaId ID da conta bancária
     * @param string $arquivoOfxPath Caminho do arquivo OFX
     * @return array Resultado da importação
     */
    public function importarOFX($contaBancariaId, $arquivoOfxPath)
    {
        try {
            $this->db->beginTransaction();
            
            // Lê e processa arquivo OFX
            $dadosOfx = $this->processarArquivoOFX($arquivoOfxPath);
            
            if (empty($dadosOfx)) {
                throw new \Exception("Erro ao processar arquivo OFX");
            }
            
            // Cria conciliação
            $conciliacaoData = [
                'conta_bancaria_id' => $contaBancariaId,
                'data_inicio' => $dadosOfx['data_inicio'],
                'data_fim' => $dadosOfx['data_fim'],
                'saldo_inicial' => $dadosOfx['saldo_inicial'],
                'saldo_final' => $dadosOfx['saldo_final'],
                'arquivo_ofx' => basename($arquivoOfxPath)
            ];
            
            $conciliacaoId = $this->create($conciliacaoData);
            
            if (!$conciliacaoId) {
                throw new \Exception("Erro ao criar registro de conciliação");
            }
            
            // Importa transações
            $transacoesImportadas = 0;
            $totalCreditos = 0;
            $totalDebitos = 0;
            
            foreach ($dadosOfx['transacoes'] as $transacao) {
                $itemData = [
                    'reconciliacao_bancaria_id' => $conciliacaoId,
                    'data_transacao' => $transacao['data'],
                    'tipo' => $transacao['tipo'],
                    'valor' => $transacao['valor'],
                    'descricao' => $transacao['descricao'],
                    'documento' => $transacao['documento'] ?? null,
                    'fitid' => $transacao['fitid'] ?? null,
                    'conciliado' => false
                ];
                
                if ($this->createItem($itemData)) {
                    $transacoesImportadas++;
                    
                    if ($transacao['tipo'] === self::TIPO_CREDITO) {
                        $totalCreditos += $transacao['valor'];
                    } else {
                        $totalDebitos += $transacao['valor'];
                    }
                }
            }
            
            // Atualiza totais da conciliação
            $this->update($conciliacaoId, [
                'total_creditos' => $totalCreditos,
                'total_debitos' => $totalDebitos
            ]);
            
            // Tenta matching automático
            $matchingResult = $this->matchingAutomatico($conciliacaoId);
            
            $this->db->commit();
            
            return [
                'conciliacao_id' => $conciliacaoId,
                'transacoes_importadas' => $transacoesImportadas,
                'total_creditos' => $totalCreditos,
                'total_debitos' => $totalDebitos,
                'matching_automatico' => $matchingResult
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao importar OFX: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Realiza matching automático de transações bancárias com lançamentos
     * 
     * @param int $conciliacaoId ID da conciliação
     * @return array Resultado do matching
     */
    public function matchingAutomatico($conciliacaoId)
    {
        try {
            $conciliacao = $this->findById($conciliacaoId);
            if (!$conciliacao) {
                throw new \Exception("Conciliação não encontrada");
            }
            
            // Busca itens não conciliados
            $itens = $this->getItensPendentes($conciliacaoId);
            
            $matcheados = 0;
            $naoMatcheados = 0;
            
            foreach ($itens as $item) {
                // Busca lançamentos financeiros compatíveis
                $lancamentos = $this->buscarLancamentosCompativeis($item, $conciliacao);
                
                if (count($lancamentos) === 1) {
                    // Match exato encontrado
                    if ($this->vincularLancamento($item['id'], $lancamentos[0]['id'])) {
                        $matcheados++;
                    }
                } else {
                    $naoMatcheados++;
                }
            }
            
            // Atualiza status da conciliação
            $this->atualizarStatus($conciliacaoId);
            
            return [
                'matcheados' => $matcheados,
                'nao_matcheados' => $naoMatcheados,
                'total_itens' => count($itens)
            ];
            
        } catch (\Exception $e) {
            error_log("Erro no matching automático: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Vincula manualmente um item de conciliação com um lançamento
     * 
     * @param int $itemId ID do item de conciliação
     * @param int $lancamentoId ID do lançamento financeiro
     * @return bool Sucesso da operação
     */
    public function vincularLancamento($itemId, $lancamentoId)
    {
        try {
            $sql = "UPDATE reconciliacoes_bancarias_itens 
                    SET lancamento_financeiro_id = :lancamento_id,
                        conciliado = TRUE,
                        data_conciliacao = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id' => $itemId,
                'lancamento_id' => $lancamentoId
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao vincular lançamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Desvincular um item de conciliação
     * 
     * @param int $itemId ID do item
     * @return bool Sucesso da operação
     */
    public function desvincularLancamento($itemId)
    {
        try {
            $sql = "UPDATE reconciliacoes_bancarias_itens 
                    SET lancamento_financeiro_id = NULL,
                        conciliado = FALSE,
                        data_conciliacao = NULL
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute(['id' => $itemId]);
            
        } catch (\Exception $e) {
            error_log("Erro ao desvincular lançamento: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cria item de conciliação (transação bancária)
     * 
     * @param array $data Dados do item
     * @return int|false ID do item criado
     */
    private function createItem(array $data)
    {
        try {
            $sql = "INSERT INTO reconciliacoes_bancarias_itens (
                reconciliacao_bancaria_id, data_transacao, tipo, valor,
                descricao, documento, fitid, lancamento_financeiro_id,
                conciliado, criado_em
            ) VALUES (
                :reconciliacao_bancaria_id, :data_transacao, :tipo, :valor,
                :descricao, :documento, :fitid, :lancamento_financeiro_id,
                :conciliado, NOW()
            )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'reconciliacao_bancaria_id' => $data['reconciliacao_bancaria_id'],
                'data_transacao' => $data['data_transacao'],
                'tipo' => $data['tipo'],
                'valor' => $data['valor'],
                'descricao' => $data['descricao'],
                'documento' => $data['documento'] ?? null,
                'fitid' => $data['fitid'] ?? null,
                'lancamento_financeiro_id' => $data['lancamento_financeiro_id'] ?? null,
                'conciliado' => $data['conciliado'] ?? false
            ];
            
            if ($stmt->execute($params)) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("Erro ao criar item de conciliação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca conciliação por ID
     * 
     * @param int $id ID da conciliação
     * @return array|false Dados da conciliação ou false se não encontrado
     */
    public function findById($id)
    {
        $sql = "SELECT r.*,
                cb.numero_conta,
                cb.banco_nome,
                u.nome as criador_nome,
                (SELECT COUNT(*) FROM reconciliacoes_bancarias_itens 
                 WHERE reconciliacao_bancaria_id = r.id) as total_itens,
                (SELECT COUNT(*) FROM reconciliacoes_bancarias_itens 
                 WHERE reconciliacao_bancaria_id = r.id AND conciliado = TRUE) as itens_conciliados
                FROM {$this->table} r
                LEFT JOIN contas_bancarias cb ON r.conta_bancaria_id = cb.id
                LEFT JOIN usuarios u ON r.criado_por = u.id
                WHERE r.id = :id AND r.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista conciliações com filtros e paginação
     * 
     * @param array $filtros Filtros a aplicar
     * @param int $page Página atual
     * @param int $limit Itens por página
     * @return array Array com 'data' e 'total'
     */
    public function all(array $filtros = [], $page = 1, $limit = 50)
    {
        $where = ["r.ativo = TRUE"];
        $params = [];
        
        // Filtro por conta bancária
        if (!empty($filtros['conta_bancaria_id'])) {
            $where[] = "r.conta_bancaria_id = :conta_bancaria_id";
            $params['conta_bancaria_id'] = $filtros['conta_bancaria_id'];
        }
        
        // Filtro por status
        if (!empty($filtros['status'])) {
            $where[] = "r.status = :status";
            $params['status'] = $filtros['status'];
        }
        
        // Filtro por período
        if (!empty($filtros['data_inicio'])) {
            $where[] = "r.data_inicio >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "r.data_fim <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Query de contagem
        $sqlCount = "SELECT COUNT(*) as total
                    FROM {$this->table} r
                    WHERE {$whereClause}";
        
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query de dados
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT r.*,
                cb.numero_conta,
                cb.banco_nome,
                (SELECT COUNT(*) FROM reconciliacoes_bancarias_itens 
                 WHERE reconciliacao_bancaria_id = r.id) as total_itens,
                (SELECT COUNT(*) FROM reconciliacoes_bancarias_itens 
                 WHERE reconciliacao_bancaria_id = r.id AND conciliado = TRUE) as itens_conciliados
                FROM {$this->table} r
                LEFT JOIN contas_bancarias cb ON r.conta_bancaria_id = cb.id
                WHERE {$whereClause}
                ORDER BY r.data_conciliacao DESC, r.criado_em DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $conciliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $conciliacoes,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Atualiza uma conciliação
     * 
     * @param int $id ID da conciliação
     * @param array $data Dados a atualizar
     * @return bool Sucesso da operação
     */
    public function update($id, array $data)
    {
        try {
            $campos = [];
            $params = ['id' => $id];
            
            $camposPermitidos = [
                'saldo_inicial', 'saldo_final', 'saldo_conciliado',
                'total_creditos', 'total_debitos', 'total_divergencias',
                'status', 'observacoes'
            ];
            
            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $data)) {
                    $campos[] = "{$campo} = :{$campo}";
                    $params[$campo] = $data[$campo];
                }
            }
            
            if (empty($campos)) {
                return true;
            }
            
            $campos[] = "atualizado_por = :atualizado_por";
            $campos[] = "atualizado_em = NOW()";
            $params['atualizado_por'] = $data['atualizado_por'] ?? $_SESSION['user_id'] ?? null;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar conciliação: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca itens pendentes de conciliação
     * 
     * @param int $conciliacaoId ID da conciliação
     * @return array Lista de itens pendentes
     */
    public function getItensPendentes($conciliacaoId)
    {
        $sql = "SELECT * FROM reconciliacoes_bancarias_itens
                WHERE reconciliacao_bancaria_id = :conciliacao_id
                AND conciliado = FALSE
                ORDER BY data_transacao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conciliacao_id' => $conciliacaoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca todos os itens de uma conciliação
     * 
     * @param int $conciliacaoId ID da conciliação
     * @return array Lista de itens
     */
    public function getItens($conciliacaoId)
    {
        $sql = "SELECT i.*,
                l.descricao as lancamento_descricao,
                l.documento as lancamento_documento
                FROM reconciliacoes_bancarias_itens i
                LEFT JOIN lancamentos_financeiros l ON i.lancamento_financeiro_id = l.id
                WHERE i.reconciliacao_bancaria_id = :conciliacao_id
                ORDER BY i.data_transacao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conciliacao_id' => $conciliacaoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca lançamentos financeiros compatíveis com uma transação bancária
     * 
     * @param array $item Dados do item de conciliação
     * @param array $conciliacao Dados da conciliação
     * @return array Lista de lançamentos compatíveis
     */
    private function buscarLancamentosCompativeis($item, $conciliacao)
    {
        // Margem de tolerância em dias
        $margemDias = 3;
        
        // Determina tipo de lançamento baseado no tipo da transação
        $tipoLancamento = ($item['tipo'] === self::TIPO_CREDITO) ? 'entrada' : 'saida';
        
        $sql = "SELECT l.* 
                FROM lancamentos_financeiros l
                WHERE l.conta_bancaria_id = :conta_bancaria_id
                AND l.tipo = :tipo
                AND ABS(l.valor - :valor) < 0.01
                AND l.data_lancamento BETWEEN DATE_SUB(:data_transacao, INTERVAL :margem DAY) 
                                          AND DATE_ADD(:data_transacao, INTERVAL :margem DAY)
                AND l.status = 'confirmado'
                AND l.ativo = TRUE
                AND NOT EXISTS (
                    SELECT 1 FROM reconciliacoes_bancarias_itens ri
                    WHERE ri.lancamento_financeiro_id = l.id
                    AND ri.conciliado = TRUE
                )
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conta_bancaria_id' => $conciliacao['conta_bancaria_id'],
            'tipo' => $tipoLancamento,
            'valor' => $item['valor'],
            'data_transacao' => $item['data_transacao'],
            'margem' => $margemDias
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualiza status da conciliação baseado nos itens
     * 
     * @param int $conciliacaoId ID da conciliação
     * @return void
     */
    private function atualizarStatus($conciliacaoId)
    {
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN conciliado = TRUE THEN 1 END) as conciliados
                FROM reconciliacoes_bancarias_itens
                WHERE reconciliacao_bancaria_id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $conciliacaoId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats['total'] == 0) {
            $status = self::STATUS_PENDENTE;
        } elseif ($stats['conciliados'] == 0) {
            $status = self::STATUS_PENDENTE;
        } elseif ($stats['conciliados'] == $stats['total']) {
            $status = self::STATUS_CONCILIADA;
        } else {
            $status = self::STATUS_PARCIAL;
        }
        
        $this->update($conciliacaoId, ['status' => $status]);
    }
    
    /**
     * Processa arquivo OFX e extrai dados
     * 
     * @param string $arquivoPath Caminho do arquivo OFX
     * @return array Dados extraídos do OFX
     */
    private function processarArquivoOFX($arquivoPath)
    {
        try {
            // Lê conteúdo do arquivo
            $conteudo = file_get_contents($arquivoPath);
            
            if ($conteudo === false) {
                throw new \Exception("Erro ao ler arquivo OFX");
            }
            
            // Remove cabeçalho SGML se existir
            $conteudo = preg_replace('/<\?OFX.*?\?>/s', '', $conteudo);
            
            // Carrega XML
            $xml = simplexml_load_string($conteudo);
            
            if ($xml === false) {
                throw new \Exception("Erro ao processar XML do OFX");
            }
            
            // Extrai dados do extrato
            $stmtrs = $xml->BANKMSGSRSV1->STMTTRNRS->STMTRS ?? null;
            
            if (!$stmtrs) {
                throw new \Exception("Estrutura OFX inválida");
            }
            
            // Extrai período
            $dataInicio = $this->parseOFXDate((string)$stmtrs->BANKTRANLIST->DTSTART);
            $dataFim = $this->parseOFXDate((string)$stmtrs->BANKTRANLIST->DTEND);
            
            // Extrai saldos
            $saldoInicial = (float)$stmtrs->BALAMT ?? 0;
            $saldoFinal = (float)$stmtrs->LEDGERBAL->BALAMT ?? 0;
            
            // Extrai transações
            $transacoes = [];
            
            if (isset($stmtrs->BANKTRANLIST->STMTTRN)) {
                foreach ($stmtrs->BANKTRANLIST->STMTTRN as $trn) {
                    $valor = (float)$trn->TRNAMT;
                    
                    $transacoes[] = [
                        'data' => $this->parseOFXDate((string)$trn->DTPOSTED),
                        'tipo' => ($valor > 0) ? self::TIPO_CREDITO : self::TIPO_DEBITO,
                        'valor' => abs($valor),
                        'descricao' => trim((string)$trn->MEMO),
                        'documento' => trim((string)$trn->CHECKNUM),
                        'fitid' => trim((string)$trn->FITID)
                    ];
                }
            }
            
            return [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'saldo_inicial' => $saldoInicial,
                'saldo_final' => $saldoFinal,
                'transacoes' => $transacoes
            ];
            
        } catch (\Exception $e) {
            error_log("Erro ao processar OFX: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Converte data do formato OFX para MySQL
     * 
     * @param string $ofxDate Data no formato OFX (YYYYMMDDHHMMSS)
     * @return string Data no formato MySQL (YYYY-MM-DD)
     */
    private function parseOFXDate($ofxDate)
    {
        // Formato OFX: YYYYMMDDHHMMSS ou YYYYMMDD
        $ofxDate = preg_replace('/[^0-9]/', '', $ofxDate);
        
        if (strlen($ofxDate) >= 8) {
            $year = substr($ofxDate, 0, 4);
            $month = substr($ofxDate, 4, 2);
            $day = substr($ofxDate, 6, 2);
            
            return "{$year}-{$month}-{$day}";
        }
        
        return date('Y-m-d');
    }
    
    /**
     * Valida dados obrigatórios
     * 
     * @param array $data Dados a validar
     * @throws \Exception Se validação falhar
     */
    private function validarDados(array $data)
    {
        if (empty($data['conta_bancaria_id'])) {
            throw new \Exception("Conta bancária é obrigatória");
        }
        
        if (empty($data['data_inicio'])) {
            throw new \Exception("Data de início é obrigatória");
        }
        
        if (empty($data['data_fim'])) {
            throw new \Exception("Data de fim é obrigatória");
        }
    }
    
    /**
     * Soft delete de uma conciliação
     * 
     * @param int $id ID da conciliação
     * @return bool Sucesso da operação
     */
    public function delete($id)
    {
        try {
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
            error_log("Erro ao excluir conciliação: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtém estatísticas de conciliações
     * 
     * @param array $filtros Filtros opcionais
     * @return array Estatísticas
     */
    public function getEstatisticas(array $filtros = [])
    {
        $where = ["ativo = TRUE"];
        $params = [];
        
        if (!empty($filtros['conta_bancaria_id'])) {
            $where[] = "conta_bancaria_id = :conta_bancaria_id";
            $params['conta_bancaria_id'] = $filtros['conta_bancaria_id'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where[] = "data_conciliacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "data_conciliacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'parcial' THEN 1 END) as parciais,
                COUNT(CASE WHEN status = 'conciliada' THEN 1 END) as conciliadas,
                COUNT(CASE WHEN status = 'divergente' THEN 1 END) as divergentes,
                SUM(total_creditos) as total_creditos_geral,
                SUM(total_debitos) as total_debitos_geral,
                SUM(total_divergencias) as total_divergencias_geral
                FROM {$this->table}
                WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
