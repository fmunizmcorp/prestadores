<?php

namespace App\Controllers;

use App\Models\ProjetoOrcamento;
use App\Models\Projeto;

class ProjetoOrcamentoController extends BaseController
{
    private $orcamento;
    private $projeto;

    public function __construct()
    {
        parent::__construct();
        $this->orcamento = new ProjetoOrcamento();
        $this->projeto = new Projeto();
    }

    public function index($projetoId)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        $projeto = $this->projeto->findById($projetoId);
        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        $itens = $this->orcamento->getByProjeto($projetoId);
        $totais = $this->orcamento->getTotais($projetoId);
        $porCategoria = $this->orcamento->getTotaisPorCategoria($projetoId);
        $categorias = $this->orcamento->getCategoriasDisponiveis();
        $unidades = $this->orcamento->getUnidadesDisponiveis();

        $data = [
            'titulo' => 'Orçamento - ' . $projeto['nome'],
            'projeto' => $projeto,
            'itens' => $itens,
            'totais' => $totais,
            'porCategoria' => $porCategoria,
            'categorias' => $categorias,
            'unidades' => $unidades
        ];

        $this->render('projetos/orcamento/index', $data);
    }

    public function store($projetoId)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos/orcamento/' . $projetoId);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos/orcamento/' . $projetoId);
            return;
        }

        $data = [
            'projeto_id' => $projetoId,
            'categoria' => $_POST['categoria'],
            'tipo' => $_POST['tipo'] ?? 'despesa',
            'descricao' => $_POST['descricao'],
            'unidade' => $_POST['unidade'] ?? 'un',
            'quantidade' => $_POST['quantidade'] ?? 1,
            'valor_unitario' => $_POST['valor_unitario'] ?? 0,
            'responsavel_id' => $_POST['responsavel_id'] ?? null,
            'centro_custo' => $_POST['centro_custo'] ?? null,
            'data_prevista' => $_POST['data_prevista'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->orcamento->create($data);
            $_SESSION['sucesso'] = 'Item orçamentário criado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar item: ' . $e->getMessage();
        }

        $this->redirect('projetos/orcamento/' . $projetoId);
    }

    public function update($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        $data = [
            'categoria' => $_POST['categoria'],
            'tipo' => $_POST['tipo'] ?? 'despesa',
            'descricao' => $_POST['descricao'],
            'unidade' => $_POST['unidade'] ?? 'un',
            'quantidade' => $_POST['quantidade'] ?? 1,
            'valor_unitario' => $_POST['valor_unitario'] ?? 0,
            'responsavel_id' => $_POST['responsavel_id'] ?? null,
            'centro_custo' => $_POST['centro_custo'] ?? null,
            'data_prevista' => $_POST['data_prevista'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->orcamento->update($id, $data);
            $_SESSION['sucesso'] = 'Item atualizado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar item: ' . $e->getMessage();
        }

        $this->redirectBack();
    }

    public function aprovar($id)
    {
        $this->checkPermission(['master', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        try {
            $this->orcamento->aprovar($id, $_SESSION['user_id'], $_POST['observacoes'] ?? null);
            $_SESSION['sucesso'] = 'Item aprovado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao aprovar item: ' . $e->getMessage();
        }

        $this->redirectBack();
    }

    public function delete($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        try {
            $this->orcamento->delete($id);
            $_SESSION['sucesso'] = 'Item removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao remover item: ' . $e->getMessage();
        }

        $this->redirectBack();
    }
}
