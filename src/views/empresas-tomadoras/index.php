<?php
$pageTitle = 'Empresas Tomadoras';
$activeMenu = 'empresas-tomadoras';
$breadcrumb = [
    ['label' => 'Empresas'],
    ['label' => 'Empresas Tomadoras']
];

require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-building"></i> Empresas Tomadoras</h2>
        <p class="text-muted">Gerenciamento de empresas clientes (tomadoras de serviço)</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= BASE_URL ?>/?page=empresas-tomadoras&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Empresa
        </a>
    </div>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Total</h6>
                        <h3 class="card-title mb-0"><?= $stats['total'] ?? 0 ?></h3>
                    </div>
                    <div>
                        <i class="fas fa-building fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Ativas</h6>
                        <h3 class="card-title mb-0"><?= $stats['ativas'] ?? 0 ?></h3>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
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
                        <h6 class="card-subtitle mb-2 text-white-50">Contratos Ativos</h6>
                        <h3 class="card-title mb-0">0</h3>
                    </div>
                    <div>
                        <i class="fas fa-file-contract fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Projetos</h6>
                        <h3 class="card-title mb-0">0</h3>
                    </div>
                    <div>
                        <i class="fas fa-project-diagram fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter"></i> Filtros
            <button class="btn btn-sm btn-link float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                <i class="fas fa-chevron-down"></i>
            </button>
        </h5>
    </div>
    <div class="collapse show" id="filtrosCollapse">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/" class="row g-3">
                <input type="hidden" name="page" value="empresas-tomadoras">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nome, razão social ou CNPJ..." 
                           value="<?= $_GET['search'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="ativo" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?= ($_GET['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativas</option>
                        <option value="0" <?= ($_GET['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf): ?>
                            <option value="<?= $uf ?>" <?= ($_GET['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Registros</label>
                    <select name="limit" class="form-select">
                        <option value="20" <?= ($_GET['limit'] ?? '20') === '20' ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= ($_GET['limit'] ?? '20') === '50' ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($_GET['limit'] ?? '20') === '100' ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Listagem -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> Empresas Cadastradas (<?= $total ?? 0 ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($empresas)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhuma empresa encontrada.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Nome Fantasia</th>
                            <th>Razão Social</th>
                            <th>CNPJ</th>
                            <th>Cidade/UF</th>
                            <th>Contratos</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($empresa['logo'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/logos/<?= $empresa['logo'] ?>" 
                                             alt="Logo" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 4px;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($empresa['nome_fantasia']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($empresa['razao_social']) ?></td>
                                <td><small class="font-monospace"><?= format_cnpj($empresa['cnpj']) ?></small></td>
                                <td><?= htmlspecialchars($empresa['localizacao'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $empresa['total_contratos_ativos'] ?? 0 ?> ativos</span>
                                </td>
                                <td>
                                    <?php if ($empresa['ativo']): ?>
                                        <span class="badge bg-success">Ativa</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/?page=empresas-tomadoras&action=show&id=<?= $empresa['id'] ?>" 
                                           class="btn btn-info" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/?page=empresas-tomadoras&action=edit&id=<?= $empresa['id'] ?>" 
                                           class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" title="Excluir"
                                                onclick="confirmarExclusao(<?= $empresa['id'] ?>, '<?= htmlspecialchars($empresa['nome_fantasia']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPaginas > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/?page=empresas-tomadoras&p=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => '', 'p' => ''])) ?>">
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

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a empresa <strong id="empresaNome"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <form method="POST" id="formExcluir">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarExclusao(id, nome) {
    document.getElementById('empresaNome').textContent = nome;
    document.getElementById('formExcluir').action = '<?= BASE_URL ?>/?page=empresas-tomadoras&action=destroy&id=' + id;
    new bootstrap.Modal(document.getElementById('modalExcluir')).show();
}

// Helper para formatar CNPJ
<?php if (!function_exists('formatCnpj')): ?>
function formatCnpj(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
}
<?php endif; ?>
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
