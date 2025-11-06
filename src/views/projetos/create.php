<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <h1>Novo Projeto</h1>

    <form method="POST" action="<?= BASE_URL ?>/?page=projetos&action=store">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Código*</label>
                            <input type="text" name="codigo" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Nome*</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Empresa Tomadora*</label>
                            <select name="empresa_tomadora_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tomadoras as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Gerente*</label>
                            <select name="gerente_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($gerentes as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label>Data Início</label>
                            <input type="date" name="data_inicio" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label>Data Fim</label>
                            <input type="date" name="data_fim" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label>Prioridade</label>
                            <select name="prioridade" class="form-select">
                                <option value="baixa">Baixa</option>
                                <option value="media" selected>Média</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Salvar Projeto</button>
                <a href="<?= BASE_URL ?>/?page=projetos" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </form>
</div>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
