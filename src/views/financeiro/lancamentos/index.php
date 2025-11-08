<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-exchange-alt"></i> Lançamentos Financeiros</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item active">Lançamentos</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=financeiro&action=lancamentos&do=create" class="btn btn-success">
                <i class="fas fa-plus"></i> Novo Lançamento
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Débitos</h6>
                            <h4 class="card-title mb-0">R$ <?= number_format($stats['total_debitos'] ?? 0, 2, ',', '.') ?></h4>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Créditos</h6>
                            <h4 class="card-title mb-0">R$ <?= number_format($stats['total_creditos'] ?? 0, 2, ',', '.') ?></h4>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Saldo</h6>
                            <h4 class="card-title mb-0">
                                R$ <?= number_format(($stats['total_debitos'] ?? 0) - ($stats['total_creditos'] ?? 0), 2, ',', '.') ?>
                            </h4>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Lançamentos</h6>
                            <h4 class="card-title mb-0"><?= $stats['total_lancamentos'] ?? 0 ?></h4>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filtros
        </div>
        <div class="card-body">
            <form method="GET" id="filtrosForm">
                <input type="hidden" name="page" value="financeiro">
                <input type="hidden" name="action" value="lancamentos">
                
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" class="form-control" name="data_inicio" id="data_inicio"
                               value="<?= $_GET['data_inicio'] ?? '' ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" class="form-control" name="data_fim" id="data_fim"
                               value="<?= $_GET['data_fim'] ?? '' ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" id="tipo">
                            <option value="">Todos</option>
                            <option value="debito" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'debito') ? 'selected' : '' ?>>Débito</option>
                            <option value="credito" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'credito') ? 'selected' : '' ?>>Crédito</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="categoria_id" class="form-label">Categoria</label>
                        <select class="form-select" name="categoria_id" id="categoria_id">
                            <option value="">Todas</option>
                            <?php if (!empty($categorias)): ?>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['categoria_id']) && $_GET['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['codigo']) ?> - <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=lancamentos" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Lançamentos -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-list"></i> Lançamentos (Sistema de Partidas Dobradas)
        </div>
        <div class="card-body">
            <?php if (empty($lancamentos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Nenhum lançamento encontrado. <a href="<?= BASE_URL ?>/?page=financeiro&action=lancamentos&do=create">Criar primeiro lançamento</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelaLancamentos">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Categoria Débito</th>
                                <th>Categoria Crédito</th>
                                <th class="text-end">Valor</th>
                                <th>Centro Custo</th>
                                <th>Documento</th>
                                <th>Usuário</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lancamentos as $lanc): ?>
                            <tr>
                                <td>
                                    <?= date('d/m/Y', strtotime($lanc['data_lancamento'])) ?>
                                    <br><small class="text-muted"><?= date('H:i', strtotime($lanc['created_at'])) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($lanc['descricao']) ?></strong>
                                    <?php if (!empty($lanc['historico'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($lanc['historico']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <?= htmlspecialchars($lanc['categoria_debito_codigo']) ?>
                                    </span>
                                    <br><small><?= htmlspecialchars($lanc['categoria_debito_nome']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?= htmlspecialchars($lanc['categoria_credito_codigo']) ?>
                                    </span>
                                    <br><small><?= htmlspecialchars($lanc['categoria_credito_nome']) ?></small>
                                </td>
                                <td class="text-end">
                                    <strong class="fs-6">R$ <?= number_format($lanc['valor'], 2, ',', '.') ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($lanc['centro_custo_nome'])): ?>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($lanc['centro_custo_codigo']) ?>
                                    </span>
                                    <?php else: ?>
                                    <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($lanc['numero_documento']) ? htmlspecialchars($lanc['numero_documento']) : '<small class="text-muted">-</small>' ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($lanc['usuario_nome'] ?? '-') ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($lanc['estornado'] == 0): ?>
                                        <button type="button" class="btn btn-warning" 
                                                onclick="estornarLancamento(<?= $lanc['id'] ?>, '<?= htmlspecialchars($lanc['descricao'], ENT_QUOTES) ?>')"
                                                title="Estornar">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Estornado</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="4" class="text-end">TOTAIS:</td>
                                <td class="text-end fs-5">R$ <?= number_format(array_sum(array_column($lancamentos, 'valor')), 2, ',', '.') ?></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($pagination['pages'] > 1): ?>
                <nav aria-label="Paginação" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=financeiro&action=lancamentos&p=<?= $i ?><?= !empty($_GET['data_inicio']) ? '&data_inicio=' . $_GET['data_inicio'] : '' ?><?= !empty($_GET['data_fim']) ? '&data_fim=' . $_GET['data_fim'] : '' ?><?= !empty($_GET['tipo']) ? '&tipo=' . $_GET['tipo'] : '' ?><?= !empty($_GET['categoria_id']) ? '&categoria_id=' . $_GET['categoria_id'] : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Estornar -->
<div class="modal fade" id="modalEstornar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=lancamentos&do=estornar">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" id="estornarId">
                
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-undo"></i> Estornar Lançamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção:</strong> O estorno criará um lançamento inverso para anular este lançamento.
                    </div>
                    
                    <p>Confirma o estorno do lançamento:</p>
                    <p><strong id="estornarDescricao"></strong></p>

                    <div class="mb-3">
                        <label for="motivo_estorno" class="form-label">Motivo do Estorno *</label>
                        <textarea class="form-control" id="motivo_estorno" name="motivo_estorno" rows="3" required
                                  placeholder="Descreva o motivo do estorno..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Confirmar Estorno
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// DataTables (se disponível)
$(document).ready(function() {
    if (typeof $.fn.dataTable !== 'undefined') {
        $('#tabelaLancamentos').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            order: [[0, 'desc']],
            pageLength: 50,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });
    }
});

// Estornar lançamento
function estornarLancamento(id, descricao) {
    document.getElementById('estornarId').value = id;
    document.getElementById('estornarDescricao').textContent = descricao;
    const modal = new bootstrap.Modal(document.getElementById('modalEstornar'));
    modal.show();
}
</script>

<style>
.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75rem;
}
</style>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
