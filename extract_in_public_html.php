<?php
header('Content-Type: text/plain');
echo "=== EXTRACTING TO /public_html/ ===\n\n";

$files = ['config_deploy.tar.gz', 'src_deploy.tar.gz', 'database_deploy.tar.gz'];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "❌ $file not found\n";
        continue;
    }
    
    echo "Extracting $file...\n";
    
    try {
        $phar = new PharData($file);
        $phar->extractTo('.', null, true);
        echo "✅ $file extracted\n";
        
        // Delete tarball after extraction
        unlink($file);
        echo "✅ $file deleted\n\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== EXTRACTION COMPLETE ===\n";
echo "Checking directories...\n";
$dirs = ['config', 'src', 'database'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $count = count(scandir($dir)) - 2;
        echo "✅ $dir/ exists ($count items)\n";
    } else {
        echo "❌ $dir/ not found\n";
    }
}
