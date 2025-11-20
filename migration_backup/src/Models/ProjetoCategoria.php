<?php

namespace App\Models;

use App\Database;
use PDO;

class ProjetoCategoria
{
    private $db;
    private $table = 'projeto_categorias';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ativo = TRUE 
                ORDER BY nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    nome, descricao, cor, icone
                ) VALUES (
                    :nome, :descricao, :cor, :icone
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'cor' => $data['cor'] ?? '#6c757d',
            'icone' => $data['icone'] ?? 'folder'
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                    nome = :nome,
                    descricao = :descricao,
                    cor = :cor,
                    icone = :icone
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'cor' => $data['cor'] ?? '#6c757d',
            'icone' => $data['icone'] ?? 'folder'
        ]);
    }

    public function delete($id)
    {
        // Verificar se há projetos usando esta categoria
        $sql = "SELECT COUNT(*) as total FROM projetos 
                WHERE categoria_id = :id AND ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['total'] > 0) {
            return false; // Não pode excluir categoria em uso
        }

        $sql = "UPDATE {$this->table} SET ativo = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function countProjetos($id)
    {
        $sql = "SELECT COUNT(*) as total FROM projetos 
                WHERE categoria_id = :id AND ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'];
    }
}
