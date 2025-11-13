#!/bin/bash

FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"
FTP_DIR="/public_html"

echo "Downloading current production index.php..."
curl "ftp://${FTP_HOST}${FTP_DIR}/index.php" \
  --user "${FTP_USER}:${FTP_PASS}" \
  -o index_prod.php 2>&1 | tail -3

if [ -f index_prod.php ]; then
    echo ""
    echo "=== Checking for debug route in production ==="
    if grep -q "debug-models-test" index_prod.php; then
        echo "✅ debug-models-test route FOUND in production"
        echo ""
        echo "Checking position relative to login check..."
        grep -n "page === 'debug-models-test'\|VERIFICAR LOGIN\|publicPages" index_prod.php | head -10
    else
        echo "❌ debug-models-test route NOT FOUND in production"
    fi
    
    echo ""
    echo "=== File size comparison ==="
    echo "Local:      $(wc -c < index.php) bytes"
    echo "Production: $(wc -c < index_prod.php) bytes"
    
    echo ""
    echo "=== Modification timestamps ==="
    echo "Local:      $(stat -c %y index.php)"
    echo "Production download: $(stat -c %y index_prod.php)"
else
    echo "❌ Failed to download production index.php"
fi

