<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-money-bill-wave text-danger"></i> Editar Conta a Pagar</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar">Contas a Pagar</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar&show=<?= $conta['id'] ?>">
                        Conta #<?= $conta['id'] ?>
                    </a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alerta de Restrições -->
    <?php if ($conta['status'] === 'pago'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-ban"></i> <strong>Atenção:</strong>
            Esta conta já foi paga e não pode ser editada. Status: <strong>Pago</strong>
            <?php if (!empty($conta['data_pagamento'])): ?>
                em <?= date('d/m/Y', strtotime($conta['data_pagamento'])) ?>
            <?php endif; ?>
        </div>
        <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar&show=<?= $conta['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    <?php else: ?>
        <?php if ($conta['status'] === 'parcial'): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Atenção:</strong>
                Esta conta possui pagamentos parciais. Ao editar, você pode estar alterando valores já pagos.
                Valor pago: <strong>R$ <?= number_format($conta['valor_pago'], 2, ',', '.') ?></strong>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar&update=<?= $conta['id'] ?>" 
              id="formEditarConta">
            <?= csrf_token() ?>

            <!-- Informações Básicas -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-info-circle"></i> Informações Básicas
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Descrição <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="descricao" 
                                   value="<?= htmlspecialchars($conta['descricao']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tipo de Conta <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo" required>
                                <option value="fornecedor" <?= $conta['tipo'] === 'fornecedor' ? 'selected' : '' ?>>Fornecedor</option>
                                <option value="prestador" <?= $conta['tipo'] === 'prestador' ? 'selected' : '' ?>>Prestador de Serviço</option>
                                <option value="funcionario" <?= $conta['tipo'] === 'funcionario' ? 'selected' : '' ?>>Funcionário</option>
                                <option value="impostos" <?= $conta['tipo'] === 'impostos' ? 'selected' : '' ?>>Impostos e Taxas</option>
                                <option value="outras" <?= $conta['tipo'] === 'outras' ? 'selected' : '' ?>>Outras Despesas</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fornecedor/Credor <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="fornecedor_id" required>
                                <option value="">Selecione...</option>
                                <?php if (isset($fornecedores) && !empty($fornecedores)): ?>
                                    <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?= $fornecedor['id'] ?>" 
                                            <?= $fornecedor['id'] == $conta['fornecedor_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($fornecedor['nome']) ?> - <?= $fornecedor['cpf_cnpj'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Categoria Financeira <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="categoria_id" required>
                                <option value="">Selecione...</option>
                                <?php if (isset($categorias) && !empty($categorias)): ?>
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            <?= $cat['id'] == $conta['categoria_id'] ? 'selected' : '' ?>>
                                        <?= str_repeat('—', $cat['nivel']) ?> <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Centro de Custo</label>
                            <select class="form-select" name="centro_custo_id">
                                <option value="">Nenhum</option>
                                <?php if (isset($centros_custo) && !empty($centros_custo)): ?>
                                    <?php foreach ($centros_custo as $centro): ?>
                                    <option value="<?= $centro['id'] ?>" 
                                            <?= $centro['id'] == $conta['centro_custo_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($centro['codigo']) ?> - <?= htmlspecialchars($centro['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Projeto (opcional)</label>
                            <select class="form-select select2" name="projeto_id">
                                <option value="">Nenhum</option>
                                <?php if (isset($projetos) && !empty($projetos)): ?>
                                    <?php foreach ($projetos as $projeto): ?>
                                    <option value="<?= $projeto['id'] ?>" 
                                            <?= $projeto['id'] == $conta['projeto_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($projeto['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valores e Datas -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-dollar-sign"></i> Valores e Datas
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Valor Original <span class="text-danger">*</span></label>
                            <input type="text" class="form-control money" name="valor_original" 
                                   value="<?= number_format($conta['valor_original'], 2, ',', '.') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Desconto</label>
                            <input type="text" class="form-control money" name="desconto" 
                                   value="<?= number_format($conta['desconto'], 2, ',', '.') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Juros/Multa</label>
                            <input type="text" class="form-control money" name="juros" 
                                   value="<?= number_format($conta['juros'], 2, ',', '.') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Valor Final</label>
                            <input type="text" class="form-control money" name="valor_final" 
                                   value="<?= number_format($conta['valor_final'], 2, ',', '.') ?>" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Emissão <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="data_emissao" 
                                   value="<?= $conta['data_emissao'] ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Vencimento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="data_vencimento" 
                                   value="<?= $conta['data_vencimento'] ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Forma de Pagamento</label>
                            <select class="form-select" name="forma_pagamento">
                                <option value="dinheiro" <?= $conta['forma_pagamento'] === 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                                <option value="boleto" <?= $conta['forma_pagamento'] === 'boleto' ? 'selected' : '' ?>>Boleto</option>
                                <option value="transferencia" <?= $conta['forma_pagamento'] === 'transferencia' ? 'selected' : '' ?>>Transferência</option>
                                <option value="pix" <?= $conta['forma_pagamento'] === 'pix' ? 'selected' : '' ?>>PIX</option>
                                <option value="cartao_credito" <?= $conta['forma_pagamento'] === 'cartao_credito' ? 'selected' : '' ?>>Cartão de Crédito</option>
                                <option value="cartao_debito" <?= $conta['forma_pagamento'] === 'cartao_debito' ? 'selected' : '' ?>>Cartão de Débito</option>
                                <option value="cheque" <?= $conta['forma_pagamento'] === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="alert alert-info mb-0">
                            <strong>Valor Final Calculado:</strong> 
                            R$ <?= number_format($conta['valor_original'] - $conta['desconto'] + $conta['juros'], 2, ',', '.') ?>
                            <br><small>Valor Original - Desconto + Juros/Multa</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documento e Observações -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-file-alt"></i> Documento e Observações
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Número do Documento</label>
                            <input type="text" class="form-control" name="numero_documento" 
                                   value="<?= htmlspecialchars($conta['numero_documento']) ?>" 
                                   placeholder="Ex: NF 12345">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Nota Fiscal Vinculada</label>
                            <select class="form-select" name="nota_fiscal_id">
                                <option value="">Nenhuma</option>
                                <?php if (isset($notas_fiscais) && !empty($notas_fiscais)): ?>
                                    <?php foreach ($notas_fiscais as $nf): ?>
                                    <option value="<?= $nf['id'] ?>" 
                                            <?= $nf['id'] == $conta['nota_fiscal_id'] ? 'selected' : '' ?>>
                                        NF-e <?= $nf['numero'] ?> - R$ <?= number_format($nf['valor_total'], 2, ',', '.') ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Conta Bancária</label>
                            <select class="form-select" name="conta_bancaria_id">
                                <option value="">Selecione...</option>
                                <?php if (isset($contas_bancarias) && !empty($contas_bancarias)): ?>
                                    <?php foreach ($contas_bancarias as $cb): ?>
                                    <option value="<?= $cb['id'] ?>" 
                                            <?= $cb['id'] == $conta['conta_bancaria_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cb['banco']) ?> - Ag: <?= $cb['agencia'] ?> / Cc: <?= $cb['numero'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="3"><?= htmlspecialchars($conta['observacoes']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parcelamento (somente visualização se já houver parcelas) -->
            <?php if (!empty($conta['parcelas']) && $conta['parcelas'] > 1): ?>
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-calendar-alt"></i> Parcelamento
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Esta conta está parcelada em <strong><?= $conta['parcelas'] ?> vezes</strong>.
                        Para alterar o parcelamento, edite cada parcela individualmente.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Botões de Ação -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-pagar&show=<?= $conta['id'] ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ width: '100%' });
    }

    // Máscaras de dinheiro
    $('.money').mask('#.##0,00', {reverse: true});

    // Calcular valor final automaticamente
    function calcularValorFinal() {
        const valorOriginal = parseFloat($('input[name="valor_original"]').val().replace(/\./g, '').replace(',', '.')) || 0;
        const desconto = parseFloat($('input[name="desconto"]').val().replace(/\./g, '').replace(',', '.')) || 0;
        const juros = parseFloat($('input[name="juros"]').val().replace(/\./g, '').replace(',', '.')) || 0;

        const valorFinal = valorOriginal - desconto + juros;
        
        $('input[name="valor_final"]').val(valorFinal.toFixed(2).replace('.', ','));
        
        // Atualizar alert
        $('.alert-info strong').next().text(' R$ ' + valorFinal.toFixed(2).replace('.', ','));
    }

    // Recalcular ao alterar valores
    $('input[name="valor_original"], input[name="desconto"], input[name="juros"]').on('input', calcularValorFinal);

    // Validação do formulário
    $('#formEditarConta').submit(function(e) {
        const valorFinal = parseFloat($('input[name="valor_final"]').val().replace(/\./g, '').replace(',', '.')) || 0;
        
        if (valorFinal <= 0) {
            e.preventDefault();
            alert('O valor final deve ser maior que zero!');
            return false;
        }

        const dataVencimento = new Date($('input[name="data_vencimento"]').val());
        const dataEmissao = new Date($('input[name="data_emissao"]').val());

        if (dataVencimento < dataEmissao) {
            if (!confirm('A data de vencimento é anterior à data de emissão. Deseja continuar?')) {
                e.preventDefault();
                return false;
            }
        }

        return true;
    });
});
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
