<?php
/**
 * AUTO DEPLOY SPRINT 31
 * Este arquivo executa automaticamente as mudanças necessárias quando acessado
 * 
 * COMO USAR:
 * 1. Fazer upload deste arquivo para /public_html/prestadores/public/
 * 2. Acessar: http://clinfec.com.br/prestadores/public/auto_deploy_sprint31.php
 * 3. Aguardar execução
 * 4. Sistema estará atualizado
 */

// Desabilitar limite de tempo
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autenticação simples
$DEPLOY_PASSWORD = 'sprint31deploy2024';
$provided_password = $_GET['password'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Deploy Sprint 31</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .step h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .success h3 { color: #28a745; }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .error h3 { color: #dc3545; }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .warning h3 { color: #ffc107; }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .info h3 { color: #17a2b8; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .progress {
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            animation: progress 2s ease-in-out;
        }
        @keyframes progress {
            from { width: 0%; }
            to { width: 100%; }
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.5;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Auto Deploy Sprint 31</h1>
            <p>Sistema: Clinfec Prestadores | Metodologia: SCRUM + PDCA</p>
        </div>
        
        <div class="content">
            <?php if ($provided_password !== $DEPLOY_PASSWORD): ?>
                
                <!-- TELA DE AUTENTICAÇÃO -->
                <div class="step warning">
                    <h3>🔐 Autenticação Necessária</h3>
                    <p>Por segurança, informe a senha de deploy para continuar:</p>
                </div>
                
                <form method="GET" style="margin-top: 20px;">
                    <input type="password" 
                           name="password" 
                           placeholder="Digite a senha de deploy" 
                           style="padding: 10px; width: 100%; border: 2px solid #667eea; border-radius: 5px; font-size: 14px;"
                           required>
                    <button type="submit" class="btn">Executar Deploy</button>
                </form>
                
                <div class="step info" style="margin-top: 20px;">
                    <h3>ℹ️ Informações</h3>
                    <p>Senha padrão: <code>sprint31deploy2024</code></p>
                    <p>Após executar, aguarde 2-3 minutos para o cache limpar.</p>
                </div>
                
            <?php else: ?>
                
                <!-- EXECUTAR DEPLOY -->
                <div class="progress">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
                
                <?php
                $steps_completed = 0;
                $steps_failed = 0;
                $total_steps = 5;
                
                // PASSO 1: Verificar diretórios
                echo '<div class="step">';
                echo '<h3>📁 Passo 1: Verificando Estrutura de Diretórios</h3>';
                
                $base_path = dirname(dirname(__FILE__));
                $public_path = __DIR__;
                $src_path = dirname(__FILE__) . '/../src';
                
                echo "<p>Base path: <code>$base_path</code></p>";
                echo "<p>Public path: <code>$public_path</code></p>";
                echo "<p>Src path: <code>$src_path</code></p>";
                
                if (is_dir($base_path) && is_dir($public_path) && is_dir($src_path)) {
                    echo '<p style="color: green; margin-top: 10px;">✅ Estrutura de diretórios OK</p>';
                    $steps_completed++;
                } else {
                    echo '<p style="color: red; margin-top: 10px;">❌ Erro na estrutura de diretórios</p>';
                    $steps_failed++;
                }
                echo '</div>';
                
                // PASSO 2: Backup index.php atual
                echo '<div class="step">';
                echo '<h3>💾 Passo 2: Backup do index.php Atual</h3>';
                
                $old_index = $public_path . '/index.php';
                $backup_index = $public_path . '/index.php.backup_' . date('Ymd_His');
                
                if (file_exists($old_index)) {
                    if (@copy($old_index, $backup_index)) {
                        echo "<p>✅ Backup criado: <code>" . basename($backup_index) . "</code></p>";
                        $steps_completed++;
                    } else {
                        echo "<p>⚠️ Não foi possível criar backup (permissões?)</p>";
                        $steps_failed++;
                    }
                } else {
                    echo "<p>ℹ️ index.php não existe (primeira instalação)</p>";
                    $steps_completed++;
                }
                echo '</div>';
                
                // PASSO 3: Criar novo index.php
                echo '<div class="step">';
                echo '<h3>📝 Passo 3: Criando Novo index.php</h3>';
                
                $new_index_content = <<<'PHP'
<?php
/**
 * Clinfec Prestadores - Front Controller
 * Sprint 31 - SEM MIGRATIONS
 */

session_start();
date_default_timezone_set('America/Sao_Paulo');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

if (!file_exists(CONFIG_PATH . '/config.php')) {
    die('ERRO: Arquivo config/config.php não encontrado!');
}

if (!file_exists(CONFIG_PATH . '/database.php')) {
    die('ERRO: Arquivo config/database.php não encontrado!');
}

$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// Verificar instalação do banco
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
    $testPdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $testPdo->query("SHOW TABLES LIKE 'database_version'");
    if ($stmt->rowCount() == 0) {
        die('ERRO: Sistema não instalado. Execute a instalação do banco de dados primeiro.');
    }
} catch (PDOException $e) {
    die('ERRO: Não foi possível conectar ao banco de dados.');
}

require_once SRC_PATH . '/Database.php';

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

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

if (!isset($routes[$page])) {
    $page = 'dashboard';
}

$controllerName = 'App\\Controllers\\' . $routes[$page];

if (!class_exists($controllerName)) {
    die("ERRO: Controller $controllerName não encontrado!");
}

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die("ERRO: Ação $action não encontrada em $controllerName!");
}

$controller->$action($id);
PHP;
                
                $new_index = $public_path . '/index.php';
                if (@file_put_contents($new_index, $new_index_content)) {
                    echo "<p>✅ Novo index.php criado com sucesso</p>";
                    echo "<p>Tamanho: " . filesize($new_index) . " bytes</p>";
                    $steps_completed++;
                } else {
                    echo "<p style='color: red;'>❌ Erro ao criar index.php (permissões?)</p>";
                    $steps_failed++;
                }
                echo '</div>';
                
                // PASSO 4: Remover DatabaseMigration.php
                echo '<div class="step">';
                echo '<h3>🗑️ Passo 4: Removendo DatabaseMigration.php</h3>';
                
                $migration_file = $src_path . '/DatabaseMigration.php';
                if (file_exists($migration_file)) {
                    $backup_migration = $src_path . '/DatabaseMigration.php.disabled_' . date('Ymd_His');
                    if (@rename($migration_file, $backup_migration)) {
                        echo "<p>✅ DatabaseMigration.php renomeado para: <code>" . basename($backup_migration) . "</code></p>";
                        $steps_completed++;
                    } else {
                        echo "<p style='color: red;'>❌ Erro ao renomear DatabaseMigration.php</p>";
                        $steps_failed++;
                    }
                } else {
                    echo "<p>ℹ️ DatabaseMigration.php não encontrado (já removido)</p>";
                    $steps_completed++;
                }
                echo '</div>';
                
                // PASSO 5: Limpar cache PHP
                echo '<div class="step">';
                echo '<h3>🔄 Passo 5: Limpando Cache PHP</h3>';
                
                $cache_cleared = false;
                
                // Tentar limpar OPcache
                if (function_exists('opcache_reset')) {
                    if (@opcache_reset()) {
                        echo "<p>✅ OPcache limpo com sucesso</p>";
                        $cache_cleared = true;
                    } else {
                        echo "<p>⚠️ opcache_reset() falhou</p>";
                    }
                } else {
                    echo "<p>ℹ️ OPcache não disponível via PHP</p>";
                }
                
                // Limpar stat cache
                clearstatcache(true);
                echo "<p>✅ Stat cache limpo</p>";
                
                if ($cache_cleared) {
                    $steps_completed++;
                } else {
                    echo "<p>⚠️ Cache PHP precisa ser limpo manualmente no hPanel</p>";
                    $steps_failed++;
                }
                
                echo '</div>';
                
                // RESUMO FINAL
                $progress_percent = ($steps_completed / $total_steps) * 100;
                
                if ($steps_failed == 0) {
                    echo '<div class="step success">';
                    echo '<h3>🎉 Deploy Concluído com Sucesso!</h3>';
                    echo "<p><strong>Progresso:</strong> $steps_completed/$total_steps passos completados ($progress_percent%)</p>";
                    echo '<p style="margin-top: 15px;"><strong>Próximas ações:</strong></p>';
                    echo '<ol style="margin-left: 20px; margin-top: 10px;">';
                    echo '<li>Aguardar 2-3 minutos para cache limpar completamente</li>';
                    echo '<li>Acessar: <a href="/prestadores/" target="_blank">http://clinfec.com.br/prestadores/</a></li>';
                    echo '<li>Fazer login com: <code>admin@clinfec.com.br</code></li>';
                    echo '<li>Verificar que erro DatabaseMigration sumiu</li>';
                    echo '<li>Testar Dashboard, Empresas e Contratos</li>';
                    echo '</ol>';
                    echo '</div>';
                } else {
                    echo '<div class="step warning">';
                    echo '<h3>⚠️ Deploy Parcialmente Concluído</h3>';
                    echo "<p><strong>Progresso:</strong> $steps_completed/$total_steps passos completados</p>";
                    echo "<p><strong>Falhas:</strong> $steps_failed passos falharam</p>";
                    echo '<p style="margin-top: 15px;">Algumas operações podem ter falhado devido a permissões.</p>';
                    echo '<p>Verifique as permissões dos arquivos e tente novamente.</p>';
                    echo '</div>';
                }
                
                // Informações técnicas
                echo '<div class="step info">';
                echo '<h3>🔧 Informações Técnicas</h3>';
                echo '<pre>';
                echo "PHP Version: " . PHP_VERSION . "\n";
                echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
                echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
                echo "Script Filename: " . __FILE__ . "\n";
                echo "OPcache Enabled: " . (function_exists('opcache_get_status') ? 'Yes' : 'No') . "\n";
                echo "Execution Time: " . number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2) . "s\n";
                echo '</pre>';
                echo '</div>';
                
                ?>
                
                <a href="/" class="btn">🏠 Ir para o Sistema</a>
                
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p><strong>Sprint 31</strong> | Clinfec Prestadores | SCRUM + PDCA</p>
            <p>Desenvolvido com metodologia ágil | <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
