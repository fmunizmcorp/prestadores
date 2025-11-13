#!/bin/bash
# Test V9 - Authenticated user flow simulation

BASE_URL="https://prestadores.clinfec.com.br"
COOKIE_FILE="/tmp/clinfec_cookies.txt"

echo "🧪 TESTE V9 - FLUXO AUTENTICADO COMPLETO"
echo "========================================"
echo ""

# Step 1: Get login page and extract CSRF token
echo "📋 Step 1: Accessing login page..."
LOGIN_PAGE=$(curl -s -c "$COOKIE_FILE" "$BASE_URL/?page=login")

if echo "$LOGIN_PAGE" | grep -q "csrf_token"; then
    echo "✅ Login page loaded with CSRF token"
else
    echo "⚠️  No CSRF token found (might not be needed)"
fi

# Step 2: Attempt login (try without credentials - should show form)
echo ""
echo "📋 Step 2: Testing login endpoint..."
LOGIN_RESPONSE=$(curl -s -b "$COOKIE_FILE" -c "$COOKIE_FILE" \
  -X POST "$BASE_URL/?page=login&action=authenticate" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=&password=" \
  2>&1)

echo "$LOGIN_RESPONSE" | head -c 500
echo ""

# Step 3: Test if dashboard loads after redirect
echo ""
echo "📋 Step 3: Testing dashboard page..."
DASHBOARD_RESPONSE=$(curl -s -b "$COOKIE_FILE" \
  "$BASE_URL/?page=dashboard" 2>&1)

if [ -z "$DASHBOARD_RESPONSE" ]; then
    echo "❌ DASHBOARD: Página EM BRANCO"
elif echo "$DASHBOARD_RESPONSE" | grep -qi "dashboard"; then
    echo "✅ DASHBOARD: Conteúdo detectado"
    echo "   Preview: $(echo "$DASHBOARD_RESPONSE" | head -c 200)"
else
    echo "⚠️  DASHBOARD: Resposta suspeita"
    echo "   Size: ${#DASHBOARD_RESPONSE} bytes"
    echo "   Preview: $(echo "$DASHBOARD_RESPONSE" | head -c 300)"
fi

# Step 4: Test empresas-tomadoras
echo ""
echo "📋 Step 4: Testing empresas-tomadoras..."
TOMADORAS_RESPONSE=$(curl -s -b "$COOKIE_FILE" \
  "$BASE_URL/?page=empresas-tomadoras" 2>&1)

if [ -z "$TOMADORAS_RESPONSE" ]; then
    echo "❌ EMPRESAS-TOMADORAS: Página EM BRANCO"
elif echo "$TOMADORAS_RESPONSE" | grep -qi "empresa"; then
    echo "✅ EMPRESAS-TOMADORAS: Conteúdo detectado"
else
    echo "⚠️  EMPRESAS-TOMADORAS: Resposta suspeita (${#TOMADORAS_RESPONSE} bytes)"
fi

echo ""
echo "🏁 Teste concluído"

rm -f "$COOKIE_FILE"
