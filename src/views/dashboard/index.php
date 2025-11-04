<?php
// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login');
    exit;
}

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
$breadcrumb = [
    ['label' => 'Dashboard']
];

require __DIR__ . '/../layouts/header.php';

// Buscar dados para estatísticas (exemplo)
use App\Models\EmpresaTomadora;
use App\Models\EmpresaPrestadora;
use App\Models\Contrato;
use App\Models\Servico;

$empresaTomadoraModel = new EmpresaTomadora();
$empresaPrestadoraModel = new EmpresaPrestadora();
$contratoModel = new Contrato();
$servicoModel = new Servico();

$stats = [
    'empresas_tomadoras' => $empresaTomadoraModel->countTotal(),
    'empresas_prestadoras' => $empresaPrestadoraModel->countTotal(),
    'contratos_ativos' => $contratoModel->countPorStatus('Vigente'),
    'servicos_cadastrados' => $servicoModel->countTotal()
];
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-home"></i> Dashboard</h2>
        <p class="text-muted">Bem-vindo(a) ao Sistema de Gestão Clinfec</p>
    </div>
</div>

<!-- Estatísticas Principais -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Empresas Tomadoras</h6>
                        <h2 class="card-title mb-0"><?= $stats['empresas_tomadoras'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-briefcase fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/empresas-tomadoras" class="btn btn-sm btn-light">
                        Ver todas <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Empresas Prestadoras</h6>
                        <h2 class="card-title mb-0"><?= $stats['empresas_prestadoras'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-handshake fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/empresas-prestadoras" class="btn btn-sm btn-light">
                        Ver todas <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Contratos Ativos</h6>
                        <h2 class="card-title mb-0"><?= $stats['contratos_ativos'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-file-contract fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/contratos?status=Vigente" class="btn btn-sm btn-light">
                        Ver contratos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Serviços Cadastrados</h6>
                        <h2 class="card-title mb-0"><?= $stats['servicos_cadastrados'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-list fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/servicos" class="btn btn-sm btn-light">
                        Ver serviços <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="/empresas-tomadoras/create" class="btn btn-outline-primary d-block">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                            Nova Empresa Tomadora
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/empresas-prestadoras/create" class="btn btn-outline-success d-block">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                            Nova Empresa Prestadora
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/contratos/create" class="btn btn-outline-info d-block">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                            Novo Contrato
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/servicos/create" class="btn btn-outline-warning d-block">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                            Novo Serviço
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contratos Vencendo -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i> 
                    Contratos Vencendo (90 dias)
                </h5>
            </div>
            <div class="card-body">
                <?php
                $contratosVencendo = $contratoModel->getVencendo(90);
                if (empty($contratosVencendo)):
                ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> Nenhum contrato vencendo nos próximos 90 dias.
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach (array_slice($contratosVencendo, 0, 5) as $contrato): ?>
                            <a href="/contratos/<?= $contrato['id'] ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($contrato['numero_contrato']) ?></h6>
                                    <small class="text-danger">
                                        <?= $contrato['dias_restantes'] ?> dias
                                    </small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($contrato['tomadora_nome']) ?></p>
                                <small class="text-muted">Vencimento: <?= date('d/m/Y', strtotime($contrato['data_fim_vigencia'])) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($contratosVencendo) > 5): ?>
                        <div class="mt-3 text-center">
                            <a href="/contratos?vencendo=90" class="btn btn-sm btn-outline-primary">
                                Ver todos (<?= count($contratosVencendo) ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> 
                    Estatísticas Gerais
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td><i class="fas fa-building text-primary"></i> Total de Empresas</td>
                            <td class="text-end"><strong><?= $stats['empresas_tomadoras'] + $stats['empresas_prestadoras'] ?></strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-file-contract text-success"></i> Total de Contratos</td>
                            <td class="text-end"><strong><?= $contratoModel->countTotal() ?></strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-dollar-sign text-info"></i> Valor Total Contratos Ativos</td>
                            <td class="text-end"><strong>R$ <?= number_format($contratoModel->getValorTotalAtivos(), 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-list text-warning"></i> Serviços Cadastrados</td>
                            <td class="text-end"><strong><?= $stats['servicos_cadastrados'] ?></strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-users text-secondary"></i> Usuário Logado</td>
                            <td class="text-end"><strong><?= $_SESSION['usuario_nome'] ?? 'N/A' ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Instruções do Sistema -->
<div class="row">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Bem-vindo ao Sistema Clinfec</h5>
            </div>
            <div class="card-body">
                <h6>Primeiros Passos:</h6>
                <ol>
                    <li><strong>Cadastrar Empresas Tomadoras:</strong> Comece cadastrando as empresas clientes que contratarão serviços.</li>
                    <li><strong>Cadastrar Empresas Prestadoras:</strong> Registre as empresas que prestarão serviços.</li>
                    <li><strong>Cadastrar Serviços:</strong> Defina os tipos de serviços que serão prestados, com valores de referência.</li>
                    <li><strong>Criar Contratos:</strong> Formalize os acordos entre tomadoras e prestadoras, vinculando serviços.</li>
                    <li><strong>Gerenciar Projetos e Atividades:</strong> Organize o trabalho em projetos e acompanhe as atividades.</li>
                </ol>
                
                <div class="alert alert-info mt-3">
                    <strong><i class="fas fa-lightbulb"></i> Dica:</strong> 
                    Use os filtros em cada tela para encontrar registros rapidamente. 
                    Todas as tabelas possuem busca e ordenação.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
