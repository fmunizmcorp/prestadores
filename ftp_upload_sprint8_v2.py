#!/usr/bin/env python3
"""
Deploy Sprint 8 - Emergency Fixes via FTP (v2)
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
FTP_REMOTE_DIR = '/home/u673902663/domains/clinfec.com.br/public_html/prestadores'

# Arquivos a fazer upload (local, remoto)
FILES_TO_UPLOAD = [
    ('public/index.php', 'index.php'),  # Vai para raiz
    ('src/Database.php', 'src/Database.php'),
    ('src/DatabaseMigration.php', 'src/DatabaseMigration.php'),
    ('config/version.php', 'config/version.php'),
]

def ensure_dir(ftp, path):
    """Garante que um diretório existe no servidor"""
    dirs = path.split('/')
    current = FTP_REMOTE_DIR
    
    for dir_name in dirs:
        if not dir_name:
            continue
        current += '/' + dir_name
        try:
            ftp.cwd(current)
        except ftplib.error_perm:
            try:
                ftp.mkd(current)
                print(f"   Criado diretório: {current}")
            except ftplib.error_perm:
                pass  # Já existe
            ftp.cwd(current)

def upload_file(ftp, local_path, remote_path):
    """Faz upload de um arquivo via FTP"""
    try:
        # Garante que o diretório remoto existe
        remote_dir = os.path.dirname(remote_path)
        if remote_dir:
            ensure_dir(ftp, remote_dir)
            ftp.cwd(FTP_REMOTE_DIR + '/' + remote_dir)
        else:
            ftp.cwd(FTP_REMOTE_DIR)
        
        # Upload do arquivo
        with open(local_path, 'rb') as f:
            remote_file = os.path.basename(remote_path)
            ftp.storbinary(f'STOR {remote_file}', f)
        
        size = os.path.getsize(local_path)
        print(f"   ✓ {local_path} → {remote_path} ({size:,} bytes)")
        return True
        
    except Exception as e:
        print(f"   ✗ Erro ao enviar {local_path}: {e}")
        return False

def main():
    print("=" * 60)
    print("SPRINT 8 - EMERGENCY FIXES DEPLOY (v2)")
    print("Sistema: Clinfec Prestadores")
    print("Data:", datetime.now().strftime('%Y-%m-%d %H:%M:%S'))
    print("=" * 60)
    print()
    
    try:
        print("1. Conectando ao servidor FTP...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"   ✓ Conectado a {FTP_HOST}")
        print()
        
        print(f"2. Verificando diretório base: {FTP_REMOTE_DIR}")
        try:
            ftp.cwd(FTP_REMOTE_DIR)
            print(f"   ✓ Diretório encontrado")
        except ftplib.error_perm:
            print(f"   ✗ Diretório não existe: {FTP_REMOTE_DIR}")
            return 1
        print()
        
        print("3. Fazendo upload dos arquivos corrigidos...")
        success_count = 0
        total_count = len(FILES_TO_UPLOAD)
        
        for local_path, remote_path in FILES_TO_UPLOAD:
            if upload_file(ftp, local_path, remote_path):
                success_count += 1
        
        print()
        print(f"4. Upload concluído: {success_count}/{total_count} arquivos")
        
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
        print()
        return 1
    except Exception as e:
        print()
        print(f"✗ ERRO: {e}")
        print()
        return 1

if __name__ == '__main__':
    sys.exit(main())
