#!/bin/bash
################################################################################
# SPRINT 62 - Script 4: Restauração do Banco de Dados no VPS
# 
# Este script restaura o backup do banco de dados no VPS
# 
# EXECUTAR NO VPS como root via SSH
#
# Autor: GenSpark AI
# Data: 2025-11-16
################################################################################

set -e  # Exit on error

echo "=========================================================================="
echo "  SPRINT 62 - Restauração do Banco de Dados"
echo "=========================================================================="
echo ""

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ ERRO: Este script deve ser executado como root"
    echo "   Use: sudo bash 4_restore_database.sh [arquivo_backup.sql]"
    exit 1
fi

# Parâmetros
BACKUP_FILE="$1"
SITE_NAME="prestadores"
DB_NAME="${SITE_NAME}_db"
DB_USER="${SITE_NAME}_user"

# Verificar se o arquivo de backup foi fornecido
if [ -z "$BACKUP_FILE" ]; then
    echo "❌ ERRO: Arquivo de backup não especificado"
    echo ""
    echo "Uso: bash 4_restore_database.sh /caminho/para/backup.sql"
    echo ""
    echo "📥 Você precisa primeiro transferir o arquivo SQL para o VPS:"
    echo "   scp -P 22 backup.sql root@72.61.53.222:/tmp/"
    echo ""
    exit 1
fi

# Verificar se o arquivo existe
if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ ERRO: Arquivo não encontrado: $BACKUP_FILE"
    exit 1
fi

FILE_SIZE=$(stat -c%s "$BACKUP_FILE")
echo "📋 Configuração:"
echo "   Arquivo: $BACKUP_FILE"
echo "   Tamanho: $FILE_SIZE bytes ($(($FILE_SIZE / 1024 / 1024)) MB)"
echo "   Database: $DB_NAME"
echo "   User: $DB_USER"
echo ""

# Solicitar senha do banco de dados
echo "🔐 Digite a senha do banco de dados $DB_NAME"
echo "   (Senha gerada pelo Script 2 - create-site.sh)"
read -s -p "   Senha: " DB_PASS
echo ""
echo ""

if [ -z "$DB_PASS" ]; then
    echo "❌ ERRO: Senha não pode ser vazia"
    exit 1
fi

# Testar conexão
echo "🚀 Etapa 1: Testando conexão com o banco de dados..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" &> /dev/null

if [ $? -ne 0 ]; then
    echo "❌ ERRO: Não foi possível conectar ao banco de dados"
    echo "   Verifique a senha e tente novamente"
    exit 1
fi

echo "✅ Conexão estabelecida!"
echo ""

# Fazer backup do banco atual (se existir conteúdo)
echo "🚀 Etapa 2: Verificando conteúdo atual do banco..."
TABLE_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" | wc -l)

if [ $TABLE_COUNT -gt 1 ]; then
    echo "⚠️  Banco contém $((TABLE_COUNT - 1)) tabela(s)"
    BACKUP_TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    CURRENT_BACKUP="/tmp/${DB_NAME}_backup_before_restore_${BACKUP_TIMESTAMP}.sql"
    
    echo "📦 Criando backup do conteúdo atual..."
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$CURRENT_BACKUP"
    echo "   ✅ Backup salvo em: $CURRENT_BACKUP"
else
    echo "ℹ️  Banco está vazio, não é necessário backup"
fi
echo ""

# Limpar banco atual
echo "🚀 Etapa 3: Limpando banco de dados atual..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS = 0;"

# Obter lista de tabelas e dropar
TABLES=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" | tail -n +2)
for table in $TABLES; do
    echo "   🗑️  Removendo tabela: $table"
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DROP TABLE IF EXISTS \`$table\`;"
done

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS = 1;"
echo "✅ Banco limpo"
echo ""

# Restaurar backup
echo "🚀 Etapa 4: Restaurando backup..."
echo "   ⏳ Isso pode levar alguns minutos..."
echo ""

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Backup restaurado com sucesso!"
else
    echo "❌ ERRO: Falha ao restaurar backup"
    
    if [ -f "$CURRENT_BACKUP" ]; then
        echo ""
        echo "🔄 Tentando restaurar backup anterior..."
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$CURRENT_BACKUP"
        echo "✅ Backup anterior restaurado (rollback completo)"
    fi
    
    exit 1
fi

# Verificar restauração
echo ""
echo "🚀 Etapa 5: Verificando restauração..."
TABLE_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" | wc -l)
echo "   📊 Tabelas restauradas: $((TABLE_COUNT - 1))"

if [ $TABLE_COUNT -gt 1 ]; then
    echo ""
    echo "📋 Lista de tabelas:"
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;"
    
    echo ""
    echo "📊 Contagem de registros por tabela:"
    TABLES=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" | tail -n +2)
    for table in $TABLES; do
        COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM \`$table\`;" | tail -n 1)
        printf "   %-30s: %s registros\n" "$table" "$COUNT"
    done
else
    echo "❌ ERRO: Nenhuma tabela foi restaurada!"
    exit 1
fi

echo ""
echo "=========================================================================="
echo "  ✅ SCRIPT 4 CONCLUÍDO"
echo "=========================================================================="
echo ""
echo "📝 Anote estas informações para o próximo script:"
echo ""
echo "   Database: $DB_NAME"
echo "   User: $DB_USER"
echo "   Password: [a senha que você digitou]"
echo "   Host: localhost"
echo ""
echo "📤 Próximo passo:"
echo "   Execute o Script 5 para atualizar as configurações da aplicação"
echo ""
