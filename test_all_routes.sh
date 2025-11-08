#!/bin/bash

BASE_URL="https://prestadores.clinfec.com.br"

echo "===== TESTANDO TODAS AS ROTAS ====="
echo ""

routes=(
    "/"
    "/login"
    "/dashboard"
    "/empresas-tomadoras"
    "/empresas-prestadoras"
    "/servicos"
    "/contratos"
    "/projetos"
    "/atividades"
    "/financeiro"
    "/notas-fiscais"
)

for route in "${routes[@]}"; do
    status=$(curl -s -o /dev/null -w "%{http_code}" -L "$BASE_URL$route" 2>/dev/null)
    if [ "$status" = "200" ] || [ "$status" = "302" ]; then
        echo "✓ $route: HTTP $status"
    else
        echo "✗ $route: HTTP $status"
    fi
done

echo ""
echo "===== TESTE DE LOGIN ====="
curl -s "$BASE_URL/login" | grep -q "Sistema Clinfec" && echo "✓ Página de login OK" || echo "✗ Erro na página de login"
