<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-chart-line"></i> Fluxo de Caixa</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item active">Fluxo de Caixa</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button type="button" class="btn btn-success" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
    </div>

    <!-- Filtros de Período -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Período e Filtros
        </div>
        <div class="card-body">
            <form method="GET" id="filtrosForm">
                <input type="hidden" name="page" value="financeiro">
                <input type="hidden" name="action" value="fluxo-caixa">
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="data_inicio" class="form-label">Data Início *</label>
                        <input type="date" class="form-control" name="data_inicio" id="data_inicio"
                               value="<?= $_GET['data_inicio'] ?? date('Y-m-01') ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="data_fim" class="form-label">Data Fim *</label>
                        <input type="date" class="form-control" name="data_fim" id="data_fim"
                               value="<?= $_GET['data_fim'] ?? date('Y-m-t') ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="agrupamento" class="form-label">Agrupamento</label>
                        <select class="form-select" name="agrupamento" id="agrupamento">
                            <option value="dia" <?= (isset($_GET['agrupamento']) && $_GET['agrupamento'] === 'dia') ? 'selected' : '' ?>>Por Dia</option>
                            <option value="semana" <?= (isset($_GET['agrupamento']) && $_GET['agrupamento'] === 'semana') ? 'selected' : '' ?>>Por Semana</option>
                            <option value="mes" <?= (isset($_GET['agrupamento']) && $_GET['agrupamento'] === 'mes') ? 'selected' : 'selected' ?>>Por Mês</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Atualizar
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="atalhosPeriodo('mes_atual')">
                            Mês Atual
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Saldo Inicial</h6>
                    <h3 class="card-title mb-0">R$ <?= number_format($resumo['saldo_inicial'] ?? 0, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Entradas</h6>
                    <h3 class="card-title mb-0">
                        <i class="fas fa-arrow-up"></i>
                        R$ <?= number_format($resumo['total_entradas'] ?? 0, 2, ',', '.') ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Saídas</h6>
                    <h3 class="card-title mb-0">
                        <i class="fas fa-arrow-down"></i>
                        R$ <?= number_format($resumo['total_saidas'] ?? 0, 2, ',', '.') ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-<?= ($resumo['saldo_final'] ?? 0) >= 0 ? 'primary' : 'warning' ?> text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Saldo Final</h6>
                    <h3 class="card-title mb-0">R$ <?= number_format($resumo['saldo_final'] ?? 0, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Fluxo -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-chart-area"></i> Evolução do Fluxo de Caixa
        </div>
        <div class="card-body">
            <canvas id="graficoFluxoCaixa" style="height: 300px;"></canvas>
        </div>
    </div>

    <!-- Tabela Detalhada -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-table"></i> Movimentação Detalhada
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm" id="tabelaFluxo">
                    <thead class="table-dark">
                        <tr>
                            <th>Período</th>
                            <th class="text-end">Saldo Inicial</th>
                            <th class="text-end">Entradas</th>
                            <th class="text-end">Saídas</th>
                            <th class="text-end">Saldo Final</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fluxo)): ?>
                            <?php foreach ($fluxo as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['periodo_label']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($item['data_inicio']) ?> a <?= htmlspecialchars($item['data_fim']) ?></small>
                                </td>
                                <td class="text-end">
                                    R$ <?= number_format($item['saldo_inicial'], 2, ',', '.') ?>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    + R$ <?= number_format($item['total_entradas'], 2, ',', '.') ?>
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    - R$ <?= number_format($item['total_saidas'], 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <span class="<?= $item['saldo_final'] >= 0 ? 'text-primary' : 'text-danger' ?>">
                                        R$ <?= number_format($item['saldo_final'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['saldo_final'] >= 0): ?>
                                        <span class="badge bg-success">Positivo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Negativo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhuma movimentação encontrada no período selecionado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr class="fw-bold">
                            <td>TOTAIS</td>
                            <td class="text-end">R$ <?= number_format($resumo['saldo_inicial'] ?? 0, 2, ',', '.') ?></td>
                            <td class="text-end text-success">+ R$ <?= number_format($resumo['total_entradas'] ?? 0, 2, ',', '.') ?></td>
                            <td class="text-end text-danger">- R$ <?= number_format($resumo['total_saidas'] ?? 0, 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format($resumo['saldo_final'] ?? 0, 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Projeção Futura -->
    <?php if (!empty($projecao)): ?>
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <i class="fas fa-crystal-ball"></i> Projeção (Próximos 30 dias)
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Entradas Previstas</h6>
                            <h4 class="text-success">R$ <?= number_format($projecao['entradas_previstas'] ?? 0, 2, ',', '.') ?></h4>
                            <small class="text-muted"><?= $projecao['qtd_contas_receber'] ?? 0 ?> contas a receber</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Saídas Previstas</h6>
                            <h4 class="text-danger">R$ <?= number_format($projecao['saidas_previstas'] ?? 0, 2, ',', '.') ?></h4>
                            <small class="text-muted"><?= $projecao['qtd_contas_pagar'] ?? 0 ?> contas a pagar</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Saldo Projetado</h6>
                            <h4 class="<?= ($projecao['saldo_projetado'] ?? 0) >= 0 ? 'text-primary' : 'text-danger' ?>">
                                R$ <?= number_format($projecao['saldo_projetado'] ?? 0, 2, ',', '.') ?>
                            </h4>
                            <small class="text-muted">Saldo atual + projeção</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Preparar dados para o gráfico
const labels = <?= json_encode(array_column($fluxo ?? [], 'periodo_label')) ?>;
const entradasData = <?= json_encode(array_column($fluxo ?? [], 'total_entradas')) ?>;
const saidasData = <?= json_encode(array_column($fluxo ?? [], 'total_saidas')) ?>;
const saldoData = <?= json_encode(array_column($fluxo ?? [], 'saldo_final')) ?>;

// Criar gráfico
const ctx = document.getElementById('graficoFluxoCaixa').getContext('2d');
const graficoFluxoCaixa = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Entradas',
            data: entradasData,
            borderColor: 'rgb(40, 167, 69)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }, {
            label: 'Saídas',
            data: saidasData,
            borderColor: 'rgb(220, 53, 69)',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }, {
            label: 'Saldo',
            data: saldoData,
            borderColor: 'rgb(0, 123, 255)',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});

// Atalhos de período
function atalhosPeriodo(tipo) {
    const hoje = new Date();
    let dataInicio, dataFim;
    
    switch(tipo) {
        case 'mes_atual':
            dataInicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            dataFim = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
            break;
        case 'mes_anterior':
            dataInicio = new Date(hoje.getFullYear(), hoje.getMonth() - 1, 1);
            dataFim = new Date(hoje.getFullYear(), hoje.getMonth(), 0);
            break;
        case 'trimestre':
            dataInicio = new Date(hoje.getFullYear(), hoje.getMonth() - 2, 1);
            dataFim = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
            break;
    }
    
    document.getElementById('data_inicio').value = dataInicio.toISOString().split('T')[0];
    document.getElementById('data_fim').value = dataFim.toISOString().split('T')[0];
    document.getElementById('filtrosForm').submit();
}

// Exportar para Excel (simulado)
function exportarExcel() {
    alert('Função de exportação para Excel será implementada no backend.');
    // window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=fluxo-caixa&do=exportar-excel&' + new URLSearchParams(new FormData(document.getElementById('filtrosForm')));
}
</script>

<style media="print">
    .btn, .card-header .btn, nav, .breadcrumb {
        display: none !important;
    }
</style>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
