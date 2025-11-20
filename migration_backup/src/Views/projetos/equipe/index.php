<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-users"></i> Equipe - <?= htmlspecialchars($projeto['nome']) ?></h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['total_membros'] ?? 0 ?></h3>
                    <p class="mb-0">Total de Membros</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['total_horas_previstas'] ?? 0 ?>h</h3>
                    <p class="mb-0">Horas Previstas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['total_horas_executadas'] ?? 0 ?>h</h3>
                    <p class="mb-0">Horas Executadas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3>R$ <?= number_format($stats['custo_total_previsto'] ?? 0, 0, ',', '.') ?></h3>
                    <p class="mb-0">Custo Total</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Papel</th>
                        <th>Horas Previstas</th>
                        <th>Horas Executadas</th>
                        <th>Custo/Hora</th>
                        <th>Disponível</th>
                        <th>Avaliação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($membros as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['usuario_nome']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($papeis[$m['papel']] ?? $m['papel']) ?></span></td>
                        <td><?= $m['horas_previstas'] ?? 0 ?>h</td>
                        <td><?= $m['horas_executadas'] ?? 0 ?>h</td>
                        <td>R$ <?= number_format($m['custo_hora'] ?? 0, 2, ',', '.') ?></td>
                        <td>
                            <?php if ($m['disponivel']): ?>
                                <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Não</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($m['avaliacao_desempenho']): ?>
                                <?php for ($i = 0; $i < $m['avaliacao_desempenho']; $i++): ?>
                                    <i class="fas fa-star text-warning"></i>
                                <?php endfor; ?>
                            <?php else: ?>
                                <span class="text-muted">Sem avaliação</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
