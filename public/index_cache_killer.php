<?php
/**
 * INDEX - Sprint 30 CACHE KILLER
 * Força limpeza de cache antes de carregar aplicação
 */

// === CACHE KILLER ===
if (function_exists('opcache_reset')) {
    opcache_reset();
}
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
}
clearstatcache(true);

// Forçar recompilação deste arquivo
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
    opcache_invalidate(__DIR__ . '/../src/Database.php', true);
    opcache_invalidate(__DIR__ . '/../src/DatabaseMigration.php', true);
}

// === CONTINUAR COM INDEX NORMAL ===

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==================== DEFINIR CONSTANTES ====================

define('ROOT_PATH', dirname(__DIR__)); // Parent directory of /public
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VENDOR_PATH', ROOT_PATH . '/vendor');

// ==================== AUTOLOADER ====================

spl_autoload_register(function($class) {
    // Converter namespace para caminho
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    // Converter para lowercase nas pastas (controllers, models, etc)
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    // Carregar arquivo se existir
    if (file_exists($file)) {
        // INVALIDATE OPCACHE ANTES DE CARREGAR
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
        
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

// ==================== MIGRATIONS DESABILITADAS ====================
// SPRINT 30 - Migrations completamente removidas do fluxo

// Carregar Database manualmente
require_once SRC_PATH . '/Database.php';

echo "<!-- Cache killer ativo - Sprint 30 -->\n";

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
