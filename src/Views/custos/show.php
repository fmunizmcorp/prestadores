<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="container-fluid py-4">
    <h1 class="h2 mb-4">Custo #<?= $custo['id'] ?></h1>
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><strong>Tipo:</strong> <?= ucfirst($custo['tipo']) ?></div>
                <div class="col-md-6"><strong>Status:</strong> <?= ucfirst($custo['status']) ?></div>
                <div class="col-12"><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($custo['descricao'])) ?></div>
                <div class="col-md-6"><strong>Valor:</strong> R$ <?= number_format($custo['valor'], 2, ',', '.') ?></div>
                <div class="col-md-6"><strong>Data:</strong> <?= date('d/m/Y', strtotime($custo['data_custo'])) ?></div>
            </div>
            <div class="mt-4">
                <?php if ($custo['status'] === 'pendente'): ?>
                <form method="POST" action="?page=custos&action=aprovar" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $custo['id'] ?>">
                    <button class="btn btn-success">Aprovar</button>
                </form>
                <?php endif; ?>
                <?php if ($custo['status'] === 'aprovado'): ?>
                <form method="POST" action="?page=custos&action=marcar_pago" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $custo['id'] ?>">
                    <button class="btn btn-primary">Marcar como Pago</button>
                </form>
                <?php endif; ?>
                <a href="?page=custos" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
