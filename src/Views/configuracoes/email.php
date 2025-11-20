<!-- Configurações de Email/SMTP -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=configuracoes">Configurações</a></li>
                    <li class="breadcrumb-item active">Email</li>
                </ol>
            </nav>

            <h2><i class="fas fa-envelope"></i> Configurações de Email</h2>
            <p class="text-muted">Configure o servidor SMTP para envio de emails do sistema</p>
            <hr>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Formulário de Configurações -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-server"></i> Configurações SMTP</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/?page=configuracoes&action=email" id="smtpForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <!-- SMTP Habilitado -->
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="smtp_enabled" name="smtp_enabled" value="1" <?= $smtpConfig['enabled'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="smtp_enabled">
                                    <strong>SMTP Habilitado</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">Ative ou desative o envio de emails pelo sistema</small>
                        </div>

                        <hr>

                        <!-- Servidor SMTP -->
                        <div class="form-group">
                            <label for="smtp_host">Servidor SMTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                   value="<?= htmlspecialchars($smtpConfig['host']) ?>" 
                                   placeholder="smtp.exemplo.com.br ou localhost" required>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Use <code>localhost</code> para servidor local ou endereço do servidor SMTP externo
                            </small>
                        </div>

                        <div class="row">
                            <!-- Porta SMTP -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_port">Porta SMTP <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                           value="<?= htmlspecialchars($smtpConfig['port']) ?>" 
                                           min="1" max="65535" required>
                                    <small class="form-text text-muted">Comum: 25, 465 (SSL), 587 (TLS)</small>
                                </div>
                            </div>

                            <!-- Segurança -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_secure">Segurança</label>
                                    <select class="form-control" id="smtp_secure" name="smtp_secure">
                                        <option value="none" <?= $smtpConfig['secure'] === 'none' ? 'selected' : '' ?>>Nenhuma</option>
                                        <option value="tls" <?= $smtpConfig['secure'] === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                                        <option value="ssl" <?= $smtpConfig['secure'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Usuário SMTP -->
                        <div class="form-group">
                            <label for="smtp_username">Usuário SMTP</label>
                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                   value="<?= htmlspecialchars($smtpConfig['username']) ?>" 
                                   placeholder="usuario@exemplo.com.br (deixe vazio se não usar autenticação)">
                            <small class="form-text text-muted">Deixe em branco se o servidor não requer autenticação</small>
                        </div>

                        <!-- Senha SMTP -->
                        <div class="form-group">
                            <label for="smtp_password">Senha SMTP</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                       placeholder="Deixe em branco para manter a senha atual">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-lock"></i> A senha é criptografada antes de ser armazenada
                            </small>
                        </div>

                        <hr>
                        <h6 class="text-muted">Informações do Remetente</h6>

                        <!-- Email Remetente -->
                        <div class="form-group">
                            <label for="smtp_from_email">Email Remetente <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" 
                                   value="<?= htmlspecialchars($smtpConfig['from_email']) ?>" 
                                   placeholder="noreply@clinfec.com.br" required>
                        </div>

                        <!-- Nome Remetente -->
                        <div class="form-group">
                            <label for="smtp_from_name">Nome do Remetente</label>
                            <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" 
                                   value="<?= htmlspecialchars($smtpConfig['from_name']) ?>" 
                                   placeholder="Sistema Clinfec">
                        </div>

                        <!-- Botões -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Salvar Configurações
                            </button>
                            <a href="<?= BASE_URL ?>/?page=configuracoes" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar - Teste de Email -->
        <div class="col-md-4">
            <!-- Card Teste de Email -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-paper-plane"></i> Testar Email</h5>
                </div>
                <div class="card-body">
                    <p class="small">Envie um email de teste para verificar se as configurações estão corretas.</p>
                    <form method="POST" action="<?= BASE_URL ?>/?page=configuracoes&action=testEmail" id="testEmailForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="form-group">
                            <label for="test_email">Email de Teste</label>
                            <input type="email" class="form-control" id="test_email" name="test_email" 
                                   placeholder="seu@email.com" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-paper-plane"></i> Enviar Teste
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card Ajuda -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Ajuda</h5>
                </div>
                <div class="card-body">
                    <h6>Configurações Comuns:</h6>
                    <ul class="small">
                        <li><strong>Gmail:</strong><br>
                            Host: smtp.gmail.com<br>
                            Porta: 587 (TLS)</li>
                        <li><strong>Hostinger:</strong><br>
                            Host: smtp.hostinger.com<br>
                            Porta: 587 (TLS)</li>
                        <li><strong>Servidor Local:</strong><br>
                            Host: localhost<br>
                            Porta: 25</li>
                    </ul>
                    <hr>
                    <p class="small mb-0">
                        <i class="fas fa-shield-alt text-success"></i> 
                        Todas as senhas são criptografadas com AES-256-CBC
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('smtp_password');
    const icon = this.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Validação do formulário
document.getElementById('smtpForm').addEventListener('submit', function(e) {
    const smtpEnabled = document.getElementById('smtp_enabled').checked;
    
    if (!smtpEnabled) {
        if (!confirm('O envio de emails está desabilitado. Deseja continuar?')) {
            e.preventDefault();
            return false;
        }
    }
});

// Confirmação antes de enviar email de teste
document.getElementById('testEmailForm').addEventListener('submit', function(e) {
    if (!confirm('Tem certeza que deseja enviar um email de teste?')) {
        e.preventDefault();
        return false;
    }
});
</script>
