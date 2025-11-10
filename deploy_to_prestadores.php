<?php
/**
 * DEPLOY TO PRESTADORES SUBDIRECTORY
 * Must be placed in /public_html and accessed directly
 */
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300);

echo "═══════════════════════════════════════════════════════════\n";
echo "DEPLOYING TO PRESTADORES DIRECTORY\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$doc_root = $_SERVER['DOCUMENT_ROOT'] ?? '/home/u673902663/domains/clinfec.com.br/public_html';
$prestadores_path = $doc_root . '/prestadores';

echo "Document Root: $doc_root\n";
echo "Prestadores Path: $prestadores_path\n";
echo "Current Working Directory: " . getcwd() . "\n\n";

if (!is_dir($prestadores_path)) {
    echo "❌ Prestadores directory not found!\n";
    exit(1);
}

echo "✅ Prestadores directory exists\n\n";

$files = [
    ['src/Models/NotaFiscal.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/NotaFiscal.php'],
    ['src/Models/Projeto.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Projeto.php'],
    ['src/Models/Atividade.php', 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/main/src/Models/Atividade.php']
];

$success = 0;
$failed = 0;

foreach ($files as $f) {
    $target_path = $prestadores_path . '/' . $f[0];
    echo "───────────────────────────────────────────────────────────────\n";
    echo "Deploying: {$f[0]}\n";
    echo "Target: $target_path\n";
    echo "Source: {$f[1]}\n\n";
    
    echo "  [1/4] Downloading from GitHub...\n";
    $content = @file_get_contents($f[1]);
    
    if (!$content || strlen($content) < 100) {
        echo "  ❌ DOWNLOAD FAILED\n\n";
        $failed++;
        continue;
    }
    
    echo "  ✅ Downloaded: " . number_format(strlen($content)) . " bytes\n\n";
    
    echo "  [2/4] Creating directory if needed...\n";
    $dir = dirname($target_path);
    if (!is_dir($dir)) {
        if (@mkdir($dir, 0755, true)) {
            echo "  ✅ Directory created: $dir\n\n";
        } else {
            echo "  ❌ Failed to create directory\n\n";
            $failed++;
            continue;
        }
    } else {
        echo "  ✅ Directory exists\n\n";
    }
    
    echo "  [3/4] Backing up existing file...\n";
    if (file_exists($target_path)) {
        $backup_path = $target_path . '.backup.' . date('YmdHis');
        if (@copy($target_path, $backup_path)) {
            echo "  ✅ Backup created: " . basename($backup_path) . "\n\n";
        } else {
            echo "  ⚠️  Backup failed (continuing anyway)\n\n";
        }
    } else {
        echo "  ℹ️  No existing file to backup\n\n";
    }
    
    echo "  [4/4] Writing new file...\n";
    if (@file_put_contents($target_path, $content)) {
        echo "  ✅ DEPLOYED SUCCESSFULLY\n\n";
        $success++;
    } else {
        echo "  ❌ WRITE FAILED\n";
        echo "  Check permissions on: $target_path\n\n";
        $failed++;
    }
}

echo "═══════════════════════════════════════════════════════════\n";
echo "DEPLOYMENT SUMMARY\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  ✅ Success: $success files\n";
echo "  ❌ Failed: $failed files\n";
echo "═══════════════════════════════════════════════════════════\n\n";

if ($success === 3) {
    echo "✅ DEPLOYMENT COMPLETE!\n\n";
    echo "Next steps:\n";
    echo "1. Clear OPcache: https://clinfec.com.br/prestadores/clear_cache.php\n";
    echo "2. Test routes: https://clinfec.com.br/prestadores/\n";
    echo "3. Expected result: 37/37 routes functional (100%)\n";
} elseif ($success > 0) {
    echo "⚠️  PARTIAL DEPLOYMENT\n";
    echo "$success files deployed, $failed failed\n";
} else {
    echo "❌ DEPLOYMENT FAILED!\n";
    echo "All files failed to deploy. Check:\n";
    echo "  - File permissions on prestadores directory\n";
    echo "  - PHP write permissions\n";
    echo "  - Network connectivity to GitHub\n";
}
