<?php

namespace App\Controllers;

use App\Models\EmpresaTomadora;

class EmpresaTomadoraController {
    private $model;
    
    public function __construct() {
        // Verificar se usuário está autenticado
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        
        $this->model = new EmpresaTomadora();
    }
    
    // LISTAGEM
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $search = $_GET['search'] ?? '';
        $ativo = $_GET['ativo'] ?? '';
        $cidade = $_GET['cidade'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        $filtros = [];
        if ($search) $filtros['search'] = $search;
        if ($ativo !== '') $filtros['ativo'] = $ativo;
        if ($cidade) $filtros['cidade'] = $cidade;
        if ($estado) $filtros['estado'] = $estado;
        
        $empresas = $this->model->all($filtros, $page, $limit);
        $total = $this->model->count($filtros);
        $totalPaginas = ceil($total / $limit);
        
        // Estatísticas para o dashboard
        $stats = [
            'total' => $this->model->countTotal(),
            'ativas' => $this->model->countAtivas()
        ];
        
        require __DIR__ . '/../views/empresas-tomadoras/index.php';
    }
    
    // EXIBIR FORMULÁRIO DE CRIAÇÃO
    public function create() {
        require __DIR__ . '/../views/empresas-tomadoras/create.php';
    }
    
    // SALVAR NOVA EMPRESA
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        // Validar CSRF Token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras/create');
            exit;
        }
        
        // Validar campos obrigatórios
        $erros = [];
        
        if (empty($_POST['razao_social'])) {
            $erros[] = 'Razão Social é obrigatória.';
        }
        
        if (empty($_POST['nome_fantasia'])) {
            $erros[] = 'Nome Fantasia é obrigatório.';
        }
        
        if (empty($_POST['cnpj'])) {
            $erros[] = 'CNPJ é obrigatório.';
        } else {
            // Validar formato do CNPJ
            $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
            if (!$this->model->validateCnpj($cnpj)) {
                $erros[] = 'CNPJ inválido.';
            }
            
            // Validar unicidade do CNPJ
            if (!$this->model->validateUniqueCnpj($cnpj)) {
                $erros[] = 'CNPJ já cadastrado.';
            }
        }
        
        if (empty($_POST['email_principal'])) {
            $erros[] = 'E-mail principal é obrigatório.';
        } elseif (!filter_var($_POST['email_principal'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail principal inválido.';
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras/create');
            exit;
        }
        
        // Upload de logo (se houver)
        $logo = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadLogo($_FILES['logo']);
            if ($upload['success']) {
                $logo = $upload['filename'];
            } else {
                $_SESSION['erro'] = $upload['erro'];
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras/create');
                exit;
            }
        }
        
        // Preparar dados
        $data = [
            'razao_social' => $_POST['razao_social'],
            'nome_fantasia' => $_POST['nome_fantasia'],
            'cnpj' => preg_replace('/[^0-9]/', '', $_POST['cnpj']),
            'inscricao_estadual' => $_POST['inscricao_estadual'] ?? null,
            'inscricao_municipal' => $_POST['inscricao_municipal'] ?? null,
            'cep' => preg_replace('/[^0-9]/', '', $_POST['cep'] ?? ''),
            'logradouro' => $_POST['logradouro'] ?? null,
            'numero' => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro' => $_POST['bairro'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'estado' => $_POST['estado'] ?? null,
            'email_principal' => $_POST['email_principal'],
            'telefone_principal' => preg_replace('/[^0-9]/', '', $_POST['telefone_principal'] ?? ''),
            'telefone_secundario' => preg_replace('/[^0-9]/', '', $_POST['telefone_secundario'] ?? ''),
            'celular' => preg_replace('/[^0-9]/', '', $_POST['celular'] ?? ''),
            'whatsapp' => preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? ''),
            'email_financeiro' => $_POST['email_financeiro'] ?? null,
            'email_projetos' => $_POST['email_projetos'] ?? null,
            'site' => $_POST['site'] ?? null,
            'dia_fechamento' => $_POST['dia_fechamento'] ?? null,
            'dia_pagamento' => $_POST['dia_pagamento'] ?? null,
            'forma_pagamento_preferencial' => $_POST['forma_pagamento_preferencial'] ?? null,
            'banco' => $_POST['banco'] ?? null,
            'agencia' => $_POST['agencia'] ?? null,
            'conta' => $_POST['conta'] ?? null,
            'tipo_conta' => $_POST['tipo_conta'] ?? null,
            'logo' => $logo,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $id = $this->model->create($data);
            $_SESSION['sucesso'] = 'Empresa Tomadora cadastrada com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao cadastrar empresa: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras/create');
            exit;
        }
    }
    
    // EXIBIR DETALHES DA EMPRESA
    public function show($id) {
        $empresa = $this->model->findById($id);
        
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        // Buscar responsáveis
        $responsaveis = $this->model->getResponsaveis($id);
        
        // Buscar documentos
        $documentos = $this->model->getDocumentos($id);
        
        // Buscar contratos (se existir)
        $totalContratos = method_exists($this->model, 'getContratosPorEmpresa') 
            ? $this->model->getContratosPorEmpresa($id) 
            : 0;
        
        // Buscar projetos (se existir)
        $totalProjetos = method_exists($this->model, 'getProjetosPorEmpresa') 
            ? $this->model->getProjetosPorEmpresa($id) 
            : 0;
        
        require __DIR__ . '/../views/empresas-tomadoras/show.php';
    }
    
    // EXIBIR FORMULÁRIO DE EDIÇÃO
    public function edit($id) {
        $empresa = $this->model->findById($id);
        
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        require __DIR__ . '/../views/empresas-tomadoras/edit.php';
    }
    
    // ATUALIZAR EMPRESA
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        // Validar CSRF Token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$id/edit");
            exit;
        }
        
        $empresa = $this->model->findById($id);
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        // Validar campos obrigatórios
        $erros = [];
        
        if (empty($_POST['razao_social'])) {
            $erros[] = 'Razão Social é obrigatória.';
        }
        
        if (empty($_POST['nome_fantasia'])) {
            $erros[] = 'Nome Fantasia é obrigatório.';
        }
        
        if (empty($_POST['cnpj'])) {
            $erros[] = 'CNPJ é obrigatório.';
        } else {
            $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
            if (!$this->model->validateCnpj($cnpj)) {
                $erros[] = 'CNPJ inválido.';
            }
            
            if (!$this->model->validateUniqueCnpj($cnpj, $id)) {
                $erros[] = 'CNPJ já cadastrado para outra empresa.';
            }
        }
        
        if (empty($_POST['email_principal'])) {
            $erros[] = 'E-mail principal é obrigatório.';
        } elseif (!filter_var($_POST['email_principal'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail principal inválido.';
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$id/edit");
            exit;
        }
        
        // Upload de logo (se houver)
        $logo = $empresa['logo'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadLogo($_FILES['logo']);
            if ($upload['success']) {
                // Remover logo antiga
                if ($logo && file_exists(__DIR__ . '/../../public/uploads/logos/' . $logo)) {
                    unlink(__DIR__ . '/../../public/uploads/logos/' . $logo);
                }
                $logo = $upload['filename'];
            }
        }
        
        // Preparar dados
        $data = [
            'razao_social' => $_POST['razao_social'],
            'nome_fantasia' => $_POST['nome_fantasia'],
            'cnpj' => preg_replace('/[^0-9]/', '', $_POST['cnpj']),
            'inscricao_estadual' => $_POST['inscricao_estadual'] ?? null,
            'inscricao_municipal' => $_POST['inscricao_municipal'] ?? null,
            'cep' => preg_replace('/[^0-9]/', '', $_POST['cep'] ?? ''),
            'logradouro' => $_POST['logradouro'] ?? null,
            'numero' => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro' => $_POST['bairro'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'estado' => $_POST['estado'] ?? null,
            'email_principal' => $_POST['email_principal'],
            'telefone_principal' => preg_replace('/[^0-9]/', '', $_POST['telefone_principal'] ?? ''),
            'telefone_secundario' => preg_replace('/[^0-9]/', '', $_POST['telefone_secundario'] ?? ''),
            'celular' => preg_replace('/[^0-9]/', '', $_POST['celular'] ?? ''),
            'whatsapp' => preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? ''),
            'email_financeiro' => $_POST['email_financeiro'] ?? null,
            'email_projetos' => $_POST['email_projetos'] ?? null,
            'site' => $_POST['site'] ?? null,
            'dia_fechamento' => $_POST['dia_fechamento'] ?? null,
            'dia_pagamento' => $_POST['dia_pagamento'] ?? null,
            'forma_pagamento_preferencial' => $_POST['forma_pagamento_preferencial'] ?? null,
            'banco' => $_POST['banco'] ?? null,
            'agencia' => $_POST['agencia'] ?? null,
            'conta' => $_POST['conta'] ?? null,
            'tipo_conta' => $_POST['tipo_conta'] ?? null,
            'logo' => $logo,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => $_POST['observacoes'] ?? null,
            'atualizado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->model->update($id, $data);
            $_SESSION['sucesso'] = 'Empresa atualizada com sucesso!';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$id");
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar empresa: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$id/edit");
            exit;
        }
    }
    
    // EXCLUIR EMPRESA (SOFT DELETE)
    public function destroy($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        // Validar CSRF Token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['erro'] = 'Token de segurança inválido.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        $empresa = $this->model->findById($id);
        if (!$empresa) {
            $_SESSION['erro'] = 'Empresa não encontrada.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        }
        
        try {
            $this->model->delete($id);
            $_SESSION['sucesso'] = 'Empresa excluída com sucesso!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/empresas-tomadoras');
            exit;
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao excluir empresa: ' . $e->getMessage();
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$id");
            exit;
        }
    }
    
    // RESPONSÁVEIS
    public function addResponsavel($empresaId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId");
            exit;
        }
        
        // Validações
        $erros = [];
        if (empty($_POST['nome'])) $erros[] = 'Nome é obrigatório.';
        if (empty($_POST['email'])) $erros[] = 'E-mail é obrigatório.';
        elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId#responsaveis");
            exit;
        }
        
        $data = [
            'nome' => $_POST['nome'],
            'cargo' => $_POST['cargo'] ?? null,
            'departamento' => $_POST['departamento'] ?? null,
            'email' => $_POST['email'],
            'telefone' => preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''),
            'celular' => preg_replace('/[^0-9]/', '', $_POST['celular'] ?? ''),
            'ramal' => $_POST['ramal'] ?? null,
            'responsavel_principal' => isset($_POST['responsavel_principal']) ? 1 : 0,
            'recebe_notificacoes' => isset($_POST['recebe_notificacoes']) ? 1 : 0,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => $_POST['observacoes'] ?? null,
            'foto' => null,
            'criado_por' => $_SESSION['usuario_id']
        ];
        
        try {
            $this->model->addResponsavel($empresaId, $data);
            $_SESSION['sucesso'] = 'Responsável adicionado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao adicionar responsável: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId#responsaveis");
        exit;
    }
    
    public function deleteResponsavel($empresaId, $responsavelId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId");
            exit;
        }
        
        try {
            $this->model->deleteResponsavel($responsavelId);
            $_SESSION['sucesso'] = 'Responsável removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao remover responsável: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId#responsaveis");
        exit;
    }
    
    // DOCUMENTOS
    public function addDocumento($empresaId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId");
            exit;
        }
        
        // Validar arquivo
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erro'] = 'Arquivo é obrigatório.';
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId#documentos");
            exit;
        }
        
        $upload = $this->uploadDocumento($_FILES['arquivo']);
        if (!$upload['success']) {
            $_SESSION['erro'] = $upload['erro'];
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId#documentos");
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
            $_SESSION['erro'] = 'Erro ao adicionar documento: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId#documentos");
        exit;
    }
    
    public function deleteDocumento($empresaId, $documentoId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId");
            exit;
        }
        
        try {
            $this->model->deleteDocumento($documentoId);
            $_SESSION['sucesso'] = 'Documento removido com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao remover documento: ' . $e->getMessage();
        }
        
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/empresas-tomadoras/$empresaId#documentos");
        exit;
    }
    
    // BUSCAR CEP (VIA AJAX)
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
        
        // Buscar via ViaCEP
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
    
    // HELPERS
    private function uploadLogo($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'erro' => 'Tipo de arquivo inválido. Use JPG, PNG, GIF ou WEBP.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'erro' => 'Arquivo muito grande. Tamanho máximo: 2MB.'];
        }
        
        $uploadDir = __DIR__ . '/../../public/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'erro' => 'Erro ao fazer upload do arquivo.'];
        }
        
        return ['success' => true, 'filename' => $filename];
    }
    
    private function uploadDocumento($file) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'erro' => 'Tipo de arquivo inválido. Use PDF, JPG, PNG, DOC ou DOCX.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'erro' => 'Arquivo muito grande. Tamanho máximo: 10MB.'];
        }
        
        $uploadDir = __DIR__ . '/../../public/uploads/documentos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'erro' => 'Erro ao fazer upload do arquivo.'];
        }
        
        return [
            'success' => true, 
            'filename' => $filename,
            'size' => $file['size'],
            'mime_type' => $file['type']
        ];
    }
}
