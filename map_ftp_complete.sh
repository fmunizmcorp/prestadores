#!/bin/bash
USER="u673902663.genspark1:Genspark1@"
FTP="ftp://ftp.clinfec.com.br"

echo "=== MAPEAMENTO COMPLETO FTP RECURSIVO ==="
echo

echo "[ROOT FILES]"
curl --user "$USER" "$FTP/./" --list-only -s | head -50

echo -e "\n[/src]"
curl --user "$USER" "$FTP/./src/" --list-only -s 2>&1 | head -20

echo -e "\n[/src/Models]"
curl --user "$USER" "$FTP/./src/Models/" --list-only -s 2>&1

echo -e "\n[/src/Controllers]"
curl --user "$USER" "$FTP/./src/Controllers/" --list-only -s 2>&1 | head -20

echo -e "\n[/src/Views]"
curl --user "$USER" "$FTP/./src/Views/" --list-only -s 2>&1 | head -20

echo -e "\n[/config]"
curl --user "$USER" "$FTP/./config/" --list-only -s 2>&1 | head -20

echo -e "\n[/public]"
curl --user "$USER" "$FTP/./public/" --list-only -s 2>&1 | head -20

echo -e "\n[/vendor]"
curl --user "$USER" "$FTP/./vendor/" --list-only -s 2>&1 | head -10

echo -e "\n[/routes]"
curl --user "$USER" "$FTP/./routes/" --list-only -s 2>&1 | head -10
