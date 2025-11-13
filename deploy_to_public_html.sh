#!/bin/bash

# Deploy config/, src/, database/ to /public_html/
# This fixes the path issue where index.php looks for these dirs in /public_html/

FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"
FTP_BASE="/public_html"

echo "==========================================="
echo "DEPLOYING DIRECTORIES TO /public_html/"
echo "==========================================="
echo ""

# Create tarballs
echo "[1/4] Creating tarballs..."
tar -czf config_deploy.tar.gz config/
tar -czf src_deploy.tar.gz src/
tar -czf database_deploy.tar.gz database/

echo "✅ Tarballs created"
echo ""

# Upload tarballs
echo "[2/4] Uploading tarballs..."
for file in config_deploy.tar.gz src_deploy.tar.gz database_deploy.tar.gz; do
    echo "  Uploading $file..."
    curl -T "$file" \
      "ftp://${FTP_HOST}${FTP_BASE}/$file" \
      --user "${FTP_USER}:${FTP_PASS}" \
      -s
    echo "  ✅ $file uploaded"
done

echo ""
echo "[3/4] Creating extraction script..."

# Create extraction PHP script
cat > extract_in_public_html.php << 'PHP'
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
PHP

echo "✅ Extraction script created"
echo ""

# Upload extraction script
echo "[4/4] Uploading extraction script..."
curl -T "extract_in_public_html.php" \
  "ftp://${FTP_HOST}${FTP_BASE}/extract_in_public_html.php" \
  --user "${FTP_USER}:${FTP_PASS}" \
  -s

echo "✅ Extraction script uploaded"
echo ""

echo "==========================================="
echo "DEPLOYMENT COMPLETE"
echo "==========================================="
echo ""
echo "Next step: Run extraction script"
echo "URL: https://prestadores.clinfec.com.br/extract_in_public_html.php"
echo ""

