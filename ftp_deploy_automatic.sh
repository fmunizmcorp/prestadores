#!/bin/bash

# FTP Credentials
FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"
FTP_BASE="ftp://${FTP_USER}:${FTP_PASS}@${FTP_HOST}"

echo "========================================"
echo "  DEPLOY AUTOMÁTICO VIA FTP - SPRINT 18"
echo "========================================"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Critical files to deploy
CRITICAL_FILES=(
    "index.php"
    "config/config.php"
    "config/database.php"
    ".htaccess"
)

# Controllers (case-sensitive directories)
CONTROLLERS=(
    "src/Controllers/AuthController.php"
    "src/Controllers/EmpresaTomadoraController.php"
    "src/Controllers/EmpresaPrestadoraController.php"
    "src/Controllers/ContratoController.php"
    "src/Controllers/ServicoController.php"
    "src/Controllers/ProjetoController.php"
)

# Models (case-sensitive)
MODELS=(
    "src/Models/Usuario.php"
    "src/Models/EmpresaTomadora.php"
    "src/Models/EmpresaPrestadora.php"
    "src/Models/Contrato.php"
    "src/Models/Servico.php"
    "src/Models/Projeto.php"
)

# Views modificadas no Sprint 17
VIEWS=(
    "src/Views/dashboard/index.php"
    "src/Views/empresas-tomadoras/create.php"
    "src/Views/empresas-tomadoras/edit.php"
    "src/Views/empresas-tomadoras/show.php"
    "src/Views/empresas-prestadoras/create.php"
    "src/Views/empresas-prestadoras/edit.php"
    "src/Views/empresas-prestadoras/index.php"
    "src/Views/empresas-prestadoras/show.php"
    "src/Views/contratos/create.php"
    "src/Views/contratos/edit.php"
    "src/Views/contratos/index.php"
    "src/Views/contratos/show.php"
    "src/Views/servicos/create.php"
    "src/Views/servicos/edit.php"
    "src/Views/servicos/index.php"
    "src/Views/servicos/show.php"
    "src/Views/layouts/header.php"
    "src/Views/layouts/footer.php"
)

SUCCESS=0
FAILED=0

deploy_file() {
    local file=$1
    
    if [ ! -f "$file" ]; then
        echo "⚠️  SKIP: $file (não existe localmente)"
        return
    fi
    
    echo -n "📤 Uploading: $file ... "
    
    # Upload via curl
    result=$(curl --user "${FTP_USER}:${FTP_PASS}" \
                  -T "$file" \
                  "ftp://${FTP_HOST}/$file" \
                  --create-dirs \
                  -s -w "%{http_code}" 2>&1)
    
    if [ $? -eq 0 ]; then
        echo "✅ OK"
        ((SUCCESS++))
    else
        echo "❌ FAILED"
        ((FAILED++))
    fi
}

echo "1️⃣  DEPLOYING CRITICAL FILES..."
echo "----------------------------------------"
for file in "${CRITICAL_FILES[@]}"; do
    deploy_file "$file"
done

echo ""
echo "2️⃣  DEPLOYING CONTROLLERS..."
echo "----------------------------------------"
for file in "${CONTROLLERS[@]}"; do
    deploy_file "$file"
done

echo ""
echo "3️⃣  DEPLOYING MODELS..."
echo "----------------------------------------"
for file in "${MODELS[@]}"; do
    deploy_file "$file"
done

echo ""
echo "4️⃣  DEPLOYING VIEWS (Sprint 17)..."
echo "----------------------------------------"
for file in "${VIEWS[@]}"; do
    deploy_file "$file"
done

echo ""
echo "========================================"
echo "  RESULTADO FINAL"
echo "========================================"
echo "✅ Sucesso: $SUCCESS arquivos"
echo "❌ Falhas:  $FAILED arquivos"
echo ""

if [ $FAILED -eq 0 ]; then
    echo "🎉 DEPLOY 100% COMPLETO!"
else
    echo "⚠️  Alguns arquivos falharam"
fi

echo "========================================"
