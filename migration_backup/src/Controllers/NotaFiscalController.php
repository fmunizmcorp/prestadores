<?php

namespace App\Controllers;

use App\Models\NotaFiscal;
use App\Models\EmpresaTomadora;
use App\Models\EmpresaPrestadora;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\ContaPagar;

/**
 * NotaFiscalController
 * 
 * Gerencia emissão, cancelamento e consulta de notas fiscais eletrônicas:
 * - NF-e (Nota Fiscal Eletrônica de produtos)
 * - NFS-e (Nota Fiscal de Serviços Eletrônica)
 * - NFC-e (Nota Fiscal de Consumidor Eletrônica)
 * 
 * Funcionalidades:
 * - Emissão de notas fiscais com cálculo automático de impostos
 * - Geração de XML para SEFAZ
 * - Geração de DANFE (PDF)
 * - Cancelamento dentro de 24h
 * - Carta de correção
 * - Consulta de status na SEFAZ
 * - Integração com contas a pagar/receber
 * - Histórico completo de operações
 * 
 * @package App\Controllers
 * @author Clinfec Prestadores
 * @since Sprint 7
 */
class NotaFiscalController extends BaseController
{
    private $notaFiscalModel;
    private $empresaTomadoraModel;
    private $empresaPrestadoraModel;
    private $fornecedorModel;
    private $clienteModel;
    private $contaReceberModel;
    private $contaPagarModel;

    public function __construct()
    {
        parent::__construct();
        
        try {
            $this->notaFiscalModel = new NotaFiscal();
            $this->empresaTomadoraModel = new EmpresaTomadora();
            $this->empresaPrestadoraModel = new EmpresaPrestadora();
            $this->fornecedorModel = new Fornecedor();
            $this->clienteModel = new Cliente();
            $this->contaReceberModel = new ContaReceber();
            $this->contaPagarModel = new ContaPagar();
        } catch (\Throwable $e) {
            error_log("NotaFiscalController construct error: " . $e->getMessage());
            $this->notaFiscalModel = null;
        }
    }

    // ============================================================================
    // LISTAGEM E VISUALIZAÇÃO
    // ============================================================================

    /**
     * Lista notas fiscais
     */
    public function index()
    {
        if ($this->notaFiscalModel === null) {
            require ROOT_PATH . '/src/Views/notas_fiscais/index_simple.php';
            return;
        }
        
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        $page = $_GET['p'] ?? 1;
        $limit = 25;

        $filtros = [];
        if (!empty($_GET['tipo'])) $filtros['tipo'] = $_GET['tipo'];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['natureza_operacao'])) $filtros['natureza_operacao'] = $_GET['natureza_operacao'];
        if (!empty($_GET['emitente_id'])) $filtros['emitente_id'] = $_GET['emitente_id'];
        if (!empty($_GET['destinatario_id'])) $filtros['destinatario_id'] = $_GET['destinatario_id'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
        if (!empty($_GET['numero'])) $filtros['numero'] = $_GET['numero'];
        if (!empty($_GET['chave_acesso'])) $filtros['chave_acesso'] = $_GET['chave_acesso'];

        $notas = $this->notaFiscalModel->all($filtros, $page, $limit);
        $total = $this->notaFiscalModel->count($filtros);
        $totalPages = ceil($total / $limit);

        // Estatísticas
        $stats = [
            'total_mes' => $this->notaFiscalModel->countMes(),
            'autorizadas_mes' => $this->notaFiscalModel->countMesPorStatus('autorizada'),
            'valor_total_mes' => $this->notaFiscalModel->getValorTotalMes(),
            'aguardando_autorizacao' => $this->notaFiscalModel->countPorStatus('emitida')
        ];

        $data = [
            'titulo' => 'Notas Fiscais Eletrônicas',
            'notas' => $notas,
            'stats' => $stats,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('financeiro/notas_fiscais/index', $data);
    }

    /**
     * Exibe detalhes de nota fiscal
     */
    public function show($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        // Buscar itens da nota
        $itens = $this->notaFiscalModel->getItens($id);

        // Histórico de eventos
        $historico = $this->notaFiscalModel->getHistorico($id);

        // Emitente e destinatário
        $emitente = $this->buscarEntidade($nota['emitente_tipo'], $nota['emitente_id']);
        $destinatario = $this->buscarEntidade($nota['destinatario_tipo'], $nota['destinatario_id']);

        // Contas vinculadas
        $contasVinculadas = $this->notaFiscalModel->getContasVinculadas($id);

        $data = [
            'titulo' => 'Nota Fiscal #' . $nota['numero'],
            'nota' => $nota,
            'itens' => $itens,
            'historico' => $historico,
            'emitente' => $emitente,
            'destinatario' => $destinatario,
            'contasVinculadas' => $contasVinculadas
        ];

        $this->render('financeiro/notas_fiscais/show', $data);
    }

    // ============================================================================
    // EMISSÃO DE NOTAS FISCAIS
    // ============================================================================

    /**
     * Formulário de emissão de nota fiscal
     */
    public function create()
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        $tipo = $_GET['tipo'] ?? NotaFiscal::TIPO_NFE;
        $natureza = $_GET['natureza'] ?? NotaFiscal::NATUREZA_VENDA;

        // Buscar empresas emitentes
        $empresasEmitentes = $this->empresaPrestadoraModel->getAtivas();

        // Buscar possíveis destinatários
        $empresasTomadasoras = $this->empresaTomadoraModel->getAtivas();
        $fornecedores = $this->fornecedorModel->getAtivos();
        $clientes = $this->clienteModel->getAtivos();

        $data = [
            'titulo' => 'Emitir Nota Fiscal',
            'tipo' => $tipo,
            'natureza' => $natureza,
            'empresasEmitentes' => $empresasEmitentes,
            'empresasTomadoras' => $empresasTomadasoras,
            'fornecedores' => $fornecedores,
            'clientes' => $clientes
        ];

        $this->render('financeiro/notas_fiscais/create', $data);
    }

    /**
     * Processa criação de nota fiscal (rascunho)
     */
    public function store()
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/notas-fiscais/create');
            return;
        }

        $erros = $this->validateNotaFiscalForm($_POST);

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/notas-fiscais/create');
            return;
        }

        $data = $this->prepareNotaFiscalData($_POST);
        $data['status'] = NotaFiscal::STATUS_RASCUNHO;
        $data['criado_por'] = $_SESSION['usuario_id'] ?? null;

        try {
            $id = $this->notaFiscalModel->create($data);

            // Adicionar itens da nota
            if (!empty($_POST['itens'])) {
                foreach ($_POST['itens'] as $item) {
                    $this->notaFiscalModel->addItem($id, $item);
                }
            }

            $_SESSION['sucesso'] = 'Nota fiscal criada em rascunho. Revise os dados antes de emitir.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar nota fiscal: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/notas-fiscais/create');
        }
    }

    /**
     * Formulário de edição de nota (apenas rascunho)
     */
    public function edit($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        // Só pode editar se estiver em rascunho
        if ($nota['status'] !== NotaFiscal::STATUS_RASCUNHO) {
            $_SESSION['erro'] = 'Apenas notas em rascunho podem ser editadas.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        $itens = $this->notaFiscalModel->getItens($id);

        $empresasEmitentes = $this->empresaPrestadoraModel->getAtivas();
        $empresasTomadoras = $this->empresaTomadoraModel->getAtivas();
        $fornecedores = $this->fornecedorModel->getAtivos();
        $clientes = $this->clienteModel->getAtivos();

        $data = [
            'titulo' => 'Editar Nota Fiscal',
            'nota' => $nota,
            'itens' => $itens,
            'empresasEmitentes' => $empresasEmitentes,
            'empresasTomadoras' => $empresasTomadoras,
            'fornecedores' => $fornecedores,
            'clientes' => $clientes
        ];

        $this->render('financeiro/notas_fiscais/edit', $data);
    }

    /**
     * Processa atualização de nota fiscal
     */
    public function update($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/notas-fiscais/edit/' . $id);
            return;
        }

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if ($nota['status'] !== NotaFiscal::STATUS_RASCUNHO) {
            $_SESSION['erro'] = 'Apenas notas em rascunho podem ser editadas.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        $erros = $this->validateNotaFiscalForm($_POST);

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/notas-fiscais/edit/' . $id);
            return;
        }

        $data = $this->prepareNotaFiscalData($_POST);
        $data['atualizado_por'] = $_SESSION['usuario_id'] ?? null;

        try {
            $this->notaFiscalModel->update($id, $data);

            // Atualizar itens
            $this->notaFiscalModel->deleteItens($id);
            if (!empty($_POST['itens'])) {
                foreach ($_POST['itens'] as $item) {
                    $this->notaFiscalModel->addItem($id, $item);
                }
            }

            $_SESSION['sucesso'] = 'Nota fiscal atualizada com sucesso!';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar nota fiscal: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('financeiro/notas-fiscais/edit/' . $id);
        }
    }

    // ============================================================================
    // EMISSÃO E AUTORIZAÇÃO
    // ============================================================================

    /**
     * Emite nota fiscal (gera XML e envia para SEFAZ)
     */
    public function emitir($id)
    {
        $this->checkPermission(['master', 'admin', 'fiscal']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if ($nota['status'] !== NotaFiscal::STATUS_RASCUNHO) {
            $_SESSION['erro'] = 'Apenas notas em rascunho podem ser emitidas.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        try {
            // Emitir nota (gera XML, chave de acesso e envia para SEFAZ)
            $resultado = $this->notaFiscalModel->emitir($id);

            if ($resultado['sucesso']) {
                $_SESSION['sucesso'] = 'Nota fiscal emitida com sucesso! Chave: ' . $resultado['chave_acesso'];

                // Gerar DANFE (PDF)
                $this->notaFiscalModel->gerarDANFE($id);

                // Criar conta a receber/pagar automaticamente se configurado
                if (isset($_POST['gerar_conta']) && $_POST['gerar_conta'] == '1') {
                    $this->gerarConta($id, $nota);
                }
            } else {
                $_SESSION['erro'] = 'Erro ao emitir nota: ' . $resultado['mensagem'];
            }
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao emitir nota fiscal: ' . $e->getMessage();
        }

        $this->redirect('financeiro/notas-fiscais/show/' . $id);
    }

    /**
     * Consulta status da nota na SEFAZ
     */
    public function consultarStatus($id)
    {
        $this->checkPermission(['master', 'admin', 'fiscal']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        try {
            $resultado = $this->notaFiscalModel->consultarStatus($id);

            if ($resultado['sucesso']) {
                $_SESSION['sucesso'] = 'Status consultado: ' . $resultado['status'];
            } else {
                $_SESSION['erro'] = 'Erro ao consultar status: ' . $resultado['mensagem'];
            }
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao consultar status: ' . $e->getMessage();
        }

        $this->redirect('financeiro/notas-fiscais/show/' . $id);
    }

    // ============================================================================
    // CANCELAMENTO E CARTA DE CORREÇÃO
    // ============================================================================

    /**
     * Formulário de cancelamento de nota
     */
    public function formCancelar($id)
    {
        $this->checkPermission(['master', 'admin', 'fiscal']);

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        // Verifica se pode cancelar (dentro de 24h)
        $podeCancelar = $this->notaFiscalModel->podeCancelar($id);

        $data = [
            'titulo' => 'Cancelar Nota Fiscal',
            'nota' => $nota,
            'podeCancelar' => $podeCancelar
        ];

        $this->render('financeiro/notas_fiscais/cancelar', $data);
    }

    /**
     * Processa cancelamento de nota fiscal
     */
    public function cancelar($id)
    {
        $this->checkPermission(['master', 'admin', 'fiscal']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if (!$this->notaFiscalModel->podeCancelar($id)) {
            $_SESSION['erro'] = 'Nota não pode ser cancelada. Prazo de 24h expirado ou status inválido.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        $erros = [];

        if (empty($_POST['justificativa'])) {
            $erros[] = 'Justificativa de cancelamento é obrigatória.';
        } elseif (strlen($_POST['justificativa']) < 15) {
            $erros[] = 'Justificativa deve ter no mínimo 15 caracteres.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $this->redirect('financeiro/notas-fiscais/cancelar/' . $id);
            return;
        }

        try {
            $resultado = $this->notaFiscalModel->cancelar($id, $_POST['justificativa']);

            if ($resultado['sucesso']) {
                $_SESSION['sucesso'] = 'Nota fiscal cancelada com sucesso!';

                // Cancelar contas vinculadas se solicitado
                if (isset($_POST['cancelar_contas']) && $_POST['cancelar_contas'] == '1') {
                    $this->cancelarContasVinculadas($id);
                }
            } else {
                $_SESSION['erro'] = 'Erro ao cancelar nota: ' . $resultado['mensagem'];
            }
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cancelar nota fiscal: ' . $e->getMessage();
        }

        $this->redirect('financeiro/notas-fiscais/show/' . $id);
    }

    /**
     * Formulário de carta de correção
     */
    public function formCartaCorrecao($id)
    {
        $this->checkPermission(['master', 'admin', 'fiscal']);

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        // Buscar cartas de correção anteriores
        $cartasCorrecao = $this->notaFiscalModel->getCartasCorrecao($id);

        $data = [
            'titulo' => 'Carta de Correção',
            'nota' => $nota,
            'cartasCorrecao' => $cartasCorrecao
        ];

        $this->render('financeiro/notas_fiscais/carta_correcao', $data);
    }

    /**
     * Processa envio de carta de correção
     */
    public function cartaCorrecao($id)
    {
        $this->checkPermission(['master', 'admin', 'fiscal']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if ($nota['status'] !== NotaFiscal::STATUS_AUTORIZADA) {
            $_SESSION['erro'] = 'Carta de correção só pode ser emitida para notas autorizadas.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        $erros = [];

        if (empty($_POST['correcao'])) {
            $erros[] = 'Texto da correção é obrigatório.';
        } elseif (strlen($_POST['correcao']) < 15) {
            $erros[] = 'Texto da correção deve ter no mínimo 15 caracteres.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $this->redirect('financeiro/notas-fiscais/carta-correcao/' . $id);
            return;
        }

        try {
            $resultado = $this->notaFiscalModel->emitirCartaCorrecao($id, $_POST['correcao']);

            if ($resultado['sucesso']) {
                $_SESSION['sucesso'] = 'Carta de correção registrada com sucesso!';
            } else {
                $_SESSION['erro'] = 'Erro ao registrar carta de correção: ' . $resultado['mensagem'];
            }
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao emitir carta de correção: ' . $e->getMessage();
        }

        $this->redirect('financeiro/notas-fiscais/show/' . $id);
    }

    // ============================================================================
    // DOWNLOAD DE DOCUMENTOS
    // ============================================================================

    /**
     * Download do XML da nota
     */
    public function downloadXML($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if (empty($nota['xml_nfe'])) {
            $_SESSION['erro'] = 'XML não disponível para esta nota.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        try {
            $this->notaFiscalModel->downloadXML($id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao fazer download do XML: ' . $e->getMessage();
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
        }
    }

    /**
     * Download do DANFE (PDF)
     */
    public function downloadDANFE($id)
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if (empty($nota['pdf_danfe'])) {
            // Tentar gerar DANFE se não existir
            try {
                $this->notaFiscalModel->gerarDANFE($id);
                $nota = $this->notaFiscalModel->findById($id); // Recarregar
            } catch (\Exception $e) {
                $_SESSION['erro'] = 'Erro ao gerar DANFE: ' . $e->getMessage();
                $this->redirect('financeiro/notas-fiscais/show/' . $id);
                return;
            }
        }

        try {
            $this->notaFiscalModel->downloadDANFE($id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao fazer download do DANFE: ' . $e->getMessage();
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
        }
    }

    // ============================================================================
    // RELATÓRIOS
    // ============================================================================

    /**
     * Relatório de notas fiscais emitidas
     */
    public function relatorio()
    {
        $this->checkPermission(['master', 'admin', 'financeiro', 'fiscal']);

        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        $tipo = $_GET['tipo'] ?? '';
        $status = $_GET['status'] ?? '';

        $filtros = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ];

        if ($tipo) $filtros['tipo'] = $tipo;
        if ($status) $filtros['status'] = $status;

        $notas = $this->notaFiscalModel->all($filtros);

        // Totalizadores
        $totalizadores = [
            'quantidade' => count($notas),
            'valor_total' => array_sum(array_column($notas, 'valor_total')),
            'valor_impostos' => array_sum(array_column($notas, 'valor_icms')) +
                               array_sum(array_column($notas, 'valor_pis')) +
                               array_sum(array_column($notas, 'valor_cofins')) +
                               array_sum(array_column($notas, 'valor_iss')),
            'valor_liquido' => array_sum(array_column($notas, 'valor_liquido'))
        ];

        // Totalizadores por tipo
        $totalizadoresPorTipo = $this->notaFiscalModel->getTotalizadoresPorTipo($dataInicio, $dataFim);

        $data = [
            'titulo' => 'Relatório de Notas Fiscais',
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'notas' => $notas,
            'totalizadores' => $totalizadores,
            'totalizadoresPorTipo' => $totalizadoresPorTipo,
            'filtros' => $filtros
        ];

        $this->render('financeiro/notas_fiscais/relatorio', $data);
    }

    // ============================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ============================================================================

    /**
     * Valida formulário de nota fiscal
     */
    private function validateNotaFiscalForm($data)
    {
        $erros = [];

        if (empty($data['tipo'])) {
            $erros[] = 'Tipo de nota é obrigatório.';
        }

        if (empty($data['natureza_operacao'])) {
            $erros[] = 'Natureza da operação é obrigatória.';
        }

        if (empty($data['emitente_id'])) {
            $erros[] = 'Emitente é obrigatório.';
        }

        if (empty($data['destinatario_id'])) {
            $erros[] = 'Destinatário é obrigatório.';
        }

        if (empty($data['data_emissao'])) {
            $erros[] = 'Data de emissão é obrigatória.';
        }

        // Validar valores
        $valorProdutos = (float)($data['valor_produtos'] ?? 0);
        $valorServicos = (float)($data['valor_servicos'] ?? 0);

        if ($valorProdutos <= 0 && $valorServicos <= 0) {
            $erros[] = 'Valor de produtos ou serviços deve ser maior que zero.';
        }

        // Validar itens
        if (empty($data['itens']) || !is_array($data['itens'])) {
            $erros[] = 'Nota deve conter ao menos um item.';
        }

        return $erros;
    }

    /**
     * Prepara dados de nota fiscal
     */
    private function prepareNotaFiscalData($post)
    {
        return [
            'numero' => $post['numero'] ?? null,
            'serie' => $post['serie'] ?? '1',
            'tipo' => $post['tipo'],
            'natureza_operacao' => $post['natureza_operacao'],
            'data_emissao' => $post['data_emissao'],
            'data_saida_entrada' => $post['data_saida_entrada'] ?? $post['data_emissao'],
            'emitente_id' => $post['emitente_id'],
            'emitente_tipo' => $post['emitente_tipo'],
            'destinatario_id' => $post['destinatario_id'],
            'destinatario_tipo' => $post['destinatario_tipo'],
            'valor_produtos' => (float)($post['valor_produtos'] ?? 0),
            'valor_servicos' => (float)($post['valor_servicos'] ?? 0),
            'valor_desconto' => (float)($post['valor_desconto'] ?? 0),
            'valor_frete' => (float)($post['valor_frete'] ?? 0),
            'valor_seguro' => (float)($post['valor_seguro'] ?? 0),
            'valor_outras_despesas' => (float)($post['valor_outras_despesas'] ?? 0),
            'observacoes' => $post['observacoes'] ?? null,
            'informacoes_adicionais' => $post['informacoes_adicionais'] ?? null,
            // Alíquotas customizadas (se fornecidas)
            'aliquota_icms' => $post['aliquota_icms'] ?? null,
            'aliquota_pis' => $post['aliquota_pis'] ?? null,
            'aliquota_cofins' => $post['aliquota_cofins'] ?? null,
            'aliquota_iss' => $post['aliquota_iss'] ?? null
        ];
    }

    /**
     * Busca entidade (emitente/destinatário) por tipo e ID
     */
    private function buscarEntidade($tipo, $id)
    {
        switch ($tipo) {
            case 'empresa_tomadora':
                return $this->empresaTomadoraModel->findById($id);
            case 'empresa_prestadora':
                return $this->empresaPrestadoraModel->findById($id);
            case 'fornecedor':
                return $this->fornecedorModel->findById($id);
            case 'cliente':
                return $this->clienteModel->findById($id);
            default:
                return null;
        }
    }

    /**
     * Gera conta a receber/pagar automaticamente
     */
    private function gerarConta($notaId, $nota)
    {
        try {
            if ($nota['natureza_operacao'] === NotaFiscal::NATUREZA_VENDA || 
                $nota['natureza_operacao'] === NotaFiscal::NATUREZA_SERVICO) {
                // Gerar conta a receber
                $this->contaReceberModel->create([
                    'numero_documento' => 'NF-' . $nota['numero'],
                    'descricao' => 'Faturamento NF #' . $nota['numero'],
                    'nota_fiscal_id' => $notaId,
                    'cliente_id' => $nota['destinatario_id'],
                    'valor_original' => $nota['valor_total'],
                    'data_vencimento' => date('Y-m-d', strtotime('+30 days')),
                    'criado_por' => $_SESSION['usuario_id'] ?? null
                ]);
            } elseif ($nota['natureza_operacao'] === NotaFiscal::NATUREZA_COMPRA) {
                // Gerar conta a pagar
                $this->contaPagarModel->create([
                    'numero_documento' => 'NF-' . $nota['numero'],
                    'descricao' => 'Compra NF #' . $nota['numero'],
                    'nota_fiscal_id' => $notaId,
                    'fornecedor_id' => $nota['emitente_id'],
                    'valor_original' => $nota['valor_total'],
                    'data_vencimento' => date('Y-m-d', strtotime('+30 days')),
                    'criado_por' => $_SESSION['usuario_id'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            error_log("Erro ao gerar conta para NF #{$notaId}: " . $e->getMessage());
        }
    }

    /**
     * Cancela contas vinculadas à nota
     */
    private function cancelarContasVinculadas($notaId)
    {
        try {
            $contas = $this->notaFiscalModel->getContasVinculadas($notaId);

            foreach ($contas as $conta) {
                if ($conta['tipo'] === 'conta_receber') {
                    $this->contaReceberModel->cancelar($conta['id'], 'Nota fiscal cancelada');
                } elseif ($conta['tipo'] === 'conta_pagar') {
                    $this->contaPagarModel->cancelar($conta['id'], 'Nota fiscal cancelada');
                }
            }
        } catch (\Exception $e) {
            error_log("Erro ao cancelar contas vinculadas à NF #{$notaId}: " . $e->getMessage());
        }
    }

    /**
     * Exclui nota (apenas rascunho)
     */
    public function delete($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        $nota = $this->notaFiscalModel->findById($id);

        if (!$nota) {
            $_SESSION['erro'] = 'Nota fiscal não encontrada.';
            $this->redirect('financeiro/notas-fiscais');
            return;
        }

        if ($nota['status'] !== NotaFiscal::STATUS_RASCUNHO) {
            $_SESSION['erro'] = 'Apenas notas em rascunho podem ser excluídas.';
            $this->redirect('financeiro/notas-fiscais/show/' . $id);
            return;
        }

        try {
            $this->notaFiscalModel->delete($id);
            $_SESSION['sucesso'] = 'Nota fiscal excluída com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao excluir nota fiscal: ' . $e->getMessage();
        }

        $this->redirect('financeiro/notas-fiscais');
    }
}
