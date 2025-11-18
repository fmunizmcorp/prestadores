<?php
/**
 * View: Visualização Detalhada de Contrato
 * Controller: ContratoController
 * 
 * Exibe todos os detalhes do contrato com:
 * - Dados principais
 * - Informações financeiras
 * - Serviços contratados
 * - Aditivos
 * - Histórico de alterações
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?page=contratos">Contratos</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($contrato['numero_contrato']) ?></li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                Contrato <?= htmlspecialchars($contrato['numero_contrato']) ?>
                <?php
                $statusClass = 'secondary';
                switch ($contrato['status']) {
                    case 'Ativo': $statusClass = 'success'; break;
                    case 'Suspenso': $statusClass = 'warning'; break;
                    case 'Encerrado': $statusClass = 'secondary'; break;
                    case 'Vencido': $statusClass = 'danger'; break;
                }
                ?>
                <span class="badge bg-<?= $statusClass ?> ms-2"><?= htmlspecialchars($contrato['status']) ?></span>
            </h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($contrato['empresa_nome']) ?></p>
        </div>
        <div>
            <a href="?page=contratos" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
            <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
            <a href="?page=contratos&action=edit&id=<?= $contrato['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <?php endif; ?>
            <?php if (!empty($contrato['arquivo_contrato'])): ?>
            <a href="<?= htmlspecialchars($contrato['arquivo_contrato']) ?>" class="btn btn-info" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Ver PDF
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert for expiring contract -->
    <?php
    $diasRestantes = ceil((strtotime($contrato['data_termino']) - time()) / 86400);
    if ($diasRestantes > 0 && $diasRestantes <= 90 && $contrato['status'] === 'Ativo'):
    ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Atenção:</strong> Este contrato vence em <?= $diasRestantes ?> dias 
        (<?= date('d/m/Y', strtotime($contrato['data_termino'])) ?>)
    </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="contratoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button">
                <i class="fas fa-info-circle me-2"></i>Dados Principais
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="financeiro-tab" data-bs-toggle="tab" data-bs-target="#financeiro" type="button">
                <i class="fas fa-dollar-sign me-2"></i>Financeiro
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="servicos-tab" data-bs-toggle="tab" data-bs-target="#servicos" type="button">
                <i class="fas fa-briefcase me-2"></i>Serviços
                <span class="badge bg-primary ms-1"><?= count($servicosContrato ?? []) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="aditivos-tab" data-bs-toggle="tab" data-bs-target="#aditivos" type="button">
                <i class="fas fa-file-signature me-2"></i>Aditivos
                <span class="badge bg-info ms-1"><?= count($aditivos ?? []) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="historico-tab" data-bs-toggle="tab" data-bs-target="#historico" type="button">
                <i class="fas fa-history me-2"></i>Histórico
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="contratoTabsContent">
        <!-- Tab 1: Dados Principais -->
        <div class="tab-pane fade show active" id="dados" role="tabpanel">
            <div class="row g-4">
                <!-- Informações Básicas -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Informações do Contrato</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="40%">Número:</th>
                                        <td><strong><?= htmlspecialchars($contrato['numero_contrato']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Tipo:</th>
                                        <td>
                                            <?php if (!empty($contrato['tipo_contrato'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($contrato['tipo_contrato']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">Não informado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Processo:</th>
                                        <td><?= !empty($contrato['numero_processo']) ? htmlspecialchars($contrato['numero_processo']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Modalidade:</th>
                                        <td><?= !empty($contrato['modalidade']) ? htmlspecialchars($contrato['modalidade']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Data Assinatura:</th>
                                        <td><?= date('d/m/Y', strtotime($contrato['data_assinatura'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Início Vigência:</th>
                                        <td><?= date('d/m/Y', strtotime($contrato['data_inicio'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Término Vigência:</th>
                                        <td>
                                            <?= date('d/m/Y', strtotime($contrato['data_termino'])) ?>
                                            <?php if ($diasRestantes > 0 && $diasRestantes <= 90): ?>
                                            <br><span class="badge bg-warning text-dark">Faltam <?= $diasRestantes ?> dias</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Prazo:</th>
                                        <td>
                                            <?= !empty($contrato['prazo_meses']) ? $contrato['prazo_meses'] . ' meses' : '<span class="text-muted">-</span>' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Renovação:</th>
                                        <td>
                                            <?php if ($contrato['renovacao_automatica']): ?>
                                            <span class="badge bg-success">Automática</span>
                                            <?php if (!empty($contrato['prazo_renovacao_meses'])): ?>
                                            <br><small>Por <?= $contrato['prazo_renovacao_meses'] ?> meses</small>
                                            <?php endif; ?>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Não automática</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars($contrato['status']) ?></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Empresa Tomadora -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Empresa Tomadora</h5>
                        </div>
                        <div class="card-body">
                            <h6><?= htmlspecialchars($contrato['empresa_nome']) ?></h6>
                            <p class="mb-2">
                                <strong>CNPJ:</strong> <?= htmlspecialchars($contrato['empresa_cnpj']) ?><br>
                                <?php if (!empty($contrato['empresa_endereco'])): ?>
                                <strong>Endereço:</strong> <?= htmlspecialchars($contrato['empresa_endereco']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($contrato['empresa_telefone'])): ?>
                                <strong>Telefone:</strong> <?= htmlspecialchars($contrato['empresa_telefone']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($contrato['empresa_email'])): ?>
                                <strong>E-mail:</strong> <?= htmlspecialchars($contrato['empresa_email']) ?>
                                <?php endif; ?>
                            </p>
                            <a href="?page=empresas-tomadoras&action=show&id=<?= $contrato['empresa_tomadora_id'] ?>" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-external-link-alt me-1"></i>Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Objeto do Contrato -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-align-left me-2"></i>Objeto do Contrato</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-justify"><?= nl2br(htmlspecialchars($contrato['objeto'])) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Gestores e Responsáveis -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Gestor do Contrato</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($contrato['gestor_contrato_nome'])): ?>
                            <p class="mb-0">
                                <strong><?= htmlspecialchars($contrato['gestor_contrato_nome']) ?></strong><br>
                                <?php if (!empty($contrato['gestor_contrato_email'])): ?>
                                <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($contrato['gestor_contrato_email']) ?>
                                <?php endif; ?>
                            </p>
                            <?php else: ?>
                            <p class="text-muted mb-0">Não informado</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Fiscal do Contrato</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($contrato['fiscal_contrato_nome'])): ?>
                            <p class="mb-0">
                                <strong><?= htmlspecialchars($contrato['fiscal_contrato_nome']) ?></strong><br>
                                <?php if (!empty($contrato['fiscal_contrato_email'])): ?>
                                <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($contrato['fiscal_contrato_email']) ?>
                                <?php endif; ?>
                            </p>
                            <?php else: ?>
                            <p class="text-muted mb-0">Não informado</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Cláusulas e Observações -->
                <?php if (!empty($contrato['clausulas_importantes']) || !empty($contrato['observacoes'])): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Cláusulas e Observações</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($contrato['clausulas_importantes'])): ?>
                            <h6>Cláusulas Importantes:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <?= nl2br(htmlspecialchars($contrato['clausulas_importantes'])) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($contrato['observacoes'])): ?>
                            <h6>Observações Gerais:</h6>
                            <div class="alert alert-info mb-0">
                                <?= nl2br(htmlspecialchars($contrato['observacoes'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab 2: Financeiro -->
        <div class="tab-pane fade" id="financeiro" role="tabpanel">
            <div class="row g-4">
                <!-- Valores -->
                <div class="col-md-4">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Valor do Contrato</h5>
                        </div>
                        <div class="card-body text-center">
                            <h2 class="text-success mb-3">
                                R$ <?= number_format($contrato['valor_total'], 2, ',', '.') ?>
                            </h2>
                            <?php if (!empty($contrato['tipo_valor'])): ?>
                            <p class="mb-0"><span class="badge bg-secondary"><?= htmlspecialchars($contrato['tipo_valor']) ?></span></p>
                            <?php endif; ?>
                            <?php if (!empty($contrato['moeda']) && $contrato['moeda'] !== 'BRL'): ?>
                            <p class="mb-0 mt-2"><strong>Moeda:</strong> <?= htmlspecialchars($contrato['moeda']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Pagamento -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-money-check me-2"></i>Informações de Pagamento</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="40%">Forma de Pagamento:</th>
                                        <td><?= !empty($contrato['forma_pagamento']) ? htmlspecialchars($contrato['forma_pagamento']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Dia de Vencimento:</th>
                                        <td><?= !empty($contrato['dia_vencimento']) ? 'Dia ' . $contrato['dia_vencimento'] : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Periodicidade:</th>
                                        <td><?= !empty($contrato['periodicidade_faturamento']) ? htmlspecialchars($contrato['periodicidade_faturamento']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Índice de Reajuste:</th>
                                        <td>
                                            <?php if (!empty($contrato['indice_reajuste'])): ?>
                                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($contrato['indice_reajuste']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">Nenhum</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Próximo Reajuste:</th>
                                        <td>
                                            <?php if (!empty($contrato['data_proximo_reajuste'])): ?>
                                            <?= date('d/m/Y', strtotime($contrato['data_proximo_reajuste'])) ?>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php if (!empty($contrato['observacoes_financeiras'])): ?>
                            <div class="alert alert-light mt-3 mb-0">
                                <strong>Observações:</strong><br>
                                <?= nl2br(htmlspecialchars($contrato['observacoes_financeiras'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Serviços -->
        <div class="tab-pane fade" id="servicos" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>Serviços do Contrato
                        <span class="badge bg-light text-primary ms-2"><?= count($servicosContrato ?? []) ?></span>
                    </h5>
                    <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdicionarServico">
                        <i class="fas fa-plus me-1"></i>Adicionar Serviço
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($servicosContrato)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Serviço</th>
                                    <th>Quantidade</th>
                                    <th>Valor Unitário</th>
                                    <th>Valor Total</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalServicos = 0;
                                foreach ($servicosContrato as $sc):
                                    $valorTotal = $sc['quantidade'] * $sc['valor_unitario'];
                                    $totalServicos += $valorTotal;
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($sc['servico_codigo']) ?></strong></td>
                                    <td><?= htmlspecialchars($sc['servico_nome']) ?></td>
                                    <td><?= $sc['quantidade'] ?> posto(s)</td>
                                    <td>R$ <?= number_format($sc['valor_unitario'], 2, ',', '.') ?></td>
                                    <td><strong class="text-success">R$ <?= number_format($valorTotal, 2, ',', '.') ?></strong></td>
                                    <td class="text-center">
                                        <a href="?page=servicos&action=show&id=<?= $sc['servico_id'] ?>" class="btn btn-sm btn-info" title="Ver Serviço">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-secondary">
                                    <th colspan="4" class="text-end">TOTAL:</th>
                                    <th colspan="2"><strong class="text-success">R$ <?= number_format($totalServicos, 2, ',', '.') ?></strong></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Nenhum serviço adicionado a este contrato ainda.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Aditivos -->
        <div class="tab-pane fade" id="aditivos" role="tabpanel">
            <div class="card">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-signature me-2"></i>Aditivos do Contrato
                        <span class="badge bg-light text-dark ms-2"><?= count($aditivos ?? []) ?></span>
                    </h5>
                    <?php if (App\Controllers\AuthController::checkRole(['Master', 'Admin'])): ?>
                    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdicionarAditivo">
                        <i class="fas fa-plus me-1"></i>Adicionar Aditivo
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($aditivos)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Número</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th class="text-center">Arquivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($aditivos as $aditivo): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($aditivo['numero_aditivo']) ?></strong></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($aditivo['tipo']) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($aditivo['data_aditivo'])) ?></td>
                                    <td><?= htmlspecialchars($aditivo['descricao']) ?></td>
                                    <td>
                                        <?php if (!empty($aditivo['valor']) && $aditivo['valor'] != 0): ?>
                                        R$ <?= number_format($aditivo['valor'], 2, ',', '.') ?>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($aditivo['arquivo'])): ?>
                                        <a href="<?= htmlspecialchars($aditivo['arquivo']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
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
                        Nenhum aditivo registrado para este contrato.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab 5: Histórico -->
        <div class="tab-pane fade" id="historico" role="tabpanel">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Histórico de Alterações</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($historico)): ?>
                    <div class="timeline">
                        <?php foreach ($historico as $h): ?>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-icon bg-primary text-white rounded-circle p-2 me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($h['acao']) ?></h6>
                                    <p class="text-muted mb-1">
                                        <small>
                                            <?= date('d/m/Y H:i', strtotime($h['data_acao'])) ?>
                                            <?php if (!empty($h['usuario_nome'])): ?>
                                            - por <?= htmlspecialchars($h['usuario_nome']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </p>
                                    <?php if (!empty($h['descricao'])): ?>
                                    <p class="mb-0"><?= htmlspecialchars($h['descricao']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Nenhum histórico disponível.
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
                        <?= date('d/m/Y \à\s H:i', strtotime($contrato['created_at'])) ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($contrato['updated_at'])): ?>
                    <p class="mb-1">
                        <strong>Última atualização:</strong> 
                        <?= date('d/m/Y \à\s H:i', strtotime($contrato['updated_at'])) ?>
                    </p>
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
            <li><strong>Dados Principais:</strong> Visualize informações completas do contrato, empresa e responsáveis</li>
            <li><strong>Financeiro:</strong> Consulte valores, forma de pagamento e informações de reajuste</li>
            <li><strong>Serviços:</strong> Veja todos os serviços contratados com quantidades e valores</li>
            <li><strong>Aditivos:</strong> Acompanhe todos os aditivos contratuais registrados</li>
            <li><strong>Histórico:</strong> Visualize o registro completo de alterações no contrato</li>
            <li><strong>Editar:</strong> Use o botão "Editar" para modificar informações do contrato</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
