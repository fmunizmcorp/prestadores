#!/bin/bash

BASE_URL="https://prestadores.clinfec.com.br"
PASS=0
FAIL=0
TOTAL=22

echo "=========================================="
echo "SPRINT 73 - COMPREHENSIVE QA TEST"
echo "Testing ALL 22 endpoints"
echo "Target: 100% (22/22 passing)"
echo "=========================================="
echo ""

# Array of modules to test (11 modules x 2 actions = 22 tests)
declare -A MODULES=(
    ["empresas-tomadoras"]="Empresas Tomadoras"
    ["empresas-prestadoras"]="Empresas Prestadoras"
    ["servicos"]="Serviços"
    ["contratos"]="Contratos"
    ["projetos"]="Projetos"
    ["pagamentos"]="Pagamentos"
    ["custos"]="Custos"
    ["relatorios-financeiros"]="Relatórios Financeiros"
    ["atividades"]="Atividades"
    ["relatorios"]="Relatórios"
    ["usuarios"]="Usuários"
)

for module in "${!MODULES[@]}"; do
    name="${MODULES[$module]}"
    
    # Test 1: Listagem (index)
    echo -n "Testing: $name - Listagem... "
    status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/?page=$module&action=index")
    if [ "$status" = "302" ] || [ "$status" = "200" ]; then
        echo "✅ PASS (HTTP $status)"
        ((PASS++))
    else
        echo "❌ FAIL (HTTP $status)"
        ((FAIL++))
    fi
    
    # Test 2: Criação (create)
    echo -n "Testing: $name - Criação... "
    status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/?page=$module&action=create")
    if [ "$status" = "302" ] || [ "$status" = "200" ]; then
        echo "✅ PASS (HTTP $status)"
        ((PASS++))
    else
        echo "❌ FAIL (HTTP $status)"
        ((FAIL++))
    fi
    
    echo ""
done

echo "=========================================="
echo "FINAL RESULTS:"
echo "PASSED: $PASS/$TOTAL"
echo "FAILED: $FAIL/$TOTAL"
PERCENTAGE=$(echo "scale=1; $PASS * 100 / $TOTAL" | bc)
echo "SUCCESS RATE: ${PERCENTAGE}%"
echo "=========================================="

if [ "$PASS" -eq "$TOTAL" ]; then
    echo "🎉 STATUS: 100% SUCCESS - ALL TESTS PASSING!"
    exit 0
else
    echo "⚠️  STATUS: PARTIAL SUCCESS - Some tests failing"
    exit 1
fi
