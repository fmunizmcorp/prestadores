<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="container-fluid py-4">
    <h1 class="h2 mb-4">Novo Custo</h1>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="?page=custos&action=store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select" name="tipo" required>
                            <?php foreach ($tipos ?? [] as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valor <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="valor" step="0.01" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="descricao" rows="3" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" name="data_custo" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fornecedor</label>
                        <input type="text" class="form-control" name="fornecedor">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nº Documento</label>
                        <input type="text" class="form-control" name="numero_documento">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="?page=custos" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
