<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * Model Fornecedor
 * 
 * Gerencia fornecedores para módulo de notas fiscais
 * 
 * @package App\Models
 * @since Sprint 9
 */
class Fornecedor
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Lista todos os fornecedores
     */
    public function all($filtros = [], $page = 1, $limit = 25)
    {
        $where = [];
        $params = [];
        
        if (!empty($filtros['ativo'])) {
            $where[] = "ativo = ?";
            $params[] = $filtros['ativo'];
        }
        
        if (!empty($filtros['busca'])) {
            $where[] = "(razao_social LIKE ? OR nome_fantasia LIKE ? OR cnpj LIKE ?)";
            $params[] = "%{$filtros['busca']}%";
            $params[] = "%{$filtros['busca']}%";
            $params[] = "%{$filtros['busca']}%";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM fornecedores $whereClause ORDER BY razao_social LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Conta total de fornecedores
     */
    public function count($filtros = [])
    {
        $where = [];
        $params = [];
        
        if (!empty($filtros['ativo'])) {
            $where[] = "ativo = ?";
            $params[] = $filtros['ativo'];
        }
        
        if (!empty($filtros['busca'])) {
            $where[] = "(razao_social LIKE ? OR nome_fantasia LIKE ? OR cnpj LIKE ?)";
            $params[] = "%{$filtros['busca']}%";
            $params[] = "%{$filtros['busca']}%";
            $params[] = "%{$filtros['busca']}%";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT COUNT(*) as total FROM fornecedores $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Busca fornecedor por ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM fornecedores WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca fornecedor por CNPJ
     */
    public function findByCnpj($cnpj)
    {
        $stmt = $this->db->prepare("SELECT * FROM fornecedores WHERE cnpj = ? LIMIT 1");
        $stmt->execute([$cnpj]);
        return $stmt->fetch();
    }
    
    /**
     * Cria novo fornecedor
     */
    public function create($data)
    {
        $sql = "INSERT INTO fornecedores (
                    razao_social, nome_fantasia, cnpj, inscricao_estadual,
                    logradouro, numero, complemento, bairro, cidade, estado, cep,
                    telefone, email, contato,
                    ativo, observacoes
                ) VALUES (
                    :razao_social, :nome_fantasia, :cnpj, :inscricao_estadual,
                    :logradouro, :numero, :complemento, :bairro, :cidade, :estado, :cep,
                    :telefone, :email, :contato,
                    :ativo, :observacoes
                )";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'] ?? null,
            'cnpj' => $data['cnpj'],
            'inscricao_estadual' => $data['inscricao_estadual'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'cep' => $data['cep'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'email' => $data['email'] ?? null,
            'contato' => $data['contato'] ?? null,
            'ativo' => $data['ativo'] ?? 1,
            'observacoes' => $data['observacoes'] ?? null
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza fornecedor
     */
    public function update($id, $data)
    {
        $sql = "UPDATE fornecedores SET
                    razao_social = :razao_social,
                    nome_fantasia = :nome_fantasia,
                    cnpj = :cnpj,
                    inscricao_estadual = :inscricao_estadual,
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    bairro = :bairro,
                    cidade = :cidade,
                    estado = :estado,
                    cep = :cep,
                    telefone = :telefone,
                    email = :email,
                    contato = :contato,
                    ativo = :ativo,
                    observacoes = :observacoes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'] ?? null,
            'cnpj' => $data['cnpj'],
            'inscricao_estadual' => $data['inscricao_estadual'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'cep' => $data['cep'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'email' => $data['email'] ?? null,
            'contato' => $data['contato'] ?? null,
            'ativo' => $data['ativo'] ?? 1,
            'observacoes' => $data['observacoes'] ?? null
        ]);
    }
    
    /**
     * Exclui fornecedor
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM fornecedores WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
