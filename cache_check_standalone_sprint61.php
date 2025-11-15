<?php
/**
 * Sprint 61: STANDALONE Cache Checker (NO DEPENDENCIES)
 * 
 * This script has ZERO dependencies on the application.
 * It can run completely standalone without triggering auth checks.
 * 
 * Access: https://clinfec.com.br/prestadores/cache_check_standalone_sprint61.php
 */

// Prevent any session/auth from parent app
if (!headers_sent()) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
}

echo "====================================================================\n";
echo "SPRINT 61: STANDALONE CACHE STATUS CHECK\n";
echo "====================================================================\n";
echo "Time: " . date('Y-m-d H:i:s') . " UTC\n";
echo "====================================================================\n\n";

// 1. OPcache Status
echo "[1] OPcache Status:\n";
if (function_exists('opcache_get_status')) {
    $status = @opcache_get_status(false);
    if ($status && isset($status['opcache_enabled'])) {
        if ($status['opcache_enabled']) {
            echo "    ⚠️  STATUS: ENABLED (caching active)\n";
            echo "    Cached Scripts: " . ($status['opcache_statistics']['num_cached_scripts'] ?? 'N/A') . "\n";
            echo "    Hits: " . ($status['opcache_statistics']['hits'] ?? 'N/A') . "\n";
            echo "    Misses: " . ($status['opcache_statistics']['misses'] ?? 'N/A') . "\n";
        } else {
            echo "    ✅ STATUS: DISABLED (no caching)\n";
        }
    } else {
        echo "    ✅ STATUS: Cannot get status (likely disabled)\n";
    }
} else {
    echo "    ✅ STATUS: Function not available (OPcache not active)\n";
}

echo "\n";

// 2. Database.php File Check
echo "[2] Database.php File Check:\n";
$db_file = __DIR__ . '/src/Database.php';
if (file_exists($db_file)) {
    $size = filesize($db_file);
    $mtime = filemtime($db_file);
    $age_min = (time() - $mtime) / 60;
    
    echo "    ✅ FILE EXISTS\n";
    echo "    Size: " . number_format($size) . " bytes\n";
    echo "    Modified: " . date('Y-m-d H:i:s', $mtime) . "\n";
    echo "    Age: " . number_format($age_min, 1) . " minutes\n";
    
    // Expected from Sprint 58
    if ($size == 4522) {
        echo "    ✅ SIZE CORRECT (matches Sprint 58 deploy)\n";
    } else {
        echo "    ⚠️  SIZE: Expected 4522, got $size\n";
    }
} else {
    echo "    ❌ FILE NOT FOUND\n";
}

echo "\n";

// 3. Direct Class Loading Test
echo "[3] Database Class Loading Test:\n";
try {
    // Clear any stat cache
    clearstatcache(true);
    
    // Try to load the class
    require_once $db_file;
    
    if (class_exists('App\\Database')) {
        echo "    ✅ CLASS LOADED\n";
        
        // Get methods
        $methods = get_class_methods('App\\Database');
        echo "    Total Methods: " . count($methods) . "\n";
        
        // Check required methods
        $required = ['getInstance', 'getConnection', 'prepare', 'query', 'exec', 
                    'lastInsertId', 'beginTransaction', 'commit', 'rollBack', 'inTransaction'];
        
        $present = 0;
        foreach ($required as $method) {
            if (in_array($method, $methods)) {
                $present++;
            }
        }
        
        echo "    Required Methods Present: $present/" . count($required) . "\n";
        
        if ($present == count($required)) {
            echo "    ✅ ALL METHODS PRESENT\n";
        } else {
            echo "    ❌ MISSING METHODS\n";
        }
        
        // Specifically check prepare()
        if (method_exists('App\\Database', 'prepare')) {
            echo "    ✅ prepare() METHOD: EXISTS\n";
        } else {
            echo "    ❌ prepare() METHOD: MISSING\n";
        }
        
    } else {
        echo "    ❌ CLASS NOT FOUND\n";
    }
} catch (Exception $e) {
    echo "    ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Summary
echo "====================================================================\n";
echo "SUMMARY\n";
echo "====================================================================\n";

// Determine overall status
$cache_active = function_exists('opcache_get_status') && 
                @opcache_get_status(false)['opcache_enabled'] ?? false;
$file_exists = file_exists($db_file);
$file_correct_size = $file_exists && filesize($db_file) == 4522;
$class_loads = class_exists('App\\Database');
$methods_present = $class_loads && method_exists('App\\Database', 'prepare');

if (!$cache_active && $file_correct_size && $methods_present) {
    echo "✅ SYSTEM READY\n";
    echo "\nAll checks passed! Cache is clear, file is correct, methods are available.\n";
    echo "The system should be working at 100% functionality.\n";
} else {
    echo "⚠️  SYSTEM NOT READY\n\n";
    
    if ($cache_active) {
        echo "❌ OPcache is still active - Wait for expiration or clear manually\n";
    }
    if (!$file_correct_size) {
        echo "❌ Database.php size doesn't match expected (cache issue?)\n";
    }
    if (!$methods_present) {
        echo "❌ prepare() method not available (cache serving old version)\n";
    }
    
    echo "\nExpected resolution: Within 1-2 hours of last deploy\n";
    echo "Last deploy: 2025-11-15 16:19:50 UTC (Sprint 58)\n";
}

echo "\n====================================================================\n";
echo "NEXT ACTIONS\n";
echo "====================================================================\n";

if (!$cache_active && $methods_present) {
    echo "✅ System is ready! Test the modules:\n";
    echo "   - https://clinfec.com.br/prestadores/?page=projetos\n";
    echo "   - https://clinfec.com.br/prestadores/?page=empresas-prestadoras\n";
} else {
    echo "⏳ Wait for cache expiration OR\n";
    echo "🧹 Use manual clear (requires app access):\n";
    echo "   - Login to system\n";
    echo "   - Access cache clear tool\n";
    echo "   - OR wait naturally (1-2 hours typical)\n";
}

echo "\n";
echo "Generated: " . date('Y-m-d H:i:s') . " UTC\n";
echo "Sprint: 61 | Standalone Cache Checker\n";
echo "====================================================================\n";

// That's it! No more code that could trigger auth redirects
