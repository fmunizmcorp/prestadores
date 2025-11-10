<?php

namespace App\Controllers;

use App\Models\ProjetoExecucao;
use App\Models\Projeto;
use App\Models\ProjetoEtapa;

class ProjetoExecucaoController extends BaseController
{
    private $execucao;
    private $projeto;
    private $etapa;

    public function __construct()
    {
        parent::__construct();
        $this->execucao = new ProjetoExecucao();
        $this->projeto = new Projeto();
        $this->etapa = new ProjetoEtapa();
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

        $filtros = [];
        if (isset($_GET['usuario_id'])) {
            $filtros['usuario_id'] = $_GET['usuario_id'];
        }
        if (isset($_GET['data_inicio'])) {
            $filtros['data_inicio'] = $_GET['data_inicio'];
            $filtros['data_fim'] = $_GET['data_fim'] ?? date('Y-m-d');
        }

        $registros = $this->execucao->getByProjeto($projetoId, $filtros);
        $totais = $this->execucao->getTotais($projetoId, $filtros);
        $etapas = $this->etapa->getByProjeto($projetoId);
        $tiposHora = $this->execucao->getTiposHoraDisponiveis();

        $data = [
            'titulo' => 'Execução - ' . $projeto['nome'],
            'projeto' => $projeto,
            'registros' => $registros,
            'totais' => $totais,
            'etapas' => $etapas,
            'tiposHora' => $tiposHora,
            'filtros' => $filtros
        ];

        $this->render('projetos/execucao/index', $data);
    }

    public function store($projetoId)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projetos/execucao/' . $projetoId);
            return;
        }

        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            $this->redirect('projetos/execucao/' . $projetoId);
            return;
        }

        $data = [
            'projeto_id' => $projetoId,
            'etapa_id' => $_POST['etapa_id'] ?? null,
            'usuario_id' => $_SESSION['user_id'],
            'data' => $_POST['data'] ?? date('Y-m-d'),
            'horas' => $_POST['horas'],
            'tipo_hora' => $_POST['tipo_hora'] ?? 'normal',
            'descricao_atividade' => $_POST['descricao_atividade'],
            'valor' => $_POST['valor'] ?? 0,
            'faturavel' => $_POST['faturavel'] ?? 1,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->execucao->create($data);
            $_SESSION['sucesso'] = 'Horas registradas com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao registrar horas: ' . $e->getMessage();
        }

        $this->redirect('projetos/execucao/' . $projetoId);
    }

    public function update($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        $data = [
            'etapa_id' => $_POST['etapa_id'] ?? null,
            'data' => $_POST['data'],
            'horas' => $_POST['horas'],
            'tipo_hora' => $_POST['tipo_hora'] ?? 'normal',
            'descricao_atividade' => $_POST['descricao_atividade'],
            'valor' => $_POST['valor'] ?? 0,
            'faturavel' => $_POST['faturavel'] ?? 1,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        try {
            $this->execucao->update($id, $data);
            $_SESSION['sucesso'] = 'Registro atualizado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar registro: ' . $e->getMessage();
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
            $this->execucao->aprovar($id, $_SESSION['user_id'], $_POST['observacoes'] ?? null);
            $_SESSION['sucesso'] = 'Registro aprovado!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao aprovar: ' . $e->getMessage();
        }

        $this->redirectBack();
    }

    public function delete($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor', 'usuario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        try {
            $this->execucao->delete($id);
            $_SESSION['sucesso'] = 'Registro removido!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao remover: ' . $e->getMessage();
        }

        $this->redirectBack();
    }
}
