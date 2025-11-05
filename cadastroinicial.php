<?php
/**
 * Script de Cadastro Inicial de Usuário
 * 
 * Este script cria um usuário administrador inicial no sistema.
 * Execute este arquivo apenas UMA VEZ após a instalação.
 * 
 * URL: https://clinfec.com.br/prestadores/cadastroinicial.php
 * 
 * ATENÇÃO: DELETE este arquivo após executar!
 */

// Configurações do banco de dados
$dbConfig = [
    'host' => 'localhost',
    'database' => 'u673902663_prestadores',
    'username' => 'u673902663_admin',
    'password' => ';>?I4dtn~2Ga',
    'charset' => 'utf8mb4'
];

// Dados do usuário a ser criado
$userData = [
    'nome' => 'Flávio Administrador',
    'email' => 'flavio@clinfec.com.br',
    'senha' => 'admin123',
    'perfil' => 'master',
    'ativo' => 1
];

// HTML Header
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Inicial - Clinfec Prestadores</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            padding: 40px;
        }
        h1 {
            color: #333;
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
        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-box strong {
            color: #333;
        }
        .result {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        .success .icon { color: #28a745; }
        .error .icon { color: #dc3545; }
        .warning .icon { color: #ffc107; }
        .credentials {
            background: #e7f3ff;
            border: 2px solid #2196F3;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials h3 {
            color: #1976D2;
            margin-bottom: 15px;
        }
        .credential-item {
            background: white;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .credential-label {
            font-weight: 600;
            color: #555;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            color: #1976D2;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .warning-box strong {
            color: #856404;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }
        ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        li {
            margin-bottom: 8px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Cadastro Inicial de Usuário</h1>
        <p class="subtitle">Clinfec Prestadores - Sistema de Gestão</p>

<?php

// Função para exibir mensagens
function showMessage($type, $icon, $title, $message) {
    echo "<div class='result $type'>";
    echo "<span class='icon'>$icon</span>";
    echo "<h3>$title</h3>";
    echo "<p>$message</p>";
    echo "</div>";
}

try {
    // Conectar ao banco de dados
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    echo "<div class='info-box'>";
    echo "<h3>✅ Conexão Estabelecida</h3>";
    echo "<p>Conectado ao banco de dados: <strong>{$dbConfig['database']}</strong></p>";
    echo "</div>";

    // Verificar se o usuário já existe
    $stmt = $pdo->prepare("SELECT id, nome, email, perfil FROM usuarios WHERE email = ?");
    $stmt->execute([$userData['email']]);
    $usuarioExistente = $stmt->fetch();

    if ($usuarioExistente) {
        showMessage(
            'warning',
            '⚠️',
            'Usuário Já Existe',
            "Um usuário com o email <strong>{$userData['email']}</strong> já está cadastrado no sistema."
        );

        echo "<div class='credentials'>";
        echo "<h3>📋 Dados do Usuário Existente:</h3>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>ID:</span>";
        echo "<span class='credential-value'>{$usuarioExistente['id']}</span>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>Nome:</span>";
        echo "<span class='credential-value'>{$usuarioExistente['nome']}</span>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>Email:</span>";
        echo "<span class='credential-value'>{$usuarioExistente['email']}</span>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>Perfil:</span>";
        echo "<span class='credential-value'>" . strtoupper($usuarioExistente['perfil']) . "</span>";
        echo "</div>";
        echo "</div>";

        echo "<p style='margin-top: 20px; color: #666;'>Se você esqueceu a senha, pode redefini-la diretamente no banco de dados ou criar um novo script de redefinição.</p>";

    } else {
        // Hash da senha
        $senhaHash = password_hash($userData['senha'], PASSWORD_DEFAULT);

        // Inserir usuário
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil, ativo, created_at) 
                VALUES (:nome, :email, :senha, :perfil, :ativo, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $userData['nome'],
            ':email' => $userData['email'],
            ':senha' => $senhaHash,
            ':perfil' => $userData['perfil'],
            ':ativo' => $userData['ativo']
        ]);

        $userId = $pdo->lastInsertId();

        showMessage(
            'success',
            '✅',
            'Usuário Criado com Sucesso!',
            "O usuário administrador foi cadastrado no sistema com ID: <strong>$userId</strong>"
        );

        // Exibir credenciais
        echo "<div class='credentials'>";
        echo "<h3>🔐 Credenciais de Acesso:</h3>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>Nome:</span>";
        echo "<span class='credential-value'>{$userData['nome']}</span>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>Email:</span>";
        echo "<span class='credential-value'>{$userData['email']}</span>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>Senha:</span>";
        echo "<span class='credential-value'>{$userData['senha']}</span>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<span class='credential-label'>Perfil:</span>";
        echo "<span class='credential-value'>MASTER (Acesso Total)</span>";
        echo "</div>";
        echo "</div>";

        echo "<div class='info-box' style='margin-top: 20px;'>";
        echo "<h3>ℹ️ Próximos Passos:</h3>";
        echo "<p><strong>1.</strong> Acesse a página de login: <a href='/?page=login' target='_blank'>https://clinfec.com.br/prestadores/?page=login</a></p>";
        echo "<p><strong>2.</strong> Faça login com as credenciais acima</p>";
        echo "<p><strong>3.</strong> <strong style='color: #dc3545;'>DELETE este arquivo (cadastroinicial.php) por segurança!</strong></p>";
        echo "</div>";
    }

    // Avisos de segurança
    echo "<div class='warning-box'>";
    echo "<strong>⚠️ IMPORTANTE - SEGURANÇA:</strong>";
    echo "<ul>";
    echo "<li><strong>DELETE</strong> este arquivo imediatamente após o uso!</li>";
    echo "<li>Este arquivo contém credenciais sensíveis do banco de dados</li>";
    echo "<li>Manter este arquivo é um risco de segurança</li>";
    echo "<li>Para deletar, execute: <code>rm cadastroinicial.php</code> no SSH ou delete pelo FTP</li>";
    echo "</ul>";
    echo "</div>";

} catch (PDOException $e) {
    showMessage(
        'error',
        '❌',
        'Erro de Conexão',
        "Não foi possível conectar ao banco de dados.<br><br><strong>Erro:</strong> {$e->getMessage()}"
    );

    echo "<div class='info-box'>";
    echo "<h3>🔧 Verificações Necessárias:</h3>";
    echo "<ul>";
    echo "<li>Confirme que o banco de dados <code>{$dbConfig['database']}</code> existe</li>";
    echo "<li>Verifique as credenciais de acesso ao banco</li>";
    echo "<li>Certifique-se que as migrations foram executadas</li>";
    echo "<li>Verifique se a tabela <code>usuarios</code> existe</li>";
    echo "</ul>";
    echo "</div>";
} catch (Exception $e) {
    showMessage(
        'error',
        '❌',
        'Erro Inesperado',
        "Ocorreu um erro ao criar o usuário.<br><br><strong>Erro:</strong> {$e->getMessage()}"
    );
}

?>

        <div class="actions">
            <a href="/?page=login" class="btn">🔓 Ir para Login</a>
            <a href="/" class="btn">🏠 Ir para Home</a>
        </div>

        <p style="margin-top: 30px; text-align: center; color: #999; font-size: 12px;">
            Clinfec Prestadores v1.0.0 - Sistema de Gestão de Atividades e Projetos
        </p>
    </div>
</body>
</html>
