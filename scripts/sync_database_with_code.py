#!/usr/bin/env python3
"""
SINCRONIZAÇÃO DO BANCO DE DADOS COM O CÓDIGO
Sprint 31 - Clinfec Prestadores
Metodologia: SCRUM + PDCA

Este script analisa as diferenças entre:
1. Estrutura do banco de dados existente (MySQL)
2. Requisitos do código PHP (src/Database.php, models, etc.)

E propõe alterações para manter sincronização.
"""

import mysql.connector
from mysql.connector import Error
import sys
from datetime import datetime

DB_CONFIG = {
    'host': '193.203.175.82',
    'database': 'u673902663_prestadores',
    'user': 'u673902663_admin',
    'password': ';>?I4dtn~2Ga',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci'
}

# Tabelas essenciais esperadas pelo código
EXPECTED_TABLES = {
    'usuarios': {
        'required_columns': ['id', 'nome', 'email', 'senha', 'ativo', 'created_at', 'updated_at'],
        'optional_columns': ['tipo', 'perfil', 'role']
    },
    'empresas_prestadoras': {
        'required_columns': ['id', 'razao_social', 'cnpj', 'ativo', 'created_at', 'updated_at']
    },
    'empresas_tomadoras': {
        'required_columns': ['id', 'razao_social', 'nome_fantasia', 'cnpj', 'ativo', 'created_at', 'updated_at']
    },
    'servicos': {
        'required_columns': ['id', 'nome', 'ativo', 'created_at', 'updated_at']
    },
    'contratos': {
        'required_columns': ['id', 'numero_contrato', 'empresa_tomadora_id', 'empresa_prestadora_id', 
                            'data_inicio', 'status', 'created_at', 'updated_at']
    },
    'atestados': {
        'required_columns': ['id', 'contrato_id', 'numero', 'mes_referencia', 'ano_referencia', 
                            'valor_bruto', 'valor_liquido', 'status', 'created_at', 'updated_at']
    },
    'faturas': {
        'required_columns': ['id', 'atestado_id', 'numero_nf', 'data_emissao', 'data_vencimento', 
                            'valor_total', 'status', 'created_at', 'updated_at']
    },
    'documentos': {
        'required_columns': ['id', 'entidade_tipo', 'entidade_id', 'nome_arquivo', 'caminho', 
                            'ativo', 'created_at']
    },
    'database_version': {
        'required_columns': ['id', 'version', 'installed_at']
    }
}

def connect_db():
    """Conecta ao banco de dados"""
    try:
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            database=DB_CONFIG['database'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password']
        )
        return connection
    except Error as e:
        print(f"❌ ERRO ao conectar: {e}")
        sys.exit(1)

def get_table_structure(connection, table_name):
    """Retorna a estrutura de uma tabela"""
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute(f"DESCRIBE {table_name}")
        columns = cursor.fetchall()
        cursor.close()
        return [col['Field'] for col in columns]
    except Error:
        return None

def analyze_database(connection):
    """Analisa a estrutura atual do banco"""
    cursor = connection.cursor(dictionary=True)
    
    print("\n" + "=" * 80)
    print("🔍 ANÁLISE DO BANCO DE DADOS - SPRINT 31")
    print("=" * 80)
    print(f"Data/Hora: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print()
    
    # Listar todas as tabelas
    cursor.execute("SHOW TABLES")
    all_tables = [list(t.values())[0] for t in cursor.fetchall()]
    
    print(f"📊 Total de tabelas no banco: {len(all_tables)}")
    print()
    
    # Verificar tabelas essenciais
    print("=" * 80)
    print("✅ TABELAS ESSENCIAIS (Verificação)")
    print("=" * 80)
    print()
    
    missing_tables = []
    incomplete_tables = []
    complete_tables = []
    
    for table_name, requirements in EXPECTED_TABLES.items():
        if table_name not in all_tables:
            missing_tables.append(table_name)
            print(f"❌ {table_name:<30} NÃO EXISTE")
        else:
            structure = get_table_structure(connection, table_name)
            required_cols = requirements['required_columns']
            missing_cols = [col for col in required_cols if col not in structure]
            
            cursor.execute(f"SELECT COUNT(*) as total FROM {table_name}")
            count = cursor.fetchone()['total']
            
            if missing_cols:
                incomplete_tables.append((table_name, missing_cols))
                print(f"⚠️  {table_name:<30} INCOMPLETA ({count} registros)")
                print(f"   Colunas faltando: {', '.join(missing_cols)}")
            else:
                complete_tables.append(table_name)
                print(f"✅ {table_name:<30} OK ({count} registros)")
    
    print()
    
    # Resumo
    print("=" * 80)
    print("📈 RESUMO DA ANÁLISE")
    print("=" * 80)
    print()
    print(f"✅ Tabelas completas: {len(complete_tables)}")
    print(f"⚠️  Tabelas incompletas: {len(incomplete_tables)}")
    print(f"❌ Tabelas faltando: {len(missing_tables)}")
    print()
    
    # Tabelas extras
    extra_tables = [t for t in all_tables if t not in EXPECTED_TABLES]
    if extra_tables:
        print("=" * 80)
        print("ℹ️  TABELAS ADICIONAIS (Não requeridas pelo código base)")
        print("=" * 80)
        print()
        for table in extra_tables:
            cursor.execute(f"SELECT COUNT(*) as total FROM {table}")
            count = cursor.fetchone()['total']
            print(f"   {table:<35} {count:>5} registros")
        print()
    
    # Verificar dados críticos
    print("=" * 80)
    print("🔑 DADOS CRÍTICOS")
    print("=" * 80)
    print()
    
    # Usuários
    if 'usuarios' in all_tables:
        cursor.execute("SELECT COUNT(*) as total FROM usuarios WHERE ativo = 1")
        active_users = cursor.fetchone()['total']
        cursor.execute("SELECT email FROM usuarios WHERE ativo = 1 LIMIT 3")
        users = cursor.fetchall()
        
        print(f"👥 Usuários ativos: {active_users}")
        for user in users:
            print(f"   - {user['email']}")
        print()
    
    # Empresas
    if 'empresas_prestadoras' in all_tables:
        cursor.execute("SELECT COUNT(*) as total FROM empresas_prestadoras WHERE ativo = 1")
        prestadoras = cursor.fetchone()['total']
        print(f"🏢 Empresas prestadoras ativas: {prestadoras}")
    
    if 'empresas_tomadoras' in all_tables:
        cursor.execute("SELECT COUNT(*) as total FROM empresas_tomadoras WHERE ativo = 1")
        tomadoras = cursor.fetchone()['total']
        print(f"🏢 Empresas tomadoras ativas: {tomadoras}")
    print()
    
    # Contratos e atestados
    if 'contratos' in all_tables:
        cursor.execute("SELECT COUNT(*) as total FROM contratos WHERE status = 'ativo'")
        contratos = cursor.fetchone()['total']
        print(f"📄 Contratos ativos: {contratos}")
    
    if 'atestados' in all_tables:
        cursor.execute("SELECT COUNT(*) as total FROM atestados")
        atestados = cursor.fetchone()['total']
        print(f"📋 Atestados emitidos: {atestados}")
    print()
    
    # Versão do banco
    if 'database_version' in all_tables:
        print("=" * 80)
        print("📌 VERSÃO DO BANCO DE DADOS")
        print("=" * 80)
        print()
        cursor.execute("SELECT * FROM database_version ORDER BY installed_at DESC LIMIT 1")
        version = cursor.fetchone()
        if version:
            print(f"   Versão: {version['version']}")
            if 'description' in version:
                print(f"   Descrição: {version['description']}")
            print(f"   Instalado em: {version['installed_at']}")
        print()
    
    cursor.close()
    
    # Retornar estatísticas
    return {
        'total_tables': len(all_tables),
        'complete_tables': len(complete_tables),
        'incomplete_tables': len(incomplete_tables),
        'missing_tables': len(missing_tables),
        'complete_list': complete_tables,
        'incomplete_list': incomplete_tables,
        'missing_list': missing_tables
    }

def generate_recommendations(stats):
    """Gera recomendações de manutenção"""
    print("=" * 80)
    print("💡 RECOMENDAÇÕES DE MANUTENÇÃO - PDCA")
    print("=" * 80)
    print()
    
    if stats['missing_tables'] > 0:
        print("🔴 PRIORIDADE ALTA:")
        print()
        print("   Tabelas faltando devem ser criadas:")
        for table in stats['missing_list']:
            print(f"   - CREATE TABLE {table}")
        print()
    
    if stats['incomplete_tables'] > 0:
        print("🟡 PRIORIDADE MÉDIA:")
        print()
        print("   Tabelas incompletas devem ter colunas adicionadas:")
        for table, cols in stats['incomplete_list']:
            print(f"   - ALTER TABLE {table} ADD COLUMN...")
            for col in cols:
                print(f"     * {col}")
        print()
    
    if stats['complete_tables'] == len(EXPECTED_TABLES):
        print("✅ ÓTIMO!")
        print()
        print("   Todas as 9 tabelas essenciais estão presentes e completas.")
        print("   O sistema está pronto para funcionar.")
        print()
        print("📋 PRÓXIMAS ATIVIDADES (Sprint 32):")
        print()
        print("   1. ✅ Testar login (admin@clinfec.com.br)")
        print("   2. 🔧 Corrigir Dashboard vazio")
        print("   3. 🔧 Corrigir formulário Empresas Tomadoras")
        print("   4. 🔧 Corrigir erro ao carregar Contratos")
        print("   5. 📦 Implementar módulos faltantes")
        print()
    
    print("=" * 80)
    print("🎯 METODOLOGIA SCRUM + PDCA")
    print("=" * 80)
    print()
    print("PLAN:    ✅ Análise da estrutura atual concluída")
    print("DO:      ⏳ Executar correções necessárias")
    print("CHECK:   ⏳ Validar funcionalidades após correções")
    print("ACT:     ⏳ Ajustar e otimizar conforme feedback")
    print()

def main():
    print()
    print("╔" + "=" * 78 + "╗")
    print("║" + " " * 78 + "║")
    print("║" + " SINCRONIZAÇÃO BANCO DE DADOS + CÓDIGO - SPRINT 31 ".center(78) + "║")
    print("║" + " Sistema: Clinfec Prestadores ".center(78) + "║")
    print("║" + " Metodologia: SCRUM + PDCA ".center(78) + "║")
    print("║" + " " * 78 + "║")
    print("╚" + "=" * 78 + "╝")
    
    connection = None
    
    try:
        # Conectar
        connection = connect_db()
        print(f"✅ Conectado ao MySQL em {DB_CONFIG['host']}")
        
        # Analisar
        stats = analyze_database(connection)
        
        # Recomendações
        generate_recommendations(stats)
        
        print("=" * 80)
        print("✅ ANÁLISE CONCLUÍDA COM SUCESSO")
        print("=" * 80)
        print()
        
        return 0
        
    except Exception as e:
        print(f"❌ ERRO: {e}")
        return 1
        
    finally:
        if connection and connection.is_connected():
            connection.close()

if __name__ == "__main__":
    sys.exit(main())
