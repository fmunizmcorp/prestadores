<?php
/**
 * REDIRECT para RAIZ - Hostinger Compartilhada
 * Este arquivo fica em /public_html/prestadores/public/index.php
 * e redireciona TUDO para a raiz /public_html/prestadores/
 */

// Pegar a URL completa
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// Remover /public/ da URL
$new_uri = str_replace('/public/', '/', $request_uri);
$new_uri = str_replace('/public', '/', $new_uri);

// Se ficou vazio, usar /
if (empty($new_uri) || $new_uri === '/') {
    $new_uri = '/';
}

// Redirecionar
header("Location: $new_uri", true, 301);
exit;
