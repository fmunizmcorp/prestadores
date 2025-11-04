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
        <h2><i class="fas fa-briefcase"></i> Empresas Tomadoras</h2>
        <p class="text-muted">Gerenciamento de empresas clientes (tomadoras de serviço)</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="/empresas-tomadoras/create" class="btn btn-primary">
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
                        <h6 class="card-subtitle mb-2 text-white-50">Inativas</h6>
                        <h3 class="card-title mb-0"><?= ($stats['total'] ?? 0) - ($stats['ativas'] ?? 0) ?></h3>
                    </div>
                    <div>
                        <i class="fas fa-pause-circle fa-3x opacity-50"></i>
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
                        <h6 class="card-subtitle mb-2 text-white-50">Este Mês</h6>
                        <h3 class="card-title mb-0">0</h3>
                    </div>
                    <div>
                        <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
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
            <form method="GET" action="/empresas-tomadoras" class="row g-3">
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
                <a href="/empresas-tomadoras/create" class="alert-link">Cadastrar primeira empresa</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Logo</th>
                            <th>Empresa</th>
                            <th>CNPJ</th>
                            <th>Cidade/Estado</th>
                            <th>Contato</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr>
                                <td>
                                    <?php if ($empresa['logo']): ?>
                                        <img src="/public/uploads/logos/<?= $empresa['logo'] ?>" 
                                             alt="<?= $empresa['nome_fantasia'] ?>" 
                                             class="img-thumbnail" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; border-radius: 4px;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($empresa['nome_fantasia']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($empresa['razao_social']) ?></small>
                                </td>
                                <td>
                                    <span class="font-monospace"><?= formatCnpj($empresa['cnpj']) ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($empresa['cidade']) ?> - <?= $empresa['estado'] ?>
                                </td>
                                <td>
                                    <?php if ($empresa['email_principal']): ?>
                                        <i class="fas fa-envelope text-muted"></i> <?= htmlspecialchars($empresa['email_principal']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($empresa['telefone_principal']): ?>
                                        <i class="fas fa-phone text-muted"></i> <?= formatTelefone($empresa['telefone_principal']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($empresa['ativo']): ?>
                                        <span class="badge bg-success">Ativa</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="/empresas-tomadoras/<?= $empresa['id'] ?>" 
                                           class="btn btn-outline-info" 
                                           data-bs-toggle="tooltip" 
                                           title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/empresas-tomadoras/<?= $empresa['id'] ?>/edit" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-delete" 
                                                data-id="<?= $empresa['id'] ?>"
                                                data-nome="<?= htmlspecialchars($empresa['nome_fantasia']) ?>"
                                                data-bs-toggle="tooltip" 
                                                title="Excluir">
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
                <nav aria-label="Paginação">
                    <ul class="pagination justify-content-center">
                        <?php
                        $currentPage = $_GET['page'] ?? 1;
                        $queryParams = $_GET;
                        
                        // Botão Anterior
                        if ($currentPage > 1):
                            $queryParams['page'] = $currentPage - 1;
                            $prevUrl = '/empresas-tomadoras?' . http_build_query($queryParams);
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $prevUrl ?>">Anterior</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Anterior</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        // Páginas
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPaginas, $currentPage + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                            $queryParams['page'] = $i;
                            $pageUrl = '/empresas-tomadoras?' . http_build_query($queryParams);
                        ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $pageUrl ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        // Botão Próximo
                        if ($currentPage < $totalPaginas):
                            $queryParams['page'] = $currentPage + 1;
                            $nextUrl = '/empresas-tomadoras?' . http_build_query($queryParams);
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $nextUrl ?>">Próximo</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Próximo</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Form para DELETE (oculto) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
</form>

<?php
// Funções auxiliares
function formatCnpj($cnpj) {
    return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
}

function formatTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) == 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) == 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    return $telefone;
}

$inlineJS = <<<'JAVASCRIPT'
// Confirmar exclusão
$('.btn-delete').on('click', function() {
    const id = $(this).data('id');
    const nome = $(this).data('nome');
    
    Swal.fire({
        title: 'Confirmar Exclusão',
        html: `Tem certeza que deseja excluir a empresa <strong>${nome}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = $('#deleteForm');
            form.attr('action', `/empresas-tomadoras/${id}/delete`);
            form.submit();
        }
    });
});
JAVASCRIPT;

require __DIR__ . '/../layouts/footer.php';
?>
