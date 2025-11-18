<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="container-fluid py-4">
    <h1 class="h2 mb-4">Relatórios Financeiros</h1>
    
    <!-- Filtro de Período -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="relatorios-financeiros">
                <div class="col-md-3">
                    <label>Data Início</label>
                    <input type="date" class="form-control" name="data_inicio" value="<?= $_GET['data_inicio'] ?? date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label>Data Fim</label>
                    <input type="date" class="form-control" name="data_fim" value="<?= $_GET['data_fim'] ?? date('Y-m-t') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Cards Resumo -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Receitas</h6>
                    <h2>R$ <?= number_format($receitas, 2, ',', '.') ?></h2>
                    <small>Total de pagamentos recebidos</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Despesas</h6>
                    <h2>R$ <?= number_format($despesas, 2, ',', '.') ?></h2>
                    <small>Total de custos</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-<?= $saldo >= 0 ? 'primary' : 'warning' ?> text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Saldo</h6>
                    <h2>R$ <?= number_format($saldo, 2, ',', '.') ?></h2>
                    <small><?= $saldo >= 0 ? 'Superávit' : 'Déficit' ?></small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas Pagamentos -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5>Estatísticas de Pagamentos</h5></div>
                <div class="card-body">
                    <p><strong>Total:</strong> <?= $statsPagamentos['total'] ?? 0 ?> pagamentos</p>
                    <p><strong>Confirmados:</strong> <?= $statsPagamentos['confirmados'] ?? 0 ?></p>
                    <p><strong>Pendentes:</strong> <?= $statsPagamentos['pendentes'] ?? 0 ?></p>
                    <p><strong>Taxas Totais:</strong> R$ <?= number_format($statsPagamentos['total_taxas'] ?? 0, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5>Estatísticas de Custos</h5></div>
                <div class="card-body">
                    <p><strong>Total:</strong> <?= $statsCustos['total'] ?? 0 ?> custos</p>
                    <p><strong>Pagos:</strong> <?= $statsCustos['pagos'] ?? 0 ?></p>
                    <p><strong>Pendentes:</strong> <?= $statsCustos['pendentes'] ?? 0 ?></p>
                    <p><strong>Valor Pendente:</strong> R$ <?= number_format($statsCustos['valor_total_pendente'] ?? 0, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pagamentos por Forma -->
    <?php if (!empty($pagamentosPorForma)): ?>
    <div class="card">
        <div class="card-header"><h5>Pagamentos por Forma de Pagamento</h5></div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Forma</th><th>Quantidade</th><th>Valor Total</th><th>Valor Médio</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pagamentosPorForma as $fp): ?>
                    <tr>
                        <td><?= ucfirst($fp['forma_pagamento']) ?></td>
                        <td><?= $fp['quantidade'] ?></td>
                        <td>R$ <?= number_format($fp['valor_total'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($fp['valor_medio'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
