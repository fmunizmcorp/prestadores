<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-check-double"></i> Conciliação Bancária</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=conciliacoes">Conciliações</a></li>
                    <li class="breadcrumb-item active">Conciliar Transações</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=financeiro&action=importar-ofx" class="btn btn-success">
                <i class="fas fa-file-import"></i> Importar OFX
            </a>
            <a href="<?= BASE_URL ?>/?page=financeiro&action=conciliacoes" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Transações</h6>
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Conciliadas</h6>
                    <h3><?= $stats['conciliadas'] ?? 0 ?></h3>
                    <small><?= $stats['percentual_conciliado'] ?? 0 ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Pendentes</h6>
                    <h3><?= $stats['pendentes'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Saldo Divergente</h6>
                    <h3>R$ <?= number_format($stats['saldo_divergente'] ?? 0, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna Esquerda: Transações Bancárias -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-university"></i> Transações Bancárias (Extrato OFX)
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="filtroTransacoes" 
                                   placeholder="Buscar por descrição, valor...">
                        </div>
                    </div>

                    <!-- Lista de Transações -->
                    <div class="list-group" id="listaTransacoes">
                        <?php if (empty($transacoes_bancarias)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Nenhuma transação bancária pendente de conciliação.
                                <a href="<?= BASE_URL ?>/?page=financeiro&action=importar-ofx">Importar OFX</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($transacoes_bancarias as $trans): ?>
                            <div class="list-group-item list-group-item-action <?= $trans['conciliada'] ? 'list-group-item-success' : '' ?>" 
                                 data-transacao-id="<?= $trans['id'] ?>"
                                 data-valor="<?= $trans['valor'] ?>"
                                 data-data="<?= $trans['data'] ?>"
                                 onclick="selecionarTransacao(<?= $trans['id'] ?>)">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <?= htmlspecialchars($trans['descricao']) ?>
                                            <?php if ($trans['conciliada']): ?>
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-check"></i> Conciliada
                                                </span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($trans['data'])) ?>
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-university"></i> <?= htmlspecialchars($trans['banco']) ?>
                                        </small>
                                        <?php if (!empty($trans['documento'])): ?>
                                            <br><small class="text-muted">
                                                Doc: <?= htmlspecialchars($trans['documento']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="mb-0 <?= $trans['tipo'] === 'credito' ? 'text-success' : 'text-danger' ?>">
                                            <?= $trans['tipo'] === 'credito' ? '+' : '-' ?>
                                            R$ <?= number_format(abs($trans['valor']), 2, ',', '.') ?>
                                        </h5>
                                        <small class="text-muted"><?= ucfirst($trans['tipo']) ?></small>
                                    </div>
                                </div>

                                <?php if ($trans['conciliada'] && !empty($trans['lancamento_id'])): ?>
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-success">
                                            <i class="fas fa-link"></i> Vinculada ao lançamento #<?= $trans['lancamento_id'] ?>
                                            <a href="<?= BASE_URL ?>/?page=financeiro&action=lancamentos&show=<?= $trans['lancamento_id'] ?>" 
                                               class="ms-2" target="_blank">
                                                <i class="fas fa-external-link-alt"></i> Ver
                                            </a>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <?php if (!$trans['conciliada']): ?>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="event.stopPropagation(); buscarLancamentos(<?= $trans['id'] ?>)">
                                            <i class="fas fa-search"></i> Buscar Lançamentos
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Lançamentos Financeiros -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-file-invoice-dollar"></i> Lançamentos Financeiros (Sistema)
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="filtroLancamentos" 
                                   placeholder="Buscar lançamentos compatíveis...">
                        </div>
                    </div>

                    <!-- Informação da Transação Selecionada -->
                    <div class="alert alert-info" id="infoTransacaoSelecionada" style="display: none;">
                        <h6><i class="fas fa-info-circle"></i> Transação Selecionada:</h6>
                        <p class="mb-0">
                            <strong id="transDescricao">-</strong><br>
                            <small>
                                Data: <span id="transData">-</span> | 
                                Valor: <span id="transValor">-</span> | 
                                Tipo: <span id="transTipo">-</span>
                            </small>
                        </p>
                    </div>

                    <!-- Lista de Lançamentos -->
                    <div class="list-group" id="listaLancamentos">
                        <div class="alert alert-secondary text-center">
                            <i class="fas fa-hand-pointer"></i> 
                            Selecione uma transação bancária à esquerda para buscar lançamentos compatíveis.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de Ação Manual -->
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-plus"></i> Criar Lançamento Manual
                </div>
                <div class="card-body">
                    <p>Se não houver lançamento correspondente, você pode criar um novo lançamento manualmente:</p>
                    <button type="button" class="btn btn-warning" id="btnCriarLancamento" disabled>
                        <i class="fas fa-plus"></i> Criar Novo Lançamento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Conciliação -->
<div class="modal fade" id="modalConciliar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-double"></i> Confirmar Conciliação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Confirma a conciliação entre:</p>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="text-primary">Transação Bancária:</h6>
                        <p class="mb-0" id="modalTransacaoDesc">-</p>
                        <small class="text-muted" id="modalTransacaoDetalhes">-</small>
                    </div>
                </div>

                <div class="text-center mb-3">
                    <i class="fas fa-arrow-down fa-2x text-success"></i>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6 class="text-success">Lançamento Financeiro:</h6>
                        <p class="mb-0" id="modalLancamentoDesc">-</p>
                        <small class="text-muted" id="modalLancamentoDetalhes">-</small>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label class="form-label">Observações (opcional)</label>
                    <textarea class="form-control" id="observacoesConciliacao" rows="2" 
                              placeholder="Observações sobre esta conciliação..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarConciliacao">
                    <i class="fas fa-check"></i> Confirmar Conciliação
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let transacaoSelecionada = null;
let lancamentoSelecionado = null;

function selecionarTransacao(transacaoId) {
    transacaoSelecionada = transacaoId;
    
    // Destacar transação selecionada
    $('.list-group-item[data-transacao-id]').removeClass('active');
    $(`.list-group-item[data-transacao-id="${transacaoId}"]`).addClass('active');
    
    // Obter dados da transação
    const trans = $(`.list-group-item[data-transacao-id="${transacaoId}"]`);
    const valor = parseFloat(trans.data('valor'));
    const data = trans.data('data');
    const descricao = trans.find('h6').first().text().trim();
    
    // Atualizar info da transação
    $('#infoTransacaoSelecionada').show();
    $('#transDescricao').text(descricao);
    $('#transData').text(data);
    $('#transValor').text('R$ ' + Math.abs(valor).toFixed(2).replace('.', ','));
    $('#transTipo').text(valor > 0 ? 'Crédito' : 'Débito');
    
    // Habilitar botão de criar lançamento
    $('#btnCriarLancamento').prop('disabled', false);
    
    // Buscar lançamentos automaticamente
    buscarLancamentos(transacaoId);
}

function buscarLancamentos(transacaoId) {
    const trans = $(`.list-group-item[data-transacao-id="${transacaoId}"]`);
    const valor = parseFloat(trans.data('valor'));
    const data = trans.data('data');
    
    $('#listaLancamentos').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>');
    
    $.ajax({
        url: '<?= BASE_URL ?>/?page=financeiro&action=buscar-lancamentos-conciliacao',
        method: 'GET',
        data: { 
            transacao_id: transacaoId,
            valor: valor,
            data: data 
        },
        success: function(response) {
            const lancamentos = JSON.parse(response);
            
            if (lancamentos.length === 0) {
                $('#listaLancamentos').html(
                    '<div class="alert alert-warning">' +
                    '<i class="fas fa-exclamation-triangle"></i> Nenhum lançamento compatível encontrado.' +
                    '</div>'
                );
                return;
            }
            
            let html = '';
            lancamentos.forEach(function(lanc) {
                const compatibilidade = lanc.score || 0;
                const badgeClass = compatibilidade >= 90 ? 'success' : (compatibilidade >= 70 ? 'warning' : 'secondary');
                
                html += `
                    <div class="list-group-item list-group-item-action" onclick="selecionarLancamento(${lanc.id})">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    ${lanc.descricao}
                                    <span class="badge bg-${badgeClass} ms-2">${compatibilidade}% compatível</span>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> ${lanc.data_formatada}
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-folder"></i> ${lanc.categoria}
                                </small>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 ${lanc.valor > 0 ? 'text-success' : 'text-danger'}">
                                    R$ ${Math.abs(lanc.valor).toFixed(2).replace('.', ',')}
                                </h5>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-success" 
                                    onclick="event.stopPropagation(); conciliar(${transacaoId}, ${lanc.id})">
                                <i class="fas fa-check"></i> Conciliar
                            </button>
                        </div>
                    </div>
                `;
            });
            
            $('#listaLancamentos').html(html);
        },
        error: function() {
            $('#listaLancamentos').html(
                '<div class="alert alert-danger">' +
                '<i class="fas fa-times-circle"></i> Erro ao buscar lançamentos.' +
                '</div>'
            );
        }
    });
}

function selecionarLancamento(lancamentoId) {
    lancamentoSelecionado = lancamentoId;
    $('.list-group-item').removeClass('border-success');
    $(event.target).closest('.list-group-item').addClass('border-success border-3');
}

function conciliar(transacaoId, lancamentoId) {
    // Obter dados para o modal
    const trans = $(`.list-group-item[data-transacao-id="${transacaoId}"]`);
    const transDesc = trans.find('h6').first().text().trim();
    const transValor = trans.find('h5').text().trim();
    const transData = trans.find('small').first().text().trim();
    
    // Preencher modal
    $('#modalTransacaoDesc').text(transDesc);
    $('#modalTransacaoDetalhes').text(transData + ' - ' + transValor);
    
    // Mostrar modal
    $('#modalConciliar').modal('show');
    
    // Configurar botão de confirmar
    $('#btnConfirmarConciliacao').off('click').on('click', function() {
        confirmarConciliacao(transacaoId, lancamentoId);
    });
}

function confirmarConciliacao(transacaoId, lancamentoId) {
    const observacoes = $('#observacoesConciliacao').val();
    
    $.ajax({
        url: '<?= BASE_URL ?>/?page=financeiro&action=conciliar',
        method: 'POST',
        data: {
            transacao_id: transacaoId,
            lancamento_id: lancamentoId,
            observacoes: observacoes,
            csrf_token: $('input[name="csrf_token"]').val()
        },
        success: function(response) {
            const resultado = JSON.parse(response);
            
            if (resultado.sucesso) {
                $('#modalConciliar').modal('hide');
                alert('Conciliação realizada com sucesso!');
                location.reload();
            } else {
                alert('Erro ao conciliar: ' + resultado.mensagem);
            }
        },
        error: function() {
            alert('Erro ao processar conciliação.');
        }
    });
}

// Filtros
$('#filtroTransacoes').on('input', function() {
    const filtro = $(this).val().toLowerCase();
    $('#listaTransacoes .list-group-item').each(function() {
        const texto = $(this).text().toLowerCase();
        $(this).toggle(texto.includes(filtro));
    });
});

$('#btnCriarLancamento').click(function() {
    if (transacaoSelecionada) {
        window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=lancamentos&create=1&transacao_id=' + transacaoSelecionada;
    }
});
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
