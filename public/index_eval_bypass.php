<?php
/**
 * INDEX - Sprint 30 EVAL BYPASS
 * Carrega Database.php via eval() para forçar recompilação
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==================== DEFINIR CONSTANTES ====================

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VENDOR_PATH', ROOT_PATH . '/vendor');

// ==================== AUTOLOADER ====================

spl_autoload_register(function($class) {
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// ==================== CARREGAR CONFIGS ====================

if (!file_exists(CONFIG_PATH . '/config.php')) {
    die('ERRO: Arquivo config/config.php não encontrado!');
}

if (!file_exists(CONFIG_PATH . '/database.php')) {
    die('ERRO: Arquivo config/database.php não encontrado!');
}

$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// ==================== FORÇAR RELOAD DE DATABASE.PHP ====================

echo "<!-- EVAL BYPASS: Carregando Database.php via eval() -->\n";

// Ler conteúdo do arquivo
$database_file = SRC_PATH . '/Database.php';
if (!file_exists($database_file)) {
    die("ERRO: $database_file não encontrado!");
}

$database_code = file_get_contents($database_file);

// Remover tags PHP de abertura/fechamento
$database_code = preg_replace('/^<\?php/', '', $database_code);
$database_code = preg_replace('/\?>$/', '', $database_code);

// Executar via eval (força recompilação)
eval($database_code);

echo "<!-- EVAL BYPASS: Database.php carregado com sucesso -->\n";

// ==================== MIGRATIONS DESABILITADAS ====================

echo "<!-- Migrations desabilitadas - Sprint 30 -->\n";

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
