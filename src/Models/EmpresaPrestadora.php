<?php /* Cache-Buster: 2025-11-15 13:58:45 */

namespace App\Models;

use App\Database;
use PDO;

class EmpresaPrestadora {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // CRUD BÁSICO
    public function create($data) {
        $sql = "INSERT INTO empresas_prestadoras (
            razao_social, nome_fantasia, cnpj, inscricao_estadual,
            inscricao_municipal, cep, logradouro, numero, complemento,
            bairro, cidade, estado, pais, email_principal, telefone_principal,
            telefone_secundario, celular, whatsapp, email_financeiro,
            site, banco, agencia, conta, tipo_conta, pix_tipo, pix_chave,
            logo, categoria_principal, area_atuacao, numero_funcionarios,
            ano_fundacao, certificacoes, ativo, observacoes, criado_por
        ) VALUES (
            :razao_social, :nome_fantasia, :cnpj, :inscricao_estadual,
            :inscricao_municipal, :cep, :logradouro, :numero, :complemento,
            :bairro, :cidade, :estado, :pais, :email_principal, :telefone_principal,
            :telefone_secundario, :celular, :whatsapp, :email_financeiro,
            :site, :banco, :agencia, :conta, :tipo_conta, :pix_tipo, :pix_chave,
            :logo, :categoria_principal, :area_atuacao, :numero_funcionarios,
            :ano_fundacao, :certificacoes, :ativo, :observacoes, :criado_por
        )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM empresas_prestadoras WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function findByCnpj($cnpj) {
        $sql = "SELECT * FROM empresas_prestadoras WHERE cnpj = :cnpj AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);
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
        
        if (!empty($filtros['cidade'])) {
            $where[] = "cidade = :cidade";
            $params['cidade'] = $filtros['cidade'];
        }
        
        if (!empty($filtros['estado'])) {
            $where[] = "estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['categoria_principal'])) {
            $where[] = "categoria_principal = :categoria_principal";
            $params['categoria_principal'] = $filtros['categoria_principal'];
        }
        
        if (!empty($filtros['area_atuacao'])) {
            $where[] = "area_atuacao LIKE :area_atuacao";
            $params['area_atuacao'] = "%{$filtros['area_atuacao']}%";
        }
        
        if (!empty($filtros['search'])) {
            $where[] = "(razao_social LIKE :search OR nome_fantasia LIKE :search OR cnpj LIKE :search)";
            $params['search'] = "%{$filtros['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM empresas_prestadoras 
                WHERE $whereClause 
                ORDER BY nome_fantasia ASC 
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
        
        if (!empty($filtros['cidade'])) {
            $where[] = "cidade = :cidade";
            $params['cidade'] = $filtros['cidade'];
        }
        
        if (!empty($filtros['estado'])) {
            $where[] = "estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['categoria_principal'])) {
            $where[] = "categoria_principal = :categoria_principal";
            $params['categoria_principal'] = $filtros['categoria_principal'];
        }
        
        if (!empty($filtros['area_atuacao'])) {
            $where[] = "area_atuacao LIKE :area_atuacao";
            $params['area_atuacao'] = "%{$filtros['area_atuacao']}%";
        }
        
        if (!empty($filtros['search'])) {
            $where[] = "(razao_social LIKE :search OR nome_fantasia LIKE :search OR cnpj LIKE :search)";
            $params['search'] = "%{$filtros['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as total FROM empresas_prestadoras WHERE $whereClause";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch()['total'];
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'razao_social', 'nome_fantasia', 'cnpj', 'inscricao_estadual',
            'inscricao_municipal', 'cep', 'logradouro', 'numero', 'complemento',
            'bairro', 'cidade', 'estado', 'pais', 'email_principal', 'telefone_principal',
            'telefone_secundario', 'celular', 'whatsapp', 'email_financeiro',
            'site', 'banco', 'agencia', 'conta', 'tipo_conta', 'pix_tipo', 'pix_chave',
            'logo', 'categoria_principal', 'area_atuacao', 'numero_funcionarios',
            'ano_fundacao', 'certificacoes', 'ativo', 'observacoes', 'atualizado_por'
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
        $sql = "UPDATE empresas_prestadoras SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "UPDATE empresas_prestadoras SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // VALIDAÇÕES
    public function validateCnpj($cnpj) {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Validação completa do CNPJ
        if ($cnpj == '00000000000000' || 
            $cnpj == '11111111111111' || 
            $cnpj == '22222222222222' || 
            $cnpj == '33333333333333' || 
            $cnpj == '44444444444444' || 
            $cnpj == '55555555555555' || 
            $cnpj == '66666666666666' || 
            $cnpj == '77777777777777' || 
            $cnpj == '88888888888888' || 
            $cnpj == '99999999999999') {
            return false;
        }
        
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        
        $resto = $soma % 11;
        
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }
        
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        
        $resto = $soma % 11;
        
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
    
    public function validateUniqueCnpj($cnpj, $id = null) {
        $sql = "SELECT id FROM empresas_prestadoras WHERE cnpj = :cnpj AND deleted_at IS NULL";
        
        if ($id) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $params = ['cnpj' => $cnpj];
        
        if ($id) {
            $params['id'] = $id;
        }
        
        $stmt->execute($params);
        return $stmt->fetch() === false;
    }
    
    // REPRESENTANTES
    public function addRepresentante($empresaId, $data) {
        $sql = "INSERT INTO empresa_prestadora_representantes (
            empresa_id, nome, cpf, cargo, email, telefone, celular,
            representante_legal, representante_comercial, ativo,
            observacoes, foto, criado_por
        ) VALUES (
            :empresa_id, :nome, :cpf, :cargo, :email, :telefone, :celular,
            :representante_legal, :representante_comercial, :ativo,
            :observacoes, :foto, :criado_por
        )";
        
        $data['empresa_id'] = $empresaId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getRepresentantes($empresaId) {
        $sql = "SELECT * FROM empresa_prestadora_representantes 
                WHERE empresa_id = :empresa_id AND deleted_at IS NULL
                ORDER BY representante_legal DESC, nome ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetchAll();
    }
    
    public function updateRepresentante($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'nome', 'cpf', 'cargo', 'email', 'telefone', 'celular',
            'representante_legal', 'representante_comercial', 'ativo',
            'observacoes', 'foto', 'atualizado_por'
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
        $sql = "UPDATE empresa_prestadora_representantes SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteRepresentante($id) {
        $sql = "UPDATE empresa_prestadora_representantes SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // DOCUMENTOS
    public function addDocumento($empresaId, $data) {
        $sql = "INSERT INTO empresa_prestadora_documentos (
            empresa_id, tipo_documento, nome_documento, descricao,
            arquivo, tamanho_bytes, mime_type, data_emissao,
            data_validade, alertar_dias_antes, status, upload_por
        ) VALUES (
            :empresa_id, :tipo_documento, :nome_documento, :descricao,
            :arquivo, :tamanho_bytes, :mime_type, :data_emissao,
            :data_validade, :alertar_dias_antes, :status, :upload_por
        )";
        
        $data['empresa_id'] = $empresaId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getDocumentos($empresaId, $status = null) {
        $sql = "SELECT * FROM empresa_prestadora_documentos 
                WHERE empresa_id = :empresa_id AND deleted_at IS NULL";
        
        $params = ['empresa_id' => $empresaId];
        
        if ($status !== null) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function updateDocumento($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'tipo_documento', 'nome_documento', 'descricao', 'arquivo',
            'tamanho_bytes', 'mime_type', 'data_emissao', 'data_validade',
            'alertar_dias_antes', 'status'
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
        $sql = "UPDATE empresa_prestadora_documentos SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteDocumento($id) {
        $sql = "UPDATE empresa_prestadora_documentos SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function getDocumentosVencendo($dias = 30) {
        $sql = "SELECT d.*, e.nome_fantasia 
                FROM empresa_prestadora_documentos d
                INNER JOIN empresas_prestadoras e ON d.empresa_id = e.id
                WHERE d.deleted_at IS NULL 
                AND d.data_validade IS NOT NULL
                AND DATEDIFF(d.data_validade, NOW()) <= :dias
                AND DATEDIFF(d.data_validade, NOW()) >= 0
                ORDER BY d.data_validade ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['dias' => $dias]);
        
        return $stmt->fetchAll();
    }
    
    // SERVIÇOS
    public function addServico($empresaId, $servicoId) {
        $sql = "INSERT INTO empresa_prestadora_servicos (empresa_id, servico_id) 
                VALUES (:empresa_id, :servico_id)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'empresa_id' => $empresaId,
            'servico_id' => $servicoId
        ]);
    }
    
    public function getServicos($empresaId) {
        $sql = "SELECT s.* 
                FROM servicos s
                INNER JOIN empresa_prestadora_servicos eps ON s.id = eps.servico_id
                WHERE eps.empresa_id = :empresa_id 
                AND s.deleted_at IS NULL
                ORDER BY s.nome ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetchAll();
    }
    
    public function removeServico($empresaId, $servicoId) {
        $sql = "DELETE FROM empresa_prestadora_servicos 
                WHERE empresa_id = :empresa_id AND servico_id = :servico_id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'empresa_id' => $empresaId,
            'servico_id' => $servicoId
        ]);
    }
    
    // ESTATÍSTICAS
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM empresas_prestadoras WHERE deleted_at IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch()['total'];
    }
    
    public function countAtivas() {
        $sql = "SELECT COUNT(*) as total FROM empresas_prestadoras WHERE ativo = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch()['total'];
    }
    
    public function getAtivas() {
        $sql = "SELECT * FROM empresas_prestadoras WHERE ativo = 1 AND deleted_at IS NULL ORDER BY nome_fantasia ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getContratosPorEmpresa($empresaId) {
        $sql = "SELECT COUNT(*) as total FROM contratos 
                WHERE empresa_prestadora_id = :empresa_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetch()['total'];
    }
    
    public function getPorCategoria() {
        $sql = "SELECT categoria_principal, COUNT(*) as total 
                FROM empresas_prestadoras 
                WHERE deleted_at IS NULL 
                GROUP BY categoria_principal
                ORDER BY total DESC";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getPorEstado() {
        $sql = "SELECT estado, COUNT(*) as total 
                FROM empresas_prestadoras 
                WHERE deleted_at IS NULL 
                GROUP BY estado
                ORDER BY total DESC";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
}
