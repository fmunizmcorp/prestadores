<?php
/**
 * Usuários - Visualização Detalhada
 * SPRINT 75: View para exibir detalhes do usuário (Bug #29)
 */

require_once ROOT_PATH . '/src/Views/layout/header.php';

$usuario = $data['usuario'] ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=usuarios">Usuários</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($usuario['nome'] ?? 'Usuário') ?></li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($usuario['nome'] ?? '') ?>
                <?php if (($usuario['ativo'] ?? true)): ?>
                <span class="badge bg-success">Ativo</span>
                <?php else: ?>
                <span class="badge bg-secondary">Inativo</span>
                <?php endif; ?>
            </h1>
            <p class="text-muted">Detalhes do usuário</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= BASE_URL ?>/?page=usuarios" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['master', 'admin'])): ?>
            <a href="<?= BASE_URL ?>/?page=usuarios&action=edit&id=<?= $usuario['id'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
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

    <div class="row">
        <!-- Informações Principais -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações Principais</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th width="40%">ID:</th>
                                <td><strong><?= $usuario['id'] ?? '-' ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nome:</th>
                                <td><?= htmlspecialchars($usuario['nome'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>E-mail:</th>
                                <td><?= htmlspecialchars($usuario['email'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Tipo:</th>
                                <td>
                                    <span class="badge bg-<?= ($usuario['role'] ?? '') === 'master' ? 'danger' : (($usuario['role'] ?? '') === 'admin' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($usuario['role'] ?? 'usuario') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php if (($usuario['ativo'] ?? true)): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informações de Acesso -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Informações de Acesso</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th width="40%">Cadastrado em:</th>
                                <td>
                                    <?php if (isset($usuario['criado_em'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($usuario['criado_em'])) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Último acesso:</th>
                                <td>
                                    <?php if (isset($usuario['ultimo_acesso'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                    <?php else: ?>
                                        Nunca acessou
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Email verificado:</th>
                                <td>
                                    <?php if (($usuario['email_verificado'] ?? false)): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verificado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Não verificado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tentativas de login:</th>
                                <td><?= $usuario['tentativas_login'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <th>Bloqueado até:</th>
                                <td>
                                    <?php if (isset($usuario['bloqueado_ate']) && $usuario['bloqueado_ate']): ?>
                                        <span class="text-danger">
                                            <i class="bi bi-lock-fill"></i> 
                                            <?= date('d/m/Y H:i', strtotime($usuario['bloqueado_ate'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-success">Não bloqueado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Empresas Vinculadas (se houver) -->
    <?php if (isset($usuario['empresas']) && !empty($usuario['empresas'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Empresas Vinculadas</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= htmlspecialchars($usuario['empresas']) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
require_once ROOT_PATH . '/src/Views/layout/footer.php';
?>
