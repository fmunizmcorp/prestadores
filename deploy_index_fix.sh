#!/bin/bash

# Deploy index.php with debug routes fix to production
# FTP: ftp.clinfec.com.br
# User: u673902663.genspark1
# Target: /public_html (prestadores directory)

FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"
FTP_DIR="/public_html"

echo "=========================================="
echo "Deploying index.php with debug routes fix"
echo "=========================================="
echo ""

# Upload index.php
echo "Uploading index.php..."
curl -T "index.php" \
  "ftp://${FTP_HOST}${FTP_DIR}/index.php" \
  --user "${FTP_USER}:${FTP_PASS}" \
  --ftp-create-dirs

if [ $? -eq 0 ]; then
    echo "✅ index.php uploaded successfully"
else
    echo "❌ Failed to upload index.php"
    exit 1
fi

echo ""
echo "=========================================="
echo "Deployment completed!"
echo "=========================================="
echo ""
echo "Test URLs:"
echo "1. Debug Models Test: https://prestadores.clinfec.com.br/?page=debug-models-test"
echo "2. Read Debug Log: https://prestadores.clinfec.com.br/?page=read-debug-log"
echo ""
echo "Note: The debug-models-test route will:"
echo "  - Execute WITHOUT authentication"
echo "  - Test Projeto, Atividade, NotaFiscal Models"
echo "  - Generate debug_errors.log file"
echo "  - Display errors with full stack traces"
echo ""

