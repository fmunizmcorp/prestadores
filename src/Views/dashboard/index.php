<!-- Dashboard Principal - Sprint 32 -->
<div class="dashboard-container">
    
    <!-- Header do Dashboard -->
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">Visão geral do sistema Clinfec Prestadores</p>
        </div>
        <div class="dashboard-actions">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Atualizar
            </button>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        
        <!-- Card: Empresas Tomadoras -->
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Empresas Tomadoras</div>
                <div class="stat-value"><?= number_format($stats['empresas_tomadoras'], 0, ',', '.') ?></div>
                <div class="stat-footer">
                    <a href="?page=empresas-tomadoras" class="stat-link">
                        Ver todas <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card: Contratos Ativos -->
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Contratos Ativos</div>
                <div class="stat-value"><?= number_format($stats['contratos_ativos'], 0, ',', '.') ?></div>
                <div class="stat-footer">
                    <span class="stat-secondary">
                        de <?= number_format($stats['contratos_total'], 0, ',', '.') ?> total
                    </span>
                </div>
            </div>
        </div>

        <!-- Card: Atestados Pendentes -->
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Atestados Pendentes</div>
                <div class="stat-value"><?= number_format($stats['atestados_pendentes'], 0, ',', '.') ?></div>
                <div class="stat-footer">
                    <a href="?page=atestados&status=pendente" class="stat-link">
                        Revisar <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card: Faturas a Vencer -->
        <div class="stat-card stat-card-danger">
            <div class="stat-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Faturas a Vencer</div>
                <div class="stat-value"><?= number_format($stats['faturas_a_vencer'], 0, ',', '.') ?></div>
                <div class="stat-footer">
                    <span class="stat-secondary">próximos 30 dias</span>
                </div>
            </div>
        </div>

        <!-- Card: Valor Total Contratos -->
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Valor Total Contratos</div>
                <div class="stat-value">R$ <?= number_format($stats['valor_contratos_ativos'], 2, ',', '.') ?></div>
                <div class="stat-footer">
                    <span class="stat-secondary">
                        Executado: R$ <?= number_format($stats['valor_executado'], 2, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Card: Empresas Prestadoras -->
        <div class="stat-card stat-card-secondary">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Empresas Prestadoras</div>
                <div class="stat-value"><?= number_format($stats['empresas_prestadoras'], 0, ',', '.') ?></div>
                <div class="stat-footer">
                    <span class="stat-secondary">
                        <?= number_format($stats['usuarios_ativos'], 0, ',', '.') ?> usuários ativos
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Gráficos -->
    <div class="charts-grid">
        
        <!-- Gráfico: Contratos por Status -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Contratos por Status</h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('chartStatus')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartStatus" height="300"></canvas>
            </div>
        </div>

        <!-- Gráfico: Contratos por Mês -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Contratos por Mês</h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('chartMeses')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartMeses" height="300"></canvas>
            </div>
        </div>

    </div>

    <div class="charts-grid">
        
        <!-- Gráfico: Faturamento Mensal -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Faturamento Mensal</h3>
                <div class="chart-actions">
                    <select class="form-select form-select-sm" onchange="updateChartPeriod(this.value)">
                        <option value="6">Últimos 6 meses</option>
                        <option value="12">Últimos 12 meses</option>
                    </select>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartFaturamento" height="300"></canvas>
            </div>
        </div>

        <!-- Gráfico: Top 5 Empresas -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Top 5 Empresas por Valor</h3>
            </div>
            <div class="chart-body">
                <canvas id="chartTopEmpresas" height="300"></canvas>
            </div>
        </div>

    </div>

    <!-- Atividades Recentes e Alertas -->
    <div class="content-grid">
        
        <!-- Atividades Recentes -->
        <div class="activity-card">
            <div class="activity-header">
                <h3>Atividades Recentes</h3>
                <a href="?page=atividades" class="btn btn-sm btn-outline-primary">
                    Ver todas
                </a>
            </div>
            <div class="activity-body">
                <?php if (empty($recentActivities)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Nenhuma atividade recente</p>
                    </div>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon activity-icon-<?= $activity['tipo'] ?>">
                                    <?php if ($activity['tipo'] == 'contrato'): ?>
                                        <i class="fas fa-file-contract"></i>
                                    <?php elseif ($activity['tipo'] == 'atestado'): ?>
                                        <i class="fas fa-clipboard-check"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file-invoice"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title"><?= htmlspecialchars($activity['titulo']) ?></div>
                                    <div class="activity-meta">
                                        <?= htmlspecialchars($activity['empresa']) ?> •
                                        <span class="badge badge-<?= $activity['status'] ?>">
                                            <?= ucfirst($activity['status']) ?>
                                        </span> •
                                        <?= date('d/m/Y H:i', strtotime($activity['data'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alertas e Notificações -->
        <div class="alert-card">
            <div class="alert-header">
                <h3>Alertas e Notificações</h3>
                <button class="btn btn-sm btn-outline-secondary" onclick="markAllRead()">
                    Marcar todas como lidas
                </button>
            </div>
            <div class="alert-body">
                <?php if (empty($alerts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>Nenhum alerta no momento</p>
                    </div>
                <?php else: ?>
                    <div class="alert-list">
                        <?php foreach ($alerts as $alert): ?>
                            <div class="alert alert-<?= $alert['tipo'] ?> alert-dismissible">
                                <span class="alert-icon"><?= $alert['icone'] ?></span>
                                <div class="alert-content">
                                    <strong><?= htmlspecialchars($alert['titulo']) ?></strong>
                                    <p><?= htmlspecialchars($alert['mensagem']) ?></p>
                                    <?php if ($alert['data']): ?>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($alert['data'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn-close" onclick="dismissAlert(this)"></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Custom Dashboard JS -->
<script>
// Dados dos gráficos passados do PHP
const chartData = <?= json_encode($chartData) ?>;

// Cores padrão
const colors = {
    primary: '#667eea',
    success: '#28a745',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#17a2b8',
    secondary: '#6c757d'
};

// Gráfico: Contratos por Status
if (document.getElementById('chartStatus')) {
    const ctx = document.getElementById('chartStatus').getContext('2d');
    const statusLabels = chartData.contratos_por_status.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
    const statusData = chartData.contratos_por_status.map(item => item.total);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: [colors.success, colors.warning, colors.danger, colors.secondary, colors.info],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Gráfico: Contratos por Mês
if (document.getElementById('chartMeses')) {
    const ctx = document.getElementById('chartMeses').getContext('2d');
    const mesesLabels = chartData.contratos_por_mes.map(item => {
        const [ano, mes] = item.mes.split('-');
        const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        return meses[parseInt(mes) - 1] + '/' + ano.substr(2);
    });
    const mesesData = chartData.contratos_por_mes.map(item => item.total);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: mesesLabels,
            datasets: [{
                label: 'Contratos',
                data: mesesData,
                backgroundColor: colors.primary,
                borderColor: colors.primary,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Gráfico: Faturamento Mensal
if (document.getElementById('chartFaturamento')) {
    const ctx = document.getElementById('chartFaturamento').getContext('2d');
    const fatLabels = chartData.faturamento_mensal.map(item => {
        const [ano, mes] = item.mes.split('-');
        const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        return meses[parseInt(mes) - 1] + '/' + ano.substr(2);
    });
    const fatData = chartData.faturamento_mensal.map(item => parseFloat(item.valor));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: fatLabels,
            datasets: [{
                label: 'Faturamento (R$)',
                data: fatData,
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderColor: colors.primary,
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    }
                }
            }
        }
    });
}

// Gráfico: Top 5 Empresas
if (document.getElementById('chartTopEmpresas')) {
    const ctx = document.getElementById('chartTopEmpresas').getContext('2d');
    const empresasLabels = chartData.top_empresas.map(item => item.nome_fantasia);
    const empresasData = chartData.top_empresas.map(item => parseFloat(item.valor_total));
    
    new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: empresasLabels,
            datasets: [{
                label: 'Valor Total (R$)',
                data: empresasData,
                backgroundColor: colors.info,
                borderColor: colors.info,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + context.parsed.x.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    }
                }
            }
        }
    });
}

// Funções auxiliares
function refreshDashboard() {
    window.location.reload();
}

function exportChart(chartId) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        const url = chart.toBase64Image();
        const link = document.createElement('a');
        link.href = url;
        link.download = chartId + '_' + new Date().getTime() + '.png';
        link.click();
    }
}

function dismissAlert(btn) {
    btn.closest('.alert').style.display = 'none';
}

function markAllRead() {
    const alerts = document.querySelectorAll('.alert-list .alert');
    alerts.forEach(alert => alert.style.display = 'none');
}

function updateChartPeriod(months) {
    // Recarregar página com novo período
    window.location.href = '?page=dashboard&periodo=' + months;
}
</script>

<!-- Custom Dashboard CSS -->
<style>
.dashboard-container {
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-title {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

.dashboard-subtitle {
    color: #6c757d;
    margin: 5px 0 0 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-card-primary .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card-success .stat-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
.stat-card-warning .stat-icon { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
.stat-card-danger .stat-icon { background: linear-gradient(135deg, #dc3545 0%, #e91e63 100%); }
.stat-card-info .stat-icon { background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%); }
.stat-card-secondary .stat-icon { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 13px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #333;
    line-height: 1;
    margin-bottom: 8px;
}

.stat-footer {
    font-size: 13px;
    color: #6c757d;
}

.stat-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.stat-link:hover {
    color: #764ba2;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.chart-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.chart-body {
    position: relative;
    height: 300px;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 20px;
}

.activity-card,
.alert-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.activity-header,
.alert-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.activity-header h3,
.alert-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.activity-body,
.alert-body {
    padding: 20px;
    max-height: 500px;
    overflow-y: auto;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.activity-icon-contrato { background: #667eea; }
.activity-icon-atestado { background: #28a745; }
.activity-icon-fatura { background: #ffc107; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.activity-meta {
    font-size: 13px;
    color: #6c757d;
}

.alert-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid;
}

.alert-warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
.alert-danger { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
.alert-info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }

.alert-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.alert-content {
    flex: 1;
}

.alert-content strong {
    display: block;
    margin-bottom: 5px;
}

.alert-content p {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.btn-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    opacity: 0.5;
    transition: opacity 0.2s;
}

.btn-close:hover {
    opacity: 1;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.3;
}

.empty-state p {
    margin: 0;
    font-size: 16px;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-ativo { background: #28a745; color: white; }
.badge-rascunho { background: #6c757d; color: white; }
.badge-suspenso { background: #ffc107; color: #333; }
.badge-encerrado { background: #dc3545; color: white; }
.badge-emitido { background: #17a2b8; color: white; }
.badge-aprovado { background: #28a745; color: white; }
.badge-pago { background: #20c997; color: white; }

@media (max-width: 768px) {
    .stats-grid,
    .charts-grid,
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>
