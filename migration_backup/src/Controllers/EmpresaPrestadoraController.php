<?php

namespace App\Controllers;

use App\Models\EmpresaPrestadora;
use App\Models\Servico;

class EmpresaPrestadoraController {
    private $model;
    private $servicoModel;
    
    public function __construct() {
        // Verificar se usuário está autenticado
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        
        $this->model = new EmpresaPrestadora();
        $this->servicoModel = new Servico();
    }
    
    // LISTAGEM
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $search = $_GET['search'] ?? '';
        $ativo = $_GET['ativo'] ?? '';
        $cidade = $_GET['cidade'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $categoria_principal = $_GET['categoria_principal'] ?? '';
        $area_atuacao = $_GET['area_atuacao'] ?? '';
        
        $filtros = [];
        if ($search) $filtros['search'] = $search;
        if ($ativo !== '') $filtros['ativo'] = $ativo;
        if ($cidade) $filtros['cidade'] = $cidade;
        if ($estado) $filtros['estado'] = $estado;
        if ($categoria_principal) $filtros['categoria_principal'] = $categoria_principal;
        if ($area_atuacao) $filtros['area_atuacao'] = $area_atuacao;
        
        $empresas = $this->model->all($filtros, $page, $limit);
        $total = $this->model->count($filtros);
        $totalPaginas = ceil($total / $limit);
        
        // Estatísticas
        $stats = [
            'total' => $this->model->countTotal(),
            'ativas' => $this->model->countAtivas()
        ];
        
        require __DIR__ . '/../Views/empresas-prestadoras/index.php';
    }
    
    // EXIBIR FORMULÁRIO DE CRIAÇÃO
    public function create() {
        $servicos = $this->servicoModel->getAtivos();
        require __DIR__ . '/../Views/empresas-prestadoras/create.php';
    }
    
    // SALVAR NOVA EMPRESA
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        // Validar CSRF Token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras/create');
            exit;
        }
        
        // Validar campos obrigatórios
        $erros = $this->validateForm($_POST);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras/create');
            exit;
        }
        
        // Upload de logo
        $logo = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadLogo($_FILES['logo']);
            if ($upload['success']) {
                $logo = $upload['filename'];
            }
        }
        
        // Preparar dados
        $data = $this->prepareData($_POST, $logo);
        $data['criado_por'] = $_SESSION['usuario_id'];
        
        try {
            $id = $this->model->create($data);
            
            // Associar serviços
            if (!empty($_POST['servicos'])) {
                foreach ($_POST['servicos'] as $servicoId) {
                    $this->model->addServico($id, $servicoId);
                }
            }
            
            $_SESSION['sucesso'] = 'Empresa Prestadora cadastrada com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cadastrar empresa: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras/create');
            exit;
        }
    }
    
    // EXIBIR DETALHES
    public function show($id) {
        $empresa = $this->model->findById($id);
        
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        $representantes = $this->model->getRepresentantes($id);
        $documentos = $this->model->getDocumentos($id);
        $servicos = $this->model->getServicos($id);
        $totalContratos = $this->model->getContratosPorEmpresa($id);
        
        require __DIR__ . '/../Views/empresas-prestadoras/show.php';
    }
    
    // EXIBIR FORMULÁRIO DE EDIÇÃO
    public function edit($id) {
        $empresa = $this->model->findById($id);
        
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        $todosServicos = $this->servicoModel->getAtivos();
        $servicosEmpresa = $this->model->getServicos($id);
        $servicosIds = array_column($servicosEmpresa, 'id');
        
        require __DIR__ . '/../Views/empresas-prestadoras/edit.php';
    }
    
    // ATUALIZAR EMPRESA
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        // Validar CSRF Token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$id/edit");
            exit;
        }
        
        $empresa = $this->model->findById($id);
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        // Validar
        $erros = $this->validateForm($_POST, $id);
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$id/edit");
            exit;
        }
        
        // Upload de logo
        $logo = $empresa['logo'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadLogo($_FILES['logo']);
            if ($upload['success']) {
                if ($logo && file_exists(__DIR__ . '/../../public/uploads/logos/' . $logo)) {
                    unlink(__DIR__ . '/../../public/uploads/logos/' . $logo);
                }
                $logo = $upload['filename'];
            }
        }
        
        // Preparar dados
        $data = $this->prepareData($_POST, $logo);
        $data['atualizado_por'] = $_SESSION['usuario_id'];
        
        try {
            $this->model->update($id, $data);
            
            // Atualizar serviços - remover todos e adicionar novamente
            $servicosAtuais = $this->model->getServicos($id);
            foreach ($servicosAtuais as $servico) {
                $this->model->removeServico($id, $servico['id']);
            }
            
            if (!empty($_POST['servicos'])) {
                foreach ($_POST['servicos'] as $servicoId) {
                    $this->model->addServico($id, $servicoId);
                }
            }
            
            $_SESSION['sucesso'] = 'Empresa atualizada com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar empresa: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$id/edit");
            exit;
        }
    }
    
    // EXCLUIR
    public function destroy($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        $empresa = $this->model->findById($id);
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        }
        
        try {
            $this->model->delete($id);
            $_SESSION['sucesso'] = 'Empresa excluída com sucesso!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-prestadoras');
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao excluir empresa: ' . $e->getMessage();
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$id");
            exit;
        }
    }
    
    // REPRESENTANTES
    public function addRepresentante($empresaId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId");
            exit;
        }
        
        $erros = [];
        if (empty($_POST['nome'])) $erros[] = 'Nome é obrigatório.';
        if (empty($_POST['email'])) $erros[] = 'E-mail é obrigatório.';
        elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId#representantes");
            exit;
        }
        
        $data = [
            'nome' => $_POST['nome'],
            'cpf' => preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? ''),
            'cargo' => $_POST['cargo'] ?? null,
            'email' => $_POST['email'],
            'telefone' => preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''),
            'celular' => preg_replace('/[^0-9]/', '', $_POST['celular'] ?? ''),
            'representante_legal' => isset($_POST['representante_legal']) ? 1 : 0,
            'representante_comercial' => isset($_POST['representante_comercial']) ? 1 : 0,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => $_POST['observacoes'] ?? null,
            'foto' => null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->model->addRepresentante($empresaId, $data);
            $_SESSION['sucesso'] = 'Representante adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId#representantes");
        exit;
    }
    
    public function deleteRepresentante($empresaId, $representanteId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId");
            exit;
        }
        
        try {
            $this->model->deleteRepresentante($representanteId);
            $_SESSION['sucesso'] = 'Representante removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId#representantes");
        exit;
    }
    
    // DOCUMENTOS
    public function addDocumento($empresaId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId");
            exit;
        }
        
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erro'] = 'Arquivo é obrigatório.';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId#documentos");
            exit;
        }
        
        $upload = $this->uploadDocumento($_FILES['arquivo']);
        if (!$upload['success']) {
            $_SESSION['erro'] = $upload['erro'];
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId#documentos");
            exit;
        }
        
        $data = [
            'tipo_documento' => $_POST['tipo_documento'],
            'nome_documento' => $_POST['nome_documento'],
            'descricao' => $_POST['descricao'] ?? null,
            'arquivo' => $upload['filename'],
            'tamanho_bytes' => $upload['size'],
            'mime_type' => $upload['mime_type'],
            'data_emissao' => $_POST['data_emissao'] ?? null,
            'data_validade' => $_POST['data_validade'] ?? null,
            'alertar_dias_antes' => $_POST['alertar_dias_antes'] ?? 30,
            'status' => 'Ativo',
            'upload_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->model->addDocumento($empresaId, $data);
            $_SESSION['sucesso'] = 'Documento adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId#documentos");
        exit;
    }
    
    public function deleteDocumento($empresaId, $documentoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId");
            exit;
        }
        
        try {
            $this->model->deleteDocumento($documentoId);
            $_SESSION['sucesso'] = 'Documento removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-prestadoras/$empresaId#documentos");
        exit;
    }
    
    // BUSCAR CEP
    public function buscarCep() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['cep'])) {
            echo json_encode(['success' => false, 'erro' => 'CEP não informado']);
            exit;
        }
        
        $cep = preg_replace('/[^0-9]/', '', $_GET['cep']);
        
        if (strlen($cep) != 8) {
            echo json_encode(['success' => false, 'erro' => 'CEP inválido']);
            exit;
        }
        
        $url = "https://viacep.com.br/ws/$cep/json/";
        $response = @file_get_contents($url);
        
        if ($response === false) {
            echo json_encode(['success' => false, 'erro' => 'Erro ao buscar CEP']);
            exit;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['erro'])) {
            echo json_encode(['success' => false, 'erro' => 'CEP não encontrado']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'logradouro' => $data['logradouro'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'cidade' => $data['localidade'] ?? '',
            'estado' => $data['uf'] ?? ''
        ]);
        exit;
    }
    
    // HELPERS PRIVADOS
    private function validateForm($data, $id = null) {
        $erros = [];
        
        if (empty($data['razao_social'])) {
            $erros[] = 'Razão Social é obrigatória.';
        }
        
        if (empty($data['nome_fantasia'])) {
            $erros[] = 'Nome Fantasia é obrigatório.';
        }
        
        if (empty($data['cnpj'])) {
            $erros[] = 'CNPJ é obrigatório.';
        } else {
            $cnpj = preg_replace('/[^0-9]/', '', $data['cnpj']);
            if (!$this->model->validateCnpj($cnpj)) {
                $erros[] = 'CNPJ inválido.';
            }
            
            if (!$this->model->validateUniqueCnpj($cnpj, $id)) {
                $erros[] = 'CNPJ já cadastrado.';
            }
        }
        
        if (empty($data['email_principal'])) {
            $erros[] = 'E-mail principal é obrigatório.';
        } elseif (!filter_var($data['email_principal'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail principal inválido.';
        }
        
        return $erros;
    }
    
    private function prepareData($post, $logo) {
        return [
            'razao_social' => $post['razao_social'],
            'nome_fantasia' => $post['nome_fantasia'],
            'cnpj' => preg_replace('/[^0-9]/', '', $post['cnpj']),
            'inscricao_estadual' => $post['inscricao_estadual'] ?? null,
            'inscricao_municipal' => $post['inscricao_municipal'] ?? null,
            'cep' => preg_replace('/[^0-9]/', '', $post['cep'] ?? ''),
            'logradouro' => $post['logradouro'] ?? null,
            'numero' => $post['numero'] ?? null,
            'complemento' => $post['complemento'] ?? null,
            'bairro' => $post['bairro'] ?? null,
            'cidade' => $post['cidade'] ?? null,
            'estado' => $post['estado'] ?? null,
            'pais' => $post['pais'] ?? 'Brasil',
            'email_principal' => $post['email_principal'],
            'telefone_principal' => preg_replace('/[^0-9]/', '', $post['telefone_principal'] ?? ''),
            'telefone_secundario' => preg_replace('/[^0-9]/', '', $post['telefone_secundario'] ?? ''),
            'celular' => preg_replace('/[^0-9]/', '', $post['celular'] ?? ''),
            'whatsapp' => preg_replace('/[^0-9]/', '', $post['whatsapp'] ?? ''),
            'email_financeiro' => $post['email_financeiro'] ?? null,
            'site' => $post['site'] ?? null,
            'banco' => $post['banco'] ?? null,
            'agencia' => $post['agencia'] ?? null,
            'conta' => $post['conta'] ?? null,
            'tipo_conta' => $post['tipo_conta'] ?? null,
            'pix_tipo' => $post['pix_tipo'] ?? null,
            'pix_chave' => $post['pix_chave'] ?? null,
            'logo' => $logo,
            'categoria_principal' => $post['categoria_principal'] ?? null,
            'area_atuacao' => $post['area_atuacao'] ?? null,
            'numero_funcionarios' => $post['numero_funcionarios'] ?? null,
            'ano_fundacao' => $post['ano_fundacao'] ?? null,
            'certificacoes' => $post['certificacoes'] ?? null,
            'ativo' => isset($post['ativo']) ? 1 : 0,
            'observacoes' => $post['observacoes'] ?? null
        ];
    }
    
    private function uploadLogo($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'erro' => 'Tipo de arquivo inválido.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'erro' => 'Arquivo muito grande. Máximo: 2MB.'];
        }
        
        $uploadDir = __DIR__ . '/../../public/uploads/logos/';
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
    
    private function uploadDocumento($file) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'erro' => 'Tipo de arquivo inválido.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'erro' => 'Arquivo muito grande. Máximo: 10MB.'];
        }
        
        $uploadDir = __DIR__ . '/../../public/uploads/documentos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'erro' => 'Erro ao fazer upload.'];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'size' => $file['size'],
            'mime_type' => $file['type']
        ];
    }
}
