<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-chart-line"></i> Financeiro do Projeto</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=projetos">Projetos</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>">
                        <?= htmlspecialchars($projeto['nome']) ?>
                    </a></li>
                    <li class="breadcrumb-item active">Financeiro</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="<?= BASE_URL ?>/?page=projetos&action=financeiro-export&id=<?= $projeto['id'] ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar
            </a>
        </div>
    </div>

    <!-- Cards de Resumo Financeiro -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Orçamento Total</h6>
                    <h3 class="mb-0">R$ <?= number_format($projeto['orcamento_total'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Custo Realizado</h6>
                    <h3 class="mb-0">R$ <?= number_format($financeiro['custos']['custo_total_realizado'], 2, ',', '.') ?></h3>
                    <small><?= number_format($financeiro['performance']['percentual_orcamento_usado'], 2) ?>% do orçamento</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Receita Realizada</h6>
                    <h3 class="mb-0">R$ <?= number_format($financeiro['receitas']['receitas_faturadas'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card <?= $financeiro['margem']['resultado'] >= 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Resultado</h6>
                    <h3 class="mb-0">R$ <?= number_format($financeiro['margem']['resultado'], 2, ',', '.') ?></h3>
                    <small>Margem: <?= number_format($financeiro['margem']['margem_percentual'], 2) ?>%</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicadores de Performance -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-tachometer-alt"></i> Indicadores de Performance
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Performance Orçamentária</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <?php 
                        $percentualUsado = $financeiro['performance']['percentual_orcamento_usado'];
                        $corBarra = $percentualUsado > 100 ? 'danger' : ($percentualUsado > 90 ? 'warning' : 'success');
                        ?>
                        <div class="progress-bar bg-<?= $corBarra ?>" role="progressbar" 
                             style="width: <?= min($percentualUsado, 100) ?>%">
                            <?= number_format($percentualUsado, 1) ?>%
                        </div>
                    </div>
                    <p class="mb-0">
                        <strong>Situação:</strong> 
                        <?php
                        $performance = $financeiro['performance']['performance'];
                        $badgeClass = match($performance) {
                            'bom' => 'success',
                            'atencao' => 'warning',
                            'ruim' => 'danger',
                            default => 'secondary'
                        };
                        $textoPerformance = match($performance) {
                            'bom' => 'Dentro do Orçamento',
                            'atencao' => 'Atenção - Próximo do Limite',
                            'ruim' => 'Orçamento Estourado',
                            default => 'Não Avaliado'
                        };
                        ?>
                        <span class="badge bg-<?= $badgeClass ?>"><?= $textoPerformance ?></span>
                    </p>
                </div>
                <div class="col-md-3">
                    <h6>ROI (Retorno sobre Investimento)</h6>
                    <h3 class="mb-0 <?= $financeiro['performance']['roi'] >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($financeiro['performance']['roi'], 2) ?>%
                    </h3>
                </div>
                <div class="col-md-3">
                    <h6>Variação de Orçamento</h6>
                    <h3 class="mb-0 <?= $financeiro['performance']['variacao_orcamento'] >= 0 ? 'text-success' : 'text-danger' ?>">
                        R$ <?= number_format(abs($financeiro['performance']['variacao_orcamento']), 2, ',', '.') ?>
                    </h3>
                    <small><?= $financeiro['performance']['variacao_orcamento'] >= 0 ? 'Economia' : 'Excesso' ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna Esquerda -->
        <div class="col-lg-6">
            <!-- Custos Detalhados -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-arrow-down"></i> Custos Detalhados
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Custos Diretos:</td>
                                <td class="text-end"><strong>R$ <?= number_format($financeiro['custos']['custos_diretos'], 2, ',', '.') ?></strong></td>
                            </tr>
                            <tr>
                                <td>Custos Indiretos:</td>
                                <td class="text-end">R$ <?= number_format($financeiro['custos']['custos_indiretos'], 2, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Mão de Obra:</td>
                                <td class="text-end">R$ <?= number_format($financeiro['custos']['custos_mao_obra'], 2, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Materiais:</td>
                                <td class="text-end">R$ <?= number_format($financeiro['custos']['custos_materiais'], 2, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Serviços Terceirizados:</td>
                                <td class="text-end">R$ <?= number_format($financeiro['custos']['custos_servicos'], 2, ',', '.') ?></td>
                            </tr>
                            <tr class="table-light">
                                <td><strong>Custos Pendentes:</strong></td>
                                <td class="text-end"><strong class="text-warning">R$ <?= number_format($financeiro['custos']['custos_pendentes'], 2, ',', '.') ?></strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>TOTAL CUSTOS:</strong></td>
                                <td class="text-end"><strong>R$ <?= number_format($financeiro['custos']['custo_total_realizado'], 2, ',', '.') ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mb-0"><small class="text-muted">Total de <?= $financeiro['custos']['total_contas'] ?> contas a pagar</small></p>
                </div>
            </div>

            <!-- Top Fornecedores -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-truck"></i> Top 10 Fornecedores
                </div>
                <div class="card-body">
                    <?php if (empty($financeiro['top_fornecedores'])): ?>
                        <p class="text-muted mb-0">Nenhum fornecedor registrado ainda.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fornecedor</th>
                                        <th class="text-center">Contas</th>
                                        <th class="text-end">Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($financeiro['top_fornecedores'] as $fornecedor): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($fornecedor['fornecedor_nome']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($fornecedor['cpf_cnpj']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= $fornecedor['quantidade_contas'] ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong>R$ <?= number_format($fornecedor['valor_total'], 2, ',', '.') ?></strong>
                                            <?php if ($fornecedor['valor_pendente'] > 0): ?>
                                                <br><small class="text-warning">Pendente: R$ <?= number_format($fornecedor['valor_pendente'], 2, ',', '.') ?></small>
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

        <!-- Coluna Direita -->
        <div class="col-lg-6">
            <!-- Receitas Detalhadas -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-arrow-up"></i> Receitas Detalhadas
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Receitas Faturadas:</td>
                                <td class="text-end"><strong>R$ <?= number_format($financeiro['receitas']['receitas_faturadas'], 2, ',', '.') ?></strong></td>
                            </tr>
                            <tr>
                                <td>Receitas a Faturar:</td>
                                <td class="text-end">R$ <?= number_format($financeiro['receitas']['receitas_a_faturar'], 2, ',', '.') ?></td>
                            </tr>
                            <tr class="table-warning">
                                <td>Receitas Vencidas:</td>
                                <td class="text-end"><strong class="text-danger">R$ <?= number_format($financeiro['receitas']['receitas_vencidas'], 2, ',', '.') ?></strong></td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>TOTAL RECEITAS:</strong></td>
                                <td class="text-end"><strong>R$ <?= number_format($financeiro['receitas']['receita_total'], 2, ',', '.') ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mb-0">
                        <small class="text-muted">
                            Total de <?= $financeiro['receitas']['total_contas'] ?> contas a receber |
                            <?= $financeiro['receitas']['total_notas_fiscais'] ?> notas fiscais
                        </small>
                    </p>
                </div>
            </div>

            <!-- Gráfico de Evolução Mensal -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-area"></i> Evolução Mensal de Custos
                </div>
                <div class="card-body">
                    <canvas id="graficoEvolucaoMensal" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Contas Pendentes -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-exclamation-triangle"></i> Contas a Pagar Pendentes
                </div>
                <div class="card-body">
                    <?php if (empty($financeiro['contas_pendentes_pagar'])): ?>
                        <p class="text-muted mb-0">Nenhuma conta a pagar pendente.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Vencimento</th>
                                        <th class="text-end">Valor</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($financeiro['contas_pendentes_pagar'] as $conta): ?>
                                    <tr class="<?= $conta['dias_atraso'] > 0 ? 'table-danger' : '' ?>">
                                        <td>
                                            <?= htmlspecialchars($conta['descricao']) ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($conta['categoria_nome']) ?></small>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?>
                                            <?php if ($conta['dias_atraso'] > 0): ?>
                                                <br><small class="text-danger"><?= $conta['dias_atraso'] ?> dias em atraso</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <strong>R$ <?= number_format($conta['valor_final'], 2, ',', '.') ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar&show=<?= $conta['id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                Ver
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

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-clock"></i> Contas a Receber Pendentes
                </div>
                <div class="card-body">
                    <?php if (empty($financeiro['contas_pendentes_receber'])): ?>
                        <p class="text-muted mb-0">Nenhuma conta a receber pendente.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Vencimento</th>
                                        <th class="text-end">Valor</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($financeiro['contas_pendentes_receber'] as $conta): ?>
                                    <tr class="<?= $conta['dias_atraso'] > 0 ? 'table-warning' : '' ?>">
                                        <td>
                                            <?= htmlspecialchars($conta['descricao']) ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($conta['categoria_nome']) ?></small>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?>
                                            <?php if ($conta['dias_atraso'] > 0): ?>
                                                <br><small class="text-warning"><?= $conta['dias_atraso'] ?> dias em atraso</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <strong>R$ <?= number_format($conta['valor_final'], 2, ',', '.') ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber&show=<?= $conta['id'] ?>" 
                                               class="btn btn-sm btn-success">
                                                Ver
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
</div>

<script>
$(document).ready(function() {
    // Gráfico de Evolução Mensal
    const ctx = document.getElementById('graficoEvolucaoMensal').getContext('2d');
    
    const labels = <?= json_encode(array_column($financeiro['consolidacao_mensal'], 'mes_formatado')) ?>;
    const custos = <?= json_encode(array_column($financeiro['consolidacao_mensal'], 'custo_mes')) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Custos Mensais',
                data: custos,
                borderColor: 'rgb(220, 53, 69)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
