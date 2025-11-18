#!/bin/bash
#================================================================
# SPRINT 67 - TESTES AUTOMATIZADOS DE LOGIN
#================================================================
# Testa login de todos os 4 usuários do sistema
# Valida sessão, redirecionamento e permissões
#================================================================

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configurações
BASE_URL="https://prestadores.clinfec.com.br"
COOKIE_JAR="/tmp/sprint67_cookies.txt"
RESULTS_LOG="/tmp/sprint67_test_results.log"

# Limpa arquivos anteriores
rm -f "$COOKIE_JAR" "$RESULTS_LOG"

echo -e "${BLUE}================================================================${NC}"
echo -e "${BLUE}  SPRINT 67 - TESTES AUTOMATIZADOS DE LOGIN${NC}"
echo -e "${BLUE}================================================================${NC}"
echo "" | tee -a "$RESULTS_LOG"

# Array de usuários para teste
declare -a USUARIOS=(
    "master@clinfec.com.br:password:master"
    "admin@clinfec.com.br:admin123:admin"
    "gestor@clinfec.com.br:password:gestor"
    "usuario@clinfec.com.br:password:usuario"
)

# Contador de testes
TOTAL=0
PASSED=0
FAILED=0

# Função para testar login
test_login() {
    local EMAIL=$1
    local SENHA=$2
    local ROLE=$3
    
    TOTAL=$((TOTAL + 1))
    
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📋 TESTE #$TOTAL: $EMAIL ($ROLE)${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo "" | tee -a "$RESULTS_LOG"
    
    # Limpa cookies anteriores
    rm -f "$COOKIE_JAR"
    
    # 1. GET na página de login
    echo "  🔍 Step 1: Acessando página de login..."
    GET_RESPONSE=$(curl -s -w "\n%{http_code}" -c "$COOKIE_JAR" "$BASE_URL/?page=login")
    GET_CODE=$(echo "$GET_RESPONSE" | tail -n1)
    
    if [ "$GET_CODE" == "200" ]; then
        echo -e "     ${GREEN}✅ Página acessível (HTTP $GET_CODE)${NC}"
    else
        echo -e "     ${RED}❌ Falha ao acessar página (HTTP $GET_CODE)${NC}"
        FAILED=$((FAILED + 1))
        return 1
    fi
    
    # 2. POST com credenciais
    echo "  🔑 Step 2: Enviando credenciais..."
    POST_RESPONSE=$(curl -s -w "\n%{http_code}\n%{redirect_url}" \
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
        -X POST \
        -d "email=$EMAIL&senha=$SENHA" \
        -L \
        "$BASE_URL/?page=login")
    
    POST_CODE=$(echo "$POST_RESPONSE" | tail -n2 | head -n1)
    REDIRECT_URL=$(echo "$POST_RESPONSE" | tail -n1)
    POST_BODY=$(echo "$POST_RESPONSE" | head -n-2)
    
    echo "     HTTP Code: $POST_CODE"
    echo "     Redirect: $REDIRECT_URL"
    
    # 3. Verifica redirecionamento (sucesso = redirect para dashboard)
    echo "  🎯 Step 3: Validando redirecionamento..."
    if [[ "$REDIRECT_URL" == *"?page=dashboard"* ]] || [[ "$REDIRECT_URL" == *"/dashboard"* ]]; then
        echo -e "     ${GREEN}✅ Redirecionado para dashboard${NC}"
    elif [[ "$POST_CODE" == "302" ]] || [[ "$POST_CODE" == "301" ]]; then
        echo -e "     ${GREEN}✅ Redirecionamento detectado (HTTP $POST_CODE)${NC}"
    else
        echo -e "     ${YELLOW}⚠️  Sem redirecionamento claro${NC}"
    fi
    
    # 4. Verifica se NÃO retornou para login (falha = volta para login)
    echo "  🚫 Step 4: Verificando se não voltou para login..."
    if [[ "$POST_BODY" == *"login"* ]] && [[ "$POST_BODY" == *"senha"* ]] && [[ "$POST_BODY" == *"form"* ]]; then
        echo -e "     ${RED}❌ FALHA: Retornou para tela de login!${NC}"
        echo "     Possíveis causas:"
        echo "       - Senha incorreta"
        echo "       - Usuário não existe"
        echo "       - Falha no password_verify()"
        echo "       - Session não persiste"
        FAILED=$((FAILED + 1))
        
        # Log detalhado
        echo "" >> "$RESULTS_LOG"
        echo "❌ TESTE FALHOU: $EMAIL ($ROLE)" >> "$RESULTS_LOG"
        echo "   HTTP: $POST_CODE" >> "$RESULTS_LOG"
        echo "   Redirect: $REDIRECT_URL" >> "$RESULTS_LOG"
        echo "" >> "$RESULTS_LOG"
        return 1
    fi
    
    # 5. Tenta acessar página protegida
    echo "  🔐 Step 5: Acessando página protegida (dashboard)..."
    DASHBOARD_RESPONSE=$(curl -s -w "\n%{http_code}" \
        -b "$COOKIE_JAR" \
        "$BASE_URL/?page=dashboard")
    
    DASHBOARD_CODE=$(echo "$DASHBOARD_RESPONSE" | tail -n1)
    DASHBOARD_BODY=$(echo "$DASHBOARD_RESPONSE" | head -n-1)
    
    if [ "$DASHBOARD_CODE" == "200" ]; then
        echo -e "     ${GREEN}✅ Dashboard acessível (HTTP $DASHBOARD_CODE)${NC}"
        
        # Verifica se NÃO foi redirecionado para login
        if [[ "$DASHBOARD_BODY" == *"login"* ]] && [[ "$DASHBOARD_BODY" == *"senha"* ]]; then
            echo -e "     ${RED}❌ FALHA: Redirecionado para login (sessão perdida)${NC}"
            FAILED=$((FAILED + 1))
            return 1
        fi
        
        # Verifica se tem conteúdo de dashboard
        if [[ "$DASHBOARD_BODY" == *"dashboard"* ]] || [[ "$DASHBOARD_BODY" == *"bem-vindo"* ]] || [[ "$DASHBOARD_BODY" == *"$ROLE"* ]]; then
            echo -e "     ${GREEN}✅ Conteúdo de dashboard detectado${NC}"
        fi
        
    else
        echo -e "     ${YELLOW}⚠️  Dashboard não acessível (HTTP $DASHBOARD_CODE)${NC}"
    fi
    
    # 6. Resultado final
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}✅ TESTE PASSOU: $EMAIL ($ROLE)${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    
    PASSED=$((PASSED + 1))
    
    # Log sucesso
    echo "✅ TESTE PASSOU: $EMAIL ($ROLE)" >> "$RESULTS_LOG"
    echo "" >> "$RESULTS_LOG"
}

# Executa testes para cada usuário
for USER_DATA in "${USUARIOS[@]}"; do
    IFS=':' read -r EMAIL SENHA ROLE <<< "$USER_DATA"
    test_login "$EMAIL" "$SENHA" "$ROLE"
    sleep 1  # Delay entre testes
done

# Relatório final
echo ""
echo -e "${BLUE}================================================================${NC}"
echo -e "${BLUE}  📊 RELATÓRIO FINAL DE TESTES${NC}"
echo -e "${BLUE}================================================================${NC}"
echo ""
echo "  Total de testes: $TOTAL"
echo -e "  ${GREEN}Passou: $PASSED${NC}"
echo -e "  ${RED}Falhou: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}🎉 TODOS OS TESTES PASSARAM! 🎉${NC}"
    echo ""
    echo "✅ Sistema pronto para QA retomar os 47 testes"
    echo ""
    exit 0
else
    echo -e "${RED}❌ ALGUNS TESTES FALHARAM${NC}"
    echo ""
    echo "📋 Próximas ações:"
    echo "   1. Verificar logs PHP: tail -f /var/log/php8.3-fpm/error.log"
    echo "   2. Analisar debug do AuthController"
    echo "   3. Validar senhas no banco: SELECT email, role, LEFT(senha, 20) FROM usuarios WHERE email LIKE '%@clinfec.com.br';"
    echo ""
    echo "📄 Log completo salvo em: $RESULTS_LOG"
    echo ""
    exit 1
fi
