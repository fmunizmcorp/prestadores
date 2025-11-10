<?php
/**
 * Model de Usuário
 */

namespace App\Models;

use App\Database;
use PDO;

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca usuário por email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Busca usuário por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Cria novo usuário
     */
    public function create($data) {
        $sql = "INSERT INTO usuarios (nome, email, senha, role, email_verificado, token_verificacao) 
                VALUES (:nome, :email, :senha, :role, :email_verificado, :token_verificacao)";
        
        $stmt = $this->db->prepare($sql);
        
        $passwordHash = password_hash($data['senha'], PASSWORD_DEFAULT);
        $tokenVerificacao = bin2hex(random_bytes(32));
        
        $result = $stmt->execute([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => $passwordHash,
            'role' => $data['role'] ?? 'usuario',
            'email_verificado' => $data['email_verificado'] ?? false,
            'token_verificacao' => $tokenVerificacao
        ]);
        
        if ($result) {
            return [
                'id' => $this->db->lastInsertId(),
                'token_verificacao' => $tokenVerificacao
            ];
        }
        
        return false;
    }
    
    /**
     * Atualiza usuário
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        
        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    /**
     * Verifica senha
     */
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['senha']);
    }
    
    /**
     * Registra tentativa de login
     */
    public function registerLoginAttempt($userId, $success = false) {
        $user = $this->findById($userId);
        
        if ($success) {
            // Reset tentativas e atualiza último acesso
            $this->update($userId, [
                'tentativas_login' => 0,
                'bloqueado_ate' => null,
                'ultimo_acesso' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Incrementa tentativas
            $tentativas = $user['tentativas_login'] + 1;
            $config = require __DIR__ . '/../../config/app.php';
            $maxAttempts = $config['security']['max_login_attempts'];
            
            $updateData = ['tentativas_login' => $tentativas];
            
            // Bloqueia se exceder tentativas
            if ($tentativas >= $maxAttempts) {
                $lockoutTime = $config['security']['lockout_time'];
                $updateData['bloqueado_ate'] = date('Y-m-d H:i:s', time() + $lockoutTime);
            }
            
            $this->update($userId, $updateData);
        }
    }
    
    /**
     * Verifica se usuário está bloqueado
     */
    public function isLocked($user) {
        if (!$user['bloqueado_ate']) {
            return false;
        }
        
        $now = time();
        $lockExpiry = strtotime($user['bloqueado_ate']);
        
        if ($now < $lockExpiry) {
            return true;
        }
        
        // Desbloqueio automático
        $this->update($user['id'], [
            'tentativas_login' => 0,
            'bloqueado_ate' => null
        ]);
        
        return false;
    }
    
    /**
     * Gera token de recuperação de senha
     */
    public function generatePasswordResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $config = require __DIR__ . '/../../config/app.php';
        $expiry = date('Y-m-d H:i:s', time() + $config['security']['token_expiry']);
        
        $this->update($userId, [
            'token_recuperacao' => $token,
            'token_recuperacao_expira' => $expiry
        ]);
        
        return $token;
    }
    
    /**
     * Valida token de recuperação
     */
    public function validateResetToken($token) {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE token_recuperacao = ? AND token_recuperacao_expira > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    /**
     * Reseta senha
     */
    public function resetPassword($userId, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->update($userId, [
            'senha' => $passwordHash,
            'token_recuperacao' => null,
            'token_recuperacao_expira' => null,
            'tentativas_login' => 0,
            'bloqueado_ate' => null
        ]);
    }
    
    /**
     * Verifica email
     */
    public function verifyEmail($token) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE token_verificacao = ? LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            return $this->update($user['id'], [
                'email_verificado' => true,
                'token_verificacao' => null
            ]);
        }
        
        return false;
    }
    
    /**
     * Atualiza último acesso do usuário
     */
    public function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (\Exception $e) {
            error_log("Erro ao atualizar último acesso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todos os usuários
     */
    public function all($filters = []) {
        $sql = "SELECT u.*, GROUP_CONCAT(e.nome_fantasia SEPARATOR ', ') as empresas
                FROM usuarios u
                LEFT JOIN usuario_empresa ue ON u.id = ue.usuario_id
                LEFT JOIN empresas e ON ue.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        // Handle both 'role' and 'perfil' parameters (same meaning)
        if (isset($filters['role']) || isset($filters['perfil'])) {
            $roleFilter = $filters['role'] ?? $filters['perfil'];
            
            // Handle array of roles
            if (is_array($roleFilter)) {
                $placeholders = str_repeat('?,', count($roleFilter) - 1) . '?';
                $sql .= " AND u.role IN ($placeholders)";
                $params = array_merge($params, $roleFilter);
            } else {
                $sql .= " AND u.role = ?";
                $params[] = $roleFilter;
            }
        }
        
        if (isset($filters['ativo'])) {
            $sql .= " AND u.ativo = ?";
            $params[] = $filters['ativo'];
        }
        
        $sql .= " GROUP BY u.id ORDER BY u.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Vincula usuário a empresa
     */
    public function vincularEmpresa($usuarioId, $empresaId, $cargo = null) {
        $sql = "INSERT INTO usuario_empresa (usuario_id, empresa_id, cargo) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE cargo = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuarioId, $empresaId, $cargo, $cargo]);
    }
    
    /**
     * Remove vínculo com empresa
     */
    public function desvincularEmpresa($usuarioId, $empresaId) {
        $sql = "DELETE FROM usuario_empresa WHERE usuario_id = ? AND empresa_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuarioId, $empresaId]);
    }
    
    /**
     * Busca empresas do usuário
     */
    public function getEmpresas($usuarioId) {
        $sql = "SELECT e.*, ue.cargo 
                FROM empresas e
                INNER JOIN usuario_empresa ue ON e.id = ue.empresa_id
                WHERE ue.usuario_id = ?
                ORDER BY e.nome_fantasia ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        
        return $stmt->fetchAll();
    }
}
