<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-dollar-sign"></i> Financeiro</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Dashboard</a></li>
                    <li class="breadcrumb-item active">Financeiro</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Módulo Financeiro em desenvolvimento.
            </div>
            
            <p>Funcionalidades:</p>
            <ul>
                <li>Fluxo de caixa</li>
                <li>Contas a pagar e receber</li>
                <li>Lançamentos financeiros</li>
                <li>Boletos</li>
                <li>Conciliação bancária</li>
            </ul>
            
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
