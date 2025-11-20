#!/bin/bash
################################################################################
# SPRINT 62 - Script 1: Export Manual do Banco de Dados
# 
# Este script deve ser executado NO SERVIDOR HOSTINGER via SSH ou terminal
# Se não tiver acesso SSH, use o phpMyAdmin para exportar manualmente
#
# Autor: GenSpark AI
# Data: 2025-11-16
################################################################################

set -e  # Exit on error

echo "=========================================================================="
echo "  SPRINT 62 - Export do Banco de Dados Hostinger"
echo "=========================================================================="
echo ""

# Credenciais do banco de dados
DB_HOST="localhost"
DB_NAME="u673902663_prestadores"
DB_USER="u673902663_admin"
DB_PASS=";>?I4dtn~2Ga"

# Arquivo de saída
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUTPUT_FILE="prestadores_db_backup_${TIMESTAMP}.sql"

echo "📋 Configuração:"
echo "   Database: $DB_NAME"
echo "   Output: $OUTPUT_FILE"
echo ""

# Verificar se mysqldump está disponível
if ! command -v mysqldump &> /dev/null; then
    echo "❌ ERRO: mysqldump não encontrado!"
    echo ""
    echo "⚠️  SOLUÇÃO ALTERNATIVA: Use o phpMyAdmin"
    echo "   1. Acesse: https://hpanel.hostinger.com"
    echo "   2. Vá em 'Bancos de Dados MySQL'"
    echo "   3. Clique em 'Gerenciar' no banco: $DB_NAME"
    echo "   4. Na aba 'Exportar', selecione:"
    echo "      - Método: Rápido"
    echo "      - Formato: SQL"
    echo "   5. Clique em 'Executar'"
    echo "   6. Salve o arquivo como: $OUTPUT_FILE"
    echo ""
    exit 1
fi

# Executar mysqldump
echo "🚀 Iniciando export..."
echo ""

mysqldump \
    --host="$DB_HOST" \
    --user="$DB_USER" \
    --password="$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-table \
    --add-locks \
    --create-options \
    --disable-keys \
    --extended-insert \
    --quick \
    --set-charset \
    "$DB_NAME" > "$OUTPUT_FILE"

# Verificar resultado
if [ -f "$OUTPUT_FILE" ]; then
    FILE_SIZE=$(stat -f%z "$OUTPUT_FILE" 2>/dev/null || stat -c%s "$OUTPUT_FILE" 2>/dev/null || echo "0")
    echo "✅ Export concluído com sucesso!"
    echo ""
    echo "📊 Informações:"
    echo "   Arquivo: $OUTPUT_FILE"
    echo "   Tamanho: $FILE_SIZE bytes ($(($FILE_SIZE / 1024 / 1024)) MB)"
    echo ""
    echo "📤 Próximo passo:"
    echo "   Baixe este arquivo para seu computador local"
    echo "   ou transfira diretamente para o VPS"
    echo ""
else
    echo "❌ ERRO: Arquivo de backup não foi criado!"
    exit 1
fi

echo "=========================================================================="
echo "  ✅ SCRIPT 1 CONCLUÍDO"
echo "=========================================================================="
