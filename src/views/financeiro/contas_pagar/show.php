<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-file-invoice-dollar"></i> Conta a Pagar #<?= htmlspecialchars($conta['numero_documento'] ?? $conta['id']) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar">Contas a Pagar</a></li>
                    <li class="breadcrumb-item active">Detalhes</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <?php if ($conta['status'] === 'pendente' || $conta['status'] === 'vencida'): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                <i class="fas fa-dollar-sign"></i> Registrar Pagamento
            </button>
            <?php endif; ?>
            <?php if ($conta['status'] !== 'cancelada' && $conta['status'] !== 'pago'): ?>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar">
                <i class="fas fa-times"></i> Cancelar Conta
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Alert -->
    <?php
    $statusClass = match($conta['status']) {
        'pago' => 'success',
        'pendente' => 'warning',
        'vencida' => 'danger',
        'cancelada' => 'secondary',
        default => 'info'
    };
    ?>
    <div class="alert alert-<?= $statusClass ?> alert-dismissible fade show">
        <strong>Status:</strong> <?= ucfirst($conta['status']) ?>
        <?php if ($conta['status'] === 'vencida'): ?>
            <br><small>Venceu em <?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></small>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="row">
        <!-- Informações Principais -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-info-circle"></i> Informações da Conta
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Descrição:</strong><br>
                            <span class="fs-5"><?= htmlspecialchars($conta['descricao']) ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Nº Documento:</strong><br>
                            <?= htmlspecialchars($conta['numero_documento'] ?? '-') ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Data Emissão:</strong><br>
                            <?= date('d/m/Y', strtotime($conta['data_emissao'])) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Fornecedor:</strong><br>
                            <?= htmlspecialchars($conta['fornecedor_nome'] ?? '-') ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Data Vencimento:</strong><br>
                            <span class="<?= $conta['status'] === 'vencida' ? 'text-danger fw-bold' : '' ?>">
                                <?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Dias:</strong><br>
                            <?php
                            $hoje = new DateTime();
                            $vencimento = new DateTime($conta['data_vencimento']);
                            $diff = $hoje->diff($vencimento);
                            $dias = $diff->days * ($vencimento < $hoje ? -1 : 1);
                            
                            if ($dias < 0) {
                                echo '<span class="text-danger fw-bold">' . abs($dias) . ' dias em atraso</span>';
                            } elseif ($dias == 0) {
                                echo '<span class="text-warning fw-bold">Vence hoje</span>';
                            } else {
                                echo '<span class="text-muted">Faltam ' . $dias . ' dias</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Categoria:</strong><br>
                            <?= htmlspecialchars($conta['categoria_nome'] ?? '-') ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Centro de Custo:</strong><br>
                            <?= htmlspecialchars($conta['centro_custo_nome'] ?? '-') ?>
                        </div>
                    </div>

                    <?php if (!empty($conta['tags'])): ?>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Tags:</strong><br>
                            <?php
                            $tags = explode(',', $conta['tags']);
                            foreach ($tags as $tag):
                            ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($conta['observacoes'])): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Observações:</strong><br>
                            <div class="border p-2 bg-light rounded">
                                <?= nl2br(htmlspecialchars($conta['observacoes'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Histórico de Pagamentos -->
            <?php if (!empty($pagamentos)): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-history"></i> Histórico de Pagamentos
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Forma</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">Desconto</th>
                                    <th class="text-end">Juros</th>
                                    <th class="text-end">Multa</th>
                                    <th class="text-end">Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagamentos as $pag): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pag['data_pagamento'])) ?></td>
                                    <td><?= ucfirst($pag['forma_pagamento']) ?></td>
                                    <td class="text-end">R$ <?= number_format($pag['valor'], 2, ',', '.') ?></td>
                                    <td class="text-end text-success">
                                        <?= $pag['desconto'] > 0 ? '- R$ ' . number_format($pag['desconto'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-end text-danger">
                                        <?= $pag['juros'] > 0 ? '+ R$ ' . number_format($pag['juros'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-end text-danger">
                                        <?= $pag['multa'] > 0 ? '+ R$ ' . number_format($pag['multa'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-end fw-bold">
                                        R$ <?= number_format($pag['valor'] - $pag['desconto'] + $pag['juros'] + $pag['multa'], 2, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $pag['status'] === 'confirmado' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($pag['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php if (!empty($pag['observacoes'])): ?>
                                <tr>
                                    <td colspan="8" class="bg-light">
                                        <small><strong>Obs:</strong> <?= htmlspecialchars($pag['observacoes']) ?></small>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Anexos -->
            <?php if (!empty($anexos)): ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-paperclip"></i> Anexos
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($anexos as $anexo): ?>
                        <a href="<?= BASE_URL ?>/uploads/financeiro/<?= htmlspecialchars($anexo['arquivo']) ?>" 
                           class="list-group-item list-group-item-action" target="_blank">
                            <i class="fas fa-file"></i> <?= htmlspecialchars($anexo['nome']) ?>
                            <small class="text-muted">(<?= date('d/m/Y H:i', strtotime($anexo['criado_em'])) ?>)</small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Card de Valores -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-dollar-sign"></i> Valores
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th>Valor Original:</th>
                            <td class="text-end fw-bold">R$ <?= number_format($conta['valor_original'], 2, ',', '.') ?></td>
                        </tr>
                        <?php if ($conta['desconto'] > 0): ?>
                        <tr class="text-success">
                            <th>(-) Desconto:</th>
                            <td class="text-end">R$ <?= number_format($conta['desconto'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($conta['juros'] > 0): ?>
                        <tr class="text-danger">
                            <th>(+) Juros:</th>
                            <td class="text-end">R$ <?= number_format($conta['juros'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($conta['multa'] > 0): ?>
                        <tr class="text-danger">
                            <th>(+) Multa:</th>
                            <td class="text-end">R$ <?= number_format($conta['multa'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="table-primary">
                            <th>Valor Total:</th>
                            <td class="text-end fw-bold fs-5">
                                R$ <?= number_format($conta['valor_original'] - $conta['desconto'] + $conta['juros'] + $conta['multa'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <tr class="table-success">
                            <th>Valor Pago:</th>
                            <td class="text-end fw-bold">R$ <?= number_format($conta['valor_pago'], 2, ',', '.') ?></td>
                        </tr>
                        <tr class="table-warning">
                            <th>Saldo Restante:</th>
                            <td class="text-end fw-bold fs-5">
                                R$ <?= number_format($conta['valor_original'] - $conta['valor_pago'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info"></i> Informações Adicionais
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Criado em:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($conta['criado_em'])) ?>
                        <?php if (!empty($conta['criado_por_nome'])): ?>
                            <br><small class="text-muted">por <?= htmlspecialchars($conta['criado_por_nome']) ?></small>
                        <?php endif; ?>
                    </p>

                    <?php if (!empty($conta['atualizado_em']) && $conta['atualizado_em'] != $conta['criado_em']): ?>
                    <p class="mb-2">
                        <strong>Atualizado em:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($conta['atualizado_em'])) ?>
                    </p>
                    <?php endif; ?>

                    <?php if ($conta['recorrente']): ?>
                    <p class="mb-2">
                        <strong>Recorrência:</strong><br>
                        <span class="badge bg-info"><?= ucfirst($conta['frequencia_recorrencia']) ?></span>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($conta['data_pagamento'])): ?>
                    <p class="mb-0">
                        <strong>Pago em:</strong><br>
                        <?= date('d/m/Y', strtotime($conta['data_pagamento'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Ações Rápidas
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar para Listagem
                        </a>
                        <?php if ($conta['status'] !== 'cancelada'): ?>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=conta-pagar-edit&id=<?= $conta['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Conta
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-info" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Registrar Pagamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=conta-pagar-pagar&id=<?= $conta['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="id" value="<?= $conta['id'] ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Data Pagamento <span class="text-danger">*</span></label>
                            <input type="date" name="data_pagamento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Valor Pago <span class="text-danger">*</span></label>
                            <input type="number" name="valor_pago" class="form-control" step="0.01" 
                                   value="<?= number_format($conta['valor_original'] - $conta['valor_pago'], 2, '.', '') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Forma de Pagamento <span class="text-danger">*</span></label>
                            <select name="forma_pagamento" class="form-select" required>
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
                        <div class="col-md-6 mb-3">
                            <label>Desconto</label>
                            <input type="number" name="desconto" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Juros</label>
                            <input type="number" name="juros" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Multa</label>
                            <input type="number" name="multa" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Observações do Pagamento</label>
                        <textarea name="observacoes_pagamento" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancelar Conta a Pagar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=conta-pagar-cancelar&id=<?= $conta['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="id" value="<?= $conta['id'] ?>">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                    </div>
                    <div class="mb-3">
                        <label>Motivo do Cancelamento <span class="text-danger">*</span></label>
                        <textarea name="motivo_cancelamento" class="form-control" rows="3" required 
                                  placeholder="Descreva o motivo do cancelamento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não, voltar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Sim, cancelar conta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
