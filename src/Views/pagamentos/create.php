<?php
/**
 * View: Formulário de Criação de Pagamento
 * Controller: PagamentoController
 * Sprint 70.1 - Módulo de Pagamentos
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?page=pagamentos">Pagamentos</a></li>
            <li class="breadcrumb-item active">Novo Pagamento</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Novo Pagamento</h1>
            <p class="text-muted mb-0">Registre um novo pagamento ou recebimento</p>
        </div>
        <a href="?page=pagamentos" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <!-- Error Messages -->
    <?php if (isset($_SESSION['erro'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['erro'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['erro']); endif; ?>

    <?php if (isset($_SESSION['erros']) && !empty($_SESSION['erros'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Corrija os seguintes erros:</h6>
        <ul class="mb-0">
            <?php foreach ($_SESSION['erros'] as $erro): ?>
            <li><?= $erro ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['erros']); endif; ?>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Dados do Pagamento</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="?page=pagamentos&action=store" id="formPagamento">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="row g-3">
                            <!-- Tipo de Origem -->
                            <div class="col-md-4">
                                <label for="origem_tipo" class="form-label">
                                    Tipo de Origem <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="origem_tipo" name="origem_tipo" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($tiposOrigem ?? [] as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($_SESSION['form_data']['origem_tipo'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Selecione a origem deste pagamento</div>
                            </div>

                            <!-- ID da Origem -->
                            <div class="col-md-4">
                                <label for="origem_id" class="form-label">
                                    ID da Origem <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="origem_id" name="origem_id" 
                                       value="<?= $_SESSION['form_data']['origem_id'] ?? '' ?>" 
                                       min="1" required>
                                <div class="form-text">ID da conta a pagar/receber</div>
                            </div>

                            <!-- Valor -->
                            <div class="col-md-4">
                                <label for="valor" class="form-label">
                                    Valor <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" id="valor" name="valor" 
                                           value="<?= $_SESSION['form_data']['valor'] ?? '' ?>" 
                                           step="0.01" min="0.01" required>
                                </div>
                            </div>

                            <!-- Forma de Pagamento -->
                            <div class="col-md-4">
                                <label for="forma_pagamento" class="form-label">
                                    Forma de Pagamento <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($formasPagamento ?? [] as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($_SESSION['form_data']['forma_pagamento'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Data do Pagamento -->
                            <div class="col-md-4">
                                <label for="data_pagamento" class="form-label">
                                    Data do Pagamento <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="data_pagamento" name="data_pagamento" 
                                       value="<?= $_SESSION['form_data']['data_pagamento'] ?? date('Y-m-d') ?>" 
                                       required>
                            </div>

                            <!-- Valor da Taxa -->
                            <div class="col-md-4">
                                <label for="valor_taxa" class="form-label">Valor da Taxa</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" id="valor_taxa" name="valor_taxa" 
                                           value="<?= $_SESSION['form_data']['valor_taxa'] ?? '0.00' ?>" 
                                           step="0.01" min="0">
                                </div>
                                <div class="form-text">Taxas bancárias ou operacionais</div>
                            </div>

                            <!-- Valor do Desconto -->
                            <div class="col-md-4">
                                <label for="valor_desconto" class="form-label">Valor do Desconto</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" id="valor_desconto" name="valor_desconto" 
                                           value="<?= $_SESSION['form_data']['valor_desconto'] ?? '0.00' ?>" 
                                           step="0.01" min="0">
                                </div>
                            </div>

                            <!-- Banco ID (Opcional) -->
                            <div class="col-md-4">
                                <label for="banco_id" class="form-label">Banco</label>
                                <input type="number" class="form-control" id="banco_id" name="banco_id" 
                                       value="<?= $_SESSION['form_data']['banco_id'] ?? '' ?>" 
                                       min="1" placeholder="ID do banco">
                                <div class="form-text">Opcional - ID do banco na tabela bancos</div>
                            </div>

                            <!-- Conta Bancária ID (Opcional) -->
                            <div class="col-md-4">
                                <label for="conta_bancaria_id" class="form-label">Conta Bancária</label>
                                <input type="number" class="form-control" id="conta_bancaria_id" name="conta_bancaria_id" 
                                       value="<?= $_SESSION['form_data']['conta_bancaria_id'] ?? '' ?>" 
                                       min="1" placeholder="ID da conta">
                                <div class="form-text">Opcional - ID da conta bancária</div>
                            </div>

                            <!-- Número do Documento -->
                            <div class="col-md-4">
                                <label for="numero_documento" class="form-label">Número do Documento</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                       value="<?= $_SESSION['form_data']['numero_documento'] ?? '' ?>" 
                                       maxlength="50" placeholder="Nº cheque, boleto, etc.">
                            </div>

                            <!-- Número de Autorização -->
                            <div class="col-md-4">
                                <label for="numero_autorizacao" class="form-label">Número de Autorização</label>
                                <input type="text" class="form-control" id="numero_autorizacao" name="numero_autorizacao" 
                                       value="<?= $_SESSION['form_data']['numero_autorizacao'] ?? '' ?>" 
                                       maxlength="50" placeholder="Código de autorização">
                            </div>

                            <!-- Observações -->
                            <div class="col-12">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" 
                                          rows="4" maxlength="1000"><?= $_SESSION['form_data']['observacoes'] ?? '' ?></textarea>
                                <div class="form-text">Informações adicionais sobre o pagamento</div>
                            </div>
                        </div>

                        <!-- Valor Líquido Calculado -->
                        <div class="alert alert-info mt-4" id="valorLiquidoAlert" style="display: none;">
                            <strong>Valor Líquido:</strong> R$ <span id="valorLiquido">0,00</span>
                            <div class="small">Valor - Taxa + Desconto</div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="?page=pagamentos" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salvar Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Ajuda</h6>
                    <ul class="mb-0 small">
                        <li><strong>Tipo de Origem:</strong> Selecione se é uma conta a pagar, conta a receber ou lançamento direto</li>
                        <li><strong>ID da Origem:</strong> Informe o ID da conta ou lançamento relacionado</li>
                        <li><strong>Valor:</strong> Valor total do pagamento (obrigatório)</li>
                        <li><strong>Taxa:</strong> Taxas bancárias que serão descontadas do valor</li>
                        <li><strong>Desconto:</strong> Descontos obtidos que serão somados ao valor</li>
                        <li><strong>Valor Líquido:</strong> Calculado automaticamente: Valor - Taxa + Desconto</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular valor líquido automaticamente
document.addEventListener('DOMContentLoaded', function() {
    const valorInput = document.getElementById('valor');
    const taxaInput = document.getElementById('valor_taxa');
    const descontoInput = document.getElementById('valor_desconto');
    const valorLiquidoSpan = document.getElementById('valorLiquido');
    const valorLiquidoAlert = document.getElementById('valorLiquidoAlert');

    function calcularValorLiquido() {
        const valor = parseFloat(valorInput.value) || 0;
        const taxa = parseFloat(taxaInput.value) || 0;
        const desconto = parseFloat(descontoInput.value) || 0;
        
        const liquido = valor - taxa + desconto;
        
        if (valor > 0) {
            valorLiquidoSpan.textContent = liquido.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            valorLiquidoAlert.style.display = 'block';
        } else {
            valorLiquidoAlert.style.display = 'none';
        }
    }

    valorInput.addEventListener('input', calcularValorLiquido);
    taxaInput.addEventListener('input', calcularValorLiquido);
    descontoInput.addEventListener('input', calcularValorLiquido);

    // Calcular na inicialização se houver valor
    calcularValorLiquido();
});
</script>

<?php 
// Limpar form_data após renderizar
unset($_SESSION['form_data']);
require_once __DIR__ . '/../layouts/footer.php'; 
?>
