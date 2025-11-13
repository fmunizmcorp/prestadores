#!/bin/bash

FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"

echo "=== Looking for src/ directory in FTP root ==="
curl -s "ftp://${FTP_HOST}/" --user "${FTP_USER}:${FTP_PASS}" | grep "^d" | grep -E "src|config|database"

echo ""
echo "=== Full FTP root listing ==="
curl -s "ftp://${FTP_HOST}/" --user "${FTP_USER}:${FTP_PASS}" | grep "^d"

