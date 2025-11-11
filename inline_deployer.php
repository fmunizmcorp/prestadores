<?php
/**
 * INLINE DEPLOYER
 * Single-file deployer with embedded GitHub RAW download code
 * Upload this to FTP root and access via: https://clinfec.com.br/inline_deployer.php
 */
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300);

echo "═══════════════════════════════════════════════════════════\n";
echo "INLINE DEPLOYER - SPRINT 14 FINAL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Determine where prestadores directory is
$possible_paths = [
    '/home/u673902663/domains/clinfec.com.br/public_html/prestadores',
    '../prestadores',
    './prestadores',
    __DIR__ . '/../prestadores',
];

$prestadores_path = null;

foreach ($possible_paths as $path) {
    if (is_dir($path)) {
        $prestadores_path = realpath($path);
        break;
    }
}

if (!$prestadores_path) {
    echo "❌ ERROR: Could not locate prestadores directory\n";
    echo "Tried:\n";
    foreach ($possible_paths as $path) {
        echo "  - $path\n";
    }
    exit(1);
}

echo "✅ Prestadores directory found: $prestadores_path\n\n";

// Files to deploy
$files = [
    [
        'path' => 'src/Models/NotaFiscal.php',
        'url' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/NotaFiscal.php'
    ],
    [
        'path' => 'src/Models/Projeto.php',
        'url' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Projeto.php'
    ],
    [
        'path' => 'src/Models/Atividade.php',
        'url' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Atividade.php'
    ],
];

$success = 0;
$failed = 0;

foreach ($files as $idx => $file) {
    echo "───────────────────────────────────────────────────────────────\n";
    echo "[" . ($idx + 1) . "/3] {$file['path']}\n";
    echo "───────────────────────────────────────────────────────────────\n";
    
    $target = $prestadores_path . '/' . $file['path'];
    
    echo "Source: {$file['url']}\n";
    echo "Target: $target\n\n";
    
    // Download
    echo "Downloading... ";
    $content = @file_get_contents($file['url']);
    
    if (!$content || strlen($content) < 100) {
        echo "❌ FAILED\n\n";
        $failed++;
        continue;
    }
    
    echo "✅ " . number_format(strlen($content)) . " bytes\n";
    
    // Verify
    if (strpos($content, '<?php') === false) {
        echo "Verifying... ❌ Invalid PHP\n\n";
        $failed++;
        continue;
    }
    echo "Verifying... ✅ Valid PHP\n";
    
    // Ensure directory exists
    $dir = dirname($target);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Backup
    if (file_exists($target)) {
        $backup = $target . '.backup_' . date('YmdHis');
        copy($target, $backup);
        echo "Backup... ✅ " . basename($backup) . "\n";
    }
    
    // Write
    echo "Writing... ";
    $result = @file_put_contents($target, $content);
    
    if ($result === false) {
        echo "❌ FAILED\n\n";
        $failed++;
        continue;
    }
    
    echo "✅ SUCCESS (" . number_format($result) . " bytes)\n\n";
    $success++;
}

echo "═══════════════════════════════════════════════════════════\n";
echo "DEPLOYMENT SUMMARY\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Success: $success / 3\n";
echo "Failed: $failed / 3\n";
echo "═══════════════════════════════════════════════════════════\n\n";

if ($success === 3) {
    echo "✅ ALL FILES DEPLOYED SUCCESSFULLY!\n\n";
    echo "Next: Clear OPcache and test routes\n";
} else {
    echo "⚠️  PARTIAL SUCCESS\n";
}
