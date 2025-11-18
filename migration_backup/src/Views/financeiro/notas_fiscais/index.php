<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-file-invoice"></i> Notas Fiscais Eletrônicas</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item active">Notas Fiscais</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=create" class="btn btn-success">
                <i class="fas fa-plus"></i> Emitir Nota Fiscal
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Emitidas</h6>
                    <h3><?= $stats['total_emitidas'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Aguardando Emissão</h6>
                    <h3><?= $stats['aguardando'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Autorizadas</h6>
                    <h3><?= $stats['autorizadas'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Canceladas</h6>
                    <h3><?= $stats['canceladas'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-filter"></i> Filtros</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="notas-fiscais">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label>Data Início</label>
                        <input type="date" class="form-control" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Data Fim</label>
                        <input type="date" class="form-control" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Tipo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            <option value="nfe" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'nfe') ? 'selected' : '' ?>>NF-e</option>
                            <option value="nfse" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'nfse') ? 'selected' : '' ?>>NFS-e</option>
                            <option value="nfce" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'nfce') ? 'selected' : '' ?>>NFC-e</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <option value="rascunho" <?= (isset($_GET['status']) && $_GET['status'] === 'rascunho') ? 'selected' : '' ?>>Rascunho</option>
                            <option value="autorizada" <?= (isset($_GET['status']) && $_GET['status'] === 'autorizada') ? 'selected' : '' ?>>Autorizada</option>
                            <option value="cancelada" <?= (isset($_GET['status']) && $_GET['status'] === 'cancelada') ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="?page=notas-fiscais" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="card-header bg-primary text-white"><i class="fas fa-list"></i> Notas Fiscais</div>
        <div class="card-body">
            <?php if (empty($notas)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma nota fiscal encontrada.
                    <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=create">Emitir primeira nota</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelaNotas">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Tipo</th>
                                <th>Data Emissão</th>
                                <th>Cliente/Destinatário</th>
                                <th class="text-end">Valor Total</th>
                                <th>Status SEFAZ</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notas as $nota): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($nota['numero']) ?></strong>
                                    <br><small class="text-muted">Série: <?= htmlspecialchars($nota['serie']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= strtoupper($nota['tipo']) ?></span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($nota['data_emissao'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($nota['destinatario_nome']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($nota['destinatario_cnpj']) ?></small>
                                </td>
                                <td class="text-end">
                                    <strong>R$ <?= number_format($nota['valor_total'], 2, ',', '.') ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($nota['status_sefaz']) {
                                        'autorizada' => 'success',
                                        'cancelada' => 'danger',
                                        'rejeitada' => 'danger',
                                        'rascunho' => 'secondary',
                                        'processando' => 'warning',
                                        default => 'info'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($nota['status_sefaz']) ?></span>
                                    <?php if (!empty($nota['chave_acesso'])): ?>
                                    <br><small class="text-muted font-monospace" style="font-size: 9px;"><?= substr($nota['chave_acesso'], 0, 20) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=show&id=<?= $nota['id'] ?>" 
                                           class="btn btn-info" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($nota['status_sefaz'] === 'rascunho'): ?>
                                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=edit&id=<?= $nota['id'] ?>" 
                                           class="btn btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-success" 
                                                onclick="emitirNota(<?= $nota['id'] ?>)" title="Emitir">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($nota['status_sefaz'] === 'autorizada'): ?>
                                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=download-xml&id=<?= $nota['id'] ?>" 
                                           class="btn btn-secondary" title="XML" target="_blank">
                                            <i class="fas fa-file-code"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=download-danfe&id=<?= $nota['id'] ?>" 
                                           class="btn btn-warning" title="DANFE" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" 
                                                onclick="cancelarNota(<?= $nota['id'] ?>)" title="Cancelar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
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

<script>
function emitirNota(id) {
    if (confirm('Confirma a emissão desta nota fiscal para a SEFAZ?')) {
        window.location.href = '<?= BASE_URL ?>/?page=notas-fiscais&action=emitir&id=' + id;
    }
}

function cancelarNota(id) {
    const justificativa = prompt('Informe a justificativa do cancelamento (mínimo 15 caracteres):');
    if (justificativa && justificativa.length >= 15) {
        window.location.href = '<?= BASE_URL ?>/?page=notas-fiscais&action=cancelar&id=' + id + '&justificativa=' + encodeURIComponent(justificativa);
    } else if (justificativa) {
        alert('A justificativa deve ter no mínimo 15 caracteres.');
    }
}

$(document).ready(function() {
    if (typeof $.fn.dataTable !== 'undefined') {
        $('#tabelaNotas').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json' },
            order: [[2, 'desc']],
            pageLength: 25
        });
    }
});
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
