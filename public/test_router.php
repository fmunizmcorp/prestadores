<?php
/**
 * Test Router and Autoloader
 * URL: prestadores.clinfec.com.br/?page=test-router
 * Purpose: Verify routing and autoloading work
 */

// Must be accessed via router (index.php)
if (basename($_SERVER['PHP_SELF']) === 'test_router.php') {
    die('❌ ERRO: Este arquivo deve ser acessado via router (use ?page=test-router)');
}

echo "✅ OK - Router funcionando!\n";
echo "Este teste foi acessado via index.php\n";
