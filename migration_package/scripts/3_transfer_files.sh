#!/bin/bash
################################################################################
# SPRINT 62 - Script 3: Transferência de Arquivos para o VPS
# 
# Este script pode ser executado:
# - De sua máquina local (se tiver os arquivos baixados)
# - Do servidor de origem (Hostinger) via SSH
#
# Transfere os arquivos essenciais via SCP para o VPS
#
# Autor: GenSpark AI
# Data: 2025-11-16
################################################################################

set -e  # Exit on error

echo "=========================================================================="
echo "  SPRINT 62 - Transferência de Arquivos para VPS"
echo "=========================================================================="
echo ""

# Configurações do VPS
VPS_HOST="72.61.53.222"
VPS_USER="root"
VPS_PASS="Jm@D@KDPnw7Q"
VPS_PORT="22"  # ou "2222" se a porta 22 não funcionar
SITE_NAME="prestadores"
VPS_TARGET="/opt/webserver/sites/$SITE_NAME/public_html"

# Diretório local (ajuste conforme necessário)
LOCAL_SOURCE="./migration_backup"

echo "📋 Configuração:"
echo "   VPS: $VPS_USER@$VPS_HOST:$VPS_PORT"
echo "   Origem: $LOCAL_SOURCE"
echo "   Destino: $VPS_TARGET"
echo ""

# Verificar se o diretório de origem existe
if [ ! -d "$LOCAL_SOURCE" ]; then
    echo "❌ ERRO: Diretório de origem não encontrado: $LOCAL_SOURCE"
    echo ""
    echo "⚠️  Ajuste a variável LOCAL_SOURCE no script para apontar"
    echo "   para o diretório onde estão os arquivos baixados"
    exit 1
fi

# Verificar se sshpass está instalado (necessário para automação)
if ! command -v sshpass &> /dev/null; then
    echo "⚠️  sshpass não encontrado. Instalando..."
    
    # Detectar sistema operacional
    if [ -f /etc/debian_version ]; then
        sudo apt-get update && sudo apt-get install -y sshpass
    elif [ -f /etc/redhat-release ]; then
        sudo yum install -y sshpass
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        echo "❌ No macOS, use: brew install hudochenkov/sshpass/sshpass"
        echo "   Ou execute manualmente com: scp -P $VPS_PORT -r $LOCAL_SOURCE/* $VPS_USER@$VPS_HOST:$VPS_TARGET/"
        exit 1
    else
        echo "❌ ERRO: Sistema operacional não suportado para instalação automática"
        echo "   Instale sshpass manualmente ou use SCP diretamente"
        exit 1
    fi
fi

echo "🚀 Etapa 1: Testando conexão SSH ao VPS..."
sshpass -p "$VPS_PASS" ssh -o StrictHostKeyChecking=no -p "$VPS_PORT" "$VPS_USER@$VPS_HOST" "echo 'Conexão OK!'"

if [ $? -ne 0 ]; then
    echo "❌ ERRO: Não foi possível conectar ao VPS"
    echo "   Verifique:"
    echo "   - IP: $VPS_HOST"
    echo "   - Porta: $VPS_PORT"
    echo "   - Usuário: $VPS_USER"
    echo "   - Senha: $VPS_PASS"
    exit 1
fi

echo "✅ Conexão SSH estabelecida!"
echo ""

echo "🚀 Etapa 2: Criando backup do conteúdo atual do VPS (se existir)..."
sshpass -p "$VPS_PASS" ssh -p "$VPS_PORT" "$VPS_USER@$VPS_HOST" "
    if [ -d '$VPS_TARGET' ] && [ \"\$(ls -A $VPS_TARGET 2>/dev/null)\" ]; then
        BACKUP_DIR='/tmp/${SITE_NAME}_backup_\$(date +%Y%m%d_%H%M%S)'
        echo \"📦 Criando backup em: \$BACKUP_DIR\"
        mkdir -p \$BACKUP_DIR
        cp -r $VPS_TARGET/* \$BACKUP_DIR/ 2>/dev/null || true
        echo \"✅ Backup criado\"
    else
        echo \"ℹ️  Sem conteúdo para backup\"
    fi
"
echo ""

echo "🚀 Etapa 3: Transferindo arquivos essenciais..."
echo ""

# Transferir cada diretório importante
for dir in src public config; do
    if [ -d "$LOCAL_SOURCE/$dir" ]; then
        echo "📤 Transferindo: $dir/"
        sshpass -p "$VPS_PASS" scp -P "$VPS_PORT" -r \
            "$LOCAL_SOURCE/$dir" \
            "$VPS_USER@$VPS_HOST:$VPS_TARGET/"
        
        if [ $? -eq 0 ]; then
            echo "   ✅ $dir/ transferido"
        else
            echo "   ❌ Erro ao transferir $dir/"
            exit 1
        fi
    else
        echo "⚠️  Diretório não encontrado: $LOCAL_SOURCE/$dir"
    fi
    echo ""
done

# Transferir arquivo .htaccess
if [ -f "$LOCAL_SOURCE/.htaccess" ]; then
    echo "📤 Transferindo: .htaccess"
    sshpass -p "$VPS_PASS" scp -P "$VPS_PORT" \
        "$LOCAL_SOURCE/.htaccess" \
        "$VPS_USER@$VPS_HOST:$VPS_TARGET/"
    echo "   ✅ .htaccess transferido"
else
    echo "⚠️  Arquivo .htaccess não encontrado"
fi

echo ""
echo "🚀 Etapa 4: Ajustando permissões no VPS..."
sshpass -p "$VPS_PASS" ssh -p "$VPS_PORT" "$VPS_USER@$VPS_HOST" "
    chown -R $SITE_NAME:$SITE_NAME $VPS_TARGET
    chmod -R 755 $VPS_TARGET
    chmod -R 775 $VPS_TARGET/logs 2>/dev/null || true
    chmod -R 775 $VPS_TARGET/cache 2>/dev/null || true
    chmod -R 775 $VPS_TARGET/uploads 2>/dev/null || true
"
echo "✅ Permissões ajustadas"

echo ""
echo "🚀 Etapa 5: Verificando transferência..."
sshpass -p "$VPS_PASS" ssh -p "$VPS_PORT" "$VPS_USER@$VPS_HOST" "
    echo \"📊 Conteúdo transferido:\"
    ls -lah $VPS_TARGET/ | tail -20
    echo \"\"
    echo \"📁 Estrutura de diretórios:\"
    find $VPS_TARGET -maxdepth 2 -type d
"

echo ""
echo "=========================================================================="
echo "  ✅ SCRIPT 3 CONCLUÍDO"
echo "=========================================================================="
echo ""
echo "📤 Próximos passos:"
echo "   1. Execute o Script 4 para restaurar o banco de dados"
echo "   2. Execute o Script 5 para atualizar as configurações"
echo ""
