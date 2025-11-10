#!/bin/bash
BASE_URL="https://prestadores.clinfec.com.br"

echo "================================================================"
echo "TESTE DE ROTAS APÓS CORREÇÕES DO BANCO"
echo "================================================================"
echo ""

# Login
echo "🔐 Login..."
curl -s -c cookies.txt -b cookies.txt -X POST "$BASE_URL/login" \
  -d "email=admin@clinfec.com.br" -d "senha=admin123" > /dev/null

# Testar rotas
declare -A ROUTES=(
    ["/dashboard"]="Dashboard"
    ["/empresas-tomadoras"]="Empresas Tomadoras"
    ["/empresas-prestadoras"]="Empresas Prestadoras"
    ["/servicos"]="Serviços"
    ["/contratos"]="Contratos"
    ["/projetos"]="Projetos"
    ["/atividades"]="Atividades"
    ["/notas-fiscais"]="Notas Fiscais"
)

ok=0
fail=0

for route in "${!ROUTES[@]}"; do
    name="${ROUTES[$route]}"
    echo -n "Testando $name ($route) ... "
    
    code=$(curl -s -o /dev/null -w "%{http_code}" -b cookies.txt "$BASE_URL$route")
    
    if [[ "$code" == "200" ]]; then
        echo "✅ $code"
        ((ok++))
    else
        echo "❌ $code"
        ((fail++))
    fi
done

echo ""
echo "================================================================"
echo "RESULTADO: $ok OK / $fail FALHOU"
echo "================================================================"
