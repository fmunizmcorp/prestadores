#!/bin/bash
FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"

# List root directory
curl "ftp://${FTP_HOST}/" \
  --user "${FTP_USER}:${FTP_PASS}" \
  -s | head -30

