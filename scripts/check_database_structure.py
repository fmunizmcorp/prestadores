#!/usr/bin/env python3
"""
VERIFICAÇÃO DA ESTRUTURA DO BANCO DE DADOS
Sprint 31 - Clinfec Prestadores
"""

import mysql.connector
from mysql.connector import Error
import sys

DB_CONFIG = {
    'host': '193.203.175.82',
    'database': 'u673902663_prestadores',
    'user': 'u673902663_admin',
    'password': ';>?I4dtn~2Ga',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci'
}

def main():
    try:
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            database=DB_CONFIG['database'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password']
        )
        
        cursor = connection.cursor(dictionary=True)
        
        print("\n" + "=" * 80)
        print("📊 ESTRUTURA DA TABELA USUARIOS")
        print("=" * 80)
        cursor.execute("DESCRIBE usuarios")
        columns = cursor.fetchall()
        
        print("\nColunas encontradas:")
        for col in columns:
            print(f"   - {col['Field']:<20} {col['Type']:<20} {col['Null']:<5} {col['Key']:<5} {col['Default']}")
        
        print("\n" + "=" * 80)
        print("👥 USUÁRIOS CADASTRADOS")
        print("=" * 80)
        cursor.execute("SELECT * FROM usuarios")
        users = cursor.fetchall()
        
        print(f"\nTotal: {len(users)} usuários\n")
        for user in users:
            print(f"ID: {user['id']}")
            print(f"Nome: {user['nome']}")
            print(f"Email: {user['email']}")
            if 'tipo' in user:
                print(f"Tipo: {user['tipo']}")
            if 'perfil' in user:
                print(f"Perfil: {user['perfil']}")
            print(f"Ativo: {user['ativo']}")
            print("-" * 40)
        
        print("\n" + "=" * 80)
        print("📊 ESTRUTURA DA TABELA SERVICOS")
        print("=" * 80)
        cursor.execute("DESCRIBE servicos")
        columns = cursor.fetchall()
        
        print("\nColunas encontradas:")
        for col in columns:
            print(f"   - {col['Field']:<20} {col['Type']:<20} {col['Null']:<5} {col['Key']:<5}")
        
        print("\n" + "=" * 80)
        print("🛠️  SERVIÇOS CADASTRADOS")
        print("=" * 80)
        cursor.execute("SELECT * FROM servicos")
        servicos = cursor.fetchall()
        
        print(f"\nTotal: {len(servicos)} serviços\n")
        for servico in servicos:
            print(f"ID: {servico['id']}")
            print(f"Nome: {servico['nome']}")
            if 'tipo' in servico:
                print(f"Tipo: {servico.get('tipo', 'N/A')}")
            if 'categoria_id' in servico:
                print(f"Categoria ID: {servico.get('categoria_id', 'N/A')}")
            print("-" * 40)
        
        print("\n" + "=" * 80)
        print("📋 TODAS AS TABELAS")
        print("=" * 80)
        cursor.execute("SHOW TABLES")
        tables = cursor.fetchall()
        
        print(f"\nTotal: {len(tables)} tabelas\n")
        for table in tables:
            table_name = list(table.values())[0]
            cursor.execute(f"SELECT COUNT(*) as total FROM {table_name}")
            count = cursor.fetchone()['total']
            print(f"   {table_name:<35} {count:>5} registros")
        
        cursor.close()
        connection.close()
        
    except Error as e:
        print(f"❌ ERRO: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()
