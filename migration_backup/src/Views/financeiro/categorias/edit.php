<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-edit"></i> Editar Categoria Financeira</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=categorias">Categorias</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=categorias&do=update" id="formCategoria">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
        
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
                                       value="<?= htmlspecialchars($categoria['codigo']) ?>"
                                       title="Apenas letras maiúsculas, números e underscore">
                                <small class="form-text text-muted">
                                    Apenas letras maiúsculas, números e underscore (_)
                                </small>
                            </div>
                            
                            <div class="col-md-8">
                                <label for="nome" class="form-label">Nome da Categoria *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required 
                                       maxlength="100" value="<?= htmlspecialchars($categoria['nome']) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= htmlspecialchars($categoria['descricao'] ?? '') ?></textarea>
                        </div>

                        <?php if ($categoria['nivel'] > 1): ?>
                        <div class="alert alert-info">
                            <strong>Categoria Pai:</strong> 
                            <?= htmlspecialchars($categoria['pai_nome']) ?>
                            <small class="d-block">Para alterar a hierarquia, exclua e recrie a categoria.</small>
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
                                <select class="form-select" id="tipo" name="tipo" required 
                                        <?= $categoria['nivel'] > 1 ? 'disabled' : '' ?>>
                                    <option value="receita" <?= $categoria['tipo'] === 'receita' ? 'selected' : '' ?>>
                                        Receita
                                    </option>
                                    <option value="despesa" <?= $categoria['tipo'] === 'despesa' ? 'selected' : '' ?>>
                                        Despesa
                                    </option>
                                    <option value="transferencia" <?= $categoria['tipo'] === 'transferencia' ? 'selected' : '' ?>>
                                        Transferência
                                    </option>
                                </select>
                                <?php if ($categoria['nivel'] > 1): ?>
                                <small class="form-text text-muted">
                                    Tipo herdado da categoria pai (não pode ser alterado)
                                </small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="natureza" class="form-label">Natureza *</label>
                                <select class="form-select" id="natureza" name="natureza" required>
                                    <option value="operacional" <?= $categoria['natureza'] === 'operacional' ? 'selected' : '' ?>>Operacional</option>
                                    <option value="financeira" <?= $categoria['natureza'] === 'financeira' ? 'selected' : '' ?>>Financeira</option>
                                    <option value="investimento" <?= $categoria['natureza'] === 'investimento' ? 'selected' : '' ?>>Investimento</option>
                                    <option value="outra" <?= $categoria['natureza'] === 'outra' ? 'selected' : '' ?>>Outra</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="centro_custo_id" class="form-label">Centro de Custo (Opcional)</label>
                            <select class="form-select" id="centro_custo_id" name="centro_custo_id">
                                <option value="">Nenhum</option>
                                <?php if (!empty($centrosCusto)): ?>
                                    <?php foreach ($centrosCusto as $cc): ?>
                                    <option value="<?= $cc['id'] ?>" <?= $categoria['centro_custo_id'] == $cc['id'] ? 'selected' : '' ?>>
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

                <!-- Subcategorias -->
                <?php if (!empty($subcategorias)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-sitemap"></i> Subcategorias (<?= count($subcategorias) ?>)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Lançamentos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subcategorias as $sub): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sub['codigo']) ?></td>
                                        <td><?= htmlspecialchars($sub['nome']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $sub['tipo'] === 'receita' ? 'success' : ($sub['tipo'] === 'despesa' ? 'danger' : 'info') ?>">
                                                <?= ucfirst($sub['tipo']) ?>
                                            </span>
                                        </td>
                                        <td><?= $sub['total_lancamentos'] ?? 0 ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
                                   name="aceita_lancamento" value="1" 
                                   <?= $categoria['aceita_lancamento'] ? 'checked' : '' ?>
                                   <?= !empty($subcategorias) ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="aceita_lancamento">
                                <strong>Aceita Lançamentos</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                <?php if (!empty($subcategorias)): ?>
                                    Esta categoria possui subcategorias e não pode aceitar lançamentos diretamente.
                                <?php else: ?>
                                    Se desmarcado, esta categoria será apenas agrupadora.
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="ativo" 
                                   name="ativo" value="1" <?= $categoria['ativo'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ativo">
                                <strong>Categoria Ativa</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                Categorias inativas não aparecem nas listagens
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-chart-bar"></i> Estatísticas
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Nível:</strong> <?= $categoria['nivel'] ?>
                        </div>
                        <div class="mb-2">
                            <strong>Caminho:</strong><br>
                            <code><?= htmlspecialchars($categoria['caminho']) ?></code>
                        </div>
                        <div class="mb-2">
                            <strong>Total de Lançamentos:</strong><br>
                            <span class="badge bg-primary"><?= $categoria['total_lancamentos'] ?? 0 ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Subcategorias:</strong><br>
                            <span class="badge bg-info"><?= !empty($subcategorias) ? count($subcategorias) : 0 ?></span>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <strong>Criado em:</strong><br>
                            <?= date('d/m/Y H:i', strtotime($categoria['created_at'])) ?>
                        </div>
                        <?php if ($categoria['updated_at'] !== $categoria['created_at']): ?>
                        <div class="small text-muted mt-2">
                            <strong>Última atualização:</strong><br>
                            <?= date('d/m/Y H:i', strtotime($categoria['updated_at'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ações -->
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=categorias&do=create&pai_id=<?= $categoria['id'] ?>" 
                           class="btn btn-success">
                            <i class="fas fa-plus"></i> Adicionar Subcategoria
                        </a>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=categorias" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <?php if ($categoria['total_lancamentos'] == 0 && empty($subcategorias)): ?>
                        <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
                            <i class="fas fa-trash"></i> Excluir Categoria
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Form de Exclusão (hidden) -->
<form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=categorias&do=delete" id="formExcluir" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
</form>

<script>
// Validação e máscara do código
document.getElementById('codigo').addEventListener('input', function(e) {
    // Converter para maiúsculas e remover caracteres inválidos
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
});

// Validação do formulário
document.getElementById('formCategoria').addEventListener('submit', function(e) {
    const codigo = document.getElementById('codigo').value;
    const nome = document.getElementById('nome').value;
    
    if (!codigo || !nome) {
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

// Confirmar exclusão
function confirmarExclusao() {
    if (confirm('Tem certeza que deseja excluir esta categoria?\n\nEsta ação não pode ser desfeita!')) {
        document.getElementById('formExcluir').submit();
    }
}
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
