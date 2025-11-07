<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-chart-line"></i> Dashboard Financeiro</h1>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar" class="btn btn-danger">
                    <i class="fas fa-file-invoice-dollar"></i> Contas a Pagar
                </a>
                <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber" class="btn btn-success">
                    <i class="fas fa-hand-holding-usd"></i> Contas a Receber
                </a>
                <a href="<?= BASE_URL ?>/?page=financeiro&action=fluxo-caixa" class="btn btn-info">
                    <i class="fas fa-chart-area"></i> Fluxo de Caixa
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3>R$ <?= number_format($dashboard['saldo_atual'] ?? 0, 2, ',', '.') ?></h3>
                    <p class="mb-0">Saldo Atual</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h3>R$ <?= number_format($dashboard['total_pagar'] ?? 0, 2, ',', '.') ?></h3>
                    <p class="mb-0">A Pagar (30 dias)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3>R$ <?= number_format($dashboard['total_receber'] ?? 0, 2, ',', '.') ?></h3>
                    <p class="mb-0">A Receber (30 dias)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card <?= ($liquidez['situacao'] ?? '') === 'excelente' ? 'bg-success' : (($liquidez['situacao'] ?? '') === 'boa' ? 'bg-info' : 'bg-warning') ?> text-white">
                <div class="card-body">
                    <h3><?= number_format($liquidez['liquidez_corrente'] ?? 0, 2, ',', '.') ?></h3>
                    <p class="mb-0">Liquidez Corrente</p>
                    <small><?= ucfirst($liquidez['situacao'] ?? '') ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Contas Vencidas e a Vencer -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle"></i> Contas a Pagar Vencidas (<?= count($contasPagarVencidas) ?>)
                </div>
                <div class="card-body p-0">
                    <?php if (empty($contasPagarVencidas)): ?>
                        <p class="p-3 text-center text-muted">Nenhuma conta vencida</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Vencimento</th>
                                        <th class="text-end">Valor</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($contasPagarVencidas, 0, 5) as $conta): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($conta['descricao']) ?></td>
                                        <td><span class="text-danger"><?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></span></td>
                                        <td class="text-end">R$ <?= number_format($conta['valor_pendente'], 2, ',', '.') ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/?page=financeiro&action=conta-pagar-show&id=<?= $conta['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($contasPagarVencidas) > 5): ?>
                        <div class="card-footer text-center">
                            <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar&status=vencida">Ver todas (<?= count($contasPagarVencidas) ?>)</a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <i class="fas fa-clock"></i> Contas a Pagar - Vencendo em 7 dias (<?= count($contasPagarVencer7) ?>)
                </div>
                <div class="card-body p-0">
                    <?php if (empty($contasPagarVencer7)): ?>
                        <p class="p-3 text-center text-muted">Nenhuma conta a vencer</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Vencimento</th>
                                        <th class="text-end">Valor</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($contasPagarVencer7, 0, 5) as $conta): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($conta['descricao']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></td>
                                        <td class="text-end">R$ <?= number_format($conta['valor_pendente'], 2, ',', '.') ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/?page=financeiro&action=conta-pagar-show&id=<?= $conta['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
    </div>

    <!-- Fluxo de Caixa 30 dias -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-area"></i> Fluxo de Caixa - Próximos 30 dias
                </div>
                <div class="card-body">
                    <canvas id="chartFluxoCaixa" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Boletos Pendentes -->
    <?php if (!empty($boletosPendentes)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-barcode"></i> Boletos Pendentes (<?= count($boletosPendentes) ?>)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Beneficiário</th>
                                    <th>Vencimento</th>
                                    <th class="text-end">Valor</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($boletosPendentes, 0, 10) as $boleto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($boleto['nosso_numero']) ?></td>
                                    <td><?= htmlspecialchars($boleto['beneficiario_nome']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($boleto['data_vencimento'])) ?></td>
                                    <td class="text-end">R$ <?= number_format($boleto['valor_original'], 2, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-warning"><?= ucfirst($boleto['status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/?page=financeiro&action=boleto-show&id=<?= $boleto['id'] ?>" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-print"></i> Imprimir
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Fluxo de Caixa Chart
<?php if (!empty($fluxo30Dias)): ?>
const ctxFluxo = document.getElementById('chartFluxoCaixa');
if (ctxFluxo) {
    new Chart(ctxFluxo, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($fluxo30Dias, 'data')) ?>,
            datasets: [
                {
                    label: 'Receitas',
                    data: <?= json_encode(array_column($fluxo30Dias, 'receitas')) ?>,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Despesas',
                    data: <?= json_encode(array_column($fluxo30Dias, 'despesas')) ?>,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Saldo Acumulado',
                    data: <?= json_encode(array_column($fluxo30Dias, 'saldo_acumulado')) ?>,
                    borderColor: 'rgb(0, 123, 255)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
