<?php
/**
 * Arquivo de redirecionamento raiz
 * Redireciona para public/index.php
 */

// Redirecionar para public/index.php
if (file_exists(__DIR__ . '/public/index.php')) {
    require_once __DIR__ . '/public/index.php';
} else {
    // Se não encontrar, mostrar erro
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Erro</title></head><body>";
    echo "<h1>Erro de Configuração</h1>";
    echo "<p>Arquivo public/index.php não encontrado.</p>";
    echo "<p>Estrutura atual: " . __DIR__ . "</p>";
    echo "</body></html>";
}
