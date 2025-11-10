<?php
$pageTitle = htmlspecialchars($empresa['nome_fantasia']);
$activeMenu = 'empresas-tomadoras';
$breadcrumb = [
    ['label' => 'Empresas'],
    ['label' => 'Empresas Tomadoras', 'url' => '/empresas-tomadoras'],
    ['label' => $empresa['nome_fantasia']]
];

require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>
            <?php if ($empresa['logo']): ?>
                <img src="/public/uploads/logos/<?= $empresa['logo'] ?>" alt="Logo" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
            <?php endif; ?>
            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
        </h2>
        <p class="text-muted"><?= htmlspecialchars($empresa['razao_social']) ?></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="/empresas-tomadoras/<?= $empresa['id'] ?>/edit" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
            <i class="fas fa-trash"></i> Excluir
        </button>
    </div>
</div>

<!-- Status -->
<div class="row mb-4">
    <div class="col-md-12">
        <?php if ($empresa['ativo']): ?>
            <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Empresa Ativa</span>
        <?php else: ?>
            <span class="badge bg-secondary fs-6"><i class="fas fa-pause-circle"></i> Empresa Inativa</span>
        <?php endif; ?>
    </div>
</div>

<!-- Abas -->
<ul class="nav nav-tabs mb-4" id="empresaTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button">
            <i class="fas fa-building"></i> Dados Principais
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="responsaveis-tab" data-bs-toggle="tab" data-bs-target="#responsaveis" type="button">
            <i class="fas fa-users"></i> Responsáveis (<?= count($responsaveis) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button">
            <i class="fas fa-file-alt"></i> Documentos (<?= count($documentos) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="contratos-tab" data-bs-toggle="tab" data-bs-target="#contratos" type="button">
            <i class="fas fa-file-contract"></i> Contratos (<?= $totalContratos ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="projetos-tab" data-bs-toggle="tab" data-bs-target="#projetos" type="button">
            <i class="fas fa-project-diagram"></i> Projetos (<?= $totalProjetos ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="empresaTabContent">
    <!-- Dados Principais -->
    <div class="tab-pane fade show active" id="dados" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informações Gerais</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Razão Social:</strong></td>
                                <td><?= htmlspecialchars($empresa['razao_social']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nome Fantasia:</strong></td>
                                <td><?= htmlspecialchars($empresa['nome_fantasia']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>CNPJ:</strong></td>
                                <td><span class="font-monospace"><?= formatCnpj($empresa['cnpj']) ?></span></td>
                            </tr>
                            <?php if ($empresa['inscricao_estadual']): ?>
                            <tr>
                                <td><strong>Inscrição Estadual:</strong></td>
                                <td><?= htmlspecialchars($empresa['inscricao_estadual']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['inscricao_municipal']): ?>
                            <tr>
                                <td><strong>Inscrição Municipal:</strong></td>
                                <td><?= htmlspecialchars($empresa['inscricao_municipal']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['site']): ?>
                            <tr>
                                <td><strong>Website:</strong></td>
                                <td><a href="<?= htmlspecialchars($empresa['site']) ?>" target="_blank"><?= htmlspecialchars($empresa['site']) ?></a></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Endereço</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($empresa['logradouro']): ?>
                            <p class="mb-1">
                                <?= htmlspecialchars($empresa['logradouro']) ?>, 
                                <?= htmlspecialchars($empresa['numero']) ?>
                                <?php if ($empresa['complemento']): ?>
                                    - <?= htmlspecialchars($empresa['complemento']) ?>
                                <?php endif; ?>
                            </p>
                            <p class="mb-1"><?= htmlspecialchars($empresa['bairro']) ?></p>
                            <p class="mb-1">
                                <?= htmlspecialchars($empresa['cidade']) ?> - <?= $empresa['estado'] ?>
                            </p>
                            <?php if ($empresa['cep']): ?>
                                <p class="mb-0">CEP: <?= formatCep($empresa['cep']) ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">Endereço não cadastrado</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-phone"></i> Contatos</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>E-mail Principal:</strong></td>
                                <td><a href="mailto:<?= $empresa['email_principal'] ?>"><?= htmlspecialchars($empresa['email_principal']) ?></a></td>
                            </tr>
                            <?php if ($empresa['email_financeiro']): ?>
                            <tr>
                                <td><strong>E-mail Financeiro:</strong></td>
                                <td><a href="mailto:<?= $empresa['email_financeiro'] ?>"><?= htmlspecialchars($empresa['email_financeiro']) ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['email_projetos']): ?>
                            <tr>
                                <td><strong>E-mail Projetos:</strong></td>
                                <td><a href="mailto:<?= $empresa['email_projetos'] ?>"><?= htmlspecialchars($empresa['email_projetos']) ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['telefone_principal']): ?>
                            <tr>
                                <td><strong>Telefone Principal:</strong></td>
                                <td><?= formatTelefone($empresa['telefone_principal']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['celular']): ?>
                            <tr>
                                <td><strong>Celular:</strong></td>
                                <td><?= formatTelefone($empresa['celular']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['whatsapp']): ?>
                            <tr>
                                <td><strong>WhatsApp:</strong></td>
                                <td>
                                    <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $empresa['whatsapp']) ?>" target="_blank">
                                        <?= formatTelefone($empresa['whatsapp']) ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Dados Financeiros</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <?php if ($empresa['dia_fechamento']): ?>
                            <tr>
                                <td><strong>Dia de Fechamento:</strong></td>
                                <td>Dia <?= $empresa['dia_fechamento'] ?> de cada mês</td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['dia_pagamento']): ?>
                            <tr>
                                <td><strong>Dia de Pagamento:</strong></td>
                                <td>Dia <?= $empresa['dia_pagamento'] ?> de cada mês</td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['forma_pagamento_preferencial']): ?>
                            <tr>
                                <td><strong>Forma de Pagamento:</strong></td>
                                <td><?= htmlspecialchars($empresa['forma_pagamento_preferencial']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['banco']): ?>
                            <tr>
                                <td><strong>Banco:</strong></td>
                                <td><?= htmlspecialchars($empresa['banco']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['agencia']): ?>
                            <tr>
                                <td><strong>Agência:</strong></td>
                                <td><?= htmlspecialchars($empresa['agencia']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($empresa['conta']): ?>
                            <tr>
                                <td><strong>Conta:</strong></td>
                                <td><?= htmlspecialchars($empresa['conta']) ?> (<?= htmlspecialchars($empresa['tipo_conta'] ?? '') ?>)</td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($empresa['observacoes']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comments"></i> Observações</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($empresa['observacoes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Responsáveis -->
    <div class="tab-pane fade" id="responsaveis" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Responsáveis</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalResponsavel">
                        <i class="fas fa-plus"></i> Adicionar Responsável
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($responsaveis)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhum responsável cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>E-mail</th>
                                    <th>Telefone</th>
                                    <th>Principal</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($responsaveis as $resp): ?>
                                <tr>
                                    <td><?= htmlspecialchars($resp['nome']) ?></td>
                                    <td><?= htmlspecialchars($resp['cargo'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($resp['email']) ?></td>
                                    <td><?= formatTelefone($resp['celular'] ?? $resp['telefone'] ?? '') ?></td>
                                    <td>
                                        <?php if ($resp['responsavel_principal']): ?>
                                            <span class="badge bg-primary">Principal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="/empresas-tomadoras/<?= $empresa['id'] ?>/responsaveis/<?= $resp['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remover este responsável?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Documentos -->
    <div class="tab-pane fade" id="documentos" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Documentos</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalDocumento">
                        <i class="fas fa-plus"></i> Adicionar Documento
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($documentos)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhum documento cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Nome</th>
                                    <th>Data Emissão</th>
                                    <th>Validade</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['tipo_documento']) ?></td>
                                    <td><?= htmlspecialchars($doc['nome_documento']) ?></td>
                                    <td><?= $doc['data_emissao'] ? date('d/m/Y', strtotime($doc['data_emissao'])) : '-' ?></td>
                                    <td>
                                        <?php if ($doc['data_validade']): ?>
                                            <?= date('d/m/Y', strtotime($doc['data_validade'])) ?>
                                            <?php
                                            $diasRestantes = (strtotime($doc['data_validade']) - time()) / 86400;
                                            if ($diasRestantes < 0): ?>
                                                <span class="badge bg-danger">Vencido</span>
                                            <?php elseif ($diasRestantes < 30): ?>
                                                <span class="badge bg-warning">Vence em <?= ceil($diasRestantes) ?> dias</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Indeterminada
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?= $doc['status'] === 'Ativo' ? 'success' : 'secondary' ?>"><?= $doc['status'] ?></span></td>
                                    <td>
                                        <a href="/public/uploads/documentos/<?= $doc['arquivo'] ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form method="POST" action="/empresas-tomadoras/<?= $empresa['id'] ?>/documentos/<?= $doc['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remover este documento?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Contratos -->
    <div class="tab-pane fade" id="contratos" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-contract"></i> Contratos</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <a href="/contratos?empresa_tomadora_id=<?= $empresa['id'] ?>">
                        Ver todos os contratos desta empresa (<?= $totalContratos ?>)
                    </a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Projetos -->
    <div class="tab-pane fade" id="projetos" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-project-diagram"></i> Projetos</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <a href="/projetos?empresa_tomadora_id=<?= $empresa['id'] ?>">
                        Ver todos os projetos desta empresa (<?= $totalProjetos ?>)
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Responsável -->
<div class="modal fade" id="modalResponsavel" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/empresas-tomadoras/<?= $empresa['id'] ?>/responsaveis/add">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Responsável</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" class="form-control" name="cargo">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Celular</label>
                            <input type="text" class="form-control mask-celular" name="celular">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="responsavel_principal" id="responsavel_principal">
                        <label class="form-check-label" for="responsavel_principal">Responsável Principal</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="ativo" id="resp_ativo" checked>
                        <label class="form-check-label" for="resp_ativo">Ativo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Adicionar Documento -->
<div class="modal fade" id="modalDocumento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/empresas-tomadoras/<?= $empresa['id'] ?>/documentos/add" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo_documento" required>
                                <option value="Contrato Social">Contrato Social</option>
                                <option value="Alteração Contratual">Alteração Contratual</option>
                                <option value="Certidão Negativa">Certidão Negativa</option>
                                <option value="Alvará">Alvará</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome do Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome_documento" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Arquivo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="arquivo" required>
                        <small class="form-text text-muted">PDF, JPG, PNG, DOC, DOCX (máx. 10MB)</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Emissão</label>
                            <input type="date" class="form-control" name="data_emissao">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Validade</label>
                            <input type="date" class="form-control" name="data_validade">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form de exclusão -->
<form id="deleteForm" method="POST" action="/empresas-tomadoras/<?= $empresa['id'] ?>/delete" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<?php
function formatCnpj($cnpj) {
    return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
}

function formatCep($cep) {
    return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
}

function formatTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) == 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) == 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    return $telefone;
}

$inlineJS = <<<'JAVASCRIPT'
function confirmarExclusao() {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: 'Tem certeza que deseja excluir esta empresa? Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm').submit();
        }
    });
}
JAVASCRIPT;

require __DIR__ . '/../layouts/footer.php';
?>
