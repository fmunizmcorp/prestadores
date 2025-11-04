<?php
/**
 * Front Controller - Sistema Clinfec
 * Ponto de entrada único da aplicação
 */

// Iniciar sessão
session_start();

// Gerar CSRF Token se não existir
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Definir diretório raiz
define('ROOT_PATH', dirname(__DIR__));

// Autoloader simples
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Carregar configurações
require ROOT_PATH . '/config/database.php';
require ROOT_PATH . '/config/version.php';

// Executar migrações automaticamente
use App\DatabaseMigration;
$migration = new DatabaseMigration();
$migration->runMigrations();

// Obter URL requisitada
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$url = str_replace($script_name, '', $request_uri);
$url = trim($url, '/');
$url = parse_url($url, PHP_URL_PATH);

// Separar URL em partes
$parts = explode('/', $url);
$route = $parts[0] ?? 'dashboard';

// Roteamento
try {
    switch ($route) {
        // Dashboard
        case '':
        case 'dashboard':
            require ROOT_PATH . '/src/views/dashboard/index.php';
            break;
            
        // Login
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new App\Controllers\AuthController();
                $controller->login();
            } else {
                require ROOT_PATH . '/src/views/auth/login.php';
            }
            break;
            
        case 'logout':
            $controller = new App\Controllers\AuthController();
            $controller->logout();
            break;
            
        // Empresas Tomadoras
        case 'empresas-tomadoras':
            $controller = new App\Controllers\EmpresaTomadoraController();
            
            if (!isset($parts[1])) {
                $controller->index();
            } elseif ($parts[1] === 'create') {
                $controller->create();
            } elseif ($parts[1] === 'buscar-cep') {
                $controller->buscarCep();
            } elseif (is_numeric($parts[1])) {
                $id = $parts[1];
                
                if (!isset($parts[2])) {
                    $controller->show($id);
                } elseif ($parts[2] === 'edit') {
                    $controller->edit($id);
                } elseif ($parts[2] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->destroy($id);
                } elseif ($parts[2] === 'responsaveis') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addResponsavel($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteResponsavel($id, $parts[3]);
                    }
                } elseif ($parts[2] === 'documentos') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addDocumento($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteDocumento($id, $parts[3]);
                    }
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->store();
            }
            break;
            
        // Empresas Prestadoras
        case 'empresas-prestadoras':
            $controller = new App\Controllers\EmpresaPrestadoraController();
            
            if (!isset($parts[1])) {
                $controller->index();
            } elseif ($parts[1] === 'create') {
                $controller->create();
            } elseif ($parts[1] === 'buscar-cep') {
                $controller->buscarCep();
            } elseif (is_numeric($parts[1])) {
                $id = $parts[1];
                
                if (!isset($parts[2])) {
                    $controller->show($id);
                } elseif ($parts[2] === 'edit') {
                    $controller->edit($id);
                } elseif ($parts[2] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->destroy($id);
                } elseif ($parts[2] === 'representantes') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addRepresentante($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteRepresentante($id, $parts[3]);
                    }
                } elseif ($parts[2] === 'documentos') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addDocumento($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteDocumento($id, $parts[3]);
                    }
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->store();
            }
            break;
            
        // Serviços
        case 'servicos':
            $controller = new App\Controllers\ServicoController();
            
            if (!isset($parts[1])) {
                $controller->index();
            } elseif ($parts[1] === 'create') {
                $controller->create();
            } elseif ($parts[1] === 'subcategorias') {
                $controller->getSubcategorias();
            } elseif ($parts[1] === 'valor-vigente') {
                $controller->getValorVigente();
            } elseif (is_numeric($parts[1])) {
                $id = $parts[1];
                
                if (!isset($parts[2])) {
                    $controller->show($id);
                } elseif ($parts[2] === 'edit') {
                    $controller->edit($id);
                } elseif ($parts[2] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->destroy($id);
                } elseif ($parts[2] === 'requisitos') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addRequisito($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteRequisito($id, $parts[3]);
                    }
                } elseif ($parts[2] === 'valores') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addValorReferencia($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteValorReferencia($id, $parts[3]);
                    }
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->store();
            }
            break;
            
        // Contratos
        case 'contratos':
            $controller = new App\Controllers\ContratoController();
            
            if (!isset($parts[1])) {
                $controller->index();
            } elseif ($parts[1] === 'create') {
                $controller->create();
            } elseif ($parts[1] === 'vencendo') {
                $controller->getVencendo();
            } elseif (is_numeric($parts[1])) {
                $id = $parts[1];
                
                if (!isset($parts[2])) {
                    $controller->show($id);
                } elseif ($parts[2] === 'edit') {
                    $controller->edit($id);
                } elseif ($parts[2] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->destroy($id);
                } elseif ($parts[2] === 'servicos') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addServico($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteServico($id, $parts[3]);
                    }
                } elseif ($parts[2] === 'aditivos') {
                    if ($parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addAditivo($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->deleteAditivo($id, $parts[3]);
                    }
                } elseif ($parts[2] === 'valores' && $parts[3] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->addValorPeriodo($id);
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->store();
            }
            break;
            
        // 404
        default:
            http_response_code(404);
            echo '<h1>404 - Página não encontrada</h1>';
            echo '<p><a href="/">Voltar para o início</a></p>';
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    
    if (isset($_SESSION['usuario_id'])) {
        $_SESSION['erro'] = 'Erro interno: ' . $e->getMessage();
        header('Location: /');
    } else {
        echo '<h1>500 - Erro Interno do Servidor</h1>';
        echo '<p>Ocorreu um erro ao processar sua solicitação.</p>';
        if (ini_get('display_errors')) {
            echo '<pre>' . $e->getMessage() . '</pre>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    }
}
