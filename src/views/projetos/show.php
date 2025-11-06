<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><?= htmlspecialchars($projeto['codigo']) ?> - <?= htmlspecialchars($projeto['nome']) ?></h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=edit&id=<?= $projeto['id'] ?>" class="btn btn-warning">Editar</a>
            <a href="<?= BASE_URL ?>/?page=projeto-etapas&projeto_id=<?= $projeto['id'] ?>" class="btn btn-info">Cronograma</a>
            <a href="<?= BASE_URL ?>/?page=projeto-equipe&projeto_id=<?= $projeto['id'] ?>" class="btn btn-success">Equipe</a>
            <a href="<?= BASE_URL ?>/?page=projeto-orcamento&projeto_id=<?= $projeto['id'] ?>" class="btn btn-primary">Orçamento</a>
            <a href="<?= BASE_URL ?>/?page=projeto-execucao&projeto_id=<?= $projeto['id'] ?>" class="btn btn-secondary">Execução</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Informações do Projeto</div>
                <div class="card-body">
                    <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>
                    <p><strong>Empresa:</strong> <?= htmlspecialchars($projeto['empresa_nome'] ?? 'N/A') ?></p>
                    <p><strong>Gerente:</strong> <?= htmlspecialchars($projeto['gerente_nome'] ?? 'N/A') ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-info"><?= ucfirst($projeto['status']) ?></span></p>
                    <p><strong>Progresso:</strong> <?= $projeto['percentual_concluido'] ?? 0 ?>%</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="card-body">
                    <p><strong>Orçado:</strong> R$ <?= number_format($dashboard['orcamento_previsto'] ?? 0, 2, ',', '.') ?></p>
                    <p><strong>Realizado:</strong> R$ <?= number_format($dashboard['orcamento_realizado'] ?? 0, 2, ',', '.') ?></p>
                    <p><strong>Horas Previstas:</strong> <?= $dashboard['horas_previstas'] ?? 0 ?>h</p>
                    <p><strong>Horas Executadas:</strong> <?= $dashboard['horas_executadas'] ?? 0 ?>h</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
