<?php
/**
 * Sprint 60: Manual Cache Clear Endpoint
 * 
 * Simple one-click cache clearing tool for user
 * Access: https://clinfec.com.br/prestadores/clear_cache_manual_sprint60.php
 * 
 * Purpose: Allow user to manually clear cache without technical knowledge
 * Sprint: 60
 * Created: 2025-11-15
 */

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Track actions
$actions_taken = [];
$success_count = 0;
$error_count = 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpar Cache - Sprint 60</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .clear-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 25px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            font-weight: bold;
        }
        .clear-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .clear-btn:active {
            transform: translateY(0);
        }
        .status-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .status-box.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .status-box.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .status-box.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .action-list {
            list-style: none;
            padding: 0;
        }
        .action-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .action-list li:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 5px;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-error {
            background: #dc3545;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #000;
        }
        .next-steps {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .next-steps h3 {
            color: #004085;
            margin-bottom: 10px;
        }
        .next-steps ol {
            margin-left: 20px;
            color: #004085;
        }
        .next-steps li {
            margin: 8px 0;
        }
        .links {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
            display: inline-block;
            margin: 5px 10px 5px 0;
            padding: 8px 15px;
            border: 2px solid #667eea;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .links a:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧹 Limpar Cache do Sistema</h1>
            <p>Sprint 60 - Ferramenta Manual de Limpeza de Cache</p>
        </div>
        
        <div class="content">
            <?php
            // Check if clear action was requested
            if (isset($_GET['action']) && $_GET['action'] === 'clear') {
                echo '<div class="status-box">';
                echo '<h2 style="margin-bottom:15px;">🔄 Executando Limpeza de Cache...</h2>';
                echo '</div>';
                
                // Action 1: OPcache Reset
                if (function_exists('opcache_reset')) {
                    try {
                        $result = opcache_reset();
                        if ($result) {
                            $actions_taken[] = ['success' => true, 'action' => 'opcache_reset()', 'message' => 'Cache OPcache resetado completamente'];
                            $success_count++;
                        } else {
                            $actions_taken[] = ['success' => false, 'action' => 'opcache_reset()', 'message' => 'Falha ao resetar OPcache'];
                            $error_count++;
                        }
                    } catch (Exception $e) {
                        $actions_taken[] = ['success' => false, 'action' => 'opcache_reset()', 'message' => $e->getMessage()];
                        $error_count++;
                    }
                } else {
                    $actions_taken[] = ['success' => false, 'action' => 'opcache_reset()', 'message' => 'Função não disponível'];
                    $error_count++;
                }
                
                // Action 2: Invalidate Database.php
                $database_file = __DIR__ . '/src/Database.php';
                if (file_exists($database_file) && function_exists('opcache_invalidate')) {
                    try {
                        $result = opcache_invalidate($database_file, true);
                        if ($result) {
                            $actions_taken[] = ['success' => true, 'action' => 'opcache_invalidate(Database.php)', 'message' => 'Database.php invalidado no cache'];
                            $success_count++;
                        } else {
                            $actions_taken[] = ['success' => false, 'action' => 'opcache_invalidate(Database.php)', 'message' => 'Falha ao invalidar Database.php'];
                            $error_count++;
                        }
                    } catch (Exception $e) {
                        $actions_taken[] = ['success' => false, 'action' => 'opcache_invalidate(Database.php)', 'message' => $e->getMessage()];
                        $error_count++;
                    }
                }
                
                // Action 3: Touch Database.php
                if (file_exists($database_file)) {
                    try {
                        $result = touch($database_file);
                        if ($result) {
                            $actions_taken[] = ['success' => true, 'action' => 'touch(Database.php)', 'message' => 'Timestamp do arquivo atualizado'];
                            $success_count++;
                        } else {
                            $actions_taken[] = ['success' => false, 'action' => 'touch(Database.php)', 'message' => 'Falha ao atualizar timestamp'];
                            $error_count++;
                        }
                    } catch (Exception $e) {
                        $actions_taken[] = ['success' => false, 'action' => 'touch(Database.php)', 'message' => $e->getMessage()];
                        $error_count++;
                    }
                }
                
                // Action 4: Clear stat cache
                try {
                    clearstatcache(true);
                    $actions_taken[] = ['success' => true, 'action' => 'clearstatcache()', 'message' => 'Cache de estatísticas limpo'];
                    $success_count++;
                } catch (Exception $e) {
                    $actions_taken[] = ['success' => false, 'action' => 'clearstatcache()', 'message' => $e->getMessage()];
                    $error_count++;
                }
                
                // Action 5: Disable OPcache temporarily
                try {
                    @ini_set('opcache.enable', '0');
                    $actions_taken[] = ['success' => true, 'action' => 'ini_set(opcache.enable, 0)', 'message' => 'OPcache desabilitado temporariamente'];
                    $success_count++;
                } catch (Exception $e) {
                    $actions_taken[] = ['success' => false, 'action' => 'ini_set(opcache.enable, 0)', 'message' => $e->getMessage()];
                    $error_count++;
                }
                
                // Show results
                if ($success_count > 0) {
                    echo '<div class="status-box success">';
                    echo '<h3>✅ Limpeza Executada com Sucesso!</h3>';
                    echo "<p>$success_count ações realizadas com sucesso</p>";
                    echo '</div>';
                } else {
                    echo '<div class="status-box error">';
                    echo '<h3>❌ Nenhuma Ação Bem-Sucedida</h3>';
                    echo "<p>$error_count ações falharam</p>";
                    echo '</div>';
                }
                
                // List actions
                echo '<div class="status-box">';
                echo '<h3>📋 Detalhes das Ações:</h3>';
                echo '<ul class="action-list">';
                
                foreach ($actions_taken as $action) {
                    $badge_class = $action['success'] ? 'badge-success' : 'badge-error';
                    $icon = $action['success'] ? '✅' : '❌';
                    
                    echo '<li>';
                    echo "<span class='badge $badge_class'>$icon</span> ";
                    echo "<strong>" . htmlspecialchars($action['action']) . "</strong><br>";
                    echo "<small>" . htmlspecialchars($action['message']) . "</small>";
                    echo '</li>';
                }
                
                echo '</ul>';
                echo '</div>';
                
                // Next steps
                echo '<div class="next-steps">';
                echo '<h3>🎯 Próximos Passos:</h3>';
                echo '<ol>';
                echo '<li><strong>Aguarde 2-3 minutos</strong> para as mudanças terem efeito</li>';
                echo '<li><strong>Teste os módulos</strong> do sistema (Projetos, Empresas, etc.)</li>';
                echo '<li>Se o problema persistir, <strong>recarregue esta página</strong> e clique em "Limpar Cache" novamente</li>';
                echo '<li><strong>Monitore o status</strong> usando o link abaixo</li>';
                echo '</ol>';
                echo '</div>';
                
                echo '<div class="links">';
                echo '<a href="monitor_cache_status_sprint60.php">📊 Monitorar Status</a>';
                echo '<a href="?page=projetos" target="_blank">🧪 Testar Projetos</a>';
                echo '<a href="?">🏠 Voltar ao Sistema</a>';
                echo '</div>';
                
            } else {
                // Show initial screen with button
                ?>
                <div class="status-box warning">
                    <h2 style="margin-bottom:10px;">⚠️ Quando Usar Esta Ferramenta</h2>
                    <p>Use esta ferramenta se:</p>
                    <ul style="margin:10px 0 0 20px;">
                        <li>Os módulos do sistema ainda mostram erros após 2+ horas do deploy</li>
                        <li>Você vê a mensagem "Call to undefined method prepare()"</li>
                        <li>O sistema está com funcionalidade abaixo de 100%</li>
                    </ul>
                </div>

                <div class="status-box">
                    <h2 style="margin-bottom:10px;">🔧 O Que Esta Ferramenta Faz</h2>
                    <p>Esta ferramenta executa 5 métodos de limpeza de cache:</p>
                    <ol style="margin:10px 0 0 20px;">
                        <li><strong>opcache_reset()</strong> - Reseta todo cache OPcache</li>
                        <li><strong>opcache_invalidate()</strong> - Invalida Database.php específico</li>
                        <li><strong>touch()</strong> - Atualiza timestamp do arquivo</li>
                        <li><strong>clearstatcache()</strong> - Limpa cache de estatísticas</li>
                        <li><strong>ini_set()</strong> - Desabilita OPcache temporariamente</li>
                    </ol>
                </div>

                <div class="button-container">
                    <form method="get">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="clear-btn">
                            🧹 Limpar Cache Agora
                        </button>
                    </form>
                    <p style="margin-top:15px; color:#6c757d; font-size:14px;">
                        Clique no botão acima para executar a limpeza de cache
                    </p>
                </div>

                <div class="links">
                    <a href="monitor_cache_status_sprint60.php">📊 Monitorar Status Primeiro</a>
                    <a href="?">🏠 Voltar ao Sistema</a>
                </div>
                <?php
            }
            ?>
            
            <div style="margin-top:30px; padding-top:20px; border-top:1px solid #e9ecef; text-align:center; color:#6c757d; font-size:12px;">
                Sprint 60 | Cache Clear Tool | <?= date('Y-m-d H:i:s') ?>
            </div>
        </div>
    </div>
</body>
</html>

<!-- Sprint 60 Manual Cache Clear | SCRUM + PDCA | User-Friendly Tool -->
