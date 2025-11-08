#!/bin/bash
# Deploy Sprint 8 - Emergency Fixes
# Data: 2025-11-08

echo "===== SPRINT 8 - EMERGENCY FIXES DEPLOY ====="
echo "Data: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Criar diretório temporário
DEPLOY_DIR="/tmp/clinfec_deploy_sprint8_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$DEPLOY_DIR"

echo "1. Copiando arquivos corrigidos..."

# Copiar apenas os 3 arquivos corrigidos
cp -v public/index.php "$DEPLOY_DIR/"
mkdir -p "$DEPLOY_DIR/src"
cp -v src/Database.php "$DEPLOY_DIR/src/"
cp -v src/DatabaseMigration.php "$DEPLOY_DIR/src/"
mkdir -p "$DEPLOY_DIR/config"
cp -v config/version.php "$DEPLOY_DIR/config/"

echo ""
echo "2. Gerando arquivo de deployment..."

# Criar script FTP batch
cat > "$DEPLOY_DIR/ftp_upload.txt" << FTPEOF
open ftp.clinfec.com.br
user u673902663.genspark1 Genspark1@
binary
cd /home/u673902663/domains/clinfec.com.br/public_html/prestadores
put public/index.php
cd src
put src/Database.php
put src/DatabaseMigration.php
cd ../config
put config/version.php
bye
FTPEOF

echo ""
echo "3. Arquivos preparados em: $DEPLOY_DIR"
ls -lh "$DEPLOY_DIR/"

echo ""
echo "===== DEPLOY PRONTO ====="
echo "Para fazer upload via FTP, execute:"
echo "ftp -n < $DEPLOY_DIR/ftp_upload.txt"
