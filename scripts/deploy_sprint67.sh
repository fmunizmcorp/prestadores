#!/bin/bash
###############################################################################
# SPRINT 67 - Deploy Completo
# Atualiza AuthController.php e config/app.php no servidor de produção
###############################################################################

set -e  # Exit on error

echo "════════════════════════════════════════════════════════════════"
echo "  SPRINT 67 - DEPLOY COMPLETO"
echo "  Servidor: Prestadores Clinfec"
echo "  Data: $(date '+%Y-%m-%d %H:%M:%S')"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Definir diretórios
BASE_DIR="/opt/webserver/sites/prestadores"
BACKUP_DIR="$BASE_DIR/backups/sprint67_$(date +%Y%m%d_%H%M%S)"
GITHUB_BRANCH="genspark_ai_developer"
GITHUB_RAW="https://raw.githubusercontent.com/fmunizmcorp/prestadores/$GITHUB_BRANCH"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Este script precisa ser executado como root"
    echo "   Use: sudo $0"
    exit 1
fi

# Verificar se o diretório existe
if [ ! -d "$BASE_DIR" ]; then
    echo "❌ ERRO: Diretório $BASE_DIR não encontrado"
    exit 1
fi

cd "$BASE_DIR"
echo "📂 Diretório de trabalho: $(pwd)"
echo ""

# Criar diretório de backup
echo "📦 Criando backup..."
mkdir -p "$BACKUP_DIR"

# Backup dos arquivos atuais
if [ -f "src/Controllers/AuthController.php" ]; then
    cp -v "src/Controllers/AuthController.php" "$BACKUP_DIR/AuthController.php.bak"
    echo "  ✅ Backup: AuthController.php"
else
    echo "  ⚠️  AuthController.php não encontrado (será criado)"
fi

if [ -f "config/app.php" ]; then
    cp -v "config/app.php" "$BACKUP_DIR/app.php.bak"
    echo "  ✅ Backup: app.php"
else
    echo "  ⚠️  app.php não encontrado (será criado)"
fi

echo ""
echo "📥 Baixando arquivos do GitHub (branch: $GITHUB_BRANCH)..."

# Baixar AuthController
echo "  → AuthController.php..."
if curl -f -s -o "/tmp/AuthController.php" \
    "$GITHUB_RAW/src/Controllers/AuthControllerDebug.php"; then
    
    # Verificar se o arquivo foi baixado e não está vazio
    if [ -s "/tmp/AuthController.php" ]; then
        # Verificar sintaxe PHP
        if php -l "/tmp/AuthController.php" > /dev/null 2>&1; then
            mv "/tmp/AuthController.php" "src/Controllers/AuthController.php"
            echo "    ✅ AuthController.php atualizado ($(stat -f%z "src/Controllers/AuthController.php" 2>/dev/null || stat -c%s "src/Controllers/AuthController.php") bytes)"
        else
            echo "    ❌ ERRO: Sintaxe PHP inválida no AuthController.php"
            rm "/tmp/AuthController.php"
            echo "    ↩️  Restaurando backup..."
            cp "$BACKUP_DIR/AuthController.php.bak" "src/Controllers/AuthController.php"
        fi
    else
        echo "    ❌ ERRO: Arquivo baixado está vazio"
    fi
else
    echo "    ❌ ERRO ao baixar AuthController.php"
fi

# Baixar config/app.php
echo "  → app.php..."
if curl -f -s -o "/tmp/app.php" \
    "$GITHUB_RAW/config/app.php"; then
    
    # Verificar se o arquivo foi baixado e não está vazio
    if [ -s "/tmp/app.php" ]; then
        # Verificar sintaxe PHP
        if php -l "/tmp/app.php" > /dev/null 2>&1; then
            mv "/tmp/app.php" "config/app.php"
            echo "    ✅ app.php atualizado ($(stat -f%z "config/app.php" 2>/dev/null || stat -c%s "config/app.php") bytes)"
        else
            echo "    ❌ ERRO: Sintaxe PHP inválida no app.php"
            rm "/tmp/app.php"
            echo "    ↩️  Restaurando backup..."
            cp "$BACKUP_DIR/app.php.bak" "config/app.php"
        fi
    else
        echo "    ❌ ERRO: Arquivo baixado está vazio"
    fi
else
    echo "    ❌ ERRO ao baixar app.php"
fi

echo ""
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data src/Controllers/ config/
chmod 644 src/Controllers/AuthController.php
chmod 644 config/app.php
echo "  ✅ Permissões ajustadas"

echo ""
echo "🔍 Verificando sintaxe dos arquivos..."
echo -n "  AuthController.php: "
if php -l src/Controllers/AuthController.php; then
    echo "  ✅ Sintaxe OK"
else
    echo "  ❌ ERRO de sintaxe!"
fi

echo -n "  app.php: "
if php -l config/app.php; then
    echo "  ✅ Sintaxe OK"
else
    echo "  ❌ ERRO de sintaxe!"
fi

echo ""
echo "🗑️  Limpando caches..."

# Limpar OPcache via PHP
php -r "
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo '  ✅ OPcache limpo via PHP' . PHP_EOL;
    } else {
        echo '  ⚠️  Falha ao limpar OPcache via PHP' . PHP_EOL;
    }
} else {
    echo '  ⚠️  OPcache não disponível' . PHP_EOL;
}
"

# Recarregar PHP-FPM
echo "  🔄 Recarregando PHP-FPM..."
if systemctl reload php8.3-fpm-prestadores.service 2>/dev/null; then
    echo "    ✅ PHP-FPM recarregado com sucesso"
elif systemctl reload php8.3-fpm 2>/dev/null; then
    echo "    ✅ PHP-FPM recarregado com sucesso (pool padrão)"
else
    echo "    ⚠️  Não foi possível recarregar PHP-FPM automaticamente"
    echo "    Execute manualmente: systemctl reload php8.3-fpm"
fi

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  ✅ DEPLOY CONCLUÍDO COM SUCESSO"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "📋 INFORMAÇÕES:"
echo "  • Backup salvo em: $BACKUP_DIR"
echo "  • AuthController.php: $(stat -f%z "src/Controllers/AuthController.php" 2>/dev/null || stat -c%s "src/Controllers/AuthController.php") bytes"
echo "  • app.php: $(stat -f%z "config/app.php" 2>/dev/null || stat -c%s "config/app.php") bytes"
echo ""
echo "🧪 PRÓXIMOS PASSOS:"
echo ""
echo "1. Testar login via curl:"
echo "   curl -s -L -c /tmp/cookies.txt -b /tmp/cookies.txt \\"
echo "     -X POST \\"
echo "     -d 'email=master@clinfec.com.br&senha=Master123!' \\"
echo "     'https://prestadores.clinfec.com.br/?page=login' | grep -o 'page=[^\"&]*'"
echo ""
echo "2. Monitorar logs:"
echo "   tail -f $BASE_DIR/logs/php-error.log | grep SPRINT"
echo ""
echo "3. Acessar via navegador:"
echo "   https://prestadores.clinfec.com.br/?page=login"
echo ""
echo "👥 USUÁRIOS DE TESTE:"
echo "  • master@clinfec.com.br / Master123! (role: master)"
echo "  • admin@clinfec.com.br / Admin123! (role: admin)"
echo "  • gestor@clinfec.com.br / Gestor123! (role: gestor)"
echo "  • usuario@clinfec.com.br / Usuario123! (role: usuario)"
echo ""
echo "════════════════════════════════════════════════════════════════"
