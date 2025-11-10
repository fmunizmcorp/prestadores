<?php
/**
 * AUTO DEPLOYER - Sprint 14
 * Upload this file via FTP and access via browser to deploy
 */

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "═══════════════════════════════════════════════════════════\n";
echo "AUTO DEPLOYER - SPRINT 14\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Current Directory: " . getcwd() . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n\n";

// Step 1: Download deployment package from GitHub
echo "[1/4] Downloading from GitHub...\n";

$branch = 'genspark_ai_developer';
$repo_url = "https://github.com/fmunizmcorp/prestadores/archive/refs/heads/{$branch}.zip";
$zip_file = "deploy_github.zip";

// Try using curl command
exec("curl -L -o $zip_file '$repo_url' 2>&1", $output1, $ret1);

if ($ret1 === 0 && file_exists($zip_file) && filesize($zip_file) > 1000) {
    echo "✓ Downloaded: " . number_format(filesize($zip_file)) . " bytes\n\n";
} else {
    echo "✗ Download failed!\n";
    echo "Trying with file_get_contents...\n";
    
    $content = @file_get_contents($repo_url);
    if ($content && strlen($content) > 1000) {
        file_put_contents($zip_file, $content);
        echo "✓ Downloaded: " . number_format(strlen($content)) . " bytes\n\n";
    } else {
        echo "✗ Both methods failed!\n";
        exit(1);
    }
}

// Step 2: Create backup
echo "[2/4] Creating backup...\n";
$backup = 'backup_' . date('YmdHis') . '.tar.gz';
exec("tar -czf $backup --exclude='*.tar.gz' --exclude='*.zip' --exclude='backup_*' . 2>&1", $output2, $ret2);
echo ($ret2 === 0 ? "✓" : "⚠") . " Backup: $backup\n\n";

// Step 3: Extract
echo "[3/4] Extracting...\n";
exec("unzip -o $zip_file 2>&1", $output3, $ret3);

if ($ret3 === 0) {
    echo "✓ Extraction successful!\n\n";
    
    // Find extracted directory
    $extracted_dir = "prestadores-{$branch}";
    
    if (is_dir($extracted_dir)) {
        echo "Moving files from $extracted_dir...\n";
        
        // Move contents
        exec("cp -rf $extracted_dir/* . 2>&1", $output4, $ret4);
        exec("cp -rf $extracted_dir/.htaccess . 2>&1", $output5, $ret5);
        
        echo "✓ Files moved!\n\n";
        
        // Cleanup
        exec("rm -rf $extracted_dir 2>&1");
        unlink($zip_file);
    }
} else {
    echo "✗ Extraction failed!\n";
    print_r($output3);
    exit(1);
}

// Step 4: Set permissions
echo "[4/4] Setting permissions...\n";
exec("chmod -R 755 . 2>&1");
exec("chmod -R 777 public/uploads 2>&1");
echo "✓ Permissions set!\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "✅ DEPLOYMENT COMPLETE!\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Access diagnostic scripts:\n";
echo "  https://prestadores.clinfec.com.br/check_projetos_table.php\n";
echo "  https://prestadores.clinfec.com.br/check_atividades_table.php\n";
echo "  https://prestadores.clinfec.com.br/check_notas_fiscais_table.php\n\n";

echo "Application available at:\n";
echo "  https://prestadores.clinfec.com.br/\n";
