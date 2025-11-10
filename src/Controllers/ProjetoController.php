<?php

namespace App\Controllers;

use App\Models\Projeto;
use App\Models\ProjetoCategoria;
use App\Models\EmpresaTomadora;
use App\Models\Contrato;
use App\Models\Usuario;
use App\Models\ProjetoFinanceiro;

class ProjetoController extends BaseController
{
    private $projeto;
    private $categoria;
    private $tomadora;
    private $contrato;
    private $usuario;
    private $projetoFinanceiro;

    public function __construct()
    {
        parent::__construct();
        
        try {
            $this->projeto = new Projeto();
            $this->categoria = new ProjetoCategoria();
            $this->tomadora = new EmpresaTomadora();
            $this->contrato = new Contrato();
            $this->usuario = new Usuario();
            $this->projetoFinanceiro = new ProjetoFinanceiro();
        } catch (\Throwable $e) {
            error_log("ProjetoController construct error: " . $e->getMessage());
            // Fallback - minimal initialization
            $this->projeto = null;
        }
    }

    /**
     * Lista todos os projetos
     */
    public function index()
    {
        // Fallback if models failed to initialize
        if ($this->projeto === null) {
            require ROOT_PATH . '/src/Views/projetos/index_simple.php';
            return;
        }
        
        $this->checkPermission(['master', 'admin', 'gestor']);

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 25;

        // Filtros
        $filtros = [];
        
        if (!empty($_GET['status'])) {
            $filtros['status'] = $_GET['status'];
        }
        
        if (!empty($_GET['categoria_id'])) {
            $filtros['categoria_id'] = $_GET['categoria_id'];
        }
        
        if (!empty($_GET['empresa_tomadora_id'])) {
            $filtros['empresa_tomadora_id'] = $_GET['empresa_tomadora_id'];
        }
        
        if (!empty($_GET['gerente_id'])) {
            $filtros['gerente_id'] = $_GET['gerente_id'];
        }
        
        if (!empty($_GET['busca'])) {
            $filtros['busca'] = $_GET['busca'];
        }

        $projetos = $this->projeto->all($filtros, $page, $limit);
        $total = $this->projeto->count($filtros);
        
        $totalPages = ceil($total / $limit);

        // Dados para filtros
        $categorias = $this->categoria->all();
        $tomadoras = $this->tomadora->all();
        $gerentes = $this->usuario->all(['perfil' => ['master', 'admin', 'gestor']]);

        // Estatísticas
        $stats = [
            'total' => $this->projeto->countTotal(),
            'planejamento' => $this->projeto->countPorStatus('planejamento'),
            'execucao' => $this->projeto->countPorStatus('execucao'),
            'concluido' => $this->projeto->countPorStatus('concluido'),
            'atrasados' => count($this->projeto->getAtrasados()),
        ];

        $data = [
            'titulo' => 'Projetos',
            'projetos' => $projetos,
            'categorias' => $categorias,
            'tomadoras' => $tomadoras,
            'gerentes' => $gerentes,
            'stats' => $stats,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('projetos/index', $data);
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        $data = [
            'titulo' => 'Novo Projeto',
            'categorias' => $this->categoria->all(),
            'tomadoras' => $this->tomadora->all(),
            'contratos' => $this->contrato->all(['status' => 'ativo']),
            'gerentes' => $this->usuario->all(['perfil' => ['master', 'admin', 'gestor']])
        ];

        $this->render('projetos/create', $data);
    }

    /**
     * Processa criação do projeto
     */
    public function store()
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos');
            return;
        }

        // Validar CSRF
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos/create');
            return;
        }

        // Validações
        $erros = [];

        if (empty($_POST['codigo'])) {
            $erros[] = 'Código do projeto é obrigatório.';
        } elseif (!$this->projeto->validateUniqueCodigo($_POST['codigo'])) {
            $erros[] = 'Código do projeto já está em uso.';
        }

        if (empty($_POST['nome'])) {
            $erros[] = 'Nome do projeto é obrigatório.';
        }

        if (empty($_POST['empresa_tomadora_id'])) {
            $erros[] = 'Empresa tomadora é obrigatória.';
        }

        if (empty($_POST['gerente_id'])) {
            $erros[] = 'Gerente do projeto é obrigatório.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('projetos/create');
            return;
        }

        // Preparar dados
        $data = [
            'codigo' => trim($_POST['codigo']),
            'nome' => trim($_POST['nome']),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'categoria_id' => $_POST['categoria_id'] ?? null,
            'empresa_tomadora_id' => $_POST['empresa_tomadora_id'],
            'contrato_id' => $_POST['contrato_id'] ?? null,
            'gerente_id' => $_POST['gerente_id'],
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'duracao_estimada' => $_POST['duracao_estimada'] ?? null,
            'orcamento_previsto' => $_POST['orcamento_previsto'] ?? 0,
            'horas_previstas' => $_POST['horas_previstas'] ?? 0,
            'status' => 'planejamento',
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'visibilidade' => $_POST['visibilidade'] ?? 'privado',
            'objetivos' => $_POST['objetivos'] ?? null,
            'escopo' => $_POST['escopo'] ?? null,
            'criterios_aceitacao' => $_POST['criterios_aceitacao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $id = $this->projeto->create($data);
            
            $_SESSION['sucesso'] = 'Projeto criado com sucesso!';
            $this->redirect('projetos/show/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar projeto: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('projetos/create');
        }
    }

    /**
     * Exibe detalhes do projeto
     */
    public function show($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        $projeto = $this->projeto->findById($id);

        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        // Buscar dashboard do projeto
        $dashboard = $this->projeto->getDashboard($id);

        $data = [
            'titulo' => 'Projeto: ' . $projeto['nome'],
            'projeto' => $projeto,
            'dashboard' => $dashboard
        ];

        $this->render('projetos/show', $data);
    }

    /**
     * Exibe dashboard completo do projeto
     */
    public function dashboard($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        $projeto = $this->projeto->findById($id);

        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        $dashboard = $this->projeto->getDashboard($id);
        $stats = $this->projeto->getEstatisticasGerais();

        $data = [
            'titulo' => 'Dashboard - ' . $projeto['nome'],
            'projeto' => $projeto,
            'dashboard' => $dashboard,
            'stats' => $stats
        ];

        $this->render('projetos/dashboard', $data);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        $projeto = $this->projeto->findById($id);

        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        $data = [
            'titulo' => 'Editar Projeto',
            'projeto' => $projeto,
            'categorias' => $this->categoria->all(),
            'tomadoras' => $this->tomadora->all(),
            'contratos' => $this->contrato->all(['status' => 'ativo']),
            'gerentes' => $this->usuario->all(['perfil' => ['master', 'admin', 'gestor']])
        ];

        $this->render('projetos/edit', $data);
    }

    /**
     * Processa atualização do projeto
     */
    public function update($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos/edit/' . $id);
            return;
        }

        $projeto = $this->projeto->findById($id);

        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        // Validações
        $erros = [];

        if (empty($_POST['codigo'])) {
            $erros[] = 'Código do projeto é obrigatório.';
        } elseif (!$this->projeto->validateUniqueCodigo($_POST['codigo'], $id)) {
            $erros[] = 'Código do projeto já está em uso.';
        }

        if (empty($_POST['nome'])) {
            $erros[] = 'Nome do projeto é obrigatório.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('projetos/edit/' . $id);
            return;
        }

        $data = [
            'codigo' => trim($_POST['codigo']),
            'nome' => trim($_POST['nome']),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'categoria_id' => $_POST['categoria_id'] ?? null,
            'empresa_tomadora_id' => $_POST['empresa_tomadora_id'],
            'contrato_id' => $_POST['contrato_id'] ?? null,
            'gerente_id' => $_POST['gerente_id'],
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'duracao_estimada' => $_POST['duracao_estimada'] ?? null,
            'orcamento_previsto' => $_POST['orcamento_previsto'] ?? 0,
            'horas_previstas' => $_POST['horas_previstas'] ?? 0,
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'visibilidade' => $_POST['visibilidade'] ?? 'privado',
            'objetivos' => $_POST['objetivos'] ?? null,
            'escopo' => $_POST['escopo'] ?? null,
            'criterios_aceitacao' => $_POST['criterios_aceitacao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->projeto->update($id, $data);
            
            $_SESSION['sucesso'] = 'Projeto atualizado com sucesso!';
            $this->redirect('projetos/show/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar projeto: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('projetos/edit/' . $id);
        }
    }

    /**
     * Altera status do projeto
     */
    public function alterarStatus($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos/show/' . $id);
            return;
        }

        $novoStatus = $_POST['status'] ?? '';
        $motivo = $_POST['motivo'] ?? null;

        if (empty($novoStatus)) {
            $_SESSION['erro'] = 'Status é obrigatório.';
            $this->redirect('projetos/show/' . $id);
            return;
        }

        try {
            $this->projeto->alterarStatus($id, $novoStatus, $motivo);
            
            $_SESSION['sucesso'] = 'Status alterado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao alterar status: ' . $e->getMessage();
        }

        $this->redirect('projetos/show/' . $id);
    }

    /**
     * Remove projeto (soft delete - arquiva)
     */
    public function delete($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos');
            return;
        }

        try {
            $this->projeto->delete($id);
            
            $_SESSION['sucesso'] = 'Projeto arquivado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao arquivar projeto: ' . $e->getMessage();
        }

        $this->redirect('projetos');
    }

    /**
     * Exibe dashboard financeiro completo do projeto
     */
    public function financeiro($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        $projeto = $this->projeto->findById($id);

        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        try {
            // Gerar relatório financeiro completo
            $relatorio = $this->projetoFinanceiro->gerarRelatorioCompleto($id);

            $data = [
                'titulo' => 'Financeiro - ' . $projeto['nome'],
                'projeto' => $relatorio['projeto'],
                'custos' => $relatorio['custos'],
                'receitas' => $relatorio['receitas'],
                'margem' => $relatorio['margem'],
                'performance' => $relatorio['performance'],
                'consolidacao_mensal' => $relatorio['consolidacao_mensal'],
                'top_fornecedores' => $relatorio['top_fornecedores'],
                'contas_pagar_pendentes' => $relatorio['contas_pagar_pendentes'],
                'contas_receber_pendentes' => $relatorio['contas_receber_pendentes']
            ];

            $this->render('projetos/financeiro', $data);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao gerar relatório financeiro: ' . $e->getMessage();
            $this->redirect('projetos/show/' . $id);
        }
    }
}
