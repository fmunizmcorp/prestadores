<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-plus-circle"></i> Editar Projeto</h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Mensagens de erro -->
    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['erro'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/?page=projetos&action=update">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Informações Básicas -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-info-circle"></i> Informações Básicas
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Código do Projeto *</label>
                            <input type="text" name="codigo" class="form-control" required 
                                   value="<?= htmlspecialchars($_SESSION['old']['codigo'] ?? '') ?>"
                                   placeholder="Ex: PROJ-2024-001">
                            <small class="text-muted">Código único identificador do projeto</small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nome do Projeto *</label>
                            <input type="text" name="nome" class="form-control" required 
                                   value="<?= htmlspecialchars($_SESSION['old']['nome'] ?? '') ?>"
                                   placeholder="Nome completo do projeto">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3" 
                                      placeholder="Descrição detalhada do projeto"><?= htmlspecialchars($_SESSION['old']['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Selecione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($_SESSION['old']['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Empresa Tomadora *</label>
                            <select name="empresa_tomadora_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tomadoras as $tom): ?>
                                <option value="<?= $tom['id'] ?>" <?= ($_SESSION['old']['empresa_tomadora_id'] ?? '') == $tom['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tom['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Contrato Vinculado</label>
                            <select name="contrato_id" class="form-select">
                                <option value="">Sem contrato</option>
                                <?php foreach ($contratos as $cont): ?>
                                <option value="<?= $cont['id'] ?>" <?= ($_SESSION['old']['contrato_id'] ?? '') == $cont['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cont['numero']) ?> - <?= htmlspecialchars($cont['empresa_nome'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gestão e Prazos -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-users-cog"></i> Gestão e Prazos
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Gerente do Projeto *</label>
                            <select name="gerente_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($gerentes as $ger): ?>
                                <option value="<?= $ger['id'] ?>" <?= ($_SESSION['old']['gerente_id'] ?? '') == $ger['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ger['nome']) ?> (<?= ucfirst($ger['perfil']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Prioridade</label>
                            <select name="prioridade" class="form-select">
                                <option value="baixa" <?= ($_SESSION['old']['prioridade'] ?? '') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                                <option value="media" <?= ($_SESSION['old']['prioridade'] ?? 'media') === 'media' ? 'selected' : '' ?>>Média</option>
                                <option value="alta" <?= ($_SESSION['old']['prioridade'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                                <option value="urgente" <?= ($_SESSION['old']['prioridade'] ?? '') === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Visibilidade</label>
                            <select name="visibilidade" class="form-select">
                                <option value="privado" <?= ($_SESSION['old']['visibilidade'] ?? 'privado') === 'privado' ? 'selected' : '' ?>>Privado</option>
                                <option value="equipe" <?= ($_SESSION['old']['visibilidade'] ?? '') === 'equipe' ? 'selected' : '' ?>>Equipe</option>
                                <option value="empresa" <?= ($_SESSION['old']['visibilidade'] ?? '') === 'empresa' ? 'selected' : '' ?>>Empresa</option>
                                <option value="publico" <?= ($_SESSION['old']['visibilidade'] ?? '') === 'publico' ? 'selected' : '' ?>>Público</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Data de Início</label>
                            <input type="date" name="data_inicio" class="form-control" 
                                   value="<?= htmlspecialchars($_SESSION['old']['data_inicio'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Data de Término</label>
                            <input type="date" name="data_fim" class="form-control" 
                                   value="<?= htmlspecialchars($_SESSION['old']['data_fim'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Duração Estimada (dias)</label>
                            <input type="number" name="duracao_estimada" class="form-control" min="1" 
                                   value="<?= htmlspecialchars($_SESSION['old']['duracao_estimada'] ?? '') ?>"
                                   placeholder="Ex: 90">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Horas Previstas</label>
                            <input type="number" name="horas_previstas" class="form-control" min="0" step="0.5" 
                                   value="<?= htmlspecialchars($_SESSION['old']['horas_previstas'] ?? '') ?>"
                                   placeholder="Ex: 720">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orçamento -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-dollar-sign"></i> Orçamento
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Orçamento Previsto (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="orcamento_previsto" class="form-control" min="0" step="0.01" 
                                       value="<?= htmlspecialchars($_SESSION['old']['orcamento_previsto'] ?? '0') ?>"
                                       placeholder="0,00">
                            </div>
                            <small class="text-muted">Orçamento total previsto para o projeto</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalhamento -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-list-ul"></i> Detalhamento do Projeto
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Objetivos</label>
                            <textarea name="objetivos" class="form-control" rows="3" 
                                      placeholder="Descreva os objetivos do projeto"><?= htmlspecialchars($_SESSION['old']['objetivos'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Escopo</label>
                            <textarea name="escopo" class="form-control" rows="3" 
                                      placeholder="Defina o escopo do projeto"><?= htmlspecialchars($_SESSION['old']['escopo'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Critérios de Aceitação</label>
                            <textarea name="criterios_aceitacao" class="form-control" rows="3" 
                                      placeholder="Liste os critérios de aceitação do projeto"><?= htmlspecialchars($_SESSION['old']['criterios_aceitacao'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="2" 
                                      placeholder="Observações gerais sobre o projeto"><?= htmlspecialchars($_SESSION['old']['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="<?= BASE_URL ?>/?page=projetos" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Inicializar Select2 para melhor UX
    $('.form-select').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Calcular duração automaticamente
    $('input[name="data_inicio"], input[name="data_fim"]').on('change', function() {
        let dataInicio = $('input[name="data_inicio"]').val();
        let dataFim = $('input[name="data_fim"]').val();
        
        if (dataInicio && dataFim) {
            let inicio = new Date(dataInicio);
            let fim = new Date(dataFim);
            let diff = Math.ceil((fim - inicio) / (1000 * 60 * 60 * 24));
            
            if (diff > 0) {
                $('input[name="duracao_estimada"]').val(diff);
            }
        }
    });
});
</script>

<?php 
unset($_SESSION['old']); 
require_once ROOT_PATH . '/src/Views/layout/footer.php'; 
?>
