<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-project-diagram"></i> Projetos</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Dashboard</a></li>
                    <li class="breadcrumb-item active">Projetos</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/projetos/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Projeto
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Módulo de Projetos em desenvolvimento.
                <br>Esta é uma versão simplificada temporária.
            </div>
            
            <p>Funcionalidades disponíveis:</p>
            <ul>
                <li>Gestão de projetos</li>
                <li>Controle de etapas</li>
                <li>Acompanhamento de progresso</li>
                <li>Gestão de equipe</li>
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
