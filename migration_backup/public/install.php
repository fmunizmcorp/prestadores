<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Clinfec Prestadores</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #856404;
        }
        
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #155724;
        }
        
        .error-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #721c24;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .log {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
        }
        
        .log-item {
            margin-bottom: 8px;
            padding: 5px;
            border-radius: 4px;
        }
        
        .log-success {
            background: #d4edda;
            color: #155724;
        }
        
        .log-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .log-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalação do Sistema</h1>
        <p class="subtitle">Clinfec Prestadores - Sprint 31</p>
        
        <?php
        define('ROOT_PATH', dirname(__DIR__));
        define('CONFIG_PATH', ROOT_PATH . '/config');
        
        // Verificar se já foi instalado
        $installed = false;
        $can_install = true;
        $messages = [];
        
        // Verificar configuração do banco
        if (!file_exists(CONFIG_PATH . '/database.php')) {
            $can_install = false;
            echo '<div class="error-box">';
            echo '<strong>❌ Erro: Configuração do banco não encontrada!</strong>';
            echo '<p>Crie o arquivo <code>config/database.php</code> com as credenciais do banco de dados.</p>';
            echo '</div>';
        } else {
            $dbConfig = require CONFIG_PATH . '/database.php';
            
            // Tentar conectar
            try {
                $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                
                // Verificar se já está instalado
                $stmt = $pdo->query("SHOW TABLES LIKE 'database_version'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->query("SELECT * FROM database_version ORDER BY version DESC LIMIT 1");
                    $version = $stmt->fetch();
                    $installed = true;
                    
                    echo '<div class="success-box">';
                    echo '<strong>✅ Sistema já instalado!</strong>';
                    echo '<p>Versão do banco: ' . $version['version'] . '</p>';
                    echo '<p>Instalado em: ' . $version['installed_at'] . '</p>';
                    echo '</div>';
                }
                
                if (!$installed) {
                    echo '<div class="info-box">';
                    echo '<strong>ℹ️ Conexão com banco de dados OK</strong>';
                    echo '<p>Host: ' . $dbConfig['host'] . '</p>';
                    echo '<p>Database: ' . $dbConfig['database'] . '</p>';
                    echo '<p>Pronto para instalar!</p>';
                    echo '</div>';
                }
                
            } catch (PDOException $e) {
                $can_install = false;
                echo '<div class="error-box">';
                echo '<strong>❌ Erro ao conectar ao banco de dados</strong>';
                echo '<p>' . $e->getMessage() . '</p>';
                echo '</div>';
            }
        }
        
        // Processar instalação
        if (isset($_POST['install']) && $can_install && !$installed) {
            echo '<div class="log">';
            echo '<div class="log-item log-info">🔧 Iniciando instalação...</div>';
            
            try {
                // Ler arquivo SQL
                $sqlFile = ROOT_PATH . '/database/install.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception("Arquivo install.sql não encontrado!");
                }
                
                $sql = file_get_contents($sqlFile);
                
                // Dividir em comandos individuais
                $commands = array_filter(array_map('trim', explode(';', $sql)));
                
                $success_count = 0;
                $error_count = 0;
                
                foreach ($commands as $command) {
                    if (empty($command) || strpos($command, '--') === 0) {
                        continue;
                    }
                    
                    try {
                        $pdo->exec($command);
                        $success_count++;
                        
                        // Mostrar apenas comandos importantes
                        if (stripos($command, 'CREATE TABLE') !== false) {
                            preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $command, $matches);
                            if (isset($matches[1])) {
                                echo '<div class="log-item log-success">✅ Tabela criada: ' . $matches[1] . '</div>';
                            }
                        } elseif (stripos($command, 'INSERT') !== false) {
                            echo '<div class="log-item log-success">✅ Dados inseridos</div>';
                        }
                        
                    } catch (PDOException $e) {
                        $error_count++;
                        // Ignorar erros de "tabela já existe"
                        if (stripos($e->getMessage(), 'already exists') === false) {
                            echo '<div class="log-item log-error">❌ Erro: ' . $e->getMessage() . '</div>';
                        }
                    }
                }
                
                echo '<div class="log-item log-success">✅ Instalação concluída!</div>';
                echo '<div class="log-item log-info">📊 Comandos executados: ' . $success_count . '</div>';
                
                if ($error_count > 0) {
                    echo '<div class="log-item log-info">⚠️  Avisos: ' . $error_count . '</div>';
                }
                
                echo '</div>';
                
                echo '<div class="success-box" style="margin-top: 20px;">';
                echo '<strong>🎉 Sistema instalado com sucesso!</strong>';
                echo '<p>Agora você pode <a href="/">acessar o sistema</a></p>';
                echo '</div>';
                
                $installed = true;
                
            } catch (Exception $e) {
                echo '<div class="log-item log-error">❌ ERRO FATAL: ' . $e->getMessage() . '</div>';
                echo '</div>';
            }
        }
        
        // Botão de instalação
        if (!$installed && $can_install) {
            echo '<div class="warning-box">';
            echo '<strong>⚠️  Atenção</strong>';
            echo '<p>Esta operação irá criar as tabelas no banco de dados.</p>';
            echo '<p>Certifique-se de ter feito backup se necessário.</p>';
            echo '</div>';
            
            echo '<form method="POST">';
            echo '<button type="submit" name="install" class="btn">Iniciar Instalação</button>';
            echo '</form>';
        } elseif ($installed) {
            echo '<a href="/"><button class="btn">Acessar Sistema</button></a>';
        }
        ?>
        
        <div class="footer">
            <p>Sprint 31 - Instalação Manual</p>
            <p>Contorna problema de cache PHP 8.1</p>
        </div>
    </div>
</body>
</html>
