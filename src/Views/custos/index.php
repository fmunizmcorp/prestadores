<?php
/**
 * View: Listagem de Custos - Sprint 70.2
 */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Custos</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Gestão de Custos</h1>
            <p class="text-muted mb-0">Controle de custos operacionais e despesas</p>
        </div>
        <a href="?page=custos&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Custo
        </a>
    </div>

    <?php if (isset($_SESSION['sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['sucesso'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['sucesso']); endif; ?>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase text-white-50 mb-2">Total</h6>
                    <h2><?= $stats['total'] ?? 0 ?></h2>
                    <small>Custos cadastrados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-uppercase text-white-50 mb-2">Pendentes</h6>
                    <h2><?= $stats['pendentes'] ?? 0 ?></h2>
                    <small>R$ <?= number_format($stats['valor_total_pendente'] ?? 0, 2, ',', '.') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase text-white-50 mb-2">Pagos</h6>
                    <h2><?= $stats['pagos'] ?? 0 ?></h2>
                    <small>R$ <?= number_format($stats['valor_total_pago'] ?? 0, 2, ',', '.') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-uppercase text-white-50 mb-2">Total Geral</h6>
                    <h2>R$ <?= number_format(($stats['valor_total_geral'] ?? 0)/1000, 0) ?>k</h2>
                    <small>Soma de todos os custos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($custos)): ?>
            <div class="alert alert-info">Nenhum custo encontrado.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th width="100">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($custos as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($c['data_custo'])) ?></td>
                            <td><?= htmlspecialchars($c['descricao']) ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($c['tipo']) ?></span></td>
                            <td><strong>R$ <?= number_format($c['valor'], 2, ',', '.') ?></strong></td>
                            <td>
                                <?php
                                $badge = ['pendente'=>'warning','aprovado'=>'info','pago'=>'success','cancelado'=>'secondary'];
                                ?>
                                <span class="badge bg-<?= $badge[$c['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="?page=custos&action=show&id=<?= $c['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPaginas > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=custos&pag=<?= $page-1 ?>">Anterior</a>
                    </li>
                    <?php for ($i = max(1, $page-2); $i <= min($totalPaginas, $page+2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=custos&pag=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPaginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=custos&pag=<?= $page+1 ?>">Próxima</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
