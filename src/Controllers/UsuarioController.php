<?php

namespace App\Controllers;

use App\Database;
use PDO;

/**
 * UsuarioController
 * Sprint 32 - Gestão completa de usuários
 */
class UsuarioController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Listar usuários
     */
    public function index()
    {
        $this->checkPermission(['admin', 'master']);
        
        $search = $_GET['search'] ?? '';
        $ativo = $_GET['ativo'] ?? '';
        $perfil = $_GET['perfil'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        try {
            // Construir query
            $sql = "SELECT * FROM usuarios WHERE 1=1";
            $params = [];
            
            if ($search) {
                $sql .= " AND (nome LIKE :search OR email LIKE :search)";
                $params[':search'] = "%{$search}%";
            }
            
            if ($ativo !== '') {
                $sql .= " AND ativo = :ativo";
                $params[':ativo'] = $ativo;
            }
            
            if ($perfil) {
                $sql .= " AND (perfil = :perfil OR role = :perfil)";
                $params[':perfil'] = $perfil;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Contar total
            $sqlCount = "SELECT COUNT(*) as total FROM usuarios WHERE 1=1";
            if ($search) {
                $sqlCount .= " AND (nome LIKE :search OR email LIKE :search)";
            }
            if ($ativo !== '') {
                $sqlCount .= " AND ativo = :ativo";
            }
            if ($perfil) {
                $sqlCount .= " AND (perfil = :perfil OR role = :perfil)";
            }
            
            $stmtCount = $this->db->prepare($sqlCount);
            foreach ($params as $key => $value) {
                if ($key != ':limit' && $key != ':offset') {
                    $stmtCount->bindValue($key, $value);
                }
            }
            $stmtCount->execute();
            $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            
            $totalPaginas = ceil($total / $limit);
            
            // Estatísticas
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE ativo = 1");
            $usuarios_ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios");
            $usuarios_total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $this->render('usuarios/index', [
                'pageTitle' => 'Usuários',
                'usuarios' => $usuarios,
                'total' => $total,
                'page' => $page,
                'totalPaginas' => $totalPaginas,
                'usuarios_ativos' => $usuarios_ativos,
                'usuarios_total' => $usuarios_total,
                'search' => $search,
                'ativo' => $ativo,
                'perfil' => $perfil
            ]);
            
        } catch (\PDOException $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            $this->render('usuarios/index', [
                'pageTitle' => 'Usuários',
                'usuarios' => [],
                'total' => 0,
                'page' => 1,
                'totalPaginas' => 0,
                'usuarios_ativos' => 0,
                'usuarios_total' => 0,
                'erro' => 'Erro ao carregar usuários'
            ]);
        }
    }
    
    /**
     * Formulário de criação
     */
    public function create()
    {
        $this->checkPermission(['admin', 'master']);
        
        $this->render('usuarios/create', [
            'pageTitle' => 'Novo Usuário'
        ]);
    }
    
    /**
     * Salvar novo usuário
     */
    public function store()
    {
        $this->checkPermission(['admin', 'master']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }
        
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido';
            $this->redirect('usuarios/create');
        }
        
        $erros = [];
        
        // Validações
        if (empty($_POST['nome'])) {
            $erros[] = 'Nome é obrigatório';
        }
        
        if (empty($_POST['email'])) {
            $erros[] = 'E-mail é obrigatório';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail inválido';
        } else {
            // Verificar se email já existe
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $_POST['email']]);
            if ($stmt->fetch()) {
                $erros[] = 'E-mail já cadastrado';
            }
        }
        
        if (empty($_POST['senha'])) {
            $erros[] = 'Senha é obrigatória';
        } elseif (strlen($_POST['senha']) < 6) {
            $erros[] = 'Senha deve ter no mínimo 6 caracteres';
        }
        
        if ($_POST['senha'] !== $_POST['senha_confirmacao']) {
            $erros[] = 'Senhas não conferem';
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('usuarios/create');
        }
        
        try {
            $sql = "INSERT INTO usuarios (nome, email, senha, perfil, role, ativo, created_at) 
                    VALUES (:nome, :email, :senha, :perfil, :role, :ativo, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nome' => $_POST['nome'],
                ':email' => $_POST['email'],
                ':senha' => password_hash($_POST['senha'], PASSWORD_DEFAULT),
                ':perfil' => $_POST['perfil'] ?? 'usuario',
                ':role' => $_POST['perfil'] ?? 'usuario',
                ':ativo' => isset($_POST['ativo']) ? 1 : 0
            ]);
            
            $_SESSION['sucesso'] = 'Usuário cadastrado com sucesso!';
            $this->redirect('usuarios');
            
        } catch (\PDOException $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao cadastrar usuário';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('usuarios/create');
        }
    }
    
    /**
     * Exibir detalhes do usuário
     */
    public function show($id)
    {
        $this->checkPermission(['admin', 'master']);
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                $_SESSION['erro'] = 'Usuário não encontrado';
                $this->redirect('usuarios');
            }
            
            $this->render('usuarios/show', [
                'pageTitle' => 'Detalhes do Usuário',
                'usuario' => $usuario
            ]);
            
        } catch (\PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao carregar usuário';
            $this->redirect('usuarios');
        }
    }
    
    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $this->checkPermission(['admin', 'master']);
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                $_SESSION['erro'] = 'Usuário não encontrado';
                $this->redirect('usuarios');
            }
            
            $this->render('usuarios/edit', [
                'pageTitle' => 'Editar Usuário',
                'usuario' => $usuario
            ]);
            
        } catch (\PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao carregar usuário';
            $this->redirect('usuarios');
        }
    }
    
    /**
     * Atualizar usuário
     */
    public function update($id)
    {
        $this->checkPermission(['admin', 'master']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }
        
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido';
            $this->redirect("usuarios/$id/edit");
        }
        
        $erros = [];
        
        if (empty($_POST['nome'])) {
            $erros[] = 'Nome é obrigatório';
        }
        
        if (empty($_POST['email'])) {
            $erros[] = 'E-mail é obrigatório';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail inválido';
        } else {
            // Verificar se email já existe em outro usuário
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $_POST['email'], ':id' => $id]);
            if ($stmt->fetch()) {
                $erros[] = 'E-mail já cadastrado';
            }
        }
        
        // Se senha foi informada
        if (!empty($_POST['senha'])) {
            if (strlen($_POST['senha']) < 6) {
                $erros[] = 'Senha deve ter no mínimo 6 caracteres';
            }
            if ($_POST['senha'] !== $_POST['senha_confirmacao']) {
                $erros[] = 'Senhas não conferem';
            }
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['form_data'] = $_POST;
            $this->redirect("usuarios/$id/edit");
        }
        
        try {
            $sql = "UPDATE usuarios SET 
                    nome = :nome,
                    email = :email,
                    perfil = :perfil,
                    role = :role,
                    ativo = :ativo,
                    updated_at = NOW()";
            
            $params = [
                ':nome' => $_POST['nome'],
                ':email' => $_POST['email'],
                ':perfil' => $_POST['perfil'] ?? 'usuario',
                ':role' => $_POST['perfil'] ?? 'usuario',
                ':ativo' => isset($_POST['ativo']) ? 1 : 0,
                ':id' => $id
            ];
            
            // Se senha foi informada, atualizar
            if (!empty($_POST['senha'])) {
                $sql .= ", senha = :senha";
                $params[':senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $_SESSION['sucesso'] = 'Usuário atualizado com sucesso!';
            $this->redirect("usuarios/$id");
            
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao atualizar usuário';
            $_SESSION['form_data'] = $_POST;
            $this->redirect("usuarios/$id/edit");
        }
    }
    
    /**
     * Deletar usuário (soft delete)
     */
    public function delete($id)
    {
        $this->checkPermission(['admin', 'master']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }
        
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['erro'] = 'Token de segurança inválido';
            $this->redirect('usuarios');
        }
        
        // Não permitir deletar a si mesmo
        if ($id == $_SESSION['usuario_id']) {
            $_SESSION['erro'] = 'Você não pode deletar seu próprio usuário';
            $this->redirect('usuarios');
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET ativo = 0, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $_SESSION['sucesso'] = 'Usuário desativado com sucesso!';
            $this->redirect('usuarios');
            
        } catch (\PDOException $e) {
            error_log("Erro ao deletar usuário: " . $e->getMessage());
            $_SESSION['erro'] = 'Erro ao desativar usuário';
            $this->redirect('usuarios');
        }
    }
}
