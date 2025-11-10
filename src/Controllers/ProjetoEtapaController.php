<?php

namespace App\Controllers;

use App\Models\ProjetoEtapa;
use App\Models\Projeto;

class ProjetoEtapaController extends BaseController
{
    private $etapa;
    private $projeto;

    public function __construct()
    {
        parent::__construct();
        $this->etapa = new ProjetoEtapa();
        $this->projeto = new Projeto();
    }

    public function index($projetoId)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        $projeto = $this->projeto->findById($projetoId);
        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        $etapas = $this->etapa->getByProjeto($projetoId);
        $atrasadas = $this->etapa->getAtrasadas($projetoId);

        $data = [
            'titulo' => 'Cronograma - ' . $projeto['nome'],
            'projeto' => $projeto,
            'etapas' => $etapas,
            'atrasadas' => $atrasadas
        ];

        $this->render('projetos/etapas/index', $data);
    }

    public function gantt($projetoId)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        $projeto = $this->projeto->findById($projetoId);
        if (!$projeto) {
            $_SESSION['erro'] = 'Projeto não encontrado.';
            $this->redirect('projetos');
            return;
        }

        $cronograma = $this->etapa->getCronogramaGantt($projetoId);
        $caminhoCritico = $this->etapa->getCaminhoCritico($projetoId);

        $data = [
            'titulo' => 'Gantt - ' . $projeto['nome'],
            'projeto' => $projeto,
            'cronograma' => $cronograma,
            'caminhoCritico' => $caminhoCritico
        ];

        $this->render('projetos/etapas/gantt', $data);
    }

    public function store($projetoId)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos/etapas/' . $projetoId);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos/etapas/' . $projetoId);
            return;
        }

        $data = [
            'projeto_id' => $projetoId,
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
            'data_inicio' => $_POST['data_inicio'],
            'data_fim' => $_POST['data_fim'],
            'duracao_estimada' => $_POST['duracao_estimada'] ?? null,
            'responsavel_id' => $_POST['responsavel_id'] ?? null,
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'dependencias' => !empty($_POST['dependencias']) ? json_encode($_POST['dependencias']) : null,
            'ordem' => $_POST['ordem'] ?? 0
        ];

        try {
            $this->etapa->create($data);
            $_SESSION['sucesso'] = 'Etapa criada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar etapa: ' . $e->getMessage();
        }

        $this->redirect('projetos/etapas/' . $projetoId);
    }

    public function update($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirectBack();
            return;
        }

        $data = [
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
            'data_inicio' => $_POST['data_inicio'],
            'data_fim' => $_POST['data_fim'],
            'duracao_estimada' => $_POST['duracao_estimada'] ?? null,
            'responsavel_id' => $_POST['responsavel_id'] ?? null,
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'percentual_concluido' => $_POST['percentual_concluido'] ?? 0
        ];

        try {
            $this->etapa->update($id, $data);
            $_SESSION['sucesso'] = 'Etapa atualizada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar etapa: ' . $e->getMessage();
        }

        $this->redirectBack();
    }

    public function aprovar($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        try {
            $this->etapa->aprovar($id, $_SESSION['user_id']);
            $_SESSION['sucesso'] = 'Etapa aprovada!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao aprovar etapa: ' . $e->getMessage();
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
            $this->etapa->delete($id);
            $_SESSION['sucesso'] = 'Etapa removida com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao remover etapa: ' . $e->getMessage();
        }

        $this->redirectBack();
    }
}
