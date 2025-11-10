#!/bin/bash
# Sprint 13 Phase 3: Comprehensive Route Testing
# Tests ALL system routes (not just the 8 main ones)

BASE_URL="https://prestadores.clinfec.com.br"
COOKIE_FILE="test_cookies.txt"

echo "═══════════════════════════════════════════════════════════════"
echo "SPRINT 13 - PHASE 3: COMPREHENSIVE ROUTE TESTING"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Login first
echo "🔐 Authenticating..."
curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" -X POST "$BASE_URL/login" \
  -d "email=admin@clinfec.com.br" \
  -d "senha=admin123" > /dev/null
echo "✅ Authentication complete"
echo ""

# Test counter
PASS=0
FAIL=0
TOTAL=0

# Function to test route
test_route() {
    local route="$1"
    local expected="$2"
    local desc="$3"
    
    TOTAL=$((TOTAL + 1))
    
    CODE=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE_FILE" "$BASE_URL$route")
    
    if [[ "$CODE" == "$expected" ]]; then
        echo "✅ $CODE - $route ($desc)"
        PASS=$((PASS + 1))
    else
        echo "❌ $CODE (expected $expected) - $route ($desc)"
        FAIL=$((FAIL + 1))
    fi
}

echo "📋 MAIN ROUTES (Primary Functionality)"
echo "─────────────────────────────────────"
test_route "/" "200" "Dashboard (root)"
test_route "/dashboard" "200" "Dashboard (explicit)"
test_route "/empresas-tomadoras" "200" "Empresas Tomadoras - List"
test_route "/empresas-prestadoras" "200" "Empresas Prestadoras - List"
test_route "/servicos" "200" "Serviços - List"
test_route "/contratos" "200" "Contratos - List"
test_route "/projetos" "200" "Projetos - List"
test_route "/atividades" "200" "Atividades - List"
test_route "/notas-fiscais" "200" "Notas Fiscais - List"
echo ""

echo "📝 FORM ROUTES (Create/Edit Pages)"
echo "─────────────────────────────────────"
test_route "/empresas-tomadoras/create" "200" "Nova Tomadora (create)"
test_route "/empresas-tomadoras/nova" "200" "Nova Tomadora (nova alias)"
test_route "/empresas-prestadoras/create" "200" "Nova Prestadora (create)"
test_route "/empresas-prestadoras/novo" "200" "Nova Prestadora (novo alias)"
test_route "/servicos/create" "200" "Novo Serviço (create)"
test_route "/servicos/novo" "200" "Novo Serviço (novo alias)"
test_route "/contratos/create" "200" "Novo Contrato (create)"
test_route "/contratos/novo" "200" "Novo Contrato (novo alias)"
test_route "/projetos/create" "200" "Novo Projeto"
test_route "/projetos/novo" "200" "Novo Projeto (novo alias)"
test_route "/atividades/create" "200" "Nova Atividade"
test_route "/atividades/nova" "200" "Nova Atividade (nova alias)"
echo ""

echo "🔀 ALIAS ROUTES (Alternative URLs)"
echo "─────────────────────────────────────"
test_route "/proj" "200" "Projetos (alias /proj)"
test_route "/projects" "200" "Projetos (alias /projects)"
test_route "/ativ" "200" "Atividades (alias /ativ)"
test_route "/tasks" "200" "Atividades (alias /tasks)"
test_route "/nf" "200" "Notas Fiscais (alias /nf)"
test_route "/invoices" "200" "Notas Fiscais (alias /invoices)"
test_route "/finance" "200" "Financeiro (alias /finance)"
test_route "/fin" "200" "Financeiro (alias /fin)"
echo ""

echo "🆕 NEW ROUTES (Phase 2.5-2.8)"
echo "─────────────────────────────────────"
test_route "/pagamentos" "200" "Pagamentos"
test_route "/custos" "200" "Custos"
test_route "/relatorios" "200" "Relatórios"
test_route "/perfil" "200" "Perfil"
test_route "/configuracoes" "200" "Configurações"
echo ""

echo "💰 FINANCEIRO ROUTES (Complex Module)"
echo "─────────────────────────────────────"
test_route "/financeiro" "200" "Financeiro - Main"
echo ""

echo "🔒 AUTH ROUTES"
echo "─────────────────────────────────────"
test_route "/login" "200" "Login Page"
test_route "/logout" "302" "Logout (redirect expected)"
echo ""

echo "═══════════════════════════════════════════════════════════════"
echo "TEST RESULTS"
echo "═══════════════════════════════════════════════════════════════"
echo "Total Tests: $TOTAL"
echo "Passed: $PASS"
echo "Failed: $FAIL"
echo ""

PERCENTAGE=$(( (PASS * 100) / TOTAL ))
echo "Success Rate: $PERCENTAGE%"
echo ""

if [[ $FAIL -eq 0 ]]; then
    echo "🎉 ALL TESTS PASSED!"
else
    echo "⚠️  $FAIL test(s) failed"
fi

# Cleanup
rm -f "$COOKIE_FILE"
