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

// Definir BASE_PATH para URLs (detecta automaticamente se está em subpasta)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_PATH', $scriptName !== '/' ? $scriptName : '');

// Definir BASE_URL com domínio completo (ABSOLUTE URL)
// Novo domínio: prestadores.clinfec.com.br (raiz, sem subpasta)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'prestadores.clinfec.com.br';
$basePath = BASE_PATH;
define('BASE_URL', $protocol . '://' . $host . $basePath);

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
try {
    $migration = new DatabaseMigration();
    $migration->checkAndMigrate();
} catch (Exception $e) {
    error_log("Erro ao executar migrations: " . $e->getMessage());
    // Continua mesmo com erro - permite visualizar página de erro
}

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
                } elseif ($parts[2] === 'faturamento') {
                    $controller->faturamento($id);
                } elseif ($parts[2] === 'gerar-fatura' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->gerarFatura($id);
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
            
        // Projetos
        case 'projetos':
            $controller = new App\Controllers\ProjetoController();
            
            if (!isset($parts[1])) {
                $controller->index();
            } elseif ($parts[1] === 'create') {
                $controller->create();
            } elseif (is_numeric($parts[1])) {
                $id = $parts[1];
                
                if (!isset($parts[2])) {
                    $controller->show($id);
                } elseif ($parts[2] === 'edit') {
                    $controller->edit($id);
                } elseif ($parts[2] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->delete($id);
                } elseif ($parts[2] === 'dashboard') {
                    $controller->dashboard($id);
                } elseif ($parts[2] === 'financeiro') {
                    $controller->financeiro($id);
                } elseif ($parts[2] === 'alterar-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->alterarStatus($id);
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->store();
            }
            break;
            
        // Atividades
        case 'atividades':
            $controller = new App\Controllers\AtividadeController();
            
            if (!isset($parts[1])) {
                $controller->index();
            } elseif ($parts[1] === 'create') {
                $controller->create();
            } elseif (is_numeric($parts[1])) {
                $id = $parts[1];
                
                if (!isset($parts[2])) {
                    $controller->show($id);
                } elseif ($parts[2] === 'edit') {
                    $controller->edit($id);
                } elseif ($parts[2] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->delete($id);
                } elseif ($parts[2] === 'custos') {
                    $controller->custos($id);
                } elseif ($parts[2] === 'alterar-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->alterarStatus($id);
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->store();
            }
            break;
            
        // Financeiro
        case 'financeiro':
            $controller = new App\Controllers\FinanceiroController();
            $action = $_GET['action'] ?? 'index';
            
            switch ($action) {
                case 'index':
                case '':
                    $controller->index();
                    break;
                    
                // Categorias Financeiras
                case 'categorias':
                    $controller->categorias();
                    break;
                case 'categoria-create':
                    $controller->categoriaCreate();
                    break;
                case 'categoria-store':
                    $controller->categoriaStore();
                    break;
                case 'categoria-edit':
                    $id = $_GET['id'] ?? 0;
                    $controller->categoriaEdit($id);
                    break;
                case 'categoria-update':
                    $id = $_POST['id'] ?? 0;
                    $controller->categoriaUpdate($id);
                    break;
                case 'categoria-toggle-ativo':
                    $id = $_POST['id'] ?? 0;
                    $controller->categoriaToggleAtivo($id);
                    break;
                    
                // Contas a Pagar
                case 'contas-pagar':
                    $controller->contasPagar();
                    break;
                case 'conta-pagar-create':
                    $controller->contaPagarCreate();
                    break;
                case 'conta-pagar-store':
                    $controller->contaPagarStore();
                    break;
                case 'conta-pagar-show':
                    $id = $_GET['id'] ?? 0;
                    $controller->contaPagarShow($id);
                    break;
                case 'conta-pagar-pagar':
                    $id = $_POST['id'] ?? 0;
                    $controller->contaPagarPagar($id);
                    break;
                case 'conta-pagar-cancelar':
                    $id = $_POST['id'] ?? 0;
                    $controller->contaPagarCancelar($id);
                    break;
                    
                // Contas a Receber
                case 'contas-receber':
                    $controller->contasReceber();
                    break;
                case 'conta-receber-create':
                    $controller->contaReceberCreate();
                    break;
                case 'conta-receber-store':
                    $controller->contaReceberStore();
                    break;
                case 'conta-receber-show':
                    $id = $_GET['id'] ?? 0;
                    $controller->contaReceberShow($id);
                    break;
                case 'conta-receber-receber':
                    $id = $_POST['id'] ?? 0;
                    $controller->contaReceberReceber($id);
                    break;
                case 'conta-receber-cancelar':
                    $id = $_POST['id'] ?? 0;
                    $controller->contaReceberCancelar($id);
                    break;
                    
                // Boletos
                case 'boletos':
                    $controller->boletos();
                    break;
                case 'boleto-show':
                    $id = $_GET['id'] ?? 0;
                    $controller->boletoShow($id);
                    break;
                case 'boleto-pagar':
                    $id = $_POST['id'] ?? 0;
                    $controller->boletoPagar($id);
                    break;
                case 'boleto-cancelar':
                    $id = $_POST['id'] ?? 0;
                    $controller->boletoCancelar($id);
                    break;
                    
                // Lançamentos Financeiros
                case 'lancamentos':
                    $controller->lancamentos();
                    break;
                case 'lancamento-create':
                    $controller->lancamentoCreate();
                    break;
                case 'lancamento-store':
                    $controller->lancamentoStore();
                    break;
                case 'lancamento-estornar':
                    $id = $_POST['id'] ?? 0;
                    $controller->lancamentoEstornar($id);
                    break;
                    
                // Conciliação Bancária
                case 'conciliacoes':
                    $controller->conciliacoes();
                    break;
                case 'conciliacao-importar':
                    $controller->conciliacaoImportar();
                    break;
                case 'conciliacao-processar-ofx':
                    $controller->conciliacaoProcessarOFX();
                    break;
                case 'conciliacao-show':
                    $id = $_GET['id'] ?? 0;
                    $controller->conciliacaoShow($id);
                    break;
                case 'conciliacao-vincular':
                    $controller->conciliacaoVincular();
                    break;
                    
                // Fluxo de Caixa
                case 'fluxo-caixa':
                    $controller->fluxoCaixa();
                    break;
                    
                // Relatórios
                case 'dre':
                    $controller->dre();
                    break;
                case 'balancete':
                    $controller->balancete();
                    break;
                    
                default:
                    $controller->index();
                    break;
            }
            break;
            
        // Notas Fiscais
        case 'notas-fiscais':
            $controller = new App\Controllers\NotaFiscalController();
            $action = $_GET['action'] ?? 'index';
            
            switch ($action) {
                case 'index':
                case '':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $id = $_GET['id'] ?? 0;
                    $controller->show($id);
                    break;
                case 'edit':
                    $id = $_GET['id'] ?? 0;
                    $controller->edit($id);
                    break;
                case 'update':
                    $id = $_POST['id'] ?? 0;
                    $controller->update($id);
                    break;
                case 'emitir':
                    $id = $_POST['id'] ?? 0;
                    $controller->emitir($id);
                    break;
                case 'consultar-status':
                    $id = $_POST['id'] ?? 0;
                    $controller->consultarStatus($id);
                    break;
                case 'form-cancelar':
                    $id = $_GET['id'] ?? 0;
                    $controller->formCancelar($id);
                    break;
                case 'cancelar':
                    $id = $_POST['id'] ?? 0;
                    $controller->cancelar($id);
                    break;
                case 'form-carta-correcao':
                    $id = $_GET['id'] ?? 0;
                    $controller->formCartaCorrecao($id);
                    break;
                case 'carta-correcao':
                    $id = $_POST['id'] ?? 0;
                    $controller->cartaCorrecao($id);
                    break;
                case 'download-xml':
                    $id = $_GET['id'] ?? 0;
                    $controller->downloadXML($id);
                    break;
                case 'download-danfe':
                    $id = $_GET['id'] ?? 0;
                    $controller->downloadDANFE($id);
                    break;
                case 'relatorio':
                    $controller->relatorio();
                    break;
                case 'delete':
                    $id = $_POST['id'] ?? 0;
                    $controller->delete($id);
                    break;
                default:
                    $controller->index();
                    break;
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
        header('Location: ' . BASE_URL . '/');
    } else {
        echo '<h1>500 - Erro Interno do Servidor</h1>';
        echo '<p>Ocorreu um erro ao processar sua solicitação.</p>';
        if (ini_get('display_errors')) {
            echo '<pre>' . $e->getMessage() . '</pre>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    }
}
