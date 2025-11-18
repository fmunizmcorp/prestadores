<?php /* Cache-Buster: 2025-11-15 12:18:13 */ 
namespace App\Controllers;

use App\Models\Servico;

class ServicoController {
    private $model = null;
    
    /**
     * Get model (lazy instantiation)
     */
    private function getModel() {
        if ($this->model === null) {
            $this->model = new Servico();
        }
        return $this->model;
    }
    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }
    
    // LISTAGEM
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $search = $_GET['search'] ?? '';
        $ativo = $_GET['ativo'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $subcategoria = $_GET['subcategoria'] ?? '';
        $complexidade = $_GET['complexidade'] ?? '';
        
        $filtros = [];
        if ($search) $filtros['search'] = $search;
        if ($ativo !== '') $filtros['ativo'] = $ativo;
        if ($categoria) $filtros['categoria'] = $categoria;
        if ($subcategoria) $filtros['subcategoria'] = $subcategoria;
        if ($complexidade) $filtros['complexidade'] = $complexidade;
        
        $servicos = $this->getModel()->all($filtros, $page, $limit);
        $total = $this->getModel()->count($filtros);
        $totalPaginas = ceil($total / $limit);
        
        // Para filtros
        $categorias = $this->getModel()->getCategorias();
        $subcategorias = $this->getModel()->getSubcategorias();
        
        // Estatísticas
        $stats = [
            'total' => $this->getModel()->countTotal(),
            'ativos' => $this->getModel()->countAtivos()
        ];
        
        require __DIR__ . '/../Views/servicos/index.php';
    }
    
    // EXIBIR FORMULÁRIO DE CRIAÇÃO
    public function create() {
        $categorias = $this->getModel()->getCategorias();
        require __DIR__ . '/../Views/servicos/create.php';
    }
    
    // SALVAR NOVO SERVIÇO
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos/create');
            exit;
        }
        
        // Validar
        $erros = $this->validateForm($_POST);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos/create');
            exit;
        }
        
        $data = [
            'codigo' => strtoupper($_POST['codigo']),
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
            'categoria' => $_POST['categoria'],
            'subcategoria' => $_POST['subcategoria'] ?? null,
            'unidade_medida' => $_POST['unidade_medida'],
            'valor_referencia_min' => $_POST['valor_referencia_min'] ?? null,
            'valor_referencia_max' => $_POST['valor_referencia_max'] ?? null,
            'prazo_medio_dias' => $_POST['prazo_medio_dias'] ?? null,
            'complexidade' => $_POST['complexidade'],
            'requisitos' => $_POST['requisitos'] ?? null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $id = $this->getModel()->create($data);
            $_SESSION['sucesso'] = 'Serviço cadastrado com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cadastrar serviço: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos/create');
            exit;
        }
    }
    
    // EXIBIR DETALHES
    public function show($id) {
        $servico = $this->getModel()->findById($id);
        
        if (!$servico) {
            $_SESSION['erro'] = 'Serviço não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        $requisitos = $this->getModel()->getRequisitos($id);
        $valoresReferencia = $this->getModel()->getValoresReferencia($id);
        $servicosRelacionados = $this->getModel()->getServicosRelacionados($id);
        
        require __DIR__ . '/../Views/servicos/show.php';
    }
    
    // EXIBIR FORMULÁRIO DE EDIÇÃO
    public function edit($id) {
        $servico = $this->getModel()->findById($id);
        
        if (!$servico) {
            $_SESSION['erro'] = 'Serviço não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        $categorias = $this->getModel()->getCategorias();
        $subcategorias = $this->getModel()->getSubcategorias($servico['categoria']);
        
        require __DIR__ . '/../Views/servicos/edit.php';
    }
    
    // ATUALIZAR SERVIÇO
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$id/edit");
            exit;
        }
        
        $servico = $this->getModel()->findById($id);
        if (!$servico) {
            $_SESSION['erro'] = 'Serviço não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        // Validar
        $erros = $this->validateForm($_POST, $id);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$id/edit");
            exit;
        }
        
        $data = [
            'codigo' => strtoupper($_POST['codigo']),
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? null,
            'categoria' => $_POST['categoria'],
            'subcategoria' => $_POST['subcategoria'] ?? null,
            'unidade_medida' => $_POST['unidade_medida'],
            'valor_referencia_min' => $_POST['valor_referencia_min'] ?? null,
            'valor_referencia_max' => $_POST['valor_referencia_max'] ?? null,
            'prazo_medio_dias' => $_POST['prazo_medio_dias'] ?? null,
            'complexidade' => $_POST['complexidade'],
            'requisitos' => $_POST['requisitos'] ?? null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => $_POST['observacoes'] ?? null,
            'atualizado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->getModel()->update($id, $data);
            $_SESSION['sucesso'] = 'Serviço atualizado com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar serviço: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$id/edit");
            exit;
        }
    }
    
    // EXCLUIR
    public function destroy($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        $servico = $this->getModel()->findById($id);
        if (!$servico) {
            $_SESSION['erro'] = 'Serviço não encontrado.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        }
        
        try {
            $this->getModel()->delete($id);
            $_SESSION['sucesso'] = 'Serviço excluído com sucesso!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/servicos');
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao excluir serviço: ' . $e->getMessage();
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$id");
            exit;
        }
    }
    
    // REQUISITOS
    public function addRequisito($servicoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId");
            exit;
        }
        
        $erros = [];
        if (empty($_POST['tipo_requisito'])) $erros[] = 'Tipo de requisito é obrigatório.';
        if (empty($_POST['descricao'])) $erros[] = 'Descrição é obrigatória.';
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId#requisitos");
            exit;
        }
        
        $data = [
            'tipo_requisito' => $_POST['tipo_requisito'],
            'descricao' => $_POST['descricao'],
            'obrigatorio' => isset($_POST['obrigatorio']) ? 1 : 0,
            'documento_requerido' => $_POST['documento_requerido'] ?? null,
            'prazo_dias' => $_POST['prazo_dias'] ?? null,
            'ordem' => $_POST['ordem'] ?? 0,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->getModel()->addRequisito($servicoId, $data);
            $_SESSION['sucesso'] = 'Requisito adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId#requisitos");
        exit;
    }
    
    public function deleteRequisito($servicoId, $requisitoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId");
            exit;
        }
        
        try {
            $this->getModel()->deleteRequisito($requisitoId);
            $_SESSION['sucesso'] = 'Requisito removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId#requisitos");
        exit;
    }
    
    // VALORES DE REFERÊNCIA
    public function addValorReferencia($servicoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId");
            exit;
        }
        
        $erros = [];
        if (empty($_POST['valor_minimo']) && empty($_POST['valor_maximo'])) {
            $erros[] = 'Informe ao menos o valor mínimo ou máximo.';
        }
        if (empty($_POST['vigencia_inicio'])) {
            $erros[] = 'Data de início de vigência é obrigatória.';
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId#valores");
            exit;
        }
        
        $data = [
            'empresa_tomadora_id' => $_POST['empresa_tomadora_id'] ?? null,
            'valor_minimo' => $_POST['valor_minimo'] ?? null,
            'valor_maximo' => $_POST['valor_maximo'] ?? null,
            'valor_medio' => $_POST['valor_medio'] ?? null,
            'moeda' => $_POST['moeda'] ?? 'BRL',
            'unidade' => $_POST['unidade'],
            'vigencia_inicio' => $_POST['vigencia_inicio'],
            'vigencia_fim' => $_POST['vigencia_fim'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->getModel()->addValorReferencia($servicoId, $data);
            $_SESSION['sucesso'] = 'Valor de referência adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId#valores");
        exit;
    }
    
    public function deleteValorReferencia($servicoId, $valorId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId");
            exit;
        }
        
        try {
            $this->getModel()->deleteValorReferencia($valorId);
            $_SESSION['sucesso'] = 'Valor de referência removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/servicos/$servicoId#valores");
        exit;
    }
    
    // AJAX - BUSCAR SUBCATEGORIAS
    public function getSubcategorias() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['categoria'])) {
            echo json_encode(['success' => false, 'erro' => 'Categoria não informada']);
            exit;
        }
        
        try {
            $subcategorias = $this->getModel()->getSubcategorias($_GET['categoria']);
            echo json_encode(['success' => true, 'subcategorias' => $subcategorias]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
    
    // AJAX - BUSCAR VALOR VIGENTE
    public function getValorVigente() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['servico_id'])) {
            echo json_encode(['success' => false, 'erro' => 'Serviço não informado']);
            exit;
        }
        
        $servicoId = $_GET['servico_id'];
        $empresaTomadoraId = $_GET['empresa_tomadora_id'] ?? null;
        
        try {
            $valor = $this->getModel()->getValorReferenciaVigente($servicoId, $empresaTomadoraId);
            echo json_encode(['success' => true, 'valor' => $valor]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
    
    // VALIDAÇÃO
    private function validateForm($data, $id = null) {
        $erros = [];
        
        if (empty($data['codigo'])) {
            $erros[] = 'Código é obrigatório.';
        } else {
            $codigo = strtoupper($data['codigo']);
            if (!$this->getModel()->validateUniqueCodigo($codigo, $id)) {
                $erros[] = 'Código já cadastrado.';
            }
        }
        
        if (empty($data['nome'])) {
            $erros[] = 'Nome é obrigatório.';
        }
        
        if (empty($data['categoria'])) {
            $erros[] = 'Categoria é obrigatória.';
        }
        
        if (empty($data['unidade_medida'])) {
            $erros[] = 'Unidade de medida é obrigatória.';
        }
        
        if (empty($data['complexidade'])) {
            $erros[] = 'Complexidade é obrigatória.';
        }
        
        // Validar valores
        if (!empty($data['valor_referencia_min']) && !empty($data['valor_referencia_max'])) {
            if (floatval($data['valor_referencia_min']) > floatval($data['valor_referencia_max'])) {
                $erros[] = 'Valor mínimo não pode ser maior que o valor máximo.';
            }
        }
        
        return $erros;
    }
}
