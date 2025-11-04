<?php
/**
 * Model de Empresa
 */

namespace App\Models;

use App\Database;
use PDO;

class Empresa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca empresa por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM empresas WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca empresa por CNPJ
     */
    public function findByCnpj($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        $stmt = $this->db->prepare("SELECT * FROM empresas WHERE cnpj = ? LIMIT 1");
        $stmt->execute([$cnpj]);
        return $stmt->fetch();
    }
    
    /**
     * Cria nova empresa
     */
    public function create($data) {
        $sql = "INSERT INTO empresas (
                    razao_social, nome_fantasia, cnpj, inscricao_estadual, inscricao_municipal,
                    cep, logradouro, numero, complemento, bairro, cidade, estado,
                    email_principal, telefone_principal, telefone_secundario, celular,
                    observacoes, ativo
                ) VALUES (
                    :razao_social, :nome_fantasia, :cnpj, :inscricao_estadual, :inscricao_municipal,
                    :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado,
                    :email_principal, :telefone_principal, :telefone_secundario, :celular,
                    :observacoes, :ativo
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => preg_replace('/[^0-9]/', '', $data['cnpj']),
            'inscricao_estadual' => $data['inscricao_estadual'] ?? null,
            'inscricao_municipal' => $data['inscricao_municipal'] ?? null,
            'cep' => $data['cep'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'email_principal' => $data['email_principal'] ?? null,
            'telefone_principal' => $data['telefone_principal'] ?? null,
            'telefone_secundario' => $data['telefone_secundario'] ?? null,
            'celular' => $data['celular'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'ativo' => $data['ativo'] ?? true
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza empresa
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        $allowedFields = [
            'razao_social', 'nome_fantasia', 'cnpj', 'inscricao_estadual', 'inscricao_municipal',
            'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
            'email_principal', 'telefone_principal', 'telefone_secundario', 'celular',
            'observacoes', 'ativo'
        ];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $values[] = ($key === 'cnpj') ? preg_replace('/[^0-9]/', '', $value) : $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE empresas SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    /**
     * Lista todas as empresas
     */
    public function all($filters = []) {
        $sql = "SELECT e.*, 
                COUNT(DISTINCT ue.usuario_id) as total_usuarios,
                GROUP_CONCAT(DISTINCT s.nome SEPARATOR ', ') as servicos
                FROM empresas e
                LEFT JOIN usuario_empresa ue ON e.id = ue.empresa_id
                LEFT JOIN empresa_servico es ON e.id = es.empresa_id
                LEFT JOIN servicos s ON es.servico_id = s.id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['ativo'])) {
            $sql .= " AND e.ativo = ?";
            $params[] = $filters['ativo'];
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (e.razao_social LIKE ? OR e.nome_fantasia LIKE ? OR e.cnpj LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.nome_fantasia ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Adiciona serviço à empresa
     */
    public function addServico($empresaId, $servicoId) {
        $sql = "INSERT IGNORE INTO empresa_servico (empresa_id, servico_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$empresaId, $servicoId]);
    }
    
    /**
     * Remove serviço da empresa
     */
    public function removeServico($empresaId, $servicoId) {
        $sql = "DELETE FROM empresa_servico WHERE empresa_id = ? AND servico_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$empresaId, $servicoId]);
    }
    
    /**
     * Busca serviços da empresa
     */
    public function getServicos($empresaId) {
        $sql = "SELECT s.* 
                FROM servicos s
                INNER JOIN empresa_servico es ON s.id = es.servico_id
                WHERE es.empresa_id = ?
                ORDER BY s.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Adiciona contato à empresa
     */
    public function addContato($empresaId, $data) {
        $sql = "INSERT INTO empresa_contatos (
                    empresa_id, nome, cargo, email, telefone, celular, setor, principal, observacoes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $empresaId,
            $data['nome'],
            $data['cargo'] ?? null,
            $data['email'] ?? null,
            $data['telefone'] ?? null,
            $data['celular'] ?? null,
            $data['setor'] ?? null,
            $data['principal'] ?? false,
            $data['observacoes'] ?? null
        ]);
    }
    
    /**
     * Busca contatos da empresa
     */
    public function getContatos($empresaId) {
        $sql = "SELECT * FROM empresa_contatos WHERE empresa_id = ? ORDER BY principal DESC, nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Busca usuários da empresa
     */
    public function getUsuarios($empresaId) {
        $sql = "SELECT u.*, ue.cargo 
                FROM usuarios u
                INNER JOIN usuario_empresa ue ON u.id = ue.usuario_id
                WHERE ue.empresa_id = ?
                ORDER BY u.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Deleta empresa (soft delete)
     */
    public function delete($id) {
        return $this->update($id, ['ativo' => false]);
    }
}
