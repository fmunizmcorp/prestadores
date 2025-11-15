#!/usr/bin/env python3
"""
Create a working index.php that handles errors properly
Sprint 33 - Make system work NOW through index.php
"""

import ftplib
import sys
from io import BytesIO
import time

# Configurações FTP
FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'timeout': 60
}

# Fixed index.php that WILL work
WORKING_INDEX = """<?php
/**
 * Clinfec Prestadores - Front Controller - FIXED Sprint 33
 * This version handles ALL errors gracefully
 */

// ==================== ERROR HANDLING ====================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $message = "ERROR [$errno]: $errstr in $errfile on line $errline";
    error_log($message);
    if (error_reporting() & $errno) {
        echo "<div style='background:#ffebee;border-left:4px solid #f44336;padding:10px;margin:10px 0'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<small>File: $errfile Line: $errline</small>";
        echo "</div>";
    }
    return true;
});

// Custom exception handler
set_exception_handler(function($e) {
    $message = "EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
    error_log($message);
    echo "<div style='background:#ffebee;border-left:4px solid #f44336;padding:10px;margin:10px 0'>";
    echo "<strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<small>File: " . htmlspecialchars($e->getFile()) . " Line: " . $e->getLine() . "</small>";
    echo "</div>";
});

// ==================== CACHE CONTROL ====================
// Inline cache control to avoid require issues
if (function_exists('opcache_reset')) {
    try {
        opcache_reset();
    } catch (\\Throwable $e) {
        // Silently fail - not critical
    }
}
clearstatcache(true);

// ==================== SESSION ====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== CONFIGURAÇÕES ====================
date_default_timezone_set('America/Sao_Paulo');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', __DIR__);
define('BASE_URL', '/prestadores');

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==================== AUTOLOADER ====================
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\\\') === 0) {
        $class = substr($class, 4);
    }
    
    $file = SRC_PATH . '/' . str_replace('\\\\', '/', $class) . '.php';
    
    // Convert Controllers, Models, etc to lowercase
    $file = preg_replace_callback('/\\/([A-Z][a-z]+)\\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// ==================== LOAD CONFIGS ====================
$config = [];
$dbConfig = [];

// Try to load config files
if (file_exists(CONFIG_PATH . '/config.php')) {
    try {
        $config = require CONFIG_PATH . '/config.php';
    } catch (\\Throwable $e) {
        echo "<div style='background:#fff3cd;padding:10px;margin:10px 0'>";
        echo "Warning: Could not load config.php: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

if (file_exists(CONFIG_PATH . '/database.php')) {
    try {
        $dbConfig = require CONFIG_PATH . '/database.php';
    } catch (\\Throwable $e) {
        echo "<div style='background:#fff3cd;padding:10px;margin:10px 0'>";
        echo "Warning: Could not load database.php: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

// Load Database class if exists
if (file_exists(SRC_PATH . '/Database.php')) {
    try {
        require_once SRC_PATH . '/Database.php';
    } catch (\\Throwable $e) {
        echo "<div style='background:#fff3cd;padding:10px;margin:10px 0'>";
        echo "Warning: Could not load Database.php: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

// ==================== ROUTING ====================
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Debug mode
if ($page === 'debug-info') {
    echo "<!DOCTYPE html><html><head><title>Debug Info</title></head><body>";
    echo "<h1>System Debug Information</h1>";
    echo "<pre>";
    echo "PHP Version: " . PHP_VERSION . "\\n";
    echo "Current Time: " . date('Y-m-d H:i:s') . "\\n";
    echo "ROOT_PATH: " . ROOT_PATH . "\\n";
    echo "CONFIG_PATH: " . CONFIG_PATH . "\\n";
    echo "SRC_PATH: " . SRC_PATH . "\\n";
    echo "BASE_URL: " . BASE_URL . "\\n\\n";
    
    echo "Directories:\\n";
    echo "  config/ exists: " . (is_dir(CONFIG_PATH) ? 'YES' : 'NO') . "\\n";
    echo "  src/ exists: " . (is_dir(SRC_PATH) ? 'YES' : 'NO') . "\\n";
    echo "  public/ exists: " . (is_dir(PUBLIC_PATH) ? 'YES' : 'NO') . "\\n\\n";
    
    echo "Config loaded: " . (is_array($config) ? 'YES' : 'NO') . "\\n";
    echo "DB Config loaded: " . (is_array($dbConfig) ? 'YES' : 'NO') . "\\n\\n";
    
    echo "Request:\\n";
    echo "  page: $page\\n";
    echo "  action: $action\\n";
    echo "  id: $id\\n";
    echo "</pre></body></html>";
    exit;
}

// ==================== AUTH CHECK ====================
$isLoggedIn = isset($_SESSION['user_id']);
$publicPages = ['login', 'logout', 'debug-info'];

if (!$isLoggedIn && !in_array($page, $publicPages)) {
    $page = 'login';
}

// ==================== CONTROLLER DISPATCH ====================
try {
    // Route to appropriate controller
    switch ($page) {
        case 'login':
            require_once SRC_PATH . '/Controllers/AuthController.php';
            $controller = new \\App\\Controllers\\AuthController();
            if ($action === 'logout') {
                $controller->logout();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLoginForm();
            }
            break;
            
        case 'dashboard':
            require_once SRC_PATH . '/Controllers/DashboardController.php';
            $controller = new \\App\\Controllers\\DashboardController();
            $controller->index();
            break;
            
        default:
            // Generic controller loading
            $controllerName = ucfirst($page) . 'Controller';
            $controllerPath = SRC_PATH . '/Controllers/' . $controllerName . '.php';
            
            if (file_exists($controllerPath)) {
                require_once $controllerPath;
                $controllerClass = "\\\\App\\\\Controllers\\\\$controllerName";
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    
                    if (method_exists($controller, $action)) {
                        if ($id) {
                            $controller->$action($id);
                        } else {
                            $controller->$action();
                        }
                    } else {
                        throw new \\Exception("Action '$action' not found in $controllerName");
                    }
                } else {
                    throw new \\Exception("Controller class $controllerClass not found");
                }
            } else {
                throw new \\Exception("Controller file not found: $controllerPath");
            }
            break;
    }
    
} catch (\\Throwable $e) {
    // Show error page
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<div style='max-width:800px;margin:50px auto;padding:20px'>";
    echo "<h1 style='color:#f44336'>Application Error</h1>";
    echo "<div style='background:#ffebee;border-left:4px solid #f44336;padding:15px;margin:20px 0'>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<small><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "</small>";
    echo "</div>";
    echo "<a href='?page=debug-info' style='color:#2196F3'>View System Debug Info</a>";
    echo "</div></body></html>";
}
"""

def download_file(ftp, path):
    """Download file"""
    try:
        buffer = BytesIO()
        ftp.retrbinary(f'RETR {path}', buffer.write)
        return buffer.getvalue().decode('utf-8', errors='replace')
    except:
        return None

def upload_file(ftp, path, content):
    """Upload file"""
    try:
        buffer = BytesIO(content.encode('utf-8'))
        ftp.storbinary(f'STOR {path}', buffer)
        return True
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def main():
    print("=" * 70)
    print("DEPLOY FIXED INDEX.PHP - WORKING VERSION")
    print("Sprint 33 - Make System Work NOW")
    print("=" * 70)
    
    try:
        # Connect
        print("\n1️⃣ Connecting...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        ftp.cwd('/public_html/prestadores')
        print(f"✅ Connected to {ftp.pwd()}")
        
        # Backup current
        print("\n2️⃣ Backing up current index.php...")
        current = download_file(ftp, 'index.php')
        if current:
            backup_name = f'index.php.backup_working_{int(time.time())}'
            if upload_file(ftp, backup_name, current):
                print(f"✅ Backup: {backup_name}")
        
        # Upload fixed version
        print("\n3️⃣ Uploading WORKING index.php...")
        if upload_file(ftp, 'index.php', WORKING_INDEX):
            print(f"✅ Uploaded ({len(WORKING_INDEX)} bytes)")
        else:
            ftp.quit()
            return 1
        
        ftp.quit()
        
        print("\n" + "=" * 70)
        print("4️⃣ TEST NOW!")
        print("=" * 70)
        
        print("\n✅ Fixed index.php deployed!")
        print("\n📝 Test URLs:")
        print("   https://clinfec.com.br/prestadores/")
        print("   https://clinfec.com.br/prestadores/?page=debug-info")
        print("   https://clinfec.com.br/prestadores/?page=login")
        print("\n💡 Should work now!")
        print("   - If error: Shows detailed error message")
        print("   - If success: Shows login page or dashboard")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
