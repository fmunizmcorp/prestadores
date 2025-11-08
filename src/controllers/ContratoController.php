<?php

namespace App\Controllers;

use App\Models\Contrato;
use App\Models\EmpresaTomadora;
use App\Models\EmpresaPrestadora;
use App\Models\Servico;
use App\Models\ContratoFinanceiro;

class ContratoController {
    private $model;
    private $empresaTomadoraModel;
    private $empresaPrestadoraModel;
    private $servicoModel;
    private $contratoFinanceiro;
    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        
        $this->model = new Contrato();
        $this->empresaTomadoraModel = new EmpresaTomadora();
        $this->empresaPrestadoraModel = new EmpresaPrestadora();
        $this->servicoModel = new Servico();
        $this->contratoFinanceiro = new ContratoFinanceiro();
    }
    
    // LISTAGEM
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $tipo_contrato = $_GET['tipo_contrato'] ?? '';
        $empresa_tomadora_id = $_GET['empresa_tomadora_id'] ?? '';
        $empresa_prestadora_id = $_GET['empresa_prestadora_id'] ?? '';
        
        $filtros = [];
        if ($search) $filtros['search'] = $search;
        if ($status) $filtros['status'] = $status;
        if ($tipo_contrato) $filtros['tipo_contrato'] = $tipo_contrato;
        if ($empresa_tomadora_id) $filtros['empresa_tomadora_id'] = $empresa_tomadora_id;
        if ($empresa_prestadora_id) $filtros['empresa_prestadora_id'] = $empresa_prestadora_id;
        
        $contratos = $this->model->all($filtros, $page, $limit);
        $total = $this->model->count($filtros);
        $totalPaginas = ceil($total / $limit);
        
        // Para filtros
        $empresasTomadoras = $this->empresaTomadoraModel->getAtivas();
        $empresasPrestadoras = $this->empresaPrestadoraModel->getAtivas();
        
        // Estatísticas
        $stats = [
            'total' => $this->model->countTotal(),
            'vigentes' => $this->model->countPorStatus('Vigente'),
            'vencendo' => count($this->model->getVencendo(90)),
            'valor_total' => $this->model->getValorTotalAtivos()
        ];
        
        require __DIR__ . '/../views/contratos/index.php';
    }
    
    // FORMULÁRIO DE CRIAÇÃO
    public function create() {
        $empresasTomadoras = $this->empresaTomadoraModel->getAtivas();
        $empresasPrestadoras = $this->empresaPrestadoraModel->getAtivas();
        $servicos = $this->servicoModel->getAtivos();
        
        require __DIR__ . '/../views/contratos/create.php';
    }
    
    // SALVAR NOVO CONTRATO
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos/create');
            exit;
        }
        
        // Validar
        $erros = $this->validateForm($_POST);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos/create');
            exit;
        }
        
        // Upload de arquivo
        $arquivo = null;
        if (isset($_FILES['arquivo_contrato']) && $_FILES['arquivo_contrato']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadContrato($_FILES['arquivo_contrato']);
            if ($upload['success']) {
                $arquivo = $upload['filename'];
            }
        }
        
        $data = $this->prepareData($_POST, $arquivo);
        $data['criado_por'] = $_SESSION['usuario_id'];
        
        try {
            $id = $this->model->create($data);
            
            // Adicionar histórico
            $this->model->addHistorico($id, [
                'tipo_evento' => 'Criação',
                'descricao' => 'Contrato criado',
                'usuario_id' => $_SESSION['usuario_id'],
                'data_evento' => date('Y-m-d H:i:s'),
                'detalhes_json' => json_encode(['action' => 'create'])
            ]);
            
            $_SESSION['sucesso'] = 'Contrato cadastrado com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cadastrar contrato: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos/create');
            exit;
        }
    }
    
    // EXIBIR DETALHES
    public function show($id) {
        $contrato = $this->model->findById($id);
        
        if (!$contrato) {
            $_SESSION['erro'] = 'Contrato não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        $servicos = $this->model->getServicos($id);
        $aditivos = $this->model->getAditivos($id);
        $historico = $this->model->getHistorico($id, 20);
        $valoresPeriodo = $this->model->getValoresPeriodo($id);
        
        require __DIR__ . '/../views/contratos/show.php';
    }
    
    // FORMULÁRIO DE EDIÇÃO
    public function edit($id) {
        $contrato = $this->model->findById($id);
        
        if (!$contrato) {
            $_SESSION['erro'] = 'Contrato não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        $empresasTomadoras = $this->empresaTomadoraModel->getAtivas();
        $empresasPrestadoras = $this->empresaPrestadoraModel->getAtivas();
        $servicos = $this->servicoModel->getAtivos();
        
        require __DIR__ . '/../views/contratos/edit.php';
    }
    
    // ATUALIZAR CONTRATO
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id/edit");
            exit;
        }
        
        $contrato = $this->model->findById($id);
        if (!$contrato) {
            $_SESSION['erro'] = 'Contrato não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        $erros = $this->validateForm($_POST, $id);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id/edit");
            exit;
        }
        
        // Upload de arquivo
        $arquivo = $contrato['arquivo_contrato'];
        if (isset($_FILES['arquivo_contrato']) && $_FILES['arquivo_contrato']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadContrato($_FILES['arquivo_contrato']);
            if ($upload['success']) {
                if ($arquivo && file_exists(__DIR__ . '/../../public/uploads/contratos/' . $arquivo)) {
                    unlink(__DIR__ . '/../../public/uploads/contratos/' . $arquivo);
                }
                $arquivo = $upload['filename'];
            }
        }
        
        $data = $this->prepareData($_POST, $arquivo);
        $data['atualizado_por'] = $_SESSION['usuario_id'];
        
        try {
            $this->model->update($id, $data);
            
            // Adicionar histórico
            $this->model->addHistorico($id, [
                'tipo_evento' => 'Atualização',
                'descricao' => 'Contrato atualizado',
                'usuario_id' => $_SESSION['usuario_id'],
                'data_evento' => date('Y-m-d H:i:s'),
                'detalhes_json' => json_encode(['action' => 'update'])
            ]);
            
            $_SESSION['sucesso'] = 'Contrato atualizado com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar contrato: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id/edit");
            exit;
        }
    }
    
    // EXCLUIR
    public function destroy($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        $contrato = $this->model->findById($id);
        if (!$contrato) {
            $_SESSION['erro'] = 'Contrato não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        try {
            $this->model->delete($id);
            $_SESSION['sucesso'] = 'Contrato excluído com sucesso!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao excluir contrato: ' . $e->getMessage();
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id");
            exit;
        }
    }
    
    // SERVIÇOS DO CONTRATO
    public function addServico($contratoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId");
            exit;
        }
        
        $erros = [];
        if (empty($_POST['servico_id'])) $erros[] = 'Serviço é obrigatório.';
        if (empty($_POST['quantidade'])) $erros[] = 'Quantidade é obrigatória.';
        if (empty($_POST['valor_unitario'])) $erros[] = 'Valor unitário é obrigatório.';
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId#servicos");
            exit;
        }
        
        $quantidade = floatval($_POST['quantidade']);
        $valorUnitario = floatval($_POST['valor_unitario']);
        $valorTotal = $quantidade * $valorUnitario;
        
        $data = [
            'servico_id' => $_POST['servico_id'],
            'descricao_customizada' => $_POST['descricao_customizada'] ?? null,
            'quantidade' => $quantidade,
            'unidade' => $_POST['unidade'],
            'valor_unitario' => $valorUnitario,
            'valor_total' => $valorTotal,
            'periodicidade' => $_POST['periodicidade'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->model->addServico($contratoId, $data);
            
            // Histórico
            $this->model->addHistorico($contratoId, [
                'tipo_evento' => 'Serviço Adicionado',
                'descricao' => 'Serviço adicionado ao contrato',
                'usuario_id' => $_SESSION['usuario_id'],
                'data_evento' => date('Y-m-d H:i:s'),
                'detalhes_json' => json_encode($data)
            ]);
            
            $_SESSION['sucesso'] = 'Serviço adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId#servicos");
        exit;
    }
    
    public function deleteServico($contratoId, $servicoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId");
            exit;
        }
        
        try {
            $this->model->deleteServico($servicoId);
            
            $this->model->addHistorico($contratoId, [
                'tipo_evento' => 'Serviço Removido',
                'descricao' => 'Serviço removido do contrato',
                'usuario_id' => $_SESSION['usuario_id'],
                'data_evento' => date('Y-m-d H:i:s'),
                'detalhes_json' => json_encode(['servico_id' => $servicoId])
            ]);
            
            $_SESSION['sucesso'] = 'Serviço removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId#servicos");
        exit;
    }
    
    // ADITIVOS
    public function addAditivo($contratoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId");
            exit;
        }
        
        $erros = [];
        if (empty($_POST['numero_aditivo'])) $erros[] = 'Número do aditivo é obrigatório.';
        if (empty($_POST['tipo_aditivo'])) $erros[] = 'Tipo de aditivo é obrigatório.';
        if (empty($_POST['data_aditivo'])) $erros[] = 'Data do aditivo é obrigatória.';
        if (empty($_POST['descricao'])) $erros[] = 'Descrição é obrigatória.';
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId#aditivos");
            exit;
        }
        
        // Upload de arquivo
        $arquivo = null;
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadContrato($_FILES['arquivo']);
            if ($upload['success']) {
                $arquivo = $upload['filename'];
            }
        }
        
        // Calcular percentual de alteração se houver valores
        $percentual = null;
        if (!empty($_POST['valor_anterior']) && !empty($_POST['valor_novo'])) {
            $valorAnterior = floatval($_POST['valor_anterior']);
            $valorNovo = floatval($_POST['valor_novo']);
            if ($valorAnterior > 0) {
                $percentual = (($valorNovo - $valorAnterior) / $valorAnterior) * 100;
            }
        }
        
        $data = [
            'numero_aditivo' => $_POST['numero_aditivo'],
            'tipo_aditivo' => $_POST['tipo_aditivo'],
            'data_aditivo' => $_POST['data_aditivo'],
            'descricao' => $_POST['descricao'],
            'justificativa' => $_POST['justificativa'] ?? null,
            'valor_anterior' => $_POST['valor_anterior'] ?? null,
            'valor_novo' => $_POST['valor_novo'] ?? null,
            'data_vigencia_anterior' => $_POST['data_vigencia_anterior'] ?? null,
            'data_vigencia_nova' => $_POST['data_vigencia_nova'] ?? null,
            'percentual_alteracao' => $percentual,
            'aprovado_por' => $_POST['aprovado_por'] ?? null,
            'data_aprovacao' => $_POST['data_aprovacao'] ?? null,
            'arquivo' => $arquivo,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->model->addAditivo($contratoId, $data);
            
            $this->model->addHistorico($contratoId, [
                'tipo_evento' => 'Aditivo Criado',
                'descricao' => "Aditivo {$data['numero_aditivo']} criado",
                'usuario_id' => $_SESSION['usuario_id'],
                'data_evento' => date('Y-m-d H:i:s'),
                'detalhes_json' => json_encode($data)
            ]);
            
            $_SESSION['sucesso'] = 'Aditivo adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId#aditivos");
        exit;
    }
    
    public function deleteAditivo($contratoId, $aditivoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId");
            exit;
        }
        
        try {
            $this->model->deleteAditivo($aditivoId);
            
            $this->model->addHistorico($contratoId, [
                'tipo_evento' => 'Aditivo Removido',
                'descricao' => 'Aditivo removido',
                'usuario_id' => $_SESSION['usuario_id'],
                'data_evento' => date('Y-m-d H:i:s'),
                'detalhes_json' => json_encode(['aditivo_id' => $aditivoId])
            ]);
            
            $_SESSION['sucesso'] = 'Aditivo removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId#aditivos");
        exit;
    }
    
    // VALORES POR PERÍODO
    public function addValorPeriodo($contratoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId");
            exit;
        }
        
        $data = [
            'mes_ano' => $_POST['mes_ano'],
            'valor_previsto' => $_POST['valor_previsto'] ?? null,
            'valor_realizado' => $_POST['valor_realizado'] ?? null,
            'valor_pago' => $_POST['valor_pago'] ?? null,
            'data_pagamento' => $_POST['data_pagamento'] ?? null,
            'numero_nf' => $_POST['numero_nf'] ?? null,
            'status_periodo' => $_POST['status_periodo'] ?? 'Pendente',
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->model->addValorPeriodo($contratoId, $data);
            $_SESSION['sucesso'] = 'Valor do período adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$contratoId#valores");
        exit;
    }
    
    // AJAX - BUSCAR CONTRATOS VENCENDO
    public function getVencendo() {
        header('Content-Type: application/json');
        
        $dias = $_GET['dias'] ?? 90;
        
        try {
            $contratos = $this->model->getVencendo($dias);
            echo json_encode(['success' => true, 'contratos' => $contratos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
    
    // FATURAMENTO DO CONTRATO
    public function faturamento($id) {
        $contrato = $this->model->findById($id);
        
        if (!$contrato) {
            $_SESSION['erro'] = 'Contrato não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/contratos');
            exit;
        }
        
        try {
            // Gerar relatório financeiro completo
            $relatorio = $this->contratoFinanceiro->gerarRelatorioCompleto($id);
            
            $contrato = $relatorio['contrato'];
            $faturamento = $relatorio['faturamento'];
            $historico_mensal = $relatorio['historico_mensal'];
            $inadimplencia = $relatorio['inadimplencia'];
            $projecao_receita = $relatorio['projecao_receita'];
            $faturas_pendentes = $relatorio['faturas_pendentes'];
            
            require __DIR__ . '/../views/contratos/faturamento.php';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao gerar relatório de faturamento: ' . $e->getMessage();
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id");
            exit;
        }
    }
    
    // GERAR FATURA RECORRENTE MANUALMENTE
    public function gerarFatura($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id/faturamento");
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id/faturamento");
            exit;
        }
        
        $mesReferencia = $_POST['mes_referencia'] ?? date('Y-m-01');
        
        try {
            $contaId = $this->contratoFinanceiro->gerarFaturaRecorrente($id, $mesReferencia);
            
            if ($contaId) {
                $_SESSION['sucesso'] = 'Fatura gerada com sucesso!';
                
                // Adicionar histórico ao contrato
                $this->model->addHistorico($id, [
                    'tipo_evento' => 'Faturamento',
                    'descricao' => 'Fatura recorrente gerada manualmente',
                    'usuario_id' => $_SESSION['usuario_id'],
                    'data_evento' => date('Y-m-d H:i:s'),
                    'detalhes_json' => json_encode([
                        'mes_referencia' => $mesReferencia,
                        'conta_receber_id' => $contaId
                    ])
                ]);
            } else {
                $_SESSION['erro'] = 'Não foi possível gerar a fatura. Verifique se o contrato permite faturamento automático e se já não existe fatura para o mês selecionado.';
            }
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao gerar fatura: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/contratos/$id/faturamento");
        exit;
    }
    
    // HELPERS PRIVADOS
    private function validateForm($data, $id = null) {
        $erros = [];
        
        if (empty($data['numero_contrato'])) {
            $erros[] = 'Número do contrato é obrigatório.';
        } else {
            if (!$this->model->validateUniqueNumero($data['numero_contrato'], $id)) {
                $erros[] = 'Número de contrato já cadastrado.';
            }
        }
        
        if (empty($data['empresa_tomadora_id'])) {
            $erros[] = 'Empresa Tomadora é obrigatória.';
        }
        
        if (empty($data['empresa_prestadora_id'])) {
            $erros[] = 'Empresa Prestadora é obrigatória.';
        }
        
        if (empty($data['tipo_contrato'])) {
            $erros[] = 'Tipo de contrato é obrigatório.';
        }
        
        if (empty($data['objeto_contrato'])) {
            $erros[] = 'Objeto do contrato é obrigatório.';
        }
        
        if (empty($data['data_assinatura'])) {
            $erros[] = 'Data de assinatura é obrigatória.';
        }
        
        if (empty($data['data_inicio_vigencia'])) {
            $erros[] = 'Data de início de vigência é obrigatória.';
        }
        
        if (empty($data['valor_total'])) {
            $erros[] = 'Valor total é obrigatório.';
        }
        
        // Validar datas
        if (!empty($data['data_inicio_vigencia']) && !empty($data['data_fim_vigencia'])) {
            if (strtotime($data['data_inicio_vigencia']) > strtotime($data['data_fim_vigencia'])) {
                $erros[] = 'Data de início não pode ser posterior à data de fim.';
            }
        }
        
        return $erros;
    }
    
    private function prepareData($post, $arquivo) {
        return [
            'numero_contrato' => $post['numero_contrato'],
            'empresa_tomadora_id' => $post['empresa_tomadora_id'],
            'empresa_prestadora_id' => $post['empresa_prestadora_id'],
            'tipo_contrato' => $post['tipo_contrato'],
            'objeto_contrato' => $post['objeto_contrato'],
            'descricao' => $post['descricao'] ?? null,
            'data_assinatura' => $post['data_assinatura'],
            'data_inicio_vigencia' => $post['data_inicio_vigencia'],
            'data_fim_vigencia' => $post['data_fim_vigencia'] ?? null,
            'prazo_meses' => $post['prazo_meses'] ?? null,
            'renovavel' => isset($post['renovavel']) ? 1 : 0,
            'prazo_renovacao_meses' => $post['prazo_renovacao_meses'] ?? null,
            'valor_total' => $post['valor_total'],
            'moeda' => $post['moeda'] ?? 'BRL',
            'forma_pagamento' => $post['forma_pagamento'] ?? null,
            'condicoes_pagamento' => $post['condicoes_pagamento'] ?? null,
            'prazo_pagamento_dias' => $post['prazo_pagamento_dias'] ?? null,
            'dia_vencimento' => $post['dia_vencimento'] ?? null,
            'periodicidade_faturamento' => $post['periodicidade_faturamento'] ?? null,
            'reajuste_previsto' => isset($post['reajuste_previsto']) ? 1 : 0,
            'indice_reajuste' => $post['indice_reajuste'] ?? null,
            'periodicidade_reajuste_meses' => $post['periodicidade_reajuste_meses'] ?? null,
            'percentual_multa_rescisao' => $post['percentual_multa_rescisao'] ?? null,
            'percentual_retencao_impostos' => $post['percentual_retencao_impostos'] ?? null,
            'exige_garantia' => isset($post['exige_garantia']) ? 1 : 0,
            'tipo_garantia' => $post['tipo_garantia'] ?? null,
            'valor_garantia' => $post['valor_garantia'] ?? null,
            'percentual_garantia' => $post['percentual_garantia'] ?? null,
            'vigencia_garantia' => $post['vigencia_garantia'] ?? null,
            'clausulas_especiais' => $post['clausulas_especiais'] ?? null,
            'penalidades' => $post['penalidades'] ?? null,
            'sla_atendimento_horas' => $post['sla_atendimento_horas'] ?? null,
            'permite_subcontratacao' => isset($post['permite_subcontratacao']) ? 1 : 0,
            'exige_seguro' => isset($post['exige_seguro']) ? 1 : 0,
            'valor_seguro_minimo' => $post['valor_seguro_minimo'] ?? null,
            'status' => $post['status'] ?? 'Rascunho',
            'observacoes' => $post['observacoes'] ?? null,
            'arquivo_contrato' => $arquivo
        ];
    }
    
    private function uploadContrato($file) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 15 * 1024 * 1024; // 15MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'erro' => 'Tipo de arquivo inválido. Use PDF, DOC ou DOCX.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'erro' => 'Arquivo muito grande. Máximo: 15MB.'];
        }
        
        $uploadDir = __DIR__ . '/../../public/uploads/contratos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'erro' => 'Erro ao fazer upload.'];
        }
        
        return ['success' => true, 'filename' => $filename];
    }
}
