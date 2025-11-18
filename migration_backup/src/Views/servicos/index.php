<?php
/**
 * View: Listagem de Serviços
 * Controller: ServicoController
 * 
 * Exibe listagem de todos os serviços cadastrados com:
 * - Cards de estatísticas
 * - Filtros avançados
 * - Tabela paginada com ações
 * - Links para criar, editar e visualizar
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Serviços</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Catálogo de Serviços</h1>
            <p class="text-muted mb-0">Gerencie todos os serviços disponíveis no sistema</p>
        </div>
        <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
        <a href="?page=servicos&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Serviço
        </a>
        <?php endif; ?>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-stats bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Total de Serviços</h6>
                            <h2 class="mb-0"><?= $stats['total'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-briefcase fa-2x opacity-75"></i>
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
                    <small class="text-white-50">Disponíveis para uso</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Com Requisitos</h6>
                            <h2 class="mb-0"><?= $stats['com_requisitos'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Exigem qualificações</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2">Com Valor Ref.</h6>
                            <h2 class="mb-0"><?= $stats['com_valor_ref'] ?? 0 ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <small class="text-white-50">Possuem valor de referência</small>
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
                <form method="GET" action="?page=servicos" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($filtros['search'] ?? '') ?>"
                               placeholder="Código, nome ou descrição...">
                    </div>

                    <div class="col-md-2">
                        <label for="ativo" class="form-label">Status</label>
                        <select class="form-select" id="ativo" name="ativo">
                            <option value="">Todos</option>
                            <option value="1" <?= ($filtros['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativos</option>
                            <option value="0" <?= ($filtros['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="com_requisitos" class="form-label">Requisitos</label>
                        <select class="form-select" id="com_requisitos" name="com_requisitos">
                            <option value="">Todos</option>
                            <option value="1" <?= ($filtros['com_requisitos'] ?? '') === '1' ? 'selected' : '' ?>>Com requisitos</option>
                            <option value="0" <?= ($filtros['com_requisitos'] ?? '') === '0' ? 'selected' : '' ?>>Sem requisitos</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="tipo" class="form-label">Tipo de Serviço</label>
                        <select class="form-select" id="tipo" name="tipo">
                            <option value="">Todos</option>
                            <option value="Técnico" <?= ($filtros['tipo'] ?? '') === 'Técnico' ? 'selected' : '' ?>>Técnico</option>
                            <option value="Operacional" <?= ($filtros['tipo'] ?? '') === 'Operacional' ? 'selected' : '' ?>>Operacional</option>
                            <option value="Administrativo" <?= ($filtros['tipo'] ?? '') === 'Administrativo' ? 'selected' : '' ?>>Administrativo</option>
                            <option value="Gerencial" <?= ($filtros['tipo'] ?? '') === 'Gerencial' ? 'selected' : '' ?>>Gerencial</option>
                            <option value="Especializado" <?= ($filtros['tipo'] ?? '') === 'Especializado' ? 'selected' : '' ?>>Especializado</option>
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
                        <a href="?page=servicos" class="btn btn-secondary">
                            <i class="fas fa-eraser me-2"></i>Limpar Filtros
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Services Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Serviços Cadastrados
                <span class="badge bg-primary ms-2"><?= $total ?> <?= $total == 1 ? 'serviço' : 'serviços' ?></span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($servicos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Nenhum serviço encontrado com os filtros aplicados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nome do Serviço</th>
                                <th>Tipo</th>
                                <th>Carga Horária</th>
                                <th>Valor Ref.</th>
                                <th>Requisitos</th>
                                <th>Status</th>
                                <th class="text-center" width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($servico['codigo']) ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($servico['nome']) ?></strong>
                                        <?php if (!empty($servico['descricao'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(mb_substr($servico['descricao'], 0, 80)) ?>
                                            <?= mb_strlen($servico['descricao']) > 80 ? '...' : '' ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($servico['tipo'])): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($servico['tipo']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($servico['carga_horaria_semanal'])): ?>
                                    <?= $servico['carga_horaria_semanal'] ?>h/sem
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($servico['valor_referencia']) && $servico['valor_referencia'] > 0): ?>
                                    <strong class="text-success">R$ <?= number_format($servico['valor_referencia'], 2, ',', '.') ?></strong>
                                    <?php if (!empty($servico['tipo_valor'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($servico['tipo_valor']) ?></small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">Não definido</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($servico['possui_requisitos'])): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clipboard-list me-1"></i>Sim
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($servico['ativo']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Ativo
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times-circle me-1"></i>Inativo
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="?page=servicos&action=show&id=<?= $servico['id'] ?>" 
                                           class="btn btn-info" 
                                           title="Visualizar"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
                                        <a href="?page=servicos&action=edit&id=<?= $servico['id'] ?>" 
                                           class="btn btn-warning" 
                                           title="Editar"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger btn-delete" 
                                                data-id="<?= $servico['id'] ?>"
                                                data-nome="<?= htmlspecialchars($servico['nome']) ?>"
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
                        Exibindo <?= count($servicos) ?> de <?= $total ?> serviço(s) 
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
            const servicoId = this.dataset.id;
            const servicoNome = this.dataset.nome;
            
            Swal.fire({
                title: 'Confirmar Exclusão',
                html: `Tem certeza que deseja excluir o serviço<br><strong>${servicoNome}</strong>?`,
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
                    form.action = `/servicos/${servicoId}/delete`;
                    
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
            <li><strong>Visualizar:</strong> Clique no ícone <i class="fas fa-eye text-info"></i> para ver detalhes completos do serviço</li>
            <li><strong>Editar:</strong> Clique no ícone <i class="fas fa-edit text-warning"></i> para modificar informações (requer permissão)</li>
            <li><strong>Excluir:</strong> Clique no ícone <i class="fas fa-trash text-danger"></i> para remover o serviço (exclusão lógica)</li>
            <li><strong>Filtros:</strong> Use os filtros para refinar sua busca por código, nome, tipo ou status</li>
            <li><strong>Novo Serviço:</strong> Clique no botão "Novo Serviço" para cadastrar um novo serviço no catálogo</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
