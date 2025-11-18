<?php

namespace App\Controllers;

use App\Models\Custo;
use App\Models\CentroCusto;

/**
 * CustoController
 * Sprint 70.2 - Módulo de Custos Operacionais
 */
class CustoController {
    private $model;
    private $centroCustoModel;
    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /?page=login');
            exit;
        }
        
        $this->model = new Custo();
        $this->centroCustoModel = new CentroCusto();
    }
    
    public function index() {
        try {
            $page = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
            $page = max(1, $page);
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            $filtros = [];
            if (!empty($_GET['tipo'])) $filtros['tipo'] = $_GET['tipo'];
            if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
            if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
            if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
            
            $resultado = $this->model->all($filtros, $page, $limit);
            $custos = $resultado['data'];
            $total = $resultado['total'];
            $totalPaginas = $resultado['pages'];
            
            $stats = $this->model->getEstatisticas($filtros);
            
            $tipos = [
                Custo::TIPO_FIXO => 'Fixo',
                Custo::TIPO_VARIAVEL => 'Variável',
                Custo::TIPO_OPERACIONAL => 'Operacional',
                Custo::TIPO_ADMINISTRATIVO => 'Administrativo',
                Custo::TIPO_FORNECEDOR => 'Fornecedor'
            ];
            
            $statusDisponiveis = [
                Custo::STATUS_PENDENTE => 'Pendente',
                Custo::STATUS_APROVADO => 'Aprovado',
                Custo::STATUS_PAGO => 'Pago',
                Custo::STATUS_CANCELADO => 'Cancelado'
            ];
            
            require __DIR__ . '/../Views/custos/index.php';
            
        } catch (\Throwable $e) {
            error_log("CustoController::index error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao carregar custos.';
            $custos = [];
            $total = 0;
            $totalPaginas = 0;
            $stats = [];
            require __DIR__ . '/../Views/custos/index.php';
        }
    }
    
    public function create() {
        $centros = $this->centroCustoModel->all([], 1, 100);
        
        $tipos = [
            Custo::TIPO_FIXO => 'Fixo',
            Custo::TIPO_VARIAVEL => 'Variável',
            Custo::TIPO_OPERACIONAL => 'Operacional',
            Custo::TIPO_ADMINISTRATIVO => 'Administrativo',
            Custo::TIPO_FORNECEDOR => 'Fornecedor'
        ];
        
        require __DIR__ . '/../Views/custos/create.php';
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=custos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token inválido.';
            header('Location: /?page=custos&action=create');
            exit;
        }
        
        try {
            $data = [
                'tipo' => $_POST['tipo'] ?? '',
                'descricao' => $_POST['descricao'] ?? '',
                'valor' => (float)($_POST['valor'] ?? 0),
                'data_custo' => $_POST['data_custo'] ?? date('Y-m-d'),
                'centro_custo_id' => !empty($_POST['centro_custo_id']) ? (int)$_POST['centro_custo_id'] : null,
                'fornecedor' => $_POST['fornecedor'] ?? null,
                'numero_documento' => $_POST['numero_documento'] ?? null,
                'categoria' => $_POST['categoria'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? '',
                'criado_por' => $_SESSION['usuario_id']
            ];
            
            $id = $this->model->create($data);
            $_SESSION['sucesso'] = 'Custo cadastrado com sucesso!';
            header('Location: /?page=custos&action=show&id=' . $id);
            exit;
            
        } catch (\Exception $e) {
            error_log("CustoController::store error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao salvar custo.';
            $_SESSION['form_data'] = $_POST;
            header('Location: /?page=custos&action=create');
            exit;
        }
    }
    
    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $_SESSION['erro'] = 'ID inválido.';
            header('Location: /?page=custos');
            exit;
        }
        
        $custo = $this->model->findById($id);
        if (!$custo) {
            $_SESSION['erro'] = 'Custo não encontrado.';
            header('Location: /?page=custos');
            exit;
        }
        
        require __DIR__ . '/../Views/custos/show.php';
    }
    
    public function aprovar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=custos');
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $this->model->aprovar($id);
        $_SESSION['sucesso'] = 'Custo aprovado!';
        header('Location: /?page=custos&action=show&id=' . $id);
        exit;
    }
    
    public function marcar_pago() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=custos');
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $this->model->marcarPago($id);
        $_SESSION['sucesso'] = 'Custo marcado como pago!';
        header('Location: /?page=custos&action=show&id=' . $id);
        exit;
    }
    
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=custos');
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $this->model->delete($id);
        $_SESSION['sucesso'] = 'Custo excluído!';
        header('Location: /?page=custos');
        exit;
    }
}
