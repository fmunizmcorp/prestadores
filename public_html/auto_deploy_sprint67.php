<?php
/**
 * SPRINT 67 - AUTO DEPLOY VIA HTTP
 * 
 * Este arquivo pode ser acessado via navegador para fazer deploy automático
 * URL: https://prestadores.clinfec.com.br/auto_deploy_sprint67.php
 * 
 * ATENÇÃO: Remover este arquivo após o deploy!
 */

// Segurança: Autenticação básica HTTP
$validUser = 'clinfec';
$validPass = 'Cf2025api#';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $validUser || 
    $_SERVER['PHP_AUTH_PW'] !== $validPass) {
    header('WWW-Authenticate: Basic realm="Sprint 67 Auto Deploy"');
    header('HTTP/1.0 401 Unauthorized');
    die('Acesso negado');
}

// Configurações
define('BASE_DIR', '/opt/webserver/sites/prestadores');
define('GITHUB_BRANCH', 'genspark_ai_developer');
define('GITHUB_RAW', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/' . GITHUB_BRANCH);

// Headers para não fazer cache
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sprint 67 - Auto Deploy</title>
    <style>
        body { 
            font-family: 'Courier New', monospace; 
            background: #000; 
            color: #0f0; 
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #ff0; text-align: center; border-bottom: 2px solid #0f0; padding-bottom: 10px; }
        .section { 
            background: #111; 
            padding: 15px; 
            margin: 15px 0; 
            border: 1px solid #0f0; 
            border-radius: 5px;
        }
        .section-title { 
            color: #ff0; 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 10px; 
        }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #fa0; }
        .info { color: #0af; }
        pre { 
            background: #000; 
            padding: 10px; 
            border: 1px solid #333; 
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .button { 
            background: #0a0; 
            color: #000; 
            padding: 10px 20px; 
            border: none; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            margin: 5px;
            border-radius: 3px;
        }
        .button:hover { background: #0f0; }
        .log-line { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 SPRINT 67 - AUTO DEPLOY</h1>
        
        <?php
        
        function logMessage($message, $type = 'info') {
            $colors = [
                'success' => 'success',
                'error' => 'error',
                'warning' => 'warning',
                'info' => 'info'
            ];
            $class = $colors[$type] ?? 'info';
            echo "<div class='log-line $class'>" . htmlspecialchars($message) . "</div>";
            flush();
            ob_flush();
        }
        
        // Verificar se foi solicitado o deploy
        if (isset($_GET['action']) && $_GET['action'] === 'deploy') {
            
            echo "<div class='section'>";
            echo "<div class='section-title'>📝 LOG DE DEPLOY</div>";
            echo "<pre>";
            
            ob_start();
            
            logMessage("════════════════════════════════════════════════════════════════", 'info');
            logMessage("  INICIANDO DEPLOY - " . date('Y-m-d H:i:s'), 'info');
            logMessage("════════════════════════════════════════════════════════════════", 'info');
            logMessage("", 'info');
            
            // Verificar se o diretório existe
            if (!is_dir(BASE_DIR)) {
                logMessage("❌ ERRO: Diretório " . BASE_DIR . " não encontrado", 'error');
                echo "</pre></div>";
                exit;
            }
            
            logMessage("✅ Diretório base encontrado: " . BASE_DIR, 'success');
            logMessage("", 'info');
            
            // Criar diretório de backup
            $backupDir = BASE_DIR . '/backups/sprint67_' . date('Ymd_His');
            logMessage("📦 Criando backup em: $backupDir", 'info');
            
            if (!is_dir($backupDir)) {
                if (!mkdir($backupDir, 0755, true)) {
                    logMessage("❌ ERRO ao criar diretório de backup", 'error');
                } else {
                    logMessage("✅ Diretório de backup criado", 'success');
                }
            }
            
            // Backup dos arquivos atuais
            $filesToBackup = [
                'src/Controllers/AuthController.php',
                'config/app.php'
            ];
            
            foreach ($filesToBackup as $file) {
                $fullPath = BASE_DIR . '/' . $file;
                if (file_exists($fullPath)) {
                    $backupFile = $backupDir . '/' . basename($file);
                    if (copy($fullPath, $backupFile)) {
                        logMessage("  ✅ Backup: $file", 'success');
                    } else {
                        logMessage("  ⚠️  Falha ao fazer backup de: $file", 'warning');
                    }
                } else {
                    logMessage("  ⚠️  Arquivo não encontrado (será criado): $file", 'warning');
                }
            }
            
            logMessage("", 'info');
            logMessage("📥 Baixando arquivos do GitHub (branch: " . GITHUB_BRANCH . ")...", 'info');
            
            // Baixar e atualizar AuthController.php
            $authControllerUrl = GITHUB_RAW . '/src/Controllers/AuthControllerDebug.php';
            $authControllerDest = BASE_DIR . '/src/Controllers/AuthController.php';
            
            logMessage("  → Baixando AuthController.php...", 'info');
            $authControllerContent = @file_get_contents($authControllerUrl);
            
            if ($authControllerContent !== false && strlen($authControllerContent) > 1000) {
                // Verificar sintaxe PHP
                $tempFile = tempnam(sys_get_temp_dir(), 'auth_');
                file_put_contents($tempFile, $authControllerContent);
                
                $syntaxCheck = exec("php -l $tempFile 2>&1", $output, $return);
                
                if ($return === 0) {
                    if (file_put_contents($authControllerDest, $authControllerContent)) {
                        logMessage("    ✅ AuthController.php atualizado (" . strlen($authControllerContent) . " bytes)", 'success');
                        chmod($authControllerDest, 0644);
                    } else {
                        logMessage("    ❌ ERRO ao salvar AuthController.php", 'error');
                    }
                } else {
                    logMessage("    ❌ ERRO: Sintaxe PHP inválida", 'error');
                    logMessage("    " . implode("\n    ", $output), 'error');
                }
                
                unlink($tempFile);
            } else {
                logMessage("    ❌ ERRO ao baixar AuthController.php", 'error');
            }
            
            // Baixar e atualizar config/app.php
            $configUrl = GITHUB_RAW . '/config/app.php';
            $configDest = BASE_DIR . '/config/app.php';
            
            logMessage("  → Baixando app.php...", 'info');
            $configContent = @file_get_contents($configUrl);
            
            if ($configContent !== false && strlen($configContent) > 500) {
                // Verificar sintaxe PHP
                $tempFile = tempnam(sys_get_temp_dir(), 'config_');
                file_put_contents($tempFile, $configContent);
                
                $syntaxCheck = exec("php -l $tempFile 2>&1", $output, $return);
                
                if ($return === 0) {
                    if (file_put_contents($configDest, $configContent)) {
                        logMessage("    ✅ app.php atualizado (" . strlen($configContent) . " bytes)", 'success');
                        chmod($configDest, 0644);
                    } else {
                        logMessage("    ❌ ERRO ao salvar app.php", 'error');
                    }
                } else {
                    logMessage("    ❌ ERRO: Sintaxe PHP inválida", 'error');
                }
                
                unlink($tempFile);
            } else {
                logMessage("    ❌ ERRO ao baixar app.php", 'error');
            }
            
            logMessage("", 'info');
            logMessage("🗑️  Limpando caches...", 'info');
            
            // Limpar OPcache
            if (function_exists('opcache_reset')) {
                if (opcache_reset()) {
                    logMessage("  ✅ OPcache limpo", 'success');
                } else {
                    logMessage("  ⚠️  Falha ao limpar OPcache", 'warning');
                }
            } else {
                logMessage("  ⚠️  OPcache não disponível", 'warning');
            }
            
            // Recarregar PHP-FPM
            logMessage("  🔄 Recarregando PHP-FPM...", 'info');
            $reloadCmd = "sudo systemctl reload php8.3-fpm-prestadores.service 2>&1";
            exec($reloadCmd, $reloadOutput, $reloadReturn);
            
            if ($reloadReturn === 0) {
                logMessage("    ✅ PHP-FPM recarregado", 'success');
            } else {
                logMessage("    ⚠️  Não foi possível recarregar PHP-FPM automaticamente", 'warning');
                logMessage("    Execute manualmente: systemctl reload php8.3-fpm", 'warning');
            }
            
            logMessage("", 'info');
            logMessage("════════════════════════════════════════════════════════════════", 'info');
            logMessage("  ✅ DEPLOY CONCLUÍDO - " . date('Y-m-d H:i:s'), 'success');
            logMessage("════════════════════════════════════════════════════════════════", 'info');
            
            echo "</pre></div>";
            
            // Instruções de teste
            echo "<div class='section'>";
            echo "<div class='section-title'>🧪 PRÓXIMOS PASSOS</div>";
            echo "<ol>";
            echo "<li>Aguarde 2-3 segundos para o PHP-FPM recarregar completamente</li>";
            echo "<li>Teste o login com os usuários abaixo</li>";
            echo "<li>Verifique os logs em caso de erro</li>";
            echo "<li><strong class='error'>REMOVA este arquivo após confirmar que tudo funciona!</strong></li>";
            echo "</ol>";
            echo "</div>";
            
            // Usuários de teste
            echo "<div class='section'>";
            echo "<div class='section-title'>👥 USUÁRIOS DE TESTE</div>";
            echo "<table style='width:100%; color:#0f0;'>";
            echo "<tr><th>Email</th><th>Senha</th><th>Role</th></tr>";
            echo "<tr><td>master@clinfec.com.br</td><td>Master123!</td><td>master</td></tr>";
            echo "<tr><td>admin@clinfec.com.br</td><td>Admin123!</td><td>admin</td></tr>";
            echo "<tr><td>gestor@clinfec.com.br</td><td>Gestor123!</td><td>gestor</td></tr>";
            echo "<tr><td>usuario@clinfec.com.br</td><td>Usuario123!</td><td>usuario</td></tr>";
            echo "</table>";
            echo "</div>";
            
            // Botão para testar login
            echo "<div class='section'>";
            echo "<div class='section-title'>🔗 AÇÕES</div>";
            echo "<a href='/?page=login' target='_blank'><button class='button'>Testar Login</button></a>";
            echo "<a href='/auto_deploy_sprint67.php'><button class='button' style='background:#a00;'>Voltar</button></a>";
            echo "</div>";
            
        } else {
            // Exibir tela inicial
            echo "<div class='section'>";
            echo "<div class='section-title'>📋 INFORMAÇÕES</div>";
            echo "<p>Este script irá:</p>";
            echo "<ul>";
            echo "<li>✅ Criar backup automático dos arquivos atuais</li>";
            echo "<li>✅ Baixar <code>AuthController.php</code> corrigido do GitHub</li>";
            echo "<li>✅ Baixar <code>config/app.php</code> com reCAPTCHA desabilitado</li>";
            echo "<li>✅ Validar sintaxe PHP dos arquivos</li>";
            echo "<li>✅ Ajustar permissões</li>";
            echo "<li>✅ Limpar OPcache</li>";
            echo "<li>✅ Recarregar PHP-FPM</li>";
            echo "</ul>";
            echo "<p class='warning'><strong>⚠️  ATENÇÃO:</strong> Este processo irá substituir os arquivos atuais. Backups serão criados automaticamente.</p>";
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<div class='section-title'>🚀 INICIAR DEPLOY</div>";
            echo "<p>Clique no botão abaixo para iniciar o deploy automático:</p>";
            echo "<a href='?action=deploy'><button class='button'>🚀 EXECUTAR DEPLOY AGORA</button></a>";
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<div class='section-title'>📁 ARQUIVOS QUE SERÃO ATUALIZADOS</div>";
            echo "<ul>";
            echo "<li><code>/opt/webserver/sites/prestadores/src/Controllers/AuthController.php</code></li>";
            echo "<li><code>/opt/webserver/sites/prestadores/config/app.php</code></li>";
            echo "</ul>";
            echo "</div>";
        }
        
        ?>
        
        <div class="section" style="text-align:center; border-color:#f00;">
            <p class="error"><strong>⚠️  IMPORTANTE: REMOVER ESTE ARQUIVO APÓS O DEPLOY! ⚠️</strong></p>
            <p class="error">Arquivo: <?= __FILE__ ?></p>
            <p style="color:#999;">rm <?= __FILE__ ?></p>
        </div>
        
    </div>
</body>
</html>
