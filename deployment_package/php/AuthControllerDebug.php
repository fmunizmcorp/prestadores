<?php
/**
 * Sprint 67 - Debug Version of AuthController
 * 
 * This file adds extensive logging to diagnose the login failure
 * reported by QA even after users were created correctly.
 * 
 * Usage: Replace AuthController.php temporarily with this file
 * or merge the debug code into the original.
 */

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
     * Login com DEBUG EXTENSIVO
     */
    public function login() {
        error_log("========== SPRINT 67 DEBUG - LOGIN ATTEMPT ==========");
        error_log("Timestamp: " . date('Y-m-d H:i:s'));
        error_log("Remote IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        error_log("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("DEBUG: Not a POST request, showing login form");
            $this->showLoginForm();
            return;
        }
        
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        error_log("DEBUG: POST data received");
        error_log("  - Email: " . $email);
        error_log("  - Password length: " . strlen($senha));
        error_log("  - POST keys: " . implode(', ', array_keys($_POST)));
        
        if (empty($email) || empty($senha)) {
            error_log("DEBUG: Empty email or password");
            $_SESSION['erro'] = 'E-mail e senha são obrigatórios.';
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
        
        error_log("DEBUG: Validating reCAPTCHA...");
        // Validar reCAPTCHA (Sprint 65)
        if (!$this->validateRecaptcha()) {
            error_log("DEBUG: reCAPTCHA validation failed");
            $_SESSION['erro'] = 'Validação de segurança falhou. Por favor, tente novamente.';
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
        error_log("DEBUG: reCAPTCHA validation passed");
        
        try {
            error_log("DEBUG: Finding user by email: $email");
            $usuario = $this->model->findByEmail($email);
            
            if (!$usuario) {
                error_log("DEBUG: User NOT FOUND in database");
                error_log("  - Email searched: $email");
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: /?page=auth&action=showLoginForm');
                exit;
            }
            
            error_log("DEBUG: User FOUND in database");
            error_log("  - User ID: " . $usuario['id']);
            error_log("  - User name: " . $usuario['nome']);
            error_log("  - User email: " . $usuario['email']);
            error_log("  - User role: " . $usuario['role']);
            error_log("  - User active: " . ($usuario['ativo'] ? 'YES' : 'NO'));
            error_log("  - Password hash (first 20 chars): " . substr($usuario['senha'], 0, 20) . '...');
            
            error_log("DEBUG: Verifying password...");
            $passwordVerified = password_verify($senha, $usuario['senha']);
            error_log("DEBUG: Password verification result: " . ($passwordVerified ? 'SUCCESS ✅' : 'FAILED ❌'));
            
            if (!$passwordVerified) {
                error_log("DEBUG: Password verification FAILED");
                error_log("  - Input password length: " . strlen($senha));
                error_log("  - Stored hash algorithm: " . password_get_info($usuario['senha'])['algoName']);
                
                // Try to rehash and compare
                $testHash = password_hash($senha, PASSWORD_DEFAULT);
                error_log("  - Test hash generated: " . substr($testHash, 0, 20) . '...');
                error_log("  - Test verification: " . (password_verify($senha, $testHash) ? 'OK' : 'FAIL'));
                
                $_SESSION['erro'] = 'E-mail ou senha inválidos.';
                header('Location: /?page=auth&action=showLoginForm');
                exit;
            }
            
            error_log("DEBUG: Checking if user is active...");
            if (!$usuario['ativo']) {
                error_log("DEBUG: User is INACTIVE");
                $_SESSION['erro'] = 'Usuário inativo. Entre em contato com o administrador.';
                header('Location: /?page=auth&action=showLoginForm');
                exit;
            }
            error_log("DEBUG: User is ACTIVE");
            
            // Criar sessão
            error_log("DEBUG: Creating session...");
            error_log("  - Session ID before: " . session_id());
            error_log("  - Session status: " . session_status());
            
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_perfil'] = $usuario['role'] ?? $usuario['perfil'] ?? 'usuario';
            $_SESSION['empresa_id'] = $usuario['empresa_id'] ?? null;
            
            error_log("DEBUG: Session created successfully");
            error_log("  - user_id: " . $_SESSION['user_id']);
            error_log("  - usuario_id: " . $_SESSION['usuario_id']);
            error_log("  - usuario_nome: " . $_SESSION['usuario_nome']);
            error_log("  - usuario_email: " . $_SESSION['usuario_email']);
            error_log("  - usuario_perfil: " . $_SESSION['usuario_perfil']);
            error_log("  - Session ID after: " . session_id());
            error_log("  - All session keys: " . implode(', ', array_keys($_SESSION)));
            
            // Atualizar último acesso
            error_log("DEBUG: Updating last login...");
            $this->model->updateLastLogin($usuario['id']);
            
            // Verificar se sessão persistiu
            error_log("DEBUG: Verifying session persistence...");
            if (isset($_SESSION['user_id'])) {
                error_log("  ✅ Session persisted - user_id: " . $_SESSION['user_id']);
            } else {
                error_log("  ❌ Session NOT persisted - user_id not set!");
            }
            
            // DEBUG: Session file check
            $sessionPath = session_save_path();
            error_log("DEBUG: Session save path: " . ($sessionPath ?: 'default'));
            if ($sessionPath && is_writable($sessionPath)) {
                error_log("  ✅ Session path is writable");
            } else {
                error_log("  ❌ Session path NOT writable or doesn't exist!");
            }
            
            $redirectUrl = '/?page=dashboard';
            error_log("DEBUG: LOGIN SUCCESS - Redirecting to: $redirectUrl");
            error_log("DEBUG: BASE_URL constant: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED'));
            
            $_SESSION['sucesso'] = 'Bem-vindo(a), ' . $usuario['nome'] . '!';
            
            error_log("DEBUG: About to redirect...");
            error_log("DEBUG: Headers sent? " . (headers_sent() ? 'YES' : 'NO'));
            
            header('Location: ' . $redirectUrl);
            exit;
            
        } catch (\Exception $e) {
            error_log("DEBUG: EXCEPTION CAUGHT during login");
            error_log("  - Message: " . $e->getMessage());
            error_log("  - File: " . $e->getFile());
            error_log("  - Line: " . $e->getLine());
            error_log("  - Trace: " . $e->getTraceAsString());
            
            $_SESSION['erro'] = 'Erro ao fazer login. Tente novamente.';
            header('Location: /?page=auth&action=showLoginForm');
            exit;
        }
        
        error_log("========== SPRINT 67 DEBUG - LOGIN ATTEMPT END ==========");
    }
    
    /**
     * Logout
     */
    public function logout() {
        error_log("DEBUG: Logout requested - user_id: " . ($_SESSION['user_id'] ?? 'not set'));
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
            error_log('[reCAPTCHA] Disabled in config');
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
