#!/usr/bin/env python3
"""
Deploy Sprint 57 - Correção CRÍTICA do Database.php
Bug #7: Call to undefined method App\\Database::prepare()

Adiciona métodos wrapper essenciais:
- prepare()
- query()
- exec()
- lastInsertId()
- beginTransaction(), commit(), rollBack(), inTransaction()

Este deploy deve DESBLOQUEAR TODOS os 5 módulos.
"""

import ftplib
import os
import sys
from datetime import datetime

# Credenciais FTP (corretas)
FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_BASE_DIR = "/public_html"

# Arquivo crítico a ser deployado
CRITICAL_FILE = "src/Database.php"

def deploy_file(ftp, local_path, remote_path):
    """Deploy um arquivo via FTP"""
    try:
        with open(local_path, 'rb') as file:
            ftp.storbinary(f'STOR {remote_path}', file)
        file_size = os.path.getsize(local_path)
        print(f"✅ {remote_path} ({file_size} bytes)")
        return True
    except Exception as e:
        print(f"❌ ERRO ao fazer upload de {remote_path}: {e}")
        return False

def main():
    print("=" * 80)
    print("DEPLOY SPRINT 57 - CORREÇÃO CRÍTICA DATABASE.PHP")
    print("=" * 80)
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Bug: #7 - Call to undefined method App\\Database::prepare()")
    print(f"Correção: Adicionar métodos wrapper (prepare, query, exec, etc.)")
    print(f"Impacto Esperado: Desbloquear TODOS os 5 módulos")
    print("=" * 80)
    print()
    
    # Conectar ao FTP
    print("🔌 Conectando ao servidor FTP...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Conectado a {FTP_HOST}")
    except Exception as e:
        print(f"❌ ERRO ao conectar ao FTP: {e}")
        return 1
    
    # Mudar para diretório base
    try:
        ftp.cwd(FTP_BASE_DIR)
        print(f"✅ Diretório: {FTP_BASE_DIR}")
    except Exception as e:
        print(f"❌ ERRO ao mudar diretório: {e}")
        ftp.quit()
        return 1
    
    print()
    print("📦 FAZENDO BACKUP DO ARQUIVO ATUAL...")
    print("-" * 80)
    
    # Fazer backup do Database.php atual
    try:
        backup_name = f"Database.php.backup_sprint57_{datetime.now().strftime('%Y%m%d_%H%M%S')}"
        ftp.rename("src/Database.php", f"src/{backup_name}")
        print(f"✅ Backup criado: src/{backup_name}")
    except Exception as e:
        print(f"⚠️  Aviso: Não foi possível fazer backup: {e}")
        print("   (Arquivo pode não existir ou já ter sido deployado)")
    
    print()
    print("🚀 FAZENDO DEPLOY DO ARQUIVO CORRIGIDO...")
    print("-" * 80)
    
    # Deploy do arquivo crítico
    local_file = CRITICAL_FILE
    remote_file = CRITICAL_FILE
    
    if not os.path.exists(local_file):
        print(f"❌ ERRO: Arquivo local não encontrado: {local_file}")
        ftp.quit()
        return 1
    
    success = deploy_file(ftp, local_file, remote_file)
    
    if not success:
        print()
        print("❌ DEPLOY FALHOU!")
        ftp.quit()
        return 1
    
    print()
    print("=" * 80)
    print("✅ DEPLOY CONCLUÍDO COM SUCESSO!")
    print("=" * 80)
    print()
    print("📊 RESUMO:")
    print(f"   • Arquivo deployado: {CRITICAL_FILE}")
    print(f"   • Métodos adicionados: prepare(), query(), exec(), lastInsertId()")
    print(f"   •                     beginTransaction(), commit(), rollBack(), inTransaction()")
    print(f"   • Bug corrigido: #7 (Call to undefined method)")
    print()
    print("🎯 PRÓXIMOS PASSOS:")
    print("   1. Testar manualmente TODOS os 5 módulos")
    print("   2. Verificar se erros 500 foram eliminados")
    print("   3. Validar que prepare() agora funciona")
    print()
    print("⏰ Aguardar 2-3 minutos para OPcache invalidar cache...")
    
    # Fechar conexão
    ftp.quit()
    print()
    print("🔌 Conexão FTP encerrada.")
    return 0

if __name__ == "__main__":
    sys.exit(main())
