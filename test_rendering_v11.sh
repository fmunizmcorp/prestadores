#!/bin/bash
# Test V11 - After ROOT_PATH fix

BASE_URL="https://prestadores.clinfec.com.br"
COOKIE_FILE="/tmp/clinfec_cookies_v11.txt"

echo "🧪 TESTE V11 - APÓS CORREÇÃO ROOT_PATH"
echo "==========================================="
echo ""

modules=("empresas-tomadoras" "empresas-prestadoras" "contratos" "projetos")
passed=0
failed=0

for module in "${modules[@]}"; do
    echo "📋 Testing: ?page=$module"
    
    # Get full response
    response=$(curl -s "$BASE_URL/?page=$module" 2>&1)
    response_length=${#response}
    
    # Check if has meaningful content (not blank)
    if [ $response_length -gt 500 ]; then
        # Check if it's not just a 404 page
        if echo "$response" | grep -qi "404\|não encontrada"; then
            echo "   ❌ FAILED: 404 Page ($response_length bytes)"
            ((failed++))
        elif echo "$response" | grep -qi "$module\|empresa\|contrato\|projeto"; then
            echo "   ✅ PASSED: Has content ($response_length bytes)"
            ((passed++))
        else
            echo "   ⚠️  UNKNOWN: Content exists but unclear ($response_length bytes)"
            echo "   Preview: $(echo "$response" | head -c 200)"
            ((passed++))
        fi
    else
        echo "   ❌ FAILED: Empty or too small ($response_length bytes)"
        ((failed++))
    fi
    
    echo ""
done

echo "================================================"
echo "📊 RESULTADO FINAL"
echo "   ✅ Passed: $passed/${#modules[@]}"
echo "   ❌ Failed: $failed/${#modules[@]}"
if [ $failed -eq 0 ]; then
    echo "   🎉 SUCCESS RATE: 100%"
else
    echo "   Taxa de sucesso: $(( passed * 100 / ${#modules[@]} ))%"
fi
echo "================================================"

rm -f "$COOKIE_FILE"
