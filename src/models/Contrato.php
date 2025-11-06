<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException;

/**
 * Model Contrato
 * Gerencia contratos entre empresas tomadoras e prestadoras
 */
class Contrato {
    
    private $db;
    private $table = 'contratos';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Buscar todos os contratos
     * 
     * @param array $filtros
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function all($filtros = [], $page = 1, $limit = 25) {
        $sql = "SELECT 
                    c.*,
                    et.nome_fantasia as tomadora_nome,
                    et.cidade as tomadora_cidade,
                    et.estado as tomadora_estado,
                    ep.nome_fantasia as prestadora_nome,
                    ep.cidade as prestadora_cidade,
                    DATEDIFF(c.data_fim, NOW()) as dias_vencimento,
                    (SELECT COUNT(*) FROM servico_valores sv WHERE sv.contrato_id = c.id AND sv.ativo = 1) as total_servicos
                FROM {$this->table} c
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtro: Status
        if (!empty($filtros['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filtros['status'];
        }
        
        // Filtro: Empresa Tomadora
        if (!empty($filtros['empresa_tomadora_id'])) {
            $sql .= " AND c.empresa_tomadora_id = :empresa_tomadora_id";
            $params[':empresa_tomadora_id'] = $filtros['empresa_tomadora_id'];
        }
        
        // Filtro: Empresa Prestadora
        if (!empty($filtros['empresa_prestadora_id'])) {
            $sql .= " AND c.empresa_prestadora_id = :empresa_prestadora_id";
            $params[':empresa_prestadora_id'] = $filtros['empresa_prestadora_id'];
        }
        
        // Filtro: Busca
        if (!empty($filtros['search'])) {
            $sql .= " AND (c.numero_contrato LIKE :search 
                         OR c.descricao LIKE :search 
                         OR et.nome_fantasia LIKE :search
                         OR ep.nome_fantasia LIKE :search)";
            $params[':search'] = '%' . $filtros['search'] . '%';
        }
        
        // Filtro: Vigência (contratos ativos em determinado período)
        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $sql .= " AND ((c.data_inicio BETWEEN :data_inicio AND :data_fim)
                         OR (c.data_fim BETWEEN :data_inicio AND :data_fim)
                         OR (c.data_inicio <= :data_inicio AND c.data_fim >= :data_fim))";
            $params[':data_inicio'] = $filtros['data_inicio'];
            $params[':data_fim'] = $filtros['data_fim'];
        }
        
        // Ordenação
        $orderBy = $filtros['order_by'] ?? 'c.created_at';
        $orderDir = $filtros['order_dir'] ?? 'DESC';
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        
        // Paginação
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Contar total de contratos
     * 
     * @param array $filtros
     * @return int
     */
    public function count($filtros = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} c
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filtros['status'];
        }
        
        if (!empty($filtros['empresa_tomadora_id'])) {
            $sql .= " AND c.empresa_tomadora_id = :empresa_tomadora_id";
            $params[':empresa_tomadora_id'] = $filtros['empresa_tomadora_id'];
        }
        
        if (!empty($filtros['empresa_prestadora_id'])) {
            $sql .= " AND c.empresa_prestadora_id = :empresa_prestadora_id";
            $params[':empresa_prestadora_id'] = $filtros['empresa_prestadora_id'];
        }
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (c.numero_contrato LIKE :search 
                         OR c.descricao LIKE :search)";
            $params[':search'] = '%' . $filtros['search'] . '%';
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar contrato por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT 
                    c.*,
                    et.nome_fantasia as tomadora_nome,
                    et.razao_social as tomadora_razao_social,
                    et.cnpj as tomadora_cnpj,
                    et.cidade as tomadora_cidade,
                    et.estado as tomadora_estado,
                    ep.nome_fantasia as prestadora_nome,
                    ep.razao_social as prestadora_razao_social,
                    ep.cnpj as prestadora_cnpj,
                    ep.cidade as prestadora_cidade,
                    ep.estado as prestadora_estado,
                    u1.nome as criado_por_nome,
                    u2.nome as atualizado_por_nome,
                    DATEDIFF(c.data_fim, NOW()) as dias_vencimento
                FROM {$this->table} c
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                LEFT JOIN usuarios u1 ON c.created_by = u1.id
                LEFT JOIN usuarios u2 ON c.updated_by = u2.id
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Buscar por número de contrato
     * 
     * @param string $numero
     * @param int|null $exceptId
     * @return array|null
     */
    public function findByNumero($numero, $exceptId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE numero_contrato = :numero";
        
        if ($exceptId) {
            $sql .= " AND id != :except_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':numero', $numero);
        
        if ($exceptId) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Criar novo contrato
     * 
     * @param array $data
     * @return int
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
                    empresa_tomadora_id, empresa_prestadora_id,
                    numero_contrato, descricao, objeto,
                    data_inicio, data_fim,
                    valor_total, valor_executado,
                    status, arquivo_contrato, observacoes,
                    created_by, created_at
                ) VALUES (
                    :empresa_tomadora_id, :empresa_prestadora_id,
                    :numero_contrato, :descricao, :objeto,
                    :data_inicio, :data_fim,
                    :valor_total, :valor_executado,
                    :status, :arquivo_contrato, :observacoes,
                    :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':empresa_tomadora_id', $data['empresa_tomadora_id'], PDO::PARAM_INT);
        $stmt->bindValue(':empresa_prestadora_id', $data['empresa_prestadora_id'], PDO::PARAM_INT);
        $stmt->bindValue(':numero_contrato', $data['numero_contrato']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':objeto', $data['objeto'] ?? null);
        $stmt->bindValue(':data_inicio', $data['data_inicio']);
        $stmt->bindValue(':data_fim', $data['data_fim'] ?? null);
        $stmt->bindValue(':valor_total', $data['valor_total'] ?? null);
        $stmt->bindValue(':valor_executado', $data['valor_executado'] ?? 0);
        $stmt->bindValue(':status', $data['status'] ?? 'rascunho');
        $stmt->bindValue(':arquivo_contrato', $data['arquivo_contrato'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        $contratoId = $this->db->lastInsertId();
        
        // Registrar no histórico
        $this->registrarHistorico($contratoId, 'criacao', null, null, null, 'Contrato criado');
        
        return $contratoId;
    }
    
    /**
     * Atualizar contrato
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET
                    empresa_tomadora_id = :empresa_tomadora_id,
                    empresa_prestadora_id = :empresa_prestadora_id,
                    numero_contrato = :numero_contrato,
                    descricao = :descricao,
                    objeto = :objeto,
                    data_inicio = :data_inicio,
                    data_fim = :data_fim,
                    valor_total = :valor_total,
                    status = :status,
                    arquivo_contrato = :arquivo_contrato,
                    observacoes = :observacoes,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_tomadora_id', $data['empresa_tomadora_id'], PDO::PARAM_INT);
        $stmt->bindValue(':empresa_prestadora_id', $data['empresa_prestadora_id'], PDO::PARAM_INT);
        $stmt->bindValue(':numero_contrato', $data['numero_contrato']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':objeto', $data['objeto'] ?? null);
        $stmt->bindValue(':data_inicio', $data['data_inicio']);
        $stmt->bindValue(':data_fim', $data['data_fim'] ?? null);
        $stmt->bindValue(':valor_total', $data['valor_total'] ?? null);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':arquivo_contrato', $data['arquivo_contrato'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':updated_by', $data['updated_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Alterar status do contrato
     * 
     * @param int $id
     * @param string $novoStatus
     * @param string|null $motivo
     * @return bool
     */
    public function alterarStatus($id, $novoStatus, $motivo = null) {
        $contratoAtual = $this->findById($id);
        
        if (!$contratoAtual) {
            throw new \Exception('Contrato não encontrado');
        }
        
        $sql = "UPDATE {$this->table} 
                SET status = :status, 
                    updated_by = :updated_by,
                    updated_at = NOW()";
        
        if ($novoStatus == 'encerrado' || $novoStatus == 'cancelado') {
            $sql .= ", motivo_encerramento = :motivo";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':status', $novoStatus);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        if ($novoStatus == 'encerrado' || $novoStatus == 'cancelado') {
            $stmt->bindValue(':motivo', $motivo);
        }
        
        $result = $stmt->execute();
        
        if ($result) {
            $this->registrarHistorico(
                $id, 
                $novoStatus == 'ativo' ? 'ativacao' : 
                ($novoStatus == 'suspenso' ? 'suspensao' : 
                ($novoStatus == 'encerrado' ? 'encerramento' : 'cancelamento')),
                'status',
                $contratoAtual['status'],
                $novoStatus,
                $motivo ?? "Status alterado para: {$novoStatus}"
            );
        }
        
        return $result;
    }
    
    /**
     * Encerrar contrato
     * 
     * @param int $id
     * @param string $motivo
     * @return bool
     */
    public function encerrar($id, $motivo) {
        return $this->alterarStatus($id, 'encerrado', $motivo);
    }
    
    /**
     * Cancelar contrato
     * 
     * @param int $id
     * @param string $motivo
     * @return bool
     */
    public function cancelar($id, $motivo) {
        return $this->alterarStatus($id, 'cancelado', $motivo);
    }
    
    /**
     * Ativar contrato
     * 
     * @param int $id
     * @return bool
     */
    public function ativar($id) {
        return $this->alterarStatus($id, 'ativo');
    }
    
    /**
     * Suspender contrato
     * 
     * @param int $id
     * @param string $motivo
     * @return bool
     */
    public function suspender($id, $motivo) {
        return $this->alterarStatus($id, 'suspenso', $motivo);
    }
    
    /**
     * Buscar contratos vigentes
     * 
     * @return array
     */
    public function getVigentes() {
        $sql = "SELECT c.*, 
                       et.nome_fantasia as tomadora_nome,
                       ep.nome_fantasia as prestadora_nome
                FROM {$this->table} c
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.status = 'ativo'
                  AND c.data_inicio <= CURDATE()
                  AND (c.data_fim IS NULL OR c.data_fim >= CURDATE())
                ORDER BY c.numero_contrato ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar contratos próximos ao vencimento
     * 
     * @param int $dias Dias antes do vencimento
     * @return array
     */
    public function getProximosVencimento($dias = 30) {
        $sql = "SELECT c.*, 
                       et.nome_fantasia as tomadora_nome,
                       ep.nome_fantasia as prestadora_nome,
                       DATEDIFF(c.data_fim, NOW()) as dias_restantes
                FROM {$this->table} c
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.status = 'ativo'
                  AND c.data_fim IS NOT NULL
                  AND DATEDIFF(c.data_fim, NOW()) <= :dias
                  AND DATEDIFF(c.data_fim, NOW()) >= 0
                ORDER BY c.data_fim ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar contratos vencidos
     * 
     * @return array
     */
    public function getVencidos() {
        $sql = "SELECT c.*, 
                       et.nome_fantasia as tomadora_nome,
                       ep.nome_fantasia as prestadora_nome,
                       DATEDIFF(NOW(), c.data_fim) as dias_vencido
                FROM {$this->table} c
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.status = 'ativo'
                  AND c.data_fim IS NOT NULL
                  AND c.data_fim < CURDATE()
                ORDER BY c.data_fim ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar valores de serviços do contrato
     * 
     * @param int $id
     * @return array
     */
    public function getValoresServicos($id) {
        $sql = "SELECT sv.*, s.nome as servico_nome
                FROM servico_valores sv
                INNER JOIN servicos s ON sv.servico_id = s.id
                WHERE sv.contrato_id = :id AND sv.ativo = 1
                ORDER BY s.nome ASC, sv.data_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar aditivos do contrato
     * 
     * @param int $id
     * @return array
     */
    public function getAditivos($id) {
        $sql = "SELECT a.*, u.nome as criado_por_nome
                FROM contrato_aditivos a
                LEFT JOIN usuarios u ON a.created_by = u.id
                WHERE a.contrato_id = :id
                ORDER BY a.data_aditivo DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar histórico do contrato
     * 
     * @param int $id
     * @return array
     */
    public function getHistorico($id) {
        $sql = "SELECT h.*, u.nome as usuario_nome
                FROM contrato_historico h
                LEFT JOIN usuarios u ON h.created_by = u.id
                WHERE h.contrato_id = :id
                ORDER BY h.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Registrar no histórico
     * 
     * @param int $contratoId
     * @param string $tipo
     * @param string|null $campo
     * @param string|null $valorAnterior
     * @param string|null $valorNovo
     * @param string $descricao
     * @return int
     */
    public function registrarHistorico($contratoId, $tipo, $campo, $valorAnterior, $valorNovo, $descricao) {
        $sql = "INSERT INTO contrato_historico (
                    contrato_id, tipo_alteracao, campo_alterado,
                    valor_anterior, valor_novo, descricao,
                    created_by, created_at
                ) VALUES (
                    :contrato_id, :tipo, :campo,
                    :valor_anterior, :valor_novo, :descricao,
                    :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':campo', $campo);
        $stmt->bindValue(':valor_anterior', $valorAnterior);
        $stmt->bindValue(':valor_novo', $valorNovo);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':created_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Buscar estatísticas gerais
     * 
     * @return array
     */
    public function getEstatisticasGerais() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos,
                    SUM(CASE WHEN status = 'suspenso' THEN 1 ELSE 0 END) as suspensos,
                    SUM(CASE WHEN status = 'encerrado' THEN 1 ELSE 0 END) as encerrados,
                    SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                    SUM(CASE WHEN status = 'ativo' AND data_fim < CURDATE() THEN 1 ELSE 0 END) as vencidos,
                    SUM(CASE WHEN status = 'ativo' AND data_fim IS NOT NULL AND DATEDIFF(data_fim, NOW()) <= 30 THEN 1 ELSE 0 END) as vencendo_30dias,
                    SUM(valor_total) as valor_total,
                    SUM(valor_executado) as valor_executado
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Contar total de contratos
     * 
     * @return int
     */
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Contar por status
     * 
     * @param string $status
     * @return int
     */
    public function countPorStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar contratos vencendo
     * 
     * @param int $dias
     * @return array
     */
    public function getVencendo($dias = 90) {
        $sql = "SELECT c.*, 
                       et.nome_fantasia as tomadora_nome,
                       ep.nome_fantasia as prestadora_nome,
                       DATEDIFF(c.data_fim, CURDATE()) as dias_restantes
                FROM {$this->table} c
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.status = 'ativo' 
                  AND c.data_fim IS NOT NULL
                  AND DATEDIFF(c.data_fim, CURDATE()) <= :dias
                  AND DATEDIFF(c.data_fim, CURDATE()) >= 0
                ORDER BY c.data_fim ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Calcular valor total dos contratos ativos
     * 
     * @return float
     */
    public function getValorTotalAtivos() {
        $sql = "SELECT SUM(valor_total) as total FROM {$this->table} WHERE status = 'ativo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return floatval($result['total'] ?? 0);
    }
    
    /**
     * Validar unicidade do número do contrato
     * 
     * @param string $numero
     * @param int|null $exceptId
     * @return bool
     */
    public function validateUniqueNumero($numero, $exceptId = null) {
        $existing = $this->findByNumero($numero, $exceptId);
        return $existing === null;
    }
    
    /**
     * Buscar serviços do contrato
     * 
     * @param int $contratoId
     * @return array
     */
    public function getServicos($id) {
        // Este método retorna os serviços vinculados ao contrato
        // da tabela servico_valores
        $sql = "SELECT sv.*, s.nome as servico_nome
                FROM servico_valores sv
                INNER JOIN servicos s ON sv.servico_id = s.id
                WHERE sv.contrato_id = :contrato_id AND sv.ativo = 1
                ORDER BY sv.data_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Adicionar serviço ao contrato
     * 
     * @param int $contratoId
     * @param array $data
     * @return int
     */
    public function addServico($contratoId, $data) {
        $sql = "INSERT INTO servico_valores (
                    contrato_id, servico_id, descricao_customizada,
                    quantidade, unidade, valor_unitario, valor_total,
                    periodicidade, data_inicio, data_fim,
                    observacoes, ativo, created_by, created_at
                ) VALUES (
                    :contrato_id, :servico_id, :descricao_customizada,
                    :quantidade, :unidade, :valor_unitario, :valor_total,
                    :periodicidade, :data_inicio, :data_fim,
                    :observacoes, 1, :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':servico_id', $data['servico_id'], PDO::PARAM_INT);
        $stmt->bindValue(':descricao_customizada', $data['descricao_customizada'] ?? null);
        $stmt->bindValue(':quantidade', $data['quantidade']);
        $stmt->bindValue(':unidade', $data['unidade'] ?? 'unidade');
        $stmt->bindValue(':valor_unitario', $data['valor_unitario']);
        $stmt->bindValue(':valor_total', $data['valor_total']);
        $stmt->bindValue(':periodicidade', $data['periodicidade'] ?? null);
        $stmt->bindValue(':data_inicio', $data['data_inicio'] ?? null);
        $stmt->bindValue(':data_fim', $data['data_fim'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':created_by', $data['criado_por'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Excluir serviço do contrato (soft delete)
     * 
     * @param int $servicoId
     * @return bool
     */
    public function deleteServico($servicoId) {
        $sql = "UPDATE servico_valores SET ativo = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $servicoId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Adicionar aditivo ao contrato
     * 
     * @param int $contratoId
     * @param array $data
     * @return int
     */
    public function addAditivo($contratoId, $data) {
        $sql = "INSERT INTO contrato_aditivos (
                    contrato_id, numero_aditivo, tipo_aditivo, data_aditivo,
                    descricao, justificativa, valor_anterior, valor_novo,
                    data_vigencia_anterior, data_vigencia_nova, percentual_alteracao,
                    aprovado_por, data_aprovacao, caminho_arquivo,
                    observacoes, ativo, created_by, created_at
                ) VALUES (
                    :contrato_id, :numero_aditivo, :tipo_aditivo, :data_aditivo,
                    :descricao, :justificativa, :valor_anterior, :valor_novo,
                    :data_vigencia_anterior, :data_vigencia_nova, :percentual_alteracao,
                    :aprovado_por, :data_aprovacao, :caminho_arquivo,
                    :observacoes, 1, :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':numero_aditivo', $data['numero_aditivo']);
        $stmt->bindValue(':tipo_aditivo', $data['tipo_aditivo']);
        $stmt->bindValue(':data_aditivo', $data['data_aditivo']);
        $stmt->bindValue(':descricao', $data['descricao']);
        $stmt->bindValue(':justificativa', $data['justificativa'] ?? null);
        $stmt->bindValue(':valor_anterior', $data['valor_anterior'] ?? null);
        $stmt->bindValue(':valor_novo', $data['valor_novo'] ?? null);
        $stmt->bindValue(':data_vigencia_anterior', $data['data_vigencia_anterior'] ?? null);
        $stmt->bindValue(':data_vigencia_nova', $data['data_vigencia_nova'] ?? null);
        $stmt->bindValue(':percentual_alteracao', $data['percentual_alteracao'] ?? null);
        $stmt->bindValue(':aprovado_por', $data['aprovado_por'] ?? null);
        $stmt->bindValue(':data_aprovacao', $data['data_aprovacao'] ?? null);
        $stmt->bindValue(':caminho_arquivo', $data['arquivo'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':created_by', $data['criado_por'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Excluir aditivo (soft delete)
     * 
     * @param int $aditivoId
     * @return bool
     */
    public function deleteAditivo($aditivoId) {
        $sql = "UPDATE contrato_aditivos SET ativo = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $aditivoId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Adicionar histórico ao contrato
     * 
     * @param int $contratoId
     * @param array $data
     * @return int
     */
    public function addHistorico($contratoId, $data) {
        $sql = "INSERT INTO contrato_historico (
                    contrato_id, tipo_alteracao, campo_alterado,
                    valor_anterior, valor_novo, descricao,
                    created_by, created_at
                ) VALUES (
                    :contrato_id, :tipo_alteracao, :campo_alterado,
                    :valor_anterior, :valor_novo, :descricao,
                    :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_alteracao', $data['tipo_evento'] ?? 'Outro');
        $stmt->bindValue(':campo_alterado', null); // Opcional
        $stmt->bindValue(':valor_anterior', null);
        $stmt->bindValue(':valor_novo', $data['detalhes_json'] ?? null);
        $stmt->bindValue(':descricao', $data['descricao']);
        $stmt->bindValue(':created_by', $data['usuario_id'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Buscar valores por período do contrato
     * 
     * @param int $contratoId
     * @return array
     */
    public function getValoresPeriodo($contratoId) {
        // Este método busca os valores mensais/periódicos do contrato
        // Retorna array vazio por enquanto - será implementado na Sprint 7 (Financeiro)
        return [];
    }
    
    /**
     * Adicionar valor de período
     * 
     * @param int $contratoId
     * @param array $data
     * @return int
     */
    public function addValorPeriodo($contratoId, $data) {
        // Placeholder - será implementado na Sprint 7 (Gestão Financeira)
        // Por enquanto retorna 0
        return 0;
    }
    
    /**
     * Soft delete
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Verificar se pode excluir (sem projetos ativos, etc)
        $sql = "UPDATE {$this->table} 
                SET status = 'cancelado', 
                    updated_by = :updated_by,
                    updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
