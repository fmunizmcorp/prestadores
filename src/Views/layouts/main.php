<?php
/**
 * Layout Principal (Main Wrapper)
 * 
 * Este arquivo serve como wrapper principal para todas as views renderizadas
 * pelo BaseController::render(). Ele inclui a view específica solicitada.
 * 
 * SPRINT 67: Criado para suportar arquitetura MVC com BaseController
 * 
 * Variáveis disponíveis:
 * - $view: Nome da view a ser incluída (ex: 'dashboard/index')
 * - Todas as variáveis extraídas via extract($data) no BaseController
 */

// Construir caminho da view
$viewPath = ROOT_PATH . '/src/Views/' . $view . '.php';

// Verificar se a view existe
if (!file_exists($viewPath)) {
    error_log("[SPRINT 67] View não encontrada: {$viewPath}");
    http_response_code(404);
    echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Erro 404 - Página não encontrada</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-6'>
                <div class='card shadow'>
                    <div class='card-body text-center p-5'>
                        <h1 class='text-danger mb-3'>
                            <i class='fas fa-exclamation-triangle'></i> 404
                        </h1>
                        <h3 class='mb-3'>Página não encontrada</h3>
                        <p class='text-muted'>A página que você está procurando não existe ou foi movida.</p>
                        <a href='/' class='btn btn-primary mt-3'>
                            <i class='fas fa-home'></i> Voltar para o Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
    exit;
}

// Debug log
error_log("[SPRINT 67] Renderizando view: {$view} | Path: {$viewPath}");

// Incluir a view
require $viewPath;
