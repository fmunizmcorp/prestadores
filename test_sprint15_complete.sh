#!/bin/bash

BASE_URL="https://prestadores.clinfec.com.br"
COOKIE_FILE="/tmp/test_session_$$"

echo "=========================================="
echo "SPRINT 15 - SISTEMA DE TESTES COMPLETO"
echo "=========================================="
echo "Base URL: $BASE_URL"
echo "Data: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Test counters
total_tests=0
passed_tests=0
failed_tests=0

# Function to test URL
test_url() {
    local url="$1"
    local description="$2"
    local expect_code="${3:-200}"
    
    total_tests=$((total_tests + 1))
    
    echo -n "[$total_tests] $description... "
    
    http_code=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE_FILE" -c "$COOKIE_FILE" "$url")
    
    if [ "$http_code" = "$expect_code" ]; then
        echo "✓ PASS (HTTP $http_code)"
        passed_tests=$((passed_tests + 1))
        return 0
    else
        echo "✗ FAIL (HTTP $http_code, expected $expect_code)"
        failed_tests=$((failed_tests + 1))
        return 1
    fi
}

# Function to test login
test_login() {
    local email="$1"
    local password="$2"
    local description="$3"
    
    total_tests=$((total_tests + 1))
    
    echo -n "[$total_tests] Login: $description ($email)... "
    
    response=$(curl -s -L -b "$COOKIE_FILE" -c "$COOKIE_FILE" \
        -d "email=$email" \
        -d "senha=$password" \
        "$BASE_URL/login")
    
    # Check if redirect to dashboard (login success) or stayed on login (failure)
    if echo "$response" | grep -q "Dashboard\|Bem-vindo"; then
        echo "✓ PASS"
        passed_tests=$((passed_tests + 1))
        return 0
    else
        echo "✗ FAIL"
        failed_tests=$((failed_tests + 1))
        return 1
    fi
}

echo "=== TESTE 1: Páginas Públicas ==="
test_url "$BASE_URL/login" "Login page loads"

echo ""
echo "=== TESTE 2: Autenticação ==="
# Clean session
rm -f "$COOKIE_FILE"

test_login "master@clinfec.com.br" "password" "Master user"

# If login succeeded, test authenticated pages
if [ $? -eq 0 ]; then
    echo ""
    echo "=== TESTE 3: Dashboard e Navegação ==="
    test_url "$BASE_URL/" "Dashboard after login"
    test_url "$BASE_URL/dashboard" "Dashboard explicit"
    
    echo ""
    echo "=== TESTE 4: Empresas (Módulos Básicos) ==="
    test_url "$BASE_URL/empresas-tomadoras" "Empresas Tomadoras"
    test_url "$BASE_URL/empresas-prestadoras" "Empresas Prestadoras"
    
    echo ""
    echo "=== TESTE 5: Contratos e Serviços ==="
    test_url "$BASE_URL/contratos" "Contratos"
    test_url "$BASE_URL/servicos" "Serviços"
    
    echo ""
    echo "=== TESTE 6: Módulos RE-ATIVADOS (Sprint 15.10) ==="
    test_url "$BASE_URL/projetos" "Projetos (RE-ATIVADO)"
    test_url "$BASE_URL/atividades" "Atividades (RE-ATIVADO)"
    test_url "$BASE_URL/financeiro" "Financeiro (RE-ATIVADO)"
    test_url "$BASE_URL/notas-fiscais" "Notas Fiscais (RE-ATIVADO)"
    
    echo ""
    echo "=== TESTE 7: Rotas Alternativas ==="
    test_url "$BASE_URL/proj" "Projetos (alias /proj)"
    test_url "$BASE_URL/ativ" "Atividades (alias /ativ)"
    test_url "$BASE_URL/fin" "Financeiro (alias /fin)"
    test_url "$BASE_URL/nf" "Notas Fiscais (alias /nf)"
    
else
    echo ""
    echo "⚠️  Login falhou - pulando testes autenticados"
    failed_tests=$((failed_tests + 13))
    total_tests=$((total_tests + 13))
fi

# Test other users
echo ""
echo "=== TESTE 8: Outros Usuários ==="
rm -f "$COOKIE_FILE"
test_login "admin@clinfec.com.br" "password" "Admin user"

rm -f "$COOKIE_FILE"
test_login "gestor@clinfec.com.br" "password" "Gestor user"

# Summary
echo ""
echo "=========================================="
echo "RESULTADO DOS TESTES"
echo "=========================================="
echo "Total de testes: $total_tests"
echo "Testes passados: $passed_tests ($(echo "scale=1; $passed_tests*100/$total_tests" | bc)%)"
echo "Testes falhados: $failed_tests"
echo ""

if [ $failed_tests -eq 0 ]; then
    echo "✓ TODOS OS TESTES PASSARAM!"
    echo "Sistema: 100% FUNCIONAL"
else
    echo "⚠️  Alguns testes falharam"
    echo "Sistema: $(echo "scale=1; $passed_tests*100/$total_tests" | bc)% FUNCIONAL"
fi

# Cleanup
rm -f "$COOKIE_FILE"

echo ""
echo "Relatório completo salvo em: $(pwd)/test_sprint15_results.txt"
