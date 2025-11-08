<?php

namespace App\Controllers;

use App\Models\Usuario;

class AuthController {
    private $model;
    
    public function __construct() {
        $this->model = new Usuario();
    }
    
    /**
     * Mostrar formulário de login
     */
    public function showLoginForm() {
        require __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLoginForm();
            return;
        }
        
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            $_SESSION['erro'] = 'E-mail e senha são obrigatórios.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        
        try {
            $usuario = $this->model->findByEmail($email);
            
            if (!$usuario) {
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
                exit;
            }
            
            if (!password_verify($senha, $usuario['senha'])) {
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
                exit;
            }
            
            if (!$usuario['ativo']) {
                $_SESSION['erro'] = 'Usuário inativo. Entre em contato com o administrador.';
                header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
                exit;
            }
            
            // Criar sessão
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_perfil'] = $usuario['role'] ?? $usuario['perfil'] ?? 'usuario';
            $_SESSION['empresa_id'] = $usuario['empresa_id'] ?? null;
            
            // Atualizar último acesso
            $this->model->updateLastLogin($usuario['id']);
            
            // DEBUG LOG
            $redirectUrl = (defined('BASE_URL') ? BASE_URL : '') . '/dashboard';
            error_log("LOGIN SUCCESS - User: {$usuario['email']} - Redirecting to: {$redirectUrl}");
            error_log("BASE_URL constant: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED'));
            error_log("Session created - usuario_id: {$_SESSION['usuario_id']}, usuario_perfil: {$_SESSION['usuario_perfil']}");
            
            $_SESSION['sucesso'] = 'Bem-vindo(a), ' . $usuario['nome'] . '!';
            header('Location: ' . $redirectUrl);
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao fazer login. Tente novamente.';
            error_log($e->getMessage());
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
        exit;
    }
    
    /**
     * Verificar se usuário está autenticado
     */
    public static function checkAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }
    
    /**
     * Verificar se usuário tem perfil específico
     */
    public static function checkRole($roles) {
        if (!isset($_SESSION['usuario_perfil'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        
        if (!in_array($_SESSION['usuario_perfil'], (array)$roles)) {
            $_SESSION['erro'] = 'Você não tem permissão para acessar esta página.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/dashboard');
            exit;
        }
    }
}
