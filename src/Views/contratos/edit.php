<?php
/**
 * View: Cadastro de Editar Contrato
 * Controller: ContratoController
 * 
 * Formulário completo para cadastrar novo contrato com:
 * - Dados principais
 * - Informações financeiras
 * - Serviços do contrato
 * - Gestores e responsáveis
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?page=contratos">Contratos</a></li>
            <li class="breadcrumb-item active">Editar Contrato</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Editar Contrato</h1>
            <p class="text-muted mb-0">Atualize as informações do contrato</p>
        </div>
        <a href="?page=contratos" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <!-- Form Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="?page=contratos&action=update&id=<?= $contrato['id'] ?>" id="formContrato" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <!-- Section 1: Dados Principais -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-file-contract text-primary me-2"></i>Dados Principais do Contrato
                    </h4>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="numero_contrato" class="form-label required">Número do Contrato</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="numero_contrato" 
                                   name="numero_contrato" 
                                   maxlength="50"
                                   required
                                   value="<?= htmlspecialchars($contrato['numero_contrato'] ?? '') ?>"
                                   placeholder="Ex: CONT-2025-001">
                            <div class="invalid-feedback">Por favor, informe o número do contrato.</div>
                        </div>

                        <div class="col-md-8">
                            <label for="empresa_tomadora_id" class="form-label required">Empresa Tomadora</label>
                            <select class="form-select select2" id="empresa_tomadora_id" name="empresa_tomadora_id" required>
                                <option value="">Selecione a empresa...</option>
                                <?php foreach ($empresasTomadoras ?? [] as $et): ?>
                                <option value="<?= $et['id'] ?>" <?= ($contrato['empresa_tomadora_id'] ?? '') == $et['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($et['nome_fantasia']) ?> - <?= htmlspecialchars($et['cnpj']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione a empresa tomadora.</div>
                        </div>

                        <div class="col-12">
                            <label for="objeto" class="form-label required">Objeto do Contrato</label>
                            <textarea class="form-control" 
                                      id="objeto" 
                                      name="objeto" 
                                      rows="3"
                                      required
                                      placeholder="Descreva resumidamente o objeto do contrato..."><?= htmlspecialchars($contrato['objeto'] ?? '') ?></textarea>
                            <div class="invalid-feedback">Por favor, informe o objeto do contrato.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="tipo_contrato" class="form-label">Tipo de Contrato</label>
                            <select class="form-select" id="tipo_contrato" name="tipo_contrato">
                                <option value="">Selecione...</option>
                                <option value="Prestação de Serviços">Prestação de Serviços</option>
                                <option value="Fornecimento">Fornecimento</option>
                                <option value="Outsourcing">Outsourcing</option>
                                <option value="Consultoria">Consultoria</option>
                                <option value="Misto">Misto</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="numero_processo" class="form-label">Número do Processo</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="numero_processo" 
                                   name="numero_processo" 
                                   maxlength="50"
                                   placeholder="Ex: PROC-2025-001">
                        </div>

                        <div class="col-md-4">
                            <label for="modalidade" class="form-label">Modalidade</label>
                            <select class="form-select" id="modalidade" name="modalidade">
                                <option value="">Selecione...</option>
                                <option value="Concorrência">Concorrência</option>
                                <option value="Tomada de Preços">Tomada de Preços</option>
                                <option value="Convite">Convite</option>
                                <option value="Pregão">Pregão</option>
                                <option value="Dispensa">Dispensa</option>
                                <option value="Inexigibilidade">Inexigibilidade</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="data_assinatura" class="form-label required">Data de Assinatura</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_assinatura" 
                                   name="data_assinatura"
                                   value="<?= htmlspecialchars($contrato['data_assinatura'] ?? '') ?>"
                                   required>
                            <div class="invalid-feedback">Por favor, informe a data de assinatura.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="data_inicio" class="form-label required">Data de Início</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_inicio" 
                                   name="data_inicio"
                                   value="<?= htmlspecialchars($contrato['data_inicio'] ?? '') ?>"
                                   required>
                            <div class="invalid-feedback">Por favor, informe a data de início.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="data_termino" class="form-label required">Data de Término</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_termino" 
                                   name="data_termino"
                                   value="<?= htmlspecialchars($contrato['data_termino'] ?? '') ?>"
                                   required>
                            <div class="invalid-feedback">Por favor, informe a data de término.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="prazo_meses" class="form-label">Prazo (meses)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="prazo_meses" 
                                   name="prazo_meses" 
                                   min="1"
                                   placeholder="Ex: 12">
                        </div>

                        <div class="col-md-4">
                            <label for="renovacao_automatica" class="form-label">Renovação Automática</label>
                            <select class="form-select" id="renovacao_automatica" name="renovacao_automatica">
                                <option value="0">Não</option>
                                <option value="1">Sim</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="prazo_renovacao_meses" class="form-label">Prazo Renovação (meses)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="prazo_renovacao_meses" 
                                   name="prazo_renovacao_meses" 
                                   min="1"
                                   placeholder="Ex: 12">
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Ativo" selected>Ativo</option>
                                <option value="Suspenso">Suspenso</option>
                                <option value="Encerrado">Encerrado</option>
                                <option value="Vencido">Vencido</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="arquivo_contrato" class="form-label">Arquivo do Contrato (PDF)</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="arquivo_contrato" 
                                   name="arquivo_contrato"
                                   accept=".pdf">
                            <small class="form-text text-muted">Tamanho máximo: 15MB</small>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Informações Financeiras -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-dollar-sign text-primary me-2"></i>Informações Financeiras
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="valor_total" class="form-label required">Valor Total do Contrato</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" 
                                       class="form-control mask-money" 
                                       id="valor_total" 
                                       name="valor_total"
                                       required
                                       placeholder="0,00">
                            </div>
                            <div class="invalid-feedback">Por favor, informe o valor total.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="tipo_valor" class="form-label">Tipo de Valor</label>
                            <select class="form-select" id="tipo_valor" name="tipo_valor">
                                <option value="">Selecione...</option>
                                <option value="Mensal">Mensal</option>
                                <option value="Anual">Anual</option>
                                <option value="Total">Total</option>
                                <option value="Por Demanda">Por Demanda</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="moeda" class="form-label">Moeda</label>
                            <select class="form-select" id="moeda" name="moeda">
                                <option value="BRL" selected>Real (BRL)</option>
                                <option value="USD">Dólar (USD)</option>
                                <option value="EUR">Euro (EUR)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                                <option value="">Selecione...</option>
                                <option value="Boleto">Boleto</option>
                                <option value="PIX">PIX</option>
                                <option value="Transferência">Transferência</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="dia_vencimento" class="form-label">Dia de Vencimento</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="dia_vencimento" 
                                   name="dia_vencimento" 
                                   min="1" 
                                   max="31"
                                   placeholder="Ex: 10">
                        </div>

                        <div class="col-md-4">
                            <label for="periodicidade_faturamento" class="form-label">Periodicidade</label>
                            <select class="form-select" id="periodicidade_faturamento" name="periodicidade_faturamento">
                                <option value="">Selecione...</option>
                                <option value="Mensal">Mensal</option>
                                <option value="Bimestral">Bimestral</option>
                                <option value="Trimestral">Trimestral</option>
                                <option value="Semestral">Semestral</option>
                                <option value="Anual">Anual</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="indice_reajuste" class="form-label">Índice de Reajuste</label>
                            <select class="form-select" id="indice_reajuste" name="indice_reajuste">
                                <option value="">Nenhum</option>
                                <option value="IPCA">IPCA</option>
                                <option value="IGP-M">IGP-M</option>
                                <option value="INPC">INPC</option>
                                <option value="IPC">IPC</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="data_proximo_reajuste" class="form-label">Data Próximo Reajuste</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_proximo_reajuste" 
                                   name="data_proximo_reajuste">
                        </div>

                        <div class="col-12">
                            <label for="observacoes_financeiras" class="form-label">Observações Financeiras</label>
                            <textarea class="form-control" 
                                      id="observacoes_financeiras" 
                                      name="observacoes_financeiras" 
                                      rows="2"
                                      placeholder="Informações adicionais sobre valores e pagamentos..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Gestores e Responsáveis -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-users text-primary me-2"></i>Gestores e Responsáveis
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="gestor_contrato_nome" class="form-label">Gestor do Contrato</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="gestor_contrato_nome" 
                                   name="gestor_contrato_nome" 
                                   maxlength="255"
                                   placeholder="Nome completo">
                        </div>

                        <div class="col-md-6">
                            <label for="gestor_contrato_email" class="form-label">E-mail do Gestor</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="gestor_contrato_email" 
                                   name="gestor_contrato_email" 
                                   maxlength="255"
                                   placeholder="email@exemplo.com">
                        </div>

                        <div class="col-md-6">
                            <label for="fiscal_contrato_nome" class="form-label">Fiscal do Contrato</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="fiscal_contrato_nome" 
                                   name="fiscal_contrato_nome" 
                                   maxlength="255"
                                   placeholder="Nome completo">
                        </div>

                        <div class="col-md-6">
                            <label for="fiscal_contrato_email" class="form-label">E-mail do Fiscal</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="fiscal_contrato_email" 
                                   name="fiscal_contrato_email" 
                                   maxlength="255"
                                   placeholder="email@exemplo.com">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Observações -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-comment-alt text-primary me-2"></i>Observações e Cláusulas
                    </h4>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="clausulas_importantes" class="form-label">Cláusulas Importantes</label>
                            <textarea class="form-control" 
                                      id="clausulas_importantes" 
                                      name="clausulas_importantes" 
                                      rows="4"
                                      placeholder="Liste as cláusulas mais importantes do contrato..."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="observacoes" class="form-label">Observações Gerais</label>
                            <textarea class="form-control" 
                                      id="observacoes" 
                                      name="observacoes" 
                                      rows="3"
                                      placeholder="Informações complementares sobre o contrato..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                    <a href="?page=contratos" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Atualizar Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Validation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formContrato');
    
    // Bootstrap validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);

    // Money mask initialization
    $('.mask-money').inputmask('currency', {
        prefix: '',
        radixPoint: ',',
        groupSeparator: '.',
        digits: 2,
        digitsOptional: false,
        placeholder: '0',
        autoGroup: true,
        rightAlign: false
    });

    // Calculate prazo_meses automatically
    const dataInicio = document.getElementById('data_inicio');
    const dataTermino = document.getElementById('data_termino');
    const prazoMeses = document.getElementById('prazo_meses');

    function calcularPrazo() {
        if (dataInicio.value && dataTermino.value) {
            const inicio = new Date(dataInicio.value);
            const termino = new Date(dataTermino.value);
            
            if (termino < inicio) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Inválida',
                    text: 'A data de término não pode ser anterior à data de início.'
                });
                dataTermino.value = '';
                return;
            }
            
            const meses = Math.round((termino - inicio) / (1000 * 60 * 60 * 24 * 30));
            prazoMeses.value = meses;
        }
    }

    dataInicio.addEventListener('change', calcularPrazo);
    dataTermino.addEventListener('change', calcularPrazo);

    // Validate data_assinatura <= data_inicio
    document.getElementById('data_assinatura').addEventListener('change', function() {
        if (dataInicio.value && this.value > dataInicio.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Inválida',
                text: 'A data de assinatura não pode ser posterior à data de início.'
            });
            this.value = '';
        }
    });
});
</script>

<!-- Footer Instructions -->
<div class="container-fluid mt-5 mb-3">
    <div class="alert alert-light border">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Instruções de Preenchimento</h6>
        <ul class="mb-0 small">
            <li><strong>Campos Obrigatórios:</strong> Número do contrato, empresa tomadora, objeto, datas e valor total</li>
            <li><strong>Datas:</strong> O sistema calculará automaticamente o prazo em meses entre início e término</li>
            <li><strong>Arquivo:</strong> Faça upload do contrato em PDF (máx. 15MB)</li>
            <li><strong>Valores:</strong> O valor total será usado para cálculos de faturamento</li>
            <li><strong>Gestores:</strong> Informe os responsáveis pelo gerenciamento e fiscalização</li>
            <li><strong>Após cadastrar:</strong> Você poderá adicionar serviços e aditivos na tela de visualização</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
