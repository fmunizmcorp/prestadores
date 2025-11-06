<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-project-diagram"></i> Projetos</h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Projeto
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                    <p class="mb-0">Total de Projetos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3><?= $stats['planejamento'] ?? 0 ?></h3>
                    <p class="mb-0">Em Planejamento</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3><?= $stats['execucao'] ?? 0 ?></h3>
                    <p class="mb-0">Em Execução</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h3><?= $stats['atrasados'] ?? 0 ?></h3>
                    <p class="mb-0">Atrasados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filtros
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/">
                <input type="hidden" name="page" value="projetos">
                <div class="row">
                    <div class="col-md-3">
                        <label>Buscar</label>
                        <input type="text" name="busca" class="form-control" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Código ou nome...">
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="planejamento" <?= ($filtros['status'] ?? '') === 'planejamento' ? 'selected' : '' ?>>Planejamento</option>
                            <option value="execucao" <?= ($filtros['status'] ?? '') === 'execucao' ? 'selected' : '' ?>>Execução</option>
                            <option value="pausado" <?= ($filtros['status'] ?? '') === 'pausado' ? 'selected' : '' ?>>Pausado</option>
                            <option value="concluido" <?= ($filtros['status'] ?? '') === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                            <option value="cancelado" <?= ($filtros['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Categoria</label>
                        <select name="categoria_id" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filtros['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Empresa</label>
                        <select name="empresa_tomadora_id" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($tomadoras as $tom): ?>
                            <option value="<?= $tom['id'] ?>" <?= ($filtros['empresa_tomadora_id'] ?? '') == $tom['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tom['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['sucesso'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['erro'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="projetosTable">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Empresa</th>
                            <th>Gerente</th>
                            <th>Status</th>
                            <th>Progresso</th>
                            <th>Prazo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($projetos)): ?>
                            <?php foreach ($projetos as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['codigo']) ?></strong></td>
                                <td><?= htmlspecialchars($p['nome']) ?></td>
                                <td><?= htmlspecialchars($p['categoria_nome'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['empresa_nome'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['gerente_nome'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'secondary';
                                    switch($p['status']) {
                                        case 'planejamento': $badgeClass = 'info'; break;
                                        case 'execucao': $badgeClass = 'success'; break;
                                        case 'pausado': $badgeClass = 'warning'; break;
                                        case 'concluido': $badgeClass = 'primary'; break;
                                        case 'cancelado': $badgeClass = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($p['status']) ?></span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?= ($p['percentual_concluido'] ?? 0) >= 75 ? 'success' : (($p['percentual_concluido'] ?? 0) >= 50 ? 'info' : 'warning') ?>" 
                                             style="width: <?= $p['percentual_concluido'] ?? 0 ?>%">
                                            <?= $p['percentual_concluido'] ?? 0 ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($p['data_fim']): ?>
                                        <?= date('d/m/Y', strtotime($p['data_fim'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Não definido</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $p['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/?page=projetos&action=edit&id=<?= $p['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/?page=projetos&action=dashboard&id=<?= $p['id'] ?>" 
                                       class="btn btn-sm btn-primary" title="Dashboard">
                                        <i class="fas fa-chart-line"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Nenhum projeto encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL ?>/?page=projetos&p=<?= $page - 1 ?><?= !empty($filtros) ? '&' . http_build_query($filtros) : '' ?>">Anterior</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/?page=projetos&p=<?= $i ?><?= !empty($filtros) ? '&' . http_build_query($filtros) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL ?>/?page=projetos&p=<?= $page + 1 ?><?= !empty($filtros) ? '&' . http_build_query($filtros) : '' ?>">Próxima</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#projetosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
