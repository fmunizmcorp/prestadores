<!-- Configurações Gerais -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=configuracoes">Configurações</a></li>
                    <li class="breadcrumb-item active">Gerais</li>
                </ol>
            </nav>

            <h2><i class="fas fa-sliders-h"></i> Configurações Gerais</h2>
            <p class="text-muted">Ajuste configurações globais do sistema</p>
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
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Configurações do Sistema</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/?page=configuracoes&action=geral">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <!-- Nome do Sistema -->
                        <div class="form-group">
                            <label for="system_name">Nome do Sistema</label>
                            <input type="text" class="form-control" id="system_name" name="system_name" 
                                   value="<?= htmlspecialchars($generalSettings['system_name']['value'] ?? 'Sistema de Gestão de Prestadores') ?>" 
                                   placeholder="Sistema de Gestão de Prestadores">
                            <small class="form-text text-muted">Nome exibido no cabeçalho e emails</small>
                        </div>

                        <!-- Fuso Horário -->
                        <div class="form-group">
                            <label for="system_timezone">Fuso Horário</label>
                            <select class="form-control" id="system_timezone" name="system_timezone">
                                <option value="America/Sao_Paulo" <?= ($generalSettings['system_timezone']['value'] ?? 'America/Sao_Paulo') === 'America/Sao_Paulo' ? 'selected' : '' ?>>
                                    América/São Paulo (BRT)
                                </option>
                                <option value="America/Manaus" <?= ($generalSettings['system_timezone']['value'] ?? '') === 'America/Manaus' ? 'selected' : '' ?>>
                                    América/Manaus (AMT)
                                </option>
                                <option value="America/Recife" <?= ($generalSettings['system_timezone']['value'] ?? '') === 'America/Recife' ? 'selected' : '' ?>>
                                    América/Recife (BRT)
                                </option>
                                <option value="America/Fortaleza" <?= ($generalSettings['system_timezone']['value'] ?? '') === 'America/Fortaleza' ? 'selected' : '' ?>>
                                    América/Fortaleza (BRT)
                                </option>
                                <option value="America/Noronha" <?= ($generalSettings['system_timezone']['value'] ?? '') === 'America/Noronha' ? 'selected' : '' ?>>
                                    América/Fernando de Noronha (FNT)
                                </option>
                            </select>
                            <small class="form-text text-muted">Define o fuso horário padrão do sistema</small>
                        </div>

                        <!-- Idioma (preparado para futuro) -->
                        <div class="form-group">
                            <label for="system_language">Idioma do Sistema</label>
                            <select class="form-control" id="system_language" name="system_language" disabled>
                                <option value="pt_BR" selected>Português (Brasil)</option>
                                <option value="en_US">English (US)</option>
                                <option value="es_ES">Español</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Funcionalidade em desenvolvimento
                            </small>
                        </div>

                        <hr>

                        <!-- Botões -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
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

        <!-- Sidebar - Informações -->
        <div class="col-md-4">
            <!-- Card Informações do Sistema -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informações</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Versão:</strong></td>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <td><strong>PHP:</strong></td>
                            <td><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td><strong>Servidor:</strong></td>
                            <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td><strong>TZ Atual:</strong></td>
                            <td><?= date_default_timezone_get() ?></td>
                        </tr>
                        <tr>
                            <td><strong>Data/Hora:</strong></td>
                            <td><?= date('d/m/Y H:i:s') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Card Dicas -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Dicas</h5>
                </div>
                <div class="card-body">
                    <ul class="small pl-3">
                        <li>Altere o <strong>nome do sistema</strong> para personalizar a interface</li>
                        <li>Configure o <strong>fuso horário</strong> correto para evitar problemas com datas</li>
                        <li>Configurações afetam todo o sistema imediatamente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
