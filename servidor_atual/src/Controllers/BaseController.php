<?php

namespace App\Controllers;

/**
 * BaseController - Classe base para todos os controllers
 * Sprint 63 - Alinhado com arquitetura VPS
 */
abstract class BaseController
{
    /**
     * Construtor base
     */
    public function __construct()
    {
        // Base constructor
    }
    
    /**
     * Redirecionar para uma rota (USA QUERY STRINGS)
     * 
     * @param string $route Rota para redirecionar (ex: 'dashboard', 'auth@login')
     */
    protected function redirect($route)
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        
        // Processar rotas especiais
        if ($route === 'login') {
            $url = $baseUrl . '/?page=auth&action=showLoginForm';
        } elseif ($route === 'logout') {
            $url = $baseUrl . '/?page=auth&action=logout';
        } elseif ($route === 'dashboard') {
            $url = $baseUrl . '/?page=dashboard';
        } elseif (strpos($route, '@') !== false) {
            // Formato: controller@action
            list($page, $action) = explode('@', $route);
            $url = $baseUrl . '/?page=' . urlencode($page) . '&action=' . urlencode($action);
        } else {
            // Rota simples (só página)
            $url = $baseUrl . '/?page=' . urlencode($route);
        }
        
        header('Location: ' . $url);
        exit;
    }

    /**
     * Renderizar uma view
     * 
     * @param string $view Nome da view (ex: 'projetos/index')
     * @param array $data Dados para passar para a view
     */
    protected function render($view, $data = [])
    {
        // Extrair variáveis para o escopo da view
        extract($data);
        
        // Incluir o layout principal
        require ROOT_PATH . '/src/Views/layouts/main.php';
    }

    /**
     * Verificar permissões do usuário
     * 
     * @param array $allowedRoles Perfis permitidos (ex: ['master', 'admin'])
     */
    protected function checkPermission($allowedRoles = [])
    {
        // Verificar se está autenticado
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['erro'] = 'Você precisa estar autenticado para acessar esta página.';
            $this->redirect('login');
        }

        // Se não especificou perfis, permite qualquer usuário autenticado
        if (empty($allowedRoles)) {
            return;
        }

        // Verificar se o perfil do usuário está na lista de permitidos
        $userProfile = $_SESSION['usuario_perfil'] ?? '';
        
        if (!in_array($userProfile, $allowedRoles)) {
            $_SESSION['erro'] = 'Você não tem permissão para acessar esta página.';
            $this->redirect('dashboard');
        }
    }

    /**
     * Validar token CSRF
     * 
     * @param string $token Token recebido do formulário
     * @return bool True se válido, False caso contrário
     */
    protected function validateCSRF($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Retornar JSON como resposta
     * 
     * @param mixed $data Dados para retornar
     * @param int $statusCode Código HTTP de status
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Retornar erro JSON
     * 
     * @param string $message Mensagem de erro
     * @param int $statusCode Código HTTP de status
     */
    protected function jsonError($message, $statusCode = 400)
    {
        $this->json([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }

    /**
     * Retornar sucesso JSON
     * 
     * @param mixed $data Dados para retornar
     * @param string $message Mensagem de sucesso (opcional)
     */
    protected function jsonSuccess($data = null, $message = null)
    {
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->json($response);
    }
}
