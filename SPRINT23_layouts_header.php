<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestão Clinfec - Gerenciamento de Empresas, Contratos, Projetos e Atividades">
    <title><?= $pageTitle ?? 'Sistema Clinfec' ?> - Clinfec</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
    
    <?php if (isset($customCSS)): ?>
        <?php foreach ($customCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="fas fa-building"></i> Clinfec
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= isset($activeMenu) && $activeMenu === 'dashboard' ? 'active' : '' ?>" href="/">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    
                    <!-- Empresas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= isset($activeMenu) && in_array($activeMenu, ['empresas-tomadoras', 'empresas-prestadoras']) ? 'active' : '' ?>" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-building"></i> Empresas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=empresas-tomadoras">
                                <i class="fas fa-briefcase"></i> Empresas Tomadoras
                            </a></li>
                            <li><a class="dropdown-item" href="?page=empresas-prestadoras">
                                <i class="fas fa-handshake"></i> Empresas Prestadoras
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- Serviços -->
                    <li class="nav-item">
                        <a class="nav-link <?= isset($activeMenu) && $activeMenu === 'servicos' ? 'active' : '' ?>" href="?page=servicos">
                            <i class="fas fa-list"></i> Serviços
                        </a>
                    </li>
                    
                    <!-- Contratos -->
                    <li class="nav-item">
                        <a class="nav-link <?= isset($activeMenu) && $activeMenu === 'contratos' ? 'active' : '' ?>" href="?page=contratos">
                            <i class="fas fa-file-contract"></i> Contratos
                        </a>
                    </li>
                    
                    <!-- Projetos -->
                    <li class="nav-item">
                        <a class="nav-link <?= isset($activeMenu) && $activeMenu === 'projetos' ? 'active' : '' ?>" href="?page=projetos">
                            <i class="fas fa-project-diagram"></i> Projetos
                        </a>
                    </li>
                    
                    <!-- Atividades -->
                    <li class="nav-item">
                        <a class="nav-link <?= isset($activeMenu) && $activeMenu === 'atividades' ? 'active' : '' ?>" href="?page=atividades">
                            <i class="fas fa-tasks"></i> Atividades
                        </a>
                    </li>
                    
                    <!-- Financeiro -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= isset($activeMenu) && in_array($activeMenu, ['pagamentos', 'custos', 'relatorios-financeiros']) ? 'active' : '' ?>" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-dollar-sign"></i> Financeiro
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=pagamentos">
                                <i class="fas fa-money-bill-wave"></i> Pagamentos
                            </a></li>
                            <li><a class="dropdown-item" href="?page=custos">
                                <i class="fas fa-calculator"></i> Custos
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?page=relatorios-financeiros">
                                <i class="fas fa-chart-line"></i> Relatórios
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- Relatórios -->
                    <li class="nav-item">
                        <a class="nav-link <?= isset($activeMenu) && $activeMenu === 'relatorios' ? 'active' : '' ?>" href="?page=relatorios">
                            <i class="fas fa-chart-bar"></i> Relatórios
                        </a>
                    </li>
                </ul>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> 
                            <?= $_SESSION['usuario_nome'] ?? 'Usuário' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="?page=perfil">
                                <i class="fas fa-user"></i> Meu Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="?page=configuracoes">
                                <i class="fas fa-cog"></i> Configurações
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="?page=logout">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumb -->
    <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
    <nav aria-label="breadcrumb" class="bg-light">
        <div class="container-fluid">
            <ol class="breadcrumb py-2 mb-0">
                <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i> Home</a></li>
                <?php foreach ($breadcrumb as $item): ?>
                    <?php if (isset($item['url'])): ?>
                        <li class="breadcrumb-item"><a href="<?= $item['url'] ?>"><?= $item['label'] ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?= $item['label'] ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="container-fluid py-4">
        <!-- Mensagens Flash -->
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['sucesso'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['erro'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['aviso'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['aviso'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['aviso']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> <?= $_SESSION['info'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erros']) && is_array($_SESSION['erros'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-circle"></i> Erros encontrados:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($_SESSION['erros'] as $erro): ?>
                        <li><?= $erro ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['erros']); ?>
        <?php endif; ?>