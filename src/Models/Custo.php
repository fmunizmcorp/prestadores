<?php

namespace App\Models;

use PDO;

/**
 * Custo Model
 * 
 * Gerencia custos operacionais e despesas gerais da empresa
 * Sprint 70.2 - Módulo de Custos
 * 
 * Diferente de projeto_custos (que são custos específicos de projetos),
 * este model gerencia custos operacionais gerais:
 * - Despesas administrativas
 * - Despesas com fornecedores
 * - Custos fixos e variáveis
 * - Despesas operacionais
 */
class Custo
{
    private $db;
    private $table = 'custos';
    
    // Tipos de Custo
    const TIPO_FIXO = 'fixo';
    const TIPO_VARIAVEL = 'variavel';
    const TIPO_OPERACIONAL = 'operacional';
    const TIPO_ADMINISTRATIVO = 'administrativo';
    const TIPO_FORNECEDOR = 'fornecedor';
    
    // Status
    const STATUS_PENDENTE = 'pendente';
    const STATUS_APROVADO = 'aprovado';
    const STATUS_PAGO = 'pago';
    const STATUS_CANCELADO = 'cancelado';
    
    /**
     * Construtor
     * SPRINT 73: Fix Bug #23 - Use Database::getInstance()
     */
    public function __construct()
    {
        $this->db = \App\Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os custos com filtros
     */
    public function all(array $filtros = [], $page = 1, $limit = 20)
    {
        $where = ["ativo = TRUE"];
        $params = [];
        
        if (!empty($filtros['tipo'])) {
            $where[] = "tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        
        if (!empty($filtros['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filtros['status'];
        }
        
        if (!empty($filtros['centro_custo_id'])) {
            $where[] = "centro_custo_id = :centro_custo_id";
            $params['centro_custo_id'] = $filtros['centro_custo_id'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where[] = "data_custo >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "data_custo <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Count
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Data
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT c.*, 
                cc.nome as centro_custo_nome,
                u.nome as criador_nome
                FROM {$this->table} c
                LEFT JOIN centros_custo cc ON c.centro_custo_id = cc.id
                LEFT JOIN usuarios u ON c.criado_por = u.id
                WHERE {$whereClause}
                ORDER BY c.data_custo DESC, c.criado_em DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $custos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $custos,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Buscar custo por ID
     */
    public function findById($id)
    {
        $sql = "SELECT c.*,
                cc.nome as centro_custo_nome,
                u.nome as criador_nome
                FROM {$this->table} c
                LEFT JOIN centros_custo cc ON c.centro_custo_id = cc.id
                LEFT JOIN usuarios u ON c.criado_por = u.id
                WHERE c.id = :id AND c.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar novo custo
     */
    public function create(array $data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (
                        tipo, descricao, valor, data_custo, centro_custo_id,
                        fornecedor, numero_documento, categoria, status,
                        observacoes, criado_por, criado_em
                    ) VALUES (
                        :tipo, :descricao, :valor, :data_custo, :centro_custo_id,
                        :fornecedor, :numero_documento, :categoria, :status,
                        :observacoes, :criado_por, NOW()
                    )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'tipo' => $data['tipo'] ?? self::TIPO_OPERACIONAL,
                'descricao' => $data['descricao'],
                'valor' => $data['valor'],
                'data_custo' => $data['data_custo'] ?? date('Y-m-d'),
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'fornecedor' => $data['fornecedor'] ?? null,
                'numero_documento' => $data['numero_documento'] ?? null,
                'categoria' => $data['categoria'] ?? null,
                'status' => $data['status'] ?? self::STATUS_PENDENTE,
                'observacoes' => $data['observacoes'] ?? '',
                'criado_por' => $data['criado_por'] ?? $_SESSION['usuario_id'] ?? null
            ];
            
            if ($stmt->execute($params)) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("Erro ao criar custo: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Atualizar custo
     */
    public function update($id, array $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET
                        tipo = :tipo,
                        descricao = :descricao,
                        valor = :valor,
                        data_custo = :data_custo,
                        centro_custo_id = :centro_custo_id,
                        fornecedor = :fornecedor,
                        numero_documento = :numero_documento,
                        categoria = :categoria,
                        status = :status,
                        observacoes = :observacoes,
                        atualizado_em = NOW()
                    WHERE id = :id AND ativo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'id' => $id,
                'tipo' => $data['tipo'],
                'descricao' => $data['descricao'],
                'valor' => $data['valor'],
                'data_custo' => $data['data_custo'],
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'fornecedor' => $data['fornecedor'] ?? null,
                'numero_documento' => $data['numero_documento'] ?? null,
                'categoria' => $data['categoria'] ?? null,
                'status' => $data['status'],
                'observacoes' => $data['observacoes'] ?? ''
            ];
            
            return $stmt->execute($params);
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar custo: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Aprovar custo
     */
    public function aprovar($id)
    {
        $sql = "UPDATE {$this->table} SET
                    status = :status,
                    data_aprovacao = NOW(),
                    atualizado_em = NOW()
                WHERE id = :id AND ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_APROVADO
        ]);
    }
    
    /**
     * Marcar como pago
     */
    public function marcarPago($id)
    {
        $sql = "UPDATE {$this->table} SET
                    status = :status,
                    data_pagamento = NOW(),
                    atualizado_em = NOW()
                WHERE id = :id AND ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_PAGO
        ]);
    }
    
    /**
     * Soft delete
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET 
                    ativo = FALSE,
                    atualizado_em = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Estatísticas
     */
    public function getEstatisticas(array $filtros = [])
    {
        $where = ["ativo = TRUE"];
        $params = [];
        
        if (!empty($filtros['data_inicio'])) {
            $where[] = "data_custo >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where[] = "data_custo <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'aprovado' THEN 1 END) as aprovados,
                COUNT(CASE WHEN status = 'pago' THEN 1 END) as pagos,
                SUM(CASE WHEN status = 'pago' THEN valor ELSE 0 END) as valor_total_pago,
                SUM(CASE WHEN status IN ('pendente', 'aprovado') THEN valor ELSE 0 END) as valor_total_pendente,
                SUM(valor) as valor_total_geral
                FROM {$this->table}
                WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
