<?php
/**
 * Teste Básico - Hostinger Compartilhada
 * URL: https://prestadores.clinfec.com.br/test.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "✅ TESTE BÁSICO - OK!\n\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename: " . __FILE__ . "\n";
echo "Current Directory: " . __DIR__ . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

echo "Verificando estrutura:\n";
echo "- config/ existe? " . (is_dir(__DIR__ . '/config') ? 'SIM' : 'NÃO') . "\n";
echo "- src/ existe? " . (is_dir(__DIR__ . '/src') ? 'SIM' : 'NÃO') . "\n";
echo "- assets/ existe? " . (is_dir(__DIR__ . '/assets') ? 'SIM' : 'NÃO') . "\n";
echo "- index.php existe? " . (file_exists(__DIR__ . '/index.php') ? 'SIM' : 'NÃO') . "\n";
echo "- .htaccess existe? " . (file_exists(__DIR__ . '/.htaccess') ? 'SIM' : 'NÃO') . "\n\n";

echo "✅ Sistema pronto para funcionar!\n";
