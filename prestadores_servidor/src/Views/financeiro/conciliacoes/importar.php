<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-file-import"></i> Importar Extrato Bancário (OFX)</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=conciliacoes">Conciliações</a></li>
                    <li class="breadcrumb-item active">Importar OFX</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Card Informativo -->
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle"></i> Sobre a Importação OFX</h5>
        <p class="mb-2">O formato OFX (Open Financial Exchange) é o padrão para troca de dados financeiros entre bancos e softwares. 
        Com ele você pode:</p>
        <ul class="mb-2">
            <li>Importar extratos bancários automaticamente</li>
            <li>Conciliar transações com lançamentos financeiros</li>
            <li>Identificar divergências entre sistema e banco</li>
            <li>Automatizar a reconciliação bancária</li>
        </ul>
        <p class="mb-0"><strong>Formato aceito:</strong> Arquivos .ofx exportados pelo seu banco (Banco do Brasil, Itaú, Bradesco, Santander, Caixa, etc.)</p>
    </div>

    <!-- Formulário de Upload -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-upload"></i> Fazer Upload do Arquivo OFX
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=processar-ofx" 
                  enctype="multipart/form-data" id="formImportarOFX">
                <?= csrf_token() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Conta Bancária <span class="text-danger">*</span></label>
                        <select class="form-select" name="conta_bancaria_id" id="contaBancariaSelect" required>
                            <option value="">Selecione a conta bancária...</option>
                            <?php if (isset($contas_bancarias) && !empty($contas_bancarias)): ?>
                                <?php foreach ($contas_bancarias as $conta): ?>
                                <option value="<?= $conta['id'] ?>" 
                                        data-banco="<?= htmlspecialchars($conta['banco']) ?>"
                                        data-agencia="<?= htmlspecialchars($conta['agencia']) ?>"
                                        data-numero="<?= htmlspecialchars($conta['numero']) ?>">
                                    <?= htmlspecialchars($conta['banco']) ?> - 
                                    Ag: <?= htmlspecialchars($conta['agencia']) ?> / 
                                    Cc: <?= htmlspecialchars($conta['numero']) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Selecione a conta correspondente ao extrato</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Arquivo OFX <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="arquivo_ofx" id="arquivoOFX" 
                               accept=".ofx,.OFX" required>
                        <small class="text-muted">Selecione o arquivo .ofx exportado do seu banco</small>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ignorarDuplicadas" 
                                   name="ignorar_duplicadas" value="1" checked>
                            <label class="form-check-label" for="ignorarDuplicadas">
                                Ignorar transações duplicadas (já importadas anteriormente)
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="conciliarAutomatico" 
                                   name="conciliar_automatico" value="1">
                            <label class="form-check-label" for="conciliarAutomatico">
                                Tentar conciliar automaticamente por valor e data
                            </label>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>/?page=financeiro&action=conciliacoes" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-file-import"></i> Importar Arquivo OFX
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview do Arquivo (após upload) -->
    <div class="card d-none" id="cardPreview">
        <div class="card-header bg-success text-white">
            <i class="fas fa-check-circle"></i> Preview da Importação
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <strong>Arquivo carregado com sucesso!</strong><br>
                Foram encontradas <strong id="totalTransacoes">0</strong> transações no período de 
                <strong id="periodoInicio">-</strong> a <strong id="periodoFim">-</strong>.
            </div>

            <div class="table-responsive">
                <table class="table table-striped" id="tabelaPreview">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th class="text-end">Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="previewBody">
                        <!-- Preenchido via JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="cancelarImportacao()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmarImportacao()">
                    <i class="fas fa-check"></i> Confirmar Importação
                </button>
            </div>
        </div>
    </div>

    <!-- Histórico de Importações -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-history"></i> Histórico de Importações OFX
        </div>
        <div class="card-body">
            <?php if (empty($historico)): ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> Nenhuma importação realizada ainda.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Conta Bancária</th>
                                <th>Arquivo</th>
                                <th>Período</th>
                                <th class="text-center">Transações</th>
                                <th class="text-center">Conciliadas</th>
                                <th>Usuário</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $import): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($import['criado_em'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($import['banco']) ?><br>
                                    <small class="text-muted">
                                        Ag: <?= htmlspecialchars($import['agencia']) ?> / 
                                        Cc: <?= htmlspecialchars($import['numero']) ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="font-monospace"><?= htmlspecialchars($import['nome_arquivo']) ?></small>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($import['data_inicio'])) ?> a 
                                    <?= date('d/m/Y', strtotime($import['data_fim'])) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= $import['total_transacoes'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $import['transacoes_conciliadas'] ?></span>
                                    <?php if ($import['transacoes_conciliadas'] < $import['total_transacoes']): ?>
                                        <br><small class="text-muted">
                                            (<?= round(($import['transacoes_conciliadas'] / $import['total_transacoes']) * 100) ?>%)
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($import['usuario_nome']) ?></td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/?page=financeiro&action=conciliacoes&importacao_id=<?= $import['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Ver Transações">
                                        <i class="fas fa-eye"></i>
                                    </a>
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

<!-- Modal de Progresso -->
<div class="modal fade" id="modalProgresso" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-spinner fa-spin"></i> Processando Importação</h5>
            </div>
            <div class="modal-body">
                <p>Aguarde enquanto processamos o arquivo OFX...</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 100%"></div>
                </div>
                <p class="mt-3 mb-0 text-center" id="mensagemProgresso">
                    <small>Analisando transações...</small>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Validação do arquivo
    $('#arquivoOFX').change(function() {
        const file = this.files[0];
        if (file) {
            const fileName = file.name.toLowerCase();
            if (!fileName.endsWith('.ofx')) {
                alert('Por favor, selecione um arquivo OFX válido.');
                $(this).val('');
                return;
            }

            // Verificar tamanho (máx 10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('O arquivo é muito grande. Tamanho máximo: 10MB');
                $(this).val('');
                return;
            }
        }
    });

    // Submit do formulário
    $('#formImportarOFX').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        
        // Mostrar modal de progresso
        $('#modalProgresso').modal('show');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#modalProgresso').modal('hide');
                
                try {
                    const dados = JSON.parse(response);
                    
                    if (dados.sucesso) {
                        // Mostrar mensagem de sucesso
                        alert(`Importação concluída com sucesso!\n\n` +
                              `Transações importadas: ${dados.total_importadas}\n` +
                              `Transações conciliadas: ${dados.total_conciliadas}\n` +
                              `Transações pendentes: ${dados.total_pendentes}`);
                        
                        // Redirecionar para conciliações
                        window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=conciliacoes';
                    } else {
                        alert('Erro ao importar: ' + dados.mensagem);
                    }
                } catch (e) {
                    console.error('Erro ao processar resposta:', e);
                    alert('Erro ao processar resposta do servidor.');
                }
            },
            error: function(xhr, status, error) {
                $('#modalProgresso').modal('hide');
                alert('Erro ao enviar arquivo: ' + error);
            }
        });

        return false;
    });
});

function cancelarImportacao() {
    $('#cardPreview').addClass('d-none');
    $('#arquivoOFX').val('');
}

function confirmarImportacao() {
    // Implementar confirmação
    alert('Importação confirmada!');
    window.location.href = '<?= BASE_URL ?>/?page=financeiro&action=conciliacoes';
}
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
