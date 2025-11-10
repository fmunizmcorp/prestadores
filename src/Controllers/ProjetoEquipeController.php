<?php

namespace App\Controllers;

use App\Models\ProjetoEquipe;
use App\Models\Projeto;

class ProjetoEquipeController extends BaseController
{
    private $equipe;
    private $projeto;

    public function __construct()
    {
        parent::__construct();
        $this->equipe = new ProjetoEquipe();
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

        $membros = $this->equipe->getByProjeto($projetoId);
        $disponiveis = $this->equipe->getUsuariosDisponiveis($projetoId);
        $stats = $this->equipe->getEstatisticas($projetoId);
        $papeis = $this->equipe->getPapeisDisponiveis();

        $data = [
            'titulo' => 'Equipe - ' . $projeto['nome'],
            'projeto' => $projeto,
            'membros' => $membros,
            'disponiveis' => $disponiveis,
            'stats' => $stats,
            'papeis' => $papeis
        ];

        $this->render('projetos/equipe/index', $data);
    }

    public function store($projetoId)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos/equipe/' . $projetoId);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos/equipe/' . $projetoId);
            return;
        }

        // Verificar se usuário já está alocado
        if ($this->equipe->verificarAlocacao($projetoId, $_POST['usuario_id'])) {
            $_SESSION['erro'] = 'Usuário já está alocado neste projeto.';
            $this->redirect('projetos/equipe/' . $projetoId);
            return;
        }

        $data = [
            'projeto_id' => $projetoId,
            'usuario_id' => $_POST['usuario_id'],
            'papel' => $_POST['papel'],
            'horas_previstas' => $_POST['horas_previstas'] ?? null,
            'custo_hora' => $_POST['custo_hora'] ?? null,
            'data_entrada' => $_POST['data_entrada'] ?? date('Y-m-d'),
            'disponivel' => 1,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->equipe->create($data);
            $_SESSION['sucesso'] = 'Membro adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao adicionar membro: ' . $e->getMessage();
        }

        $this->redirect('projetos/equipe/' . $projetoId);
    }

    public function update($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        $data = [
            'papel' => $_POST['papel'],
            'horas_previstas' => $_POST['horas_previstas'] ?? null,
            'custo_hora' => $_POST['custo_hora'] ?? null,
            'disponivel' => $_POST['disponivel'] ?? 1,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->equipe->update($id, $data);
            $_SESSION['sucesso'] = 'Alocação atualizada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar alocação: ' . $e->getMessage();
        }

        $this->redirectBack();
    }

    public function avaliar($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        try {
            $this->equipe->avaliarDesempenho($id, $_POST['nota'], $_POST['comentario'] ?? null);
            $_SESSION['sucesso'] = 'Avaliação registrada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao avaliar: ' . $e->getMessage();
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
            $this->equipe->delete($id);
            $_SESSION['sucesso'] = 'Membro removido da equipe!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao remover membro: ' . $e->getMessage();
        }

        $this->redirectBack();
    }
}
