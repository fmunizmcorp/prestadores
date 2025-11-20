<!-- Usuários - Listagem -->
<div class="container-fluid">
    <div class="page-header">
        <h1>
            <i class="fas fa-users"></i> Usuários
        </h1>
        <div class="page-actions">
            <a href="?page=usuarios&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Usuário
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-number"><?= $usuarios_total ?? 0 ?></div>
            <div class="stat-label">Total de Usuários</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?= $usuarios_ativos ?? 0 ?></div>
            <div class="stat-label">Usuários Ativos</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="usuarios">
            
            <div class="form-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome ou email" 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            
            <div class="form-group">
                <select name="ativo" class="form-control">
                    <option value="">Todos os status</option>
                    <option value="1" <?= ($ativo ?? '') === '1' ? 'selected' : '' ?>>Ativos</option>
                    <option value="0" <?= ($ativo ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
                </select>
            </div>
            
            <div class="form-group">
                <select name="perfil" class="form-control">
                    <option value="">Todos os perfis</option>
                    <option value="master" <?= ($perfil ?? '') === 'master' ? 'selected' : '' ?>>Master</option>
                    <option value="admin" <?= ($perfil ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="gestor" <?= ($perfil ?? '') === 'gestor' ? 'selected' : '' ?>>Gestor</option>
                    <option value="usuario" <?= ($perfil ?? '') === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            
            <a href="?page=usuarios" class="btn btn-secondary">
                <i class="fas fa-times"></i> Limpar
            </a>
        </form>
    </div>

    <!-- Tabela de Usuários -->
    <div class="table-card">
        <?php if (empty($usuarios)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>Nenhum usuário encontrado</p>
                <a href="?page=usuarios&action=create" class="btn btn-primary">
                    Cadastrar Primeiro Usuário
                </a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= $usuario['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= $usuario['perfil'] ?? $usuario['role'] ?? 'usuario' ?>">
                                    <?= strtoupper($usuario['perfil'] ?? $usuario['role'] ?? 'usuario') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($usuario['ativo']): ?>
                                    <span class="badge badge-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($usuario['created_at'])) ?></td>
                            <td>
                                <a href="?page=usuarios&action=show&id=<?= $usuario['id'] ?>" 
                                   class="btn btn-sm btn-info" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?page=usuarios&action=edit&id=<?= $usuario['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <?php if ($totalPaginas > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=usuarios&page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>&ativo=<?= $ativo ?? '' ?>&perfil=<?= $perfil ?? '' ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <span class="pagination-info">
                        Página <?= $page ?> de <?= $totalPaginas ?>
                    </span>
                    
                    <?php if ($page < $totalPaginas): ?>
                        <a href="?page=usuarios&page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>&ativo=<?= $ativo ?? '' ?>&perfil=<?= $perfil ?? '' ?>" 
                           class="btn btn-secondary">
                            Próxima <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 8px;
}

.stat-label {
    color: #6c757d;
    font-size: 14px;
}

.filters-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.filters-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filters-form .form-group {
    flex: 1;
    min-width: 200px;
}

.table-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.badge-master { background: #dc3545; color: white; }
.badge-admin { background: #ffc107; color: #333; }
.badge-gestor { background: #17a2b8; color: white; }
.badge-usuario { background: #6c757d; color: white; }

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
}
</style>
