<?php // CACHE BUST: 1762901640 ?>
<?php
/**
 * Clinfec Prestadores - Front Controller
 * Entry Point da Aplicação - VERSÃO SUBPASTA
 * Local: public_html/prestadores/index.php
 * URL: https://clinfec.com.br/prestadores
 */

// ==================== DEBUG MODE ====================
// Sprint 20 - Temporary debug to diagnose blank pages
if (isset($_GET['debug']) && $_GET['debug'] === 'sprint20') {
    header('Content-Type: text/plain; charset=utf-8');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "=== SPRINT 20 DEBUG ===\n\n";
    echo "1. __DIR__: " . __DIR__ . "\n";
    echo "2. __FILE__: " . __FILE__ . "\n";
    echo "3. ROOT_PATH will be: " . dirname(__DIR__) . "\n\n";
    
    echo "4. Checking paths:\n";
    echo "   - ../src exists? " . (is_dir(dirname(__DIR__) . '/src') ? 'YES' : 'NO') . "\n";
    echo "   - ../config exists? " . (is_dir(dirname(__DIR__) . '/config') ? 'YES' : 'NO') . "\n";
    echo "   - ../vendor exists? " . (is_dir(dirname(__DIR__) . '/vendor') ? 'YES' : 'NO') . "\n\n";
    
    echo "5. Checking controller:\n";
    $controller_path = dirname(__DIR__) . '/src/Controllers/EmpresaTomadoraController.php';
    echo "   - Path: $controller_path\n";
    echo "   - Exists? " . (file_exists($controller_path) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($controller_path)) {
        echo "   - Size: " . filesize($controller_path) . " bytes\n";
    }
    
    echo "\n6. GET params:\n";
    echo "   - page: " . ($_GET['page'] ?? 'not set') . "\n";
    echo "   - action: " . ($_GET['action'] ?? 'not set') . "\n";
    
    exit;
}

// ==================== CONFIGURAÇÕES INICIAIS ====================

// Iniciar sessão
session_start();

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);  // Mude para 0 em produção
ini_set('log_errors', 1);

// ==================== DEFINIR CAMINHOS ====================

define('ROOT_PATH', dirname(__DIR__)); // Parent directory of /public
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('BASE_URL', ''); // FIXED: No subdirectory - FTP root = document root

// ==================== GERAR CSRF TOKEN ====================

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==================== AUTOLOADER PSR-4 ====================

spl_autoload_register(function ($class) {
    // Converter namespace para caminho de arquivo
    // Exemplo: App\Controllers\AuthController → src/controllers/AuthController.php
    
    // Remover prefixo App\
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    
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

// ==================== EXECUTAR MIGRATIONS ====================

try {
    // Importar classes necessárias
    require_once SRC_PATH . '/Database.php';
    require_once SRC_PATH . '/DatabaseMigration.php';
    
    if (!isset($_SESSION['migrations_executed'])) {
        $migration = new App\DatabaseMigration();
        $result = $migration->checkAndMigrate();
        
        if (!$result['success']) {
            error_log("Erro nas migrations: " . ($result['error'] ?? 'Erro desconhecido'));
            if (!empty($config['debug'])) {
                die("Erro ao executar migrations: " . ($result['error'] ?? 'Erro desconhecido'));
            }
        }
        
        $_SESSION['migrations_executed'] = true;
    }
} catch (Exception $e) {
    error_log("Erro ao executar migrations: " . $e->getMessage());
    if (!empty($config['debug'])) {
        die("Erro ao executar migrations: " . $e->getMessage());
    }
}

// ==================== OBTER PARÂMETROS ====================

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// ==================== DEBUG ROUTE (EXECUTES BEFORE LOGIN CHECK) ====================
// Access: ?page=debug-models-test
// This route bypasses authentication to allow error diagnosis
if ($page === 'debug-models-test') {
    // Disable output buffering and force immediate output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "=== DEBUG MODELS TEST ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    echo "PHP Version: " . PHP_VERSION . "\n\n";
    
    // Log file for persistent error capture
    $logFile = ROOT_PATH . '/debug_errors.log';
    $logContent = "";
    
    // Test 1: Projeto Model
    try {
        echo "[1] Testing Projeto Model...\n";
        $logContent .= "[1] Testing Projeto Model...\n";
        
        $projeto = new \App\Models\Projeto();
        $result = $projeto->all([], 1, 1);
        
        $success = "✅ SUCCESS: " . count($result) . " results\n\n";
        echo $success;
        $logContent .= $success;
        
    } catch (\Throwable $e) {
        $error = "❌ ERROR: " . $e->getMessage() . "\n";
        $error .= "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        $error .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
        
        echo $error;
        $logContent .= $error;
    }
    
    // Test 2: Atividade Model
    try {
        echo "[2] Testing Atividade Model...\n";
        $logContent .= "[2] Testing Atividade Model...\n";
        
        $atividade = new \App\Models\Atividade();
        $result = $atividade->all([], 1, 1);
        
        $success = "✅ SUCCESS: " . count($result) . " results\n\n";
        echo $success;
        $logContent .= $success;
        
    } catch (\Throwable $e) {
        $error = "❌ ERROR: " . $e->getMessage() . "\n";
        $error .= "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
        
        echo $error;
        $logContent .= $error;
    }
    
    // Test 3: NotaFiscal Model
    try {
        echo "[3] Testing NotaFiscal Model...\n";
        $logContent .= "[3] Testing NotaFiscal Model...\n";
        
        $nota = new \App\Models\NotaFiscal();
        $result = $nota->all([], 1, 1);
        
        $success = "✅ SUCCESS: " . count($result) . " results\n\n";
        echo $success;
        $logContent .= $success;
        
    } catch (\Throwable $e) {
        $error = "❌ ERROR: " . $e->getMessage() . "\n";
        $error .= "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
        
        echo $error;
        $logContent .= $error;
    }
    
    // Write to log file
    file_put_contents($logFile, 
        "=== DEBUG LOG ===\n" . 
        "Timestamp: " . date('Y-m-d H:i:s') . "\n" .
        "PHP Version: " . PHP_VERSION . "\n\n" .
        $logContent .
        "=== END DEBUG LOG ===\n"
    );
    
    echo "=== END DEBUG ===\n";
    echo "\nLog file written to: debug_errors.log\n";
    echo "Access log at: ?page=read-debug-log\n";
    
    exit;
}

// ==================== READ DEBUG LOG ROUTE ====================
// Access: ?page=read-debug-log
// Reads the debug_errors.log file if it exists
if ($page === 'read-debug-log') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    $logFile = ROOT_PATH . '/debug_errors.log';
    
    if (file_exists($logFile)) {
        echo "=== READING DEBUG LOG ===\n";
        echo "File: " . $logFile . "\n";
        echo "Size: " . filesize($logFile) . " bytes\n";
        echo "Modified: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n\n";
        echo file_get_contents($logFile);
    } else {
        echo "❌ Log file not found: " . $logFile . "\n";
        echo "Run ?page=debug-models-test first to generate the log.\n";
    }
    
    exit;
}

// ==================== VERIFICAR LOGIN ====================

$publicPages = ['login', 'logout', 'debug-models-test', 'read-debug-log'];

if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header('Location: ' . BASE_URL . '/?page=login');
    exit;
}

// ==================== ROTEAMENTO ====================

try {
    switch ($page) {
        
        // ==================== AUTENTICAÇÃO ====================
        case 'login':
            require_once SRC_PATH . '/controllers/AuthController.php';
            $controller = new App\Controllers\AuthController();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLoginForm();
            }
            break;
            
        case 'logout':
            require_once SRC_PATH . '/controllers/AuthController.php';
            $controller = new App\Controllers\AuthController();
            $controller->logout();
            break;
            
        // ==================== DASHBOARD ====================
        case 'dashboard':
            require SRC_PATH . '/views/dashboard/index.php';
            break;
            
        // ==================== EMPRESAS TOMADORAS ====================
        case 'empresas-tomadoras':
            require_once SRC_PATH . '/controllers/EmpresaTomadoraController.php';
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
            require_once SRC_PATH . '/controllers/EmpresaPrestadoraController.php';
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
            
        // ==================== SERVIÇOS ====================
        case 'servicos':
            require_once SRC_PATH . '/controllers/ServicoController.php';
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
            
        // ==================== CONTRATOS ====================
        case 'contratos':
            require_once SRC_PATH . '/controllers/ContratoController.php';
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
            
        // ==================== SERVICO VALORES ====================
        case 'servico-valores':
            require_once SRC_PATH . '/controllers/ServicoValorController.php';
            $controller = new App\Controllers\ServicoValorController();
            
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
                case 'getValorVigente':
                    $controller->getValorVigente();
                    break;
                case 'verificarSobreposicao':
                    $controller->verificarSobreposicao();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // ==================== PROJETOS ====================
        case 'projetos':
            require_once SRC_PATH . '/controllers/ProjetoController.php';
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
                case 'alterarStatus':
                    $controller->alterarStatus($id);
                    break;
                case 'destroy':
                    $controller->delete($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // ==================== PROJETO ETAPAS (CRONOGRAMA) ====================
        case 'projeto-etapas':
            require_once SRC_PATH . '/controllers/ProjetoEtapaController.php';
            $controller = new App\Controllers\ProjetoEtapaController();
            $projetoId = $_GET['projeto_id'] ?? $id;
            
            switch ($action) {
                case 'index':
                    $controller->index($projetoId);
                    break;
                case 'gantt':
                    $controller->gantt($projetoId);
                    break;
                case 'store':
                    $controller->store($projetoId);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'aprovar':
                    $controller->aprovar($id);
                    break;
                case 'destroy':
                    $controller->delete($id);
                    break;
                default:
                    $controller->index($projetoId);
            }
            break;
            
        // ==================== PROJETO EQUIPE ====================
        case 'projeto-equipe':
            require_once SRC_PATH . '/controllers/ProjetoEquipeController.php';
            $controller = new App\Controllers\ProjetoEquipeController();
            $projetoId = $_GET['projeto_id'] ?? $id;
            
            switch ($action) {
                case 'index':
                    $controller->index($projetoId);
                    break;
                case 'store':
                    $controller->store($projetoId);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'avaliar':
                    $controller->avaliar($id);
                    break;
                case 'destroy':
                    $controller->delete($id);
                    break;
                default:
                    $controller->index($projetoId);
            }
            break;
            
        // ==================== PROJETO ORÇAMENTO ====================
        case 'projeto-orcamento':
            require_once SRC_PATH . '/controllers/ProjetoOrcamentoController.php';
            $controller = new App\Controllers\ProjetoOrcamentoController();
            $projetoId = $_GET['projeto_id'] ?? $id;
            
            switch ($action) {
                case 'index':
                    $controller->index($projetoId);
                    break;
                case 'store':
                    $controller->store($projetoId);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'aprovar':
                    $controller->aprovar($id);
                    break;
                case 'destroy':
                    $controller->delete($id);
                    break;
                default:
                    $controller->index($projetoId);
            }
            break;
            
        // ==================== PROJETO EXECUÇÃO (APONTAMENTO) ====================
        case 'projeto-execucao':
            require_once SRC_PATH . '/controllers/ProjetoExecucaoController.php';
            $controller = new App\Controllers\ProjetoExecucaoController();
            $projetoId = $_GET['projeto_id'] ?? $id;
            
            switch ($action) {
                case 'index':
                    $controller->index($projetoId);
                    break;
                case 'store':
                    $controller->store($projetoId);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'aprovar':
                    $controller->aprovar($id);
                    break;
                case 'destroy':
                    $controller->delete($id);
                    break;
                default:
                    $controller->index($projetoId);
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
                        <h5>Stack Trace:</h5>
                        <pre style="text-align: left; max-height: 400px; overflow-y: auto;"><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
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