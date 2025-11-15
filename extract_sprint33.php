<?php
/**
 * EXTRACTOR SPRINT 33 - DEPLOY COMPLETO
 * Este script extrai o pacote deploy_sprint33_complete.tar.gz
 * e realiza todas as configurações necessárias
 */

set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$startTime = microtime(true);
$log = [];

function logMessage($message) {
    global $log;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message";
    $log[] = $logEntry;
    echo "<p>$logEntry</p>\n";
    flush();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sprint 33 Deploy - Extração Automática</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Sprint 33 - Deploy Automático</h1>
            <p>Sistema de Prestadores Clinfec</p>
        </div>
        <div class="content">
            <?php
            
            // PASSO 1: Verificar arquivo
            echo '<div class="step">';
            echo '<h3>📦 Passo 1: Verificando Arquivo de Deploy</h3>';
            
            $packageFile = __DIR__ . '/deploy_sprint33_complete.tar.gz';
            
            if (file_exists($packageFile)) {
                $size = filesize($packageFile);
                $sizeMB = round($size / 1024 / 1024, 2);
                logMessage("✅ Arquivo encontrado: deploy_sprint33_complete.tar.gz");
                logMessage("✅ Tamanho: $sizeMB MB");
                echo '</div>';
            } else {
                logMessage("❌ ERRO: Arquivo deploy_sprint33_complete.tar.gz não encontrado!");
                echo '</div>';
                die('<div class="step error"><h3>❌ ERRO FATAL</h3><p>Arquivo de deploy não encontrado. Upload necessário.</p></div></div></body></html>');
            }
            
            // PASSO 2: Criar backup
            echo '<div class="step">';
            echo '<h3>💾 Passo 2: Criando Backup</h3>';
            
            $backupDir = __DIR__ . '/backup_' . date('Ymd_His');
            
            if (!is_dir($backupDir)) {
                if (mkdir($backupDir, 0755, true)) {
                    logMessage("✅ Diretório de backup criado: " . basename($backupDir));
                } else {
                    logMessage("⚠️ Aviso: Não foi possível criar backup (continuando...)");
                }
            }
            
            echo '</div>';
            
            // PASSO 3: Criar diretório prestadores
            echo '<div class="step">';
            echo '<h3>📁 Passo 3: Preparando Diretório</h3>';
            
            $prestadoresDir = __DIR__ . '/prestadores';
            
            if (!is_dir($prestadoresDir)) {
                if (mkdir($prestadoresDir, 0755, true)) {
                    logMessage("✅ Diretório /prestadores criado");
                } else {
                    logMessage("❌ ERRO: Não foi possível criar diretório /prestadores");
                    echo '</div>';
                    die('<div class="step error"><h3>❌ ERRO FATAL</h3><p>Não foi possível criar diretório.</p></div></div></body></html>');
                }
            } else {
                logMessage("✅ Diretório /prestadores já existe");
            }
            
            echo '</div>';
            
            // PASSO 4: Extrair arquivos
            echo '<div class="step">';
            echo '<h3>📤 Passo 4: Extraindo Arquivos</h3>';
            
            try {
                $phar = new PharData($packageFile);
                $phar->extractTo($prestadoresDir, null, true);
                logMessage("✅ Arquivos extraídos com sucesso!");
                
                // Contar arquivos extraídos
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($prestadoresDir),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                $fileCount = 0;
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $fileCount++;
                    }
                }
                
                logMessage("✅ Total de arquivos extraídos: $fileCount");
                
            } catch (Exception $e) {
                logMessage("❌ ERRO na extração: " . $e->getMessage());
                echo '</div>';
                die('<div class="step error"><h3>❌ ERRO FATAL</h3><p>' . $e->getMessage() . '</p></div></div></body></html>');
            }
            
            echo '</div>';
            
            // PASSO 5: Ajustar permissões
            echo '<div class="step">';
            echo '<h3>🔐 Passo 5: Ajustando Permissões</h3>';
            
            $dirsToChmod = [
                $prestadoresDir . '/public/uploads',
                $prestadoresDir . '/public/uploads/logos',
                $prestadoresDir . '/public/uploads/documentos',
                $prestadoresDir . '/public/uploads/contratos',
                $prestadoresDir . '/logs',
                $prestadoresDir . '/cache'
            ];
            
            foreach ($dirsToChmod as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                    logMessage("✅ Criado e configurado: " . basename($dir));
                } else {
                    chmod($dir, 0777);
                    logMessage("✅ Permissões ajustadas: " . basename($dir));
                }
            }
            
            echo '</div>';
            
            // PASSO 6: Executar ações do Sprint 31
            echo '<div class="step">';
            echo '<h3>🔧 Passo 6: Aplicando Correções Sprint 31</h3>';
            
            // 6.1: Renomear index.php antigo (se existir)
            $oldIndex = $prestadoresDir . '/public/index.php';
            $backupIndex = $prestadoresDir . '/public/index.php.OLD_CACHE';
            
            if (file_exists($oldIndex) && !file_exists($backupIndex)) {
                if (rename($oldIndex, $backupIndex)) {
                    logMessage("✅ index.php antigo renomeado para backup");
                }
            }
            
            // 6.2: Usar index_sprint31.php se existir
            $sprint31Index = $prestadoresDir . '/public/index_sprint31.php';
            if (file_exists($sprint31Index) && !file_exists($oldIndex)) {
                if (copy($sprint31Index, $oldIndex)) {
                    logMessage("✅ index_sprint31.php copiado para index.php");
                }
            }
            
            // 6.3: Remover DatabaseMigration.php se existir
            $migrationFile = $prestadoresDir . '/src/DatabaseMigration.php';
            if (file_exists($migrationFile)) {
                $backupMigration = $prestadoresDir . '/src/DatabaseMigration.php.DISABLED_' . date('Ymd_His');
                if (rename($migrationFile, $backupMigration)) {
                    logMessage("✅ DatabaseMigration.php desabilitado");
                }
            } else {
                logMessage("ℹ️ DatabaseMigration.php não encontrado (já removido)");
            }
            
            // 6.4: Usar .htaccess_nocache se existir
            $oldHtaccess = $prestadoresDir . '/public/.htaccess';
            $nocacheHtaccess = $prestadoresDir . '/public/.htaccess_nocache';
            
            if (file_exists($nocacheHtaccess)) {
                $backupHtaccess = $prestadoresDir . '/public/.htaccess.OLD';
                if (file_exists($oldHtaccess) && !file_exists($backupHtaccess)) {
                    rename($oldHtaccess, $backupHtaccess);
                }
                if (copy($nocacheHtaccess, $oldHtaccess)) {
                    logMessage("✅ .htaccess_nocache aplicado");
                }
            }
            
            echo '</div>';
            
            // PASSO 7: Limpar cache PHP
            echo '<div class="step">';
            echo '<h3>🔄 Passo 7: Limpando Cache PHP</h3>';
            
            if (function_exists('opcache_reset')) {
                if (opcache_reset()) {
                    logMessage("✅ OPcache limpo com sucesso");
                } else {
                    logMessage("⚠️ opcache_reset() retornou false");
                }
            } else {
                logMessage("ℹ️ OPcache não disponível via PHP");
            }
            
            clearstatcache(true);
            logMessage("✅ Stat cache limpo");
            
            echo '</div>';
            
            // PASSO 8: Resumo final
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            echo '<div class="step success">';
            echo '<h3>🎉 Deploy Concluído com Sucesso!</h3>';
            echo "<p><strong>Tempo de execução:</strong> {$executionTime} segundos</p>";
            echo "<p><strong>Arquivos extraídos:</strong> $fileCount</p>";
            echo "<p><strong>Diretório:</strong> <code>$prestadoresDir</code></p>";
            echo '<hr>';
            echo '<h4>📋 Próximos Passos:</h4>';
            echo '<ol>';
            echo '<li>Aguardar 2-3 minutos para cache limpar completamente</li>';
            echo '<li>Acessar: <a href="https://prestadores.clinfec.com.br" target="_blank">https://prestadores.clinfec.com.br</a></li>';
            echo '<li>Login: <code>admin@clinfec.com.br</code> / <code>password</code></li>';
            echo '<li>Verificar Dashboard com 6 cards e 4 gráficos</li>';
            echo '<li>Testar Gestão de Usuários</li>';
            echo '<li>Testar Empresas Tomadoras e Contratos</li>';
            echo '</ol>';
            echo '<h4>🔐 Credenciais de Teste:</h4>';
            echo '<ul>';
            echo '<li><strong>Admin:</strong> admin@clinfec.com.br / password</li>';
            echo '<li><strong>Master:</strong> master@clinfec.com.br / password</li>';
            echo '<li><strong>Gestor:</strong> gestor@clinfec.com.br / Gestor@2024</li>';
            echo '</ul>';
            echo '</div>';
            
            // Salvar log completo
            $logFile = $prestadoresDir . '/deploy_log_' . date('Ymd_His') . '.txt';
            file_put_contents($logFile, implode("\n", $log));
            logMessage("✅ Log salvo em: " . basename($logFile));
            
            ?>
            
            <div class="step warning">
                <h3>⚠️ Importante</h3>
                <p><strong>Remova este arquivo após validar o deploy:</strong></p>
                <p><code>rm <?php echo __FILE__; ?></code></p>
                <p>Por segurança, delete também o pacote .tar.gz</p>
            </div>
            
        </div>
    </div>
</body>
</html>
<?php
// Limpar buffer de saída
if (ob_get_level()) {
    ob_end_flush();
}
?>
