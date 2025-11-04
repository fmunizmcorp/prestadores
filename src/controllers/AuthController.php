<?php

namespace App\Controllers;

use App\Models\Usuario;

class AuthController {
    private $model;
    
    public function __construct() {
        $this->model = new Usuario();
    }
    
    /**
     * Login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../views/auth/login.php';
            return;
        }
        
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            $_SESSION['erro'] = 'E-mail e senha são obrigatórios.';
            header('Location: /login');
            exit;
        }
        
        try {
            $usuario = $this->model->findByEmail($email);
            
            if (!$usuario) {
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: /login');
                exit;
            }
            
            if (!password_verify($senha, $usuario['senha'])) {
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: /login');
                exit;
            }
            
            if (!$usuario['ativo']) {
                $_SESSION['erro'] = 'Usuário inativo. Entre em contato com o administrador.';
                header('Location: /login');
                exit;
            }
            
            // Criar sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];
            $_SESSION['empresa_id'] = $usuario['empresa_id'] ?? null;
            
            // Atualizar último acesso
            $this->model->updateLastLogin($usuario['id']);
            
            $_SESSION['sucesso'] = 'Bem-vindo(a), ' . $usuario['nome'] . '!';
            header('Location: /');
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao fazer login. Tente novamente.';
            error_log($e->getMessage());
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
    
    /**
     * Verificar se usuário está autenticado
     */
    public static function checkAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Verificar se usuário tem perfil específico
     */
    public static function checkRole($roles) {
        if (!isset($_SESSION['usuario_perfil'])) {
            header('Location: /login');
            exit;
        }
        
        if (!in_array($_SESSION['usuario_perfil'], (array)$roles)) {
            $_SESSION['erro'] = 'Você não tem permissão para acessar esta página.';
            header('Location: /');
            exit;
        }
    }
}
