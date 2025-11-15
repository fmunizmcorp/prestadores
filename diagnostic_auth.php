<?php
/**
 * DIAGNÓSTICO COMPLETO DE AUTENTICAÇÃO
 * Este script verifica e corrige problemas de login
 */

// Desabilitar cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico de Autenticação</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.container { background: white; padding: 20px; border-radius: 8px; max-width: 900px; margin: 0 auto; }
h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
h2 { color: #667eea; margin-top: 30px; }
.success { background: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 4px; color: #155724; margin: 10px 0; }
.error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 4px; color: #721c24; margin: 10px 0; }
.info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 12px; border-radius: 4px; color: #0c5460; margin: 10px 0; }
.warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 4px; color: #856404; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; margin: 15px 0; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { background: #667eea; color: white; }
code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style></head><body><div class='container'>";

echo "<h1>🔍 DIAGNÓSTICO COMPLETO DE AUTENTICAÇÃO</h1>";
echo "<p><strong>Data:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";

// Variável para rastrear problemas
$problems = [];
$fixes = [];

// ============================================================================
echo "<h2>1️⃣ VERIFICAÇÃO DA CONEXÃO COM BANCO DE DADOS</h2>";
// ============================================================================

try {
    // Carregar configuração do banco
    $config = [
        'host' => 'localhost',
        'name' => 'u673902663_prestadores',
        'user' => 'u673902663_admin',
        'pass' => ';>?I4dtn~2Ga'
    ];
    
    echo "<div class='info'>";
    echo "<strong>Configuração:</strong><br>";
    echo "Host: <code>" . htmlspecialchars($config['host']) . "</code><br>";
    echo "Database: <code>" . htmlspecialchars($config['name']) . "</code><br>";
    echo "User: <code>" . htmlspecialchars($config['user']) . "</code>";
    echo "</div>";
    
    $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<div class='success'>✅ Conexão com banco de dados estabelecida com sucesso!</div>";
    
} catch (PDOException $e) {
    $problems[] = "Falha na conexão com banco de dados";
    echo "<div class='error'>❌ <strong>ERRO:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='warning'>⚠️ O sistema não pode funcionar sem conexão com o banco. Verifique as credenciais.</div>";
    echo "</div></body></html>";
    exit;
}

// ============================================================================
echo "<h2>2️⃣ VERIFICAÇÃO DA TABELA USUARIOS</h2>";
// ============================================================================

try {
    // Verificar se tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        $problems[] = "Tabela 'usuarios' não existe";
        echo "<div class='error'>❌ Tabela <code>usuarios</code> não encontrada no banco de dados!</div>";
        
        // Criar tabela
        echo "<div class='info'>🔧 Criando tabela <code>usuarios</code>...</div>";
        
        $createTable = "
        CREATE TABLE `usuarios` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `nome` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `senha` VARCHAR(255) NOT NULL,
            `perfil` ENUM('admin','gestor','usuario') NOT NULL DEFAULT 'usuario',
            `ativo` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTable);
        $fixes[] = "Tabela 'usuarios' criada com sucesso";
        echo "<div class='success'>✅ Tabela <code>usuarios</code> criada com sucesso!</div>";
        
    } else {
        echo "<div class='success'>✅ Tabela <code>usuarios</code> existe</div>";
    }
    
    // Mostrar estrutura da tabela
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll();
    
    echo "<div class='info'><strong>Estrutura da tabela:</strong></div>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><code>" . htmlspecialchars($col['Field']) . "</code></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    $problems[] = "Erro ao verificar tabela usuarios";
    echo "<div class='error'>❌ <strong>ERRO:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

// ============================================================================
echo "<h2>3️⃣ VERIFICAÇÃO DO USUÁRIO ADMIN</h2>";
// ============================================================================

try {
    // Verificar se usuário admin existe
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@clinfec.com.br']);
    $adminUser = $stmt->fetch();
    
    if (!$adminUser) {
        $problems[] = "Usuário admin@clinfec.com.br não existe";
        echo "<div class='error'>❌ Usuário <code>admin@clinfec.com.br</code> não encontrado!</div>";
        
        // Criar usuário admin
        echo "<div class='info'>🔧 Criando usuário administrador...</div>";
        
        $senha = 'Master@2024';
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, perfil, ativo) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Administrador do Sistema',
            'admin@clinfec.com.br',
            $senhaHash,
            'admin',
            1
        ]);
        
        $fixes[] = "Usuário admin criado com sucesso";
        echo "<div class='success'>✅ Usuário administrador criado com sucesso!</div>";
        echo "<div class='info'>";
        echo "<strong>Credenciais:</strong><br>";
        echo "E-mail: <code>admin@clinfec.com.br</code><br>";
        echo "Senha: <code>Master@2024</code><br>";
        echo "Hash: <code>" . htmlspecialchars(substr($senhaHash, 0, 50)) . "...</code>";
        echo "</div>";
        
        // Buscar usuário recém-criado
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute(['admin@clinfec.com.br']);
        $adminUser = $stmt->fetch();
        
    } else {
        echo "<div class='success'>✅ Usuário <code>admin@clinfec.com.br</code> existe</div>";
        
        // Verificar se senha está correta
        echo "<div class='info'>🔐 Verificando hash da senha...</div>";
        
        $senhaCorreta = 'Master@2024';
        $senhaVerificada = password_verify($senhaCorreta, $adminUser['senha']);
        
        if ($senhaVerificada) {
            echo "<div class='success'>✅ Hash da senha está correto!</div>";
        } else {
            $problems[] = "Hash da senha incorreto";
            echo "<div class='error'>❌ Hash da senha NÃO confere com 'Master@2024'</div>";
            echo "<div class='info'>🔧 Atualizando senha para 'Master@2024'...</div>";
            
            $novoHash = password_hash('Master@2024', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
            $stmt->execute([$novoHash, 'admin@clinfec.com.br']);
            
            $fixes[] = "Senha do admin atualizada";
            echo "<div class='success'>✅ Senha atualizada com sucesso!</div>";
            
            // Re-buscar usuário
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute(['admin@clinfec.com.br']);
            $adminUser = $stmt->fetch();
        }
    }
    
    // Mostrar dados do usuário
    echo "<div class='info'><strong>Dados do usuário admin:</strong></div>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    foreach ($adminUser as $key => $value) {
        if ($key === 'senha') {
            $value = substr($value, 0, 30) . '...';
        }
        echo "<tr><td><strong>" . htmlspecialchars($key) . "</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    $problems[] = "Erro ao verificar/criar usuário admin";
    echo "<div class='error'>❌ <strong>ERRO:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

// ============================================================================
echo "<h2>4️⃣ TESTE DE AUTENTICAÇÃO</h2>";
// ============================================================================

try {
    echo "<div class='info'>🧪 Testando autenticação com credenciais...</div>";
    
    $email = 'admin@clinfec.com.br';
    $senha = 'Master@2024';
    
    // Buscar usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $problems[] = "Usuário não encontrado ou inativo";
        echo "<div class='error'>❌ Usuário não encontrado ou está inativo</div>";
    } else {
        // Verificar senha
        if (password_verify($senha, $usuario['senha'])) {
            echo "<div class='success'>✅ AUTENTICAÇÃO BEM-SUCEDIDA!</div>";
            echo "<div class='success'>🎉 Login com <code>admin@clinfec.com.br / Master@2024</code> deve funcionar!</div>";
        } else {
            $problems[] = "Senha incorreta";
            echo "<div class='error'>❌ Senha incorreta</div>";
        }
    }
    
} catch (PDOException $e) {
    $problems[] = "Erro ao testar autenticação";
    echo "<div class='error'>❌ <strong>ERRO:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

// ============================================================================
echo "<h2>5️⃣ VERIFICAÇÃO DE SESSÕES PHP</h2>";
// ============================================================================

echo "<div class='info'><strong>Configuração de Sessões:</strong></div>";
echo "<table>";
echo "<tr><td><strong>session.save_handler</strong></td><td><code>" . ini_get('session.save_handler') . "</code></td></tr>";
echo "<tr><td><strong>session.save_path</strong></td><td><code>" . ini_get('session.save_path') . "</code></td></tr>";
echo "<tr><td><strong>session.cookie_lifetime</strong></td><td><code>" . ini_get('session.cookie_lifetime') . "</code></td></tr>";
echo "<tr><td><strong>session.gc_maxlifetime</strong></td><td><code>" . ini_get('session.gc_maxlifetime') . "</code></td></tr>";
echo "</table>";

// Testar se sessão pode ser iniciada
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<div class='success'>✅ Sessões PHP estão funcionando</div>";
        $_SESSION['test'] = 'OK';
        echo "<div class='info'>Session ID: <code>" . session_id() . "</code></div>";
    } else {
        $problems[] = "Não foi possível iniciar sessão PHP";
        echo "<div class='error'>❌ Não foi possível iniciar sessão PHP</div>";
    }
} else {
    echo "<div class='success'>✅ Sessão PHP já estava ativa</div>";
}

// ============================================================================
echo "<h2>6️⃣ RESUMO FINAL</h2>";
// ============================================================================

if (empty($problems)) {
    echo "<div class='success'>";
    echo "<h3>🎉 SISTEMA PRONTO PARA LOGIN!</h3>";
    echo "<p><strong>Todas as verificações passaram!</strong></p>";
    echo "<p>Você pode fazer login em: <a href='/?page=login'>/?page=login</a></p>";
    echo "<p><strong>Credenciais:</strong></p>";
    echo "<ul>";
    echo "<li>E-mail: <code>admin@clinfec.com.br</code></li>";
    echo "<li>Senha: <code>Master@2024</code></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ PROBLEMAS ENCONTRADOS</h3>";
    echo "<ul>";
    foreach ($problems as $problem) {
        echo "<li>" . htmlspecialchars($problem) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($fixes)) {
    echo "<div class='info'>";
    echo "<h3>🔧 CORREÇÕES APLICADAS</h3>";
    echo "<ul>";
    foreach ($fixes as $fix) {
        echo "<li>" . htmlspecialchars($fix) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #666; margin-top: 30px;'>";
echo "Diagnóstico gerado em " . date('Y-m-d H:i:s') . " | Sistema Prestadores Clinfec";
echo "</p>";

echo "</div></body></html>";
?>
