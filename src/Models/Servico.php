<?php /* Cache-Buster: 2025-11-15 13:58:50 */

namespace App\Models;

use App\Database;
use PDO;

class Servico {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // CRUD BÁSICO
    public function create($data) {
        $sql = "INSERT INTO servicos (
            codigo, nome, descricao, categoria, subcategoria,
            unidade_medida, valor_referencia_min, valor_referencia_max,
            prazo_medio_dias, complexidade, requisitos, ativo,
            observacoes, criado_por
        ) VALUES (
            :codigo, :nome, :descricao, :categoria, :subcategoria,
            :unidade_medida, :valor_referencia_min, :valor_referencia_max,
            :prazo_medio_dias, :complexidade, :requisitos, :ativo,
            :observacoes, :criado_por
        )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM servicos WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function findByCodigo($codigo) {
        $sql = "SELECT * FROM servicos WHERE codigo = :codigo AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch();
    }
    
    public function all($filtros = [], $page = 1, $limit = 20) {
        // Garantir que são integers para operações matemáticas
        $page = (int) $page;
        $limit = (int) $limit;
        $offset = ($page - 1) * $limit;
        $where = ["deleted_at IS NULL"];
        $params = [];
        
        if (!empty($filtros['ativo'])) {
            $where[] = "ativo = :ativo";
            $params['ativo'] = $filtros['ativo'];
        }
        
        if (!empty($filtros['categoria'])) {
            $where[] = "categoria = :categoria";
            $params['categoria'] = $filtros['categoria'];
        }
        
        if (!empty($filtros['subcategoria'])) {
            $where[] = "subcategoria = :subcategoria";
            $params['subcategoria'] = $filtros['subcategoria'];
        }
        
        if (!empty($filtros['complexidade'])) {
            $where[] = "complexidade = :complexidade";
            $params['complexidade'] = $filtros['complexidade'];
        }
        
        if (!empty($filtros['search'])) {
            $where[] = "(codigo LIKE :search OR nome LIKE :search OR descricao LIKE :search)";
            $params['search'] = "%{$filtros['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM servicos 
                WHERE $whereClause 
                ORDER BY categoria ASC, nome ASC 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function count($filtros = []) {
        $where = ["deleted_at IS NULL"];
        $params = [];
        
        if (!empty($filtros['ativo'])) {
            $where[] = "ativo = :ativo";
            $params['ativo'] = $filtros['ativo'];
        }
        
        if (!empty($filtros['categoria'])) {
            $where[] = "categoria = :categoria";
            $params['categoria'] = $filtros['categoria'];
        }
        
        if (!empty($filtros['subcategoria'])) {
            $where[] = "subcategoria = :subcategoria";
            $params['subcategoria'] = $filtros['subcategoria'];
        }
        
        if (!empty($filtros['complexidade'])) {
            $where[] = "complexidade = :complexidade";
            $params['complexidade'] = $filtros['complexidade'];
        }
        
        if (!empty($filtros['search'])) {
            $where[] = "(codigo LIKE :search OR nome LIKE :search OR descricao LIKE :search)";
            $params['search'] = "%{$filtros['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as total FROM servicos WHERE $whereClause";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch()['total'];
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'codigo', 'nome', 'descricao', 'categoria', 'subcategoria',
            'unidade_medida', 'valor_referencia_min', 'valor_referencia_max',
            'prazo_medio_dias', 'complexidade', 'requisitos', 'ativo',
            'observacoes', 'atualizado_por'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE servicos SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "UPDATE servicos SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // VALIDAÇÕES
    public function validateUniqueCodigo($codigo, $id = null) {
        $sql = "SELECT id FROM servicos WHERE codigo = :codigo AND deleted_at IS NULL";
        
        if ($id) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $params = ['codigo' => $codigo];
        
        if ($id) {
            $params['id'] = $id;
        }
        
        $stmt->execute($params);
        return $stmt->fetch() === false;
    }
    
    // CATEGORIAS
    public function getCategorias() {
        $sql = "SELECT DISTINCT categoria FROM servicos 
                WHERE deleted_at IS NULL AND categoria IS NOT NULL
                ORDER BY categoria ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getSubcategorias($categoria = null) {
        $sql = "SELECT DISTINCT subcategoria FROM servicos 
                WHERE deleted_at IS NULL AND subcategoria IS NOT NULL";
        
        $params = [];
        if ($categoria) {
            $sql .= " AND categoria = :categoria";
            $params['categoria'] = $categoria;
        }
        
        $sql .= " ORDER BY subcategoria ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // REQUISITOS
    public function addRequisito($servicoId, $data) {
        $sql = "INSERT INTO servico_requisitos (
            servico_id, tipo_requisito, descricao, obrigatorio,
            documento_requerido, prazo_dias, ordem, criado_por
        ) VALUES (
            :servico_id, :tipo_requisito, :descricao, :obrigatorio,
            :documento_requerido, :prazo_dias, :ordem, :criado_por
        )";
        
        $data['servico_id'] = $servicoId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getRequisitos($servicoId) {
        $sql = "SELECT * FROM servico_requisitos 
                WHERE servico_id = :servico_id AND deleted_at IS NULL
                ORDER BY ordem ASC, tipo_requisito ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['servico_id' => $servicoId]);
        
        return $stmt->fetchAll();
    }
    
    public function updateRequisito($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'tipo_requisito', 'descricao', 'obrigatorio',
            'documento_requerido', 'prazo_dias', 'ordem', 'atualizado_por'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE servico_requisitos SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteRequisito($id) {
        $sql = "UPDATE servico_requisitos SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // VALORES DE REFERÊNCIA
    public function addValorReferencia($servicoId, $data) {
        $sql = "INSERT INTO servico_valores_referencia (
            servico_id, empresa_tomadora_id, valor_minimo, valor_maximo,
            valor_medio, moeda, unidade, vigencia_inicio, vigencia_fim,
            observacoes, criado_por
        ) VALUES (
            :servico_id, :empresa_tomadora_id, :valor_minimo, :valor_maximo,
            :valor_medio, :moeda, :unidade, :vigencia_inicio, :vigencia_fim,
            :observacoes, :criado_por
        )";
        
        $data['servico_id'] = $servicoId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getValoresReferencia($servicoId, $empresaTomadoraId = null) {
        $sql = "SELECT vr.*, et.nome_fantasia as empresa_nome
                FROM servico_valores_referencia vr
                LEFT JOIN empresas_tomadoras et ON vr.empresa_tomadora_id = et.id
                WHERE vr.servico_id = :servico_id 
                AND vr.deleted_at IS NULL";
        
        $params = ['servico_id' => $servicoId];
        
        if ($empresaTomadoraId) {
            $sql .= " AND vr.empresa_tomadora_id = :empresa_tomadora_id";
            $params['empresa_tomadora_id'] = $empresaTomadoraId;
        }
        
        $sql .= " ORDER BY vr.vigencia_inicio DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getValorReferenciaVigente($servicoId, $empresaTomadoraId = null) {
        $sql = "SELECT * FROM servico_valores_referencia 
                WHERE servico_id = :servico_id 
                AND deleted_at IS NULL
                AND vigencia_inicio <= CURDATE()
                AND (vigencia_fim IS NULL OR vigencia_fim >= CURDATE())";
        
        $params = ['servico_id' => $servicoId];
        
        if ($empresaTomadoraId) {
            $sql .= " AND empresa_tomadora_id = :empresa_tomadora_id";
            $params['empresa_tomadora_id'] = $empresaTomadoraId;
        }
        
        $sql .= " ORDER BY vigencia_inicio DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
    
    public function updateValorReferencia($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'valor_minimo', 'valor_maximo', 'valor_medio', 'moeda',
            'unidade', 'vigencia_inicio', 'vigencia_fim', 'observacoes',
            'atualizado_por'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE servico_valores_referencia SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteValorReferencia($id) {
        $sql = "UPDATE servico_valores_referencia SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // ESTATÍSTICAS
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM servicos WHERE deleted_at IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch()['total'];
    }
    
    public function countAtivos() {
        $sql = "SELECT COUNT(*) as total FROM servicos WHERE ativo = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch()['total'];
    }
    
    public function getAtivos() {
        $sql = "SELECT * FROM servicos WHERE ativo = 1 AND deleted_at IS NULL ORDER BY nome ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getPorCategoria() {
        $sql = "SELECT categoria, COUNT(*) as total 
                FROM servicos 
                WHERE deleted_at IS NULL 
                GROUP BY categoria
                ORDER BY total DESC";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getPorComplexidade() {
        $sql = "SELECT complexidade, COUNT(*) as total 
                FROM servicos 
                WHERE deleted_at IS NULL 
                GROUP BY complexidade
                ORDER BY 
                    CASE complexidade
                        WHEN 'Baixa' THEN 1
                        WHEN 'Média' THEN 2
                        WHEN 'Alta' THEN 3
                        ELSE 4
                    END";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getValorMedio($categoria = null) {
        $sql = "SELECT AVG((valor_referencia_min + valor_referencia_max) / 2) as valor_medio 
                FROM servicos 
                WHERE deleted_at IS NULL
                AND valor_referencia_min IS NOT NULL
                AND valor_referencia_max IS NOT NULL";
        
        $params = [];
        if ($categoria) {
            $sql .= " AND categoria = :categoria";
            $params['categoria'] = $categoria;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['valor_medio'] ?? 0;
    }
    
    public function getServicosRelacionados($servicoId, $limit = 5) {
        $servico = $this->findById($servicoId);
        if (!$servico) {
            return [];
        }
        
        $sql = "SELECT * FROM servicos 
                WHERE deleted_at IS NULL 
                AND id != :id
                AND (categoria = :categoria OR subcategoria = :subcategoria)
                AND ativo = 1
                ORDER BY 
                    CASE 
                        WHEN categoria = :categoria AND subcategoria = :subcategoria THEN 1
                        WHEN categoria = :categoria THEN 2
                        WHEN subcategoria = :subcategoria THEN 3
                        ELSE 4
                    END,
                    nome ASC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $servicoId, PDO::PARAM_INT);
        $stmt->bindValue(':categoria', $servico['categoria'], PDO::PARAM_STR);
        $stmt->bindValue(':subcategoria', $servico['subcategoria'], PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
