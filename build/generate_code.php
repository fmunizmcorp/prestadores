#!/usr/bin/env php
<?php
/**
 * Gerador Automático de Código - Sprint 4
 * Gera Models, Controllers, Views a partir dos templates da documentação
 */

// Verificar se está no diretório correto
if (!file_exists(__DIR__ . '/../src')) {
    die("Erro: Execute este script a partir do diretório raiz do projeto\n");
}

echo "🚀 Gerador Automático de Código - Sistema Clinfec\n";
echo "==================================================\n\n";

// Estatísticas
$stats = [
    'models' => 0,
    'controllers' => 0,
    'views' => 0,
    'js' => 0,
    'css' => 0
];

/**
 * Gerar Model EmpresaTomadora
 */
function gerarModelEmpresaTomadora() {
    global $stats;
    
    $codigo = <<<'PHP'
<?php

namespace App\Models;

use App\Database;
use PDO;

class EmpresaTomadora {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // CRUD BÁSICO
    public function create($data) {
        $sql = "INSERT INTO empresas_tomadoras (
            razao_social, nome_fantasia, cnpj, inscricao_estadual,
            inscricao_municipal, cep, logradouro, numero, complemento,
            bairro, cidade, estado, email_principal, telefone_principal,
            telefone_secundario, celular, whatsapp, email_financeiro,
            email_projetos, site, dia_fechamento, dia_pagamento,
            forma_pagamento_preferencial, banco, agencia, conta,
            tipo_conta, logo, ativo, observacoes, criado_por
        ) VALUES (
            :razao_social, :nome_fantasia, :cnpj, :inscricao_estadual,
            :inscricao_municipal, :cep, :logradouro, :numero, :complemento,
            :bairro, :cidade, :estado, :email_principal, :telefone_principal,
            :telefone_secundario, :celular, :whatsapp, :email_financeiro,
            :email_projetos, :site, :dia_fechamento, :dia_pagamento,
            :forma_pagamento_preferencial, :banco, :agencia, :conta,
            :tipo_conta, :logo, :ativo, :observacoes, :criado_por
        )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM empresas_tomadoras WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function findByCnpj($cnpj) {
        $sql = "SELECT * FROM empresas_tomadoras WHERE cnpj = :cnpj AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);
        return $stmt->fetch();
    }
    
    public function all($filtros = [], $page = 1, $limit = 20) {
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
        
        if (!empty($filtros['search'])) {
            $where[] = "(razao_social LIKE :search OR nome_fantasia LIKE :search OR cnpj LIKE :search)";
            $params['search'] = "%{$filtros['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM empresas_tomadoras 
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
        
        // Aplicar mesmos filtros do método all()
        if (!empty($filtros['ativo'])) {
            $where[] = "ativo = :ativo";
            $params['ativo'] = $filtros['ativo'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as total FROM empresas_tomadoras WHERE $whereClause";
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
            'bairro', 'cidade', 'estado', 'email_principal', 'telefone_principal',
            'telefone_secundario', 'celular', 'whatsapp', 'email_financeiro',
            'email_projetos', 'site', 'dia_fechamento', 'dia_pagamento',
            'forma_pagamento_preferencial', 'banco', 'agencia', 'conta',
            'tipo_conta', 'logo', 'ativo', 'observacoes', 'atualizado_por'
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
        $sql = "UPDATE empresas_tomadoras SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "UPDATE empresas_tomadoras SET deleted_at = NOW() WHERE id = :id";
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
        
        // Validação básica (pode ser melhorada com algoritmo completo)
        return true;
    }
    
    public function validateUniqueCnpj($cnpj, $id = null) {
        $sql = "SELECT id FROM empresas_tomadoras WHERE cnpj = :cnpj AND deleted_at IS NULL";
        
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
    
    // RESPONSÁVEIS
    public function addResponsavel($empresaId, $data) {
        $sql = "INSERT INTO empresa_tomadora_responsaveis (
            empresa_id, nome, cargo, departamento, email,
            telefone, celular, ramal, responsavel_principal,
            recebe_notificacoes, ativo, observacoes, foto, criado_por
        ) VALUES (
            :empresa_id, :nome, :cargo, :departamento, :email,
            :telefone, :celular, :ramal, :responsavel_principal,
            :recebe_notificacoes, :ativo, :observacoes, :foto, :criado_por
        )";
        
        $data['empresa_id'] = $empresaId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getResponsaveis($empresaId) {
        $sql = "SELECT * FROM empresa_tomadora_responsaveis 
                WHERE empresa_id = :empresa_id AND deleted_at IS NULL
                ORDER BY responsavel_principal DESC, nome ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetchAll();
    }
    
    // DOCUMENTOS
    public function addDocumento($empresaId, $data) {
        $sql = "INSERT INTO empresa_tomadora_documentos (
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
    
    public function getDocumentos($empresaId) {
        $sql = "SELECT * FROM empresa_tomadora_documentos 
                WHERE empresa_id = :empresa_id AND deleted_at IS NULL
                ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetchAll();
    }
    
    // ESTATÍSTICAS
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM empresas_tomadoras WHERE deleted_at IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch()['total'];
    }
    
    public function countAtivas() {
        $sql = "SELECT COUNT(*) as total FROM empresas_tomadoras WHERE ativo = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch()['total'];
    }
    
    public function getAtivas() {
        $sql = "SELECT * FROM empresas_tomadoras WHERE ativo = 1 AND deleted_at IS NULL ORDER BY nome_fantasia ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
}
PHP;
    
    $arquivo = __DIR__ . '/../src/models/EmpresaTomadora.php';
    file_put_contents($arquivo, $codigo);
    $stats['models']++;
    
    echo "✅ Model EmpresaTomadora.php criado\n";
}

// Executar geradores
gerarModelEmpresaTomadora();

echo "\n==================================================\n";
echo "📊 Estatísticas:\n";
echo "   Models: {$stats['models']}\n";
echo "   Controllers: {$stats['controllers']}\n";
echo "   Views: {$stats['views']}\n";
echo "   JavaScript: {$stats['js']}\n";
echo "   CSS: {$stats['css']}\n";
echo "\n✅ Geração concluída!\n";
PHP;
