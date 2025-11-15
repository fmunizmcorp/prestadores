<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO COMPLETO ===\n\n";

echo "1. INFORMAÇÕES DO SERVIDOR:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "   Script Filename: " . __FILE__ . "\n";
echo "   Current Directory: " . __DIR__ . "\n";
echo "   Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "\n";
echo "   Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n\n";

echo "2. ESTRUTURA DE PASTAS:\n";
$dirs = ['config', 'src', 'src/controllers', 'src/models', 'src/views', 'assets', 'assets/css', 'assets/js'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    echo "   " . $dir . ": " . (is_dir($path) ? "✅ EXISTE" : "❌ NÃO EXISTE") . "\n";
}

echo "\n3. ARQUIVOS CRÍTICOS:\n";
$files = ['index.php', '.htaccess', 'test.php', 'config/database.php', 'config/app.php'];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "   ✅ $file (" . filesize($path) . " bytes)\n";
    } else {
        echo "   ❌ $file NÃO EXISTE\n";
    }
}

echo "\n4. CONFIGURAÇÕES PHP:\n";
echo "   display_errors: " . ini_get('display_errors') . "\n";
echo "   error_reporting: " . ini_get('error_reporting') . "\n";
echo "   opcache.enable: " . ini_get('opcache.enable') . "\n";
echo "   allow_url_fopen: " . ini_get('allow_url_fopen') . "\n";

echo "\n5. TESTE DE REQUIRE:\n";
try {
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
        echo "   ✅ config/database.php carregado com sucesso!\n";
    } else {
        echo "   ❌ config/database.php não encontrado!\n";
    }
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
