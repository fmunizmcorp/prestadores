#!/usr/bin/env python3
"""
Script para fazer upload e executar migration 012 no servidor remoto
Sprint 13 - Correção Completa do Banco de Dados
"""

import ftplib
import sys
from pathlib import Path

# Configurações FTP
FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "@GenSpark10"
FTP_DIR = "/"

# Arquivos a enviar
FILES_TO_UPLOAD = [
    "database/migrations/012_corrigir_banco_completo_sprint13.sql"
]

print("="*60)
print("SPRINT 13: UPLOAD MIGRATION 012 - CORREÇÃO DO BANCO")
print("="*60)
print()

try:
    # Conectar ao FTP
    print(f"🔌 Conectando ao FTP: {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado como: {FTP_USER}\n")
    
    # Mudar para diretório raiz
    ftp.cwd(FTP_DIR)
    
    # Criar diretório database/migrations se não existir
    try:
        ftp.cwd("database")
    except:
        print("📁 Criando diretório database...")
        ftp.mkd("database")
        ftp.cwd("database")
    
    try:
        ftp.cwd("migrations")
    except:
        print("📁 Criando diretório migrations...")
        ftp.mkd("migrations")
        ftp.cwd("migrations")
    
    # Upload dos arquivos
    uploaded = 0
    for file_path in FILES_TO_UPLOAD:
        local_file = Path(file_path)
        if not local_file.exists():
            print(f"❌ Arquivo não encontrado: {file_path}")
            continue
        
        remote_name = local_file.name
        
        try:
            with open(local_file, 'rb') as f:
                print(f"📤 Enviando: {file_path}")
                ftp.storbinary(f'STOR {remote_name}', f)
                uploaded += 1
                
                # Verificar tamanho
                size = local_file.stat().st_size
                print(f"   ✅ Enviado: {size:,} bytes")
        except Exception as e:
            print(f"   ❌ Erro: {e}")
    
    print()
    print("="*60)
    print(f"✅ Upload concluído: {uploaded}/{len(FILES_TO_UPLOAD)} arquivos")
    print("="*60)
    print()
    print("📋 PRÓXIMOS PASSOS:")
    print("1. Acessar o servidor via FTP ou SSH")
    print("2. Executar a migration via phpMyAdmin ou linha de comando:")
    print("   mysql -u u673902663_admin -p u673902663_prestadores < database/migrations/012_corrigir_banco_completo_sprint13.sql")
    print()
    print("   OU via PHP:")
    print("   php -r \"require 'execute_migration_012_remote.php';\"")
    print()
    
    ftp.quit()
    
except ftplib.all_errors as e:
    print(f"❌ Erro FTP: {e}")
    sys.exit(1)
except Exception as e:
    print(f"❌ Erro: {e}")
    sys.exit(1)
