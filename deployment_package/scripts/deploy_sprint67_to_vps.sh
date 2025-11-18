#!/bin/bash
#================================================================
# SPRINT 67 - DEPLOYMENT COMPLETO PARA VPS
#================================================================
# Data: 2025-11-16
# Objetivo: Deploy de todas correções do Sprint 67 em produção
# Servidor: 72.61.53.222
# Path: /opt/webserver/sites/prestadores
#================================================================

set -e  # Exit on error

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
VPS_HOST="72.61.53.222"
VPS_USER="root"
VPS_PATH="/opt/webserver/sites/prestadores"
VPS_SSH_PORT="22"

# Banner
echo -e "${BLUE}"
echo "================================================================"
echo "  SPRINT 67 - DEPLOYMENT PARA PRODUÇÃO"
echo "================================================================"
echo -e "${NC}"
echo ""
echo "Servidor: $VPS_HOST"
echo "Path: $VPS_PATH"
echo "Usuário: $VPS_USER"
echo ""

# Verificação inicial
echo -e "${YELLOW}[1/9] Verificando conexão SSH...${NC}"
if ssh -o ConnectTimeout=5 -p $VPS_SSH_PORT $VPS_USER@$VPS_HOST "echo 'SSH OK'" >/dev/null 2>&1; then
    echo -e "${GREEN}✅ Conexão SSH estabelecida${NC}"
else
    echo -e "${RED}❌ Falha na conexão SSH. Verifique credenciais e conectividade.${NC}"
    exit 1
fi
echo ""

# Backup do AuthController original
echo -e "${YELLOW}[2/9] Fazendo backup do AuthController original...${NC}"
ssh -p $VPS_SSH_PORT $VPS_USER@$VPS_HOST << 'EOSSH'
cd /opt/webserver/sites/prestadores
if [ -f "src/Controllers/AuthController.php" ]; then
    cp src/Controllers/AuthController.php src/Controllers/AuthController.php.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ Backup criado"
else
    echo "⚠️  AuthController não encontrado"
fi
EOSSH
echo ""

# Upload da migration ENUM
echo -e "${YELLOW}[3/9] Fazendo upload da migration 026 (ENUM fix)...${NC}"
scp -P $VPS_SSH_PORT database/migrations/026_fix_usuarios_role_enum.sql \
    $VPS_USER@$VPS_HOST:$VPS_PATH/database/migrations/
echo -e "${GREEN}✅ Migration 026 enviada${NC}"
echo ""

# Upload do script completo
echo -e "${YELLOW}[4/9] Fazendo upload do script completo Sprint 67...${NC}"
scp -P $VPS_SSH_PORT database/sprint67_complete_fix.sql \
    $VPS_USER@$VPS_HOST:$VPS_PATH/database/
echo -e "${GREEN}✅ Script completo enviado${NC}"
echo ""

# Upload do AuthControllerDebug
echo -e "${YELLOW}[5/9] Fazendo upload do AuthController com debug...${NC}"
scp -P $VPS_SSH_PORT src/Controllers/AuthControllerDebug.php \
    $VPS_USER@$VPS_HOST:$VPS_PATH/src/Controllers/
echo -e "${GREEN}✅ AuthControllerDebug enviado${NC}"
echo ""

# Executar SQL na produção
echo -e "${YELLOW}[6/9] Executando correções SQL no banco de dados...${NC}"
ssh -p $VPS_SSH_PORT $VPS_USER@$VPS_HOST << 'EOSSH'
cd /opt/webserver/sites/prestadores

echo "Executando sprint67_complete_fix.sql..."
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores < database/sprint67_complete_fix.sql

echo ""
echo "✅ SQL executado com sucesso"
echo ""

# Verificar usuários criados
echo "Verificando usuários de teste..."
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores -e \
"SELECT id, nome, email, role, ativo FROM usuarios WHERE email LIKE '%@clinfec.com.br' ORDER BY role;"

EOSSH
echo -e "${GREEN}✅ Banco de dados atualizado${NC}"
echo ""

# Substituir AuthController pelo Debug
echo -e "${YELLOW}[7/9] Ativando AuthController com debug extensivo...${NC}"
ssh -p $VPS_SSH_PORT $VPS_USER@$VPS_HOST << 'EOSSH'
cd /opt/webserver/sites/prestadores/src/Controllers

# Copiar debug version como AuthController
cp AuthControllerDebug.php AuthController.php

echo "✅ AuthController substituído pela versão debug"
EOSSH
echo -e "${GREEN}✅ Debug ativado${NC}"
echo ""

# Reload PHP-FPM e Clear OPcache
echo -e "${YELLOW}[8/9] Recarregando PHP-FPM e limpando OPcache...${NC}"
ssh -p $VPS_SSH_PORT $VPS_USER@$VPS_HOST << 'EOSSH'
# Reload PHP-FPM
echo "Recarregando PHP-FPM..."
systemctl reload php8.3-fpm-prestadores
systemctl status php8.3-fpm-prestadores | head -5

echo ""

# Clear OPcache
echo "Limpando OPcache..."
echo "<?php opcache_reset(); echo 'OPcache limpo'; ?>" | php8.3

echo ""
echo "✅ Serviços recarregados"
EOSSH
echo -e "${GREEN}✅ PHP-FPM recarregado e OPcache limpo${NC}"
echo ""

# Verificar logs
echo -e "${YELLOW}[9/9] Exibindo últimas linhas dos logs...${NC}"
ssh -p $VPS_SSH_PORT $VPS_USER@$VPS_HOST << 'EOSSH'
echo "=== LOG PHP-FPM (últimas 10 linhas) ==="
tail -10 /var/log/php8.3-fpm/error.log 2>/dev/null || echo "Log não encontrado ou sem permissão"

echo ""
echo "=== LOG NGINX (últimas 10 linhas) ==="
tail -10 /var/log/nginx/prestadores-error.log 2>/dev/null || echo "Log não encontrado ou sem permissão"
EOSSH
echo ""

# Resumo final
echo -e "${BLUE}"
echo "================================================================"
echo "  ✅ DEPLOYMENT SPRINT 67 CONCLUÍDO"
echo "================================================================"
echo -e "${NC}"
echo ""
echo -e "${GREEN}Próximos passos:${NC}"
echo ""
echo "1. Testar login com os 4 usuários:"
echo "   • master@clinfec.com.br / password"
echo "   • admin@clinfec.com.br / admin123"
echo "   • gestor@clinfec.com.br / password"
echo "   • usuario@clinfec.com.br / password"
echo ""
echo "2. Monitorar logs durante o teste:"
echo "   ssh root@$VPS_HOST 'tail -f /var/log/php8.3-fpm/error.log'"
echo ""
echo "3. Analisar debug output nos logs"
echo ""
echo "4. Se login funcionar, remover debug:"
echo "   • Restaurar AuthController.php do backup"
echo "   • Reload PHP-FPM novamente"
echo ""
echo -e "${YELLOW}IMPORTANTE:${NC}"
echo "O AuthController AGORA TEM DEBUG EXTENSIVO!"
echo "Todos os logins serão registrados detalhadamente nos logs."
echo ""
echo "URL: https://prestadores.clinfec.com.br"
echo ""
echo -e "${BLUE}================================================================${NC}"
echo ""
