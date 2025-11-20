<?php
/**
 * Usuários - Formulário de Criação
 * SPRINT 75: View para criar novo usuário (Bug #29)
 */

require_once ROOT_PATH . '/src/Views/layout/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=usuarios">Usuários</a></li>
            <li class="breadcrumb-item active">Novo Usuário</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="bi bi-person-plus-fill"></i> <?= $data['titulo'] ?? 'Novo Usuário' ?></h1>
            <p class="text-muted">Cadastrar novo usuário no sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= BASE_URL ?>/?page=usuarios" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-pencil-square"></i> Dados do Usuário</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/?page=usuarios&action=store" id="formUsuario" novalidate>
                <div class="row g-3">
                    <!-- Nome -->
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="nome" 
                               name="nome" 
                               required
                               maxlength="255"
                               placeholder="Digite o nome completo">
                        <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required
                               maxlength="255"
                               placeholder="usuario@exemplo.com">
                        <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                    </div>

                    <!-- Senha -->
                    <div class="col-md-6">
                        <label for="senha" class="form-label">Senha <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="senha" 
                               name="senha" 
                               required
                               minlength="6"
                               placeholder="Mínimo 6 caracteres">
                        <div class="invalid-feedback">A senha deve ter no mínimo 6 caracteres.</div>
                    </div>

                    <!-- Confirmar Senha -->
                    <div class="col-md-6">
                        <label for="confirmar_senha" class="form-label">Confirmar Senha <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="confirmar_senha" 
                               name="confirmar_senha" 
                               required
                               minlength="6"
                               placeholder="Repita a senha">
                        <div class="invalid-feedback">As senhas devem ser iguais.</div>
                    </div>

                    <!-- Role/Tipo -->
                    <div class="col-md-6">
                        <label for="role" class="form-label">Tipo de Usuário <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($data['roles'] as $role): ?>
                            <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione o tipo de usuário.</div>
                        <small class="form-text text-muted">
                            Master: Acesso total | Admin: Gerenciamento | Gestor: Supervisão | Usuario: Operacional
                        </small>
                    </div>

                    <!-- Status Ativo -->
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="ativo" 
                                   name="ativo" 
                                   checked>
                            <label class="form-check-label" for="ativo">
                                Usuário ativo no sistema
                            </label>
                        </div>
                        <small class="form-text text-muted">Desmarque para criar usuário inativo</small>
                    </div>
                </div>

                <!-- Botões -->
                <div class="row mt-4">
                    <div class="col-12">
                        <hr>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Salvar Usuário
                        </button>
                        <a href="<?= BASE_URL ?>/?page=usuarios" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validação do formulário
(function() {
    'use strict';
    
    const form = document.getElementById('formUsuario');
    const senha = document.getElementById('senha');
    const confirmarSenha = document.getElementById('confirmar_senha');
    
    // Validação de senhas iguais
    function validarSenhas() {
        if (senha.value !== confirmarSenha.value) {
            confirmarSenha.setCustomValidity('As senhas não coincidem');
        } else {
            confirmarSenha.setCustomValidity('');
        }
    }
    
    senha.addEventListener('change', validarSenhas);
    confirmarSenha.addEventListener('keyup', validarSenhas);
    
    // Validação ao submeter
    form.addEventListener('submit', function(event) {
        validarSenhas();
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();
</script>

<?php
require_once ROOT_PATH . '/src/Views/layout/footer.php';
?>
