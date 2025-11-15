<?php /* Cache-Buster: 2025-11-15 12:18:13 */ 
namespace App\Controllers;

use App\Models\ServicoValor;
use App\Models\Contrato;
use App\Models\Servico;

/**
 * Controller ServicoValor
 * Gerencia valores de serviços por período com validação de sobreposição
 */
class ServicoValorController {
    private $model = null;
    
    /**
     * Get model (lazy instantiation)
     */
    private function getModel() {
        if ($this->model === null) {
            $this->model = new ServicoValor();
        }
        return $this->model;
    }
    private $contratoModel;
    private $servicoModel;
    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=login');
            exit;
        }
        $this->contratoModel = new Contrato();
        $this->servicoModel = new Servico();
    }
    
    /**
     * LISTAGEM - Timeline de valores por contrato
     */
    public function index() {
        $contratoId = $_GET['contrato_id'] ?? null;
        $servicoId = $_GET['servico_id'] ?? null;
        
        if (!$contratoId) {
            $_SESSION['erro'] = 'Contrato não especificado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        $contrato = $this->contratoModel->findById($contratoId);
        if (!$contrato) {
            $_SESSION['erro'] = 'Contrato não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        // Buscar todos os valores do contrato
        $valores = $servicoId 
            ? $this->getModel()->getByContratoServico($contratoId, $servicoId)
            : $this->getModel()->getByContrato($contratoId);
        
        // Buscar serviços disponíveis
        $servicos = $this->servicoModel->getAtivos();
        
        require __DIR__ . '/../Views/servico-valores/index.php';
    }
    
    /**
     * FORMULÁRIO DE CRIAÇÃO
     */
    public function create() {
        $contratoId = $_GET['contrato_id'] ?? null;
        $servicoId = $_GET['servico_id'] ?? null;
        
        if (!$contratoId) {
            $_SESSION['erro'] = 'Contrato não especificado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        $contrato = $this->contratoModel->findById($contratoId);
        if (!$contrato) {
            $_SESSION['erro'] = 'Contrato não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        $servicos = $this->servicoModel->getAtivos();
        
        require __DIR__ . '/../Views/servico-valores/create.php';
    }
    
    /**
     * SALVAR NOVO VALOR
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&action=create&contrato_id=' . ($_POST['contrato_id'] ?? ''));
            exit;
        }
        
        // Validar
        $erros = $this->validateForm($_POST);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&action=create&contrato_id=' . $_POST['contrato_id']);
            exit;
        }
        
        $data = $this->prepareData($_POST);
        
        try {
            $id = $this->getModel()->create($data);
            
            // Registrar histórico no contrato
            $this->contratoModel->addHistorico($data['contrato_id'], [
                'tipo_evento' => 'Valor Período Criado',
                'descricao' => 'Novo valor de serviço cadastrado',
                'usuario_id' => $_SESSION['usuario_id'],
                'detalhes_json' => json_encode([
                    'servico_id' => $data['servico_id'],
                    'data_inicio' => $data['data_inicio'],
                    'data_fim' => $data['data_fim'],
                    'valor_base' => $data['valor_base']
                ])
            ]);
            
            $_SESSION['sucesso'] = 'Valor de serviço cadastrado com sucesso!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&contrato_id=' . $data['contrato_id']);
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cadastrar valor: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&action=create&contrato_id=' . $_POST['contrato_id']);
            exit;
        }
    }
    
    /**
     * EXIBIR DETALHES
     */
    public function show($id) {
        $valor = $this->getModel()->findById($id);
        
        if (!$valor) {
            $_SESSION['erro'] = 'Valor não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        $contrato = $this->contratoModel->findById($valor['contrato_id']);
        $servico = $this->servicoModel->findById($valor['servico_id']);
        
        // Buscar histórico de valores do mesmo serviço
        $historico = $this->getModel()->getHistoricoValores($valor['contrato_id'], $valor['servico_id']);
        
        require __DIR__ . '/../Views/servico-valores/show.php';
    }
    
    /**
     * FORMULÁRIO DE EDIÇÃO
     */
    public function edit($id) {
        $valor = $this->getModel()->findById($id);
        
        if (!$valor) {
            $_SESSION['erro'] = 'Valor não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        $contrato = $this->contratoModel->findById($valor['contrato_id']);
        $servicos = $this->servicoModel->getAtivos();
        
        require __DIR__ . '/../Views/servico-valores/edit.php';
    }
    
    /**
     * ATUALIZAR VALOR
     * 
     * IMPORTANTE: Este método NÃO altera o registro existente.
     * Ele fecha o período atual e cria um novo período começando na data especificada.
     * Isso mantém o histórico imutável.
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&action=edit&id=' . $id);
            exit;
        }
        
        $valor = $this->getModel()->findById($id);
        if (!$valor) {
            $_SESSION['erro'] = 'Valor não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        $erros = $this->validateForm($_POST, $id);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&action=edit&id=' . $id);
            exit;
        }
        
        $data = $this->prepareData($_POST);
        $data['updated_by'] = $_SESSION['usuario_id'];
        
        try {
            // Este método fecha o período anterior e cria um novo
            $this->getModel()->atualizar($id, $data);
            
            // Registrar histórico
            $this->contratoModel->addHistorico($valor['contrato_id'], [
                'tipo_evento' => 'Valor Atualizado',
                'descricao' => 'Valor de serviço atualizado (novo período criado)',
                'usuario_id' => $_SESSION['usuario_id'],
                'detalhes_json' => json_encode([
                    'valor_anterior_id' => $id,
                    'nova_data_inicio' => $data['data_inicio'],
                    'novo_valor_base' => $data['valor_base']
                ])
            ]);
            
            $_SESSION['sucesso'] = 'Valor atualizado com sucesso! Um novo período foi criado mantendo o histórico.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&contrato_id=' . $valor['contrato_id']);
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar valor: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&action=edit&id=' . $id);
            exit;
        }
    }
    
    /**
     * EXCLUIR (SOFT DELETE)
     */
    public function destroy($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        $valor = $this->getModel()->findById($id);
        if (!$valor) {
            $_SESSION['erro'] = 'Valor não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=contratos');
            exit;
        }
        
        try {
            $this->getModel()->delete($id);
            
            // Registrar histórico
            $this->contratoModel->addHistorico($valor['contrato_id'], [
                'tipo_evento' => 'Valor Removido',
                'descricao' => 'Valor de serviço removido',
                'usuario_id' => $_SESSION['usuario_id'],
                'detalhes_json' => json_encode(['valor_id' => $id])
            ]);
            
            $_SESSION['sucesso'] = 'Valor excluído com sucesso!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&contrato_id=' . $valor['contrato_id']);
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao excluir valor: ' . $e->getMessage();
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=servico-valores&contrato_id=' . $valor['contrato_id']);
            exit;
        }
    }
    
    /**
     * AJAX - Buscar valor vigente
     */
    public function getValorVigente() {
        header('Content-Type: application/json');
        
        $contratoId = $_GET['contrato_id'] ?? null;
        $servicoId = $_GET['servico_id'] ?? null;
        $data = $_GET['data'] ?? date('Y-m-d');
        
        if (!$contratoId || !$servicoId) {
            echo json_encode(['success' => false, 'erro' => 'Parâmetros inválidos']);
            exit;
        }
        
        try {
            $valor = $this->getModel()->getValorVigente($contratoId, $servicoId, $data);
            
            if ($valor) {
                echo json_encode(['success' => true, 'valor' => $valor]);
            } else {
                echo json_encode(['success' => false, 'erro' => 'Nenhum valor vigente encontrado para esta data']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * AJAX - Verificar sobreposição de períodos
     */
    public function verificarSobreposicao() {
        header('Content-Type: application/json');
        
        $contratoId = $_POST['contrato_id'] ?? null;
        $servicoId = $_POST['servico_id'] ?? null;
        $dataInicio = $_POST['data_inicio'] ?? null;
        $dataFim = $_POST['data_fim'] ?? null;
        $exceptId = $_POST['except_id'] ?? null;
        
        if (!$contratoId || !$servicoId || !$dataInicio) {
            echo json_encode(['success' => false, 'erro' => 'Parâmetros inválidos']);
            exit;
        }
        
        try {
            $sobreposicao = $this->getModel()->verificarSobreposicao(
                $contratoId,
                $servicoId,
                $dataInicio,
                $dataFim,
                $exceptId
            );
            
            if ($sobreposicao) {
                echo json_encode([
                    'success' => false,
                    'sobrepoe' => true,
                    'mensagem' => 'Este período se sobrepõe a um período já cadastrado.',
                    'periodo_existente' => $sobreposicao
                ]);
            } else {
                echo json_encode(['success' => true, 'sobrepoe' => false]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * HELPERS PRIVADOS
     */
    private function validateForm($data, $id = null) {
        $erros = [];
        
        if (empty($data['contrato_id'])) {
            $erros[] = 'Contrato é obrigatório.';
        }
        
        if (empty($data['servico_id'])) {
            $erros[] = 'Serviço é obrigatório.';
        }
        
        if (empty($data['data_inicio'])) {
            $erros[] = 'Data de início é obrigatória.';
        }
        
        if (empty($data['valor_base'])) {
            $erros[] = 'Valor base é obrigatório.';
        }
        
        if (empty($data['tipo_remuneracao'])) {
            $erros[] = 'Tipo de remuneração é obrigatório.';
        }
        
        // Validar datas
        if (!empty($data['data_inicio']) && !empty($data['data_fim'])) {
            if (strtotime($data['data_inicio']) > strtotime($data['data_fim'])) {
                $erros[] = 'Data de início não pode ser posterior à data de fim.';
            }
        }
        
        // Verificar sobreposição de períodos
        if (!empty($data['contrato_id']) && !empty($data['servico_id']) && !empty($data['data_inicio'])) {
            $sobreposicao = $this->getModel()->verificarSobreposicao(
                $data['contrato_id'],
                $data['servico_id'],
                $data['data_inicio'],
                $data['data_fim'] ?? null,
                $id
            );
            
            if ($sobreposicao) {
                $erros[] = 'Este período se sobrepõe a um período já cadastrado entre ' . 
                          date('d/m/Y', strtotime($sobreposicao['data_inicio'])) . ' e ' . 
                          ($sobreposicao['data_fim'] ? date('d/m/Y', strtotime($sobreposicao['data_fim'])) : 'indeterminado');
            }
        }
        
        return $erros;
    }
    
    private function prepareData($post) {
        return [
            'contrato_id' => $post['contrato_id'],
            'servico_id' => $post['servico_id'],
            'data_inicio' => $post['data_inicio'],
            'data_fim' => $post['data_fim'] ?? null,
            'tipo_remuneracao' => $post['tipo_remuneracao'],
            'valor_base' => $post['valor_base'],
            'valor_hora_extra' => $post['valor_hora_extra'] ?? null,
            'valor_hora_noturna' => $post['valor_hora_noturna'] ?? null,
            'valor_domingo_feriado' => $post['valor_domingo_feriado'] ?? null,
            'adicional_periculosidade' => $post['adicional_periculosidade'] ?? null,
            'adicional_insalubridade' => $post['adicional_insalubridade'] ?? null,
            'vale_transporte' => $post['vale_transporte'] ?? null,
            'vale_alimentacao' => $post['vale_alimentacao'] ?? null,
            'quantidade_minima' => $post['quantidade_minima'] ?? null,
            'quantidade_maxima' => $post['quantidade_maxima'] ?? null,
            'observacoes' => $post['observacoes'] ?? null,
            'ativo' => isset($post['ativo']) ? 1 : 1, // Sempre ativo ao criar
            'created_by' => $_SESSION['usuario_id']
        ];
    }
}
