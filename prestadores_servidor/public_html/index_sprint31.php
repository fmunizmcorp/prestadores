<?php
/**
 * Clinfec Prestadores - Front Controller
 * Sprint 31 - SEM MIGRATIONS (contorna cache PHP 8.1)
 * Instalação manual via install.php
 */

// ==================== CONFIGURAÇÕES INICIAIS ====================

session_start();
date_default_timezone_set('America/Sao_Paulo');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// ==================== DEFINIR CAMINHOS ====================

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VENDOR_PATH', ROOT_PATH . '/vendor');

// ==================== CSRF TOKEN ====================

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==================== AUTOLOADER PSR-4 ====================

spl_autoload_register(function($class) {
    // Converter namespace para caminho
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    // Converter para lowercase nas pastas (controllers, models, etc)
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    // Carregar arquivo se existir
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
    die('ERRO: Arquivo config/database.php não encontrado! Configure as credenciais do banco de dados.');
}

$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// ==================== VERIFICAR SE SISTEMA ESTÁ INSTALADO ====================

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
    $testPdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar se tabela database_version existe
    $stmt = $testPdo->query("SHOW TABLES LIKE 'database_version'");
    if ($stmt->rowCount() == 0) {
        // Sistema não instalado - redirecionar para instalação
        if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
            header('Location: /prestadores/public/install.php');
            exit;
        }
    }
} catch (PDOException $e) {
    die('ERRO: Não foi possível conectar ao banco de dados. Verifique as configurações em config/database.php');
}

// ==================== CARREGAR DATABASE (SEM MIGRATIONS!) ====================

require_once SRC_PATH . '/Database.php';

// ==================== OBTER PARÂMETROS ====================

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// ==================== ROTEAMENTO ====================

// Mapeamento de páginas para controllers
$routes = [
    'dashboard' => 'DashboardController',
    'empresas-tomadoras' => 'EmpresaTomadoraController',
    'contratos' => 'ContratoController',
    'atestados' => 'AtestadoController',
    'faturas' => 'FaturaController',
    'documentos' => 'DocumentoController',
    'usuarios' => 'UsuarioController',
    'relatorios' => 'RelatorioController',
];

// Buscar controller
if (!isset($routes[$page])) {
    $page = 'dashboard';
}

$controllerName = 'App\\Controllers\\' . $routes[$page];

// Verificar se controller existe
if (!class_exists($controllerName)) {
    die("ERRO: Controller $controllerName não encontrado!");
}

// Instanciar e chamar ação
$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die("ERRO: Ação $action não encontrada em $controllerName!");
}

// Executar ação
$controller->$action($id);
