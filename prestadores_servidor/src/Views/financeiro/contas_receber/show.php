<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-hand-holding-usd"></i> Conta a Receber #<?= htmlspecialchars($conta['numero_documento'] ?? $conta['id']) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber">Contas a Receber</a></li>
                    <li class="breadcrumb-item active">Detalhes</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <?php if ($conta['status'] === 'pendente' || $conta['status'] === 'vencida'): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRecebimento">
                <i class="fas fa-dollar-sign"></i> Registrar Recebimento
            </button>
            <?php endif; ?>
            <?php if ($conta['status'] !== 'cancelada' && $conta['status'] !== 'recebido'): ?>
            <button type="button" class="btn btn-warning" onclick="gerarBoleto(<?= $conta['id'] ?>)">
                <i class="fas fa-barcode"></i> Gerar Boleto
            </button>
            <?php endif; ?>
            <?php if ($conta['status'] !== 'cancelada' && $conta['status'] !== 'recebido'): ?>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar">
                <i class="fas fa-times"></i> Cancelar Conta
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Alert -->
    <?php
    $statusClass = match($conta['status']) {
        'recebido' => 'success',
        'pendente' => 'warning',
        'vencida' => 'danger',
        'cancelada' => 'secondary',
        'parcial' => 'info',
        default => 'info'
    };
    ?>
    <div class="alert alert-<?= $statusClass ?> alert-dismissible fade show">
        <strong>Status:</strong> <?= ucfirst($conta['status']) ?>
        <?php if ($conta['status'] === 'vencida'): ?>
            <br><small>Venceu em <?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></small>
        <?php elseif ($conta['status'] === 'parcial'): ?>
            <br><small>Parcialmente recebido: R$ <?= number_format($conta['valor_recebido'], 2, ',', '.') ?> de R$ <?= number_format($conta['valor_total'], 2, ',', '.') ?></small>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="row">
        <!-- Informações Principais -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
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
                            <strong>Cliente:</strong><br>
                            <?= htmlspecialchars($conta['cliente_nome'] ?? '-') ?>
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
                        <div class="col-md-4">
                            <strong>Categoria:</strong><br>
                            <span class="badge bg-info"><?= htmlspecialchars($conta['categoria_nome'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Centro de Custo:</strong><br>
                            <span class="badge bg-secondary"><?= htmlspecialchars($conta['centro_custo_nome'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Forma de Recebimento:</strong><br>
                            <?= htmlspecialchars($conta['forma_recebimento'] ?? 'Não definida') ?>
                        </div>
                    </div>

                    <?php if (!empty($conta['observacoes'])): ?>
                    <div class="row">
                        <div class="col-12">
                            <strong>Observações:</strong><br>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($conta['observacoes'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Histórico de Recebimentos -->
            <?php if (!empty($recebimentos)): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-history"></i> Histórico de Recebimentos
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Desconto</th>
                                    <th>Juros</th>
                                    <th>Multa</th>
                                    <th>Total</th>
                                    <th>Forma</th>
                                    <th>Usuário</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recebimentos as $rec): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($rec['data_recebimento'])) ?></td>
                                    <td>R$ <?= number_format($rec['valor_recebido'], 2, ',', '.') ?></td>
                                    <td class="text-danger">
                                        <?= $rec['valor_desconto'] > 0 ? '-R$ ' . number_format($rec['valor_desconto'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-success">
                                        <?= $rec['valor_juros'] > 0 ? '+R$ ' . number_format($rec['valor_juros'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-success">
                                        <?= $rec['valor_multa'] > 0 ? '+R$ ' . number_format($rec['valor_multa'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="fw-bold">
                                        R$ <?= number_format(
                                            $rec['valor_recebido'] - $rec['valor_desconto'] + $rec['valor_juros'] + $rec['valor_multa'],
                                            2, ',', '.'
                                        ) ?>
                                    </td>
                                    <td><?= htmlspecialchars($rec['forma_recebimento']) ?></td>
                                    <td><small><?= htmlspecialchars($rec['usuario_nome'] ?? '-') ?></small></td>
                                    <td><small><?= htmlspecialchars($rec['observacoes'] ?? '-') ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Anexos -->
            <?php if (!empty($anexos)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-paperclip"></i> Anexos
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($anexos as $anexo): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file"></i>
                                <?= htmlspecialchars($anexo['nome_arquivo']) ?>
                                <br><small class="text-muted">
                                    Adicionado em <?= date('d/m/Y H:i', strtotime($anexo['created_at'])) ?>
                                    por <?= htmlspecialchars($anexo['usuario_nome']) ?>
                                </small>
                            </div>
                            <div>
                                <a href="<?= BASE_URL ?>/<?= $anexo['caminho'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            <!-- Card de Valores -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-dollar-sign"></i> Valores
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Valor Original:</span>
                            <strong>R$ <?= number_format($conta['valor_original'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <?php if ($conta['valor_desconto'] > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-success">
                            <span>Desconto:</span>
                            <strong>-R$ <?= number_format($conta['valor_desconto'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($conta['valor_juros'] > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-danger">
                            <span>Juros:</span>
                            <strong>+R$ <?= number_format($conta['valor_juros'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($conta['valor_multa'] > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-danger">
                            <span>Multa:</span>
                            <strong>+R$ <?= number_format($conta['valor_multa'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between fs-5">
                            <span>Valor Total:</span>
                            <strong>R$ <?= number_format($conta['valor_total'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <?php if ($conta['valor_recebido'] > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-success">
                            <span>Valor Recebido:</span>
                            <strong>R$ <?= number_format($conta['valor_recebido'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between fs-5 fw-bold text-<?= $conta['valor_recebido'] >= $conta['valor_total'] ? 'success' : 'danger' ?>">
                            <span>Saldo:</span>
                            <strong>R$ <?= number_format($conta['valor_total'] - $conta['valor_recebido'], 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info"></i> Informações Adicionais
                </div>
                <div class="card-body">
                    <?php if (!empty($conta['projeto_nome'])): ?>
                    <div class="mb-2">
                        <strong>Projeto:</strong><br>
                        <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $conta['projeto_id'] ?>">
                            <?= htmlspecialchars($conta['projeto_nome']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($conta['contrato_numero'])): ?>
                    <div class="mb-2">
                        <strong>Contrato:</strong><br>
                        <a href="<?= BASE_URL ?>/?page=contratos&action=show&id=<?= $conta['contrato_id'] ?>">
                            <?= htmlspecialchars($conta['contrato_numero']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="mb-2">
                        <strong>Criado em:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($conta['created_at'])) ?>
                    </div>
                    <div class="mb-2">
                        <strong>Criado por:</strong><br>
                        <?= htmlspecialchars($conta['criador_nome'] ?? '-') ?>
                    </div>
                    <?php if ($conta['updated_at'] !== $conta['created_at']): ?>
                    <div class="mb-0">
                        <strong>Última atualização:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($conta['updated_at'])) ?>
                    </div>
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
                        <?php if ($conta['status'] !== 'recebido' && $conta['status'] !== 'cancelada'): ?>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber&do=edit&id=<?= $conta['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Conta
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <?php if (!empty($conta['boleto_id'])): ?>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=boletos&do=show&id=<?= $conta['boleto_id'] ?>" class="btn btn-warning" target="_blank">
                            <i class="fas fa-barcode"></i> Ver Boleto
                        </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Recebimento -->
<div class="modal fade" id="modalRecebimento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=contas-receber&do=receber" id="formRecebimento">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="conta_id" value="<?= $conta['id'] ?>">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-dollar-sign"></i> Registrar Recebimento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Saldo a receber:</strong> R$ <?= number_format($conta['valor_total'] - $conta['valor_recebido'], 2, ',', '.') ?>
                    </div>

                    <div class="mb-3">
                        <label for="data_recebimento" class="form-label">Data do Recebimento *</label>
                        <input type="date" class="form-control" id="data_recebimento" name="data_recebimento" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="valor_recebido" class="form-label">Valor Recebido *</label>
                        <input type="number" step="0.01" class="form-control" id="valor_recebido" name="valor_recebido" 
                               value="<?= $conta['valor_total'] - $conta['valor_recebido'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="forma_recebimento" class="form-label">Forma de Recebimento *</label>
                        <select class="form-select" id="forma_recebimento" name="forma_recebimento" required>
                            <option value="">Selecione...</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="transferencia">Transferência</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="boleto">Boleto</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valor_desconto" class="form-label">Desconto</label>
                                <input type="number" step="0.01" class="form-control" id="valor_desconto" name="valor_desconto" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valor_juros" class="form-label">Juros</label>
                                <input type="number" step="0.01" class="form-control" id="valor_juros" name="valor_juros" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="valor_multa" class="form-label">Multa</label>
                        <input type="number" step="0.01" class="form-control" id="valor_multa" name="valor_multa" value="0">
                    </div>

                    <div class="mb-3">
                        <label for="observacoes_recebimento" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes_recebimento" name="observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Recebimento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancelar Conta -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=contas-receber&do=cancelar">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="conta_id" value="<?= $conta['id'] ?>">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times"></i> Cancelar Conta a Receber</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Esta ação não pode ser desfeita!
                    </div>

                    <p>Tem certeza que deseja cancelar esta conta a receber?</p>
                    <p><strong>Conta:</strong> <?= htmlspecialchars($conta['descricao']) ?></p>
                    <p><strong>Valor:</strong> R$ <?= number_format($conta['valor_total'], 2, ',', '.') ?></p>

                    <div class="mb-3">
                        <label for="motivo_cancelamento" class="form-label">Motivo do Cancelamento *</label>
                        <textarea class="form-control" id="motivo_cancelamento" name="motivo_cancelamento" rows="3" required 
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

<script>
function gerarBoleto(contaId) {
    if (confirm('Deseja gerar um boleto para esta conta?')) {
        window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=boletos&do=gerar&conta_receber_id=' + contaId;
    }
}

// Validação do formulário de recebimento
document.getElementById('formRecebimento').addEventListener('submit', function(e) {
    const valorRecebido = parseFloat(document.getElementById('valor_recebido').value) || 0;
    const valorDesconto = parseFloat(document.getElementById('valor_desconto').value) || 0;
    const valorJuros = parseFloat(document.getElementById('valor_juros').value) || 0;
    const valorMulta = parseFloat(document.getElementById('valor_multa').value) || 0;
    
    const saldoDevedor = <?= $conta['valor_total'] - $conta['valor_recebido'] ?>;
    const totalReceber = valorRecebido - valorDesconto + valorJuros + valorMulta;
    
    if (valorRecebido <= 0) {
        e.preventDefault();
        alert('O valor recebido deve ser maior que zero!');
        return false;
    }
    
    if (totalReceber > (saldoDevedor * 1.5)) {
        if (!confirm('O valor total está muito acima do saldo devedor. Deseja continuar?')) {
            e.preventDefault();
            return false;
        }
    }
});
</script>

<style media="print">
    .btn, .modal, nav, .breadcrumb, .card-header .btn {
        display: none !important;
    }
    .alert-dismissible .btn-close {
        display: none !important;
    }
</style>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
