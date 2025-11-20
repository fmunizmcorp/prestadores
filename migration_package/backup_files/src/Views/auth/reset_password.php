<?php 
$pageTitle = 'Redefinir Senha';
$bodyClass = 'auth-page';
require __DIR__ . '/../layout/header.php';
$config = require __DIR__ . '/../../../config/app.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="<?= asset('images/logo.png') ?>" alt="Clinfec" class="auth-logo" onerror="this.style.display='none'">
            <h1>Redefinir Senha</h1>
            <p>Digite sua nova senha</p>
        </div>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('reset-password') ?>" class="auth-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Nova Senha
                </label>
                <div class="password-input">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Mínimo <?= $config['security']['password_min_length'] ?> caracteres"
                        required
                        autofocus
                    >
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="form-text">
                    A senha deve conter letras maiúsculas, números e caracteres especiais
                </small>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i>
                    Confirmar Nova Senha
                </label>
                <div class="password-input">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        placeholder="Repita a nova senha"
                        required
                    >
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-check"></i>
                Redefinir Senha
            </button>
        </form>

        <div class="auth-footer">
            <p><a href="<?= base_url('login') ?>" class="link"><i class="fas fa-arrow-left"></i> Voltar ao login</a></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
