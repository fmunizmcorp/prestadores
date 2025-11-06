<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>
<div class="container-fluid">
    <h1>Execução - <?= htmlspecialchars($projeto['nome']) ?></h1>
    <a href="<?= BASE_URL />/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary mb-3">Voltar</a>
    <div class="card mb-3">
        <div class="card-body">
            <strong>Total de Horas:</strong> <?= $totais['total_horas'] ?? 0 ?>h | 
            <strong>Aprovadas:</strong> <?= $totais['horas_aprovadas'] ?? 0 ?>h | 
            <strong>Pendentes:</strong> <?= $totais['horas_pendentes'] ?? 0 ?>h
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Data</th><th>Usuário</th><th>Horas</th><th>Atividade</th><th>Aprovado</th></tr></thead>
                <tbody>
                    <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                        <td><?= htmlspecialchars($r['usuario_nome']) ?></td>
                        <td><?= $r['horas'] ?>h</td>
                        <td><?= htmlspecialchars($r['descricao_atividade']) ?></td>
                        <td><?= $r['aprovado'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-warning">Não</span>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
