<?php
/**
 * DIAGNOSTIC COMPLETE V7 - Post Sprint 16
 * Comprehensive system diagnostic to identify ALL issues
 * 
 * Tests:
 * 1. Database Connection
 * 2. All Users and Credentials
 * 3. Password Verification
 * 4. Tables Structure
 * 5. Migrations Status
 * 6. Models Loading
 * 7. Controllers Existence
 * 8. Routes Configuration
 * 9. File Permissions
 * 10. PHP Configuration
 */

// Force clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
}

header('Content-Type: text/plain; charset=utf-8');

echo "=========================================================\n";
echo "DIAGNOSTIC COMPLETE V7 - POST SPRINT 16\n";
echo "=========================================================\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "\n";
echo "\n";

$errors = [];
$warnings = [];
$success = [];

// ==========================================
// 1. DATABASE CONNECTION
// ==========================================
echo "[1] TESTING DATABASE CONNECTION\n";
echo "-----------------------------------------------------------\n";

try {
    require __DIR__ . '/config/database.php';
    
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✓ Database connected successfully\n";
    echo "  Host: " . DB_HOST . "\n";
    echo "  Database: " . DB_NAME . "\n";
    $success[] = "Database connection OK";
    
} catch (PDOException $e) {
    echo "✗ DATABASE ERROR: " . $e->getMessage() . "\n";
    $errors[] = "Database connection failed: " . $e->getMessage();
    die("\n[CRITICAL] Cannot proceed without database\n");
}

echo "\n";

// ==========================================
// 2. USERS AND CREDENTIALS
// ==========================================
echo "[2] CHECKING USERS TABLE\n";
echo "-----------------------------------------------------------\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "Total users in database: {$result['total']}\n\n";
    
    if ($result['total'] == 0) {
        $warnings[] = "No users found in database";
        echo "⚠️  WARNING: No users found!\n\n";
    }
    
    // List all users
    $stmt = $pdo->query("
        SELECT id, nome, email, perfil, ativo, 
               LEFT(senha, 30) as senha_truncated,
               created_at, updated_at
        FROM usuarios 
        ORDER BY id
    ");
    $users = $stmt->fetchAll();
    
    echo "All Users:\n";
    foreach ($users as $user) {
        $status = $user['ativo'] ? '✓ ACTIVE' : '✗ INACTIVE';
        echo "  [{$user['id']}] {$user['email']}\n";
        echo "      Name: {$user['nome']}\n";
        echo "      Profile: {$user['perfil']}\n";
        echo "      Status: $status\n";
        echo "      Hash: {$user['senha_truncated']}...\n";
        echo "      Created: {$user['created_at']}\n";
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "✗ ERROR querying users: " . $e->getMessage() . "\n";
    $errors[] = "Users table error: " . $e->getMessage();
}

echo "\n";

// ==========================================
// 3. PASSWORD VERIFICATION TESTS
// ==========================================
echo "[3] PASSWORD VERIFICATION TESTS\n";
echo "-----------------------------------------------------------\n";

$test_credentials = [
    ['email' => 'admin@clinfec.com.br', 'password' => 'admin123', 'note' => 'V4 Report credentials'],
    ['email' => 'admin@clinfec.com', 'password' => 'admin123', 'note' => 'V4 Report (no .br)'],
    ['email' => 'master@clinfec.com.br', 'password' => 'password', 'note' => 'Sprint 15 credentials'],
    ['email' => 'admin@clinfec.com.br', 'password' => 'password', 'note' => 'Sprint 15 admin'],
    ['email' => 'gestor@clinfec.com.br', 'password' => 'password', 'note' => 'Sprint 15 gestor'],
];

foreach ($test_credentials as $cred) {
    echo "Testing: {$cred['email']} / {$cred['password']}\n";
    echo "  Note: {$cred['note']}\n";
    
    try {
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, perfil, ativo FROM usuarios WHERE email = ?");
        $stmt->execute([$cred['email']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "  ✓ User exists in database\n";
            echo "  Profile: {$user['perfil']}\n";
            echo "  Active: " . ($user['ativo'] ? 'YES' : 'NO') . "\n";
            
            if (password_verify($cred['password'], $user['senha'])) {
                echo "  ✓✓ PASSWORD VERIFIED!\n";
                $success[] = "Login OK: {$cred['email']} / {$cred['password']}";
            } else {
                echo "  ✗ Password does NOT verify\n";
                echo "  Hash starts with: " . substr($user['senha'], 0, 10) . "...\n";
                $warnings[] = "Password mismatch: {$cred['email']} / {$cred['password']}";
            }
        } else {
            echo "  ✗ User NOT FOUND in database\n";
            $warnings[] = "User not found: {$cred['email']}";
        }
    } catch (PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
        $errors[] = "Password test error: {$cred['email']}";
    }
    
    echo "\n";
}

echo "\n";

// ==========================================
// 4. TABLES STRUCTURE
// ==========================================
echo "[4] DATABASE TABLES STRUCTURE\n";
echo "-----------------------------------------------------------\n";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Total tables: " . count($tables) . "\n\n";
    
    $expected_tables = [
        'usuarios', 'empresas_tomadoras', 'empresas_prestadoras', 
        'contratos', 'servicos', 'projetos', 'atividades',
        'notas_fiscais', 'financeiro', 'migrations'
    ];
    
    echo "Critical Tables Check:\n";
    foreach ($expected_tables as $table) {
        if (in_array($table, $tables)) {
            echo "  ✓ $table\n";
        } else {
            echo "  ✗ $table MISSING\n";
            $errors[] = "Table missing: $table";
        }
    }
    
    echo "\nAll Tables:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$table`");
        $count = $stmt->fetch();
        echo "  - $table ({$count['total']} records)\n";
    }
    
} catch (PDOException $e) {
    echo "✗ ERROR checking tables: " . $e->getMessage() . "\n";
    $errors[] = "Tables structure error: " . $e->getMessage();
}

echo "\n";

// ==========================================
// 5. MIGRATIONS STATUS
// ==========================================
echo "[5] MIGRATIONS STATUS\n";
echo "-----------------------------------------------------------\n";

try {
    $stmt = $pdo->query("
        SELECT * FROM migrations 
        ORDER BY version DESC 
        LIMIT 10
    ");
    $migrations = $stmt->fetchAll();
    
    if (empty($migrations)) {
        echo "⚠️  No migrations found in database\n";
        $warnings[] = "No migrations records found";
    } else {
        echo "Latest Migrations:\n";
        foreach ($migrations as $mig) {
            echo "  Version {$mig['version']}: {$mig['description']}\n";
            echo "    Applied: {$mig['applied_at']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Migrations table error: " . $e->getMessage() . "\n";
    $errors[] = "Migrations check failed: " . $e->getMessage();
}

echo "\n";

// ==========================================
// 6. MODELS LOADING TEST
// ==========================================
echo "[6] MODELS LOADING TEST\n";
echo "-----------------------------------------------------------\n";

// Setup autoloader
require_once __DIR__ . '/src/Database.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

$critical_models = [
    'Usuario', 'EmpresaTomadora', 'EmpresaPrestadora', 
    'Contrato', 'Servico', 'Projeto', 'Atividade', 
    'NotaFiscal', 'DatabaseMigration'
];

echo "Testing Critical Models:\n";
foreach ($critical_models as $model_name) {
    $class_name = "App\\Models\\$model_name";
    
    try {
        if (class_exists($class_name)) {
            $model = new $class_name();
            echo "  ✓ $model_name instantiated OK\n";
            $success[] = "Model OK: $model_name";
        } else {
            echo "  ✗ $model_name class not found\n";
            $errors[] = "Model not found: $model_name";
        }
    } catch (Throwable $e) {
        echo "  ✗ $model_name ERROR: " . $e->getMessage() . "\n";
        $errors[] = "Model instantiation error: $model_name - " . $e->getMessage();
    }
}

echo "\n";

// ==========================================
// 7. CONTROLLERS EXISTENCE
// ==========================================
echo "[7] CONTROLLERS EXISTENCE\n";
echo "-----------------------------------------------------------\n";

$critical_controllers = [
    'AuthController', 'EmpresaTomadoraController', 'EmpresaPrestadoraController',
    'ContratoController', 'ServicoController', 'ProjetoController',
    'AtividadeController', 'FinanceiroController', 'NotaFiscalController'
];

echo "Checking Controllers:\n";
foreach ($critical_controllers as $controller) {
    $file = __DIR__ . "/src/Controllers/$controller.php";
    if (file_exists($file)) {
        $size = filesize($file);
        echo "  ✓ $controller ($size bytes)\n";
        $success[] = "Controller exists: $controller";
    } else {
        echo "  ✗ $controller MISSING\n";
        $errors[] = "Controller missing: $controller";
    }
}

echo "\n";

// ==========================================
// 8. ROUTES CONFIGURATION
// ==========================================
echo "[8] ROUTES CONFIGURATION\n";
echo "-----------------------------------------------------------\n";

$index_file = __DIR__ . '/public/index.php';
if (file_exists($index_file)) {
    echo "✓ public/index.php exists\n";
    
    $content = file_get_contents($index_file);
    
    // Check BASE_URL
    if (preg_match('/define\s*\(\s*[\'"]BASE_URL[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]/', $content, $matches)) {
        $base_url = $matches[1];
        echo "  BASE_URL: '$base_url'\n";
        if ($base_url === '') {
            echo "  ✓ BASE_URL correctly empty\n";
            $success[] = "BASE_URL correct";
        } else {
            echo "  ⚠️  BASE_URL not empty (should be '')\n";
            $warnings[] = "BASE_URL is '$base_url' instead of empty";
        }
    }
    
    // Check critical routes
    $critical_routes = ['projetos', 'atividades', 'financeiro', 'notas-fiscais', 'empresas-tomadoras'];
    echo "\n  Critical Routes:\n";
    foreach ($critical_routes as $route) {
        if (strpos($content, "case '$route':") !== false) {
            echo "    ✓ /$route defined\n";
        } else {
            echo "    ✗ /$route NOT FOUND\n";
            $errors[] = "Route not found: /$route";
        }
    }
    
} else {
    echo "✗ public/index.php MISSING\n";
    $errors[] = "Main routing file missing";
}

echo "\n";

// ==========================================
// 9. FILE PERMISSIONS
// ==========================================
echo "[9] FILE PERMISSIONS\n";
echo "-----------------------------------------------------------\n";

$critical_dirs = ['src', 'config', 'public', 'database'];
echo "Checking Critical Directories:\n";
foreach ($critical_dirs as $dir) {
    $path = __DIR__ . "/$dir";
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "  ✓ /$dir ($perms)\n";
    } else {
        echo "  ✗ /$dir MISSING\n";
        $errors[] = "Directory missing: /$dir";
    }
}

echo "\n";

// ==========================================
// 10. PHP CONFIGURATION
// ==========================================
echo "[10] PHP CONFIGURATION\n";
echo "-----------------------------------------------------------\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "OPcache Enabled: " . (ini_get('opcache.enable') ? 'YES' : 'NO') . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";

echo "\n";

// ==========================================
// SUMMARY
// ==========================================
echo "=========================================================\n";
echo "DIAGNOSTIC SUMMARY\n";
echo "=========================================================\n\n";

echo "✓ SUCCESS (" . count($success) . " items):\n";
foreach ($success as $item) {
    echo "  - $item\n";
}
echo "\n";

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (" . count($warnings) . " items):\n";
    foreach ($warnings as $item) {
        echo "  - $item\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "✗ ERRORS (" . count($errors) . " items):\n";
    foreach ($errors as $item) {
        echo "  - $item\n";
    }
    echo "\n";
}

// Calculate status
$total_checks = count($success) + count($warnings) + count($errors);
$health_score = ($total_checks > 0) ? round((count($success) / $total_checks) * 100, 1) : 0;

echo "SYSTEM HEALTH SCORE: $health_score%\n\n";

if ($health_score >= 90) {
    echo "STATUS: 🟢 EXCELLENT\n";
} elseif ($health_score >= 70) {
    echo "STATUS: 🟡 GOOD (needs improvement)\n";
} elseif ($health_score >= 50) {
    echo "STATUS: 🟠 FAIR (multiple issues)\n";
} else {
    echo "STATUS: 🔴 POOR (critical issues)\n";
}

echo "\n=========================================================\n";
echo "DIAGNOSTIC COMPLETE\n";
echo "=========================================================\n";
