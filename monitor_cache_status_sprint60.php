<?php
/**
 * Sprint 60: Advanced Cache Status Monitor
 * 
 * This script provides detailed OPcache status and Database.php loading info
 * Access: https://clinfec.com.br/prestadores/monitor_cache_status_sprint60.php
 * 
 * Sprint: 60
 * Purpose: Monitor cache status and verify Database.php loading
 * Created: 2025-11-15
 */

// Disable output buffering for immediate display
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint 60: Cache Status Monitor</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        h2 {
            color: #569cd6;
            margin-top: 30px;
        }
        .status-box {
            background: #252526;
            border-left: 4px solid #4ec9b0;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success {
            border-left-color: #4ec9b0;
        }
        .warning {
            border-left-color: #ce9178;
        }
        .error {
            border-left-color: #f48771;
        }
        .info {
            border-left-color: #569cd6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: #252526;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #3e3e42;
        }
        th {
            background: #2d2d30;
            color: #4ec9b0;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .badge-success {
            background: #4ec9b0;
            color: #1e1e1e;
        }
        .badge-error {
            background: #f48771;
            color: #1e1e1e;
        }
        .badge-warning {
            background: #ce9178;
            color: #1e1e1e;
        }
        .code-block {
            background: #1e1e1e;
            border: 1px solid #3e3e42;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .timestamp {
            color: #858585;
            font-size: 0.9em;
        }
        .reload-btn {
            background: #0e639c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 0;
        }
        .reload-btn:hover {
            background: #1177bb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Sprint 60: Cache Status Monitor</h1>
        <div class="timestamp">
            Generated: <?= date('Y-m-d H:i:s') ?> (<?= date('H:i:s') ?> UTC / <?= date('H:i:s', strtotime('-3 hours')) ?> BRT)
        </div>
        
        <button class="reload-btn" onclick="location.reload()">🔄 Reload Status</button>

        <?php
        // =================================================================
        // 1. PHP VERSION AND ENVIRONMENT
        // =================================================================
        ?>
        <h2>📌 PHP Environment</h2>
        <div class="status-box info">
            <table>
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><?= PHP_VERSION ?></td>
                </tr>
                <tr>
                    <td>Server Time</td>
                    <td><?= date('Y-m-d H:i:s') ?></td>
                </tr>
                <tr>
                    <td>Script Path</td>
                    <td><?= __FILE__ ?></td>
                </tr>
                <tr>
                    <td>Document Root</td>
                    <td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></td>
                </tr>
            </table>
        </div>

        <?php
        // =================================================================
        // 2. OPCACHE STATUS
        // =================================================================
        ?>
        <h2>🗄️ OPcache Status</h2>
        <?php
        $opcache_enabled = function_exists('opcache_get_status');
        
        if ($opcache_enabled) {
            $status = @opcache_get_status(false);
            $config = @opcache_get_configuration();
            
            if ($status) {
                $is_enabled = $status['opcache_enabled'] ?? false;
                
                if ($is_enabled) {
                    echo '<div class="status-box warning">';
                    echo '<span class="badge badge-warning">⚠️ ENABLED</span> ';
                    echo 'OPcache is ACTIVE - This may be caching old code';
                    echo '</div>';
                } else {
                    echo '<div class="status-box success">';
                    echo '<span class="badge badge-success">✅ DISABLED</span> ';
                    echo 'OPcache is disabled - Fresh code will load';
                    echo '</div>';
                }
                
                // Cache statistics
                echo '<table>';
                echo '<tr><th>Metric</th><th>Value</th></tr>';
                echo '<tr><td>Cached Scripts</td><td>' . ($status['opcache_statistics']['num_cached_scripts'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td>Hits</td><td>' . ($status['opcache_statistics']['hits'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td>Misses</td><td>' . ($status['opcache_statistics']['misses'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td>Memory Used</td><td>' . number_format(($status['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 2) . ' MB</td></tr>';
                echo '<tr><td>Memory Free</td><td>' . number_format(($status['memory_usage']['free_memory'] ?? 0) / 1024 / 1024, 2) . ' MB</td></tr>';
                
                if (isset($config['directives'])) {
                    echo '<tr><td>Revalidate Frequency</td><td>' . ($config['directives']['opcache.revalidate_freq'] ?? 'N/A') . ' seconds</td></tr>';
                    echo '<tr><td>Max Accelerated Files</td><td>' . ($config['directives']['opcache.max_accelerated_files'] ?? 'N/A') . '</td></tr>';
                }
                
                echo '</table>';
                
            } else {
                echo '<div class="status-box success">';
                echo '<span class="badge badge-success">✅ DISABLED</span> ';
                echo 'OPcache status unavailable - Likely disabled';
                echo '</div>';
            }
        } else {
            echo '<div class="status-box success">';
            echo '<span class="badge badge-success">✅ NOT AVAILABLE</span> ';
            echo 'OPcache functions not available';
            echo '</div>';
        }
        ?>

        <?php
        // =================================================================
        // 3. DATABASE.PHP FILE STATUS
        // =================================================================
        ?>
        <h2>📄 Database.php File Status</h2>
        <?php
        $database_file = __DIR__ . '/src/Database.php';
        
        if (file_exists($database_file)) {
            echo '<div class="status-box success">';
            echo '<span class="badge badge-success">✅ EXISTS</span> ';
            echo 'Database.php file found';
            echo '</div>';
            
            // File details
            $size = filesize($database_file);
            $modified = filemtime($database_file);
            $md5 = md5_file($database_file);
            
            echo '<table>';
            echo '<tr><th>Property</th><th>Value</th></tr>';
            echo '<tr><td>Path</td><td>' . htmlspecialchars($database_file) . '</td></tr>';
            echo '<tr><td>Size</td><td>' . number_format($size) . ' bytes</td></tr>';
            echo '<tr><td>Modified</td><td>' . date('Y-m-d H:i:s', $modified) . '</td></tr>';
            echo '<tr><td>MD5</td><td>' . $md5 . '</td></tr>';
            echo '<tr><td>Age</td><td>' . number_format((time() - $modified) / 60, 1) . ' minutes</td></tr>';
            echo '</table>';
            
            // Expected values from Sprint 58
            $expected_size = 4522; // Sprint 58 size
            $expected_min_age = 60; // Should be at least 60 minutes old (from Sprint 58 deploy)
            
            if ($size == $expected_size) {
                echo '<div class="status-box success">';
                echo '<span class="badge badge-success">✅ CORRECT SIZE</span> ';
                echo "File size matches Sprint 58 deploy ($expected_size bytes)";
                echo '</div>';
            } else {
                echo '<div class="status-box warning">';
                echo '<span class="badge badge-warning">⚠️ SIZE MISMATCH</span> ';
                echo "Expected $expected_size bytes, found $size bytes";
                echo '</div>';
            }
            
        } else {
            echo '<div class="status-box error">';
            echo '<span class="badge badge-error">❌ NOT FOUND</span> ';
            echo 'Database.php file does not exist!';
            echo '</div>';
        }
        ?>

        <?php
        // =================================================================
        // 4. DATABASE CLASS LOADING TEST
        // =================================================================
        ?>
        <h2>🔧 Database Class Loading Test</h2>
        <?php
        try {
            // Clear any previous includes
            $included_before = get_included_files();
            
            // Try to load Database class
            require_once $database_file;
            
            // Check class exists
            if (class_exists('App\\Database')) {
                echo '<div class="status-box success">';
                echo '<span class="badge badge-success">✅ CLASS LOADED</span> ';
                echo 'App\\Database class loaded successfully';
                echo '</div>';
                
                // Get class methods
                $methods = get_class_methods('App\\Database');
                
                echo '<div class="code-block">';
                echo '<strong>Available Methods:</strong><br>';
                echo implode(', ', $methods);
                echo '</div>';
                
                // Check for required methods
                $required_methods = [
                    'getInstance',
                    'getConnection',
                    'prepare',
                    'query',
                    'exec',
                    'lastInsertId',
                    'beginTransaction',
                    'commit',
                    'rollBack',
                    'inTransaction'
                ];
                
                echo '<table>';
                echo '<tr><th>Method</th><th>Status</th></tr>';
                
                foreach ($required_methods as $method) {
                    $exists = method_exists('App\\Database', $method);
                    $badge = $exists ? 'badge-success' : 'badge-error';
                    $icon = $exists ? '✅' : '❌';
                    $status = $exists ? 'PRESENT' : 'MISSING';
                    
                    echo "<tr>";
                    echo "<td><code>$method()</code></td>";
                    echo "<td><span class='badge $badge'>$icon $status</span></td>";
                    echo "</tr>";
                }
                
                echo '</table>';
                
                // Count present methods
                $present_count = 0;
                foreach ($required_methods as $method) {
                    if (method_exists('App\\Database', $method)) {
                        $present_count++;
                    }
                }
                
                if ($present_count === count($required_methods)) {
                    echo '<div class="status-box success">';
                    echo '<span class="badge badge-success">✅ ALL METHODS PRESENT</span> ';
                    echo "All $present_count required methods are available!";
                    echo '</div>';
                } else {
                    echo '<div class="status-box error">';
                    echo '<span class="badge badge-error">❌ INCOMPLETE</span> ';
                    echo "$present_count/" . count($required_methods) . " methods present";
                    echo '</div>';
                }
                
                // Test prepare() specifically via reflection
                try {
                    $reflection = new ReflectionClass('App\\Database');
                    $prepare_method = $reflection->getMethod('prepare');
                    
                    echo '<div class="status-box success">';
                    echo '<span class="badge badge-success">✅ prepare() METHOD</span> ';
                    echo 'Verified via ReflectionClass';
                    echo '<div class="code-block">';
                    echo '<strong>Method Signature:</strong><br>';
                    echo 'public function prepare(string $sql): \\PDOStatement';
                    echo '</div>';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="status-box error">';
                    echo '<span class="badge badge-error">❌ REFLECTION ERROR</span> ';
                    echo htmlspecialchars($e->getMessage());
                    echo '</div>';
                }
                
            } else {
                echo '<div class="status-box error">';
                echo '<span class="badge badge-error">❌ CLASS NOT FOUND</span> ';
                echo 'App\\Database class could not be loaded';
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="status-box error">';
            echo '<span class="badge badge-error">❌ LOAD ERROR</span> ';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>

        <?php
        // =================================================================
        // 5. CACHED FILES LIST (if OPcache enabled)
        // =================================================================
        ?>
        <h2>📋 Cached Files (OPcache)</h2>
        <?php
        if ($opcache_enabled && $status && $is_enabled) {
            $cached_scripts = @opcache_get_status(true);
            
            if ($cached_scripts && isset($cached_scripts['scripts'])) {
                $database_in_cache = false;
                $database_cache_info = null;
                
                foreach ($cached_scripts['scripts'] as $script_path => $script_info) {
                    if (strpos($script_path, 'Database.php') !== false) {
                        $database_in_cache = true;
                        $database_cache_info = $script_info;
                        break;
                    }
                }
                
                if ($database_in_cache) {
                    echo '<div class="status-box warning">';
                    echo '<span class="badge badge-warning">⚠️ IN CACHE</span> ';
                    echo 'Database.php IS cached by OPcache';
                    
                    if ($database_cache_info) {
                        echo '<table>';
                        echo '<tr><th>Property</th><th>Value</th></tr>';
                        echo '<tr><td>Cached Script</td><td>' . htmlspecialchars($script_path) . '</td></tr>';
                        echo '<tr><td>Hits</td><td>' . ($database_cache_info['hits'] ?? 'N/A') . '</td></tr>';
                        echo '<tr><td>Last Used</td><td>' . date('Y-m-d H:i:s', $database_cache_info['last_used_timestamp']) . '</td></tr>';
                        echo '<tr><td>Memory</td><td>' . number_format($database_cache_info['memory_consumption']) . ' bytes</td></tr>';
                        echo '</table>';
                    }
                    
                    echo '</div>';
                } else {
                    echo '<div class="status-box success">';
                    echo '<span class="badge badge-success">✅ NOT CACHED</span> ';
                    echo 'Database.php is NOT in OPcache - Fresh version will load';
                    echo '</div>';
                }
                
                echo '<div class="info">';
                echo '<p>Total cached scripts: ' . count($cached_scripts['scripts']) . '</p>';
                echo '</div>';
                
            } else {
                echo '<div class="status-box info">';
                echo '<span class="badge badge-warning">ℹ️ UNAVAILABLE</span> ';
                echo 'Cannot retrieve cached scripts list';
                echo '</div>';
            }
        } else {
            echo '<div class="status-box success">';
            echo '<span class="badge badge-success">✅ N/A</span> ';
            echo 'OPcache not enabled - No cached files';
            echo '</div>';
        }
        ?>

        <?php
        // =================================================================
        // 6. DIAGNOSIS SUMMARY
        // =================================================================
        ?>
        <h2>🎯 Diagnosis Summary</h2>
        <?php
        $all_good = true;
        $issues = [];
        
        // Check OPcache
        if ($opcache_enabled && $is_enabled) {
            $all_good = false;
            $issues[] = 'OPcache is enabled and may be serving old code';
        }
        
        // Check file size
        if ($size != $expected_size) {
            $all_good = false;
            $issues[] = "Database.php size mismatch (expected $expected_size, found $size)";
        }
        
        // Check methods
        if ($present_count !== count($required_methods)) {
            $all_good = false;
            $issues[] = "Missing methods in Database class ($present_count/" . count($required_methods) . " present)";
        }
        
        if ($all_good) {
            echo '<div class="status-box success">';
            echo '<h3 style="margin:0; color:#4ec9b0;">✅ ALL SYSTEMS OPERATIONAL</h3>';
            echo '<p style="margin:10px 0 0 0;">Everything looks good! The system should be working at 100% functionality.</p>';
            echo '</div>';
        } else {
            echo '<div class="status-box warning">';
            echo '<h3 style="margin:0; color:#ce9178;">⚠️ ISSUES DETECTED</h3>';
            echo '<ul style="margin:10px 0 0 0;">';
            foreach ($issues as $issue) {
                echo '<li>' . htmlspecialchars($issue) . '</li>';
            }
            echo '</ul>';
            echo '<p style="margin:10px 0 0 0;"><strong>Expected Resolution:</strong> Cache should clear within 1-2 hours of last deploy</p>';
            echo '</div>';
        }
        ?>

        <div class="status-box info">
            <h3 style="margin:0; color:#569cd6;">ℹ️ Next Steps</h3>
            <ol style="margin:10px 0 0 0;">
                <li>If OPcache is still caching old code, wait for automatic expiration (typically 1-2 hours)</li>
                <li>Try accessing: <a href="force_opcache_reset_sprint58.php" style="color:#569cd6;">force_opcache_reset_sprint58.php</a></li>
                <li>Reload this page every 15-30 minutes to monitor progress</li>
                <li>Test actual modules once "ALL SYSTEMS OPERATIONAL" shows</li>
            </ol>
        </div>

        <div class="timestamp" style="margin-top:30px; text-align:center;">
            Sprint 60 Cache Monitor | Generated: <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</body>
</html>

<!-- Sprint 60 Cache Monitor v1.0 | SCRUM + PDCA | GenSpark AI -->
