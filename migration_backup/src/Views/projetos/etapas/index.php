<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-tasks"></i> Cronograma - <?= htmlspecialchars($projeto['nome']) ?></h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projeto-etapas&action=gantt&projeto_id=<?= $projeto['id'] ?>" class="btn btn-info">
                <i class="fas fa-chart-gantt"></i> Gantt
            </a>
            <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if (!empty($atrasadas)): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> <strong><?= count($atrasadas) ?> etapa(s) atrasada(s)</strong>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Etapa</th>
                        <th>Responsável</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Progresso</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etapas as $e): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($e['nome']) ?></strong></td>
                        <td><?= htmlspecialchars($e['responsavel_nome'] ?? 'Não atribuído') ?></td>
                        <td><?= date('d/m/Y', strtotime($e['data_inicio'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($e['data_fim'])) ?></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $e['percentual_concluido'] ?? 0 ?>%"><?= $e['percentual_concluido'] ?? 0 ?>%</div>
                            </div>
                        </td>
                        <td><span class="badge bg-<?= $e['status'] === 'concluida' ? 'success' : 'info' ?>"><?= ucfirst($e['status']) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-success" title="Aprovar"><i class="fas fa-check"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
