<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-check-double"></i> Conciliação Bancária</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item active">Conciliação</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=financeiro&action=conciliacoes&do=importar" class="btn btn-success">
                <i class="fas fa-file-upload"></i> Importar OFX
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Pendentes Conciliação</h6>
                    <h3><?= $stats['pendentes'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Conciliadas</h6>
                    <h3><?= $stats['conciliadas'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Taxa de Conciliação</h6>
                    <h3><?= $stats['taxa_conciliacao'] ?? 0 ?>%</h3>
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
                <input type="hidden" name="action" value="conciliacoes">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Período</label>
                        <input type="month" class="form-control" name="periodo" value="<?= $_GET['periodo'] ?? date('Y-m') ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <option value="pendente" <?= (isset($_GET['status']) && $_GET['status'] === 'pendente') ? 'selected' : '' ?>>Pendente</option>
                            <option value="conciliado" <?= (isset($_GET['status']) && $_GET['status'] === 'conciliado') ? 'selected' : '' ?>>Conciliado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Conta Bancária</label>
                        <select class="form-select" name="conta_id">
                            <option value="">Todas</option>
                            <?php if (!empty($contas)): foreach ($contas as $conta): ?>
                            <option value="<?= $conta['id'] ?>" <?= (isset($_GET['conta_id']) && $_GET['conta_id'] == $conta['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($conta['banco'] . ' - ' . $conta['agencia'] . '/' . $conta['conta']) ?>
                            </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="?page=financeiro&action=conciliacoes" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="card-header bg-primary text-white"><i class="fas fa-list"></i> Transações Bancárias</div>
        <div class="card-body">
            <?php if (empty($transacoes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma transação para conciliar.
                    <a href="<?= BASE_URL ?>/?page=financeiro&action=conciliacoes&do=importar">Importar extrato OFX</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Documento</th>
                                <th class="text-end">Valor</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transacoes as $trans): ?>
                            <tr class="<?= $trans['conciliado'] ? 'table-success' : '' ?>">
                                <td><?= date('d/m/Y', strtotime($trans['data_transacao'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($trans['descricao']) ?>
                                    <?php if ($trans['conciliado'] && !empty($trans['lancamento_id'])): ?>
                                    <br><small class="text-success">
                                        <i class="fas fa-link"></i> Vinculado ao lançamento #<?= $trans['lancamento_id'] ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($trans['documento'] ?? '-') ?></td>
                                <td class="text-end">
                                    <span class="<?= $trans['valor'] > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                        R$ <?= number_format($trans['valor'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($trans['conciliado']): ?>
                                        <span class="badge bg-success">Conciliado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$trans['conciliado']): ?>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="conciliar(<?= $trans['id'] ?>)">
                                        <i class="fas fa-check"></i> Conciliar
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-secondary" 
                                            onclick="desconciliar(<?= $trans['id'] ?>)">
                                        <i class="fas fa-undo"></i> Desconciliar
                                    </button>
                                    <?php endif; ?>
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
function conciliar(id) {
    window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=conciliacoes&do=show&id=' + id;
}

function desconciliar(id) {
    if (confirm('Confirma a desconciliação desta transação?')) {
        window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=conciliacoes&do=desconciliar&id=' + id;
    }
}
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
