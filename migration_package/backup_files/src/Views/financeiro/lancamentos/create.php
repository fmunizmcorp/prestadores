<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-plus-circle"></i> Novo Lançamento Manual</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=lancamentos">Lançamentos</a></li>
                    <li class="breadcrumb-item active">Novo Lançamento</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Sistema de Partidas Dobradas:</strong> Todo lançamento possui uma conta de débito e uma conta de crédito com valores iguais.
    </div>

    <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=lancamentos&do=store" id="formLancamento">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Informações Principais -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-info-circle"></i> Informações do Lançamento
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="data_lancamento" class="form-label">Data do Lançamento *</label>
                                <input type="date" class="form-control" id="data_lancamento" name="data_lancamento" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="col-md-8">
                                <label for="numero_documento" class="form-label">Nº Documento</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento"
                                       placeholder="Ex: NF-001234, Recibo-456">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição *</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" required
                                   maxlength="255" placeholder="Descrição breve do lançamento">
                        </div>

                        <div class="mb-3">
                            <label for="historico" class="form-label">Histórico Detalhado</label>
                            <textarea class="form-control" id="historico" name="historico" rows="3"
                                      placeholder="Detalhamento completo da operação..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Partidas Dobradas -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-balance-scale"></i> Débito e Crédito (Partidas Dobradas)
                    </div>
                    <div class="card-body">
                        <!-- Débito -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-success">
                                    <i class="fas fa-arrow-up"></i> DÉBITO (Entrada/Aplicação de Recurso)
                                </h5>
                                <hr>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categoria_debito_id" class="form-label">Categoria de Débito *</label>
                                <select class="form-select select2" id="categoria_debito_id" name="categoria_debito_id" required>
                                    <option value="">Selecione a categoria...</option>
                                    <?php if (!empty($categorias)): ?>
                                        <?php foreach ($categorias as $cat): ?>
                                            <?php if ($cat['aceita_lancamento']): ?>
                                            <option value="<?= $cat['id'] ?>" 
                                                    data-tipo="<?= $cat['tipo'] ?>"
                                                    data-codigo="<?= htmlspecialchars($cat['codigo']) ?>">
                                                <?= str_repeat('&nbsp;&nbsp;&nbsp;', $cat['nivel'] - 1) ?>
                                                <?= htmlspecialchars($cat['codigo']) ?> - <?= htmlspecialchars($cat['nome']) ?>
                                                <span class="badge bg-<?= $cat['tipo'] === 'receita' ? 'success' : ($cat['tipo'] === 'despesa' ? 'danger' : 'info') ?>">
                                                    <?= ucfirst($cat['tipo']) ?>
                                                </span>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    Onde o valor está entrando/sendo aplicado
                                </small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="valor" class="form-label">Valor *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" class="form-control form-control-lg" 
                                           id="valor" name="valor" required min="0.01"
                                           placeholder="0,00" style="font-size: 1.5rem; font-weight: bold;">
                                </div>
                            </div>
                        </div>

                        <!-- Crédito -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5 class="text-danger">
                                    <i class="fas fa-arrow-down"></i> CRÉDITO (Saída/Origem do Recurso)
                                </h5>
                                <hr>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categoria_credito_id" class="form-label">Categoria de Crédito *</label>
                                <select class="form-select select2" id="categoria_credito_id" name="categoria_credito_id" required>
                                    <option value="">Selecione a categoria...</option>
                                    <?php if (!empty($categorias)): ?>
                                        <?php foreach ($categorias as $cat): ?>
                                            <?php if ($cat['aceita_lancamento']): ?>
                                            <option value="<?= $cat['id'] ?>" 
                                                    data-tipo="<?= $cat['tipo'] ?>"
                                                    data-codigo="<?= htmlspecialchars($cat['codigo']) ?>">
                                                <?= str_repeat('&nbsp;&nbsp;&nbsp;', $cat['nivel'] - 1) ?>
                                                <?= htmlspecialchars($cat['codigo']) ?> - <?= htmlspecialchars($cat['nome']) ?>
                                                <span class="badge bg-<?= $cat['tipo'] === 'receita' ? 'success' : ($cat['tipo'] === 'despesa' ? 'danger' : 'info') ?>">
                                                    <?= ucfirst($cat['tipo']) ?>
                                                </span>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    De onde o valor está saindo/vindo
                                </small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Valor (automático)</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control form-control-lg" 
                                           id="valor_credito_display" readonly
                                           value="0,00" style="font-size: 1.5rem; font-weight: bold; background-color: #e9ecef;">
                                </div>
                                <small class="form-text text-muted">
                                    Mesmo valor do débito (partidas dobradas)
                                </small>
                            </div>
                        </div>

                        <!-- Validação visual -->
                        <div class="alert alert-secondary" id="alertValidacao">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <strong>Débito:</strong><br>
                                    <span class="fs-4 text-success" id="displayDebito">R$ 0,00</span>
                                </div>
                                <div class="col-md-4 text-center">
                                    <strong>=</strong><br>
                                    <i class="fas fa-equals fs-4"></i>
                                </div>
                                <div class="col-md-4 text-center">
                                    <strong>Crédito:</strong><br>
                                    <span class="fs-4 text-danger" id="displayCredito">R$ 0,00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-info"></i> Informações Adicionais (Opcional)
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="centro_custo_id" class="form-label">Centro de Custo</label>
                                <select class="form-select" id="centro_custo_id" name="centro_custo_id">
                                    <option value="">Nenhum</option>
                                    <?php if (!empty($centrosCusto)): ?>
                                        <?php foreach ($centrosCusto as $cc): ?>
                                        <option value="<?= $cc['id'] ?>">
                                            <?= htmlspecialchars($cc['codigo']) ?> - <?= htmlspecialchars($cc['nome']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="projeto_id" class="form-label">Projeto</label>
                                <select class="form-select" id="projeto_id" name="projeto_id">
                                    <option value="">Nenhum</option>
                                    <?php if (!empty($projetos)): ?>
                                        <?php foreach ($projetos as $proj): ?>
                                        <option value="<?= $proj['id'] ?>">
                                            <?= htmlspecialchars($proj['codigo']) ?> - <?= htmlspecialchars($proj['nome']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral -->
            <div class="col-lg-4">
                <!-- Ajuda -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-question-circle"></i> Como Funciona?
                    </div>
                    <div class="card-body">
                        <h6>Partidas Dobradas</h6>
                        <p class="small">
                            Cada lançamento tem duas partes com valores iguais:
                        </p>
                        <ul class="small">
                            <li><strong class="text-success">Débito:</strong> Onde o dinheiro entra/é aplicado</li>
                            <li><strong class="text-danger">Crédito:</strong> De onde o dinheiro sai/vem</li>
                        </ul>

                        <hr>

                        <h6>Exemplos Práticos:</h6>
                        
                        <div class="mb-3">
                            <strong>Venda de Serviço:</strong>
                            <ul class="small mb-0">
                                <li><span class="text-success">Débito:</span> Banco</li>
                                <li><span class="text-danger">Crédito:</span> Receita de Serviços</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <strong>Pagamento de Fornecedor:</strong>
                            <ul class="small mb-0">
                                <li><span class="text-success">Débito:</span> Despesa com Materiais</li>
                                <li><span class="text-danger">Crédito:</span> Banco</li>
                            </ul>
                        </div>

                        <div class="mb-0">
                            <strong>Transferência entre Contas:</strong>
                            <ul class="small mb-0">
                                <li><span class="text-success">Débito:</span> Banco Destino</li>
                                <li><span class="text-danger">Crédito:</span> Banco Origem</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Modelos Rápidos -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-bolt"></i> Modelos Rápidos
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="aplicarModelo('venda')">
                                Venda (Banco ← Receita)
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="aplicarModelo('pagamento')">
                                Pagamento (Despesa ← Banco)
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="aplicarModelo('transferencia')">
                                Transferência entre Contas
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save"></i> Salvar Lançamento
                        </button>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=lancamentos" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Atualizar displays de valor em tempo real
document.getElementById('valor').addEventListener('input', function() {
    const valor = parseFloat(this.value) || 0;
    const valorFormatado = valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    document.getElementById('valor_credito_display').value = valorFormatado;
    document.getElementById('displayDebito').textContent = 'R$ ' + valorFormatado;
    document.getElementById('displayCredito').textContent = 'R$ ' + valorFormatado;
    
    // Mudar cor do alerta conforme validação
    const alert = document.getElementById('alertValidacao');
    if (valor > 0) {
        alert.classList.remove('alert-secondary');
        alert.classList.add('alert-success');
    } else {
        alert.classList.remove('alert-success');
        alert.classList.add('alert-secondary');
    }
});

// Validação do formulário
document.getElementById('formLancamento').addEventListener('submit', function(e) {
    const categoriaDebito = document.getElementById('categoria_debito_id').value;
    const categoriaCredito = document.getElementById('categoria_credito_id').value;
    const valor = parseFloat(document.getElementById('valor').value) || 0;
    
    if (!categoriaDebito || !categoriaCredito) {
        e.preventDefault();
        alert('Por favor, selecione as categorias de débito e crédito.');
        return false;
    }
    
    if (categoriaDebito === categoriaCredito) {
        e.preventDefault();
        alert('As categorias de débito e crédito não podem ser iguais!');
        return false;
    }
    
    if (valor <= 0) {
        e.preventDefault();
        alert('O valor deve ser maior que zero!');
        return false;
    }
    
    return true;
});

// Aplicar modelos rápidos (exemplo - ajustar conforme suas categorias)
function aplicarModelo(tipo) {
    // Esta função seria customizada com os IDs reais das categorias do seu sistema
    alert('Função de modelo rápido: ' + tipo + '\nPersonalize esta função com as categorias corretas do seu sistema.');
}

// Initialize Select2 (se disponível)
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
});
</script>

<style>
.form-control-lg {
    border: 2px solid #dee2e6;
}

#alertValidacao {
    border: 2px solid #dee2e6;
    margin-top: 20px;
}

#alertValidacao.alert-success {
    border-color: #28a745;
}

.select2-container {
    z-index: 1050;
}
</style>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
