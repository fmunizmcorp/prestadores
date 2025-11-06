<?php
namespace App\Models;
use App\Database;
use PDO;

class ProjetoRisco {
    private $db;
    private $table = 'projeto_riscos';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getByProjeto($projetoId) {
        $sql = "SELECT * FROM {$this->table} WHERE projeto_id = :projeto_id AND ativo = 1 ORDER BY nivel_risco DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (projeto_id, titulo, descricao, categoria, probabilidade, impacto, status, estrategia, plano_acao, responsavel_id, data_identificacao, observacoes, created_by, created_at) 
                VALUES (:projeto_id, :titulo, :descricao, :categoria, :probabilidade, :impacto, :status, :estrategia, :plano_acao, :responsavel_id, :data_identificacao, :observacoes, :created_by, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $data['projeto_id'], PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $data['titulo']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':categoria', $data['categoria']);
        $stmt->bindValue(':probabilidade', $data['probabilidade'] ?? 'media');
        $stmt->bindValue(':impacto', $data['impacto'] ?? 'medio');
        $stmt->bindValue(':status', $data['status'] ?? 'identificado');
        $stmt->bindValue(':estrategia', $data['estrategia'] ?? 'mitigar');
        $stmt->bindValue(':plano_acao', $data['plano_acao'] ?? null);
        $stmt->bindValue(':responsavel_id', $data['responsavel_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':data_identificacao', $data['data_identificacao'] ?? date('Y-m-d'));
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET titulo = :titulo, descricao = :descricao, categoria = :categoria, probabilidade = :probabilidade, impacto = :impacto, status = :status, estrategia = :estrategia, plano_acao = :plano_acao, responsavel_id = :responsavel_id, observacoes = :observacoes, updated_by = :updated_by, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $data['titulo']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':categoria', $data['categoria']);
        $stmt->bindValue(':probabilidade', $data['probabilidade']);
        $stmt->bindValue(':impacto', $data['impacto']);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':estrategia', $data['estrategia']);
        $stmt->bindValue(':plano_acao', $data['plano_acao'] ?? null);
        $stmt->bindValue(':responsavel_id', $data['responsavel_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET ativo = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
