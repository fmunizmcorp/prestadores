<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-project-diagram"></i> Projetos</h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Projeto
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                    <p>Total de Projetos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3><?= $stats['planejamento'] ?? 0 ?></h3>
                    <p>Em Planejamento</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3><?= $stats['execucao'] ?? 0 ?></h3>
                    <p>Em Execução</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h3><?= $stats['atrasados'] ?? 0 ?></h3>
                    <p>Atrasados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="projetosTable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Empresa</th>
                        <th>Status</th>
                        <th>Progresso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projetos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['codigo']) ?></td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td><?= htmlspecialchars($p['empresa_nome'] ?? 'N/A') ?></td>
                        <td><span class="badge bg-<?= $p['status'] === 'execucao' ? 'success' : 'info' ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $p['percentual_concluido'] ?? 0 ?>%"><?= $p['percentual_concluido'] ?? 0 ?>%</div>
                            </div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Ver</a>
                            <a href="<?= BASE_URL ?>/?page=projetos&action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#projetosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
