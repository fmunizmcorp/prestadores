#!/bin/bash

FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"
FTP_DIR="/public_html"

echo "Downloading .htaccess..."
curl "ftp://${FTP_HOST}${FTP_DIR}/.htaccess" \
  --user "${FTP_USER}:${FTP_PASS}" \
  -o .htaccess_prod 2>&1

if [ -f .htaccess_prod ]; then
    echo ""
    echo "=== .htaccess content ==="
    cat .htaccess_prod
    echo ""
    echo "=== End .htaccess ==="
else
    echo "No .htaccess file found"
fi

