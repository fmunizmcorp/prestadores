#!/usr/bin/env python3
"""
Deploy Sprint 8 - Emergency Fixes via FTP
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

# Arquivos a fazer upload
FILES_TO_UPLOAD = [
    ('public/index.php', 'public/index.php'),
    ('src/Database.php', 'src/Database.php'),
    ('src/DatabaseMigration.php', 'src/DatabaseMigration.php'),
    ('config/version.php', 'config/version.php'),
]

def upload_file(ftp, local_path, remote_path):
    """Faz upload de um arquivo via FTP"""
    try:
        with open(local_path, 'rb') as f:
            remote_file = os.path.basename(remote_path)
            remote_dir = os.path.dirname(remote_path)
            
            # Navega para o diretório correto
            if remote_dir:
                try:
                    ftp.cwd(FTP_REMOTE_DIR + '/' + remote_dir)
                except ftplib.error_perm:
                    print(f"   Criando diretório: {remote_dir}")
                    ftp.mkd(FTP_REMOTE_DIR + '/' + remote_dir)
                    ftp.cwd(FTP_REMOTE_DIR + '/' + remote_dir)
            else:
                ftp.cwd(FTP_REMOTE_DIR)
            
            # Upload do arquivo
            ftp.storbinary(f'STOR {remote_file}', f)
            
            # Volta para raiz
            ftp.cwd(FTP_REMOTE_DIR)
            
            size = os.path.getsize(local_path)
            print(f"   ✓ {local_path} ({size:,} bytes)")
            return True
            
    except Exception as e:
        print(f"   ✗ Erro ao enviar {local_path}: {e}")
        return False

def main():
    print("=" * 60)
    print("SPRINT 8 - EMERGENCY FIXES DEPLOY")
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
        print("✓ DEPLOY CONCLUÍDO COM SUCESSO!")
        print("=" * 60)
        print()
        print("Próximo passo: Testar em https://prestadores.clinfec.com.br")
        print()
        
        return 0
        
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
