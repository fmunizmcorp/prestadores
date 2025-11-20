<!-- Configurações - Index -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-cog"></i> Configurações do Sistema</h2>
            <p class="text-muted">Gerencie as configurações globais do sistema</p>
            <hr>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Card Email/SMTP -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-envelope fa-4x text-primary"></i>
                    </div>
                    <h5 class="card-title">Configurações de Email</h5>
                    <p class="card-text text-muted">
                        Configure servidor SMTP, remetente e opções de email do sistema.
                    </p>
                    <a href="<?= BASE_URL ?>/?page=configuracoes&action=email" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Configurar Email
                    </a>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-info-circle"></i> SMTP, Remetente, Segurança
                </div>
            </div>
        </div>

        <!-- Card Configurações Gerais -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-sliders-h fa-4x text-success"></i>
                    </div>
                    <h5 class="card-title">Configurações Gerais</h5>
                    <p class="card-text text-muted">
                        Ajuste nome do sistema, fuso horário e preferências globais.
                    </p>
                    <a href="<?= BASE_URL ?>/?page=configuracoes&action=geral" class="btn btn-success">
                        <i class="fas fa-edit"></i> Configurar Sistema
                    </a>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-info-circle"></i> Nome, Timezone, Idioma
                </div>
            </div>
        </div>
    </div>

    <!-- Informações Adicionais -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-shield-alt"></i> Segurança</h5>
                <p>Apenas usuários com perfil <strong>Master</strong> ou <strong>Administrador</strong> podem acessar e modificar estas configurações.</p>
                <p class="mb-0">Todas as senhas são criptografadas antes de serem armazenadas no banco de dados.</p>
            </div>
        </div>
    </div>

    <!-- Categorias Disponíveis -->
    <?php if (!empty($categories)): ?>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-folder-open"></i> Categorias de Configurações</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($categories as $category): ?>
                            <div class="list-group-item">
                                <i class="fas fa-tag text-muted"></i>
                                <strong><?= ucfirst($category) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
}

.fa-4x {
    font-size: 4rem;
}
</style>
