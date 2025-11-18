<?php

namespace App\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Boleto;
use App\Models\Pagamento;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\LancamentoFinanceiro;
use App\Models\ConciliacaoBancaria;
use App\Helpers\FluxoCaixaHelper;

/**
 * FinanceiroController
 * 
 * Gerencia todas as operações financeiras do sistema:
 * - Categorias financeiras (hierárquicas)
 * - Centros de custo
 * - Boletos bancários
 * - Pagamentos
 * - Contas a pagar
 * - Contas a receber
 * - Lançamentos financeiros (partidas dobradas)
 * - Conciliação bancária
 * - Fluxo de caixa e relatórios
 * 
 * @package App\Controllers
 * @author Clinfec Prestadores
 * @since Sprint 7
 */
class FinanceiroController extends BaseController
{
    private $categoriaModel;
    private $centroCustoModel;
    private $boletoModel;
    private $pagamentoModel;
    private $contaPagarModel;
    private $contaReceberModel;
    private $lancamentoModel;
    private $conciliacaoModel;
    private $fluxoCaixaHelper;

    public function __construct()
    {
        parent::__construct();
        
        try {
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->boletoModel = new Boleto();
            $this->pagamentoModel = new Pagamento();
            $this->contaPagarModel = new ContaPagar();
            $this->contaReceberModel = new ContaReceber();
            $this->lancamentoModel = new LancamentoFinanceiro();
            $this->conciliacaoModel = new ConciliacaoBancaria();
            $this->fluxoCaixaHelper = new FluxoCaixaHelper();
        } catch (\Throwable $e) {
            error_log("FinanceiroController construct error: " . $e->getMessage());
            $this->categoriaModel = null;
        }
    }

    // ============================================================================
    // DASHBOARD FINANCEIRO
    // ============================================================================

    /**
     * Dashboard principal do módulo financeiro
     */
    public function index()
    {
        if ($this->categoriaModel === null) {
            require ROOT_PATH . '/src/Views/financeiro/index_simple.php';
            return;
        }
        
        $this->checkPermission(['master', 'admin', 'financeiro']);

        // Dashboard consolidado
        $dashboard = $this->fluxoCaixaHelper->getDashboard();

        // Fluxo de caixa próximos 30 dias
        $dataInicio = date('Y-m-d');
        $dataFim = date('Y-m-d', strtotime('+30 days'));
        $fluxo30Dias = $this->fluxoCaixaHelper->getFluxoConsolidado($dataInicio, $dataFim);

        // Análise de liquidez
        $liquidez = $this->fluxoCaixaHelper->analisarLiquidez();

        // Contas vencidas e a vencer
        $contasPagarVencidas = $this->contaPagarModel->getVencidas();
        $contasPagarVencer7 = $this->contaPagarModel->getAVencer(7);
        $contasReceberVencidas = $this->contaReceberModel->getVencidas();
        $contasReceberVencer7 = $this->contaReceberModel->getAVencer(7);

        // Boletos pendentes
        $boletosPendentes = $this->boletoModel->getPendentes();

        $data = [
            'titulo' => 'Financeiro - Dashboard',
            'dashboard' => $dashboard,
            'fluxo30Dias' => $fluxo30Dias,
            'liquidez' => $liquidez,
            'contasPagarVencidas' => $contasPagarVencidas,
            'contasPagarVencer7' => $contasPagarVencer7,
            'contasReceberVencidas' => $contasReceberVencidas,
            'contasReceberVencer7' => $contasReceberVencer7,
            'boletosPendentes' => $boletosPendentes
        ];

        $this->render('financeiro/index', $data);
    }

    // ============================================================================
    // CATEGORIAS FINANCEIRAS
    // ============================================================================

    /**
     * Lista categorias financeiras em estrutura hierárquica
     */
    public function categorias()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $tipo = $_GET['tipo'] ?? '';
        $natureza = $_GET['natureza'] ?? '';
        $ativo = $_GET['ativo'] ?? '1';

        $filtros = [];
        if ($tipo) $filtros['tipo'] = $tipo;
        if ($natureza) $filtros['natureza'] = $natureza;
        if ($ativo !== '') $filtros['ativo'] = $ativo;

        // Buscar árvore completa de categorias
        $categorias = $this->categoriaModel->getTree($filtros);

        // Estatísticas
        $stats = [
            'total' => $this->categoriaModel->countTotal(),
            'receitas' => $this->categoriaModel->countPorTipo('receita'),
            'despesas' => $this->categoriaModel->countPorTipo('despesa'),
            'transferencias' => $this->categoriaModel->countPorTipo('transferencia')
        ];

        $data = [
            'titulo' => 'Categorias Financeiras',
            'categorias' => $categorias,
            'stats' => $stats,
            'filtros' => $filtros
        ];

        $this->render('financeiro/categorias/index', $data);
    }

    /**
     * Formulário de criação de categoria
     */
    public function categoriaCreate()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $paiId = $_GET['pai_id'] ?? null;
        $categoriaPai = null;

        if ($paiId) {
            $categoriaPai = $this->categoriaModel->findById($paiId);
        }

        // Buscar categorias disponíveis para ser pai
        $categoriasDisponiveis = $this->categoriaModel->all(['ativo' => 1]);

        // Centros de custo
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        $data = [
            'titulo' => 'Nova Categoria Financeira',
            'categoriaPai' => $categoriaPai,
            'categoriasDisponiveis' => $categoriasDisponiveis,
            'centrosCusto' => $centrosCusto
        ];

        $this->render('financeiro/categorias/create', $data);
    }

    /**
     * Processa criação de categoria
     */
    public function categoriaStore()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/categorias');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/categorias/create');
            return;
        }

        $erros = [];

        if (empty($_POST['codigo'])) {
            $erros[] = 'Código é obrigatório.';
        } elseif ($this->categoriaModel->codigoExists($_POST['codigo'])) {
            $erros[] = 'Código já está em uso.';
        }

        if (empty($_POST['nome'])) {
            $erros[] = 'Nome é obrigatório.';
        }

        if (empty($_POST['tipo'])) {
            $erros[] = 'Tipo é obrigatório.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/categorias/create');
            return;
        }

        $data = [
            'codigo' => trim($_POST['codigo']),
            'nome' => trim($_POST['nome']),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'pai_id' => $_POST['pai_id'] ?? null,
            'tipo' => $_POST['tipo'],
            'natureza' => $_POST['natureza'] ?? 'operacional',
            'centro_custo_id' => $_POST['centro_custo_id'] ?? null,
            'aceita_lancamento' => isset($_POST['aceita_lancamento']) ? 1 : 0,
            'criado_por' => $_SESSION['usuario_id'] ?? null
        ];

        try {
            $id = $this->categoriaModel->create($data);
            $_SESSION['sucesso'] = 'Categoria criada com sucesso!';
            $this->redirect('financeiro/categorias');
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar categoria: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/categorias/create');
        }
    }

    /**
     * Formulário de edição de categoria
     */
    public function categoriaEdit($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $categoria = $this->categoriaModel->findById($id);

        if (!$categoria) {
            $_SESSION['erro'] = 'Categoria não encontrada.';
            $this->redirect('financeiro/categorias');
            return;
        }

        // Categorias disponíveis (exceto ela mesma e suas filhas)
        $categoriasDisponiveis = $this->categoriaModel->getDisponiveisParaPai($id);
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        // Estatísticas de uso
        $stats = $this->categoriaModel->getEstatisticas($id);

        $data = [
            'titulo' => 'Editar Categoria',
            'categoria' => $categoria,
            'categoriasDisponiveis' => $categoriasDisponiveis,
            'centrosCusto' => $centrosCusto,
            'stats' => $stats
        ];

        $this->render('financeiro/categorias/edit', $data);
    }

    /**
     * Processa atualização de categoria
     */
    public function categoriaUpdate($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/categorias');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/categorias/edit/' . $id);
            return;
        }

        $categoria = $this->categoriaModel->findById($id);

        if (!$categoria) {
            $_SESSION['erro'] = 'Categoria não encontrada.';
            $this->redirect('financeiro/categorias');
            return;
        }

        $erros = [];

        if (empty($_POST['codigo'])) {
            $erros[] = 'Código é obrigatório.';
        } elseif ($this->categoriaModel->codigoExists($_POST['codigo'], $id)) {
            $erros[] = 'Código já está em uso.';
        }

        if (empty($_POST['nome'])) {
            $erros[] = 'Nome é obrigatório.';
        }

        if (!empty($_POST['pai_id']) && $this->categoriaModel->criaCiclo($_POST['pai_id'], $id)) {
            $erros[] = 'Operação criaria ciclo na hierarquia.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/categorias/edit/' . $id);
            return;
        }

        $data = [
            'codigo' => trim($_POST['codigo']),
            'nome' => trim($_POST['nome']),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'pai_id' => $_POST['pai_id'] ?? null,
            'tipo' => $_POST['tipo'],
            'natureza' => $_POST['natureza'] ?? 'operacional',
            'centro_custo_id' => $_POST['centro_custo_id'] ?? null,
            'aceita_lancamento' => isset($_POST['aceita_lancamento']) ? 1 : 0,
            'atualizado_por' => $_SESSION['usuario_id'] ?? null
        ];

        try {
            $this->categoriaModel->update($id, $data);
            $_SESSION['sucesso'] = 'Categoria atualizada com sucesso!';
            $this->redirect('financeiro/categorias');
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar categoria: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/categorias/edit/' . $id);
        }
    }

    /**
     * Inativa/Ativa categoria
     */
    public function categoriaToggleAtivo($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/categorias');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/categorias');
            return;
        }

        try {
            $categoria = $this->categoriaModel->findById($id);
            $novoStatus = !$categoria['ativo'];

            $this->categoriaModel->update($id, ['ativo' => $novoStatus]);

            $_SESSION['sucesso'] = 'Status alterado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao alterar status: ' . $e->getMessage();
        }

        $this->redirect('financeiro/categorias');
    }

    // ============================================================================
    // CONTAS A PAGAR
    // ============================================================================

    /**
     * Lista contas a pagar
     */
    public function contasPagar()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $page = $_GET['p'] ?? 1;
        $limit = 25;

        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['categoria_id'])) $filtros['categoria_id'] = $_GET['categoria_id'];
        if (!empty($_GET['fornecedor_id'])) $filtros['fornecedor_id'] = $_GET['fornecedor_id'];
        if (!empty($_GET['centro_custo_id'])) $filtros['centro_custo_id'] = $_GET['centro_custo_id'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
        if (!empty($_GET['busca'])) $filtros['busca'] = $_GET['busca'];

        $contas = $this->contaPagarModel->all($filtros, $page, $limit);
        $total = $this->contaPagarModel->count($filtros);
        $totalPages = ceil($total / $limit);

        // Dados para filtros
        $categorias = $this->categoriaModel->all(['tipo' => 'despesa', 'ativo' => 1]);
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        // Estatísticas
        $stats = [
            'total_pendente' => $this->contaPagarModel->getTotalPendente(),
            'total_vencido' => $this->contaPagarModel->getTotalVencido(),
            'total_pago_mes' => $this->contaPagarModel->getTotalPagoMes(),
            'vencendo_7_dias' => count($this->contaPagarModel->getAVencer(7))
        ];

        $data = [
            'titulo' => 'Contas a Pagar',
            'contas' => $contas,
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto,
            'stats' => $stats,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('financeiro/contas_pagar/index', $data);
    }

    /**
     * Formulário de criação de conta a pagar
     */
    public function contaPagarCreate()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $categorias = $this->categoriaModel->all(['tipo' => 'despesa', 'ativo' => 1, 'aceita_lancamento' => 1]);
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        $data = [
            'titulo' => 'Nova Conta a Pagar',
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto
        ];

        $this->render('financeiro/contas_pagar/create', $data);
    }

    /**
     * Processa criação de conta a pagar
     */
    public function contaPagarStore()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/contas-pagar');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/contas-pagar/create');
            return;
        }

        $erros = $this->validateContaPagarForm($_POST);

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/contas-pagar/create');
            return;
        }

        $data = $this->prepareContaPagarData($_POST);
        $data['criado_por'] = $_SESSION['usuario_id'] ?? null;

        try {
            // Verificar se é parcelada
            if (!empty($_POST['numero_parcelas']) && $_POST['numero_parcelas'] > 1) {
                $ids = $this->contaPagarModel->createParcelada(
                    $data,
                    (int)$_POST['numero_parcelas'],
                    (float)($_POST['taxa_juros'] ?? 0)
                );
                $_SESSION['sucesso'] = 'Conta a pagar parcelada criada com sucesso! (' . count($ids) . ' parcelas)';
                $this->redirect('financeiro/contas-pagar');
            } else {
                $id = $this->contaPagarModel->create($data);
                $_SESSION['sucesso'] = 'Conta a pagar criada com sucesso!';
                $this->redirect('financeiro/contas-pagar/show/' . $id);
            }
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar conta a pagar: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/contas-pagar/create');
        }
    }

    /**
     * Exibe detalhes de conta a pagar
     */
    public function contaPagarShow($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $conta = $this->contaPagarModel->findById($id);

        if (!$conta) {
            $_SESSION['erro'] = 'Conta a pagar não encontrada.';
            $this->redirect('financeiro/contas-pagar');
            return;
        }

        // Histórico de pagamentos
        $pagamentos = $this->pagamentoModel->getPorOrigem('conta_pagar', $id);

        // Anexos
        $anexos = $this->contaPagarModel->getAnexos($id);

        $data = [
            'titulo' => 'Conta a Pagar #' . $conta['numero_documento'],
            'conta' => $conta,
            'pagamentos' => $pagamentos,
            'anexos' => $anexos
        ];

        $this->render('financeiro/contas_pagar/show', $data);
    }

    /**
     * Processa pagamento de conta
     */
    public function contaPagarPagar($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/contas-pagar/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/contas-pagar/show/' . $id);
            return;
        }

        $conta = $this->contaPagarModel->findById($id);

        if (!$conta) {
            $_SESSION['erro'] = 'Conta não encontrada.';
            $this->redirect('financeiro/contas-pagar');
            return;
        }

        $erros = [];

        if (empty($_POST['data_pagamento'])) {
            $erros[] = 'Data de pagamento é obrigatória.';
        }

        if (empty($_POST['valor_pago'])) {
            $erros[] = 'Valor pago é obrigatório.';
        }

        if (empty($_POST['forma_pagamento'])) {
            $erros[] = 'Forma de pagamento é obrigatória.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $this->redirect('financeiro/contas-pagar/show/' . $id);
            return;
        }

        try {
            $this->contaPagarModel->pagar($id, [
                'data_pagamento' => $_POST['data_pagamento'],
                'valor_pago' => (float)$_POST['valor_pago'],
                'forma_pagamento' => $_POST['forma_pagamento'],
                'desconto' => (float)($_POST['desconto'] ?? 0),
                'juros' => (float)($_POST['juros'] ?? 0),
                'multa' => (float)($_POST['multa'] ?? 0),
                'observacoes_pagamento' => $_POST['observacoes_pagamento'] ?? null,
                'pago_por' => $_SESSION['usuario_id'] ?? null
            ]);

            $_SESSION['sucesso'] = 'Pagamento registrado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao registrar pagamento: ' . $e->getMessage();
        }

        $this->redirect('financeiro/contas-pagar/show/' . $id);
    }

    /**
     * Cancela conta a pagar
     */
    public function contaPagarCancelar($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/contas-pagar/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/contas-pagar/show/' . $id);
            return;
        }

        try {
            $this->contaPagarModel->cancelar($id, $_POST['motivo_cancelamento'] ?? 'Cancelamento manual');
            $_SESSION['sucesso'] = 'Conta a pagar cancelada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cancelar conta: ' . $e->getMessage();
        }

        $this->redirect('financeiro/contas-pagar');
    }

    // ============================================================================
    // CONTAS A RECEBER
    // ============================================================================

    /**
     * Lista contas a receber
     */
    public function contasReceber()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $page = $_GET['p'] ?? 1;
        $limit = 25;

        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['categoria_id'])) $filtros['categoria_id'] = $_GET['categoria_id'];
        if (!empty($_GET['cliente_id'])) $filtros['cliente_id'] = $_GET['cliente_id'];
        if (!empty($_GET['centro_custo_id'])) $filtros['centro_custo_id'] = $_GET['centro_custo_id'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
        if (!empty($_GET['busca'])) $filtros['busca'] = $_GET['busca'];

        $contas = $this->contaReceberModel->all($filtros, $page, $limit);
        $total = $this->contaReceberModel->count($filtros);
        $totalPages = ceil($total / $limit);

        // Dados para filtros
        $categorias = $this->categoriaModel->all(['tipo' => 'receita', 'ativo' => 1]);
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        // Estatísticas
        $stats = [
            'total_pendente' => $this->contaReceberModel->getTotalPendente(),
            'total_vencido' => $this->contaReceberModel->getTotalVencido(),
            'total_recebido_mes' => $this->contaReceberModel->getTotalRecebidoMes(),
            'vencendo_7_dias' => count($this->contaReceberModel->getAVencer(7))
        ];

        $data = [
            'titulo' => 'Contas a Receber',
            'contas' => $contas,
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto,
            'stats' => $stats,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('financeiro/contas_receber/index', $data);
    }

    /**
     * Formulário de criação de conta a receber
     */
    public function contaReceberCreate()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $categorias = $this->categoriaModel->all(['tipo' => 'receita', 'ativo' => 1, 'aceita_lancamento' => 1]);
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        $data = [
            'titulo' => 'Nova Conta a Receber',
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto
        ];

        $this->render('financeiro/contas_receber/create', $data);
    }

    /**
     * Processa criação de conta a receber
     */
    public function contaReceberStore()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/contas-receber');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/contas-receber/create');
            return;
        }

        $erros = $this->validateContaReceberForm($_POST);

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/contas-receber/create');
            return;
        }

        $data = $this->prepareContaReceberData($_POST);
        $data['criado_por'] = $_SESSION['usuario_id'] ?? null;

        try {
            // Verificar se é parcelada
            if (!empty($_POST['numero_parcelas']) && $_POST['numero_parcelas'] > 1) {
                $ids = $this->contaReceberModel->createParcelada(
                    $data,
                    (int)$_POST['numero_parcelas'],
                    (float)($_POST['taxa_juros'] ?? 0),
                    isset($_POST['gerar_boleto']) ? 1 : 0
                );
                $_SESSION['sucesso'] = 'Conta a receber parcelada criada com sucesso! (' . count($ids) . ' parcelas)';
                $this->redirect('financeiro/contas-receber');
            } else {
                $id = $this->contaReceberModel->create($data);

                // Gerar boleto se solicitado
                if (isset($_POST['gerar_boleto'])) {
                    $this->contaReceberModel->gerarBoleto($id, [
                        'banco_codigo' => $_POST['banco_codigo'] ?? '001',
                        'instrucoes' => $_POST['instrucoes_boleto'] ?? null
                    ]);
                }

                $_SESSION['sucesso'] = 'Conta a receber criada com sucesso!';
                $this->redirect('financeiro/contas-receber/show/' . $id);
            }
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar conta a receber: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/contas-receber/create');
        }
    }

    /**
     * Exibe detalhes de conta a receber
     */
    public function contaReceberShow($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $conta = $this->contaReceberModel->findById($id);

        if (!$conta) {
            $_SESSION['erro'] = 'Conta a receber não encontrada.';
            $this->redirect('financeiro/contas-receber');
            return;
        }

        // Boleto associado
        $boleto = null;
        if ($conta['boleto_id']) {
            $boleto = $this->boletoModel->findById($conta['boleto_id']);
        }

        // Histórico de recebimentos
        $pagamentos = $this->pagamentoModel->getPorOrigem('conta_receber', $id);

        // Anexos
        $anexos = $this->contaReceberModel->getAnexos($id);

        $data = [
            'titulo' => 'Conta a Receber #' . $conta['numero_documento'],
            'conta' => $conta,
            'boleto' => $boleto,
            'pagamentos' => $pagamentos,
            'anexos' => $anexos
        ];

        $this->render('financeiro/contas_receber/show', $data);
    }

    /**
     * Processa recebimento de conta
     */
    public function contaReceberReceber($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/contas-receber/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/contas-receber/show/' . $id);
            return;
        }

        $conta = $this->contaReceberModel->findById($id);

        if (!$conta) {
            $_SESSION['erro'] = 'Conta não encontrada.';
            $this->redirect('financeiro/contas-receber');
            return;
        }

        $erros = [];

        if (empty($_POST['data_recebimento'])) {
            $erros[] = 'Data de recebimento é obrigatória.';
        }

        if (empty($_POST['valor_recebido'])) {
            $erros[] = 'Valor recebido é obrigatório.';
        }

        if (empty($_POST['forma_recebimento'])) {
            $erros[] = 'Forma de recebimento é obrigatória.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $this->redirect('financeiro/contas-receber/show/' . $id);
            return;
        }

        try {
            $this->contaReceberModel->receber($id, [
                'data_recebimento' => $_POST['data_recebimento'],
                'valor_recebido' => (float)$_POST['valor_recebido'],
                'forma_recebimento' => $_POST['forma_recebimento'],
                'desconto' => (float)($_POST['desconto'] ?? 0),
                'juros' => (float)($_POST['juros'] ?? 0),
                'multa' => (float)($_POST['multa'] ?? 0),
                'observacoes_recebimento' => $_POST['observacoes_recebimento'] ?? null,
                'recebido_por' => $_SESSION['usuario_id'] ?? null
            ]);

            $_SESSION['sucesso'] = 'Recebimento registrado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao registrar recebimento: ' . $e->getMessage();
        }

        $this->redirect('financeiro/contas-receber/show/' . $id);
    }

    /**
     * Cancela conta a receber
     */
    public function contaReceberCancelar($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/contas-receber/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/contas-receber/show/' . $id);
            return;
        }

        try {
            $this->contaReceberModel->cancelar($id, $_POST['motivo_cancelamento'] ?? 'Cancelamento manual');
            $_SESSION['sucesso'] = 'Conta a receber cancelada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cancelar conta: ' . $e->getMessage();
        }

        $this->redirect('financeiro/contas-receber');
    }

    // ============================================================================
    // BOLETOS
    // ============================================================================

    /**
     * Lista boletos
     */
    public function boletos()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $page = $_GET['p'] ?? 1;
        $limit = 25;

        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['banco_codigo'])) $filtros['banco_codigo'] = $_GET['banco_codigo'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];

        $boletos = $this->boletoModel->all($filtros, $page, $limit);
        $total = $this->boletoModel->count($filtros);
        $totalPages = ceil($total / $limit);

        // Estatísticas
        $stats = [
            'total_pendente' => $this->boletoModel->getTotalPendente(),
            'total_vencido' => $this->boletoModel->getTotalVencido(),
            'total_pago_mes' => $this->boletoModel->getTotalPagoMes(),
            'vencendo_7_dias' => count($this->boletoModel->getVencendo(7))
        ];

        $data = [
            'titulo' => 'Boletos Bancários',
            'boletos' => $boletos,
            'stats' => $stats,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('financeiro/boletos/index', $data);
    }

    /**
     * Exibe boleto para impressão
     */
    public function boletoShow($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'usuario']);

        $boleto = $this->boletoModel->findById($id);

        if (!$boleto) {
            $_SESSION['erro'] = 'Boleto não encontrado.';
            $this->redirect('financeiro/boletos');
            return;
        }

        // Conta a receber associada
        $conta = null;
        if ($boleto['conta_receber_id']) {
            $conta = $this->contaReceberModel->findById($boleto['conta_receber_id']);
        }

        $data = [
            'titulo' => 'Boleto',
            'boleto' => $boleto,
            'conta' => $conta
        ];

        // Renderizar sem layout (impressão)
        $this->render('financeiro/boletos/show', $data, false);
    }

    /**
     * Processa pagamento de boleto
     */
    public function boletoPagar($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/boletos');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/boletos');
            return;
        }

        try {
            $this->boletoModel->pagar($id, [
                'data_pagamento' => $_POST['data_pagamento'],
                'valor_pago' => (float)$_POST['valor_pago'],
                'tarifa_bancaria' => (float)($_POST['tarifa_bancaria'] ?? 0)
            ]);

            $_SESSION['sucesso'] = 'Pagamento do boleto registrado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao registrar pagamento: ' . $e->getMessage();
        }

        $this->redirect('financeiro/boletos');
    }

    /**
     * Cancela boleto
     */
    public function boletoCancelar($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/boletos');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/boletos');
            return;
        }

        try {
            $this->boletoModel->cancelar($id, $_POST['motivo_cancelamento'] ?? 'Cancelamento manual');
            $_SESSION['sucesso'] = 'Boleto cancelado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cancelar boleto: ' . $e->getMessage();
        }

        $this->redirect('financeiro/boletos');
    }

    // ============================================================================
    // LANÇAMENTOS FINANCEIROS (Partidas Dobradas)
    // ============================================================================

    /**
     * Lista lançamentos financeiros
     */
    public function lancamentos()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $page = $_GET['p'] ?? 1;
        $limit = 50;

        $filtros = [];
        if (!empty($_GET['tipo'])) $filtros['tipo'] = $_GET['tipo'];
        if (!empty($_GET['natureza'])) $filtros['natureza'] = $_GET['natureza'];
        if (!empty($_GET['categoria_id'])) $filtros['categoria_id'] = $_GET['categoria_id'];
        if (!empty($_GET['centro_custo_id'])) $filtros['centro_custo_id'] = $_GET['centro_custo_id'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];

        $lancamentos = $this->lancamentoModel->all($filtros, $page, $limit);
        $total = $this->lancamentoModel->count($filtros);
        $totalPages = ceil($total / $limit);

        // Dados para filtros
        $categorias = $this->categoriaModel->all(['ativo' => 1]);
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        $data = [
            'titulo' => 'Lançamentos Financeiros',
            'lancamentos' => $lancamentos,
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('financeiro/lancamentos/index', $data);
    }

    /**
     * Formulário de lançamento manual
     */
    public function lancamentoCreate()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $categorias = $this->categoriaModel->all(['ativo' => 1, 'aceita_lancamento' => 1]);
        $centrosCusto = $this->centroCustoModel->all(['ativo' => 1]);

        $data = [
            'titulo' => 'Novo Lançamento Financeiro',
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto
        ];

        $this->render('financeiro/lancamentos/create', $data);
    }

    /**
     * Processa criação de lançamento (partidas dobradas)
     */
    public function lancamentoStore()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/lancamentos');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/lancamentos/create');
            return;
        }

        $erros = [];

        if (empty($_POST['tipo'])) {
            $erros[] = 'Tipo de lançamento é obrigatório.';
        }

        if (empty($_POST['data_lancamento'])) {
            $erros[] = 'Data do lançamento é obrigatória.';
        }

        if (empty($_POST['valor'])) {
            $erros[] = 'Valor é obrigatório.';
        }

        if (empty($_POST['categoria_debito_id']) && empty($_POST['categoria_credito_id'])) {
            $erros[] = 'Pelo menos uma categoria (débito ou crédito) deve ser informada.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/lancamentos/create');
            return;
        }

        $data = [
            'tipo' => $_POST['tipo'],
            'data_lancamento' => $_POST['data_lancamento'],
            'valor' => (float)$_POST['valor'],
            'descricao' => $_POST['descricao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'categoria_debito_id' => $_POST['categoria_debito_id'] ?? null,
            'categoria_credito_id' => $_POST['categoria_credito_id'] ?? null,
            'centro_custo_id' => $_POST['centro_custo_id'] ?? null,
            'projeto_id' => $_POST['projeto_id'] ?? null,
            'criado_por' => $_SESSION['usuario_id'] ?? null
        ];

        try {
            // Criar lançamento em partidas dobradas
            $result = $this->lancamentoModel->createPartidasDobradas($data);

            $_SESSION['sucesso'] = 'Lançamento criado com sucesso!';
            $this->redirect('financeiro/lancamentos');
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar lançamento: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/lancamentos/create');
        }
    }

    /**
     * Estorna lançamento
     */
    public function lancamentoEstornar($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/lancamentos');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/lancamentos');
            return;
        }

        try {
            $this->lancamentoModel->estornar($id, $_POST['motivo'] ?? 'Estorno manual');
            $_SESSION['sucesso'] = 'Lançamento estornado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao estornar lançamento: ' . $e->getMessage();
        }

        $this->redirect('financeiro/lancamentos');
    }

    // ============================================================================
    // CONCILIAÇÃO BANCÁRIA
    // ============================================================================

    /**
     * Lista conciliações bancárias
     */
    public function conciliacoes()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $page = $_GET['p'] ?? 1;
        $limit = 25;

        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['conta_bancaria_id'])) $filtros['conta_bancaria_id'] = $_GET['conta_bancaria_id'];

        $conciliacoes = $this->conciliacaoModel->all($filtros, $page, $limit);
        $total = $this->conciliacaoModel->count($filtros);
        $totalPages = ceil($total / $limit);

        $data = [
            'titulo' => 'Conciliação Bancária',
            'conciliacoes' => $conciliacoes,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('financeiro/conciliacoes/index', $data);
    }

    /**
     * Formulário de importação OFX
     */
    public function conciliacaoImportar()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $data = [
            'titulo' => 'Importar Extrato OFX'
        ];

        $this->render('financeiro/conciliacoes/importar', $data);
    }

    /**
     * Processa importação de arquivo OFX
     */
    public function conciliacaoProcessarOFX()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/conciliacoes');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/conciliacoes/importar');
            return;
        }

        // Validar upload
        if (!isset($_FILES['arquivo_ofx']) || $_FILES['arquivo_ofx']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erro'] = 'Erro no upload do arquivo.';
            $this->redirect('financeiro/conciliacoes/importar');
            return;
        }

        $arquivo = $_FILES['arquivo_ofx'];

        // Validar extensão
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['ofx', 'qfx'])) {
            $_SESSION['erro'] = 'Formato de arquivo inválido. Use .ofx ou .qfx';
            $this->redirect('financeiro/conciliacoes/importar');
            return;
        }

        try {
            $result = $this->conciliacaoModel->importarOFX(
                $_POST['conta_bancaria_id'],
                $arquivo['tmp_name']
            );

            $_SESSION['sucesso'] = "Importação concluída! {$result['transacoes_importadas']} transações importadas. {$result['transacoes_conciliadas']} automaticamente conciliadas.";
            $this->redirect('financeiro/conciliacoes/show/' . $result['conciliacao_id']);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao importar OFX: ' . $e->getMessage();
            $this->redirect('financeiro/conciliacoes/importar');
        }
    }

    /**
     * Exibe detalhes de conciliação
     */
    public function conciliacaoShow($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $conciliacao = $this->conciliacaoModel->findById($id);

        if (!$conciliacao) {
            $_SESSION['erro'] = 'Conciliação não encontrada.';
            $this->redirect('financeiro/conciliacoes');
            return;
        }

        // Itens da conciliação
        $itens = $this->conciliacaoModel->getItens($id);

        // Lançamentos disponíveis para vincular
        $lancamentosDisponiveis = $this->lancamentoModel->getNaoConciliados();

        $data = [
            'titulo' => 'Conciliação Bancária',
            'conciliacao' => $conciliacao,
            'itens' => $itens,
            'lancamentosDisponiveis' => $lancamentosDisponiveis
        ];

        $this->render('financeiro/conciliacoes/show', $data);
    }

    /**
     * Vincula lançamento manualmente
     */
    public function conciliacaoVincular()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/conciliacoes');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/conciliacoes');
            return;
        }

        try {
            $this->conciliacaoModel->vincularLancamento(
                (int)$_POST['item_id'],
                (int)$_POST['lancamento_id']
            );

            $_SESSION['sucesso'] = 'Lançamento vinculado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao vincular: ' . $e->getMessage();
        }

        $this->redirect('financeiro/conciliacoes/show/' . $_POST['conciliacao_id']);
    }

    // ============================================================================
    // FLUXO DE CAIXA E RELATÓRIOS
    // ============================================================================

    /**
     * Fluxo de caixa
     */
    public function fluxoCaixa()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Primeiro dia do mês
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t', strtotime('+3 months')); // 3 meses à frente

        // Fluxo realizado
        $fluxoRealizado = $this->fluxoCaixaHelper->getFluxoRealizado($dataInicio, date('Y-m-d'));

        // Fluxo projetado
        $fluxoProjetado = $this->fluxoCaixaHelper->getFluxoProjetado(date('Y-m-d'), $dataFim);

        // Fluxo consolidado
        $fluxoConsolidado = $this->fluxoCaixaHelper->getFluxoConsolidado($dataInicio, $dataFim);

        // Análise de liquidez
        $liquidez = $this->fluxoCaixaHelper->analisarLiquidez();

        $data = [
            'titulo' => 'Fluxo de Caixa',
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'fluxoRealizado' => $fluxoRealizado,
            'fluxoProjetado' => $fluxoProjetado,
            'fluxoConsolidado' => $fluxoConsolidado,
            'liquidez' => $liquidez
        ];

        $this->render('financeiro/fluxo_caixa/index', $data);
    }

    /**
     * DRE - Demonstração do Resultado do Exercício
     */
    public function dre()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $dataInicio = $_GET['data_inicio'] ?? date('Y-01-01'); // Primeiro dia do ano
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');

        $dre = $this->fluxoCaixaHelper->getDRE($dataInicio, $dataFim);

        $data = [
            'titulo' => 'DRE - Demonstração do Resultado',
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'dre' => $dre
        ];

        $this->render('financeiro/relatorios/dre', $data);
    }

    /**
     * Balancete
     */
    public function balancete()
    {
        $this->checkPermission(['master', 'admin', 'financeiro']);

        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');

        $balancete = $this->lancamentoModel->getBalancete($dataInicio, $dataFim);

        $data = [
            'titulo' => 'Balancete',
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'balancete' => $balancete
        ];

        $this->render('financeiro/relatorios/balancete', $data);
    }

    // ============================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ============================================================================

    /**
     * Valida formulário de conta a pagar
     */
    private function validateContaPagarForm($data)
    {
        $erros = [];

        if (empty($data['descricao'])) {
            $erros[] = 'Descrição é obrigatória.';
        }

        if (empty($data['valor_original'])) {
            $erros[] = 'Valor é obrigatório.';
        } elseif ($data['valor_original'] <= 0) {
            $erros[] = 'Valor deve ser maior que zero.';
        }

        if (empty($data['data_vencimento'])) {
            $erros[] = 'Data de vencimento é obrigatória.';
        }

        if (empty($data['categoria_id'])) {
            $erros[] = 'Categoria é obrigatória.';
        }

        return $erros;
    }

    /**
     * Prepara dados de conta a pagar
     */
    private function prepareContaPagarData($post)
    {
        return [
            'numero_documento' => $post['numero_documento'] ?? null,
            'descricao' => trim($post['descricao']),
            'fornecedor_id' => $post['fornecedor_id'] ?? null,
            'fornecedor_nome' => $post['fornecedor_nome'] ?? null,
            'categoria_id' => $post['categoria_id'],
            'centro_custo_id' => $post['centro_custo_id'] ?? null,
            'projeto_id' => $post['projeto_id'] ?? null,
            'valor_original' => (float)$post['valor_original'],
            'data_emissao' => $post['data_emissao'] ?? date('Y-m-d'),
            'data_vencimento' => $post['data_vencimento'],
            'observacoes' => $post['observacoes'] ?? null,
            'tags' => $post['tags'] ?? null,
            'recorrente' => isset($post['recorrente']) ? 1 : 0,
            'frequencia_recorrencia' => $post['frequencia_recorrencia'] ?? null
        ];
    }

    /**
     * Valida formulário de conta a receber
     */
    private function validateContaReceberForm($data)
    {
        $erros = [];

        if (empty($data['descricao'])) {
            $erros[] = 'Descrição é obrigatória.';
        }

        if (empty($data['valor_original'])) {
            $erros[] = 'Valor é obrigatório.';
        } elseif ($data['valor_original'] <= 0) {
            $erros[] = 'Valor deve ser maior que zero.';
        }

        if (empty($data['data_vencimento'])) {
            $erros[] = 'Data de vencimento é obrigatória.';
        }

        if (empty($data['categoria_id'])) {
            $erros[] = 'Categoria é obrigatória.';
        }

        return $erros;
    }

    /**
     * Prepara dados de conta a receber
     */
    private function prepareContaReceberData($post)
    {
        return [
            'numero_documento' => $post['numero_documento'] ?? null,
            'descricao' => trim($post['descricao']),
            'cliente_id' => $post['cliente_id'] ?? null,
            'cliente_nome' => $post['cliente_nome'] ?? null,
            'categoria_id' => $post['categoria_id'],
            'centro_custo_id' => $post['centro_custo_id'] ?? null,
            'projeto_id' => $post['projeto_id'] ?? null,
            'contrato_id' => $post['contrato_id'] ?? null,
            'valor_original' => (float)$post['valor_original'],
            'data_emissao' => $post['data_emissao'] ?? date('Y-m-d'),
            'data_vencimento' => $post['data_vencimento'],
            'observacoes' => $post['observacoes'] ?? null,
            'tags' => $post['tags'] ?? null,
            'recorrente' => isset($post['recorrente']) ? 1 : 0,
            'frequencia_recorrencia' => $post['frequencia_recorrencia'] ?? null
        ];
    }
}
