<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE DIRETO DOS MODELS ===\n\n";

// Database
require_once 'config/database.php';

// Test NotaFiscal
echo "[1/3] NotaFiscal Model\n";
try {
    require_once 'src/Models/NotaFiscal.php';
    $nf = new App\Models\NotaFiscal($pdo);
    $count = $nf->count();
    echo "  ✅ NotaFiscal OK - Count: $count\n\n";
} catch (Exception $e) {
    echo "  ❌ ERRO: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

// Test Projeto
echo "[2/3] Projeto Model\n";
try {
    require_once 'src/Models/Projeto.php';
    $proj = new App\Models\Projeto($pdo);
    $count = $proj->count();
    echo "  ✅ Projeto OK - Count: $count\n\n";
} catch (Exception $e) {
    echo "  ❌ ERRO: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

// Test Atividade
echo "[3/3] Atividade Model\n";
try {
    require_once 'src/Models/Atividade.php';
    $ativ = new App\Models\Atividade($pdo);
    $count = $ativ->count();
    echo "  ✅ Atividade OK - Count: $count\n\n";
} catch (Exception $e) {
    echo "  ❌ ERRO: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

echo "=== FIM DOS TESTES ===\n";
