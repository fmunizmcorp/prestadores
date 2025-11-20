#!/bin/bash
#================================================================
# SPRINT 67 - VALIDAÇÃO RÁPIDA PÓS-DEPLOYMENT
#================================================================
# Script para executar DIRETAMENTE no servidor de produção
# Valida se o deployment foi bem-sucedido
#================================================================

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}================================================================${NC}"
echo -e "${BLUE}  SPRINT 67 - VALIDAÇÃO RÁPIDA${NC}"
echo -e "${BLUE}================================================================${NC}"
echo ""

ERRORS=0
WARNINGS=0

# Função para check com ícone
check_ok() {
    echo -e "  ${GREEN}✅ $1${NC}"
}

check_fail() {
    echo -e "  ${RED}❌ $1${NC}"
    ERRORS=$((ERRORS + 1))
}

check_warn() {
    echo -e "  ${YELLOW}⚠️  $1${NC}"
    WARNINGS=$((WARNINGS + 1))
}

# 1. Verificar arquivos existem
echo -e "${YELLOW}📂 Verificando arquivos...${NC}"

if [ -f "database/sprint67_complete_fix.sql" ]; then
    check_ok "SQL de correção encontrado"
else
    check_fail "SQL de correção NÃO encontrado"
fi

if [ -f "src/Controllers/AuthControllerDebug.php" ]; then
    check_ok "AuthControllerDebug encontrado"
else
    check_warn "AuthControllerDebug não encontrado (esperado se já deployado)"
fi

if [ -f "src/Controllers/AuthController.php" ]; then
    check_ok "AuthController existe"
else
    check_fail "AuthController NÃO existe!"
fi

echo ""

# 2. Verificar banco de dados
echo -e "${YELLOW}🗄️  Verificando banco de dados...${NC}"

# Testar conexão
if mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores -e "SELECT 1" &>/dev/null; then
    check_ok "Conexão com banco OK"
else
    check_fail "Falha na conexão com banco"
    exit 1
fi

# Verificar ENUM
ENUM_VALUES=$(mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores -N -e "
    SELECT COLUMN_TYPE 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'usuarios' 
    AND COLUMN_NAME = 'role' 
    AND TABLE_SCHEMA = 'db_prestadores'
")

if [[ "$ENUM_VALUES" == *"master"* ]] && [[ "$ENUM_VALUES" == *"gestor"* ]]; then
    check_ok "ENUM contém 'master' e 'gestor'"
else
    check_fail "ENUM não contém valores esperados: $ENUM_VALUES"
fi

# Verificar usuários
USER_COUNT=$(mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores -N -e "
    SELECT COUNT(*) 
    FROM usuarios 
    WHERE email LIKE '%@clinfec.com.br'
")

if [ "$USER_COUNT" -ge 4 ]; then
    check_ok "Usuários de teste encontrados: $USER_COUNT"
else
    check_warn "Poucos usuários de teste: $USER_COUNT (esperado: 4)"
fi

echo ""

# 3. Listar usuários
echo -e "${YELLOW}👥 Usuários de teste:${NC}"
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores -e "
    SELECT 
        CONCAT('  ', 
            CASE role 
                WHEN 'master' THEN '👑'
                WHEN 'admin' THEN '🔧'
                WHEN 'gestor' THEN '📊'
                WHEN 'usuario' THEN '👤'
                ELSE '❓'
            END,
            ' ', email, ' (', role, ')'
        ) AS usuario
    FROM usuarios 
    WHERE email LIKE '%@clinfec.com.br' 
    ORDER BY 
        CASE role 
            WHEN 'master' THEN 1 
            WHEN 'admin' THEN 2 
            WHEN 'gestor' THEN 3 
            WHEN 'usuario' THEN 4 
            ELSE 5 
        END
" -N

echo ""

# 4. Verificar PHP-FPM
echo -e "${YELLOW}⚙️  Verificando serviços...${NC}"

if systemctl is-active --quiet php8.3-fpm-prestadores; then
    check_ok "PHP-FPM está rodando"
else
    check_fail "PHP-FPM NÃO está rodando"
fi

echo ""

# 5. Verificar logs
echo -e "${YELLOW}📋 Últimas linhas do log PHP:${NC}"
if [ -f "/var/log/php8.3-fpm/error.log" ]; then
    echo ""
    tail -5 /var/log/php8.3-fpm/error.log | sed 's/^/  | /'
    echo ""
else
    check_warn "Log PHP não encontrado em /var/log/php8.3-fpm/error.log"
fi

# 6. Verificar se debug está ativo
echo -e "${YELLOW}🔍 Verificando debug...${NC}"

if grep -q "SPRINT 67 DEBUG" src/Controllers/AuthController.php 2>/dev/null; then
    check_ok "Debug ATIVO no AuthController"
    echo -e "     ${YELLOW}💡 Lembre-se de remover após validar!${NC}"
else
    check_warn "Debug NÃO detectado no AuthController"
    echo -e "     ${YELLOW}💡 Se login falhar, ative o debug!${NC}"
fi

echo ""

# 7. Resultado final
echo -e "${BLUE}================================================================${NC}"
echo -e "${BLUE}  📊 RESULTADO DA VALIDAÇÃO${NC}"
echo -e "${BLUE}================================================================${NC}"
echo ""

if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ VALIDAÇÃO PASSOU!${NC}"
    echo ""
    echo "  Erros: $ERRORS"
    echo "  Avisos: $WARNINGS"
    echo ""
    echo -e "${GREEN}🎯 Sistema pronto para testes de login!${NC}"
    echo ""
    echo "📝 Próximos passos:"
    echo "   1. Testar login com master@clinfec.com.br / password"
    echo "   2. Monitorar logs: tail -f /var/log/php8.3-fpm/error.log"
    echo "   3. Se login funcionar, remover debug do AuthController"
    echo "   4. Informar QA que sistema está pronto"
    echo ""
else
    echo -e "${RED}❌ VALIDAÇÃO FALHOU!${NC}"
    echo ""
    echo "  Erros: $ERRORS"
    echo "  Avisos: $WARNINGS"
    echo ""
    echo "📝 Ações necessárias:"
    echo "   1. Corrigir erros acima"
    echo "   2. Re-executar deployment se necessário"
    echo "   3. Re-validar com este script"
    echo ""
    exit 1
fi

echo -e "${BLUE}================================================================${NC}"
echo ""
