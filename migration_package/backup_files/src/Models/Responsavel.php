<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * Model Responsavel
 * Gerencia responsáveis das empresas tomadoras
 */
class Responsavel {
    
    private $db;
    private $table = 'empresa_tomadora_responsaveis';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os responsáveis de uma empresa
     * 
     * @param int $empresaId
     * @return array
     */
    public function getByEmpresa($empresaId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE empresa_tomadora_id = :empresa_id AND ativo = 1
                ORDER BY principal DESC, nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar responsável por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT r.*, et.nome_fantasia as empresa_nome
                FROM {$this->table} r
                INNER JOIN empresas_tomadoras et ON r.empresa_tomadora_id = et.id
                WHERE r.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Buscar responsável principal de uma empresa
     * 
     * @param int $empresaId
     * @return array|null
     */
    public function getPrincipal($empresaId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE empresa_tomadora_id = :empresa_id 
                  AND principal = 1 
                  AND ativo = 1
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Criar novo responsável
     * 
     * @param array $data
     * @return int
     */
    public function create($data) {
        // Se este vai ser o principal, desmarcar os outros
        if (!empty($data['principal']) && $data['principal'] == 1) {
            $this->desmarcarPrincipal($data['empresa_tomadora_id']);
        }
        
        $sql = "INSERT INTO {$this->table} (
                    empresa_tomadora_id, nome, cargo, departamento,
                    email, telefone, celular,
                    principal, ativo, observacoes,
                    created_by, created_at
                ) VALUES (
                    :empresa_tomadora_id, :nome, :cargo, :departamento,
                    :email, :telefone, :celular,
                    :principal, :ativo, :observacoes,
                    :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':empresa_tomadora_id', $data['empresa_tomadora_id'], PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':cargo', $data['cargo'] ?? null);
        $stmt->bindValue(':departamento', $data['departamento'] ?? null);
        $stmt->bindValue(':email', $data['email'] ?? null);
        $stmt->bindValue(':telefone', $data['telefone'] ?? null);
        $stmt->bindValue(':celular', $data['celular'] ?? null);
        $stmt->bindValue(':principal', $data['principal'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar responsável
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $responsavel = $this->findById($id);
        
        if (!$responsavel) {
            throw new \Exception('Responsável não encontrado');
        }
        
        // Se está marcando como principal, desmarcar os outros
        if (!empty($data['principal']) && $data['principal'] == 1) {
            $this->desmarcarPrincipal($responsavel['empresa_tomadora_id']);
        }
        
        $sql = "UPDATE {$this->table} SET
                    nome = :nome,
                    cargo = :cargo,
                    departamento = :departamento,
                    email = :email,
                    telefone = :telefone,
                    celular = :celular,
                    principal = :principal,
                    ativo = :ativo,
                    observacoes = :observacoes,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':cargo', $data['cargo'] ?? null);
        $stmt->bindValue(':departamento', $data['departamento'] ?? null);
        $stmt->bindValue(':email', $data['email'] ?? null);
        $stmt->bindValue(':telefone', $data['telefone'] ?? null);
        $stmt->bindValue(':celular', $data['celular'] ?? null);
        $stmt->bindValue(':principal', $data['principal'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':updated_by', $data['updated_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Excluir responsável
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $responsavel = $this->findById($id);
        
        if (!$responsavel) {
            throw new \Exception('Responsável não encontrado');
        }
        
        // Verificar se não é o único responsável
        $totalResponsaveis = $this->countByEmpresa($responsavel['empresa_tomadora_id']);
        
        if ($totalResponsaveis <= 1) {
            throw new \Exception('Não é possível excluir o único responsável da empresa');
        }
        
        // Soft delete
        $sql = "UPDATE {$this->table} 
                SET ativo = 0,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Desmarcar todos os responsáveis principais de uma empresa
     * 
     * @param int $empresaId
     * @return bool
     */
    private function desmarcarPrincipal($empresaId) {
        $sql = "UPDATE {$this->table} 
                SET principal = 0
                WHERE empresa_tomadora_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Contar responsáveis de uma empresa
     * 
     * @param int $empresaId
     * @return int
     */
    public function countByEmpresa($empresaId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE empresa_tomadora_id = :empresa_id AND ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
