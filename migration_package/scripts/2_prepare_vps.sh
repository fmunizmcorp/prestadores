#!/bin/bash
################################################################################
# SPRINT 62 - Script 2: Preparação do VPS
# 
# Este script cria o site 'prestadores' no VPS usando o script create-site.sh
# Executa configurações iniciais necessárias
#
# EXECUTAR NO VPS como root via SSH
#
# Autor: GenSpark AI
# Data: 2025-11-16
################################################################################

set -e  # Exit on error

echo "=========================================================================="
echo "  SPRINT 62 - Preparação do VPS"
echo "=========================================================================="
echo ""

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ ERRO: Este script deve ser executado como root"
    echo "   Use: sudo bash 2_prepare_vps.sh"
    exit 1
fi

# Configurações
SITE_NAME="prestadores"
SITE_USER="prestadores"
SITE_DOMAIN="prestadores.clinfec.com.br"
PHP_VERSION="8.3"

echo "📋 Configuração:"
echo "   Site Name: $SITE_NAME"
echo "   Site User: $SITE_USER"
echo "   Domain: $SITE_DOMAIN"
echo "   PHP Version: $PHP_VERSION"
echo ""

# Verificar se o script create-site.sh existe
if [ ! -f "/opt/webserver/scripts/create-site.sh" ]; then
    echo "❌ ERRO: Script create-site.sh não encontrado!"
    echo "   Caminho esperado: /opt/webserver/scripts/create-site.sh"
    echo ""
    echo "⚠️  Verifique se o VPS está configurado corretamente"
    exit 1
fi

echo "🚀 Etapa 1: Criando site usando create-site.sh..."
echo ""

# Executar create-site.sh
cd /opt/webserver/scripts
bash create-site.sh "$SITE_NAME"

if [ $? -ne 0 ]; then
    echo "❌ ERRO: Falha ao criar o site"
    exit 1
fi

echo ""
echo "✅ Site criado com sucesso!"
echo ""

# Verificar estrutura criada
SITE_ROOT="/opt/webserver/sites/$SITE_NAME"
if [ -d "$SITE_ROOT" ]; then
    echo "📁 Estrutura do site criada:"
    ls -lah "$SITE_ROOT"
    echo ""
else
    echo "❌ ERRO: Diretório do site não foi criado!"
    exit 1
fi

# Verificar configuração NGINX
NGINX_CONF="/etc/nginx/sites-available/$SITE_NAME.conf"
if [ -f "$NGINX_CONF" ]; then
    echo "✅ Configuração NGINX criada: $NGINX_CONF"
else
    echo "⚠️  Aviso: Configuração NGINX não encontrada"
fi

# Verificar pool PHP-FPM
PHP_POOL="/etc/php/${PHP_VERSION}/fpm/pool.d/${SITE_NAME}.conf"
if [ -f "$PHP_POOL" ]; then
    echo "✅ Pool PHP-FPM criado: $PHP_POOL"
else
    echo "⚠️  Aviso: Pool PHP-FPM não encontrado"
fi

# Criar diretórios adicionais necessários
echo ""
echo "🚀 Etapa 2: Criando diretórios adicionais..."
cd "$SITE_ROOT/public_html"
mkdir -p logs cache temp uploads
chown -R "$SITE_USER:$SITE_USER" logs cache temp uploads
chmod 755 logs cache temp uploads
echo "✅ Diretórios criados: logs, cache, temp, uploads"

# Configurar permissões
echo ""
echo "🚀 Etapa 3: Configurando permissões..."
chown -R "$SITE_USER:$SITE_USER" "$SITE_ROOT"
chmod 755 "$SITE_ROOT/public_html"
echo "✅ Permissões configuradas"

# Testar NGINX
echo ""
echo "🚀 Etapa 4: Testando configuração NGINX..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Configuração NGINX válida"
    
    # Recarregar NGINX
    echo "🔄 Recarregando NGINX..."
    systemctl reload nginx
    echo "✅ NGINX recarregado"
else
    echo "❌ ERRO: Configuração NGINX inválida!"
    exit 1
fi

# Restartar PHP-FPM
echo ""
echo "🚀 Etapa 5: Reiniciando PHP-FPM..."
systemctl restart php${PHP_VERSION}-fpm
echo "✅ PHP-FPM reiniciado"

# Resumo das credenciais criadas
echo ""
echo "=========================================================================="
echo "  📋 RESUMO DAS CREDENCIAIS CRIADAS"
echo "=========================================================================="
echo ""
echo "🗄️  Banco de Dados MariaDB:"
echo "   Nome: ${SITE_NAME}_db"
echo "   Usuário: ${SITE_NAME}_user"
echo "   Senha: [gerada automaticamente pelo script]"
echo ""
echo "   ⚠️  IMPORTANTE: Anote a senha que foi exibida pelo create-site.sh"
echo ""
echo "📁 Diretórios:"
echo "   Root: $SITE_ROOT"
echo "   Public: $SITE_ROOT/public_html"
echo "   Logs: $SITE_ROOT/logs"
echo ""
echo "👤 Usuário do Sistema:"
echo "   Nome: $SITE_USER"
echo "   Home: /home/$SITE_USER"
echo ""
echo "🌐 Acesso Web:"
echo "   IP: http://$(hostname -I | awk '{print $1}')"
echo "   Domain (quando DNS configurado): http://$SITE_DOMAIN"
echo ""
echo "=========================================================================="
echo "  ✅ SCRIPT 2 CONCLUÍDO"
echo "=========================================================================="
echo ""
echo "📤 Próximos passos:"
echo "   1. Anote as credenciais do banco de dados"
echo "   2. Execute o Script 3 para transferir os arquivos"
echo ""
