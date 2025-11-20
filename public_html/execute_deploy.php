<?php
/**
 * SPRINT 67 - EXECUTAR DEPLOY AUTOMATICAMENTE
 * Este arquivo executa o deploy quando acessado via HTTP
 */

// Autenticação
$validUser = 'clinfec';
$validPass = 'Cf2025api#';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $validUser || 
    $_SERVER['PHP_AUTH_PW'] !== $validPass) {
    header('WWW-Authenticate: Basic realm="Deploy Sprint 67"');
    header('HTTP/1.0 401 Unauthorized');
    die('Acesso negado');
}

header('Content-Type: text/plain; charset=UTF-8');

echo "════════════════════════════════════════════════════════════════\n";
echo "  SPRINT 67 - EXECUTANDO DEPLOY AUTOMÁTICO\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$baseDir = '/opt/webserver/sites/prestadores';

// Verificar diretório
if (!is_dir($baseDir)) {
    die("❌ ERRO: Diretório $baseDir não encontrado\n");
}

echo "✅ Diretório base: $baseDir\n\n";

// Criar backup
$backupDir = "$baseDir/backups/sprint67_" . date('Ymd_His');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
echo "📦 Backup criado: $backupDir\n";

// Backup arquivos
$files = [
    'src/Controllers/AuthController.php',
    'config/app.php'
];

foreach ($files as $file) {
    $fullPath = "$baseDir/$file";
    if (file_exists($fullPath)) {
        copy($fullPath, "$backupDir/" . basename($file));
        echo "  ✅ Backup: $file\n";
    }
}

echo "\n📥 Baixando arquivos do GitHub...\n";

// Baixar AuthController
$authUrl = 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/src/Controllers/AuthControllerDebug.php';
$authContent = @file_get_contents($authUrl);

if ($authContent && strlen($authContent) > 1000) {
    $tempFile = tempnam(sys_get_temp_dir(), 'auth_');
    file_put_contents($tempFile, $authContent);
    
    // Validar sintaxe
    exec("php -l $tempFile 2>&1", $output, $return);
    
    if ($return === 0) {
        $destFile = "$baseDir/src/Controllers/AuthController.php";
        if (file_put_contents($destFile, $authContent)) {
            chmod($destFile, 0644);
            echo "  ✅ AuthController.php atualizado (" . strlen($authContent) . " bytes)\n";
        } else {
            echo "  ❌ ERRO ao salvar AuthController.php\n";
        }
    } else {
        echo "  ❌ ERRO: Sintaxe inválida no AuthController.php\n";
    }
    
    unlink($tempFile);
} else {
    echo "  ❌ ERRO ao baixar AuthController.php\n";
}

// Baixar config/app.php
$configUrl = 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/config/app.php';
$configContent = @file_get_contents($configUrl);

if ($configContent && strlen($configContent) > 500) {
    $tempFile = tempnam(sys_get_temp_dir(), 'config_');
    file_put_contents($tempFile, $configContent);
    
    // Validar sintaxe
    exec("php -l $tempFile 2>&1", $output, $return);
    
    if ($return === 0) {
        $destFile = "$baseDir/config/app.php";
        if (file_put_contents($destFile, $configContent)) {
            chmod($destFile, 0644);
            echo "  ✅ config/app.php atualizado (" . strlen($configContent) . " bytes)\n";
        } else {
            echo "  ❌ ERRO ao salvar config/app.php\n";
        }
    } else {
        echo "  ❌ ERRO: Sintaxe inválida no config/app.php\n";
    }
    
    unlink($tempFile);
} else {
    echo "  ❌ ERRO ao baixar config/app.php\n";
}

echo "\n🔄 Limpando caches...\n";

// Limpar OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "  ✅ OPcache limpo\n";
} else {
    echo "  ⚠️  OPcache não disponível\n";
}

// Recarregar PHP-FPM
echo "  🔄 Recarregando PHP-FPM...\n";
exec('sudo systemctl reload php8.3-fpm-prestadores.service 2>&1', $output, $return);
if ($return === 0) {
    echo "  ✅ PHP-FPM recarregado\n";
} else {
    exec('sudo systemctl reload php8.3-fpm 2>&1', $output, $return);
    if ($return === 0) {
        echo "  ✅ PHP-FPM recarregado (pool padrão)\n";
    } else {
        echo "  ⚠️  Execute manualmente: systemctl reload php8.3-fpm\n";
    }
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "  ✅ DEPLOY CONCLUÍDO COM SUCESSO!\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "📋 Próximos passos:\n";
echo "  1. Testar login: https://prestadores.clinfec.com.br/?page=login\n";
echo "  2. Usar: master@clinfec.com.br / Master123!\n";
echo "  3. Verificar logs: tail -f $baseDir/logs/php-error.log\n";
echo "  4. REMOVER este arquivo após confirmar sucesso\n\n";

echo "⚠️  REMOVER: " . __FILE__ . "\n";
