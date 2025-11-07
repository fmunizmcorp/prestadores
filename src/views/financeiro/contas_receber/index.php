<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-hand-holding-usd"></i> Contas a Receber</h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=financeiro&action=conta-receber-create" class="btn btn-success">
                <i class="fas fa-plus"></i> Nova Conta a Receber
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3>R$ <?= number_format($stats['total_pendente'] ?? 0, 2, ',', '.') ?></h3>
                    <p class="mb-0">Total a Receber</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h3>R$ <?= number_format($stats['total_vencido'] ?? 0, 2, ',', '.') ?></h3>
                    <p class="mb-0">Vencido</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3>R$ <?= number_format($stats['total_recebido_mes'] ?? 0, 2, ',', '.') ?></h3>
                    <p class="mb-0">Recebido no Mês</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h3><?= $stats['vencendo_7_dias'] ?? 0 ?></h3>
                    <p class="mb-0">Vencendo em 7 dias</p>
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
            <form method="GET" action="<?= BASE_URL ?>/">
                <input type="hidden" name="page" value="financeiro">
                <input type="hidden" name="action" value="contas-receber">
                <div class="row">
                    <div class="col-md-3">
                        <label>Buscar</label>
                        <input type="text" name="busca" class="form-control" 
                               value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" 
                               placeholder="Descrição ou documento...">
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendente" <?= ($filtros['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="recebido" <?= ($filtros['status'] ?? '') === 'recebido' ? 'selected' : '' ?>>Recebido</option>
                            <option value="vencida" <?= ($filtros['status'] ?? '') === 'vencida' ? 'selected' : '' ?>>Vencida</option>
                            <option value="cancelada" <?= ($filtros['status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Categoria</label>
                        <select name="categoria_id" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filtros['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $filtros['data_inicio'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $filtros['data_fim'] ?? '' ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Contas -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabelaContas">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Descrição</th>
                            <th>Cliente</th>
                            <th>Categoria</th>
                            <th>Emissão</th>
                            <th>Vencimento</th>
                            <th class="text-end">Valor Original</th>
                            <th class="text-end">Valor Recebido</th>
                            <th class="text-end">Saldo</th>
                            <th>Boleto</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($contas)): ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted">Nenhuma conta encontrada</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($contas as $conta): 
                                $statusClass = match($conta['status']) {
                                    'recebido' => 'success',
                                    'pendente' => 'warning',
                                    'vencida' => 'danger',
                                    'cancelada' => 'secondary',
                                    default => 'info'
                                };
                                $saldo = $conta['valor_original'] - $conta['valor_recebido'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($conta['numero_documento']) ?></td>
                                <td><?= htmlspecialchars($conta['descricao']) ?></td>
                                <td><?= htmlspecialchars($conta['cliente_nome'] ?? '-') ?></td>
                                <td><small><?= htmlspecialchars($conta['categoria_nome'] ?? '-') ?></small></td>
                                <td><?= date('d/m/Y', strtotime($conta['data_emissao'])) ?></td>
                                <td>
                                    <?php if ($conta['status'] === 'vencida'): ?>
                                        <span class="text-danger fw-bold"><?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></span>
                                    <?php else: ?>
                                        <?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">R$ <?= number_format($conta['valor_original'], 2, ',', '.') ?></td>
                                <td class="text-end">R$ <?= number_format($conta['valor_recebido'], 2, ',', '.') ?></td>
                                <td class="text-end fw-bold">R$ <?= number_format($saldo, 2, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if ($conta['boleto_id']): ?>
                                        <a href="<?= BASE_URL ?>/?page=financeiro&action=boleto-show&id=<?= $conta['boleto_id'] ?>" 
                                           class="btn btn-sm btn-info" target="_blank" title="Ver Boleto">
                                            <i class="fas fa-barcode"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($conta['status']) ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/?page=financeiro&action=conta-receber-show&id=<?= $conta['id'] ?>" 
                                           class="btn btn-primary" title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($conta['status'] === 'pendente' || $conta['status'] === 'vencida'): ?>
                                        <button type="button" class="btn btn-success" 
                                                onclick="modalReceber(<?= $conta['id'] ?>, <?= $saldo ?>)" title="Receber">
                                            <i class="fas fa-dollar-sign"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!$conta['boleto_id'] && $conta['status'] !== 'recebido'): ?>
                                        <button type="button" class="btn btn-info" 
                                                onclick="gerarBoleto(<?= $conta['id'] ?>)" title="Gerar Boleto">
                                            <i class="fas fa-barcode"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-success fw-bold">
                            <td colspan="6" class="text-end">TOTAIS:</td>
                            <td class="text-end">R$ <?= number_format(array_sum(array_column($contas, 'valor_original')), 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format(array_sum(array_column($contas, 'valor_recebido')), 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format(array_sum(array_map(fn($c) => $c['valor_original'] - $c['valor_recebido'], $contas)), 2, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber&p=<?= $i ?><?= !empty($filtros) ? '&' . http_build_query($filtros) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Recebimento -->
<div class="modal fade" id="modalRecebimento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Registrar Recebimento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=conta-receber-receber">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="id" id="recebimento_conta_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Data Recebimento <span class="text-danger">*</span></label>
                        <input type="date" name="data_recebimento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Valor Recebido <span class="text-danger">*</span></label>
                        <input type="number" name="valor_recebido" id="valor_recebido_input" class="form-control" 
                               step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label>Forma de Recebimento <span class="text-danger">*</span></label>
                        <select name="forma_recebimento" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="transferencia">Transferência</option>
                            <option value="debito">Débito</option>
                            <option value="credito">Crédito</option>
                            <option value="boleto">Boleto</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Desconto</label>
                            <input type="number" name="desconto" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Juros</label>
                            <input type="number" name="juros" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Multa</label>
                            <input type="number" name="multa" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Observações</label>
                        <textarea name="observacoes_recebimento" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Registrar Recebimento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modalReceber(contaId, saldo) {
    document.getElementById('recebimento_conta_id').value = contaId;
    document.getElementById('valor_recebido_input').value = saldo.toFixed(2);
    new bootstrap.Modal(document.getElementById('modalRecebimento')).show();
}

function gerarBoleto(contaId) {
    if (confirm('Deseja gerar boleto para esta conta?')) {
        window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=gerar-boleto&conta_id=' + contaId;
    }
}

// DataTables initialization
$(document).ready(function() {
    $('#tabelaContas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        order: [[5, 'asc']], // Order by vencimento
        pageLength: 25
    });
});
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
