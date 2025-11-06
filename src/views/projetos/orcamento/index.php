<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>
<div class="container-fluid">
    <h1>Orçamento - <?= htmlspecialchars($projeto['nome']) ?></h1>
    <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary mb-3">Voltar</a>
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h4>Receitas</h4>
                    <p>R$ <?= number_format($totais['receitas']['valor_previsto'] ?? 0, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h4>Despesas</h4>
                    <p>R$ <?= number_format($totais['despesas']['valor_previsto'] ?? 0, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4>Resultado</h4>
                    <p>R$ <?= number_format($totais['resultado']['valor_previsto'] ?? 0, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Categoria</th><th>Tipo</th><th>Descrição</th><th>Valor</th></tr></thead>
                <tbody>
                    <?php foreach ($itens as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($i['categoria']) ?></td>
                        <td><?= $i['tipo'] ?></td>
                        <td><?= htmlspecialchars($i['descricao']) ?></td>
                        <td>R$ <?= number_format($i['valor_total'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
