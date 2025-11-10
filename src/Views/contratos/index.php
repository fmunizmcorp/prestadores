<?php
/**
 * View: Listagem de Contratos
 * Controller: ContratoController
 * 
 * Exibe listagem de todos os contratos com:
 * - Cards de estatísticas
 * - Filtros avançados
 * - Tabela paginada com ações
 * - Alertas de contratos vencendo
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Contratos</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Gestão de Contratos</h1>
            <p class="text-muted mb-0">Gerencie todos os contratos do sistema</p>
        </div>
        <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
        <a href="/contratos/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Contrato
        </a>
        <?php endif; ?>
    </div>

    <!-- Alerts for contracts expiring soon -->
    <?php if (!empty($contratosVencendo)): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Atenção: Contratos Vencendo</h5>
        <p>Existem <strong><?= count($contratosVencendo) ?> contrato(s)</strong> que vencem nos próximos 90 dias:</p>
        <ul>
            <?php foreach ($contratosVencendo as $cv): ?>
            <li>
                <strong><?= htmlspecialchars($cv['numero_contrato']) ?></strong> - 
                <?= htmlspecialchars($cv['empresa_nome']) ?> - 
                Vence em <?= date('d/m/Y', strtotime($cv['data_termino'])) ?>
                <a href="/contratos/<?= $cv['id'] ?>" class="btn btn-sm btn-warning ms-2">Ver Detalhes</a>
            </li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-stats bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Total de Contratos</h6>
                            <h2 class="mb-0"><?= $stats['total'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-file-contract fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Cadastrados no sistema</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Ativos</h6>
                            <h2 class="mb-0"><?= $stats['ativos'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Em vigência</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Vencendo (90d)</h6>
                            <h2 class="mb-0"><?= $stats['vencendo'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Requerem atenção</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Valor Total Mensal</h6>
                            <h2 class="mb-0">R$ <?= number_format($stats['valor_total_mensal'] ?? 0, 0, ',', '.') ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Contratos ativos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                    <i class="fas fa-filter me-2"></i>Filtros de Pesquisa
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filtrosCollapse">
            <div class="card-body">
                <form method="GET" action="/contratos" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($filtros['search'] ?? '') ?>"
                               placeholder="Número do contrato ou empresa...">
                    </div>

                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="Ativo" <?= ($filtros['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Suspenso" <?= ($filtros['status'] ?? '') === 'Suspenso' ? 'selected' : '' ?>>Suspenso</option>
                            <option value="Encerrado" <?= ($filtros['status'] ?? '') === 'Encerrado' ? 'selected' : '' ?>>Encerrado</option>
                            <option value="Vencido" <?= ($filtros['status'] ?? '') === 'Vencido' ? 'selected' : '' ?>>Vencido</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo">
                            <option value="">Todos</option>
                            <option value="Prestação de Serviços" <?= ($filtros['tipo'] ?? '') === 'Prestação de Serviços' ? 'selected' : '' ?>>Prestação de Serviços</option>
                            <option value="Fornecimento" <?= ($filtros['tipo'] ?? '') === 'Fornecimento' ? 'selected' : '' ?>>Fornecimento</option>
                            <option value="Outsourcing" <?= ($filtros['tipo'] ?? '') === 'Outsourcing' ? 'selected' : '' ?>>Outsourcing</option>
                            <option value="Consultoria" <?= ($filtros['tipo'] ?? '') === 'Consultoria' ? 'selected' : '' ?>>Consultoria</option>
                            <option value="Misto" <?= ($filtros['tipo'] ?? '') === 'Misto' ? 'selected' : '' ?>>Misto</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="empresa_tomadora_id" class="form-label">Empresa Tomadora</label>
                        <select class="form-select select2" id="empresa_tomadora_id" name="empresa_tomadora_id">
                            <option value="">Todas</option>
                            <?php foreach ($empresasTomadoras ?? [] as $et): ?>
                            <option value="<?= $et['id'] ?>" <?= ($filtros['empresa_tomadora_id'] ?? '') == $et['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($et['nome_fantasia']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="limit" class="form-label">Itens por página</label>
                        <select class="form-select" id="limit" name="limit">
                            <option value="10" <?= ($filtros['limit'] ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= ($filtros['limit'] ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= ($filtros['limit'] ?? 20) == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= ($filtros['limit'] ?? 20) == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                        <a href="/contratos" class="btn btn-secondary">
                            <i class="fas fa-eraser me-2"></i>Limpar Filtros
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Contratos
                <span class="badge bg-primary ms-2"><?= $total ?> <?= $total == 1 ? 'contrato' : 'contratos' ?></span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($contratos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Nenhum contrato encontrado com os filtros aplicados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Empresa Tomadora</th>
                                <th>Tipo</th>
                                <th>Vigência</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th class="text-center" width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratos as $contrato): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($contrato['numero_contrato']) ?></strong>
                                    <?php if (!empty($contrato['objeto'])): ?>
                                    <br><small class="text-muted">
                                        <?= htmlspecialchars(mb_substr($contrato['objeto'], 0, 40)) ?>
                                        <?= mb_strlen($contrato['objeto']) > 40 ? '...' : '' ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($contrato['empresa_nome']) ?></strong>
                                        <?php if (!empty($contrato['empresa_cnpj'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($contrato['empresa_cnpj']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($contrato['tipo_contrato'])): ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($contrato['tipo_contrato']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <strong>Início:</strong> <?= date('d/m/Y', strtotime($contrato['data_inicio'])) ?><br>
                                        <strong>Término:</strong> <?= date('d/m/Y', strtotime($contrato['data_termino'])) ?>
                                    </small>
                                    <?php
                                    $diasRestantes = ceil((strtotime($contrato['data_termino']) - time()) / 86400);
                                    if ($diasRestantes > 0 && $diasRestantes <= 90 && $contrato['status'] === 'Ativo'):
                                    ?>
                                    <br><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i><?= $diasRestantes ?> dias</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($contrato['valor_total']) && $contrato['valor_total'] > 0): ?>
                                    <strong class="text-success">R$ <?= number_format($contrato['valor_total'], 2, ',', '.') ?></strong>
                                    <?php if (!empty($contrato['tipo_valor'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($contrato['tipo_valor']) ?></small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">A definir</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    $statusIcon = 'info-circle';
                                    switch ($contrato['status']) {
                                        case 'Ativo':
                                            $statusClass = 'success';
                                            $statusIcon = 'check-circle';
                                            break;
                                        case 'Suspenso':
                                            $statusClass = 'warning';
                                            $statusIcon = 'pause-circle';
                                            break;
                                        case 'Encerrado':
                                            $statusClass = 'secondary';
                                            $statusIcon = 'times-circle';
                                            break;
                                        case 'Vencido':
                                            $statusClass = 'danger';
                                            $statusIcon = 'exclamation-circle';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <i class="fas fa-<?= $statusIcon ?> me-1"></i><?= htmlspecialchars($contrato['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="/contratos/<?= $contrato['id'] ?>" 
                                           class="btn btn-info" 
                                           title="Visualizar"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
                                        <a href="/contratos/<?= $contrato['id'] ?>/edit" 
                                           class="btn btn-warning" 
                                           title="Editar"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger btn-delete" 
                                                data-id="<?= $contrato['id'] ?>"
                                                data-numero="<?= htmlspecialchars($contrato['numero_contrato']) ?>"
                                                title="Excluir"
                                                data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Navegação de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <?php if ($paginaAtual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $paginaAtual - 1])) ?>">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-chevron-left"></i> Anterior</span>
                        </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $inicio = max(1, $paginaAtual - 2);
                        $fim = min($totalPaginas, $paginaAtual + 2);
                        
                        if ($inicio > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => 1])) ?>">1</a>
                            </li>
                            <?php if ($inicio > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                        <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($fim < $totalPaginas): ?>
                            <?php if ($fim < $totalPaginas - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $totalPaginas])) ?>">
                                    <?= $totalPaginas ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <?php if ($paginaAtual < $totalPaginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $paginaAtual + 1])) ?>">
                                Próxima <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Próxima <i class="fas fa-chevron-right"></i></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Results Info -->
                <div class="text-center text-muted mt-3">
                    <small>
                        Exibindo <?= count($contratos) ?> de <?= $total ?> contrato(s) 
                        (Página <?= $paginaAtual ?> de <?= $totalPaginas ?>)
                    </small>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(function(button) {
        button.addEventListener('click', function() {
            const contratoId = this.dataset.id;
            const contratoNumero = this.dataset.numero;
            
            Swal.fire({
                title: 'Confirmar Exclusão',
                html: `Tem certeza que deseja excluir o contrato<br><strong>${contratoNumero}</strong>?<br><br>` +
                      `<small class="text-muted">Esta ação irá remover o contrato e todos os seus relacionamentos.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/contratos/${contratoId}/delete`;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = 'csrf_token';
                    csrfToken.value = '<?= $_SESSION['csrf_token'] ?>';
                    form.appendChild(csrfToken);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>

<!-- Footer Instructions -->
<div class="container-fluid mt-5 mb-3">
    <div class="alert alert-light border">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Instruções de Uso</h6>
        <ul class="mb-0 small">
            <li><strong>Visualizar:</strong> Clique no ícone <i class="fas fa-eye text-info"></i> para ver detalhes completos do contrato</li>
            <li><strong>Editar:</strong> Clique no ícone <i class="fas fa-edit text-warning"></i> para modificar informações (requer permissão)</li>
            <li><strong>Excluir:</strong> Clique no ícone <i class="fas fa-trash text-danger"></i> para remover o contrato (exclusão lógica)</li>
            <li><strong>Filtros:</strong> Use os filtros para refinar sua busca por número, empresa, tipo ou status</li>
            <li><strong>Alertas:</strong> Contratos com menos de 90 dias para vencer aparecem com alerta amarelo</li>
            <li><strong>Novo Contrato:</strong> Clique no botão "Novo Contrato" para cadastrar um novo contrato</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
