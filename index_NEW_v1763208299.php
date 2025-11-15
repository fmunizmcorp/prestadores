<?php
/**
 * Clinfec Prestadores - Front Controller
 * Entry Point - VERSÃO HOSTINGER COMPARTILHADA
 * Local: /public_html/prestadores/index.php (RAIZ do subdomínio)
 * URL: https://prestadores.clinfec.com.br/
 */

// ==================== CACHE CONTROL ====================
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
clearstatcache(true);

// ==================== CONFIGURAÇÕES INICIAIS ====================
session_start();
date_default_timezone_set('America/Sao_Paulo');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// ==================== DEFINIR CAMINHOS ====================
// SIMPLIFICADO: index.php está na RAIZ
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('BASE_URL', '');

// ==================== GERAR CSRF TOKEN ====================
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==================== AUTOLOADER PSR-4 ====================
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    // NÃO converter para lowercase - pastas já estão em PascalCase!
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// ==================== CARREGAR CONFIGURAÇÕES ====================
if (!file_exists(CONFIG_PATH . '/config.php')) {
    die('ERRO: Arquivo config/config.php não encontrado!');
}

if (!file_exists(CONFIG_PATH . '/database.php')) {
    die('ERRO: Arquivo config/database.php não encontrado!');
}

$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// ==================== CARREGAR DATABASE ====================
require_once SRC_PATH . '/Database.php';

// ==================== OBTER PARÂMETROS ====================
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// ==================== VERIFICAR LOGIN ====================
$publicPages = ['login', 'logout'];

if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header('Location: /?page=login');
    exit;
}

// ==================== ROTEAMENTO ====================
try {
    switch ($page) {
        
        // ==================== AUTENTICAÇÃO ====================
        case 'login':
            $controller = new App\Controllers\AuthController();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLoginForm();
            }
            break;
            
        case 'logout':
            $controller = new App\Controllers\AuthController();
            $controller->logout();
            break;
            
        // ==================== DASHBOARD ====================
        case 'dashboard':
            require SRC_PATH . '/views/dashboard/index.php';
            break;
            
        // ==================== EMPRESAS TOMADORAS ====================
        case 'empresas-tomadoras':
            $controller = new App\Controllers\EmpresaTomadoraController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // ==================== EMPRESAS PRESTADORAS ====================
        case 'empresas-prestadoras':
            $controller = new App\Controllers\EmpresaPrestadoraController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // ==================== CONTRATOS ====================
        case 'contratos':
            $controller = new App\Controllers\ContratoController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // ==================== SERVIÇOS ====================
        case 'servicos':
            $controller = new App\Controllers\ServicoController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // ==================== PROJETOS ====================
        case 'projetos':
            $controller = new App\Controllers\ProjetoController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'dashboard':
                    $controller->dashboard($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->delete($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // ==================== 404 ====================
        default:
            http_response_code(404);
            ?>
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <title>404 - Página não encontrada</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-5">
                    <div class="text-center">
                        <h1 class="display-1">404</h1>
                        <h2>Página não encontrada</h2>
                        <a href="/" class="btn btn-primary">Voltar para o início</a>
                    </div>
                </div>
            </body>
            </html>
            <?php
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>500 - Erro Interno</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="text-center">
                <h1 class="display-1">500</h1>
                <h2>Erro Interno do Servidor</h2>
                
                <?php if (!empty($config['debug'])): ?>
                    <div class="alert alert-danger text-start mt-4">
                        <h4>Detalhes do erro:</h4>
                        <p><strong>Mensagem:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
                        <p><strong>Arquivo:</strong> <?= htmlspecialchars($e->getFile()) ?></p>
                        <p><strong>Linha:</strong> <?= $e->getLine() ?></p>
                    </div>
                <?php endif; ?>
                
                <a href="/" class="btn btn-primary mt-3">Voltar para o início</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
