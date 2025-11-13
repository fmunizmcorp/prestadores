<?php
/**
 * SPRINT 20 - DIAGNOSTIC SCRIPT
 * Identify EXACTLY why pages render blank
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/diagnostic_errors.log');

echo "<!DOCTYPE html><html><head><title>Sprint 20 Diagnostic</title>";
echo "<style>body{font-family:monospace;padding:20px;} h2{color:#333;border-bottom:2px solid #007bff;} pre{background:#f5f5f5;padding:10px;border-left:3px solid #007bff;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";

echo "<h1>🔍 SPRINT 20 - DIAGNÓSTICO COMPLETO</h1>";
echo "<p>Testando EXATAMENTE por que páginas renderizam em branco</p><hr>";

$results = [];
$critical_errors = [];

// TEST 1: PHP Configuration
echo "<h2>1️⃣ PHP Configuration</h2>";
try {
    $results['php_version'] = phpversion();
    $results['display_errors'] = ini_get('display_errors');
    $results['error_reporting'] = error_reporting();
    $results['memory_limit'] = ini_get('memory_limit');
    
    echo "<pre class='success'>";
    echo "✅ PHP Version: " . $results['php_version'] . "\n";
    echo "✅ Display Errors: " . $results['display_errors'] . "\n";
    echo "✅ Error Reporting: " . $results['error_reporting'] . "\n";
    echo "✅ Memory Limit: " . $results['memory_limit'] . "\n";
    echo "</pre>";
} catch (Exception $e) {
    echo "<pre class='error'>❌ Error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "PHP Config: " . $e->getMessage();
}

// TEST 2: File System
echo "<h2>2️⃣ File System Access</h2>";
$critical_files = [
    'vendor/autoload.php',
    'config/config.php',
    'config/database.php',
    'public/index.php',
    'src/Controllers/AuthController.php',
    'src/Controllers/EmpresaTomadoraController.php',
    'src/Models/Usuario.php'
];

$file_status = [];
foreach ($critical_files as $file) {
    $exists = file_exists($file);
    $readable = $exists ? is_readable($file) : false;
    $file_status[$file] = ['exists' => $exists, 'readable' => $readable];
    
    $icon = ($exists && $readable) ? '✅' : '❌';
    $class = ($exists && $readable) ? 'success' : 'error';
    echo "<pre class='$class'>$icon $file - ";
    echo ($exists ? "EXISTS" : "NOT FOUND");
    echo ($exists && $readable ? " (readable)" : " (NOT READABLE)");
    echo "</pre>";
    
    if (!$exists || !$readable) {
        $critical_errors[] = "File missing/unreadable: $file";
    }
}

// TEST 3: Autoloader
echo "<h2>3️⃣ Composer Autoloader</h2>";
try {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "<pre class='success'>✅ Autoloader loaded successfully</pre>";
        $results['autoloader'] = true;
    } else {
        echo "<pre class='error'>❌ vendor/autoload.php NOT FOUND</pre>";
        $critical_errors[] = "Autoloader missing";
        $results['autoloader'] = false;
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Autoloader error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "Autoloader: " . $e->getMessage();
    $results['autoloader'] = false;
}

// TEST 4: Config Files
echo "<h2>4️⃣ Configuration Files</h2>";
try {
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "<pre class='success'>✅ config.php loaded</pre>";
        echo "<pre>BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "</pre>";
        echo "<pre>APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "</pre>";
        $results['config'] = true;
    } else {
        echo "<pre class='error'>❌ config/config.php NOT FOUND</pre>";
        $critical_errors[] = "config.php missing";
        $results['config'] = false;
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Config error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "Config: " . $e->getMessage();
    $results['config'] = false;
}

// TEST 5: Database Connection
echo "<h2>5️⃣ Database Connection</h2>";
try {
    if (file_exists('config/database.php')) {
        $dbConfig = require 'config/database.php';
        
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
        
        echo "<pre class='success'>✅ Database connection successful</pre>";
        echo "<pre>Host: {$dbConfig['host']}</pre>";
        echo "<pre>Database: {$dbConfig['database']}</pre>";
        
        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
        $result = $stmt->fetch();
        echo "<pre class='success'>✅ Usuarios table: {$result['count']} records</pre>";
        
        $results['database'] = true;
    } else {
        echo "<pre class='error'>❌ config/database.php NOT FOUND</pre>";
        $critical_errors[] = "database.php missing";
        $results['database'] = false;
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Database error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "Database: " . $e->getMessage();
    $results['database'] = false;
}

// TEST 6: Controller Loading
echo "<h2>6️⃣ Controller Class Loading</h2>";
try {
    if (class_exists('App\Controllers\AuthController')) {
        echo "<pre class='success'>✅ AuthController class exists</pre>";
        $results['auth_controller'] = true;
    } else {
        echo "<pre class='error'>❌ AuthController class NOT FOUND</pre>";
        $critical_errors[] = "AuthController class missing";
        $results['auth_controller'] = false;
    }
    
    if (class_exists('App\Controllers\EmpresaTomadoraController')) {
        echo "<pre class='success'>✅ EmpresaTomadoraController class exists</pre>";
        $results['tomadora_controller'] = true;
    } else {
        echo "<pre class='error'>❌ EmpresaTomadoraController class NOT FOUND</pre>";
        $critical_errors[] = "EmpresaTomadoraController class missing";
        $results['tomadora_controller'] = false;
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Controller loading error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "Controller loading: " . $e->getMessage();
}

// TEST 7: Model Loading
echo "<h2>7️⃣ Model Class Loading</h2>";
try {
    if (class_exists('App\Models\Usuario')) {
        echo "<pre class='success'>✅ Usuario model class exists</pre>";
        $results['usuario_model'] = true;
    } else {
        echo "<pre class='error'>❌ Usuario model class NOT FOUND</pre>";
        $critical_errors[] = "Usuario model class missing";
        $results['usuario_model'] = false;
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Model loading error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "Model loading: " . $e->getMessage();
}

// TEST 8: Session
echo "<h2>8️⃣ Session Management</h2>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<pre class='success'>✅ Session started: " . session_id() . "</pre>";
    echo "<pre>Session data: " . print_r($_SESSION, true) . "</pre>";
    $results['session'] = true;
} catch (Exception $e) {
    echo "<pre class='error'>❌ Session error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "Session: " . $e->getMessage();
    $results['session'] = false;
}

// TEST 9: Router Test
echo "<h2>9️⃣ Router Simulation</h2>";
try {
    $_GET['page'] = 'empresas-tomadoras';
    $_GET['action'] = 'index';
    
    $page = $_GET['page'] ?? 'dashboard';
    $action = $_GET['action'] ?? 'index';
    
    echo "<pre class='success'>✅ Router variables set:</pre>";
    echo "<pre>   page = $page</pre>";
    echo "<pre>   action = $action</pre>";
    
    // Try to instantiate controller
    $controllerClass = 'App\\Controllers\\' . str_replace('-', '', ucwords($page, '-')) . 'Controller';
    echo "<pre>   Expected controller: $controllerClass</pre>";
    
    if (class_exists($controllerClass)) {
        echo "<pre class='success'>✅ Controller class exists!</pre>";
        $results['controller_instantiation'] = true;
    } else {
        echo "<pre class='error'>❌ Controller class NOT FOUND: $controllerClass</pre>";
        $critical_errors[] = "Controller instantiation failed";
        $results['controller_instantiation'] = false;
    }
} catch (Exception $e) {
    echo "<pre class='error'>❌ Router error: " . $e->getMessage() . "</pre>";
    $critical_errors[] = "Router: " . $e->getMessage();
}

// SUMMARY
echo "<hr><h2>📊 DIAGNOSTIC SUMMARY</h2>";

$total_tests = count($results);
$passed_tests = count(array_filter($results, function($v) { return $v === true; }));
$success_rate = ($total_tests > 0) ? round(($passed_tests / $total_tests) * 100, 1) : 0;

echo "<pre><strong>Total Tests:</strong> $total_tests</pre>";
echo "<pre><strong>Passed:</strong> $passed_tests</pre>";
echo "<pre><strong>Failed:</strong> " . ($total_tests - $passed_tests) . "</pre>";
echo "<pre><strong>Success Rate:</strong> $success_rate%</pre>";

if (count($critical_errors) > 0) {
    echo "<h3 class='error'>🚨 CRITICAL ERRORS FOUND:</h3>";
    foreach ($critical_errors as $i => $error) {
        echo "<pre class='error'>" . ($i + 1) . ". $error</pre>";
    }
    echo "<hr>";
    echo "<h3>💡 RECOMMENDED ACTIONS:</h3>";
    echo "<pre>1. Fix all critical errors listed above</pre>";
    echo "<pre>2. Ensure vendor/autoload.php exists (run composer install)</pre>";
    echo "<pre>3. Verify database connection and tables</pre>";
    echo "<pre>4. Check file permissions</pre>";
    echo "<pre>5. Clear OPcache after fixes</pre>";
} else {
    echo "<h3 class='success'>✅ No critical errors found!</h3>";
    echo "<p>If pages still render blank, the problem is in the view rendering logic.</p>";
}

echo "<hr><p><small>Diagnostic completed at: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</body></html>";

// Save results to file
$log_file = __DIR__ . '/diagnostic_sprint20_results.json';
file_put_contents($log_file, json_encode([
    'timestamp' => time(),
    'date' => date('Y-m-d H:i:s'),
    'results' => $results,
    'critical_errors' => $critical_errors,
    'success_rate' => $success_rate
], JSON_PRETTY_PRINT));
?>
