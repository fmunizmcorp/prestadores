<?php
/**
 * Test Basic PHP Execution
 * URL: prestadores.clinfec.com.br/test_basic.php
 * Purpose: Verify PHP is executing correctly
 */

header('Content-Type: text/plain; charset=utf-8');
echo "✅ OK - PHP está executando!\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
