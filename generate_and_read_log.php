<?php
/**
 * Gerar log de erro e ler o arquivo
 */
header('Content-Type: text/plain; charset=utf-8');

$logFile = __DIR__ . '/debug_errors.log';

// Limpar log anterior
if (file_exists($logFile)) {
    unlink($logFile);
}

echo "=== GERANDO E LENDO LOGS DE ERRO ===\n\n";

// Configurar error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logFile) {
    $log = sprintf("[%s] ERROR %d: %s in %s:%d\n", 
        date('Y-m-d H:i:s'), $errno, $errstr, $errfile, $errline);
    file_put_contents($logFile, $log, FILE_APPEND);
    return true; // Não propagar erro
});

set_exception_handler(function($e) use ($logFile) {
    $log = sprintf("[%s] EXCEPTION: %s\nFile: %s:%d\nTrace:\n%s\n\n", 
        date('Y-m-d H:i:s'), 
        $e->getMessage(), 
        $e->getFile(), 
        $e->getLine(),
        $e->getTraceAsString()
    );
    file_put_contents($logFile, $log, FILE_APPEND);
});

echo "[1] Loading autoloader...\n";
file_put_contents($logFile, "[STEP 1] Loading autoloader\n", FILE_APPEND);

try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader loaded\n";
    file_put_contents($logFile, "✅ Autoloader OK\n", FILE_APPEND);
} catch (\Throwable $e) {
    echo "❌ Autoloader failed: " . $e->getMessage() . "\n";
    file_put_contents($logFile, "❌ Autoloader ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}

echo "\n[2] Testing Database::getInstance()...\n";
file_put_contents($logFile, "\n[STEP 2] Testing Database\n", FILE_APPEND);

try {
    $db = \App\Database::getInstance();
    echo "✅ Database::getInstance() OK\n";
    echo "   Type: " . get_class($db) . "\n";
    file_put_contents($logFile, "✅ Database OK: " . get_class($db) . "\n", FILE_APPEND);
    
    // Test query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    echo "✅ Query test: " . json_encode($result) . "\n";
    file_put_contents($logFile, "✅ Query OK\n", FILE_APPEND);
} catch (\Throwable $e) {
    echo "❌ Database failed: " . $e->getMessage() . "\n";
    file_put_contents($logFile, "❌ Database ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
}

echo "\n[3] Testing Projeto Model...\n";
file_put_contents($logFile, "\n[STEP 3] Testing Projeto Model\n", FILE_APPEND);

try {
    file_put_contents($logFile, "  Creating Projeto instance...\n", FILE_APPEND);
    $projeto = new \App\Models\Projeto();
    echo "✅ Projeto instantiated\n";
    file_put_contents($logFile, "✅ Projeto instantiated\n", FILE_APPEND);
    
    file_put_contents($logFile, "  Calling all() method...\n", FILE_APPEND);
    $result = $projeto->all([], 1, 1);
    echo "✅ all() returned: " . count($result) . " results\n";
    file_put_contents($logFile, "✅ all() returned " . count($result) . " results\n", FILE_APPEND);
} catch (\Throwable $e) {
    echo "❌ Projeto failed: " . $e->getMessage() . "\n";
    $errorMsg = sprintf("❌ Projeto ERROR:\n  Message: %s\n  File: %s:%d\n  Trace:\n%s\n",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
}

echo "\n[4] Testing Atividade Model...\n";
file_put_contents($logFile, "\n[STEP 4] Testing Atividade Model\n", FILE_APPEND);

try {
    $atividade = new \App\Models\Atividade();
    echo "✅ Atividade instantiated\n";
    file_put_contents($logFile, "✅ Atividade instantiated\n", FILE_APPEND);
    
    $result = $atividade->all([], 1, 1);
    echo "✅ all() returned: " . count($result) . " results\n";
    file_put_contents($logFile, "✅ Atividade all() returned " . count($result) . " results\n", FILE_APPEND);
} catch (\Throwable $e) {
    echo "❌ Atividade failed: " . $e->getMessage() . "\n";
    $errorMsg = sprintf("❌ Atividade ERROR:\n  Message: %s\n  File: %s:%d\n",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
}

echo "\n[5] Testing NotaFiscal Model...\n";
file_put_contents($logFile, "\n[STEP 5] Testing NotaFiscal Model\n", FILE_APPEND);

try {
    $nota = new \App\Models\NotaFiscal();
    echo "✅ NotaFiscal instantiated\n";
    file_put_contents($logFile, "✅ NotaFiscal instantiated\n", FILE_APPEND);
    
    $result = $nota->all([], 1, 1);
    echo "✅ all() returned: " . count($result) . " results\n";
    file_put_contents($logFile, "✅ NotaFiscal all() returned " . count($result) . " results\n", FILE_APPEND);
} catch (\Throwable $e) {
    echo "❌ NotaFiscal failed: " . $e->getMessage() . "\n";
    $errorMsg = sprintf("❌ NotaFiscal ERROR:\n  Message: %s\n  File: %s:%d\n",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
}

echo "\n=== TESTE COMPLETO ===\n\n";
file_put_contents($logFile, "\n=== TESTE COMPLETO ===\n", FILE_APPEND);

// Ler e exibir o log
echo "=== CONTEÚDO DO LOG (debug_errors.log) ===\n\n";
if (file_exists($logFile)) {
    echo file_get_contents($logFile);
} else {
    echo "❌ Arquivo de log não foi criado!\n";
}

echo "\n=== FIM ===\n";
