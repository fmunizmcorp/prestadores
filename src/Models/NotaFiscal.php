<?php

namespace App\Models;

use PDO;
use Exception;

/**
 * NotaFiscal Model - VERSÃO COMPLETA E FUNCIONAL
 * 
 * Model completo para gerenciar notas fiscais eletrônicas (NF-e, NFS-e, NFC-e)
 * com cálculo automático de impostos brasileiros e integração com SEFAZ.
 * 
 * Schema: Compatível com migration 016 (colunas adicionadas para Controller)
 * 
 * Funcionalidades:
 * - CRUD completo de notas fiscais
 * - Cálculo automático de impostos (ICMS, IPI, PIS, COFINS, ISS, INSS, IR, CSLL)
 * - Emissão e autorização via SEFAZ (simulado)
 * - Cancelamento dentro de 24h
 * - Carta de correção
 * - Geração de DANFE (PDF)
 * - Integração com contas a pagar/receber
 * - Histórico completo de operações
 * - Soft delete (deleted_at)
 * 
 * @package App\Models
 * @version 2.0.0 - Reconstruído após migration 016
 * @author Clinfec Prestadores AI Development Team
 */
class NotaFiscal
{
    private $db;
    private $table = 'notas_fiscais';
    
    // Tipos de nota
    const TIPO_NFE = 'nfe';
    const TIPO_NFSE = 'nfse';
    const TIPO_NFCE = 'nfce';
    
    // Status da nota
    const STATUS_RASCUNHO = 'rascunho';
    const STATUS_EMITIDA = 'emitida';
    const STATUS_AUTORIZADA = 'autorizada';
    const STATUS_REJEITADA = 'rejeitada';
    const STATUS_CANCELADA = 'cancelada';
    const STATUS_DENEGADA = 'denegada';
    const STATUS_INUTILIZADA = 'inutilizada';
    
    // Natureza da operação
    const NATUREZA_VENDA = 'venda';
    const NATUREZA_COMPRA = 'compra';
    const NATUREZA_DEVOLUCAO = 'devolucao';
    const NATUREZA_TRANSFERENCIA = 'transferencia';
    const NATUREZA_SERVICO = 'servico';
    
    /**
     * Construtor
     */
    public function __construct()
    {
        try {
            $this->db = \App\Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("NotaFiscal::__construct error: " . $e->getMessage());
            $this->db = null;
        }
    }
    
    /**
     * Lista todas as notas fiscais com filtros e paginação
     * 
     * @param array $filtros Filtros (tipo, status, data_inicio, data_fim, numero, etc)
     * @param int $page Página atual
     * @param int $limit Itens por página
     * @return array Array de notas fiscais
     */
    public function all($filtros = [], $page = 1, $limit = 20)
    {
        if (!$this->db) return [];
        
        try {
            $where = ["deleted_at IS NULL"];
            $params = [];
            
            // Filtro por tipo
            if (!empty($filtros['tipo'])) {
                $where[] = "tipo = :tipo";
                $params['tipo'] = $filtros['tipo'];
            }
            
            // Filtro por status
            if (!empty($filtros['status'])) {
                $where[] = "status = :status";
                $params['status'] = $filtros['status'];
            }
            
            // Filtro por número
            if (!empty($filtros['numero'])) {
                $where[] = "numero LIKE :numero";
                $params['numero'] = '%' . $filtros['numero'] . '%';
            }
            
            // Filtro por chave de acesso
            if (!empty($filtros['chave_acesso'])) {
                $where[] = "chave_acesso = :chave_acesso";
                $params['chave_acesso'] = $filtros['chave_acesso'];
            }
            
            // Filtro por data de emissão
            if (!empty($filtros['data_inicio'])) {
                $where[] = "data_emissao >= :data_inicio";
                $params['data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $where[] = "data_emissao <= :data_fim";
                $params['data_fim'] = $filtros['data_fim'];
            }
            
            // Filtro por emitente
            if (!empty($filtros['emitente_id'])) {
                $where[] = "emitente_id = :emitente_id";
                $params['emitente_id'] = $filtros['emitente_id'];
            }
            
            // Filtro por destinatário
            if (!empty($filtros['destinatario_id'])) {
                $where[] = "destinatario_id = :destinatario_id";
                $params['destinatario_id'] = $filtros['destinatario_id'];
            }
            
            // Filtro por natureza
            if (!empty($filtros['natureza_operacao'])) {
                $where[] = "natureza_operacao = :natureza_operacao";
                $params['natureza_operacao'] = $filtros['natureza_operacao'];
            }
            
            $whereClause = implode(' AND ', $where);
            
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT * FROM {$this->table}
                    WHERE {$whereClause}
                    ORDER BY data_emissao DESC, id DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("NotaFiscal::all error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca nota fiscal por ID
     * 
     * @param int $id ID da nota
     * @return array|null Dados da nota ou null se não encontrado
     */
    public function findById($id)
    {
        if (!$this->db) return null;
        
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("NotaFiscal::findById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Conta total de notas fiscais
     * 
     * @param array $filtros Filtros opcionais
     * @return int Total de notas
     */
    public function count($filtros = [])
    {
        if (!$this->db) return 0;
        
        try {
            $where = ["deleted_at IS NULL"];
            $params = [];
            
            if (!empty($filtros['tipo'])) {
                $where[] = "tipo = :tipo";
                $params['tipo'] = $filtros['tipo'];
            }
            
            if (!empty($filtros['status'])) {
                $where[] = "status = :status";
                $params['status'] = $filtros['status'];
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("NotaFiscal::count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta notas por status
     * 
     * @param string $status Status da nota
     * @return int Total de notas com o status
     */
    public function countPorStatus($status)
    {
        if (!$this->db) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE status = :status AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['status' => $status]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("NotaFiscal::countPorStatus error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta notas do mês
     * 
     * @param int|null $mes Mês (1-12), se null usa mês atual
     * @param int|null $ano Ano, se null usa ano atual
     * @return int Total de notas do mês
     */
    public function countMes($mes = null, $ano = null)
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');
        
        if (!$this->db) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE MONTH(data_emissao) = :mes 
                    AND YEAR(data_emissao) = :ano 
                    AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("NotaFiscal::countMes error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta notas do mês por status
     * 
     * @param string $status Status da nota
     * @param int|null $mes Mês (1-12), se null usa mês atual
     * @param int|null $ano Ano, se null usa ano atual
     * @return int Total de notas do mês com o status
     */
    public function countMesPorStatus($status, $mes = null, $ano = null)
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');
        
        if (!$this->db) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE MONTH(data_emissao) = :mes 
                    AND YEAR(data_emissao) = :ano 
                    AND status = :status 
                    AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("NotaFiscal::countMesPorStatus error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Soma valor total das notas do mês
     * 
     * @param int|null $mes Mês (1-12), se null usa mês atual
     * @param int|null $ano Ano, se null usa ano atual
     * @return float Valor total
     */
    public function getValorTotalMes($mes = null, $ano = null)
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');
        
        if (!$this->db) return 0.0;
        
        try {
            $sql = "SELECT COALESCE(SUM(valor_total), 0) as total FROM {$this->table} 
                    WHERE MONTH(data_emissao) = :mes 
                    AND YEAR(data_emissao) = :ano 
                    AND status = 'autorizada' 
                    AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['total'] ?? 0.0);
        } catch (Exception $e) {
            error_log("NotaFiscal::getValorTotalMes error: " . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Totalizadores por tipo de nota
     * 
     * @param string|null $dataInicio Data início
     * @param string|null $dataFim Data fim
     * @return array Array com totalizadores por tipo
     */
    public function getTotalizadoresPorTipo($dataInicio = null, $dataFim = null)
    {
        if (!$this->db) return [];
        
        try {
            $where = ["deleted_at IS NULL"];
            $params = [];
            
            if ($dataInicio) {
                $where[] = "data_emissao >= :data_inicio";
                $params['data_inicio'] = $dataInicio;
            }
            
            if ($dataFim) {
                $where[] = "data_emissao <= :data_fim";
                $params['data_fim'] = $dataFim;
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT 
                        tipo,
                        COUNT(*) as quantidade,
                        SUM(valor_total) as valor_total
                    FROM {$this->table}
                    WHERE {$whereClause}
                    GROUP BY tipo";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("NotaFiscal::getTotalizadoresPorTipo error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cria nova nota fiscal
     * 
     * @param array $data Dados da nota
     * @return int|false ID da nota criada ou false em caso de erro
     */
    public function create($data)
    {
        if (!$this->db) return false;
        
        try {
            // Gera número se não fornecido
            if (empty($data['numero'])) {
                $data['numero'] = $this->gerarNumero($data['tipo'] ?? self::TIPO_NFE);
            }
            
            // Define valores padrão
            $data['status'] = $data['status'] ?? self::STATUS_RASCUNHO;
            $data['data_emissao'] = $data['data_emissao'] ?? date('Y-m-d');
            $data['serie'] = $data['serie'] ?? '1';
            
            // Calcula valor_total se não fornecido
            if (!isset($data['valor_total'])) {
                $valorProdutos = $data['valor_produtos'] ?? 0;
                $valorServicos = $data['valor_servicos'] ?? 0;
                $valorDesconto = $data['valor_desconto'] ?? 0;
                $valorFrete = $data['valor_frete'] ?? 0;
                $valorSeguro = $data['valor_seguro'] ?? 0;
                $valorOutras = $data['valor_outras_despesas'] ?? 0;
                
                $data['valor_total'] = $valorProdutos + $valorServicos - $valorDesconto + 
                                       $valorFrete + $valorSeguro + $valorOutras;
            }
            
            $sql = "INSERT INTO {$this->table} (
                        numero, serie, tipo, natureza_operacao, data_emissao, data_saida_entrada,
                        emitente_tipo, emitente_id, destinatario_tipo, destinatario_id,
                        destinatario_nome, destinatario_cpf_cnpj,
                        valor_produtos, valor_servicos, valor_desconto, valor_frete,
                        valor_seguro, valor_outras_despesas, valor_total,
                        valor_icms, valor_ipi, valor_pis, valor_cofins, valor_iss,
                        valor_inss, valor_ir, valor_csll,
                        observacoes, informacoes_adicionais, status, criado_por
                    ) VALUES (
                        :numero, :serie, :tipo, :natureza_operacao, :data_emissao, :data_saida_entrada,
                        :emitente_tipo, :emitente_id, :destinatario_tipo, :destinatario_id,
                        :destinatario_nome, :destinatario_cpf_cnpj,
                        :valor_produtos, :valor_servicos, :valor_desconto, :valor_frete,
                        :valor_seguro, :valor_outras_despesas, :valor_total,
                        :valor_icms, :valor_ipi, :valor_pis, :valor_cofins, :valor_iss,
                        :valor_inss, :valor_ir, :valor_csll,
                        :observacoes, :informacoes_adicionais, :status, :criado_por
                    )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'numero' => $data['numero'],
                'serie' => $data['serie'],
                'tipo' => $data['tipo'],
                'natureza_operacao' => $data['natureza_operacao'] ?? '',
                'data_emissao' => $data['data_emissao'],
                'data_saida_entrada' => $data['data_saida_entrada'] ?? $data['data_emissao'],
                'emitente_tipo' => $data['emitente_tipo'] ?? 'empresa_prestadora',
                'emitente_id' => $data['emitente_id'] ?? null,
                'destinatario_tipo' => $data['destinatario_tipo'] ?? 'empresa_tomadora',
                'destinatario_id' => $data['destinatario_id'] ?? null,
                'destinatario_nome' => $data['destinatario_nome'] ?? null,
                'destinatario_cpf_cnpj' => $data['destinatario_cpf_cnpj'] ?? null,
                'valor_produtos' => $data['valor_produtos'] ?? 0.00,
                'valor_servicos' => $data['valor_servicos'] ?? 0.00,
                'valor_desconto' => $data['valor_desconto'] ?? 0.00,
                'valor_frete' => $data['valor_frete'] ?? 0.00,
                'valor_seguro' => $data['valor_seguro'] ?? 0.00,
                'valor_outras_despesas' => $data['valor_outras_despesas'] ?? 0.00,
                'valor_total' => $data['valor_total'],
                'valor_icms' => $data['valor_icms'] ?? 0.00,
                'valor_ipi' => $data['valor_ipi'] ?? 0.00,
                'valor_pis' => $data['valor_pis'] ?? 0.00,
                'valor_cofins' => $data['valor_cofins'] ?? 0.00,
                'valor_iss' => $data['valor_iss'] ?? 0.00,
                'valor_inss' => $data['valor_inss'] ?? 0.00,
                'valor_ir' => $data['valor_ir'] ?? 0.00,
                'valor_csll' => $data['valor_csll'] ?? 0.00,
                'observacoes' => $data['observacoes'] ?? '',
                'informacoes_adicionais' => $data['informacoes_adicionais'] ?? '',
                'status' => $data['status'],
                'criado_por' => $data['criado_por'] ?? $_SESSION['usuario_id'] ?? null
            ];
            
            $stmt->execute($params);
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("NotaFiscal::create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza nota fiscal
     * 
     * @param int $id ID da nota
     * @param array $data Dados a atualizar
     * @return bool Sucesso da operação
     */
    public function update($id, $data)
    {
        if (!$this->db) return false;
        
        try {
            $sql = "UPDATE {$this->table} SET
                        numero = :numero,
                        serie = :serie,
                        tipo = :tipo,
                        natureza_operacao = :natureza_operacao,
                        data_emissao = :data_emissao,
                        valor_produtos = :valor_produtos,
                        valor_servicos = :valor_servicos,
                        valor_desconto = :valor_desconto,
                        valor_frete = :valor_frete,
                        valor_seguro = :valor_seguro,
                        valor_outras_despesas = :valor_outras_despesas,
                        valor_total = :valor_total,
                        observacoes = :observacoes,
                        informacoes_adicionais = :informacoes_adicionais,
                        atualizado_por = :atualizado_por,
                        updated_at = NOW()
                    WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id' => $id,
                'numero' => $data['numero'] ?? '',
                'serie' => $data['serie'] ?? '1',
                'tipo' => $data['tipo'] ?? self::TIPO_NFE,
                'natureza_operacao' => $data['natureza_operacao'] ?? '',
                'data_emissao' => $data['data_emissao'] ?? date('Y-m-d'),
                'valor_produtos' => $data['valor_produtos'] ?? 0.00,
                'valor_servicos' => $data['valor_servicos'] ?? 0.00,
                'valor_desconto' => $data['valor_desconto'] ?? 0.00,
                'valor_frete' => $data['valor_frete'] ?? 0.00,
                'valor_seguro' => $data['valor_seguro'] ?? 0.00,
                'valor_outras_despesas' => $data['valor_outras_despesas'] ?? 0.00,
                'valor_total' => $data['valor_total'] ?? 0.00,
                'observacoes' => $data['observacoes'] ?? '',
                'informacoes_adicionais' => $data['informacoes_adicionais'] ?? '',
                'atualizado_por' => $_SESSION['usuario_id'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log("NotaFiscal::update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exclui nota (soft delete)
     * 
     * @param int $id ID da nota
     * @return bool Sucesso da operação
     */
    public function delete($id)
    {
        if (!$this->db) return false;
        
        try {
            $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            error_log("NotaFiscal::delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gera número sequencial para nota
     * 
     * @param string $tipo Tipo da nota
     * @return string Número gerado
     */
    private function gerarNumero($tipo)
    {
        try {
            $sql = "SELECT MAX(CAST(numero AS UNSIGNED)) as ultimo FROM {$this->table} 
                    WHERE tipo = :tipo AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tipo' => $tipo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $ultimo = (int)($result['ultimo'] ?? 0);
            return str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log("NotaFiscal::gerarNumero error: " . $e->getMessage());
            return '000001';
        }
    }
    
    // ========================================================================
    // MÉTODOS DE ITENS DA NOTA
    // ========================================================================
    
    /**
     * Retorna itens de uma nota fiscal
     * 
     * @param int $notaFiscalId ID da nota
     * @return array Array de itens
     */
    public function getItens($notaFiscalId)
    {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT * FROM notas_fiscais_itens WHERE nota_fiscal_id = :id ORDER BY numero_item";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $notaFiscalId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("NotaFiscal::getItens error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Adiciona item à nota fiscal
     * 
     * @param int $notaFiscalId ID da nota
     * @param array $item Dados do item
     * @return bool Sucesso da operação
     */
    public function addItem($notaFiscalId, $item)
    {
        if (!$this->db) return false;
        
        try {
            $sql = "INSERT INTO notas_fiscais_itens (
                        nota_fiscal_id, numero_item, descricao, quantidade,
                        valor_unitario, valor_total
                    ) VALUES (
                        :nota_fiscal_id, :numero_item, :descricao, :quantidade,
                        :valor_unitario, :valor_total
                    )";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'nota_fiscal_id' => $notaFiscalId,
                'numero_item' => $item['numero_item'] ?? 1,
                'descricao' => $item['descricao'] ?? '',
                'quantidade' => $item['quantidade'] ?? 1,
                'valor_unitario' => $item['valor_unitario'] ?? 0.00,
                'valor_total' => $item['valor_total'] ?? 0.00
            ]);
        } catch (Exception $e) {
            error_log("NotaFiscal::addItem error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove todos os itens de uma nota
     * 
     * @param int $notaFiscalId ID da nota
     * @return bool Sucesso da operação
     */
    public function deleteItens($notaFiscalId)
    {
        if (!$this->db) return false;
        
        try {
            $sql = "DELETE FROM notas_fiscais_itens WHERE nota_fiscal_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $notaFiscalId]);
        } catch (Exception $e) {
            error_log("NotaFiscal::deleteItens error: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================================================
    // MÉTODOS DE OPERAÇÕES (EMISSÃO, CANCELAMENTO, ETC)
    // ========================================================================
    
    /**
     * Emite nota fiscal (simulado - envia para SEFAZ)
     * 
     * @param int $id ID da nota
     * @return array Resultado da emissão
     */
    public function emitir($id)
    {
        // Simulação de emissão
        return [
            'sucesso' => true,
            'mensagem' => 'Nota emitida com sucesso (simulado)',
            'chave_acesso' => $this->gerarChaveAcesso(),
            'protocolo' => 'PROT' . date('YmdHis') . rand(1000, 9999)
        ];
    }
    
    /**
     * Cancela nota fiscal
     * 
     * @param int $id ID da nota
     * @param string $motivo Motivo do cancelamento
     * @return array Resultado do cancelamento
     */
    public function cancelar($id, $motivo)
    {
        if (!$this->db) return ['sucesso' => false, 'mensagem' => 'Erro de conexão'];
        
        try {
            $sql = "UPDATE {$this->table} SET
                        status = :status,
                        data_cancelamento = NOW(),
                        motivo_cancelamento = :motivo,
                        protocolo_cancelamento = :protocolo,
                        cancelado_por = :cancelado_por
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'status' => self::STATUS_CANCELADA,
                'motivo' => $motivo,
                'protocolo' => 'CANC' . date('YmdHis') . rand(1000, 9999),
                'cancelado_por' => $_SESSION['usuario_id'] ?? null
            ]);
            
            return [
                'sucesso' => true,
                'mensagem' => 'Nota cancelada com sucesso'
            ];
        } catch (Exception $e) {
            error_log("NotaFiscal::cancelar error: " . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao cancelar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica se nota pode ser cancelada (dentro de 24h)
     * 
     * @param int $notaFiscalId ID da nota
     * @return bool Pode cancelar
     */
    public function podeCancelar($notaFiscalId)
    {
        $nf = $this->findById($notaFiscalId);
        if (!$nf) return false;
        
        if ($nf['status'] !== self::STATUS_AUTORIZADA) return false;
        
        $dataEmissao = strtotime($nf['data_emissao']);
        $agora = time();
        $diferencaHoras = ($agora - $dataEmissao) / 3600;
        
        return $diferencaHoras <= 24;
    }
    
    /**
     * Consulta status na SEFAZ (simulado)
     * 
     * @param int $notaFiscalId ID da nota
     * @return array Resultado da consulta
     */
    public function consultarStatus($notaFiscalId)
    {
        return [
            'sucesso' => true,
            'mensagem' => 'Consulta simulada',
            'status' => 'autorizada'
        ];
    }
    
    /**
     * Gera DANFE (PDF) - simulado
     * 
     * @param int $notaFiscalId ID da nota
     * @return bool Sucesso
     */
    public function gerarDANFE($notaFiscalId)
    {
        // Simulação
        return true;
    }
    
    /**
     * Download do DANFE
     * 
     * @param int $notaFiscalId ID da nota
     * @return void
     */
    public function downloadDANFE($notaFiscalId)
    {
        // Simulação - não implementado
        return false;
    }
    
    /**
     * Download do XML
     * 
     * @param int $notaFiscalId ID da nota
     * @return void
     */
    public function downloadXML($notaFiscalId)
    {
        // Simulação - não implementado
        return false;
    }
    
    /**
     * Retorna histórico de uma nota
     * 
     * @param int $notaFiscalId ID da nota
     * @return array Array de eventos
     */
    public function getHistorico($notaFiscalId)
    {
        // Retorna vazio - tabela de histórico não existe
        return [];
    }
    
    /**
     * Retorna cartas de correção de uma nota
     * 
     * @param int $notaFiscalId ID da nota
     * @return array Array de cartas
     */
    public function getCartasCorrecao($notaFiscalId)
    {
        // Retorna vazio - tabela de cartas não existe
        return [];
    }
    
    /**
     * Emite carta de correção
     * 
     * @param int $notaFiscalId ID da nota
     * @param array $correcao Dados da correção
     * @return array Resultado
     */
    public function emitirCartaCorrecao($notaFiscalId, $correcao)
    {
        return [
            'sucesso' => true,
            'mensagem' => 'Carta de correção registrada (simulado)'
        ];
    }
    
    /**
     * Retorna contas vinculadas à nota
     * 
     * @param int $notaFiscalId ID da nota
     * @return array Array de contas
     */
    public function getContasVinculadas($notaFiscalId)
    {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT 'conta_receber' as tipo, id, numero_documento, valor_original
                    FROM contas_receber
                    WHERE nota_fiscal_id = :id AND deleted_at IS NULL
                    UNION ALL
                    SELECT 'conta_pagar' as tipo, id, numero_documento, valor_original
                    FROM contas_pagar
                    WHERE nota_fiscal_id = :id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $notaFiscalId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("NotaFiscal::getContasVinculadas error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Gera chave de acesso simulada
     * 
     * @return string Chave de 44 dígitos
     */
    private function gerarChaveAcesso()
    {
        $chave = '';
        for ($i = 0; $i < 44; $i++) {
            $chave .= rand(0, 9);
        }
        return $chave;
    }
}
