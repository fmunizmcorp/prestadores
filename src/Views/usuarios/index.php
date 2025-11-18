<?php
/**
 * Usuários - Listagem
 * SPRINT 75: View básica para corrigir Bug #29
 */

// Include header
require_once ROOT_PATH . '/src/Views/layout/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="bi bi-people-fill"></i> <?= $data['titulo'] ?? 'Usuários' ?></h1>
            <p class="text-muted">Gerenciamento de usuários do sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= BASE_URL ?>/?page=usuarios&action=create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Usuário
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Estatísticas -->
    <?php if (isset($data['stats'])): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Usuários</h5>
                    <h2><?= $data['stats']['total'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Usuários Ativos</h5>
                    <h2><?= $data['stats']['ativos'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Usuários Inativos</h5>
                    <h2><?= $data['stats']['inativos'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="bi bi-funnel"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="usuarios">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="busca" class="form-control" 
                               value="<?= htmlspecialchars($data['filtros']['busca'] ?? '') ?>"
                               placeholder="Nome ou email...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="role" class="form-select">
                            <option value="">Todos</option>
                            <option value="master" <?= ($data['filtros']['role'] ?? '') === 'master' ? 'selected' : '' ?>>Master</option>
                            <option value="admin" <?= ($data['filtros']['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="gestor" <?= ($data['filtros']['role'] ?? '') === 'gestor' ? 'selected' : '' ?>>Gestor</option>
                            <option value="usuario" <?= ($data['filtros']['role'] ?? '') === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="ativo" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" <?= isset($data['filtros']['ativo']) && $data['filtros']['ativo'] === '1' ? 'selected' : '' ?>>Ativo</option>
                            <option value="0" <?= isset($data['filtros']['ativo']) && $data['filtros']['ativo'] === '0' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Usuários -->
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-list-ul"></i> Lista de Usuários</h5>
        </div>
        <div class="card-body">
            <?php if (empty($data['usuarios'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Nenhum usuário encontrado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Criado Em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['usuarios'] as $usuario): ?>
                            <tr>
                                <td><?= $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nome'] ?? '') ?></td>
                                <td><?= htmlspecialchars($usuario['email'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= $usuario['role'] === 'master' ? 'danger' : ($usuario['role'] === 'admin' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($usuario['role'] ?? 'usuario') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($usuario['ativo'] ?? true)): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= isset($usuario['criado_em']) ? date('d/m/Y', strtotime($usuario['criado_em'])) : '-' ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/?page=usuarios&action=show&id=<?= $usuario['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/?page=usuarios&action=edit&id=<?= $usuario['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if (($data['totalPages'] ?? 1) > 1): ?>
                <nav aria-label="Paginação">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                        <li class="page-item <?= $i === ($data['page'] ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/?page=usuarios&p=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
require_once ROOT_PATH . '/src/Views/layout/footer.php';
?>
