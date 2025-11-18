<?php 
$pageTitle = 'Recuperar Senha';
$bodyClass = 'auth-page';
require __DIR__ . '/../layout/header.php';
$config = require __DIR__ . '/../../../config/app.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="<?= asset('images/logo.png') ?>" alt="Clinfec" class="auth-logo" onerror="this.style.display='none'">
            <h1>Recuperar Senha</h1>
            <p>Digite seu email para receber instruções de recuperação</p>
        </div>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success = flash('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('forgot-password') ?>" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="seu@email.com"
                    required
                    autofocus
                >
            </div>

            <?php if ($config['recaptcha']['enabled']): ?>
            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="<?= $config['recaptcha']['site_key'] ?>"></div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i>
                Enviar Instruções
            </button>
        </form>

        <div class="auth-footer">
            <p><a href="<?= base_url('login') ?>" class="link"><i class="fas fa-arrow-left"></i> Voltar ao login</a></p>
        </div>
    </div>
</div>

<?php if ($config['recaptcha']['enabled']): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
