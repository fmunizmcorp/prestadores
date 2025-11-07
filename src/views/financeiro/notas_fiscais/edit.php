<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-file-invoice"></i> Editar Nota Fiscal (Rascunho)</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=notas-fiscais">Notas Fiscais</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=notas-fiscais&action=show&id=<?= $nota['id'] ?>">
                        Nota #<?= htmlspecialchars($nota['numero']) ?>
                    </a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alerta -->
    <?php if ($nota['status_sefaz'] !== 'rascunho'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-ban"></i> <strong>Atenção:</strong>
            Esta nota não pode ser editada pois já foi emitida ou está em processamento.
            Status atual: <strong><?= ucfirst($nota['status_sefaz']) ?></strong>
        </div>
        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=show&id=<?= $nota['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> <strong>Editando Rascunho:</strong>
            Esta nota está em rascunho e pode ser editada livremente. Após salvar, você poderá emiti-la para a SEFAZ.
        </div>

        <form method="POST" action="<?= BASE_URL ?>/?page=notas-fiscais&action=update&id=<?= $nota['id'] ?>" id="formNotaFiscal">
            <?= csrf_token() ?>

            <!-- Dados da Nota -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-alt"></i> Dados da Nota Fiscal
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Nota <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo" id="tipoNota" required>
                                <option value="">Selecione...</option>
                                <option value="nfe" <?= $nota['tipo'] === 'nfe' ? 'selected' : '' ?>>NF-e - Nota Fiscal Eletrônica</option>
                                <option value="nfse" <?= $nota['tipo'] === 'nfse' ? 'selected' : '' ?>>NFS-e - Nota Fiscal de Serviço</option>
                                <option value="nfce" <?= $nota['tipo'] === 'nfce' ? 'selected' : '' ?>>NFC-e - Nota Fiscal Consumidor</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Série</label>
                            <input type="text" class="form-control" name="serie" value="<?= htmlspecialchars($nota['serie']) ?>" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" class="form-control" name="numero" value="<?= htmlspecialchars($nota['numero']) ?>" readonly>
                            <small class="text-muted">Não pode ser alterado</small>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Natureza da Operação <span class="text-danger">*</span></label>
                            <select class="form-select" name="natureza_operacao" required>
                                <option value="">Selecione...</option>
                                <option value="venda" <?= $nota['natureza_operacao'] === 'venda' ? 'selected' : '' ?>>Venda de Produtos</option>
                                <option value="servico" <?= $nota['natureza_operacao'] === 'servico' ? 'selected' : '' ?>>Prestação de Serviços</option>
                                <option value="devolucao" <?= $nota['natureza_operacao'] === 'devolucao' ? 'selected' : '' ?>>Devolução de Mercadorias</option>
                                <option value="transferencia" <?= $nota['natureza_operacao'] === 'transferencia' ? 'selected' : '' ?>>Transferência</option>
                                <option value="remessa" <?= $nota['natureza_operacao'] === 'remessa' ? 'selected' : '' ?>>Remessa para Conserto</option>
                                <option value="retorno" <?= $nota['natureza_operacao'] === 'retorno' ? 'selected' : '' ?>>Retorno de Mercadoria</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Data de Emissão <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="data_emissao" 
                                   value="<?= $nota['data_emissao'] ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Data de Saída/Entrada</label>
                            <input type="date" class="form-control" name="data_saida_entrada" 
                                   value="<?= $nota['data_saida_entrada'] ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Finalidade <span class="text-danger">*</span></label>
                            <select class="form-select" name="finalidade" required>
                                <option value="1" <?= $nota['finalidade'] == 1 ? 'selected' : '' ?>>1 - Normal</option>
                                <option value="2" <?= $nota['finalidade'] == 2 ? 'selected' : '' ?>>2 - Complementar</option>
                                <option value="3" <?= $nota['finalidade'] == 3 ? 'selected' : '' ?>>3 - Ajuste</option>
                                <option value="4" <?= $nota['finalidade'] == 4 ? 'selected' : '' ?>>4 - Devolução/Retorno</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emitente (Apenas visualização) -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-building"></i> Emitente (Sua Empresa)
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <strong>Emitente:</strong> <?= htmlspecialchars($nota['emitente_razao_social']) ?><br>
                        <strong>CNPJ:</strong> <?= htmlspecialchars($nota['emitente_cnpj']) ?><br>
                        <small class="text-muted">O emitente não pode ser alterado após a criação da nota.</small>
                    </div>
                    <input type="hidden" name="emitente_id" value="<?= $nota['emitente_id'] ?>">
                </div>
            </div>

            <!-- Destinatário (Apenas visualização) -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-user"></i> Destinatário (Cliente/Fornecedor)
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <strong>Destinatário:</strong> <?= htmlspecialchars($nota['destinatario_nome']) ?><br>
                        <strong>CPF/CNPJ:</strong> <?= htmlspecialchars($nota['destinatario_cnpj']) ?><br>
                        <small class="text-muted">O destinatário não pode ser alterado após a criação da nota.</small>
                    </div>
                    <input type="hidden" name="destinatario_id" value="<?= $nota['destinatario_id'] ?>">
                    <input type="hidden" name="destinatario_tipo" value="<?= $nota['destinatario_tipo'] ?>">
                </div>
            </div>

            <!-- Produtos/Serviços -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-boxes"></i> Produtos / Serviços
                    <button type="button" class="btn btn-sm btn-success float-end" id="btnAdicionarItem">
                        <i class="fas fa-plus"></i> Adicionar Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabelaItens">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 30%">Descrição</th>
                                    <th style="width: 10%">NCM/Código</th>
                                    <th style="width: 10%">Qtd</th>
                                    <th style="width: 10%">Unidade</th>
                                    <th style="width: 12%">Vlr. Unit.</th>
                                    <th style="width: 12%">Vlr. Total</th>
                                    <th style="width: 10%">CFOP</th>
                                    <th style="width: 6%">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="itensBody">
                                <!-- Items carregados via JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total dos Itens:</strong></td>
                                    <td><strong id="totalItens">R$ 0,00</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Valores e Impostos -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-calculator"></i> Valores e Impostos
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Coluna 1 - Valores Base -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Valores</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Valor dos Produtos</label>
                                <input type="text" class="form-control money" name="valor_produtos" 
                                       id="valorProdutos" value="<?= number_format($nota['valor_produtos'], 2, ',', '.') ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Desconto</label>
                                <input type="text" class="form-control money" name="valor_desconto" 
                                       id="valorDesconto" value="<?= number_format($nota['valor_desconto'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Frete</label>
                                <input type="text" class="form-control money" name="valor_frete" 
                                       id="valorFrete" value="<?= number_format($nota['valor_frete'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Seguro</label>
                                <input type="text" class="form-control money" name="valor_seguro" 
                                       id="valorSeguro" value="<?= number_format($nota['valor_seguro'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Outras Despesas</label>
                                <input type="text" class="form-control money" name="valor_outras" 
                                       id="valorOutras" value="<?= number_format($nota['valor_outras_despesas'], 2, ',', '.') ?>">
                            </div>
                        </div>

                        <!-- Coluna 2 - Impostos Federais -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Impostos Federais</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Base de Cálculo</label>
                                <input type="text" class="form-control money" name="valor_base_calculo" 
                                       id="valorBaseCalculo" value="<?= number_format($nota['valor_base_calculo'], 2, ',', '.') ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">IPI</label>
                                <input type="text" class="form-control money" name="valor_ipi" 
                                       id="valorIPI" value="<?= number_format($nota['valor_ipi'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">PIS</label>
                                <input type="text" class="form-control money" name="valor_pis" 
                                       id="valorPIS" value="<?= number_format($nota['valor_pis'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">COFINS</label>
                                <input type="text" class="form-control money" name="valor_cofins" 
                                       id="valorCOFINS" value="<?= number_format($nota['valor_cofins'], 2, ',', '.') ?>">
                            </div>
                        </div>

                        <!-- Coluna 3 - Impostos Estaduais/Municipais -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Impostos Estaduais/Municipais</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">ICMS</label>
                                <input type="text" class="form-control money" name="valor_icms" 
                                       id="valorICMS" value="<?= number_format($nota['valor_icms'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ICMS ST</label>
                                <input type="text" class="form-control money" name="valor_icms_st" 
                                       id="valorICMSST" value="<?= number_format($nota['valor_icms_st'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ISS</label>
                                <input type="text" class="form-control money" name="valor_iss" 
                                       id="valorISS" value="<?= number_format($nota['valor_iss'], 2, ',', '.') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">INSS</label>
                                <input type="text" class="form-control money" name="valor_inss" 
                                       id="valorINSS" value="<?= number_format($nota['valor_inss'], 2, ',', '.') ?>">
                            </div>
                        </div>

                        <!-- Totalizador -->
                        <div class="col-12">
                            <div class="alert alert-primary d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Valor Total da Nota Fiscal:</strong>
                                </div>
                                <h3 class="mb-0" id="valorTotalNota">R$ 0,00</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-comment"></i> Informações Adicionais
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Observações Internas</label>
                            <textarea class="form-control" name="observacoes" rows="4"><?= htmlspecialchars($nota['observacoes']) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Informações Adicionais (DANFE)</label>
                            <textarea class="form-control" name="informacoes_adicionais" rows="4"><?= htmlspecialchars($nota['informacoes_adicionais']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=show&id=<?= $nota['id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <div>
                            <button type="submit" name="acao" value="salvar" class="btn btn-primary me-2">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <button type="submit" name="acao" value="emitir" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Salvar e Emitir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Modal Adicionar Item (igual ao create.php) -->
<div class="modal fade" id="modalAdicionarItem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-box"></i> Adicionar Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Descrição do Item</label>
                        <input type="text" class="form-control" id="itemDescricao" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NCM/Código</label>
                        <input type="text" class="form-control" id="itemNCM">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="itemQuantidade" value="1" step="0.01" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unidade</label>
                        <select class="form-select" id="itemUnidade">
                            <option value="UN">UN - Unidade</option>
                            <option value="PC">PC - Peça</option>
                            <option value="KG">KG - Quilograma</option>
                            <option value="MT">MT - Metro</option>
                            <option value="LT">LT - Litro</option>
                            <option value="CX">CX - Caixa</option>
                            <option value="HR">HR - Hora</option>
                            <option value="SV">SV - Serviço</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valor Unitário</label>
                        <input type="text" class="form-control money" id="itemValorUnitario" value="0,00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CFOP</label>
                        <select class="form-select" id="itemCFOP">
                            <option value="5101">5101 - Venda produção própria</option>
                            <option value="5102">5102 - Venda mercadoria adquirida</option>
                            <option value="5103">5103 - Venda produção não industrializador</option>
                            <option value="5405">5405 - Venda produção (ST)</option>
                            <option value="5933">5933 - Prestação serviço tributado ISSQN</option>
                            <option value="6101">6101 - Venda produção própria (inter)</option>
                            <option value="6102">6102 - Venda mercadoria adquirida (inter)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarItem">
                    <i class="fas fa-check"></i> Adicionar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Carregar itens existentes da nota
let itensNota = <?= json_encode(json_decode($nota['itens_json'], true) ?? []) ?>;
let itemCounter = itensNota.length + 1;

$(document).ready(function() {
    // Máscaras
    $('.money').mask('#.##0,00', {reverse: true});

    // Renderizar itens existentes
    renderizarItens();
    calcularTotais();

    // Modal adicionar item
    $('#btnAdicionarItem').click(function() {
        $('#modalAdicionarItem').modal('show');
    });

    // Confirmar adicionar item
    $('#btnConfirmarItem').click(function() {
        const descricao = $('#itemDescricao').val();
        const ncm = $('#itemNCM').val();
        const qtd = parseFloat($('#itemQuantidade').val()) || 1;
        const unidade = $('#itemUnidade').val();
        const valorUnitStr = $('#itemValorUnitario').val();
        const valorUnit = parseFloat(valorUnitStr.replace(/\./g, '').replace(',', '.')) || 0;
        const cfop = $('#itemCFOP').val();
        const valorTotal = qtd * valorUnit;

        if (!descricao) {
            alert('Preencha a descrição do item!');
            return;
        }

        const item = {
            id: itemCounter++,
            descricao: descricao,
            ncm: ncm,
            quantidade: qtd,
            unidade: unidade,
            valor_unitario: valorUnit,
            valor_total: valorTotal,
            cfop: cfop
        };

        itensNota.push(item);
        renderizarItens();
        
        $('#itemDescricao').val('');
        $('#itemNCM').val('');
        $('#itemQuantidade').val('1');
        $('#itemValorUnitario').val('0,00');
        $('#modalAdicionarItem').modal('hide');

        calcularTotais();
    });

    // Recalcular ao alterar valores
    $('.money, #valorDesconto, #valorFrete, #valorSeguro, #valorOutras').on('input', function() {
        calcularTotais();
    });
});

function renderizarItens() {
    let html = '';
    
    itensNota.forEach(function(item) {
        html += `
            <tr data-item-id="${item.id}">
                <td>${item.id}</td>
                <td>${item.descricao}</td>
                <td>${item.ncm}</td>
                <td class="text-center">${item.quantidade}</td>
                <td class="text-center">${item.unidade}</td>
                <td class="text-end">R$ ${item.valor_unitario.toFixed(2).replace('.', ',')}</td>
                <td class="text-end">R$ ${item.valor_total.toFixed(2).replace('.', ',')}</td>
                <td class="text-center">${item.cfop}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removerItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    $('#itensBody').html(html);
}

function removerItem(itemId) {
    if (confirm('Confirma a exclusão deste item?')) {
        itensNota = itensNota.filter(item => item.id !== itemId);
        renderizarItens();
        calcularTotais();
    }
}

function calcularTotais() {
    let totalItens = 0;
    itensNota.forEach(function(item) {
        totalItens += item.valor_total;
    });

    const desconto = parseFloat($('#valorDesconto').val().replace(/\./g, '').replace(',', '.')) || 0;
    const frete = parseFloat($('#valorFrete').val().replace(/\./g, '').replace(',', '.')) || 0;
    const seguro = parseFloat($('#valorSeguro').val().replace(/\./g, '').replace(',', '.')) || 0;
    const outras = parseFloat($('#valorOutras').val().replace(/\./g, '').replace(',', '.')) || 0;
    const ipi = parseFloat($('#valorIPI').val().replace(/\./g, '').replace(',', '.')) || 0;
    const icmsST = parseFloat($('#valorICMSST').val().replace(/\./g, '').replace(',', '.')) || 0;

    const baseCalculo = totalItens - desconto + frete + seguro + outras;
    const valorTotal = baseCalculo + ipi + icmsST;

    $('#totalItens').text('R$ ' + totalItens.toFixed(2).replace('.', ','));
    $('#valorProdutos').val(totalItens.toFixed(2).replace('.', ','));
    $('#valorBaseCalculo').val(baseCalculo.toFixed(2).replace('.', ','));
    $('#valorTotalNota').text('R$ ' + valorTotal.toFixed(2).replace('.', ','));
}

$('#formNotaFiscal').submit(function(e) {
    if (itensNota.length === 0) {
        e.preventDefault();
        alert('Adicione pelo menos um item à nota fiscal!');
        return false;
    }

    $('<input>').attr({
        type: 'hidden',
        name: 'itens_json',
        value: JSON.stringify(itensNota)
    }).appendTo(this);

    return true;
});
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
