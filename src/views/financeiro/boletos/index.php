<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-barcode"></i> Boletos Bancários</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item active">Boletos</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=financeiro&action=boletos&do=create" class="btn btn-success">
                <i class="fas fa-plus"></i> Gerar Boleto
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Pendentes</h6>
                    <h3><?= $stats['pendentes'] ?? 0 ?></h3>
                    <small>R$ <?= number_format($stats['valor_pendente'] ?? 0, 2, ',', '.') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Pagos</h6>
                    <h3><?= $stats['pagos'] ?? 0 ?></h3>
                    <small>R$ <?= number_format($stats['valor_pago'] ?? 0, 2, ',', '.') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Vencidos</h6>
                    <h3><?= $stats['vencidos'] ?? 0 ?></h3>
                    <small>R$ <?= number_format($stats['valor_vencido'] ?? 0, 2, ',', '.') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6>Cancelados</h6>
                    <h3><?= $stats['cancelados'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-filter"></i> Filtros</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="financeiro">
                <input type="hidden" name="action" value="boletos">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <option value="pendente" <?= (isset($_GET['status']) && $_GET['status'] === 'pendente') ? 'selected' : '' ?>>Pendente</option>
                            <option value="pago" <?= (isset($_GET['status']) && $_GET['status'] === 'pago') ? 'selected' : '' ?>>Pago</option>
                            <option value="vencido" <?= (isset($_GET['status']) && $_GET['status'] === 'vencido') ? 'selected' : '' ?>>Vencido</option>
                            <option value="cancelado" <?= (isset($_GET['status']) && $_GET['status'] === 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Vencimento Início</label>
                        <input type="date" class="form-control" name="venc_inicio" value="<?= $_GET['venc_inicio'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Vencimento Fim</label>
                        <input type="date" class="form-control" name="venc_fim" value="<?= $_GET['venc_fim'] ?? '' ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="?page=financeiro&action=boletos" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="card-header bg-primary text-white"><i class="fas fa-list"></i> Boletos</div>
        <div class="card-body">
            <?php if (empty($boletos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhum boleto encontrado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelaBoletos">
                        <thead>
                            <tr>
                                <th>Nosso Número</th>
                                <th>Cliente</th>
                                <th>Vencimento</th>
                                <th class="text-end">Valor</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($boletos as $bol): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($bol['nosso_numero']) ?></strong>
                                    <br><small class="text-muted font-monospace"><?= htmlspecialchars($bol['linha_digitavel']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($bol['sacado_nome']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($bol['sacado_documento']) ?></small>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($bol['data_vencimento'])) ?>
                                    <?php
                                    $hoje = new DateTime();
                                    $venc = new DateTime($bol['data_vencimento']);
                                    $diff = $hoje->diff($venc);
                                    $dias = $diff->days * ($venc < $hoje ? -1 : 1);
                                    if ($dias < 0 && $bol['status'] !== 'pago') {
                                        echo '<br><small class="text-danger fw-bold">' . abs($dias) . ' dias vencido</small>';
                                    } elseif ($dias >= 0 && $dias <= 7 && $bol['status'] !== 'pago') {
                                        echo '<br><small class="text-warning">Vence em ' . $dias . ' dias</small>';
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <strong>R$ <?= number_format($bol['valor'], 2, ',', '.') ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($bol['status']) {
                                        'pago' => 'success',
                                        'pendente' => 'warning',
                                        'vencido' => 'danger',
                                        'cancelado' => 'secondary',
                                        default => 'info'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($bol['status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/?page=financeiro&action=boletos&do=show&id=<?= $bol['id'] ?>" 
                                           class="btn btn-warning" title="Ver/Imprimir" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <?php if ($bol['status'] !== 'pago' && $bol['status'] !== 'cancelado'): ?>
                                        <button type="button" class="btn btn-success" 
                                                onclick="registrarPagamento(<?= $bol['id'] ?>)" title="Registrar Pagamento">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" 
                                                onclick="cancelarBoleto(<?= $bol['id'] ?>)" title="Cancelar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function registrarPagamento(id) {
    if (confirm('Confirma o pagamento deste boleto?')) {
        window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=boletos&do=registrar-pagamento&id=' + id;
    }
}

function cancelarBoleto(id) {
    if (confirm('Confirma o cancelamento deste boleto?\nEsta ação não pode ser desfeita!')) {
        window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=boletos&do=cancelar&id=' + id;
    }
}

$(document).ready(function() {
    if (typeof $.fn.dataTable !== 'undefined') {
        $('#tabelaBoletos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json' },
            order: [[2, 'asc']],
            pageLength: 25
        });
    }
});
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
