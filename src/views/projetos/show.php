<?php require_once ROOT_PATH . '/src/views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1>
                <i class="fas fa-project-diagram"></i> 
                <?= htmlspecialchars($projeto['codigo']) ?> - <?= htmlspecialchars($projeto['nome']) ?>
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-building"></i> <?= htmlspecialchars($projeto['empresa_nome'] ?? 'N/A') ?> |
                <i class="fas fa-user-tie"></i> <?= htmlspecialchars($projeto['gerente_nome'] ?? 'N/A') ?>
            </p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/?page=projetos&action=edit&id=<?= $projeto['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="<?= BASE_URL ?>/?page=projeto-etapas&projeto_id=<?= $projeto['id'] ?>" class="btn btn-info">
                <i class="fas fa-tasks"></i> Cronograma
            </a>
            <a href="<?= BASE_URL ?>/?page=projeto-equipe&projeto_id=<?= $projeto['id'] ?>" class="btn btn-success">
                <i class="fas fa-users"></i> Equipe
            </a>
            <a href="<?= BASE_URL ?>/?page=projeto-orcamento&projeto_id=<?= $projeto['id'] ?>" class="btn btn-primary">
                <i class="fas fa-dollar-sign"></i> Orçamento
            </a>
            <a href="<?= BASE_URL ?>/?page=projeto-execucao&projeto_id=<?= $projeto['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-clock"></i> Execução
            </a>
            <a href="<?= BASE_URL ?>/?page=projetos" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['sucesso'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <!-- Status e Progresso -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Status</h6>
                    <?php
                    $badgeClass = 'secondary';
                    switch($projeto['status']) {
                        case 'planejamento': $badgeClass = 'info'; break;
                        case 'execucao': $badgeClass = 'success'; break;
                        case 'pausado': $badgeClass = 'warning'; break;
                        case 'concluido': $badgeClass = 'primary'; break;
                        case 'cancelado': $badgeClass = 'danger'; break;
                    }
                    ?>
                    <h4><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($projeto['status']) ?></span></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Progresso</h6>
                    <h4><?= $projeto['percentual_concluido'] ?? 0 ?>%</h4>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?= $projeto['percentual_concluido'] ?? 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Orçamento</h6>
                    <h4 class="text-primary">R$ <?= number_format($dashboard['orcamento_previsto'] ?? 0, 2, ',', '.') ?></h4>
                    <small class="text-muted">Previsto</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Horas</h6>
                    <h4 class="text-info"><?= $dashboard['horas_executadas'] ?? 0 ?>h / <?= $dashboard['horas_previstas'] ?? 0 ?>h</h4>
                    <small class="text-muted">Executadas / Previstas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna Esquerda -->
        <div class="col-md-8">
            <!-- Informações Básicas -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-info-circle"></i> Informações do Projeto
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Descrição:</strong>
                            <p><?= nl2br(htmlspecialchars($projeto['descricao'] ?? 'Não informado')) ?></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Categoria:</strong> <?= htmlspecialchars($projeto['categoria_nome'] ?? 'N/A') ?></p>
                            <p><strong>Contrato:</strong> <?= htmlspecialchars($projeto['contrato_numero'] ?? 'Sem contrato') ?></p>
                            <p><strong>Prioridade:</strong> <span class="badge bg-<?= $projeto['prioridade'] === 'urgente' ? 'danger' : ($projeto['prioridade'] === 'alta' ? 'warning' : 'info') ?>"><?= ucfirst($projeto['prioridade'] ?? 'média') ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Data Início:</strong> <?= $projeto['data_inicio'] ? date('d/m/Y', strtotime($projeto['data_inicio'])) : 'Não definido' ?></p>
                            <p><strong>Data Fim:</strong> <?= $projeto['data_fim'] ? date('d/m/Y', strtotime($projeto['data_fim'])) : 'Não definido' ?></p>
                            <p><strong>Duração:</strong> <?= $projeto['duracao_estimada'] ?? 0 ?> dias</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Objetivos e Escopo -->
            <?php if ($projeto['objetivos'] || $projeto['escopo']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bullseye"></i> Objetivos e Escopo
                </div>
                <div class="card-body">
                    <?php if ($projeto['objetivos']): ?>
                        <div class="mb-3">
                            <strong>Objetivos:</strong>
                            <p><?= nl2br(htmlspecialchars($projeto['objetivos'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($projeto['escopo']): ?>
                        <div class="mb-3">
                            <strong>Escopo:</strong>
                            <p><?= nl2br(htmlspecialchars($projeto['escopo'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Equipe do Projeto -->
            <?php if (!empty($dashboard['equipe'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users"></i> Equipe do Projeto
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Papel</th>
                                <th>Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dashboard['equipe'] as $membro): ?>
                            <tr>
                                <td><?= htmlspecialchars($membro['usuario_nome']) ?></td>
                                <td><?= htmlspecialchars($membro['papel']) ?></td>
                                <td><?= $membro['horas_executadas'] ?? 0 ?>h</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Coluna Direita -->
        <div class="col-md-4">
            <!-- Dashboard Financeiro -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-chart-pie"></i> Dashboard Financeiro
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Orçamento Previsto</strong>
                        <h5 class="text-primary">R$ <?= number_format($dashboard['orcamento_previsto'] ?? 0, 2, ',', '.') ?></h5>
                    </div>
                    <div class="mb-3">
                        <strong>Realizado</strong>
                        <h5 class="text-success">R$ <?= number_format($dashboard['orcamento_realizado'] ?? 0, 2, ',', '.') ?></h5>
                    </div>
                    <div class="mb-3">
                        <strong>Saldo</strong>
                        <h5 class="<?= ($dashboard['orcamento_previsto'] - $dashboard['orcamento_realizado']) >= 0 ? 'text-info' : 'text-danger' ?>">
                            R$ <?= number_format(($dashboard['orcamento_previsto'] ?? 0) - ($dashboard['orcamento_realizado'] ?? 0), 2, ',', '.') ?>
                        </h5>
                    </div>
                </div>
            </div>

            <!-- Alterar Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-exchange-alt"></i> Alterar Status
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/?page=projetos&action=alterarStatus&id=<?= $projeto['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <option value="planejamento" <?= $projeto['status'] === 'planejamento' ? 'selected' : '' ?>>Planejamento</option>
                                <option value="execucao" <?= $projeto['status'] === 'execucao' ? 'selected' : '' ?>>Em Execução</option>
                                <option value="pausado" <?= $projeto['status'] === 'pausado' ? 'selected' : '' ?>>Pausado</option>
                                <option value="concluido" <?= $projeto['status'] === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                <option value="cancelado" <?= $projeto['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea name="motivo" class="form-control" rows="2" placeholder="Motivo da alteração (opcional)"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Alterar Status</button>
                    </form>
                </div>
            </div>

            <!-- Observações -->
            <?php if ($projeto['observacoes']): ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sticky-note"></i> Observações
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($projeto['observacoes'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/src/views/layout/footer.php'; ?>
