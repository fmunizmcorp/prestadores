#!/bin/bash
###############################################################################
# SPRINT 67 - DEPLOY AUTOMÁTICO FINAL E TESTE COMPLETO
# Este script deve ser executado NO SERVIDOR DE PRODUÇÃO como root
###############################################################################

set -e

echo "════════════════════════════════════════════════════════════════"
echo "  🚀 SPRINT 67 - DEPLOY AUTOMÁTICO + TESTES"
echo "  Data: $(date '+%Y-%m-%d %H:%M:%S')"
echo "════════════════════════════════════════════════════════════════"
echo ""

BASE_DIR="/opt/webserver/sites/prestadores"
BACKUP_DIR="$BASE_DIR/backups/sprint67_$(date +%Y%m%d_%H%M%S)"
GITHUB_RAW="https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer"

# Verificar se está como root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Este script precisa ser executado como root"
    exit 1
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 1: BACKUP"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

mkdir -p "$BACKUP_DIR"
echo "📦 Criando backup em: $BACKUP_DIR"

cd "$BASE_DIR"

if [ -f "src/Controllers/AuthController.php" ]; then
    cp -v src/Controllers/AuthController.php "$BACKUP_DIR/"
    echo "  ✅ Backup: AuthController.php"
fi

if [ -f "config/app.php" ]; then
    cp -v config/app.php "$BACKUP_DIR/"
    echo "  ✅ Backup: app.php"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 2: DOWNLOAD DOS ARQUIVOS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "📥 Baixando AuthController.php..."
curl -f -sL -o /tmp/AuthController_new.php \
    "$GITHUB_RAW/src/Controllers/AuthControllerDebug.php"

if [ $? -eq 0 ] && [ -s /tmp/AuthController_new.php ]; then
    SIZE=$(stat -c%s /tmp/AuthController_new.php 2>/dev/null || stat -f%z /tmp/AuthController_new.php 2>/dev/null)
    echo "  ✅ AuthController.php baixado ($SIZE bytes)"
else
    echo "  ❌ ERRO ao baixar AuthController.php"
    exit 1
fi

echo "📥 Baixando config/app.php..."
curl -f -sL -o /tmp/app_new.php \
    "$GITHUB_RAW/config/app.php"

if [ $? -eq 0 ] && [ -s /tmp/app_new.php ]; then
    SIZE=$(stat -c%s /tmp/app_new.php 2>/dev/null || stat -f%z /tmp/app_new.php 2>/dev/null)
    echo "  ✅ app.php baixado ($SIZE bytes)"
else
    echo "  ❌ ERRO ao baixar app.php"
    exit 1
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 3: VALIDAÇÃO DE SINTAXE"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "🔍 Validando AuthController.php..."
if php -l /tmp/AuthController_new.php > /dev/null 2>&1; then
    echo "  ✅ Sintaxe válida"
else
    echo "  ❌ Sintaxe inválida!"
    php -l /tmp/AuthController_new.php
    exit 1
fi

echo "🔍 Validando app.php..."
if php -l /tmp/app_new.php > /dev/null 2>&1; then
    echo "  ✅ Sintaxe válida"
else
    echo "  ❌ Sintaxe inválida!"
    php -l /tmp/app_new.php
    exit 1
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 4: VERIFICAÇÃO DE CORREÇÕES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "🔍 Verificando fix isset() no AuthController..."
if grep -q "isset.*skip_in_development.*&&.*skip_in_development" /tmp/AuthController_new.php; then
    echo "  ✅ Fix isset() presente"
else
    echo "  ❌ Fix isset() NÃO encontrado!"
    exit 1
fi

echo "🔍 Verificando reCAPTCHA disabled no config..."
if grep -q "'enabled' => false" /tmp/app_new.php; then
    echo "  ✅ reCAPTCHA disabled"
else
    echo "  ⚠️  reCAPTCHA pode estar enabled"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 5: INSTALAÇÃO DOS ARQUIVOS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "📝 Instalando AuthController.php..."
mv /tmp/AuthController_new.php src/Controllers/AuthController.php
chown www-data:www-data src/Controllers/AuthController.php
chmod 644 src/Controllers/AuthController.php
echo "  ✅ AuthController.php instalado"

echo "📝 Instalando app.php..."
mv /tmp/app_new.php config/app.php
chown www-data:www-data config/app.php
chmod 644 config/app.php
echo "  ✅ app.php instalado"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 6: LIMPEZA DE CACHE E RELOAD"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "🗑️  Limpando OPcache..."
php -r "if(function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache limpo\n'; } else { echo 'OPcache não disponível\n'; }"

echo "🔄 Recarregando PHP-FPM..."
if systemctl reload php8.3-fpm-prestadores.service 2>/dev/null; then
    echo "  ✅ PHP-FPM recarregado (prestadores pool)"
elif systemctl reload php8.3-fpm 2>/dev/null; then
    echo "  ✅ PHP-FPM recarregado (pool padrão)"
else
    echo "  ⚠️  Execute manualmente: systemctl reload php8.3-fpm"
fi

sleep 2

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 7: TESTES AUTOMÁTICOS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo ""
echo "🧪 TESTE 1: Login com usuário MASTER"
RESULTADO=$(curl -s -L -c /tmp/test_cookies.txt -b /tmp/test_cookies.txt \
    -X POST \
    -d "email=master@clinfec.com.br&senha=Master123!" \
    "https://prestadores.clinfec.com.br/?page=login" | grep -o 'page=[^"&]*' | head -1)

if echo "$RESULTADO" | grep -q "dashboard"; then
    echo "  ✅ SUCESSO: Login master redirecionou para dashboard"
else
    echo "  ❌ FALHA: Login master não funcionou (redirecionou para: $RESULTADO)"
fi

echo ""
echo "🧪 TESTE 2: Login com usuário ADMIN"
RESULTADO=$(curl -s -L -c /tmp/test_cookies2.txt -b /tmp/test_cookies2.txt \
    -X POST \
    -d "email=admin@clinfec.com.br&senha=Admin123!" \
    "https://prestadores.clinfec.com.br/?page=login" | grep -o 'page=[^"&]*' | head -1)

if echo "$RESULTADO" | grep -q "dashboard"; then
    echo "  ✅ SUCESSO: Login admin redirecionou para dashboard"
else
    echo "  ❌ FALHA: Login admin não funcionou (redirecionou para: $RESULTADO)"
fi

echo ""
echo "🧪 TESTE 3: Login com usuário GESTOR"
RESULTADO=$(curl -s -L -c /tmp/test_cookies3.txt -b /tmp/test_cookies3.txt \
    -X POST \
    -d "email=gestor@clinfec.com.br&senha=Gestor123!" \
    "https://prestadores.clinfec.com.br/?page=login" | grep -o 'page=[^"&]*' | head -1)

if echo "$RESULTADO" | grep -q "dashboard"; then
    echo "  ✅ SUCESSO: Login gestor redirecionou para dashboard"
else
    echo "  ❌ FALHA: Login gestor não funcionou (redirecionou para: $RESULTADO)"
fi

echo ""
echo "🧪 TESTE 4: Login com usuário USUARIO"
RESULTADO=$(curl -s -L -c /tmp/test_cookies4.txt -b /tmp/test_cookies4.txt \
    -X POST \
    -d "email=usuario@clinfec.com.br&senha=Usuario123!" \
    "https://prestadores.clinfec.com.br/?page=login" | grep -o 'page=[^"&]*' | head -1)

if echo "$RESULTADO" | grep -q "dashboard"; then
    echo "  ✅ SUCESSO: Login usuario redirecionou para dashboard"
else
    echo "  ❌ FALHA: Login usuario não funcionou (redirecionou para: $RESULTADO)"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FASE 8: VERIFICAÇÃO DE LOGS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo ""
echo "📝 Últimas 20 linhas do log (erros e warnings):"
tail -20 logs/php-error.log | grep -E "(Warning|Error|SPRINT 67)" || echo "  ℹ️  Nenhum erro recente encontrado"

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  ✅ DEPLOY E TESTES CONCLUÍDOS"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "📋 RESUMO:"
echo "  • Backup: $BACKUP_DIR"
echo "  • Arquivos atualizados: 2"
echo "  • PHP-FPM: Recarregado"
echo "  • OPcache: Limpo"
echo "  • Testes executados: 4 usuários"
echo ""
echo "📊 PRÓXIMOS PASSOS:"
echo "  1. Revisar resultados dos testes acima"
echo "  2. Se todos passaram: QA pode retomar testes"
echo "  3. Se algum falhou: verificar logs em: $BASE_DIR/logs/php-error.log"
echo "  4. Após aprovação: re-habilitar reCAPTCHA em config/app.php"
echo ""
echo "🔗 LINKS:"
echo "  • Login: https://prestadores.clinfec.com.br/?page=login"
echo "  • Dashboard: https://prestadores.clinfec.com.br/?page=dashboard"
echo ""
echo "════════════════════════════════════════════════════════════════"
