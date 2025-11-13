#!/bin/bash

echo "================================================================="
echo "   TESTE DIRETO V8 - Após Deploy index.php"
echo "================================================================="
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

BASE="https://prestadores.clinfec.com.br"

# Test URLs
declare -A TESTS
TESTS=(
    ["BC-001"]="$BASE/?page=empresas-tomadoras&action=create"
    ["BC-002"]="$BASE/?page=contratos"
    ["BC-003"]="$BASE/?page=documentos"
    ["BC-004"]="$BASE/?page=treinamentos"
    ["BC-005"]="$BASE/?page=aso"
    ["BC-006"]="$BASE/?page=relatorios"
)

PASSED=0
FAILED=0

for code in "${!TESTS[@]}"; do
    url="${TESTS[$code]}"
    echo "[$code] Testando: $url"
    
    response=$(curl -sI "$url" 2>&1)
    http_code=$(echo "$response" | grep "^HTTP" | awk '{print $2}')
    location=$(echo "$response" | grep -i "^location:" | awk '{print $2}' | tr -d '\r')
    
    echo "  HTTP Code: $http_code"
    
    if [ "$http_code" == "302" ] && [[ "$location" == *"/login"* ]]; then
        echo "  Status: ✅ PASSOU - Redirecionando para login (correto)"
        ((PASSED++))
    elif [ "$http_code" == "200" ]; then
        echo "  Status: ✅ PASSOU - Página carregou"
        ((PASSED++))
    else
        echo "  Status: ❌ FALHOU - HTTP $http_code"
        echo "  Location: $location"
        ((FAILED++))
    fi
    
    echo "----------------------------------------------------------------"
    echo ""
done

TOTAL=$((PASSED + FAILED))
RATE=$((PASSED * 100 / TOTAL))

echo "================================================================="
echo "   RESUMO"
echo "================================================================="
echo "Total: $TOTAL"
echo "Passou: $PASSED ✅"
echo "Falhou: $FAILED ❌"
echo "Taxa de Sucesso: $RATE%"
echo ""

if [ $RATE -ge 80 ]; then
    echo "CONCLUSÃO: ✅ SISTEMA FUNCIONAL"
else
    echo "CONCLUSÃO: ❌ SISTEMA NÃO FUNCIONAL"
fi

echo "================================================================="
