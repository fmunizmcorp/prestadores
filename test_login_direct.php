<?php
/**
 * TESTE DIRETO DE LOGIN
 * Testa todo o fluxo de autenticação
 */

// Desabilitar cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=UTF-8');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Teste de Login</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
.success { background: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 4px; color: #155724; margin: 10px 0; }
.error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 4px; color: #721c24; margin: 10px 0; }
.info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 12px; border-radius: 4px; color: #0c5460; margin: 10px 0; }
pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
.btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
.btn:hover { background: #5568d3; }
</style></head><body><div class='container'>";

echo "<h1>🧪 TESTE DIRETO DE LOGIN</h1>";
echo "<p><strong>Data:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar se foi enviado POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>📋 DADOS RECEBIDOS</h2>";
    echo "<div class='info'>";
    echo "<strong>E-mail:</strong> " . htmlspecialchars($_POST['email'] ?? '(vazio)') . "<br>";
    echo "<strong>Senha:</strong> " . (empty($_POST['senha']) ? '(vazia)' : '****** (presente)');
    echo "</div>";
    
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        echo "<div class='error'>❌ E-mail e senha são obrigatórios</div>";
    } else {
        // Conectar ao banco
        try {
            $dsn = "mysql:host=localhost;dbname=u673902663_prestadores;charset=utf8mb4";
            $pdo = new PDO($dsn, 'u673902663_admin', ';>?I4dtn~2Ga', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            echo "<div class='success'>✅ Conectado ao banco de dados</div>";
            
            // Buscar usuário
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                echo "<div class='error'>❌ Usuário não encontrado ou inativo</div>";
            } else {
                echo "<div class='success'>✅ Usuário encontrado</div>";
                echo "<div class='info'>";
                echo "<strong>ID:</strong> " . $usuario['id'] . "<br>";
                echo "<strong>Nome:</strong> " . htmlspecialchars($usuario['nome']) . "<br>";
                echo "<strong>E-mail:</strong> " . htmlspecialchars($usuario['email']) . "<br>";
                echo "<strong>Perfil:</strong> " . htmlspecialchars($usuario['perfil']) . "<br>";
                echo "<strong>Role:</strong> " . htmlspecialchars($usuario['role'] ?? 'N/A') . "<br>";
                echo "<strong>Ativo:</strong> " . ($usuario['ativo'] ? 'Sim' : 'Não');
                echo "</div>";
                
                // Verificar senha
                if (password_verify($senha, $usuario['senha'])) {
                    echo "<div class='success'>✅ Senha correta!</div>";
                    
                    // Tentar criar sessão
                    echo "<h2>🔐 CRIANDO SESSÃO</h2>";
                    
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION['user_id'] = $usuario['id'];
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['usuario_nome'] = $usuario['nome'];
                        $_SESSION['usuario_email'] = $usuario['email'];
                        $_SESSION['usuario_perfil'] = $usuario['role'] ?? $usuario['perfil'];
                        
                        echo "<div class='success'>✅ Sessão criada com sucesso!</div>";
                        echo "<div class='info'>";
                        echo "<strong>Session ID:</strong> " . session_id() . "<br>";
                        echo "<strong>Dados da sessão:</strong><br>";
                        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
                        echo "</div>";
                        
                        echo "<div class='success'>";
                        echo "<h3>🎉 LOGIN BEM-SUCEDIDO!</h3>";
                        echo "<p>Você pode agora acessar o sistema normalmente.</p>";
                        echo "<a href='/?page=dashboard' class='btn'>Ir para Dashboard</a>";
                        echo "<a href='/?page=login' class='btn'>Ir para Login</a>";
                        echo "</div>";
                        
                    } else {
                        echo "<div class='error'>❌ Sessão PHP não está ativa</div>";
                        echo "<div class='info'>Status da sessão: " . session_status() . "</div>";
                    }
                    
                } else {
                    echo "<div class='error'>❌ Senha incorreta</div>";
                    echo "<div class='info'>A senha fornecida não confere com o hash do banco</div>";
                }
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Erro de banco de dados: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
} else {
    // Mostrar formulário
    echo "<h2>📝 FORMULÁRIO DE TESTE</h2>";
    echo "<div class='info'>Use este formulário para testar o login diretamente</div>";
    
    echo "<form method='POST' action=''>";
    echo "<p>";
    echo "<label><strong>E-mail:</strong></label><br>";
    echo "<input type='email' name='email' value='admin@clinfec.com.br' style='width: 100%; padding: 8px; margin: 5px 0;' required>";
    echo "</p>";
    
    echo "<p>";
    echo "<label><strong>Senha:</strong></label><br>";
    echo "<input type='password' name='senha' value='Master@2024' style='width: 100%; padding: 8px; margin: 5px 0;' required>";
    echo "</p>";
    
    echo "<p>";
    echo "<button type='submit' class='btn'>Testar Login</button>";
    echo "</p>";
    echo "</form>";
    
    echo "<div class='info'>";
    echo "<h3>ℹ️ Informações da Sessão Atual</h3>";
    echo "<strong>Session Status:</strong> ";
    switch (session_status()) {
        case PHP_SESSION_DISABLED:
            echo "DISABLED (sessões desabilitadas)";
            break;
        case PHP_SESSION_NONE:
            echo "NONE (sessão não iniciada)";
            break;
        case PHP_SESSION_ACTIVE:
            echo "ACTIVE (sessão ativa)";
            break;
    }
    echo "<br>";
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<strong>Session ID:</strong> " . session_id() . "<br>";
        echo "<strong>Session Data:</strong><br>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    }
    echo "</div>";
}

echo "</div></body></html>";
?>
