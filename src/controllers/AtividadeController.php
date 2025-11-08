<?php

namespace App\Controllers;

use App\Models\Atividade;
use App\Models\Projeto;
use App\Models\Usuario;
use App\Models\AtividadeFinanceiro;

class AtividadeController extends BaseController
{
    private $atividade;
    private $projeto;
    private $usuario;
    private $atividadeFinanceiro;

    public function __construct()
    {
        parent::__construct();
        
        $this->atividade = new Atividade();
        $this->projeto = new Projeto();
        $this->usuario = new Usuario();
        $this->atividadeFinanceiro = new AtividadeFinanceiro();
    }

    /**
     * Lista todas as atividades
     */
    public function index()
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 25;

        // Filtros
        $filtros = [];
        
        if (!empty($_GET['projeto_id'])) {
            $filtros['projeto_id'] = $_GET['projeto_id'];
        }
        
        if (!empty($_GET['status'])) {
            $filtros['status'] = $_GET['status'];
        }
        
        if (!empty($_GET['responsavel_id'])) {
            $filtros['responsavel_id'] = $_GET['responsavel_id'];
        }
        
        if (!empty($_GET['busca'])) {
            $filtros['busca'] = $_GET['busca'];
        }

        $atividades = $this->atividade->all($filtros, $page, $limit);
        $total = $this->atividade->count($filtros);
        
        $totalPages = ceil($total / $limit);

        // Dados para filtros
        $projetos = $this->projeto->all();
        $usuarios = $this->usuario->all();

        // Estatísticas
        $stats = [
            'total' => $this->atividade->countTotal(),
            'pendente' => $this->atividade->countPorStatus('pendente'),
            'em_andamento' => $this->atividade->countPorStatus('em_andamento'),
            'concluida' => $this->atividade->countPorStatus('concluida'),
        ];

        $data = [
            'titulo' => 'Atividades',
            'atividades' => $atividades,
            'projetos' => $projetos,
            'usuarios' => $usuarios,
            'stats' => $stats,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];

        $this->render('atividades/index', $data);
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        $data = [
            'titulo' => 'Nova Atividade',
            'projetos' => $this->projeto->all(),
            'usuarios' => $this->usuario->all()
        ];

        $this->render('atividades/create', $data);
    }

    /**
     * Processa criação da atividade
     */
    public function store()
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('atividades');
            return;
        }

        // Validar CSRF
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('atividades/create');
            return;
        }

        // Validações
        $erros = [];

        if (empty($_POST['titulo'])) {
            $erros[] = 'Título da atividade é obrigatório.';
        }

        if (empty($_POST['projeto_id'])) {
            $erros[] = 'Projeto é obrigatório.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('atividades/create');
            return;
        }

        // Preparar dados
        $data = [
            'projeto_id' => $_POST['projeto_id'],
            'titulo' => trim($_POST['titulo']),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'responsavel_id' => $_POST['responsavel_id'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'status' => 'pendente',
            'custo_estimado' => $_POST['custo_estimado'] ?? 0,
            'horas_estimadas' => $_POST['horas_estimadas'] ?? 0,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $id = $this->atividade->create($data);
            
            $_SESSION['sucesso'] = 'Atividade criada com sucesso!';
            $this->redirect('atividades/show/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar atividade: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('atividades/create');
        }
    }

    /**
     * Exibe detalhes da atividade
     */
    public function show($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        $atividade = $this->atividade->findById($id);

        if (!$atividade) {
            $_SESSION['erro'] = 'Atividade não encontrada.';
            $this->redirect('atividades');
            return;
        }

        $data = [
            'titulo' => 'Atividade: ' . $atividade['titulo'],
            'atividade' => $atividade
        ];

        $this->render('atividades/show', $data);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        $atividade = $this->atividade->findById($id);

        if (!$atividade) {
            $_SESSION['erro'] = 'Atividade não encontrada.';
            $this->redirect('atividades');
            return;
        }

        $data = [
            'titulo' => 'Editar Atividade',
            'atividade' => $atividade,
            'projetos' => $this->projeto->all(),
            'usuarios' => $this->usuario->all()
        ];

        $this->render('atividades/edit', $data);
    }

    /**
     * Processa atualização da atividade
     */
    public function update($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('atividades');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('atividades/edit/' . $id);
            return;
        }

        $atividade = $this->atividade->findById($id);

        if (!$atividade) {
            $_SESSION['erro'] = 'Atividade não encontrada.';
            $this->redirect('atividades');
            return;
        }

        // Validações
        $erros = [];

        if (empty($_POST['titulo'])) {
            $erros[] = 'Título da atividade é obrigatório.';
        }

        if (!empty($erros)) {
            $_SESSION['erro'] = implode('<br>', $erros);
            $_SESSION['old'] = $_POST;
            $this->redirect('atividades/edit/' . $id);
            return;
        }

        $data = [
            'titulo' => trim($_POST['titulo']),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'responsavel_id' => $_POST['responsavel_id'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'custo_estimado' => $_POST['custo_estimado'] ?? 0,
            'horas_estimadas' => $_POST['horas_estimadas'] ?? 0,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->atividade->update($id, $data);
            
            $_SESSION['sucesso'] = 'Atividade atualizada com sucesso!';
            $this->redirect('atividades/show/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar atividade: ' . $e->getMessage();
            $_SESSION['old'] = $_POST;
            $this->redirect('atividades/edit/' . $id);
        }
    }

    /**
     * Altera status da atividade
     */
    public function alterarStatus($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('atividades');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('atividades/show/' . $id);
            return;
        }

        $novoStatus = $_POST['status'] ?? '';

        if (empty($novoStatus)) {
            $_SESSION['erro'] = 'Status é obrigatório.';
            $this->redirect('atividades/show/' . $id);
            return;
        }

        try {
            $this->atividade->alterarStatus($id, $novoStatus);
            
            $_SESSION['sucesso'] = 'Status alterado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao alterar status: ' . $e->getMessage();
        }

        $this->redirect('atividades/show/' . $id);
    }

    /**
     * Remove atividade
     */
    public function delete($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('atividades');
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('atividades');
            return;
        }

        try {
            $this->atividade->delete($id);
            
            $_SESSION['sucesso'] = 'Atividade removida com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao remover atividade: ' . $e->getMessage();
        }

        $this->redirect('atividades');
    }

    /**
     * Exibe dashboard de custos da atividade
     */
    public function custos($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        $atividade = $this->atividade->findById($id);

        if (!$atividade) {
            $_SESSION['erro'] = 'Atividade não encontrada.';
            $this->redirect('atividades');
            return;
        }

        try {
            // Gerar relatório financeiro completo
            $relatorio = $this->atividadeFinanceiro->gerarRelatorioCompleto($id);

            $data = [
                'titulo' => 'Custos - ' . $atividade['titulo'],
                'atividade' => $relatorio['atividade'],
                'custos' => $relatorio['custos'],
                'custo_hora' => $relatorio['custo_hora'],
                'variacao' => $relatorio['variacao'],
                'alocacao' => $relatorio['alocacao']
            ];

            $this->render('atividades/custos', $data);
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao gerar relatório de custos: ' . $e->getMessage();
            $this->redirect('atividades/show/' . $id);
        }
    }
}
