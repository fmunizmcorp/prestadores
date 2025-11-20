<?php
/**
 * View: Detalhes do Pagamento
 * Controller: PagamentoController
 * Sprint 70.1
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?page=pagamentos">Pagamentos</a></li>
            <li class="breadcrumb-item active">Pagamento #<?= $pagamento['id'] ?></li>
        </ol>
    </nav>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['sucesso'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['sucesso']); endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['erro'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['erro']); endif; ?>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Pagamento #<?= $pagamento['id'] ?></h1>
            <p class="text-muted mb-0">
                Status: 
                <?php
                $statusClass = [
                    'pendente' => 'warning',
                    'processado' => 'info',
                    'confirmado' => 'success',
                    'estornado' => 'danger',
                    'cancelado' => 'secondary'
                ];
                $badgeClass = $statusClass[$pagamento['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $badgeClass ?>">
                    <?= ucfirst($pagamento['status']) ?>
                </span>
            </p>
        </div>
        <a href="?page=pagamentos" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row">
        <!-- Dados Principais -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações do Pagamento</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Tipo de Origem</label>
                            <p class="mb-0 fw-bold"><?= ucfirst(str_replace('_', ' ', $pagamento['origem_tipo'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Origem</label>
                            <p class="mb-0 fw-bold">
                                #<?= $pagamento['origem_id'] ?>
                                <?php if (!empty($pagamento['origem_numero'])): ?>
                                (<?= $pagamento['origem_numero'] ?>)
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Forma de Pagamento</label>
                            <p class="mb-0 fw-bold"><?= ucfirst($pagamento['forma_pagamento']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Data do Pagamento</label>
                            <p class="mb-0 fw-bold"><?= date('d/m/Y', strtotime($pagamento['data_pagamento'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valores -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Valores</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Valor</label>
                            <p class="mb-0 h4 text-primary">R$ <?= number_format($pagamento['valor'], 2, ',', '.') ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Taxa</label>
                            <p class="mb-0 h4 text-danger">R$ <?= number_format($pagamento['valor_taxa'], 2, ',', '.') ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Desconto</label>
                            <p class="mb-0 h4 text-success">R$ <?= number_format($pagamento['valor_desconto'], 2, ',', '.') ?></p>
                        </div>
                        <div class="col-12">
                            <hr>
                            <label class="text-muted small">Valor Líquido</label>
                            <p class="mb-0 h3 text-info">R$ <?= number_format($pagamento['valor_liquido'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Bancários -->
            <?php if (!empty($pagamento['banco_id']) || !empty($pagamento['conta_bancaria_id'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Dados Bancários</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if (!empty($pagamento['banco_nome'])): ?>
                        <div class="col-md-6">
                            <label class="text-muted small">Banco</label>
                            <p class="mb-0"><?= htmlspecialchars($pagamento['banco_nome']) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($pagamento['conta_bancaria_numero'])): ?>
                        <div class="col-md-6">
                            <label class="text-muted small">Conta</label>
                            <p class="mb-0"><?= htmlspecialchars($pagamento['conta_bancaria_numero']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Observações -->
            <?php if (!empty($pagamento['observacoes'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Observações</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($pagamento['observacoes'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Ações e Auditoria -->
        <div class="col-md-4">
            <!-- Ações -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ações</h5>
                </div>
                <div class="card-body">
                    <?php if ($pagamento['status'] === 'pendente'): ?>
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalConfirmar">
                        <i class="fas fa-check me-2"></i>Confirmar Pagamento
                    </button>
                    <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalCancelar">
                        <i class="fas fa-ban me-2"></i>Cancelar
                    </button>
                    <?php endif; ?>

                    <?php if (in_array($pagamento['status'], ['processado', 'confirmado'])): ?>
                    <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalEstornar">
                        <i class="fas fa-undo me-2"></i>Estornar
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Auditoria -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Auditoria</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Criado por</label>
                        <p class="mb-0"><?= htmlspecialchars($pagamento['criador_nome'] ?? 'Sistema') ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Criado em</label>
                        <p class="mb-0"><?= date('d/m/Y H:i', strtotime($pagamento['criado_em'])) ?></p>
                    </div>
                    <?php if (!empty($pagamento['data_confirmacao'])): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Confirmado em</label>
                        <p class="mb-0"><?= date('d/m/Y H:i', strtotime($pagamento['data_confirmacao'])) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($pagamento['data_estorno'])): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Estornado em</label>
                        <p class="mb-0"><?= date('d/m/Y H:i', strtotime($pagamento['data_estorno'])) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Motivo do Estorno</label>
                        <p class="mb-0 text-danger"><?= htmlspecialchars($pagamento['motivo_estorno']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar -->
<div class="modal fade" id="modalConfirmar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="?page=pagamentos&action=confirmar">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" value="<?= $pagamento['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="numero_autorizacao" class="form-label">Número de Autorização</label>
                        <input type="text" class="form-control" id="numero_autorizacao" name="numero_autorizacao" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="observacao" class="form-label">Observação</label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Estornar -->
<div class="modal fade" id="modalEstornar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="?page=pagamentos&action=estornar">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" value="<?= $pagamento['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Estornar Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Esta ação irá reverter o pagamento e atualizar a conta de origem.
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo do Estorno <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Estornar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="?page=pagamentos&action=cancelar">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" value="<?= $pagamento['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Cancelar Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="motivo_cancelar" class="form-label">Motivo do Cancelamento <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivo_cancelar" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-warning">Cancelar Pagamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
