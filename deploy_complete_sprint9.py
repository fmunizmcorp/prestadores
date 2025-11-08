#!/usr/bin/env python3
"""
Deploy Sprint 9 - Correção Completa Sistema
Data: 2025-11-08
"""

import ftplib
import os
import sys
from datetime import datetime

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Arquivos críticos para upload
FILES_TO_UPLOAD = [
    ('.htaccess', '.htaccess'),
    ('public/index.php', 'public/index.php'),
    ('src/Database.php', 'src/Database.php'),
    ('src/DatabaseMigration.php', 'src/DatabaseMigration.php'),
    ('config/version.php', 'config/version.php'),
    ('config/database.php', 'config/database.php'),
    ('config/app.php', 'config/app.php'),
]

def ensure_dir(ftp, path):
    """Garante que diretório existe"""
    if not path:
        return
    dirs = path.split('/')
    for i, dir_name in enumerate(dirs):
        if not dir_name:
            continue
        path_check = '/'.join(dirs[:i+1])
        try:
            ftp.cwd('/' + path_check)
        except:
            try:
                ftp.mkd('/' + path_check)
            except:
                pass

def upload_file(ftp, local_path, remote_path):
    """Upload arquivo via FTP"""
    try:
        remote_dir = os.path.dirname(remote_path)
        if remote_dir:
            ensure_dir(ftp, remote_dir)
            ftp.cwd('/' + remote_dir)
        else:
            ftp.cwd('/')
        
        with open(local_path, 'rb') as f:
            remote_file = os.path.basename(remote_path)
            ftp.storbinary(f'STOR {remote_file}', f)
        
        ftp.cwd('/')
        
        size = os.path.getsize(local_path)
        print(f"   ✓ {local_path} → /{remote_path} ({size:,} bytes)")
        return True
        
    except Exception as e:
        print(f"   ✗ Erro: {local_path}: {e}")
        return False

def main():
    print("=" * 70)
    print("SPRINT 9 - DEPLOY COMPLETO - CORREÇÃO SISTEMA")
    print("Data:", datetime.now().strftime('%Y-%m-%d %H:%M:%S'))
    print("=" * 70)
    print()
    
    try:
        print("1. Conectando ao FTP...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"   ✓ Conectado a {FTP_HOST}")
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
        print("=" * 70)
        
        if success_count == total_count:
            print("✓ DEPLOY 100% SUCESSO!")
        else:
            print(f"⚠ DEPLOY PARCIAL: {success_count}/{total_count}")
        
        print("=" * 70)
        print()
        print("Próximo: Testar em https://prestadores.clinfec.com.br")
        print()
        
        return 0 if success_count == total_count else 1
        
    except Exception as e:
        print()
        print(f"✗ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
