<?php
/**
 * ULTIMATE DEPLOYER
 * Self-contained deployer that downloads from GitHub and deploys to current directory
 * Upload this to prestadores directory and execute via HTTP
 */
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300);

echo "═══════════════════════════════════════════════════════════\n";
echo "ULTIMATE DEPLOYER - SPRINT 14 FINAL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$base_path = getcwd();
$timestamp = date('Y-m-d H:i:s');

echo "Timestamp: $timestamp\n";
echo "Working Directory: $base_path\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

// Files to deploy
$files = [
    [
        'target' => 'src/Models/NotaFiscal.php',
        'source' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/NotaFiscal.php',
        'description' => 'NotaFiscal Model - Complete rewrite (30,885 bytes)'
    ],
    [
        'target' => 'src/Models/Projeto.php',
        'source' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Projeto.php',
        'description' => 'Projeto Model - Schema corrected'
    ],
    [
        'target' => 'src/Models/Atividade.php',
        'source' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Atividade.php',
        'description' => 'Atividade Model - Schema corrected'
    ]
];

$success_count = 0;
$failed_count = 0;
$results = [];

foreach ($files as $index => $file) {
    $num = $index + 1;
    $total = count($files);
    
    echo "───────────────────────────────────────────────────────────────\n";
    echo "[$num/$total] {$file['description']}\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo "Target: {$file['target']}\n";
    echo "Source: {$file['source']}\n\n";
    
    // Step 1: Download from GitHub
    echo "  [1/5] Downloading from GitHub...\n";
    $content = @file_get_contents($file['source']);
    
    if (!$content || strlen($content) < 100) {
        echo "  ❌ DOWNLOAD FAILED\n";
        echo "  Error: Could not download or file too small\n\n";
        $failed_count++;
        $results[] = ['file' => $file['target'], 'status' => 'FAILED', 'reason' => 'Download failed'];
        continue;
    }
    
    $size = strlen($content);
    echo "  ✅ Downloaded: " . number_format($size) . " bytes\n\n";
    
    // Step 2: Verify content
    echo "  [2/5] Verifying content...\n";
    if (strpos($content, '<?php') === false) {
        echo "  ❌ INVALID PHP FILE\n\n";
        $failed_count++;
        $results[] = ['file' => $file['target'], 'status' => 'FAILED', 'reason' => 'Invalid PHP'];
        continue;
    }
    echo "  ✅ Valid PHP file\n\n";
    
    // Step 3: Prepare target directory
    echo "  [3/5] Preparing target directory...\n";
    $target_path = $base_path . '/' . $file['target'];
    $target_dir = dirname($target_path);
    
    if (!is_dir($target_dir)) {
        echo "  Creating directory: $target_dir\n";
        if (!@mkdir($target_dir, 0755, true)) {
            echo "  ❌ FAILED to create directory\n\n";
            $failed_count++;
            $results[] = ['file' => $file['target'], 'status' => 'FAILED', 'reason' => 'Directory creation failed'];
            continue;
        }
        echo "  ✅ Directory created\n\n";
    } else {
        echo "  ✅ Directory exists\n\n";
    }
    
    // Step 4: Backup existing file
    echo "  [4/5] Backing up existing file...\n";
    if (file_exists($target_path)) {
        $old_size = filesize($target_path);
        $backup_path = $target_path . '.backup.' . date('YmdHis');
        
        if (@copy($target_path, $backup_path)) {
            echo "  ✅ Backup created: " . basename($backup_path) . " ($old_size bytes)\n\n";
        } else {
            echo "  ⚠️  Backup failed (continuing anyway)\n\n";
        }
    } else {
        echo "  ℹ️  No existing file to backup\n\n";
    }
    
    // Step 5: Write new file
    echo "  [5/5] Writing new file...\n";
    $bytes_written = @file_put_contents($target_path, $content);
    
    if ($bytes_written === false || $bytes_written === 0) {
        echo "  ❌ WRITE FAILED\n";
        echo "  Error: Could not write to $target_path\n";
        echo "  Check permissions and disk space\n\n";
        $failed_count++;
        $results[] = ['file' => $file['target'], 'status' => 'FAILED', 'reason' => 'Write failed'];
        continue;
    }
    
    echo "  ✅ SUCCESS - Written: " . number_format($bytes_written) . " bytes\n";
    echo "  ✅ File deployed successfully!\n\n";
    $success_count++;
    $results[] = ['file' => $file['target'], 'status' => 'SUCCESS', 'size' => $bytes_written];
}

// Summary
echo "═══════════════════════════════════════════════════════════\n";
echo "DEPLOYMENT SUMMARY\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Total files: " . count($files) . "\n";
echo "  ✅ Success: $success_count\n";
echo "  ❌ Failed: $failed_count\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Detailed results
echo "DETAILED RESULTS:\n";
foreach ($results as $result) {
    $icon = $result['status'] === 'SUCCESS' ? '✅' : '❌';
    echo "$icon {$result['file']} - {$result['status']}";
    if (isset($result['size'])) {
        echo " (" . number_format($result['size']) . " bytes)";
    } elseif (isset($result['reason'])) {
        echo " - {$result['reason']}";
    }
    echo "\n";
}

echo "\n";

if ($success_count === count($files)) {
    echo "✅ DEPLOYMENT COMPLETE - ALL FILES DEPLOYED SUCCESSFULLY!\n\n";
    echo "Next steps:\n";
    echo "1. Clear OPcache: ?action=clear_cache\n";
    echo "2. Test routes to verify 100% functionality\n";
    echo "3. Expected improvement: 64% → 100%\n";
} elseif ($success_count > 0) {
    echo "⚠️  PARTIAL DEPLOYMENT - Some files deployed\n\n";
    echo "Check failed files and retry if needed\n";
} else {
    echo "❌ DEPLOYMENT FAILED - No files deployed\n\n";
    echo "Check:\n";
    echo "  - File write permissions\n";
    echo "  - Network connectivity to GitHub\n";
    echo "  - Disk space\n";
    echo "  - PHP configuration\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "END OF DEPLOYMENT\n";
echo "═══════════════════════════════════════════════════════════\n";
