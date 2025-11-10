<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-money-bill-wave"></i> Custos da Atividade</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=atividades">Atividades</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=atividades&action=show&id=<?= $atividade['id'] ?>">
                        <?= htmlspecialchars($atividade['titulo']) ?>
                    </a></li>
                    <li class="breadcrumb-item active">Custos</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=atividades&action=show&id=<?= $atividade['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Custo Estimado</h6>
                    <h3 class="mb-0">R$ <?= number_format($custos['custo_estimado'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Custo Realizado</h6>
                    <h3 class="mb-0">R$ <?= number_format($custos['custo_realizado'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card <?= $custos['variacao_custo'] >= 0 ? 'bg-success' : 'bg-warning' ?> text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Variação</h6>
                    <h3 class="mb-0">R$ <?= number_format(abs($custos['variacao_custo']), 2, ',', '.') ?></h3>
                    <small><?= $custos['variacao_custo'] >= 0 ? 'Economia' : 'Excesso' ?> (<?= number_format(abs($custos['variacao_percentual']), 2) ?>%)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Custo Pendente</h6>
                    <h3 class="mb-0">R$ <?= number_format($custos['custo_pendente'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Análise de Variação -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-chart-pie"></i> Análise de Variação de Custos
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Performance Orçamentária</h6>
                    <?php 
                    $percentualUsado = ($custos['custo_estimado'] > 0) 
                        ? ($custos['custo_realizado'] / $custos['custo_estimado'] * 100) 
                        : 0;
                    $corBarra = $percentualUsado > 100 ? 'danger' : ($percentualUsado > 90 ? 'warning' : 'success');
                    ?>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-<?= $corBarra ?>" role="progressbar" 
                             style="width: <?= min($percentualUsado, 100) ?>%">
                            <?= number_format($percentualUsado, 1) ?>%
                        </div>
                    </div>
                    
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td>Status:</td>
                            <td class="text-end">
                                <?php
                                $status = $variacao['status'];
                                $badgeStatus = match($status) {
                                    'ok' => 'success',
                                    'alerta' => 'warning',
                                    'atencao' => 'danger',
                                    default => 'secondary'
                                };
                                $textoStatus = match($status) {
                                    'ok' => 'Dentro do Orçamento',
                                    'alerta' => 'Alerta - Próximo do Limite',
                                    'atencao' => 'Atenção - Orçamento Estourado',
                                    default => 'Não Avaliado'
                                };
                                ?>
                                <span class="badge bg-<?= $badgeStatus ?>"><?= $textoStatus ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>Performance:</td>
                            <td class="text-end">
                                <?php
                                $performance = $variacao['performance'];
                                $badgePerf = match($performance) {
                                    'abaixo_orcamento' => 'success',
                                    'no_prazo' => 'info',
                                    'acima_orcamento' => 'danger',
                                    default => 'secondary'
                                };
                                $textoPerf = match($performance) {
                                    'abaixo_orcamento' => 'Abaixo do Orçamento',
                                    'no_prazo' => 'No Orçamento',
                                    'acima_orcamento' => 'Acima do Orçamento',
                                    default => 'Não Avaliado'
                                };
                                ?>
                                <span class="badge bg-<?= $badgePerf ?>"><?= $textoPerf ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6>Custo por Hora</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Custo/Hora Estimado:</td>
                            <td class="text-end"><strong>R$ <?= number_format($custo_hora['custo_hora_estimado'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr>
                            <td>Custo/Hora Realizado:</td>
                            <td class="text-end"><strong>R$ <?= number_format($custo_hora['custo_hora_realizado'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr class="<?= $custo_hora['variacao_custo_hora'] >= 0 ? 'table-danger' : 'table-success' ?>">
                            <td>Variação Custo/Hora:</td>
                            <td class="text-end">
                                <strong>R$ <?= number_format(abs($custo_hora['variacao_custo_hora']), 2, ',', '.') ?></strong>
                                <?= $custo_hora['variacao_custo_hora'] >= 0 ? '↑' : '↓' ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Horas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-clock"></i> Controle de Horas
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Horas Estimadas:</td>
                            <td class="text-end"><strong><?= number_format($custo_hora['horas_estimadas'], 2, ',', '.') ?>h</strong></td>
                        </tr>
                        <tr>
                            <td>Horas Realizadas:</td>
                            <td class="text-end"><strong><?= number_format($custo_hora['horas_realizadas'], 2, ',', '.') ?>h</strong></td>
                        </tr>
                        <tr>
                            <td>Horas Restantes:</td>
                            <td class="text-end"><strong><?= number_format($custo_hora['horas_restantes'], 2, ',', '.') ?>h</strong></td>
                        </tr>
                        <tr class="table-light">
                            <td>Percentual Usado:</td>
                            <td class="text-end"><strong><?= number_format($custo_hora['percentual_horas_usado'], 2) ?>%</strong></td>
                        </tr>
                    </table>
                    
                    <div class="progress" style="height: 20px;">
                        <?php 
                        $percHoras = $custo_hora['percentual_horas_usado'];
                        $corHoras = $percHoras > 100 ? 'danger' : ($percHoras > 90 ? 'warning' : 'success');
                        ?>
                        <div class="progress-bar bg-<?= $corHoras ?>" role="progressbar" 
                             style="width: <?= min($percHoras, 100) ?>%">
                            <?= number_format($percHoras, 1) ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-file-invoice"></i> Contas a Pagar
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Total de Contas:</td>
                            <td class="text-end"><strong><?= $custos['total_contas'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Contas Pagas:</td>
                            <td class="text-end"><strong>R$ <?= number_format($custos['custo_pago'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr>
                            <td>Contas Pendentes:</td>
                            <td class="text-end"><strong class="text-warning">R$ <?= number_format($custos['custo_pendente'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Total:</strong></td>
                            <td class="text-end"><strong>R$ <?= number_format($custos['custo_pago'] + $custos['custo_pendente'], 2, ',', '.') ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Alocação de Recursos -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-users"></i> Equipe Alocada
                </div>
                <div class="card-body">
                    <?php if (empty($alocacao['equipe'])): ?>
                        <p class="text-muted mb-0">Nenhum membro da equipe alocado ainda.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Membro</th>
                                        <th>Função</th>
                                        <th class="text-center">Horas</th>
                                        <th class="text-end">Custo/Hora</th>
                                        <th class="text-end">Custo Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alocacao['equipe'] as $membro): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($membro['membro_nome']) ?></strong></td>
                                        <td><?= htmlspecialchars($membro['funcao']) ?></td>
                                        <td class="text-center"><?= number_format($membro['horas_alocadas'], 2, ',', '.') ?>h</td>
                                        <td class="text-end">R$ <?= number_format($membro['custo_hora_membro'], 2, ',', '.') ?></td>
                                        <td class="text-end"><strong>R$ <?= number_format($membro['custo_total_membro'], 2, ',', '.') ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total Equipe:</strong></td>
                                        <td class="text-end"><strong>R$ <?= number_format($alocacao['total_equipe'], 2, ',', '.') ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-box"></i> Materiais e Recursos
                </div>
                <div class="card-body">
                    <?php if (empty($alocacao['recursos'])): ?>
                        <p class="text-muted mb-0">Nenhum recurso/material registrado ainda.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Categoria</th>
                                        <th class="text-end">Custo</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alocacao['recursos'] as $recurso): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($recurso['descricao']) ?></strong>
                                            <?php if ($recurso['data_pagamento']): ?>
                                                <br><small class="text-muted">Pago em <?= date('d/m/Y', strtotime($recurso['data_pagamento'])) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($recurso['categoria']) ?></td>
                                        <td class="text-end"><strong>R$ <?= number_format($recurso['custo'], 2, ',', '.') ?></strong></td>
                                        <td class="text-center">
                                            <?php
                                            $statusBadge = match($recurso['status']) {
                                                'pago' => 'success',
                                                'pendente' => 'warning',
                                                'cancelado' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $statusBadge ?>"><?= ucfirst($recurso['status']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Total Recursos:</strong></td>
                                        <td class="text-end"><strong>R$ <?= number_format($alocacao['total_recursos'], 2, ',', '.') ?></strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo Total -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-calculator"></i> Resumo Total de Custos
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6>Custos por Tipo</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Equipe (Mão de Obra):</td>
                            <td class="text-end"><strong>R$ <?= number_format($alocacao['total_equipe'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr>
                            <td>Recursos (Materiais):</td>
                            <td class="text-end"><strong>R$ <?= number_format($alocacao['total_recursos'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Total Geral:</strong></td>
                            <td class="text-end"><strong>R$ <?= number_format($alocacao['total_equipe'] + $alocacao['total_recursos'], 2, ',', '.') ?></strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-4">
                    <h6>Comparativo Estimado vs Realizado</h6>
                    <canvas id="graficoComparativo" height="150"></canvas>
                </div>
                
                <div class="col-md-4">
                    <h6>Distribuição de Custos</h6>
                    <canvas id="graficoDistribuicao" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Gráfico Comparativo
    const ctxComp = document.getElementById('graficoComparativo').getContext('2d');
    new Chart(ctxComp, {
        type: 'bar',
        data: {
            labels: ['Estimado', 'Realizado'],
            datasets: [{
                label: 'Custo',
                data: [
                    <?= $custos['custo_estimado'] ?>,
                    <?= $custos['custo_realizado'] ?>
                ],
                backgroundColor: ['rgba(0, 123, 255, 0.8)', 'rgba(220, 53, 69, 0.8)']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Distribuição
    const ctxDist = document.getElementById('graficoDistribuicao').getContext('2d');
    new Chart(ctxDist, {
        type: 'doughnut',
        data: {
            labels: ['Equipe', 'Recursos'],
            datasets: [{
                data: [
                    <?= $alocacao['total_equipe'] ?>,
                    <?= $alocacao['total_recursos'] ?>
                ],
                backgroundColor: ['rgba(23, 162, 184, 0.8)', 'rgba(255, 193, 7, 0.8)']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': R$ ' + 
                                   context.parsed.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
