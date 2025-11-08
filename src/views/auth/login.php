<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Clinfec</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/public/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 450px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card login-card">
                    <div class="login-header">
                        <i class="fas fa-building"></i>
                        <h3 class="mb-0">Sistema Clinfec</h3>
                        <p class="mb-0 mt-2">Gestão de Empresas e Contratos</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['sucesso'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= $_SESSION['sucesso'] ?>
                            </div>
                            <?php unset($_SESSION['sucesso']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['erro'])): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['erro'] ?>
                            </div>
                            <?php unset($_SESSION['erro']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['info'])): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <?= $_SESSION['info'] ?>
                            </div>
                            <?php unset($_SESSION['info']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/login" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> E-mail
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg" 
                                       id="email" 
                                       name="email" 
                                       placeholder="seu@email.com"
                                       required 
                                       autofocus>
                                <div class="invalid-feedback">
                                    Por favor, informe um e-mail válido.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    <i class="fas fa-lock"></i> Senha
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="senha" 
                                           name="senha" 
                                           placeholder="••••••••"
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, informe sua senha.
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="lembrar" 
                                       name="lembrar">
                                <label class="form-check-label" for="lembrar">
                                    Lembrar-me neste dispositivo
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Entrar
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/recuperar-senha" class="text-decoration-none">
                                <i class="fas fa-key"></i> Esqueci minha senha
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center">
                        <small class="text-muted">
                            © <?= date('Y') ?> Clinfec. Todos os direitos reservados.
                        </small>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-white">
                        <strong>Usuário de teste:</strong><br>
                        <span class="font-monospace">admin@clinfec.com / admin123</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('senha');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
