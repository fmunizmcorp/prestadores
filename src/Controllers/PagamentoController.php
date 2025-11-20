<?php

namespace App\Controllers;

use App\Models\Pagamento;

/**
 * PagamentoController
 * 
 * Controller para gerenciar pagamentos e recebimentos
 * Sprint 70.1 - Implementação completa CRUD
 */
class PagamentoController {
    private $model;
    
    public function __construct() {
        // Verificar autenticação
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=login');
            exit;
        }
        
        try {
            $this->model = new Pagamento();
        } catch (\Exception $e) {
            error_log("PagamentoController::__construct error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * LISTAGEM DE PAGAMENTOS
     */
    public function index() {
        try {
            // SPRINT 70: Usar paginação correta com $_GET['pag']
            $page = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
            $page = max(1, $page);
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $limit = max(1, min(100, $limit));
            
            // Filtros
            $filtros = [];
            
            if (!empty($_GET['origem_tipo'])) {
                $filtros['origem_tipo'] = $_GET['origem_tipo'];
            }
            
            if (!empty($_GET['forma_pagamento'])) {
                $filtros['forma_pagamento'] = $_GET['forma_pagamento'];
            }
            
            if (!empty($_GET['status'])) {
                $filtros['status'] = $_GET['status'];
            }
            
            if (!empty($_GET['data_inicio'])) {
                $filtros['data_pagamento_inicio'] = $_GET['data_inicio'];
            }
            
            if (!empty($_GET['data_fim'])) {
                $filtros['data_pagamento_fim'] = $_GET['data_fim'];
            }
            
            // Buscar dados
            $resultado = $this->model->all($filtros, $page, $limit);
            $pagamentos = $resultado['data'];
            $total = $resultado['total'];
            $totalPaginas = $resultado['pages'];
            
            // Estatísticas
            $stats = $this->model->getEstatisticas($filtros);
            
            // Formas de pagamento disponíveis
            $formasPagamento = [
                Pagamento::FORMA_DINHEIRO => 'Dinheiro',
                Pagamento::FORMA_PIX => 'PIX',
                Pagamento::FORMA_BOLETO => 'Boleto',
                Pagamento::FORMA_TRANSFERENCIA => 'Transferência',
                Pagamento::FORMA_CREDITO => 'Cartão Crédito',
                Pagamento::FORMA_DEBITO => 'Cartão Débito',
                Pagamento::FORMA_CHEQUE => 'Cheque',
                Pagamento::FORMA_DEPOSITO => 'Depósito'
            ];
            
            // Status disponíveis
            $statusDisponiveis = [
                Pagamento::STATUS_PENDENTE => 'Pendente',
                Pagamento::STATUS_PROCESSADO => 'Processado',
                Pagamento::STATUS_CONFIRMADO => 'Confirmado',
                Pagamento::STATUS_ESTORNADO => 'Estornado',
                Pagamento::STATUS_CANCELADO => 'Cancelado'
            ];
            
            require __DIR__ . '/../Views/pagamentos/index.php';
            
        } catch (\Throwable $e) {
            error_log("PagamentoController::index error: " . $e->getMessage());
            error_log($e->getTraceAsString());
            $_SESSION['erro'] = 'Erro ao carregar pagamentos. Tente novamente.';
            
            // Fallback
            $pagamentos = [];
            $total = 0;
            $totalPaginas = 0;
            $stats = ['total' => 0, 'valor_total' => 0];
            $page = 1;
            $limit = 20;
            $formasPagamento = [];
            $statusDisponiveis = [];
            
            require __DIR__ . '/../Views/pagamentos/index.php';
        }
    }
    
    /**
     * FORMULÁRIO DE CRIAÇÃO
     */
    public function create() {
        try {
            // Formas de pagamento
            $formasPagamento = [
                Pagamento::FORMA_DINHEIRO => 'Dinheiro',
                Pagamento::FORMA_PIX => 'PIX',
                Pagamento::FORMA_BOLETO => 'Boleto',
                Pagamento::FORMA_TRANSFERENCIA => 'Transferência',
                Pagamento::FORMA_CREDITO => 'Cartão Crédito',
                Pagamento::FORMA_DEBITO => 'Cartão Débito',
                Pagamento::FORMA_CHEQUE => 'Cheque',
                Pagamento::FORMA_DEPOSITO => 'Depósito'
            ];
            
            // Tipos de origem
            $tiposOrigem = [
                Pagamento::ORIGEM_CONTA_PAGAR => 'Conta a Pagar',
                Pagamento::ORIGEM_CONTA_RECEBER => 'Conta a Receber',
                Pagamento::ORIGEM_LANCAMENTO => 'Lançamento Direto'
            ];
            
            require __DIR__ . '/../Views/pagamentos/create.php';
            
        } catch (\Exception $e) {
            error_log("PagamentoController::create error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao carregar formulário.';
            header('Location: /?page=pagamentos');
            exit;
        }
    }
    
    /**
     * SALVAR NOVO PAGAMENTO
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=pagamentos');
            exit;
        }
        
        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: /?page=pagamentos&action=create');
            exit;
        }
        
        try {
            // Preparar dados
            $data = [
                'origem_tipo' => $_POST['origem_tipo'] ?? '',
                'origem_id' => (int)($_POST['origem_id'] ?? 0),
                'valor' => (float)($_POST['valor'] ?? 0),
                'valor_taxa' => (float)($_POST['valor_taxa'] ?? 0),
                'valor_desconto' => (float)($_POST['valor_desconto'] ?? 0),
                'data_pagamento' => $_POST['data_pagamento'] ?? date('Y-m-d'),
                'forma_pagamento' => $_POST['forma_pagamento'] ?? '',
                'banco_id' => !empty($_POST['banco_id']) ? (int)$_POST['banco_id'] : null,
                'conta_bancaria_id' => !empty($_POST['conta_bancaria_id']) ? (int)$_POST['conta_bancaria_id'] : null,
                'numero_documento' => $_POST['numero_documento'] ?? null,
                'numero_autorizacao' => $_POST['numero_autorizacao'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? '',
                'criado_por' => $_SESSION['usuario_id']
            ];
            
            // Processar pagamento
            $id = $this->model->processar($data);
            
            $_SESSION['sucesso'] = 'Pagamento registrado com sucesso!';
            header('Location: /?page=pagamentos&action=show&id=' . $id);
            exit;
            
        } catch (\Exception $e) {
            error_log("PagamentoController::store error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao salvar pagamento: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: /?page=pagamentos&action=create');
            exit;
        }
    }
    
    /**
     * VISUALIZAR PAGAMENTO
     */
    public function show() {
        try {
            $id = (int)($_GET['id'] ?? 0);
            
            if (!$id) {
                $_SESSION['erro'] = 'ID inválido.';
                header('Location: /?page=pagamentos');
                exit;
            }
            
            $pagamento = $this->model->findById($id);
            
            if (!$pagamento) {
                $_SESSION['erro'] = 'Pagamento não encontrado.';
                header('Location: /?page=pagamentos');
                exit;
            }
            
            require __DIR__ . '/../Views/pagamentos/show.php';
            
        } catch (\Exception $e) {
            error_log("PagamentoController::show error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao carregar pagamento.';
            header('Location: /?page=pagamentos');
            exit;
        }
    }
    
    /**
     * CONFIRMAR PAGAMENTO PENDENTE
     */
    public function confirmar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=pagamentos');
            exit;
        }
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            
            if (!$id) {
                $_SESSION['erro'] = 'ID inválido.';
                header('Location: /?page=pagamentos');
                exit;
            }
            
            $dadosConfirmacao = [
                'numero_autorizacao' => $_POST['numero_autorizacao'] ?? '',
                'observacao' => $_POST['observacao'] ?? ''
            ];
            
            $this->model->confirmar($id, $dadosConfirmacao);
            
            $_SESSION['sucesso'] = 'Pagamento confirmado com sucesso!';
            header('Location: /?page=pagamentos&action=show&id=' . $id);
            exit;
            
        } catch (\Exception $e) {
            error_log("PagamentoController::confirmar error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao confirmar pagamento: ' . $e->getMessage();
            header('Location: /?page=pagamentos');
            exit;
        }
    }
    
    /**
     * ESTORNAR PAGAMENTO
     */
    public function estornar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=pagamentos');
            exit;
        }
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            $motivo = $_POST['motivo'] ?? '';
            
            if (!$id) {
                $_SESSION['erro'] = 'ID inválido.';
                header('Location: /?page=pagamentos');
                exit;
            }
            
            if (empty($motivo)) {
                $_SESSION['erro'] = 'Motivo do estorno é obrigatório.';
                header('Location: /?page=pagamentos&action=show&id=' . $id);
                exit;
            }
            
            $this->model->estornar($id, $motivo);
            
            $_SESSION['sucesso'] = 'Pagamento estornado com sucesso!';
            header('Location: /?page=pagamentos&action=show&id=' . $id);
            exit;
            
        } catch (\Exception $e) {
            error_log("PagamentoController::estornar error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao estornar pagamento: ' . $e->getMessage();
            header('Location: /?page=pagamentos');
            exit;
        }
    }
    
    /**
     * CANCELAR PAGAMENTO PENDENTE
     */
    public function cancelar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=pagamentos');
            exit;
        }
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            $motivo = $_POST['motivo'] ?? '';
            
            if (!$id) {
                $_SESSION['erro'] = 'ID inválido.';
                header('Location: /?page=pagamentos');
                exit;
            }
            
            if (empty($motivo)) {
                $_SESSION['erro'] = 'Motivo do cancelamento é obrigatório.';
                header('Location: /?page=pagamentos&action=show&id=' . $id);
                exit;
            }
            
            $this->model->cancelar($id, $motivo);
            
            $_SESSION['sucesso'] = 'Pagamento cancelado com sucesso!';
            header('Location: /?page=pagamentos&action=show&id=' . $id);
            exit;
            
        } catch (\Exception $e) {
            error_log("PagamentoController::cancelar error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao cancelar pagamento: ' . $e->getMessage();
            header('Location: /?page=pagamentos');
            exit;
        }
    }
    
    /**
     * EXCLUIR PAGAMENTO (SOFT DELETE)
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=pagamentos');
            exit;
        }
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            
            if (!$id) {
                $_SESSION['erro'] = 'ID inválido.';
                header('Location: /?page=pagamentos');
                exit;
            }
            
            $this->model->delete($id);
            
            $_SESSION['sucesso'] = 'Pagamento excluído com sucesso!';
            header('Location: /?page=pagamentos');
            exit;
            
        } catch (\Exception $e) {
            error_log("PagamentoController::delete error: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao excluir pagamento: ' . $e->getMessage();
            header('Location: /?page=pagamentos');
            exit;
        }
    }
}
