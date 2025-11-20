<?php 
$pageTitle = 'Cadastro';
$bodyClass = 'auth-page';
require __DIR__ . '/../layout/header.php';
$config = require __DIR__ . '/../../../config/app.php';
?>

<div class="auth-container">
    <div class="auth-card auth-card-large">
        <div class="auth-header">
            <img src="<?= asset('images/logo.png') ?>" alt="Clinfec" class="auth-logo" onerror="this.style.display='none'">
            <h1>Criar Nova Conta</h1>
            <p>Preencha os dados abaixo para se cadastrar</p>
        </div>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div><?= $error ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('register') ?>" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="form-group">
                <label for="nome">
                    <i class="fas fa-user"></i>
                    Nome Completo *
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    class="form-control" 
                    placeholder="Digite seu nome completo"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email *
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="seu@email.com"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Senha *
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Mínimo <?= $config['security']['password_min_length'] ?> caracteres"
                            required
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
                        Confirmar Senha *
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            placeholder="Repita a senha"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($config['recaptcha']['enabled']): ?>
            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="<?= $config['recaptcha']['site_key'] ?>"></div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>Eu aceito os <a href="#" class="link">Termos de Uso</a> e a <a href="#" class="link">Política de Privacidade</a></span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i>
                Criar Conta
            </button>
        </form>

        <div class="auth-footer">
            <p>Já tem uma conta? <a href="<?= base_url('login') ?>" class="link">Faça login</a></p>
        </div>
    </div>
</div>

<?php if ($config['recaptcha']['enabled']): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
