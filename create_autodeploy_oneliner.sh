#!/bin/bash
###############################################################################
# Criar o arquivo auto_deploy_sprint67.php no servidor via download do GitHub
###############################################################################

echo "════════════════════════════════════════════════════════════════"
echo "  CRIANDO AUTO-DEPLOY NO SERVIDOR VIA GITHUB"
echo "════════════════════════════════════════════════════════════════"
echo ""

# URL do arquivo no GitHub
GITHUB_URL="https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/public_html/auto_deploy_sprint67.php"

# Executar no servidor via SSH (se disponível) OU via comando manual
COMMAND="curl -sL '$GITHUB_URL' -o /opt/webserver/sites/prestadores/public_html/auto_deploy_sprint67.php && chmod 644 /opt/webserver/sites/prestadores/public_html/auto_deploy_sprint67.php && chown www-data:www-data /opt/webserver/sites/prestadores/public_html/auto_deploy_sprint67.php && echo '✅ Arquivo criado em: /opt/webserver/sites/prestadores/public_html/auto_deploy_sprint67.php' && echo '🌐 Acesse: https://prestadores.clinfec.com.br/auto_deploy_sprint67.php'"

echo "📋 COMANDO A SER EXECUTADO NO SERVIDOR:"
echo ""
echo "$COMMAND"
echo ""
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "💡 INSTRUÇÕES:"
echo ""
echo "1. Copie o comando acima"
echo "2. Acesse o servidor via SSH"
echo "3. Execute como root (ou com sudo)"
echo "4. Depois acesse: https://prestadores.clinfec.com.br/auto_deploy_sprint67.php"
echo "   (use credenciais: clinfec / Cf2025api#)"
echo "5. Clique em 'EXECUTAR DEPLOY AGORA'"
echo ""
echo "════════════════════════════════════════════════════════════════"
