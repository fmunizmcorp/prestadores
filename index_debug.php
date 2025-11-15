<?php
/**
 * INDEX WRAPPER - Sprint 30 DEBUG
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- DEBUG: Wrapper iniciado -->\n";

$target_file = __DIR__ . '/public/index_unique_1763123588.php';

echo "<!-- DEBUG: Tentando carregar: $target_file -->\n";
echo "<!-- DEBUG: Arquivo existe? " . (file_exists($target_file) ? 'SIM' : 'NAO') . " -->\n";

if (!file_exists($target_file)) {
    die("ERRO: Arquivo não encontrado: $target_file");
}

echo "<!-- DEBUG: Carregando arquivo... -->\n";

require_once $target_file;

echo "<!-- DEBUG: Arquivo carregado com sucesso -->\n";
