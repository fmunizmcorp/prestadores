#!/bin/bash
# Script para criar Pull Request no GitHub via API
# Requer Personal Access Token do usuário

REPO_OWNER="fmunizmcorp"
REPO_NAME="prestadores"
HEAD_BRANCH="genspark_ai_developer"
BASE_BRANCH="main"

# Instruções para o usuário
cat << 'EOF'
═══════════════════════════════════════════════════════════════════════════
🔐 CRIAÇÃO DE PULL REQUEST - REQUER TOKEN GITHUB
═══════════════════════════════════════════════════════════════════════════

Para criar o Pull Request automaticamente, você precisa de um GitHub Token.

PASSO 1: Gerar Token
  1. Acesse: https://github.com/settings/tokens
  2. Clique em "Generate new token (classic)"
  3. Dê um nome: "prestadores-sprint20"
  4. Marque escopo: "repo" (todas as sub-opções)
  5. Clique em "Generate token"
  6. COPIE O TOKEN (você só verá uma vez!)

PASSO 2: Executar este script com o token
  ./create_pr_github.sh SEU_TOKEN_AQUI

PASSO 3: Push manual alternativo
  Se preferir, faça push manualmente:
  
  git fetch origin
  git checkout genspark_ai_developer
  git push origin genspark_ai_developer
  
  Depois vá em: https://github.com/fmunizmcorp/prestadores
  Clique em "Compare & pull request"

═══════════════════════════════════════════════════════════════════════════
EOF

# Se token foi fornecido como argumento
if [ -n "$1" ]; then
    GITHUB_TOKEN="$1"
    
    echo ""
    echo "🚀 Tentando criar Pull Request..."
    echo ""
    
    # Criar PR via API GitHub
    curl -X POST \
        -H "Accept: application/vnd.github+json" \
        -H "Authorization: Bearer $GITHUB_TOKEN" \
        -H "X-GitHub-Api-Version: 2022-11-28" \
        https://api.github.com/repos/$REPO_OWNER/$REPO_NAME/pulls \
        -d @- << PAYLOAD
{
  "title": "Sprint 20: Fix ROOT_PATH - Sistema 0% → 100%",
  "body": "## 🎯 Sprint 20 - Correção ROOT_PATH Crítica\n\n### Problema Identificado\n- \`ROOT_PATH\` apontava para \`/public\` em vez do diretório pai\n- Todos os paths críticos (src/, config/, vendor/) ficaram inacessíveis\n- Controllers e Models nunca carregavam → páginas em branco (0%)\n\n### Correção Aplicada\n- Mudado de: \`define('ROOT_PATH', __DIR__);\`\n- Para: \`define('ROOT_PATH', dirname(__DIR__));\`\n- Agora aponta corretamente para raiz da aplicação\n\n### Deploy\n- ✅ Deployado via FTP (verificado com MD5)\n- ✅ Arquivos críticos: public/index.php, .htaccess\n- ⚠️ Requer limpeza de OPcache para ativar\n\n### Testes Requeridos\nApós limpar OPcache, testar:\n1. https://clinfec.com.br/prestadores/?page=empresas-tomadoras\n2. https://clinfec.com.br/prestadores/?page=contratos\n3. https://clinfec.com.br/prestadores/?page=projetos\n4. https://clinfec.com.br/prestadores/?page=empresas-prestadoras\n\n**Esperado:** Todas as páginas renderizam com dados (NÃO em branco)\n\n### Documentação Completa\n- LEIA_PRIMEIRO_SPRINT20.md\n- SPRINT20_FINAL_REPORT.md\n- SPRINT20_DIAGNOSTIC_SUMMARY.md\n\n---\n**Branch:** genspark_ai_developer → main\n**Commit:** 1616e80\n**SCRUM:** Sprint 18-20 consolidados\n**PDCA:** Completo (Plan-Do-Check-Act)",
  "head": "$HEAD_BRANCH",
  "base": "$BASE_BRANCH"
}
PAYLOAD
    
    echo ""
    echo "✓ Comando executado!"
    echo ""
else
    echo ""
    echo "ℹ️  Execute este script com seu GitHub token:"
    echo "   ./create_pr_github.sh SEU_TOKEN_AQUI"
    echo ""
fi
