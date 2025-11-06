<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-dollar-sign"></i> Orçamento - <?= htmlspecialchars($projeto['nome']) ?></h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=show&id=<?= $projeto['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>Receitas</h4>
                    <h3>R$ <?= number_format($totais['receitas']['valor_previsto'] ?? 0, 2, ',', '.') ?></h3>
                    <small>Previsto</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>Despesas</h4>
                    <h3>R$ <?= number_format($totais['despesas']['valor_previsto'] ?? 0, 2, ',', '.') ?></h3>
                    <small>Previsto</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>Resultado</h4>
                    <h3>R$ <?= number_format($totais['resultado']['valor_previsto'] ?? 0, 2, ',', '.') ?></h3>
                    <small>Previsto</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Itens Orçamentários
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Qtd</th>
                        <th>Valor Unit.</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($categorias[$i['categoria']] ?? $i['categoria']) ?></td>
                        <td><span class="badge bg-<?= $i['tipo'] === 'receita' ? 'success' : 'danger' ?>"><?= ucfirst($i['tipo']) ?></span></td>
                        <td><?= htmlspecialchars($i['descricao']) ?></td>
                        <td><?= $i['quantidade'] ?> <?= $i['unidade'] ?></td>
                        <td>R$ <?= number_format($i['valor_unitario'], 2, ',', '.') ?></td>
                        <td><strong>R$ <?= number_format($i['valor_total'], 2, ',', '.') ?></strong></td>
                        <td><span class="badge bg-info"><?= ucfirst($i['status'] ?? 'pendente') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
