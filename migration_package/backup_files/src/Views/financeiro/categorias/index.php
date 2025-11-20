<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-sitemap"></i> Categorias Financeiras</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item active">Categorias</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=financeiro&action=categorias&do=create" class="btn btn-success">
                <i class="fas fa-plus"></i> Nova Categoria
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filtros
        </div>
        <div class="card-body">
            <form method="GET" id="filtrosForm">
                <input type="hidden" name="page" value="financeiro">
                <input type="hidden" name="action" value="categorias">
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" id="tipo">
                            <option value="">Todos</option>
                            <option value="receita" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'receita') ? 'selected' : '' ?>>Receita</option>
                            <option value="despesa" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'despesa') ? 'selected' : '' ?>>Despesa</option>
                            <option value="transferencia" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'transferencia') ? 'selected' : '' ?>>Transferência</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="natureza" class="form-label">Natureza</label>
                        <select class="form-select" name="natureza" id="natureza">
                            <option value="">Todas</option>
                            <option value="operacional" <?= (isset($_GET['natureza']) && $_GET['natureza'] === 'operacional') ? 'selected' : '' ?>>Operacional</option>
                            <option value="financeira" <?= (isset($_GET['natureza']) && $_GET['natureza'] === 'financeira') ? 'selected' : '' ?>>Financeira</option>
                            <option value="investimento" <?= (isset($_GET['natureza']) && $_GET['natureza'] === 'investimento') ? 'selected' : '' ?>>Investimento</option>
                            <option value="outra" <?= (isset($_GET['natureza']) && $_GET['natureza'] === 'outra') ? 'selected' : '' ?>>Outra</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="busca" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="busca" name="busca" 
                               placeholder="Código, nome ou descrição..."
                               value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="<?= BASE_URL ?>/?page=financeiro&action=categorias" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Receitas</h6>
                            <h3 class="card-title mb-0"><?= $stats['total_receitas'] ?? 0 ?></h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Despesas</h6>
                            <h3 class="card-title mb-0"><?= $stats['total_despesas'] ?? 0 ?></h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Transferências</h6>
                            <h3 class="card-title mb-0"><?= $stats['total_transferencias'] ?? 0 ?></h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Categorias</h6>
                            <h3 class="card-title mb-0"><?= $stats['total_categorias'] ?? 0 ?></h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-sitemap"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Árvore Hierárquica de Categorias -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fas fa-tree"></i> Estrutura Hierárquica</span>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-light btn-sm" onclick="expandirTodos()">
                    <i class="fas fa-plus-square"></i> Expandir Todos
                </button>
                <button type="button" class="btn btn-light btn-sm" onclick="recolherTodos()">
                    <i class="fas fa-minus-square"></i> Recolher Todos
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($arvore)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Nenhuma categoria encontrada. <a href="<?= BASE_URL ?>/?page=financeiro&action=categorias&do=create">Criar primeira categoria</a>
                </div>
            <?php else: ?>
                <div class="tree-view">
                    <?php echo renderizarArvore($arvore); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
/**
 * Função recursiva para renderizar árvore hierárquica
 * 
 * @param array $categorias Array de categorias
 * @param int $nivel Nível atual na hierarquia
 * @return string HTML da árvore
 */
function renderizarArvore($categorias, $nivel = 0) {
    if (empty($categorias)) {
        return '';
    }
    
    $html = '<ul class="tree-list' . ($nivel === 0 ? ' tree-root' : '') . '">';
    
    foreach ($categorias as $cat) {
        // Badge de tipo
        $tipoBadgeClass = match($cat['tipo']) {
            'receita' => 'success',
            'despesa' => 'danger',
            'transferencia' => 'info',
            default => 'secondary'
        };
        
        $tipoBadge = '<span class="badge bg-' . $tipoBadgeClass . '">' . ucfirst($cat['tipo']) . '</span>';
        
        // Badge de lançamento
        $lancamentoBadge = '';
        if (!$cat['aceita_lancamento']) {
            $lancamentoBadge = '<span class="badge bg-warning" title="Categoria agrupadora">Grupo</span>';
        }
        
        // Contador de usos
        $usos = '';
        if ($cat['total_lancamentos'] > 0) {
            $usos = '<span class="badge bg-secondary" title="Lançamentos">' . $cat['total_lancamentos'] . ' lançamentos</span>';
        }
        
        // Tem filhas?
        $hasChildren = !empty($cat['filhas']);
        $toggleIcon = $hasChildren ? '<i class="fas fa-chevron-right tree-toggle"></i>' : '<i class="fas fa-circle tree-dot"></i>';
        
        $html .= '<li class="tree-item' . ($hasChildren ? ' has-children' : '') . '">';
        $html .= '<div class="tree-node" data-id="' . $cat['id'] . '">';
        $html .= '<span class="tree-icon">' . $toggleIcon . '</span>';
        $html .= '<span class="tree-label">';
        $html .= '<strong>' . htmlspecialchars($cat['codigo']) . '</strong> - ';
        $html .= htmlspecialchars($cat['nome']) . ' ';
        $html .= $tipoBadge . ' ';
        $html .= $lancamentoBadge . ' ';
        $html .= $usos;
        $html .= '</span>';
        
        // Ações
        $html .= '<span class="tree-actions">';
        $html .= '<a href="' . BASE_URL . '/?page=financeiro&action=categorias&do=create&pai_id=' . $cat['id'] . '" class="btn btn-sm btn-success" title="Adicionar subcategoria">';
        $html .= '<i class="fas fa-plus"></i>';
        $html .= '</a> ';
        $html .= '<a href="' . BASE_URL . '/?page=financeiro&action=categorias&do=edit&id=' . $cat['id'] . '" class="btn btn-sm btn-primary" title="Editar">';
        $html .= '<i class="fas fa-edit"></i>';
        $html .= '</a> ';
        
        if ($cat['total_lancamentos'] == 0 && empty($cat['filhas'])) {
            $html .= '<button type="button" class="btn btn-sm btn-danger" onclick="confirmarExclusao(' . $cat['id'] . ', \'' . htmlspecialchars($cat['nome'], ENT_QUOTES) . '\')" title="Excluir">';
            $html .= '<i class="fas fa-trash"></i>';
            $html .= '</button>';
        }
        
        $html .= '</span>';
        $html .= '</div>';
        
        // Renderizar filhas recursivamente
        if ($hasChildren) {
            $html .= renderizarArvore($cat['filhas'], $nivel + 1);
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    
    return $html;
}
?>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/?page=financeiro&action=categorias&do=delete">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" id="excluirId">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash"></i> Excluir Categoria</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Esta ação não pode ser desfeita!
                    </div>
                    <p>Tem certeza que deseja excluir a categoria <strong id="excluirNome"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sim, excluir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.tree-view {
    font-size: 14px;
}

.tree-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
}

.tree-list:not(.tree-root) {
    padding-left: 30px;
    display: none; /* Inicialmente recolhido */
}

.tree-item.expanded > .tree-list {
    display: block;
}

.tree-node {
    padding: 8px 12px;
    margin: 2px 0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}

.tree-node:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.tree-icon {
    cursor: pointer;
    width: 20px;
    text-align: center;
    color: #6c757d;
}

.tree-toggle {
    transition: transform 0.2s;
}

.tree-item.expanded > .tree-node .tree-toggle {
    transform: rotate(90deg);
}

.tree-dot {
    font-size: 8px;
}

.tree-label {
    flex-grow: 1;
}

.tree-actions {
    margin-left: auto;
    display: flex;
    gap: 4px;
}

.badge {
    font-size: 10px;
    padding: 3px 6px;
}

/* Cores por nível */
.tree-root > .tree-item > .tree-node {
    background-color: #e3f2fd;
    border-color: #90caf9;
    font-weight: 500;
}

.tree-root > .tree-item > .tree-list > .tree-item > .tree-node {
    background-color: #f1f8e9;
    border-color: #c5e1a5;
}

.tree-root > .tree-item > .tree-list > .tree-item > .tree-list > .tree-item > .tree-node {
    background-color: #fff3e0;
    border-color: #ffcc80;
}
</style>

<script>
// Toggle expandir/recolher categoria
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tree-icon').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.stopPropagation();
            const item = this.closest('.tree-item');
            if (item.classList.contains('has-children')) {
                item.classList.toggle('expanded');
            }
        });
    });
    
    // Expandir primeiro nível por padrão
    document.querySelectorAll('.tree-root > .tree-item.has-children').forEach(item => {
        item.classList.add('expanded');
    });
});

// Expandir todos os níveis
function expandirTodos() {
    document.querySelectorAll('.tree-item.has-children').forEach(item => {
        item.classList.add('expanded');
    });
}

// Recolher todos os níveis
function recolherTodos() {
    document.querySelectorAll('.tree-item.has-children').forEach(item => {
        item.classList.remove('expanded');
    });
}

// Confirmar exclusão
function confirmarExclusao(id, nome) {
    document.getElementById('excluirId').value = id;
    document.getElementById('excluirNome').textContent = nome;
    const modal = new bootstrap.Modal(document.getElementById('modalExcluir'));
    modal.show();
}
</script>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
