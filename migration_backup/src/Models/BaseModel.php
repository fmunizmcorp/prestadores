<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * BaseModel - Classe base para Models
 * Fornece funcionalidades comuns a todos os models
 */
class BaseModel
{
    protected $db;
    protected $table;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find by ID
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Delete (soft delete if deleted_at exists)
     */
    public function delete($id)
    {
        // Check if table has deleted_at column
        $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table} LIKE 'deleted_at'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Soft delete
            $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id");
        } else {
            // Hard delete
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        }
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
