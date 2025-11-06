<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>
<div class="container-fluid">
    <h1>Cronograma - <?= htmlspecialchars($projeto['nome']) ?></h1>
    <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary mb-3">Voltar</a>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Etapa</th><th>Início</th><th>Fim</th><th>Progresso</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($etapas as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nome']) ?></td>
                        <td><?= date('d/m/Y', strtotime($e['data_inicio'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($e['data_fim'])) ?></td>
                        <td><?= $e['percentual_concluido'] ?? 0 ?>%</td>
                        <td><span class="badge bg-info"><?= $e['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
