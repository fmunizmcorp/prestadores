#!/usr/bin/env python3
"""
Deploy Sprint 8 - Emergency Fixes via FTP (v3 - Root Directory)
Sistema Clinfec Prestadores
Data: 2025-11-08
"""

import ftplib
import os
import sys
from datetime import datetime

# Configurações FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Arquivos a fazer upload (local, remoto relative to FTP root)
FILES_TO_UPLOAD = [
    ('public/index.php', 'public/index.php'),
    ('src/Database.php', 'src/Database.php'),
    ('src/DatabaseMigration.php', 'src/DatabaseMigration.php'),
    ('config/version.php', 'config/version.php'),
]

def ensure_dir(ftp, path):
    """Garante que um diretório existe no servidor"""
    if not path:
        return
    
    dirs = path.split('/')
    for i, dir_name in enumerate(dirs):
        if not dir_name:
            continue
        
        path_to_check = '/'.join(dirs[:i+1])
        try:
            ftp.cwd('/' + path_to_check)
        except ftplib.error_perm:
            try:
                ftp.mkd('/' + path_to_check)
                print(f"   Criado diretório: {path_to_check}")
            except ftplib.error_perm as e:
                # Diretório já existe
                pass

def upload_file(ftp, local_path, remote_path):
    """Faz upload de um arquivo via FTP"""
    try:
        # Garante que o diretório remoto existe
        remote_dir = os.path.dirname(remote_path)
        if remote_dir:
            ensure_dir(ftp, remote_dir)
            ftp.cwd('/' + remote_dir)
        else:
            ftp.cwd('/')
        
        # Upload do arquivo
        with open(local_path, 'rb') as f:
            remote_file = os.path.basename(remote_path)
            ftp.storbinary(f'STOR {remote_file}', f)
        
        # Volta para raiz
        ftp.cwd('/')
        
        size = os.path.getsize(local_path)
        print(f"   ✓ {local_path} → /{remote_path} ({size:,} bytes)")
        return True
        
    except Exception as e:
        print(f"   ✗ Erro ao enviar {local_path}: {e}")
        import traceback
        traceback.print_exc()
        return False

def main():
    print("=" * 60)
    print("SPRINT 8 - EMERGENCY FIXES DEPLOY (v3)")
    print("Sistema: Clinfec Prestadores")
    print("Data:", datetime.now().strftime('%Y-%m-%d %H:%M:%S'))
    print("=" * 60)
    print()
    
    try:
        print("1. Conectando ao servidor FTP...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"   ✓ Conectado a {FTP_HOST}")
        print(f"   Diretório atual: {ftp.pwd()}")
        print()
        
        print("2. Fazendo upload dos arquivos corrigidos...")
        success_count = 0
        total_count = len(FILES_TO_UPLOAD)
        
        for local_path, remote_path in FILES_TO_UPLOAD:
            if upload_file(ftp, local_path, remote_path):
                success_count += 1
        
        print()
        print(f"3. Upload concluído: {success_count}/{total_count} arquivos")
        
        ftp.quit()
        print()
        print("=" * 60)
        
        if success_count == total_count:
            print("✓ DEPLOY CONCLUÍDO COM SUCESSO!")
        else:
            print(f"⚠ DEPLOY PARCIAL: {success_count}/{total_count}")
        
        print("=" * 60)
        print()
        print("Próximo passo: Testar em https://prestadores.clinfec.com.br")
        print()
        
        return 0 if success_count == total_count else 1
        
    except ftplib.all_errors as e:
        print()
        print(f"✗ ERRO FTP: {e}")
        import traceback
        traceback.print_exc()
        print()
        return 1
    except Exception as e:
        print()
        print(f"✗ ERRO: {e}")
        import traceback
        traceback.print_exc()
        print()
        return 1

if __name__ == '__main__':
    sys.exit(main())
