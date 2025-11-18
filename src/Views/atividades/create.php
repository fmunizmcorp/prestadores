<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Nova Atividade' ?> - Sistema Prestadores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar placeholder -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="px-3">Menu</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/?page=dashboard">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/?page=atividades">
                                <i class="bi bi-list-check"></i> Atividades
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-plus-circle"></i> Nova Atividade</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= BASE_URL ?>/?page=atividades" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['erro'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['erro'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['erro']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['sucesso'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['sucesso'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['sucesso']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Formulário de Cadastro</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL ?>/?page=atividades&action=store" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="titulo" class="form-label">Título da Atividade *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="200">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="projeto_id" class="form-label">Projeto *</label>
                                    <select class="form-select" id="projeto_id" name="projeto_id" required>
                                        <option value="">Selecione...</option>
                                        <?php if (isset($projetos) && is_array($projetos)): ?>
                                            <?php foreach ($projetos as $projeto): ?>
                                                <option value="<?= $projeto['id'] ?>">
                                                    <?= htmlspecialchars($projeto['nome'] ?? $projeto['titulo'] ?? 'Projeto #' . $projeto['id']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="descricao" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="4"></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="data_inicio" class="form-label">Data Início *</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="data_fim" class="form-label">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="prioridade" class="form-label">Prioridade *</label>
                                    <select class="form-select" id="prioridade" name="prioridade" required>
                                        <option value="baixa">Baixa</option>
                                        <option value="media" selected>Média</option>
                                        <option value="alta">Alta</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pendente" selected>Pendente</option>
                                        <option value="em_andamento">Em Andamento</option>
                                        <option value="pausada">Pausada</option>
                                        <option value="concluida">Concluída</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="responsavel_id" class="form-label">Responsável</label>
                                    <select class="form-select" id="responsavel_id" name="responsavel_id">
                                        <option value="">Selecione...</option>
                                        <?php if (isset($usuarios) && is_array($usuarios)): ?>
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <option value="<?= $usuario['id'] ?>">
                                                    <?= htmlspecialchars($usuario['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="horas_estimadas" class="form-label">Horas Estimadas</label>
                                    <input type="number" class="form-control" id="horas_estimadas" name="horas_estimadas" step="0.5" min="0">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="progresso" class="form-label">Progresso (%)</label>
                                    <input type="number" class="form-control" id="progresso" name="progresso" min="0" max="100" value="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Salvar Atividade
                                    </button>
                                    <a href="<?= BASE_URL ?>/?page=atividades" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
