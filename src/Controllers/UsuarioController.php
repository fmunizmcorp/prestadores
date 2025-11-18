<?php

namespace App\Controllers;

use App\Models\Usuario;

/**
 * UsuarioController
 * SPRINT 75: Implementação básica para corrigir Bug #29
 * 
 * Gerencia operações de usuários do sistema
 */
class UsuarioController extends BaseController
{
    private $usuario;
    
    public function __construct()
    {
        parent::__construct();
        $this->usuario = new Usuario();
    }
    
    /**
     * Lista todos os usuários
     * GET /?page=usuarios&action=index
     */
    public function index()
    {
        $this->checkPermission(['master', 'admin']);
        
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 25;
        
        // Filtros
        $filtros = [];
        
        if (!empty($_GET['role'])) {
            $filtros['role'] = $_GET['role'];
        }
        
        if (!empty($_GET['busca'])) {
            $filtros['busca'] = $_GET['busca'];
        }
        
        if (isset($_GET['ativo'])) {
            $filtros['ativo'] = $_GET['ativo'];
        }
        
        $usuarios = $this->usuario->all($filtros);
        $total = count($usuarios); // Simplificado
        
        $totalPages = ceil($total / $limit);
        
        // Estatísticas básicas
        $stats = [
            'total' => $total,
            'ativos' => count(array_filter($usuarios, fn($u) => $u['ativo'] ?? true)),
            'inativos' => count(array_filter($usuarios, fn($u) => !($u['ativo'] ?? true))),
        ];
        
        $data = [
            'titulo' => 'Usuários',
            'usuarios' => $usuarios,
            'stats' => $stats,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];
        
        $this->render('usuarios/index', $data);
    }
    
    /**
     * Exibe formulário de criação
     * GET /?page=usuarios&action=create
     */
    public function create()
    {
        $this->checkPermission(['master', 'admin']);
        
        $data = [
            'titulo' => 'Novo Usuário',
            'roles' => ['master', 'admin', 'gestor', 'usuario']
        ];
        
        $this->render('usuarios/create', $data);
    }
    
    /**
     * Salva novo usuário
     * POST /?page=usuarios&action=store
     */
    public function store()
    {
        $this->checkPermission(['master', 'admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=usuarios&action=create');
            exit;
        }
        
        try {
            $result = $this->usuario->create([
                'nome' => $_POST['nome'] ?? '',
                'email' => $_POST['email'] ?? '',
                'senha' => $_POST['senha'] ?? '',
                'role' => $_POST['role'] ?? 'usuario',
                'ativo' => isset($_POST['ativo']) ? 1 : 0
            ]);
            
            if ($result) {
                $_SESSION['success'] = 'Usuário criado com sucesso!';
                header('Location: ' . BASE_URL . '/?page=usuarios');
            } else {
                $_SESSION['error'] = 'Erro ao criar usuário.';
                header('Location: ' . BASE_URL . '/?page=usuarios&action=create');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/?page=usuarios&action=create');
        }
        
        exit;
    }
    
    /**
     * Exibe detalhes do usuário
     * GET /?page=usuarios&action=show&id=X
     */
    public function show($id)
    {
        $this->checkPermission(['master', 'admin', 'gestor']);
        
        $usuario = $this->usuario->findById($id);
        
        if (!$usuario) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: ' . BASE_URL . '/?page=usuarios');
            exit;
        }
        
        $data = [
            'titulo' => 'Detalhes do Usuário',
            'usuario' => $usuario
        ];
        
        $this->render('usuarios/show', $data);
    }
    
    /**
     * Exibe formulário de edição
     * GET /?page=usuarios&action=edit&id=X
     */
    public function edit($id)
    {
        $this->checkPermission(['master', 'admin']);
        
        $usuario = $this->usuario->findById($id);
        
        if (!$usuario) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: ' . BASE_URL . '/?page=usuarios');
            exit;
        }
        
        $data = [
            'titulo' => 'Editar Usuário',
            'usuario' => $usuario,
            'roles' => ['master', 'admin', 'gestor', 'usuario']
        ];
        
        $this->render('usuarios/edit', $data);
    }
    
    /**
     * Atualiza usuário
     * POST /?page=usuarios&action=update&id=X
     */
    public function update($id)
    {
        $this->checkPermission(['master', 'admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=usuarios&action=edit&id=' . $id);
            exit;
        }
        
        try {
            $updateData = [
                'nome' => $_POST['nome'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? 'usuario',
                'ativo' => isset($_POST['ativo']) ? 1 : 0
            ];
            
            // Se senha foi fornecida, atualizar
            if (!empty($_POST['senha'])) {
                $updateData['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            }
            
            $result = $this->usuario->update($id, $updateData);
            
            if ($result) {
                $_SESSION['success'] = 'Usuário atualizado com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao atualizar usuário.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/?page=usuarios');
        exit;
    }
    
    /**
     * Deleta usuário (soft delete)
     * POST /?page=usuarios&action=destroy&id=X
     */
    public function destroy($id)
    {
        $this->checkPermission(['master', 'admin']);
        
        try {
            $result = $this->usuario->update($id, ['ativo' => 0]);
            
            if ($result) {
                $_SESSION['success'] = 'Usuário desativado com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao desativar usuário.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/?page=usuarios');
        exit;
    }
}
