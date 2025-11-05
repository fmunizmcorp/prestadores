<?php
/**
 * Clinfec Prestadores - Front Controller
 * Entry Point da Aplicação - VERSÃO SUBPASTA
 * Local: public_html/prestadores/index.php
 * URL: https://clinfec.com.br/prestadores
 */

// Iniciar sessão
session_start();

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurar error reporting (EM PRODUÇÃO: display_errors = 0)
error_reporting(E_ALL);
ini_set('display_errors', 1);  // Mude para 0 em produção
ini_set('log_errors', 1);

// Definir constantes de caminho
define('ROOT_PATH', __DIR__);  // Raiz é public_html/prestadores/
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('BASE_URL', '/prestadores');  // Importante para subpasta

// Gerar CSRF Token se não existir
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Autoloader PSR-4
spl_autoload_register(function ($class) {
    // Namespace base
    $prefix = 'App\\';
    
    // Diretório base
    $base_dir = SRC_PATH . '/';
    
    // Verificar se a classe usa o namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Nome relativo da classe
    $relative_class = substr($class, $len);
    
    // Substituir namespace por diretório
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Carregar arquivo se existir
    if (file_exists($file)) {
        require $file;
    }
});

// Carregar configurações (verifica se existem)
if (!file_exists(CONFIG_PATH . '/config.php')) {
    die('ERRO: Arquivo config/config.php não encontrado! Verifique a instalação.');
}

if (!file_exists(CONFIG_PATH . '/database.php')) {
    die('ERRO: Arquivo config/database.php não encontrado! Configure as credenciais do banco de dados.');
}

$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// Importar classes necessárias
use App\Database;
use App\DatabaseMigration;

// Executar migrations automaticamente (apenas uma vez)
try {
    if (!isset($_SESSION['migrations_executed'])) {
        $migration = new DatabaseMigration();
        $result = $migration->runMigrations();
        $_SESSION['migrations_executed'] = true;
    }
} catch (Exception $e) {
    // Log error
    error_log("Erro ao executar migrations: " . $e->getMessage());
    
    // Mostrar erro apenas se debug estiver ativo
    if (!empty($config['debug'])) {
        die("Erro ao executar migrations: " . $e->getMessage());
    }
}

// Verificar se usuário está logado (exceto páginas públicas)
$publicPages = ['login', 'logout'];

// Pegar a página da URL
$page = $_GET['page'] ?? 'dashboard';

// Se não está logado e não é página pública, redirecionar para login
if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header('Location: ' . BASE_URL . '/?page=login');
    exit;
}

// Roteamento simples baseado em query string
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Importar controllers
use App\Controllers\AuthController;
use App\Controllers\EmpresaTomadoraController;
use App\Controllers\EmpresaPrestadoraController;
use App\Controllers\ServicoController;
use App\Controllers\ContratoController;

// Roteamento
try {
    switch ($page) {
        // ==================== AUTENTICAÇÃO ====================
        case 'login':
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLoginForm();
            }
            break;
            
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
            
        // ==================== DASHBOARD ====================
        case 'dashboard':
            require SRC_PATH . '/views/dashboard/index.php';
            break;
            
        // ==================== EMPRESAS TOMADORAS ====================
        case 'empresas-tomadoras':
            $controller = new EmpresaTomadoraController();
            
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
            $controller = new EmpresaPrestadoraController();
            
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
            $controller = new ServicoController();
            
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
            $controller = new ContratoController();
            
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
            
        // ==================== 404 ====================
        default:
            http_response_code(404);
            ?>
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>404 - Página não encontrada</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-5">
                    <div class="text-center">
                        <h1 class="display-1">404</h1>
                        <h2>Página não encontrada</h2>
                        <p class="lead">A página que você está procurando não existe.</p>
                        <a href="<?= BASE_URL ?>/" class="btn btn-primary">Voltar para o início</a>
                    </div>
                </div>
            </body>
            </html>
            <?php
            break;
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Erro na aplicação: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Resposta de erro
    http_response_code(500);
    
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>500 - Erro Interno</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="text-center">
                <h1 class="display-1">500</h1>
                <h2>Erro Interno do Servidor</h2>
                <p class="lead">Ocorreu um erro ao processar sua solicitação.</p>
                
                <?php if (!empty($config['debug']) && $config['debug'] === true): ?>
                    <div class="alert alert-danger text-start mt-4">
                        <h4>Detalhes do erro (modo debug):</h4>
                        <p><strong>Mensagem:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
                        <p><strong>Arquivo:</strong> <?= htmlspecialchars($e->getFile()) ?></p>
                        <p><strong>Linha:</strong> <?= $e->getLine() ?></p>
                        <hr>
                        <pre><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
                    </div>
                <?php else: ?>
                    <p>Por favor, tente novamente mais tarde ou entre em contato com o suporte.</p>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/" class="btn btn-primary mt-3">Voltar para o início</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
