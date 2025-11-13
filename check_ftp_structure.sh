#!/bin/bash

FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"

echo "=== FTP Root Directory (/) ==="
curl -s "ftp://${FTP_HOST}/" --user "${FTP_USER}:${FTP_PASS}" | head -10

echo ""
echo "=== /public_html/ Directory ==="
curl -s "ftp://${FTP_HOST}/public_html/" --user "${FTP_USER}:${FTP_PASS}" | head -10

echo ""
echo "=== Checking for /prestadores/ subdirectory ==="
curl -s "ftp://${FTP_HOST}/prestadores/" --user "${FTP_USER}:${FTP_PASS}" 2>&1 | head -5

echo ""
echo "=== Checking for /public_html/prestadores/ subdirectory ==="
curl -s "ftp://${FTP_HOST}/public_html/prestadores/" --user "${FTP_USER}:${FTP_PASS}" 2>&1 | head -5

