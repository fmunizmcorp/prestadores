<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-file-invoice-dollar"></i> Faturamento do Contrato</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=contratos">Contratos</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=contratos&action=show&id=<?= $contrato['id'] ?>">
                        Contrato <?= htmlspecialchars($contrato['numero']) ?>
                    </a></li>
                    <li class="breadcrumb-item active">Faturamento</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <?php if ($contrato['gerar_fatura_automatica']): ?>
                <button type="button" class="btn btn-primary" onclick="gerarFaturaManual()">
                    <i class="fas fa-plus"></i> Gerar Fatura Manual
                </button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/?page=contratos&action=show&id=<?= $contrato['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Valor Total do Contrato</h6>
                    <h3 class="mb-0">R$ <?= number_format($faturamento['valor_contrato'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Faturamento Realizado</h6>
                    <h3 class="mb-0">R$ <?= number_format($faturamento['faturamento_realizado'], 2, ',', '.') ?></h3>
                    <small><?= number_format($faturamento['percentual_faturado'], 2) ?>% do total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Faturamento Pendente</h6>
                    <h3 class="mb-0">R$ <?= number_format($faturamento['faturamento_pendente'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Saldo a Faturar</h6>
                    <h3 class="mb-0">R$ <?= number_format($faturamento['saldo_faturar'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicadores -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-tachometer-alt"></i> Indicadores de Faturamento
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6>Progresso de Faturamento</h6>
                    <div class="progress mb-2" style="height: 30px;">
                        <?php 
                        $percentual = $faturamento['percentual_faturado'];
                        $corBarra = $percentual >= 75 ? 'success' : ($percentual >= 50 ? 'info' : ($percentual >= 25 ? 'warning' : 'danger'));
                        ?>
                        <div class="progress-bar bg-<?= $corBarra ?>" role="progressbar" 
                             style="width: <?= $percentual ?>%">
                            <?= number_format($percentual, 1) ?>%
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6>Total de Faturas</h6>
                    <p class="mb-0">
                        <span class="badge bg-primary fs-5"><?= $faturamento['total_faturas'] ?></span> faturas emitidas<br>
                        <span class="badge bg-success"><?= $faturamento['faturas_pagas'] ?></span> pagas |
                        <span class="badge bg-warning"><?= $faturamento['faturas_pendentes'] ?></span> pendentes
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($inadimplencia['total_faturas_atrasadas'] > 0): ?>
    <!-- Alerta de Inadimplência -->
    <div class="alert alert-danger">
        <h5><i class="fas fa-exclamation-triangle"></i> Alerta de Inadimplência</h5>
        <div class="row">
            <div class="col-md-3">
                <strong>Faturas Atrasadas:</strong> <?= $inadimplencia['total_faturas_atrasadas'] ?>
            </div>
            <div class="col-md-3">
                <strong>Valor Total:</strong> R$ <?= number_format($inadimplencia['valor_total_atrasado'], 2, ',', '.') ?>
            </div>
            <div class="col-md-3">
                <strong>Dias de Atraso Máximo:</strong> <?= $inadimplencia['dias_atraso_maximo'] ?> dias
            </div>
            <div class="col-md-3">
                <?php
                $risco = $inadimplencia['classificacao_risco'];
                $badgeRisco = match($risco) {
                    'sem_risco' => 'success',
                    'baixo' => 'info',
                    'medio' => 'warning',
                    'alto' => 'danger',
                    default => 'secondary'
                };
                $textoRisco = match($risco) {
                    'sem_risco' => 'Sem Risco',
                    'baixo' => 'Risco Baixo',
                    'medio' => 'Risco Médio',
                    'alto' => 'Risco Alto',
                    default => 'Não Avaliado'
                };
                ?>
                <strong>Classificação:</strong> <span class="badge bg-<?= $badgeRisco ?>"><?= $textoRisco ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Coluna Esquerda: Histórico Mensal -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-line"></i> Histórico de Faturamento Mensal
                </div>
                <div class="card-body">
                    <canvas id="graficoFaturamento" height="250"></canvas>
                </div>
            </div>

            <!-- Tabela de Histórico -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history"></i> Detalhamento Mensal
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th class="text-end">Valor Recebido</th>
                                    <th class="text-end">Valor Pendente</th>
                                    <th class="text-end">Total Faturado</th>
                                    <th class="text-center">Faturas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $mes): ?>
                                <tr>
                                    <td><strong><?= $mes['mes_formatado'] ?></strong></td>
                                    <td class="text-end text-success">
                                        R$ <?= number_format($mes['valor_recebido'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end text-warning">
                                        R$ <?= number_format($mes['valor_pendente'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
                                        <strong>R$ <?= number_format($mes['valor_total'], 2, ',', '.') ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= $mes['quantidade_faturas'] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Faturas Pendentes e Projeção -->
        <div class="col-lg-5">
            <!-- Faturas Pendentes -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-clock"></i> Faturas Pendentes
                </div>
                <div class="card-body">
                    <?php if (empty($faturas_pendentes)): ?>
                        <p class="text-muted mb-0">Nenhuma fatura pendente.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($faturas_pendentes as $fatura): ?>
                            <div class="list-group-item <?= $fatura['situacao'] === 'vencida' ? 'list-group-item-danger' : ($fatura['situacao'] === 'vencendo' ? 'list-group-item-warning' : '') ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($fatura['descricao']) ?></h6>
                                        <small>
                                            Vencimento: <?= date('d/m/Y', strtotime($fatura['data_vencimento'])) ?>
                                            <?php if ($fatura['dias_atraso'] > 0): ?>
                                                <br><span class="text-danger"><?= $fatura['dias_atraso'] ?> dias em atraso</span>
                                            <?php elseif ($fatura['situacao'] === 'vencendo'): ?>
                                                <br><span class="text-warning">Vence em breve</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="mb-0">R$ <?= number_format($fatura['valor_final'], 2, ',', '.') ?></h5>
                                        <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber&show=<?= $fatura['id'] ?>" 
                                           class="btn btn-sm btn-primary mt-2">
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Projeção de Receita -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-crystal-ball"></i> Projeção de Receita (12 meses)
                </div>
                <div class="card-body">
                    <?php if (empty($projecao)): ?>
                        <p class="text-muted mb-0">Não há projeção disponível para este contrato.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mês</th>
                                        <th class="text-end">Valor Projetado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalProjecao = 0;
                                    foreach ($projecao as $mes): 
                                        $totalProjecao += $mes['valor_projetado'];
                                    ?>
                                    <tr>
                                        <td><?= $mes['mes_formatado'] ?></td>
                                        <td class="text-end">R$ <?= number_format($mes['valor_projetado'], 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td><strong>Total Projetado:</strong></td>
                                        <td class="text-end"><strong>R$ <?= number_format($totalProjecao, 2, ',', '.') ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Configurações de Faturamento -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cog"></i> Configurações de Faturamento
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Faturamento Automático:</td>
                            <td class="text-end">
                                <?php if ($contrato['gerar_fatura_automatica']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Dia de Vencimento:</td>
                            <td class="text-end"><strong><?= $contrato['dia_vencimento_fatura'] ?? 10 ?></strong></td>
                        </tr>
                        <tr>
                            <td>Valor Mensal:</td>
                            <td class="text-end">
                                <strong>R$ <?= number_format($contrato['valor_mensal'] ?? ($contrato['valor_total'] / 12), 2, ',', '.') ?></strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Gráfico de Faturamento Mensal
    const ctx = document.getElementById('graficoFaturamento').getContext('2d');
    
    const labels = <?= json_encode(array_column($historico, 'mes_formatado')) ?>;
    const recebido = <?= json_encode(array_column($historico, 'valor_recebido')) ?>;
    const pendente = <?= json_encode(array_column($historico, 'valor_pendente')) ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Recebido',
                data: recebido,
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgb(40, 167, 69)',
                borderWidth: 1
            }, {
                label: 'Pendente',
                data: pendente,
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: 'rgb(255, 193, 7)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + 
                                   context.parsed.y.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        }
                    }
                }
            }
        }
    });
});

function gerarFaturaManual() {
    if (confirm('Deseja gerar uma fatura manual para este contrato?')) {
        window.location.href = '<?= BASE_URL ?>/?page=contratos&action=gerar-fatura&id=<?= $contrato['id'] ?>';
    }
}
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
