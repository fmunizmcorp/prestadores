<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-balance-scale"></i> Balancete de Verificação</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=relatorios">Relatórios</a></li>
                    <li class="breadcrumb-item active">Balancete</li>
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

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-filter"></i> Período</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="financeiro">
                <input type="hidden" name="action" value="relatorios">
                <input type="hidden" name="report" value="balancete">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Data Início *</label>
                        <input type="date" class="form-control" name="data_inicio" required
                               value="<?= $_GET['data_inicio'] ?? date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Data Fim *</label>
                        <input type="date" class="form-control" name="data_fim" required
                               value="<?= $_GET['data_fim'] ?? date('Y-m-t') ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Agrupar por:</label>
                        <select class="form-select" name="agrupamento">
                            <option value="categoria">Categoria</option>
                            <option value="centro_custo">Centro de Custo</option>
                            <option value="tipo">Tipo (Receita/Despesa)</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-chart-line"></i> Gerar Balancete
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Balancete -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-file-invoice-dollar"></i>
            Balancete - Período: <?= date('d/m/Y', strtotime($_GET['data_inicio'] ?? date('Y-m-01'))) ?> a <?= date('d/m/Y', strtotime($_GET['data_fim'] ?? date('Y-m-t'))) ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Conta</th>
                            <th class="text-end">Saldo Anterior</th>
                            <th class="text-end">Débitos</th>
                            <th class="text-end">Créditos</th>
                            <th class="text-end">Saldo Atual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_saldo_anterior = 0;
                        $total_debitos = 0;
                        $total_creditos = 0;
                        $total_saldo_atual = 0;

                        if (!empty($balancete)):
                            foreach ($balancete as $item):
                                $total_saldo_anterior += $item['saldo_anterior'];
                                $total_debitos += $item['total_debitos'];
                                $total_creditos += $item['total_creditos'];
                                $total_saldo_atual += $item['saldo_atual'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['codigo']) ?></td>
                            <td>
                                <?= str_repeat('&nbsp;&nbsp;&nbsp;', $item['nivel'] - 1) ?>
                                <?= htmlspecialchars($item['nome']) ?>
                                <?php if (!empty($item['centro_custo'])): ?>
                                <br><small class="text-muted">Centro de Custo: <?= htmlspecialchars($item['centro_custo']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <span class="<?= $item['saldo_anterior'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    R$ <?= number_format($item['saldo_anterior'], 2, ',', '.') ?>
                                </span>
                            </td>
                            <td class="text-end text-success">
                                R$ <?= number_format($item['total_debitos'], 2, ',', '.') ?>
                            </td>
                            <td class="text-end text-danger">
                                R$ <?= number_format($item['total_creditos'], 2, ',', '.') ?>
                            </td>
                            <td class="text-end fw-bold">
                                <span class="<?= $item['saldo_atual'] >= 0 ? 'text-primary' : 'text-danger' ?>">
                                    R$ <?= number_format($item['saldo_atual'], 2, ',', '.') ?>
                                </span>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Nenhuma movimentação encontrada no período selecionado.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td colspan="2">TOTAIS</td>
                            <td class="text-end">R$ <?= number_format($total_saldo_anterior, 2, ',', '.') ?></td>
                            <td class="text-end text-success">R$ <?= number_format($total_debitos, 2, ',', '.') ?></td>
                            <td class="text-end text-danger">R$ <?= number_format($total_creditos, 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format($total_saldo_atual, 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center">
                                <?php if (abs($total_debitos - $total_creditos) < 0.01): ?>
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle"></i> Balancete Fechado (Débitos = Créditos)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Balancete Desbalanceado (Diferença: R$ <?= number_format(abs($total_debitos - $total_creditos), 2, ',', '.') ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Resumo -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total de Contas</h6>
                            <h3><?= count($balancete ?? []) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Total Movimentado</h6>
                            <h3>R$ <?= number_format($total_debitos + $total_creditos, 2, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-<?= $total_saldo_atual >= 0 ? 'primary' : 'danger' ?> text-white">
                        <div class="card-body text-center">
                            <h6>Saldo Total</h6>
                            <h3>R$ <?= number_format($total_saldo_atual, 2, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportarExcel() {
    alert('Função de exportação para Excel será implementada no backend.');
}
</script>

<style media="print">
    .btn, nav, .breadcrumb, .card-header .btn {
        display: none !important;
    }
</style>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
