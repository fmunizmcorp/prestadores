<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-clock"></i> Execução - <?= htmlspecialchars($projeto['nome']) ?></h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $totais['total_horas'] ?? 0 ?>h</h3>
                    <p class="mb-0">Total de Horas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $totais['horas_aprovadas'] ?? 0 ?>h</h3>
                    <p class="mb-0">Horas Aprovadas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3><?= $totais['horas_pendentes'] ?? 0 ?>h</h3>
                    <p class="mb-0">Pendentes Aprovação</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Apontamentos de Horas
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Etapa</th>
                        <th>Horas</th>
                        <th>Tipo</th>
                        <th>Atividade</th>
                        <th>Valor</th>
                        <th>Aprovado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                        <td><?= htmlspecialchars($r['usuario_nome']) ?></td>
                        <td><?= htmlspecialchars($r['etapa_nome'] ?? 'N/A') ?></td>
                        <td><strong><?= $r['horas'] ?>h</strong></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($tiposHora[$r['tipo_hora']] ?? $r['tipo_hora']) ?></span></td>
                        <td><?= htmlspecialchars($r['descricao_atividade']) ?></td>
                        <td>R$ <?= number_format($r['valor'], 2, ',', '.') ?></td>
                        <td>
                            <?php if ($r['aprovado']): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Sim</span>
                            <?php else: ?>
                                <span class="badge bg-warning"><i class="fas fa-clock"></i> Pendente</span>
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
