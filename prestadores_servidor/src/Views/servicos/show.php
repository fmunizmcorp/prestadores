<?php
/**
 * View: Visualização Detalhada de Serviço
 * Controller: ServicoController
 * 
 * Exibe todos os detalhes do serviço com:
 * - Dados principais
 * - Requisitos e qualificações
 * - Valores de referência
 * - Histórico de valores
 * - Contratos que utilizam este serviço
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?page=servicos">Serviços</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($servico['nome']) ?></li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <?= htmlspecialchars($servico['nome']) ?>
                <?php if ($servico['ativo']): ?>
                <span class="badge bg-success ms-2">Ativo</span>
                <?php else: ?>
                <span class="badge bg-secondary ms-2">Inativo</span>
                <?php endif; ?>
            </h1>
            <p class="text-muted mb-0">
                <strong>Código:</strong> <?= htmlspecialchars($servico['codigo']) ?>
                <?php if (!empty($servico['tipo'])): ?>
                | <strong>Tipo:</strong> <?= htmlspecialchars($servico['tipo']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <a href="?page=servicos" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
            <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
            <a href="?page=servicos&action=edit&id=<?= $servico['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="servicoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button">
                <i class="fas fa-info-circle me-2"></i>Dados Principais
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="requisitos-tab" data-bs-toggle="tab" data-bs-target="#requisitos" type="button">
                <i class="fas fa-clipboard-list me-2"></i>Requisitos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="valores-tab" data-bs-toggle="tab" data-bs-target="#valores" type="button">
                <i class="fas fa-dollar-sign me-2"></i>Valores
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="contratos-tab" data-bs-toggle="tab" data-bs-target="#contratos" type="button">
                <i class="fas fa-file-contract me-2"></i>Contratos
                <span class="badge bg-primary ms-1"><?= count($contratos ?? []) ?></span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="servicoTabsContent">
        <!-- Tab 1: Dados Principais -->
        <div class="tab-pane fade show active" id="dados" role="tabpanel">
            <div class="row g-4">
                <!-- Informações Básicas -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações Básicas</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="40%">Código:</th>
                                        <td><strong><?= htmlspecialchars($servico['codigo']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Nome:</th>
                                        <td><?= htmlspecialchars($servico['nome']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tipo:</th>
                                        <td>
                                            <?php if (!empty($servico['tipo'])): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($servico['tipo']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">Não informado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Categoria:</th>
                                        <td><?= !empty($servico['categoria']) ? htmlspecialchars($servico['categoria']) : '<span class="text-muted">Não informada</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>CBO:</th>
                                        <td><?= !empty($servico['cbo']) ? htmlspecialchars($servico['cbo']) : '<span class="text-muted">Não informado</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Carga Horária:</th>
                                        <td>
                                            <?php if (!empty($servico['carga_horaria_semanal'])): ?>
                                            <?= $servico['carga_horaria_semanal'] ?> horas/semana
                                            <?php else: ?>
                                            <span class="text-muted">Não informada</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Jornada Padrão:</th>
                                        <td><?= !empty($servico['jornada_padrao']) ? htmlspecialchars($servico['jornada_padrao']) : '<span class="text-muted">Não informada</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Teletrabalho:</th>
                                        <td>
                                            <?php if ($servico['permite_teletrabalho']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Permite</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Não permite</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <?php if ($servico['ativo']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Ativo</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Descrição e Atividades -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-align-left me-2"></i>Descrição e Atividades</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($servico['descricao'])): ?>
                            <h6>Descrição:</h6>
                            <p class="text-justify"><?= nl2br(htmlspecialchars($servico['descricao'])) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($servico['atividades_principais'])): ?>
                            <h6 class="mt-3">Atividades Principais:</h6>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($servico['atividades_principais'])) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (empty($servico['descricao']) && empty($servico['atividades_principais'])): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhuma descrição ou atividade cadastrada.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Informações Complementares -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Informações Complementares</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6>Local de Trabalho:</h6>
                                    <p><?= !empty($servico['local_trabalho_padrao']) ? htmlspecialchars($servico['local_trabalho_padrao']) : '<span class="text-muted">Não informado</span>' ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Equipamentos Necessários:</h6>
                                    <p><?= !empty($servico['equipamentos_necessarios']) ? htmlspecialchars($servico['equipamentos_necessarios']) : '<span class="text-muted">Não informado</span>' ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Nível de Complexidade:</h6>
                                    <p>
                                        <?php if (!empty($servico['nivel_complexidade'])): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($servico['nivel_complexidade']) ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">Não informado</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($servico['uniformes_epis'])): ?>
                            <div class="mt-3">
                                <h6>Uniformes e EPIs:</h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($servico['uniformes_epis'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($servico['beneficios_padrao'])): ?>
                            <div class="mt-3">
                                <h6>Benefícios Padrão:</h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($servico['beneficios_padrao'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($servico['observacoes'])): ?>
                            <div class="mt-3">
                                <h6>Observações Gerais:</h6>
                                <div class="alert alert-warning">
                                    <?= nl2br(htmlspecialchars($servico['observacoes'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Requisitos -->
        <div class="tab-pane fade" id="requisitos" role="tabpanel">
            <div class="row g-4">
                <!-- Requisitos Básicos -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Requisitos Básicos</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="45%">Escolaridade Mínima:</th>
                                        <td>
                                            <?php if (!empty($servico['escolaridade_minima'])): ?>
                                            <span class="badge bg-primary"><?= htmlspecialchars($servico['escolaridade_minima']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">Não exigida</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Experiência Mínima:</th>
                                        <td>
                                            <?php if (!empty($servico['experiencia_minima'])): ?>
                                            <?= $servico['experiencia_minima'] ?> <?= $servico['experiencia_minima'] == 1 ? 'ano' : 'anos' ?>
                                            <?php else: ?>
                                            <span class="text-muted">Não exigida</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Idiomas:</th>
                                        <td><?= !empty($servico['idiomas_requeridos']) ? htmlspecialchars($servico['idiomas_requeridos']) : '<span class="text-muted">Não requerido</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>CNH:</th>
                                        <td>
                                            <?php if (!empty($servico['cnh_obrigatoria'])): ?>
                                            <span class="badge bg-danger">Categoria <?= htmlspecialchars($servico['cnh_obrigatoria']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">Não exigida</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Certificações -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificações</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($servico['certificacoes_obrigatorias'])): ?>
                            <h6><i class="fas fa-exclamation-circle text-danger me-2"></i>Obrigatórias:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <?= nl2br(htmlspecialchars($servico['certificacoes_obrigatorias'])) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($servico['certificacoes_desejaveis'])): ?>
                            <h6><i class="fas fa-star text-warning me-2"></i>Desejáveis:</h6>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($servico['certificacoes_desejaveis'])) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (empty($servico['certificacoes_obrigatorias']) && empty($servico['certificacoes_desejaveis'])): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhuma certificação cadastrada.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Habilidades Técnicas -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Habilidades Técnicas</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($servico['habilidades_tecnicas'])): ?>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($servico['habilidades_tecnicas'])) ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhuma habilidade técnica cadastrada.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Habilidades Comportamentais -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Habilidades Comportamentais</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($servico['habilidades_comportamentais'])): ?>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($servico['habilidades_comportamentais'])) ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhuma habilidade comportamental cadastrada.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Valores -->
        <div class="tab-pane fade" id="valores" role="tabpanel">
            <div class="row g-4">
                <!-- Valor Atual -->
                <div class="col-md-4">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Valor Atual</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($servico['valor_referencia']) && $servico['valor_referencia'] > 0): ?>
                            <h2 class="text-success mb-3">
                                R$ <?= number_format($servico['valor_referencia'], 2, ',', '.') ?>
                            </h2>
                            <?php if (!empty($servico['tipo_valor'])): ?>
                            <p class="mb-0"><strong>Tipo:</strong> <?= htmlspecialchars($servico['tipo_valor']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($servico['moeda']) && $servico['moeda'] !== 'BRL'): ?>
                            <p class="mb-0"><strong>Moeda:</strong> <?= htmlspecialchars($servico['moeda']) ?></p>
                            <?php endif; ?>
                            <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Valor não definido
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Vigência -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Vigência do Valor</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="30%">Data Início:</th>
                                        <td>
                                            <?php if (!empty($servico['data_vigencia_inicio'])): ?>
                                            <?= date('d/m/Y', strtotime($servico['data_vigencia_inicio'])) ?>
                                            <?php else: ?>
                                            <span class="text-muted">Não definida</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Data Fim:</th>
                                        <td>
                                            <?php if (!empty($servico['data_vigencia_fim'])): ?>
                                            <?php
                                            $dataFim = strtotime($servico['data_vigencia_fim']);
                                            $hoje = time();
                                            $vencido = $dataFim < $hoje;
                                            ?>
                                            <?= date('d/m/Y', $dataFim) ?>
                                            <?php if ($vencido): ?>
                                            <span class="badge bg-danger ms-2"><i class="fas fa-exclamation-circle me-1"></i>Vencido</span>
                                            <?php else: ?>
                                            <span class="badge bg-success ms-2"><i class="fas fa-check-circle me-1"></i>Vigente</span>
                                            <?php endif; ?>
                                            <?php else: ?>
                                            <span class="text-muted">Indeterminada</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <?php if (!empty($servico['observacoes_valor'])): ?>
                            <div class="mt-3">
                                <h6>Observações:</h6>
                                <div class="alert alert-light">
                                    <?= nl2br(htmlspecialchars($servico['observacoes_valor'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Histórico de Valores (se implementado) -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Histórico de Valores</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($historicoValores)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data Início</th>
                                            <th>Data Fim</th>
                                            <th>Valor</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historicoValores as $historico): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($historico['data_inicio'])) ?></td>
                                            <td><?= !empty($historico['data_fim']) ? date('d/m/Y', strtotime($historico['data_fim'])) : 'Indeterminada' ?></td>
                                            <td><strong>R$ <?= number_format($historico['valor'], 2, ',', '.') ?></strong></td>
                                            <td><?= htmlspecialchars($historico['tipo_valor']) ?></td>
                                            <td>
                                                <?php if (strtotime($historico['data_fim']) < time()): ?>
                                                <span class="badge bg-secondary">Expirado</span>
                                                <?php else: ?>
                                                <span class="badge bg-success">Vigente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhum histórico de valores registrado.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Contratos -->
        <div class="tab-pane fade" id="contratos" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>Contratos que Utilizam este Serviço
                        <span class="badge bg-light text-primary ms-2"><?= count($contratos ?? []) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($contratos)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Número</th>
                                    <th>Empresa Tomadora</th>
                                    <th>Quantidade</th>
                                    <th>Valor no Contrato</th>
                                    <th>Vigência</th>
                                    <th>Status</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contratos as $contrato): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($contrato['numero_contrato']) ?></strong></td>
                                    <td><?= htmlspecialchars($contrato['empresa_nome']) ?></td>
                                    <td><?= $contrato['quantidade'] ?> posto(s)</td>
                                    <td><strong class="text-success">R$ <?= number_format($contrato['valor_unitario'], 2, ',', '.') ?></strong></td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($contrato['data_inicio'])) ?>
                                        até
                                        <?= date('d/m/Y', strtotime($contrato['data_termino'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($contrato['status'] === 'Ativo'): ?>
                                        <span class="badge bg-success">Ativo</span>
                                        <?php elseif ($contrato['status'] === 'Vencido'): ?>
                                        <span class="badge bg-danger">Vencido</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($contrato['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?page=contratos&action=show&id=<?= $contrato['id'] ?>" class="btn btn-sm btn-info" title="Ver Contrato">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Este serviço ainda não está sendo utilizado em nenhum contrato.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Information -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Informações de Auditoria</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Criado em:</strong> 
                        <?= date('d/m/Y \à\s H:i', strtotime($servico['created_at'])) ?>
                    </p>
                    <?php if (!empty($servico['created_by'])): ?>
                    <p class="mb-0">
                        <strong>Criado por:</strong> 
                        <?= htmlspecialchars($servico['created_by_name'] ?? 'Usuário #' . $servico['created_by']) ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($servico['updated_at'])): ?>
                    <p class="mb-1">
                        <strong>Última atualização:</strong> 
                        <?= date('d/m/Y \à\s H:i', strtotime($servico['updated_at'])) ?>
                    </p>
                    <?php if (!empty($servico['updated_by'])): ?>
                    <p class="mb-0">
                        <strong>Atualizado por:</strong> 
                        <?= htmlspecialchars($servico['updated_by_name'] ?? 'Usuário #' . $servico['updated_by']) ?>
                    </p>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer Instructions -->
<div class="container-fluid mt-5 mb-3">
    <div class="alert alert-light border">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Instruções de Navegação</h6>
        <ul class="mb-0 small">
            <li><strong>Dados Principais:</strong> Visualize informações básicas do serviço, descrição e atividades</li>
            <li><strong>Requisitos:</strong> Veja escolaridade, experiência, certificações e habilidades necessárias</li>
            <li><strong>Valores:</strong> Consulte o valor de referência atual e histórico de valores</li>
            <li><strong>Contratos:</strong> Lista de contratos que utilizam este serviço</li>
            <li><strong>Editar:</strong> Clique no botão "Editar" para modificar informações do serviço</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
