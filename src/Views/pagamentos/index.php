<?php
/**
 * View: Listagem de Pagamentos
 * Controller: PagamentoController
 * Sprint 70.1 - Módulo de Pagamentos Completo
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Pagamentos</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Gestão de Pagamentos</h1>
            <p class="text-muted mb-0">Gerencie todos os pagamentos e recebimentos</p>
        </div>
        <a href="?page=pagamentos&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Pagamento
        </a>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['sucesso'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['sucesso']); endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['erro'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['erro']); endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-stats bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Total</h6>
                            <h2 class="mb-0"><?= $stats['total'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-receipt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Pagamentos registrados</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Pendentes</h6>
                            <h2 class="mb-0"><?= $stats['pendentes'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Aguardando processamento</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Confirmados</h6>
                            <h2 class="mb-0"><?= $stats['confirmados'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Pagamentos confirmados</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Valor Total</h6>
                            <h2 class="mb-0">R$ <?= number_format($stats['valor_total'] ?? 0, 2, ',', '.') ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Processados + Confirmados</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                    <i class="fas fa-filter me-2"></i>Filtros de Pesquisa
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filtrosCollapse">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="page" value="pagamentos">
                    
                    <div class="col-md-2">
                        <label for="forma_pagamento" class="form-label">Forma Pagamento</label>
                        <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                            <option value="">Todas</option>
                            <?php foreach ($formasPagamento ?? [] as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($_GET['forma_pagamento'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusDisponiveis ?? [] as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                               value="<?= $_GET['data_inicio'] ?? '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" 
                               value="<?= $_GET['data_fim'] ?? '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="limit" class="form-label">Itens por página</label>
                        <select class="form-select" id="limit" name="limit">
                            <option value="10" <?= ($_GET['limit'] ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= ($_GET['limit'] ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= ($_GET['limit'] ?? 20) == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= ($_GET['limit'] ?? 20) == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Lista de Pagamentos</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pagamentos)): ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>Nenhum pagamento encontrado.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Forma</th>
                            <th>Origem</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagamentos as $pag): ?>
                        <tr>
                            <td><?= $pag['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($pag['data_pagamento'])) ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= ucfirst($pag['forma_pagamento']) ?>
                                </span>
                            </td>
                            <td>
                                <?= ucfirst(str_replace('_', ' ', $pag['origem_tipo'])) ?>
                                <?php if (!empty($pag['origem_numero'])): ?>
                                <br><small class="text-muted">#<?= $pag['origem_numero'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>R$ <?= number_format($pag['valor'], 2, ',', '.') ?></strong>
                                <?php if ($pag['valor_taxa'] > 0): ?>
                                <br><small class="text-danger">Taxa: R$ <?= number_format($pag['valor_taxa'], 2, ',', '.') ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'pendente' => 'warning',
                                    'processado' => 'info',
                                    'confirmado' => 'success',
                                    'estornado' => 'danger',
                                    'cancelado' => 'secondary'
                                ];
                                $badgeClass = $statusClass[$pag['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= ucfirst($pag['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="?page=pagamentos&action=show&id=<?= $pag['id'] ?>" 
                                       class="btn btn-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($pag['status'] === 'pendente'): ?>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="confirmarExclusao(<?= $pag['id'] ?>)" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPaginas > 1): ?>
            <nav aria-label="Paginação" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=pagamentos&pag=<?= $page - 1 ?><?= !empty($_GET['forma_pagamento']) ? '&forma_pagamento='.$_GET['forma_pagamento'] : '' ?><?= !empty($_GET['status']) ? '&status='.$_GET['status'] : '' ?>">
                            Anterior
                        </a>
                    </li>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPaginas, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=pagamentos&pag=<?= $i ?><?= !empty($_GET['forma_pagamento']) ? '&forma_pagamento='.$_GET['forma_pagamento'] : '' ?><?= !empty($_GET['status']) ? '&status='.$_GET['status'] : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPaginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=pagamentos&pag=<?= $page + 1 ?><?= !empty($_GET['forma_pagamento']) ? '&forma_pagamento='.$_GET['forma_pagamento'] : '' ?><?= !empty($_GET['status']) ? '&status='.$_GET['status'] : '' ?>">
                            Próxima
                        </a>
                    </li>
                </ul>
                <p class="text-center text-muted">
                    Página <?= $page ?> de <?= $totalPaginas ?> (<?= $total ?> registro<?= $total != 1 ? 's' : '' ?> no total)
                </p>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir este pagamento?
            </div>
            <div class="modal-footer">
                <form id="formExcluir" method="POST" action="?page=pagamentos&action=delete">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" id="pagamentoIdExcluir">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarExclusao(id) {
    document.getElementById('pagamentoIdExcluir').value = id;
    new bootstrap.Modal(document.getElementById('modalExcluir')).show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
