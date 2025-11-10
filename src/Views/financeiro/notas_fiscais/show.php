<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-file-invoice"></i> Detalhes da Nota Fiscal</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=notas-fiscais">Notas Fiscais</a></li>
                    <li class="breadcrumb-item active">Nota #<?= htmlspecialchars($nota['numero']) ?></li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <?php if ($nota['status_sefaz'] === 'rascunho'): ?>
                <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=edit&id=<?= $nota['id'] ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <button type="button" class="btn btn-success" onclick="emitirNota(<?= $nota['id'] ?>)">
                    <i class="fas fa-paper-plane"></i> Emitir
                </button>
            <?php endif; ?>

            <?php if ($nota['status_sefaz'] === 'autorizada'): ?>
                <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=download-xml&id=<?= $nota['id'] ?>" 
                   class="btn btn-secondary" target="_blank">
                    <i class="fas fa-file-code"></i> Baixar XML
                </a>
                <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=download-danfe&id=<?= $nota['id'] ?>" 
                   class="btn btn-warning" target="_blank">
                    <i class="fas fa-file-pdf"></i> Imprimir DANFE
                </a>
                <button type="button" class="btn btn-danger" onclick="cancelarNota(<?= $nota['id'] ?>)">
                    <i class="fas fa-ban"></i> Cancelar NF-e
                </button>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/?page=notas-fiscais" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alerta de Status -->
    <?php
    $alertClass = match($nota['status_sefaz']) {
        'autorizada' => 'success',
        'cancelada' => 'danger',
        'rejeitada' => 'danger',
        'rascunho' => 'warning',
        'processando' => 'info',
        default => 'secondary'
    };
    ?>
    <div class="alert alert-<?= $alertClass ?> d-flex justify-content-between align-items-center">
        <div>
            <strong>Status da Nota:</strong> <?= ucfirst($nota['status_sefaz']) ?>
            <?php if (!empty($nota['mensagem_sefaz'])): ?>
                <br><small><?= htmlspecialchars($nota['mensagem_sefaz']) ?></small>
            <?php endif; ?>
        </div>
        <?php if ($nota['status_sefaz'] === 'autorizada'): ?>
            <span class="badge bg-success" style="font-size: 1.2em;">
                <i class="fas fa-check-circle"></i> AUTORIZADA
            </span>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Dados da Nota -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-alt"></i> Dados da Nota Fiscal
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-muted">Número</label>
                            <p class="mb-0"><strong><?= htmlspecialchars($nota['numero']) ?></strong></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Série</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['serie']) ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Tipo</label>
                            <p class="mb-0"><span class="badge bg-primary"><?= strtoupper($nota['tipo']) ?></span></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Modelo</label>
                            <p class="mb-0"><?= $nota['tipo'] === 'nfe' ? '55' : ($nota['tipo'] === 'nfce' ? '65' : 'SE') ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted">Natureza da Operação</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['natureza_operacao']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Data Emissão</label>
                            <p class="mb-0"><?= date('d/m/Y', strtotime($nota['data_emissao'])) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Data Saída/Entrada</label>
                            <p class="mb-0"><?= date('d/m/Y', strtotime($nota['data_saida_entrada'])) ?></p>
                        </div>

                        <?php if (!empty($nota['chave_acesso'])): ?>
                        <div class="col-12">
                            <label class="text-muted">Chave de Acesso</label>
                            <p class="mb-0 font-monospace"><?= htmlspecialchars($nota['chave_acesso']) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($nota['protocolo_autorizacao'])): ?>
                        <div class="col-md-6">
                            <label class="text-muted">Protocolo de Autorização</label>
                            <p class="mb-0 font-monospace"><?= htmlspecialchars($nota['protocolo_autorizacao']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Data/Hora Autorização</label>
                            <p class="mb-0"><?= !empty($nota['data_autorizacao']) ? date('d/m/Y H:i:s', strtotime($nota['data_autorizacao'])) : '-' ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Emitente -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-building"></i> Emitente
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="text-muted">Razão Social</label>
                            <p class="mb-0"><strong><?= htmlspecialchars($nota['emitente_razao_social']) ?></strong></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted">CNPJ</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['emitente_cnpj']) ?></p>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">Inscrição Estadual</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['emitente_ie']) ?></p>
                        </div>

                        <div class="col-md-8">
                            <label class="text-muted">Endereço Completo</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['emitente_endereco']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Destinatário -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-user"></i> Destinatário
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="text-muted">Nome/Razão Social</label>
                            <p class="mb-0"><strong><?= htmlspecialchars($nota['destinatario_nome']) ?></strong></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted">CPF/CNPJ</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['destinatario_cnpj']) ?></p>
                        </div>

                        <div class="col-md-12">
                            <label class="text-muted">Endereço Completo</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['destinatario_endereco']) ?></p>
                        </div>

                        <div class="col-md-8">
                            <label class="text-muted">Município</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['destinatario_municipio']) ?></p>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">UF</label>
                            <p class="mb-0"><?= htmlspecialchars($nota['destinatario_uf']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Itens da Nota -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-boxes"></i> Itens da Nota Fiscal
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Descrição</th>
                                    <th>NCM</th>
                                    <th>CFOP</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-center">Un</th>
                                    <th class="text-end">Vlr. Unit.</th>
                                    <th class="text-end">Vlr. Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $itens = json_decode($nota['itens_json'], true) ?? [];
                                $totalItens = 0;
                                foreach ($itens as $index => $item): 
                                    $totalItens += $item['valor_total'];
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($item['descricao']) ?></td>
                                    <td><?= htmlspecialchars($item['ncm']) ?></td>
                                    <td><?= htmlspecialchars($item['cfop']) ?></td>
                                    <td class="text-center"><?= number_format($item['quantidade'], 2, ',', '.') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($item['unidade']) ?></td>
                                    <td class="text-end">R$ <?= number_format($item['valor_unitario'], 2, ',', '.') ?></td>
                                    <td class="text-end"><strong>R$ <?= number_format($item['valor_total'], 2, ',', '.') ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="7" class="text-end"><strong>Total dos Itens:</strong></td>
                                    <td class="text-end"><strong>R$ <?= number_format($totalItens, 2, ',', '.') ?></strong></td>
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
                        <!-- Coluna 1 - Valores -->
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Valor dos Produtos:</td>
                                    <td class="text-end"><strong>R$ <?= number_format($nota['valor_produtos'], 2, ',', '.') ?></strong></td>
                                </tr>
                                <tr>
                                    <td>(-) Desconto:</td>
                                    <td class="text-end text-danger">R$ <?= number_format($nota['valor_desconto'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(+) Frete:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_frete'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(+) Seguro:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_seguro'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(+) Outras Despesas:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_outras_despesas'], 2, ',', '.') ?></td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Base de Cálculo:</strong></td>
                                    <td class="text-end"><strong>R$ <?= number_format($nota['valor_base_calculo'], 2, ',', '.') ?></strong></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Coluna 2 - Impostos -->
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>ICMS:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_icms'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>ICMS ST:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_icms_st'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>IPI:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_ipi'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>PIS:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_pis'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>COFINS:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_cofins'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>ISS:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_iss'], 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>INSS:</td>
                                    <td class="text-end">R$ <?= number_format($nota['valor_inss'], 2, ',', '.') ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Total Geral -->
                        <div class="col-12">
                            <div class="alert alert-primary mb-0 d-flex justify-content-between align-items-center">
                                <div><strong>Valor Total da Nota Fiscal:</strong></div>
                                <h3 class="mb-0">R$ <?= number_format($nota['valor_total'], 2, ',', '.') ?></h3>
                            </div>
                        </div>

                        <?php if ($nota['valor_liquido'] != $nota['valor_total']): ?>
                        <div class="col-12">
                            <div class="alert alert-success mb-0 d-flex justify-content-between align-items-center">
                                <div><strong>Valor Líquido (após impostos retidos):</strong></div>
                                <h4 class="mb-0">R$ <?= number_format($nota['valor_liquido'], 2, ',', '.') ?></h4>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <?php if (!empty($nota['observacoes']) || !empty($nota['informacoes_adicionais'])): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-comment"></i> Informações Adicionais
                </div>
                <div class="card-body">
                    <?php if (!empty($nota['observacoes'])): ?>
                    <div class="mb-3">
                        <label class="text-muted"><strong>Observações Internas:</strong></label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($nota['observacoes'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($nota['informacoes_adicionais'])): ?>
                    <div>
                        <label class="text-muted"><strong>Informações Adicionais (DANFE):</strong></label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($nota['informacoes_adicionais'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Direita -->
        <div class="col-lg-4">
            <!-- Card de Resumo -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-info-circle"></i> Resumo
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><i class="fas fa-hashtag text-muted"></i> Número:</td>
                            <td class="text-end"><strong><?= htmlspecialchars($nota['numero']) ?></strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-tag text-muted"></i> Série:</td>
                            <td class="text-end"><?= htmlspecialchars($nota['serie']) ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-file text-muted"></i> Tipo:</td>
                            <td class="text-end"><span class="badge bg-primary"><?= strtoupper($nota['tipo']) ?></span></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar text-muted"></i> Emissão:</td>
                            <td class="text-end"><?= date('d/m/Y', strtotime($nota['data_emissao'])) ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-dollar-sign text-muted"></i> Valor Total:</td>
                            <td class="text-end"><strong>R$ <?= number_format($nota['valor_total'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-circle text-muted"></i> Status:</td>
                            <td class="text-end">
                                <span class="badge bg-<?= $alertClass ?>"><?= ucfirst($nota['status_sefaz']) ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Card de Ações Rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Ações Rápidas
                </div>
                <div class="card-body d-grid gap-2">
                    <?php if ($nota['status_sefaz'] === 'rascunho'): ?>
                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=edit&id=<?= $nota['id'] ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Editar Nota
                        </a>
                        <button type="button" class="btn btn-success" onclick="emitirNota(<?= $nota['id'] ?>)">
                            <i class="fas fa-paper-plane"></i> Emitir para SEFAZ
                        </button>
                        <button type="button" class="btn btn-danger" onclick="excluirRascunho(<?= $nota['id'] ?>)">
                            <i class="fas fa-trash"></i> Excluir Rascunho
                        </button>
                    <?php endif; ?>

                    <?php if ($nota['status_sefaz'] === 'autorizada'): ?>
                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=download-xml&id=<?= $nota['id'] ?>" 
                           class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-file-code"></i> Baixar XML
                        </a>
                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=download-danfe&id=<?= $nota['id'] ?>" 
                           class="btn btn-outline-warning" target="_blank">
                            <i class="fas fa-file-pdf"></i> Imprimir DANFE
                        </a>
                        <a href="<?= BASE_URL ?>/?page=notas-fiscais&action=enviar-email&id=<?= $nota['id'] ?>" 
                           class="btn btn-outline-info">
                            <i class="fas fa-envelope"></i> Enviar por E-mail
                        </a>
                        <button type="button" class="btn btn-outline-primary" onclick="consultarStatus(<?= $nota['id'] ?>)">
                            <i class="fas fa-sync"></i> Consultar Status
                        </button>
                        <hr>
                        <button type="button" class="btn btn-danger" onclick="cancelarNota(<?= $nota['id'] ?>)">
                            <i class="fas fa-ban"></i> Cancelar NF-e
                        </button>
                        <button type="button" class="btn btn-warning" onclick="cartaCorrecao(<?= $nota['id'] ?>)">
                            <i class="fas fa-edit"></i> Carta de Correção
                        </button>
                    <?php endif; ?>

                    <?php if ($nota['status_sefaz'] === 'cancelada'): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-ban"></i> Nota cancelada
                            <?php if (!empty($nota['data_cancelamento'])): ?>
                                <br><small>Em <?= date('d/m/Y H:i', strtotime($nota['data_cancelamento'])) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card de Auditoria -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Auditoria
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><small class="text-muted">Criado por:</small></td>
                            <td class="text-end"><small><?= htmlspecialchars($nota['criado_por_nome'] ?? 'Sistema') ?></small></td>
                        </tr>
                        <tr>
                            <td><small class="text-muted">Criado em:</small></td>
                            <td class="text-end"><small><?= date('d/m/Y H:i', strtotime($nota['criado_em'])) ?></small></td>
                        </tr>
                        <?php if (!empty($nota['atualizado_em'])): ?>
                        <tr>
                            <td><small class="text-muted">Atualizado em:</small></td>
                            <td class="text-end"><small><?= date('d/m/Y H:i', strtotime($nota['atualizado_em'])) ?></small></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($nota['data_autorizacao'])): ?>
                        <tr>
                            <td><small class="text-muted">Autorizado em:</small></td>
                            <td class="text-end"><small><?= date('d/m/Y H:i', strtotime($nota['data_autorizacao'])) ?></small></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function emitirNota(id) {
    if (confirm('Confirma a emissão desta nota fiscal para a SEFAZ?\n\nApós a emissão, a nota não poderá ser editada.')) {
        window.location.href = '<?= BASE_URL ?>/?page=notas-fiscais&action=emitir&id=' + id;
    }
}

function cancelarNota(id) {
    const justificativa = prompt('Informe a justificativa do cancelamento (mínimo 15 caracteres):');
    if (justificativa && justificativa.length >= 15) {
        if (confirm('Confirma o cancelamento desta nota fiscal na SEFAZ?')) {
            window.location.href = '<?= BASE_URL ?>/?page=notas-fiscais&action=cancelar&id=' + id + 
                                   '&justificativa=' + encodeURIComponent(justificativa);
        }
    } else if (justificativa) {
        alert('A justificativa deve ter no mínimo 15 caracteres.');
    }
}

function excluirRascunho(id) {
    if (confirm('Confirma a exclusão permanente deste rascunho?')) {
        window.location.href = '<?= BASE_URL ?>/?page=notas-fiscais&action=delete&id=' + id;
    }
}

function consultarStatus(id) {
    alert('Consultando status na SEFAZ...');
    window.location.href = '<?= BASE_URL ?>/?page=notas-fiscais&action=consultar-status&id=' + id;
}

function cartaCorrecao(id) {
    const correcao = prompt('Informe a correção a ser feita (mínimo 15 caracteres):');
    if (correcao && correcao.length >= 15) {
        window.location.href = '<?= BASE_URL ?>/?page=notas-fiscais&action=carta-correcao&id=' + id + 
                               '&correcao=' + encodeURIComponent(correcao);
    } else if (correcao) {
        alert('A correção deve ter no mínimo 15 caracteres.');
    }
}
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
