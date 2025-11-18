<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-plus-circle"></i> Nova Categoria Financeira</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=categorias">Categorias</a></li>
                    <li class="breadcrumb-item active">Nova Categoria</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($pai)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Criando subcategoria de: <strong><?= htmlspecialchars($pai['codigo']) ?> - <?= htmlspecialchars($pai['nome']) ?></strong>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=categorias&do=store" id="formCategoria">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <?php if (!empty($pai)): ?>
        <input type="hidden" name="pai_id" value="<?= $pai['id'] ?>">
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Informações Básicas -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-info-circle"></i> Informações Básicas
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="codigo" class="form-label">Código *</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required 
                                       maxlength="20" pattern="[A-Z0-9_]+"
                                       placeholder="Ex: VENDA_SERV"
                                       title="Apenas letras maiúsculas, números e underscore">
                                <small class="form-text text-muted">
                                    Apenas letras maiúsculas, números e underscore (_)
                                </small>
                            </div>
                            
                            <div class="col-md-8">
                                <label for="nome" class="form-label">Nome da Categoria *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required 
                                       maxlength="100" placeholder="Ex: Venda de Serviços">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                      placeholder="Descrição detalhada da categoria..."></textarea>
                        </div>

                        <?php if (empty($pai)): ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="pai_id" class="form-label">Categoria Pai (Opcional)</label>
                                <select class="form-select" id="pai_id" name="pai_id">
                                    <option value="">Categoria Raiz (Nível 1)</option>
                                    <?php if (!empty($categoriasPossiveis)): ?>
                                        <?php foreach ($categoriasPossiveis as $cat): ?>
                                        <option value="<?= $cat['id'] ?>">
                                            <?= str_repeat('&nbsp;&nbsp;&nbsp;', $cat['nivel'] - 1) ?>
                                            <?= htmlspecialchars($cat['codigo']) ?> - <?= htmlspecialchars($cat['nome']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    Deixe vazio para criar categoria de nível superior
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Classificação -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-tags"></i> Classificação
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <option value="receita" <?= (!empty($pai) && $pai['tipo'] === 'receita') ? 'selected' : '' ?>>
                                        <span class="text-success">▲</span> Receita
                                    </option>
                                    <option value="despesa" <?= (!empty($pai) && $pai['tipo'] === 'despesa') ? 'selected' : '' ?>>
                                        <span class="text-danger">▼</span> Despesa
                                    </option>
                                    <option value="transferencia">
                                        <span class="text-info">⇄</span> Transferência
                                    </option>
                                </select>
                                <?php if (!empty($pai)): ?>
                                <small class="form-text text-muted">
                                    Tipo herdado da categoria pai
                                </small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="natureza" class="form-label">Natureza *</label>
                                <select class="form-select" id="natureza" name="natureza" required>
                                    <option value="operacional" selected>Operacional</option>
                                    <option value="financeira">Financeira</option>
                                    <option value="investimento">Investimento</option>
                                    <option value="outra">Outra</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="centro_custo_id" class="form-label">Centro de Custo (Opcional)</label>
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
                            <small class="form-text text-muted">
                                Centro de custo padrão para lançamentos desta categoria
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral -->
            <div class="col-lg-4">
                <!-- Configurações -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-cog"></i> Configurações
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="aceita_lancamento" 
                                   name="aceita_lancamento" value="1" checked>
                            <label class="form-check-label" for="aceita_lancamento">
                                <strong>Aceita Lançamentos</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                Se desmarcado, esta categoria será apenas agrupadora (para organizar subcategorias)
                            </small>
                        </div>

                        <div class="alert alert-info mb-0">
                            <small>
                                <strong><i class="fas fa-lightbulb"></i> Dica:</strong><br>
                                Categorias agrupadora são úteis para organizar hierarquicamente suas categorias.
                                Por exemplo: "PESSOAL" pode agrupar "SALARIOS", "BENEFICIOS", etc.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Ajuda -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-question-circle"></i> Ajuda
                    </div>
                    <div class="card-body">
                        <h6>Estrutura Hierárquica</h6>
                        <p class="small">
                            As categorias podem ter múltiplos níveis. Exemplo:
                        </p>
                        <ul class="small">
                            <li><strong>VENDAS</strong> (Nível 1)
                                <ul>
                                    <li><strong>VENDA_PROD</strong> - Venda de Produtos</li>
                                    <li><strong>VENDA_SERV</strong> - Venda de Serviços
                                        <ul>
                                            <li><strong>CONSULT</strong> - Consultoria</li>
                                            <li><strong>MANUT</strong> - Manutenção</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>

                        <hr>

                        <h6>Boas Práticas</h6>
                        <ul class="small">
                            <li>Use códigos curtos e significativos</li>
                            <li>Mantenha hierarquia de até 3-4 níveis</li>
                            <li>Agrupe categorias relacionadas</li>
                            <li>Subcategorias herdam o tipo da categoria pai</li>
                        </ul>
                    </div>
                </div>

                <!-- Ações -->
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save"></i> Salvar Categoria
                        </button>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=categorias" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Validação e máscara do código
document.getElementById('codigo').addEventListener('input', function(e) {
    // Converter para maiúsculas e remover caracteres inválidos
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
});

// Sincronizar tipo com categoria pai
<?php if (!empty($pai)): ?>
document.getElementById('tipo').disabled = true;
<?php endif; ?>

// Validação ao alterar categoria pai
document.getElementById('pai_id')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value) {
        // Herdar tipo da categoria pai
        const tipoSelect = document.getElementById('tipo');
        // Aqui você pode fazer uma requisição AJAX para buscar o tipo da categoria pai
        // Por enquanto, desabilita o campo para evitar inconsistências
        tipoSelect.disabled = true;
    } else {
        document.getElementById('tipo').disabled = false;
    }
});

// Validação do formulário
document.getElementById('formCategoria').addEventListener('submit', function(e) {
    const codigo = document.getElementById('codigo').value;
    const nome = document.getElementById('nome').value;
    const tipo = document.getElementById('tipo').value;
    
    if (!codigo || !nome || !tipo) {
        e.preventDefault();
        alert('Por favor, preencha todos os campos obrigatórios.');
        return false;
    }
    
    if (!/^[A-Z0-9_]+$/.test(codigo)) {
        e.preventDefault();
        alert('O código deve conter apenas letras maiúsculas, números e underscore.');
        return false;
    }
    
    return true;
});
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
