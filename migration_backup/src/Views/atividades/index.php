<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-tasks"></i> <?= $titulo ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Dashboard</a></li>
                    <li class="breadcrumb-item active">Atividades</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <?php if (in_array($_SESSION['perfil'], ['master', 'admin', 'gestor'])): ?>
            <a href="<?= BASE_URL ?>/?page=atividades&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Atividade
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Total de Atividades</h6>
                    <h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Pendentes</h6>
                    <h3 class="mb-0"><?= $stats['pendente'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Em Andamento</h6>
                    <h3 class="mb-0"><?= $stats['em_andamento'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Concluídas</h6>
                    <h3 class="mb-0"><?= $stats['concluida'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="atividades">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Projeto</label>
                        <select name="projeto_id" class="form-select">
                            <option value="">Todos os projetos</option>
                            <?php foreach ($projetos as $projeto): ?>
                            <option value="<?= $projeto['id'] ?>" <?= ($filtros['projeto_id'] ?? '') == $projeto['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($projeto['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendente" <?= ($filtros['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="em_andamento" <?= ($filtros['status'] ?? '') == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="concluida" <?= ($filtros['status'] ?? '') == 'concluida' ? 'selected' : '' ?>>Concluída</option>
                            <option value="cancelada" <?= ($filtros['status'] ?? '') == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Responsável</label>
                        <select name="responsavel_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id'] ?>" <?= ($filtros['responsavel_id'] ?? '') == $usuario['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="busca" class="form-control" placeholder="Título ou descrição" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Atividades -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($atividades)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhuma atividade encontrada.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Projeto</th>
                            <th>Responsável</th>
                            <th>Status</th>
                            <th>Prazo</th>
                            <th>Progresso</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($atividades as $atividade): ?>
                        <tr>
                            <td><?= $atividade['id'] ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/?page=atividades&action=show&id=<?= $atividade['id'] ?>">
                                    <?= htmlspecialchars($atividade['titulo']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($atividade['projeto_nome'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($atividade['responsavel_nome'] ?? '-') ?></td>
                            <td>
                                <?php
                                $statusClass = [
                                    'pendente' => 'warning',
                                    'em_andamento' => 'primary',
                                    'concluida' => 'success',
                                    'cancelada' => 'danger'
                                ][$atividade['status']] ?? 'secondary';
                                
                                $statusLabel = [
                                    'pendente' => 'Pendente',
                                    'em_andamento' => 'Em Andamento',
                                    'concluida' => 'Concluída',
                                    'cancelada' => 'Cancelada'
                                ][$atividade['status']] ?? $atividade['status'];
                                ?>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($atividade['data_prazo']): ?>
                                    <?= date('d/m/Y', strtotime($atividade['data_prazo'])) ?>
                                    <?php
                                    $hoje = new DateTime();
                                    $prazo = new DateTime($atividade['data_prazo']);
                                    $diff = $hoje->diff($prazo);
                                    if ($prazo < $hoje && $atividade['status'] != 'concluida'):
                                    ?>
                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Atrasada</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?= $atividade['progresso'] ?? 0 ?>%;" 
                                         aria-valuenow="<?= $atividade['progresso'] ?? 0 ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $atividade['progresso'] ?? 0 ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/?page=atividades&action=show&id=<?= $atividade['id'] ?>" 
                                       class="btn btn-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_array($_SESSION['perfil'], ['master', 'admin', 'gestor'])): ?>
                                    <a href="<?= BASE_URL ?>/?page=atividades&action=edit&id=<?= $atividade['id'] ?>" 
                                       class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/?page=atividades&action=delete&id=<?= $atividade['id'] ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir esta atividade?')" 
                                       title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Navegação de páginas">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=atividades&p=<?= $i ?><?= !empty($filtros['projeto_id']) ? '&projeto_id='.$filtros['projeto_id'] : '' ?><?= !empty($filtros['status']) ? '&status='.$filtros['status'] : '' ?><?= !empty($filtros['responsavel_id']) ? '&responsavel_id='.$filtros['responsavel_id'] : '' ?><?= !empty($filtros['busca']) ? '&busca='.$filtros['busca'] : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <div class="mt-3">
                <small class="text-muted">
                    Mostrando <?= count($atividades) ?> de <?= $total ?> atividade(s)
                </small>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
