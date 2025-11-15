<?php
set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Deploying Sprint 33...</h1>\n";
flush();

$sourceDir = '/home/u673902663/domains/clinfec.com.br/public_html';
$packageFile = $sourceDir . '/deploy_sprint33_complete.tar.gz';
$targetDir = $sourceDir . '/prestadores';

// Check if package exists
if (!file_exists($packageFile)) {
    die("ERROR: Package not found at: $packageFile");
}

echo "<p>✅ Package found: " . filesize($packageFile) . " bytes</p>\n";
flush();

// Create prestadores directory if it doesn't exist
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
    echo "<p>✅ Created directory: $targetDir</p>\n";
} else {
    echo "<p>ℹ️ Directory exists: $targetDir</p>\n";
}
flush();

// Extract using PharData
try {
    $phar = new PharData($packageFile);
    $phar->extractTo($targetDir, null, true);
    echo "<p>✅ Files extracted successfully!</p>\n";
    flush();
    
    // Apply Sprint 31 fixes
    echo "<h2>Applying Sprint 31 Fixes...</h2>\n";
    flush();
    
    // Rename old index.php
    $oldIndex = $targetDir . '/public/index.php';
    if (file_exists($oldIndex)) {
        rename($oldIndex, $oldIndex . '.OLD_CACHE');
        echo "<p>✅ Renamed old index.php</p>\n";
    }
    
    // Copy sprint31 index
    $sprint31Index = $targetDir . '/public/index_sprint31.php';
    if (file_exists($sprint31Index)) {
        copy($sprint31Index, $targetDir . '/public/index.php');
        echo "<p>✅ Applied Sprint 31 index.php</p>\n";
    }
    
    // Remove DatabaseMigration.php
    $migrationFile = $targetDir . '/src/DatabaseMigration.php';
    if (file_exists($migrationFile)) {
        rename($migrationFile, $migrationFile . '.DISABLED');
        echo "<p>✅ Disabled DatabaseMigration.php</p>\n";
    }
    
    // Set permissions
    chmod($targetDir . '/public/uploads', 0777);
    chmod($targetDir . '/public', 0755);
    echo "<p>✅ Permissions set</p>\n";
    
    // Clear OPcache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "<p>✅ OPcache cleared</p>\n";
    }
    
    clearstatcache(true);
    
    echo "<h2 style='color:green;'>✅ DEPLOY COMPLETE!</h2>\n";
    echo "<p><strong>Access:</strong> <a href='https://prestadores.clinfec.com.br'>https://prestadores.clinfec.com.br</a></p>\n";
    echo "<p><strong>Login:</strong> admin@clinfec.com.br / password</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ ERROR: " . $e->getMessage() . "</p>\n";
}
