<?php
/**
 * DEBUG SPRINT 67 - Ver logs e estado do sistema
 * REMOVER APÓS DEBUG!
 */

// Verificar autenticação básica
$validUser = 'clinfec';
$validPass = 'Cf2025api#';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $validUser || 
    $_SERVER['PHP_AUTH_PW'] !== $validPass) {
    header('WWW-Authenticate: Basic realm="Sprint 67 Debug"');
    header('HTTP/1.0 401 Unauthorized');
    die('Acesso negado');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sprint 67 - Debug</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        .section { background: #000; padding: 15px; margin: 10px 0; border: 1px solid #0f0; }
        .title { color: #ff0; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .log { white-space: pre-wrap; max-height: 500px; overflow-y: auto; font-size: 12px; }
        .error { color: #f00; }
        .success { color: #0f0; }
        .warning { color: #fa0; }
    </style>
</head>
<body>
    <h1>🔍 SPRINT 67 - SISTEMA DE DEBUG</h1>
    
    <div class="section">
        <div class="title">📊 INFORMAÇÕES DO SISTEMA</div>
        <div>
            <strong>PHP Version:</strong> <?= PHP_VERSION ?><br>
            <strong>Server Time:</strong> <?= date('Y-m-d H:i:s') ?><br>
            <strong>Session Status:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? '<span class="success">ATIVA</span>' : '<span class="error">INATIVA</span>' ?><br>
            <?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
            <strong>Session ID:</strong> <?= session_id() ?><br>
            <strong>Session Data:</strong> <?= !empty($_SESSION) ? '<pre>'.print_r($_SESSION, true).'</pre>' : '<span class="warning">Vazia</span>' ?>
        </div>
    </div>
    
    <div class="section">
        <div class="title">📁 VERIFICAR ARQUIVOS</div>
        <div>
            <?php
            $files = [
                '/opt/webserver/sites/prestadores/config/app.php' => 'Config Principal',
                '/opt/webserver/sites/prestadores/src/Controllers/AuthController.php' => 'AuthController',
                '/opt/webserver/sites/prestadores/public_html/index.php' => 'Index.php (Router)',
            ];
            
            foreach ($files as $file => $desc) {
                $exists = file_exists($file);
                $size = $exists ? filesize($file) : 0;
                $modified = $exists ? date('Y-m-d H:i:s', filemtime($file)) : 'N/A';
                $color = $exists ? 'success' : 'error';
                
                echo "<strong>$desc:</strong> <span class='$color'>" . ($exists ? "✅ Existe" : "❌ Não existe") . "</span><br>";
                if ($exists) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;Tamanho: " . number_format($size) . " bytes | Modificado: $modified<br>";
                }
            }
            ?>
        </div>
    </div>
    
    <div class="section">
        <div class="title">🔑 TESTAR USUÁRIO MASTER</div>
        <div>
            <?php
            try {
                require_once '/opt/webserver/sites/prestadores/config/database.php';
                require_once '/opt/webserver/sites/prestadores/src/Database/Database.php';
                
                $db = \App\Database\Database::getInstance();
                $pdo = $db->getConnection();
                
                $stmt = $pdo->prepare("SELECT id, nome, email, role, status FROM usuarios WHERE email = ?");
                $stmt->execute(['master@clinfec.com.br']);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo "<span class='success'>✅ Usuário encontrado no banco</span><br>";
                    echo "<pre>" . print_r($user, true) . "</pre>";
                } else {
                    echo "<span class='error'>❌ Usuário NÃO encontrado</span><br>";
                }
                
                // Verificar senha
                $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE email = ?");
                $stmt->execute(['master@clinfec.com.br']);
                $hash = $stmt->fetchColumn();
                
                if ($hash) {
                    $senhaTest = 'Master123!';
                    $match = password_verify($senhaTest, $hash);
                    echo "<br><strong>Verificação de senha ('$senhaTest'):</strong> ";
                    echo $match ? "<span class='success'>✅ CORRETA</span>" : "<span class='error'>❌ INCORRETA</span>";
                    echo "<br><strong>Hash no banco:</strong> $hash<br>";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ ERRO: " . $e->getMessage() . "</span>";
            }
            ?>
        </div>
    </div>
    
    <div class="section">
        <div class="title">📝 ÚLTIMAS 100 LINHAS DO LOG (php-error.log)</div>
        <div class="log">
            <?php
            $logFile = '/opt/webserver/sites/prestadores/logs/php-error.log';
            if (file_exists($logFile)) {
                $lines = file($logFile);
                $lastLines = array_slice($lines, -100);
                foreach ($lastLines as $line) {
                    $class = '';
                    if (strpos($line, 'ERROR') !== false || strpos($line, 'FAILED') !== false) {
                        $class = 'error';
                    } elseif (strpos($line, 'SUCCESS') !== false || strpos($line, '✅') !== false) {
                        $class = 'success';
                    } elseif (strpos($line, 'Warning') !== false || strpos($line, '⚠') !== false) {
                        $class = 'warning';
                    }
                    echo "<span class='$class'>" . htmlspecialchars($line) . "</span>";
                }
            } else {
                echo "<span class='error'>❌ Arquivo de log não encontrado</span>";
            }
            ?>
        </div>
    </div>
    
    <div class="section">
        <div class="title">⚙️ CONFIGURAÇÃO RECAPTCHA</div>
        <div>
            <?php
            $config = require '/opt/webserver/sites/prestadores/config/app.php';
            echo "<pre>";
            print_r($config['recaptcha'] ?? 'NÃO CONFIGURADO');
            echo "</pre>";
            ?>
        </div>
    </div>
    
    <div class="section">
        <div class="title">🔄 LIMPAR CACHE E RECARREGAR</div>
        <div>
            <form method="post">
                <button type="submit" name="action" value="clear_cache">🗑️ Limpar OPcache</button>
                <button type="submit" name="action" value="reload_php">🔄 Reload PHP-FPM</button>
            </form>
            <?php
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'clear_cache') {
                    if (function_exists('opcache_reset')) {
                        opcache_reset();
                        echo "<span class='success'>✅ OPcache limpo</span>";
                    } else {
                        echo "<span class='warning'>⚠️ OPcache não disponível</span>";
                    }
                } elseif ($_POST['action'] === 'reload_php') {
                    exec('sudo systemctl reload php8.3-fpm-prestadores.service 2>&1', $output, $return);
                    if ($return === 0) {
                        echo "<span class='success'>✅ PHP-FPM recarregado</span>";
                    } else {
                        echo "<span class='error'>❌ Erro ao recarregar: " . implode("\n", $output) . "</span>";
                    }
                }
            }
            ?>
        </div>
    </div>
    
    <hr>
    <p style="color: #f00; text-align: center;">
        ⚠️ REMOVER ESTE ARQUIVO APÓS O DEBUG! ⚠️<br>
        <small>Arquivo: <?= __FILE__ ?></small>
    </p>
</body>
</html>
