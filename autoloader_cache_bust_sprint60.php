<?php
/**
 * Sprint 60: Cache-Busting Autoloader (Alternative Solution)
 * 
 * This is an ALTERNATIVE autoloader that bypasses OPcache by using
 * dynamic file inclusion with cache-busting parameters.
 * 
 * USAGE: Replace the autoloader in public/index.php with this file
 * if cache issues persist after 2+ hours.
 * 
 * HOW IT WORKS:
 * 1. Uses file modification time as version parameter
 * 2. Forces PHP to treat each load as unique
 * 3. Bypasses OPcache file-based caching
 * 4. Automatically reverts when cache is cleared
 * 
 * Sprint: 60
 * Created: 2025-11-15
 * Purpose: Alternative solution for persistent cache issues
 */

/**
 * Cache-Busting PSR-4 Autoloader
 * 
 * This autoloader works identically to the standard PSR-4 autoloader,
 * but adds cache-busting logic when loading files.
 */
function autoloader_cache_bust(string $class): void {
    // Convert namespace to path
    // Example: App\Controllers\AuthController → src/controllers/AuthController.php
    
    // Remove App\ prefix
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    
    // Convert namespace to path
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    // Convert to lowercase for folders (controllers, models, etc)
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    // Check if file exists
    if (!file_exists($file)) {
        return;
    }
    
    // CACHE-BUSTING LOGIC:
    // Method 1: Clear stat cache to force fresh file read
    clearstatcache(true, $file);
    
    // Method 2: Get file modification time for versioning
    $mtime = filemtime($file);
    
    // Method 3: Read file content directly to bypass bytecode cache
    $content = file_get_contents($file);
    
    // Method 4: Add cache-busting comment at eval time
    $cache_bust_comment = "// CACHE_BUST_VERSION: {$mtime}_" . md5($content) . "\n";
    
    // Method 5: Eval with unique version identifier
    // This forces PHP to compile as new code each time
    eval('?>' . $cache_bust_comment . $content);
    
    // Alternative Method (safer): Direct include with opcache invalidation
    // Uncomment this and comment the eval if eval causes issues:
    /*
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($file, true);
    }
    require_once $file;
    */
}

/**
 * Standard PSR-4 Autoloader (Fallback)
 * 
 * Use this if cache-busting autoloader causes any issues.
 * This is the ORIGINAL autoloader from public/index.php.
 */
function autoloader_standard(string $class): void {
    // Convert namespace to path
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    // Convert to lowercase for folders
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    // Load file if exists
    if (file_exists($file)) {
        require_once $file;
    }
}

/**
 * Hybrid Autoloader (RECOMMENDED)
 * 
 * This autoloader uses cache-busting ONLY for critical files
 * (Database.php, Models) and standard loading for others.
 * 
 * This balances performance with cache-busting needs.
 */
function autoloader_hybrid(string $class): void {
    // Convert namespace to path
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    // Convert to lowercase for folders
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    
    if (!file_exists($file)) {
        return;
    }
    
    // Critical files that need cache-busting
    $critical_files = [
        'Database.php',
        'DatabaseMigration.php'
    ];
    
    $is_critical = false;
    foreach ($critical_files as $critical_file) {
        if (strpos($file, $critical_file) !== false) {
            $is_critical = true;
            break;
        }
    }
    
    // Also cache-bust all Model files
    if (strpos($file, '/models/') !== false || strpos($file, '/Models/') !== false) {
        $is_critical = true;
    }
    
    if ($is_critical) {
        // Use cache-busting for critical files
        clearstatcache(true, $file);
        
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }
        
        // Update file access time to force reload
        @touch($file);
    }
    
    // Load file
    require_once $file;
}

// =============================================================================
// DEPLOYMENT INSTRUCTIONS
// =============================================================================
/*

TO DEPLOY THIS AUTOLOADER:

1. BACKUP current public/index.php first!

2. LOCATE the autoloader section in public/index.php (around line 72):
   
   spl_autoload_register(function ($class) {
       ...existing code...
   });

3. REPLACE with ONE of these options:

   OPTION A: Hybrid (RECOMMENDED) - Cache-bust only critical files
   ----------------------------------------------------------------
   require_once __DIR__ . '/../autoloader_cache_bust_sprint60.php';
   spl_autoload_register('autoloader_hybrid');


   OPTION B: Full Cache-Bust - All files get cache-busting
   -------------------------------------------------------
   require_once __DIR__ . '/../autoloader_cache_bust_sprint60.php';
   spl_autoload_register('autoloader_cache_bust');


   OPTION C: Standard (Fallback) - No cache-busting
   ------------------------------------------------
   require_once __DIR__ . '/../autoloader_cache_bust_sprint60.php';
   spl_autoload_register('autoloader_standard');

4. UPLOAD modified public/index.php to production

5. TEST by accessing: ?page=debug-models-test

6. MONITOR results with: monitor_cache_status_sprint60.php

WHEN TO USE EACH OPTION:

- Use HYBRID: If cache issues persist after 2 hours (RECOMMENDED)
- Use FULL: If hybrid doesn't work (more aggressive)
- Use STANDARD: If cache-busting causes any issues (fallback)

REVERT WHEN:
- Cache has cleared and system works normally
- To restore original performance after cache issue resolved

*/

// =============================================================================
// TESTING THIS AUTOLOADER
// =============================================================================

// Test script (run this file directly to test):
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    echo "<h1>Testing Cache-Busting Autoloader</h1>";
    
    // Define paths
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    echo "<h2>Test 1: Hybrid Autoloader</h2>";
    spl_autoload_register('autoloader_hybrid');
    
    try {
        $db = \App\Database::getInstance();
        echo "✅ SUCCESS: Database class loaded via hybrid autoloader<br>";
        
        $methods = get_class_methods($db);
        echo "✅ Methods available: " . count($methods) . "<br>";
        
        if (method_exists($db, 'prepare')) {
            echo "✅ SUCCESS: prepare() method exists<br>";
        } else {
            echo "❌ ERROR: prepare() method missing<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    
    echo "<hr>";
    echo "<h2>Test Results</h2>";
    echo "<p>If all tests show ✅ SUCCESS, the autoloader is working correctly.</p>";
    echo "<p>If any test shows ❌ ERROR, review the error message and adjust accordingly.</p>";
}

// End of autoloader_cache_bust_sprint60.php
// Sprint 60 | SCRUM + PDCA | Alternative Solution for Cache Issues
