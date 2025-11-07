<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-hand-holding-usd"></i> Nova Conta a Receber</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber">Contas a Receber</a></li>
                    <li class="breadcrumb-item active">Nova Conta</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=conta-receber-store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <!-- Informações Básicas -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-info-circle"></i> Informações Básicas
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Descrição <span class="text-danger">*</span></label>
                                <input type="text" name="descricao" class="form-control" required 
                                       value="<?= htmlspecialchars($_SESSION['old']['descricao'] ?? '') ?>"
                                       placeholder="Ex: Faturamento serviço X">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Número do Documento</label>
                                <input type="text" name="numero_documento" class="form-control"
                                       value="<?= htmlspecialchars($_SESSION['old']['numero_documento'] ?? '') ?>"
                                       placeholder="NF, Fatura, etc">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Valor <span class="text-danger">*</span></label>
                                <input type="number" name="valor_original" class="form-control" step="0.01" min="0" required
                                       value="<?= $_SESSION['old']['valor_original'] ?? '' ?>"
                                       placeholder="0.00" id="valor_total_input">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Cliente</label>
                                <input type="text" name="cliente_nome" class="form-control"
                                       value="<?= htmlspecialchars($_SESSION['old']['cliente_nome'] ?? '') ?>"
                                       placeholder="Nome do cliente">
                                <small class="text-muted">Digite manualmente ou selecione de cadastros futuros</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Data de Emissão</label>
                                <input type="date" name="data_emissao" class="form-control"
                                       value="<?= $_SESSION['old']['data_emissao'] ?? date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Data de Vencimento <span class="text-danger">*</span></label>
                                <input type="date" name="data_vencimento" class="form-control" required
                                       value="<?= $_SESSION['old']['data_vencimento'] ?? date('Y-m-d', strtotime('+30 days')) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classificação -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-tags"></i> Classificação
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Categoria <span class="text-danger">*</span></label>
                                <select name="categoria_id" class="form-select" required>
                                    <option value="">Selecione a categoria...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                                <?= ($_SESSION['old']['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= str_repeat('&nbsp;&nbsp;', $cat['nivel'] - 1) ?>
                                            <?= htmlspecialchars($cat['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Centro de Custo</label>
                                <select name="centro_custo_id" class="form-select">
                                    <option value="">Nenhum</option>
                                    <?php foreach ($centrosCusto as $cc): ?>
                                        <option value="<?= $cc['id'] ?>"
                                                <?= ($_SESSION['old']['centro_custo_id'] ?? '') == $cc['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cc['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Tags</label>
                                <input type="text" name="tags" class="form-control"
                                       value="<?= htmlspecialchars($_SESSION['old']['tags'] ?? '') ?>"
                                       placeholder="Ex: consultoria, desenvolvimento, suporte (separar por vírgula)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boleto Bancário -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-barcode"></i> Boleto Bancário
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="gerar_boleto" id="habilitar_boleto" 
                                   value="1" onchange="toggleBoleto()">
                            <label class="form-check-label fw-bold" for="habilitar_boleto">
                                <i class="fas fa-barcode"></i> Gerar boleto bancário para esta conta
                            </label>
                        </div>

                        <div id="opcoes_boleto" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Banco <span class="text-danger">*</span></label>
                                    <select name="banco_codigo" class="form-select">
                                        <option value="001">001 - Banco do Brasil</option>
                                        <option value="237">237 - Bradesco</option>
                                        <option value="341">341 - Itaú</option>
                                        <option value="033">033 - Santander</option>
                                        <option value="104">104 - Caixa Econômica</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Juros ao Dia (%)</label>
                                    <input type="number" name="juros_ao_dia" class="form-control" 
                                           step="0.01" min="0" max="100" value="0.033">
                                    <small class="text-muted">1% ao mês = 0.033% ao dia</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Multa Atraso (%)</label>
                                    <input type="number" name="multa_atraso" class="form-control" 
                                           step="0.01" min="0" max="100" value="2">
                                    <small class="text-muted">Geralmente 2%</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Instruções para o Banco</label>
                                <textarea name="instrucoes_boleto" class="form-control" rows="3"
                                          placeholder="Ex: Não receber após vencimento&#10;Protestar após 10 dias">Sr. Caixa, não receber após vencimento.
Após vencimento cobrar multa de 2% e juros de 0,033% ao dia.</textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Boleto:</strong> Será gerado automaticamente com código de barras e linha digitável.
                                O cliente poderá imprimir e pagar em qualquer banco.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parcelamento -->
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <i class="fas fa-credit-card"></i> Parcelamento (Opcional)
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="habilitar_parcelamento" 
                                   onchange="toggleParcelamento()">
                            <label class="form-check-label" for="habilitar_parcelamento">
                                Dividir em parcelas (gera múltiplas contas)
                            </label>
                        </div>

                        <div id="opcoes_parcelamento" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Número de Parcelas <span class="text-danger">*</span></label>
                                    <input type="number" name="numero_parcelas" class="form-control" 
                                           min="2" max="48" value="2"
                                           onchange="calcularParcelas()">
                                    <small class="text-muted">Mínimo: 2, Máximo: 48</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Taxa de Juros (% a.m.)</label>
                                    <input type="number" name="taxa_juros" class="form-control" 
                                           step="0.01" min="0" max="100" value="0"
                                           onchange="calcularParcelas()">
                                    <small class="text-muted">Juros compostos</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Valor da Parcela</label>
                                    <input type="text" id="valor_parcela_preview" class="form-control" readonly
                                           value="R$ 0,00">
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="boleto_parcelas" 
                                       <?= isset($_SESSION['old']['gerar_boleto']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="boleto_parcelas">
                                    Gerar boleto para cada parcela
                                </label>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Parcelamento:</strong> Serão criadas múltiplas contas com vencimentos mensais.
                                Se ativar boletos, cada parcela terá seu próprio boleto bancário.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recorrência -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-redo"></i> Recorrência (Opcional)
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="recorrente" id="habilitar_recorrencia"
                                   value="1" onchange="toggleRecorrencia()">
                            <label class="form-check-label" for="habilitar_recorrencia">
                                Receita recorrente (repetir automaticamente)
                            </label>
                        </div>

                        <div id="opcoes_recorrencia" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Frequência <span class="text-danger">*</span></label>
                                    <select name="frequencia_recorrencia" class="form-select">
                                        <option value="mensal">Mensal</option>
                                        <option value="bimestral">Bimestral</option>
                                        <option value="trimestral">Trimestral</option>
                                        <option value="semestral">Semestral</option>
                                        <option value="anual">Anual</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Próximo Vencimento</label>
                                    <input type="date" name="proxima_data_vencimento" class="form-control"
                                           value="<?= date('Y-m-d', strtotime('+1 month')) ?>">
                                </div>
                            </div>

                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Recorrência:</strong> O sistema criará automaticamente as próximas 12 ocorrências desta receita.
                                Ideal para mensalidades, assinaturas e contratos de prestação de serviços.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-comment"></i> Observações
                    </div>
                    <div class="card-body">
                        <textarea name="observacoes" class="form-control" rows="4"
                                  placeholder="Informações adicionais sobre esta receita..."><?= htmlspecialchars($_SESSION['old']['observacoes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Botões -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/?page=financeiro&action=contas-receber" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Salvar Conta a Receber
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBoleto() {
    const checkbox = document.getElementById('habilitar_boleto');
    const opcoes = document.getElementById('opcoes_boleto');
    opcoes.style.display = checkbox.checked ? 'block' : 'none';
}

function toggleParcelamento() {
    const checkbox = document.getElementById('habilitar_parcelamento');
    const opcoes = document.getElementById('opcoes_parcelamento');
    opcoes.style.display = checkbox.checked ? 'block' : 'none';
    
    if (checkbox.checked) {
        calcularParcelas();
    }
}

function toggleRecorrencia() {
    const checkbox = document.getElementById('habilitar_recorrencia');
    const opcoes = document.getElementById('opcoes_recorrencia');
    opcoes.style.display = checkbox.checked ? 'block' : 'none';
}

function calcularParcelas() {
    const valorTotal = parseFloat(document.getElementById('valor_total_input').value) || 0;
    const numeroParcelas = parseInt(document.querySelector('input[name="numero_parcelas"]').value) || 1;
    const taxaJuros = parseFloat(document.querySelector('input[name="taxa_juros"]').value) || 0;
    
    let valorParcela = valorTotal / numeroParcelas;
    
    // Calcular juros compostos se informado
    if (taxaJuros > 0) {
        const taxaDecimal = taxaJuros / 100;
        const coeficiente = Math.pow(1 + taxaDecimal, numeroParcelas);
        valorParcela = (valorTotal * coeficiente * taxaDecimal) / (coeficiente - 1);
    }
    
    document.getElementById('valor_parcela_preview').value = 
        'R$ ' + valorParcela.toFixed(2).replace('.', ',');
}

// Calcular quando mudar o valor total
document.getElementById('valor_total_input').addEventListener('input', function() {
    if (document.getElementById('habilitar_parcelamento').checked) {
        calcularParcelas();
    }
});
</script>

<?php 
unset($_SESSION['old']); 
require_once ROOT_PATH . '/src/views/layout/footer.php'; 
?>
