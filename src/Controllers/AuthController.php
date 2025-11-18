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
        require __DIR__ . '/../Views/auth/login.php';
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
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
        
        // Validar reCAPTCHA (Sprint 65)
        if (!$this->validateRecaptcha()) {
            $_SESSION['erro'] = 'Validação de segurança falhou. Por favor, tente novamente.';
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
        
        try {
            $usuario = $this->model->findByEmail($email);
            
            if (!$usuario) {
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: /?page=auth&action=showLoginForm');
                exit;
            }
            
            if (!password_verify($senha, $usuario['senha'])) {
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: /?page=auth&action=showLoginForm');
                exit;
            }
            
            if (!$usuario['ativo']) {
                $_SESSION['erro'] = 'Usuário inativo. Entre em contato com o administrador.';
                header('Location: /?page=auth&action=showLoginForm');
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
            $redirectUrl = '/?page=dashboard';
            error_log("LOGIN SUCCESS - User: {$usuario['email']} - Redirecting to: {$redirectUrl}");
            error_log("BASE_URL constant: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED'));
            error_log("Session created - usuario_id: {$_SESSION['usuario_id']}, usuario_perfil: {$_SESSION['usuario_perfil']}");
            
            $_SESSION['sucesso'] = 'Bem-vindo(a), ' . $usuario['nome'] . '!';
            header('Location: ' . $redirectUrl);
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao fazer login. Tente novamente.';
            error_log($e->getMessage());
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        header('Location: /?page=auth&action=showLoginForm');
        exit;
    }
    
    /**
     * Verificar se usuário está autenticado
     */
    public static function checkAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
    }
    
    /**
     * Verificar se usuário tem perfil específico
     */
    public static function checkRole($roles) {
        if (!isset($_SESSION['usuario_perfil'])) {
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
        
        if (!in_array($_SESSION['usuario_perfil'], (array)$roles)) {
            $_SESSION['erro'] = 'Você não tem permissão para acessar esta página.';
            header('Location: /?page=dashboard');
            exit;
        }
    }
    
    /**
     * Validar Google reCAPTCHA v2
     * Sprint 65 - com opção de skip em desenvolvimento
     * 
     * @return bool
     */
    private function validateRecaptcha(): bool
    {
        // Carregar configurações
        $config = require __DIR__ . '/../../config/app.php';
        
        // Verificar se reCAPTCHA está habilitado
        if (!$config['recaptcha']['enabled']) {
            return true;
        }
        
        // Skip em desenvolvimento se configurado
        if ($config['recaptcha']['skip_in_development']) {
            error_log('[reCAPTCHA] Validation skipped - Development mode');
            return true;
        }
        
        // Verificar se o token foi enviado
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        
        if (empty($recaptchaResponse)) {
            error_log('[reCAPTCHA] Token not provided');
            return false;
        }
        
        // Validar com API do Google
        $secretKey = $config['recaptcha']['secret_key'];
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        
        $data = [
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($verifyUrl, false, $context);
        
        if ($result === false) {
            error_log('[reCAPTCHA] Failed to connect to Google API');
            // Em caso de erro na API, permitir login (fail-safe)
            return true;
        }
        
        $responseData = json_decode($result, true);
        
        if (!isset($responseData['success'])) {
            error_log('[reCAPTCHA] Invalid API response');
            return true; // fail-safe
        }
        
        if ($responseData['success']) {
            error_log('[reCAPTCHA] Validation successful');
            return true;
        } else {
            $errors = $responseData['error-codes'] ?? [];
            error_log('[reCAPTCHA] Validation failed: ' . implode(', ', $errors));
            return false;
        }
    }
}
