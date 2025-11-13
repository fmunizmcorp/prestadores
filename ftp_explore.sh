#!/bin/bash
USER="u673902663.genspark1:Genspark1@"
FTP="ftp://ftp.clinfec.com.br"

echo "=== MAPEAMENTO COMPLETO DA ESTRUTURA FTP ==="
echo

# Root
echo "[ROOT]"
curl --user "$USER" "$FTP/./" --list-only -s | grep -v "^\." | head -20

echo
echo "[/app]"
curl --user "$USER" "$FTP/./app/" --list-only -s 2>&1 | head -10

echo
echo "[/controllers]"
curl --user "$USER" "$FTP/./controllers/" --list-only -s 2>&1 | head -10

echo
echo "[/application]"
curl --user "$USER" "$FTP/./application/" --list-only -s 2>&1 | head -10

echo
echo "[/includes]"
curl --user "$USER" "$FTP/./includes/" --list-only -s 2>&1 | head -10
