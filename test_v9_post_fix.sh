#!/bin/bash
# Test V9 - After public/index.php fix

BASE_URL="https://prestadores.clinfec.com.br"

echo "🧪 TESTE V9 - PÓS CORREÇÃO public/index.php"
echo "=============================================="
echo ""

# Test modules
modules=("dashboard" "empresas-tomadoras" "empresas-prestadoras" "contratos" "projetos" "servicos")
passed=0
failed=0

for module in "${modules[@]}"; do
    echo "📋 Testing: ?page=$module"
    
    # Get response
    response=$(curl -sI "$BASE_URL/?page=$module" 2>&1)
    http_code=$(echo "$response" | grep "^HTTP" | tail -1 | awk '{print $2}')
    location=$(echo "$response" | grep -i "^location:" | awk '{print $2}' | tr -d '\r')
    
    if [ "$http_code" == "302" ] && [[ "$location" == *"login"* ]]; then
        echo "   ✅ PASSED: HTTP $http_code → $location"
        ((passed++))
    elif [ "$http_code" == "200" ]; then
        # Check if page has content
        content=$(curl -s "$BASE_URL/?page=$module" 2>&1)
        content_length=${#content}
        
        if [ $content_length -gt 100 ]; then
            echo "   ✅ PASSED: HTTP $http_code (content: $content_length bytes)"
            ((passed++))
        else
            echo "   ❌ FAILED: HTTP $http_code but EMPTY ($content_length bytes)"
            ((failed++))
        fi
    else
        echo "   ❌ FAILED: HTTP $http_code"
        ((failed++))
    fi
    
    echo ""
done

echo "================================================"
echo "📊 RESULTADO FINAL"
echo "   ✅ Passed: $passed/${#modules[@]}"
echo "   ❌ Failed: $failed/${#modules[@]}"
echo "   Taxa de sucesso: $(( passed * 100 / ${#modules[@]} ))%"
echo "================================================"

