<?php
$pageTitle = 'Valores de Serviços - Contrato';
$activeMenu = 'contratos';
$breadcrumb = [
    ['label' => 'Contratos', 'url' => BASE_URL . '/?page=contratos'],
    ['label' => $contrato['numero_contrato'] ?? 'Contrato'],
    ['label' => 'Valores de Serviços']
];

require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-dollar-sign"></i> Valores de Serviços</h2>
        <p class="text-muted">Contrato: <strong><?= htmlspecialchars($contrato['numero_contrato'] ?? '') ?></strong></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= BASE_URL ?>/?page=servico-valores&action=create&contrato_id=<?= $contrato['id'] ?? '' ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Valor
        </a>
        <a href="<?= BASE_URL ?>/?page=contratos&action=show&id=<?= $contrato['id'] ?? '' ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Timeline de Valores -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-clock"></i> Timeline de Valores por Período</h5>
    </div>
    <div class="card-body">
        <?php if (empty($valores)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum valor cadastrado para este contrato.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Período</th>
                            <th>Tipo Remuneração</th>
                            <th>Valor Base</th>
                            <th>Hora Extra</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($valores as $valor): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($valor['servico_nome'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <small>
                                        <?= format_date($valor['data_inicio']) ?> até 
                                        <?= $valor['data_fim'] ? format_date($valor['data_fim']) : '<span class="badge bg-info">Indeterminado</span>' ?>
                                    </small>
                                </td>
                                <td><?= htmlspecialchars($valor['tipo_remuneracao'] ?? '-') ?></td>
                                <td><strong>R$ <?= number_format($valor['valor_base'] ?? 0, 2, ',', '.') ?></strong></td>
                                <td>
                                    <?php if (!empty($valor['valor_hora_extra'])): ?>
                                        R$ <?= number_format($valor['valor_hora_extra'], 2, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($valor['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/?page=servico-valores&action=show&id=<?= $valor['id'] ?>" 
                                           class="btn btn-info" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/?page=servico-valores&action=edit&id=<?= $valor['id'] ?>" 
                                           class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" title="Excluir"
                                                onclick="confirmarExclusao(<?= $valor['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este valor?</p>
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
function confirmarExclusao(id) {
    document.getElementById('formExcluir').action = '<?= BASE_URL ?>/?page=servico-valores&action=destroy&id=' + id;
    new bootstrap.Modal(document.getElementById('modalExcluir')).show();
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
