<!-- Usuários - Criar -->
<div class="container-fluid">
    <div class="page-header">
        <h1>
            <i class="fas fa-user-plus"></i> Novo Usuário
        </h1>
        <div class="page-actions">
            <a href="?page=usuarios" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="?page=usuarios&action=store">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" class="form-control" required
                           value="<?= htmlspecialchars($_SESSION['form_data']['nome'] ?? '') ?>">
                </div>

                <div class="form-group col-md-4">
                    <label for="perfil">Perfil *</label>
                    <select id="perfil" name="perfil" class="form-control" required>
                        <option value="usuario" <?= ($_SESSION['form_data']['perfil'] ?? '') === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                        <option value="gestor" <?= ($_SESSION['form_data']['perfil'] ?? '') === 'gestor' ? 'selected' : '' ?>>Gestor</option>
                        <option value="admin" <?= ($_SESSION['form_data']['perfil'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <?php if (($_SESSION['usuario_perfil'] ?? '') === 'master'): ?>
                            <option value="master" <?= ($_SESSION['form_data']['perfil'] ?? '') === 'master' ? 'selected' : '' ?>>Master</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>">
                </div>

                <div class="form-group col-md-6">
                    <div class="form-check" style="margin-top: 35px;">
                        <input type="checkbox" id="ativo" name="ativo" class="form-check-input" checked>
                        <label for="ativo" class="form-check-label">Usuário Ativo</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="senha">Senha *</label>
                    <input type="password" id="senha" name="senha" class="form-control" required minlength="6">
                    <small class="form-text text-muted">Mínimo de 6 caracteres</small>
                </div>

                <div class="form-group col-md-6">
                    <label for="senha_confirmacao">Confirmar Senha *</label>
                    <input type="password" id="senha_confirmacao" name="senha_confirmacao" class="form-control" required minlength="6">
                </div>
            </div>

            <?php if (!empty($_SESSION['erros'])): ?>
                <div class="alert alert-danger">
                    <strong>Erro:</strong>
                    <ul>
                        <?php foreach ($_SESSION['erros'] as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['erros']); ?>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Usuário
                </button>
                <a href="?page=usuarios" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php unset($_SESSION['form_data']); ?>

<style>
.form-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-width: 800px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    border-color: #667eea;
    outline: none;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert ul {
    margin: 10px 0 0 20px;
}
</style>
