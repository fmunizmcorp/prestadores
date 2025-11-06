<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>
<div class="container-fluid">
    <h1>Equipe - <?= htmlspecialchars($projeto['nome']) ?></h1>
    <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary mb-3">Voltar</a>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Nome</th><th>Papel</th><th>Horas Previstas</th><th>Horas Executadas</th><th>Disponível</th></tr></thead>
                <tbody>
                    <?php foreach ($membros as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['usuario_nome']) ?></td>
                        <td><?= htmlspecialchars($m['papel']) ?></td>
                        <td><?= $m['horas_previstas'] ?? 0 ?>h</td>
                        <td><?= $m['horas_executadas'] ?? 0 ?>h</td>
                        <td><?= $m['disponivel'] ? 'Sim' : 'Não' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
