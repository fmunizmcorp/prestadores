#!/bin/bash
#================================================================
# SPRINT 67 - EXECUÇÃO REMOTA VIA SSH
#================================================================
# Este script executa o deployment no servidor remoto
# Uso: ./remote_execute.sh [SSH_KEY_PATH]
#================================================================

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configurações
VPS_HOST="72.61.53.222"
VPS_USER="root"
VPS_PATH="/opt/webserver/sites/prestadores"
SSH_KEY="${1:-$HOME/.ssh/id_rsa}"

echo -e "${BLUE}================================================================${NC}"
echo -e "${BLUE}  SPRINT 67 - EXECUÇÃO REMOTA DE DEPLOYMENT${NC}"
echo -e "${BLUE}================================================================${NC}"
echo ""

# Verifica se tem chave SSH
if [ ! -f "$SSH_KEY" ]; then
    echo -e "${RED}❌ Chave SSH não encontrada: $SSH_KEY${NC}"
    echo -e "${YELLOW}💡 Uso: $0 [caminho_para_chave_ssh]${NC}"
    exit 1
fi

# Testa conexão
echo -e "${YELLOW}🔍 Testando conexão SSH...${NC}"
if ssh -i "$SSH_KEY" -o ConnectTimeout=5 "$VPS_USER@$VPS_HOST" "echo 'SSH OK'" &> /dev/null; then
    echo -e "${GREEN}✅ Conexão SSH estabelecida${NC}"
else
    echo -e "${RED}❌ Falha na conexão SSH${NC}"
    echo -e "${YELLOW}💡 Verifique:${NC}"
    echo "   - Chave SSH correta: $SSH_KEY"
    echo "   - Servidor acessível: $VPS_HOST"
    echo "   - Usuário correto: $VPS_USER"
    exit 1
fi

# Upload dos arquivos
echo ""
echo -e "${YELLOW}📤 Fazendo upload dos arquivos...${NC}"

# SQL
echo "  - Uploading SQL files..."
scp -i "$SSH_KEY" ../sql/*.sql "$VPS_USER@$VPS_HOST:$VPS_PATH/database/"

# PHP
echo "  - Uploading PHP files..."
scp -i "$SSH_KEY" ../php/AuthControllerDebug.php "$VPS_USER@$VPS_HOST:$VPS_PATH/src/Controllers/"

# Docs
echo "  - Uploading documentation..."
scp -i "$SSH_KEY" ../docs/*.md "$VPS_USER@$VPS_HOST:$VPS_PATH/"

echo -e "${GREEN}✅ Upload completo${NC}"

# Execução remota
echo ""
echo -e "${YELLOW}🚀 Executando deployment no servidor...${NC}"

ssh -i "$SSH_KEY" "$VPS_USER@$VPS_HOST" bash << 'ENDSSH'
set -e

cd /opt/webserver/sites/prestadores

echo "================================================"
echo "  EXECUTANDO SPRINT 67 NO SERVIDOR"
echo "================================================"

# Backup do AuthController original
echo ""
echo "📦 Fazendo backup do AuthController..."
BACKUP_FILE="src/Controllers/AuthController.php.backup.$(date +%Y%m%d_%H%M%S)"
if [ -f "src/Controllers/AuthController.php" ]; then
    cp src/Controllers/AuthController.php "$BACKUP_FILE"
    echo "✅ Backup criado: $BACKUP_FILE"
fi

# Executa SQL de correção
echo ""
echo "🗄️  Executando correções no banco de dados..."
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores < database/sprint67_complete_fix.sql
echo "✅ SQL executado com sucesso"

# Ativa versão debug do AuthController
echo ""
echo "🔍 Ativando AuthController com debug..."
cp src/Controllers/AuthControllerDebug.php src/Controllers/AuthController.php
echo "✅ Debug ativado"

# Reload PHP-FPM
echo ""
echo "♻️  Recarregando PHP-FPM..."
systemctl reload php8.3-fpm-prestadores
echo "✅ PHP-FPM recarregado"

# Limpa OPcache
echo ""
echo "🗑️  Limpando OPcache..."
echo "<?php opcache_reset(); echo 'OPcache cleared'; ?>" | php8.3
echo "✅ OPcache limpo"

# Valida usuários
echo ""
echo "👥 Validando usuários de teste..."
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores -e "
SELECT CONCAT('✅ ', email, ' (', role, ')') AS usuario 
FROM usuarios 
WHERE email LIKE '%@clinfec.com.br' 
ORDER BY 
    CASE role 
        WHEN 'master' THEN 1 
        WHEN 'admin' THEN 2 
        WHEN 'gestor' THEN 3 
        WHEN 'usuario' THEN 4 
        ELSE 5 
    END;
"

echo ""
echo "================================================"
echo "  ✅ DEPLOYMENT COMPLETO"
echo "================================================"
echo ""
echo "📊 PRÓXIMOS PASSOS:"
echo "   1. Testar login com cada usuário"
echo "   2. Verificar logs: tail -f /var/log/php8.3-fpm/error.log"
echo "   3. Analisar debug output"
echo ""

ENDSSH

echo ""
echo -e "${GREEN}================================================================${NC}"
echo -e "${GREEN}  ✅ DEPLOYMENT EXECUTADO COM SUCESSO${NC}"
echo -e "${GREEN}================================================================${NC}"
echo ""
echo -e "${YELLOW}📝 COMANDOS ÚTEIS:${NC}"
echo ""
echo "  # Ver logs em tempo real:"
echo "  ssh -i $SSH_KEY $VPS_USER@$VPS_HOST 'tail -f /var/log/php8.3-fpm/error.log'"
echo ""
echo "  # Testar login via curl:"
echo "  curl -X POST https://prestadores.clinfec.com.br/?page=login \\"
echo "       -d 'email=master@clinfec.com.br&senha=password' -v"
echo ""
echo "  # Restaurar AuthController original (se necessário):"
echo "  ssh -i $SSH_KEY $VPS_USER@$VPS_HOST 'cd $VPS_PATH && cp $BACKUP_FILE src/Controllers/AuthController.php && systemctl reload php8.3-fpm-prestadores'"
echo ""
