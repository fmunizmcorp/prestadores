#!/usr/bin/env python3
"""
DEPLOY COMPLETO - TODO O SISTEMA
Faz upload de TODOS os diretórios src/, config/, database/, public/
"""

import ftplib
import os
import sys
from pathlib import Path

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Diretórios para fazer upload completo
DIRS_TO_UPLOAD = ['src', 'config', 'database', 'public']

def upload_directory(ftp, local_dir, remote_dir=''):
    """Upload recursivo de diretório"""
    uploaded = 0
    failed = 0
    
    for item in Path(local_dir).rglob('*'):
        if item.is_file():
            # Calcula caminho relativo
            rel_path = str(item.relative_to(local_dir))
            remote_path = f"{remote_dir}/{rel_path}" if remote_dir else rel_path
            remote_path = remote_path.replace('\\', '/')
            
            # Cria diretórios necessários
            remote_file_dir = os.path.dirname(remote_path)
            if remote_file_dir:
                ensure_dir(ftp, remote_file_dir)
            
            # Upload do arquivo
            try:
                with open(item, 'rb') as f:
                    ftp.storbinary(f'STOR /{remote_path}', f)
                uploaded += 1
                if uploaded % 10 == 0:
                    print(f"   {uploaded} arquivos enviados...")
            except Exception as e:
                print(f"   ✗ Erro em {remote_path}: {e}")
                failed += 1
    
    return uploaded, failed

def ensure_dir(ftp, path):
    """Garante que diretório existe"""
    dirs = path.split('/')
    current = ''
    for d in dirs:
        if not d:
            continue
        current += '/' + d
        try:
            ftp.cwd(current)
        except:
            try:
                ftp.mkd(current)
            except:
                pass
    ftp.cwd('/')

def main():
    print("=" * 70)
    print("DEPLOY COMPLETO - TODO O SISTEMA")
    print("=" * 70)
    print()
    
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd('/')
    print("✓ Conectado ao FTP")
    print()
    
    total_up = 0
    total_fail = 0
    
    for dir_name in DIRS_TO_UPLOAD:
        print(f"Uploading {dir_name}/...")
        up, fail = upload_directory(ftp, dir_name, dir_name)
        total_up += up
        total_fail += fail
        print(f"   ✓ {dir_name}: {up} arquivos OK, {fail} erros")
        print()
    
    # Upload arquivos raiz importantes
    print("Uploading arquivos raiz...")
    root_files = ['.htaccess']
    for f in root_files:
        if os.path.exists(f):
            try:
                with open(f, 'rb') as local_f:
                    ftp.storbinary(f'STOR /{f}', local_f)
                total_up += 1
                print(f"   ✓ {f}")
            except Exception as e:
                print(f"   ✗ {f}: {e}")
                total_fail += 1
    
    ftp.quit()
    
    print()
    print("=" * 70)
    print(f"✓ DEPLOY CONCLUÍDO!")
    print(f"  Total enviados: {total_up} arquivos")
    print(f"  Total erros: {total_fail}")
    print("=" * 70)
    
    return 0 if total_fail == 0 else 1

if __name__ == '__main__':
    sys.exit(main())
