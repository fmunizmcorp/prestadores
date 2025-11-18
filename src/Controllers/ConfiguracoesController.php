<?php

namespace App\Controllers;

use App\Models\SystemSetting;

/**
 * ConfiguracoesController - Gerenciar Configurações do Sistema
 * Sprint 65 - SMTP e Configurações Gerais
 * 
 * Apenas usuários Master e Admin podem acessar
 */
class ConfiguracoesController extends BaseController
{
    /**
     * Construtor - Verificar permissões
     */
    public function __construct()
    {
        parent::__construct();
        
        // Verificar se está logado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth@showLoginForm');
            exit;
        }
        
        // Verificar se tem permissão (Master ou Admin)
        $userRole = $_SESSION['user_role'] ?? 'usuario';
        if (!in_array($userRole, ['master', 'admin'])) {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem acessar configurações.';
            $this->redirect('dashboard');
            exit;
        }
    }
    
    /**
     * Página principal de configurações
     */
    public function index()
    {
        $pageTitle = 'Configurações do Sistema';
        $categories = SystemSetting::getCategories();
        
        require_once __DIR__ . '/../Views/layouts/header.php';
        require_once __DIR__ . '/../Views/configuracoes/index.php';
        require_once __DIR__ . '/../Views/layouts/footer.php';
    }
    
    /**
     * Configurações de Email/SMTP
     */
    public function email()
    {
        $pageTitle = 'Configurações de Email';
        
        // Se for POST, salvar configurações
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = 'Token de segurança inválido.';
                $this->redirect('configuracoes@email');
                exit;
            }
            
            // Validar campos
            $errors = [];
            
            if (empty($_POST['smtp_host'])) {
                $errors[] = 'Servidor SMTP é obrigatório.';
            }
            
            if (empty($_POST['smtp_port']) || !is_numeric($_POST['smtp_port'])) {
                $errors[] = 'Porta SMTP inválida.';
            }
            
            if (empty($_POST['smtp_from_email']) || !filter_var($_POST['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email remetente inválido.';
            }
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
            } else {
                // Salvar configurações
                $config = [
                    'smtp_host' => trim($_POST['smtp_host']),
                    'smtp_port' => (int)$_POST['smtp_port'],
                    'smtp_secure' => $_POST['smtp_secure'] ?? 'tls',
                    'smtp_username' => trim($_POST['smtp_username'] ?? ''),
                    'smtp_from_email' => trim($_POST['smtp_from_email']),
                    'smtp_from_name' => trim($_POST['smtp_from_name'] ?? 'Sistema Clinfec'),
                    'smtp_enabled' => isset($_POST['smtp_enabled']) ? 1 : 0
                ];
                
                // Salvar senha apenas se foi fornecida
                if (!empty($_POST['smtp_password'])) {
                    $config['smtp_password'] = $_POST['smtp_password'];
                }
                
                if (SystemSetting::saveSmtpConfig($config)) {
                    $_SESSION['success'] = 'Configurações de email salvas com sucesso!';
                } else {
                    $_SESSION['error'] = 'Erro ao salvar configurações de email.';
                }
            }
            
            $this->redirect('configuracoes@email');
            exit;
        }
        
        // Carregar configurações atuais
        $smtpConfig = SystemSetting::getSmtpConfig();
        
        require_once __DIR__ . '/../Views/layouts/header.php';
        require_once __DIR__ . '/../Views/configuracoes/email.php';
        require_once __DIR__ . '/../Views/layouts/footer.php';
    }
    
    /**
     * Testar envio de email
     */
    public function testEmail()
    {
        // Apenas POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('configuracoes@email');
            exit;
        }
        
        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Token de segurança inválido.';
            $this->redirect('configuracoes@email');
            exit;
        }
        
        $testEmail = trim($_POST['test_email'] ?? '');
        
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email de teste inválido.';
            $this->redirect('configuracoes@email');
            exit;
        }
        
        // Carregar serviço de email
        require_once __DIR__ . '/../Services/EmailService.php';
        
        try {
            $emailService = new \App\Services\EmailService();
            $result = $emailService->sendTestEmail($testEmail);
            
            if ($result) {
                $_SESSION['success'] = "Email de teste enviado com sucesso para {$testEmail}!";
            } else {
                $_SESSION['error'] = 'Falha ao enviar email de teste. Verifique as configurações.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao enviar email: ' . $e->getMessage();
        }
        
        $this->redirect('configuracoes@email');
        exit;
    }
    
    /**
     * Configurações gerais do sistema
     */
    public function geral()
    {
        $pageTitle = 'Configurações Gerais';
        
        // Se for POST, salvar configurações
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = 'Token de segurança inválido.';
                $this->redirect('configuracoes@geral');
                exit;
            }
            
            // Salvar configurações gerais
            $success = true;
            
            if (isset($_POST['system_name'])) {
                $success = $success && SystemSetting::set('system_name', trim($_POST['system_name']));
            }
            
            if (isset($_POST['system_timezone'])) {
                $success = $success && SystemSetting::set('system_timezone', $_POST['system_timezone']);
            }
            
            if ($success) {
                $_SESSION['success'] = 'Configurações gerais salvas com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao salvar configurações gerais.';
            }
            
            $this->redirect('configuracoes@geral');
            exit;
        }
        
        // Carregar configurações atuais
        $generalSettings = SystemSetting::getByCategory('general');
        
        require_once __DIR__ . '/../Views/layouts/header.php';
        require_once __DIR__ . '/../Views/configuracoes/geral.php';
        require_once __DIR__ . '/../Views/layouts/footer.php';
    }
}
