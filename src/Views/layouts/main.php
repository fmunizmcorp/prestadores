<?php
/**
 * Layout Principal
 * Inclui header, conteúdo da view e footer
 */

// Garantir que $view e $data existem
$view = $view ?? 'dashboard/index';
$pageTitle = $pageTitle ?? 'Dashboard';

// Incluir header
require __DIR__ . '/header.php';

// Incluir a view específica
$viewPath = ROOT_PATH . '/src/Views/' . $view . '.php';
if (file_exists($viewPath)) {
    require $viewPath;
} else {
    echo '<div class="alert alert-danger">';
    echo '<h4>⚠️ View não encontrada</h4>';
    echo '<p>Caminho: ' . htmlspecialchars($viewPath) . '</p>';
    echo '</div>';
}

// Incluir footer
require __DIR__ . '/footer.php';
