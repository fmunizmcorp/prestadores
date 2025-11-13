<?php
/**
 * Diagnostic Script - Sprint 16
 * Generated: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Force OPcache reset
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset executed\n\n";
}

// Basic environment check
echo "=== BASIC ENVIRONMENT CHECK ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Script Path: " . __FILE__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

// Define ROOT_PATH
define('ROOT_PATH', dirname(__FILE__));
echo "ROOT_PATH: " . ROOT_PATH . "\n\n";

// Check config file
$config_file = ROOT_PATH . '/config/database.php';
echo "=== CONFIG CHECK ===\n";
echo "Config file exists: " . (file_exists($config_file) ? 'YES' : 'NO') . "\n";

if (file_exists($config_file)) {
    require_once $config_file;
    echo "Config loaded successfully\n\n";
    
    // Test database connection
    echo "=== DATABASE CONNECTION ===\n";
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo "✓ Database connected successfully\n";
        echo "Database: " . DB_NAME . "\n\n";
        
        // Check usuarios table
        echo "=== USUARIOS TABLE ===\n";
        $stmt = $pdo->query("SELECT id, nome, email, perfil, ativo FROM usuarios ORDER BY id");
        $users = $stmt->fetchAll();
        echo "Total users: " . count($users) . "\n\n";
        foreach ($users as $user) {
            echo sprintf("ID: %d | %s | %s | Perfil: %s | Ativo: %s\n", 
                $user['id'], 
                $user['nome'], 
                $user['email'], 
                $user['perfil'], 
                $user['ativo'] ? 'SIM' : 'NÃO'
            );
        }
        
        // Check password for master user
        echo "\n=== PASSWORD CHECK ===\n";
        $stmt = $pdo->query("SELECT senha FROM usuarios WHERE email = 'master@clinfec.com.br' LIMIT 1");
        $result = $stmt->fetch();
        if ($result) {
            $hash = $result['senha'];
            $test_password = 'password';
            $verify = password_verify($test_password, $hash);
            echo "Master user hash: " . substr($hash, 0, 20) . "...\n";
            echo "Password 'password' verification: " . ($verify ? '✓ VALID' : '✗ INVALID') . "\n";
        } else {
            echo "Master user not found\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Config file not found!\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
