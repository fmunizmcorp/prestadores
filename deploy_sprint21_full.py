#!/usr/bin/env python3
"""
SPRINT 21 - DEPLOY COMPLETO AUTOMÁTICO
Deploy de TODA a estrutura src/ + config/ via FTP
Correção do deploy incompleto do Sprint 20
"""

import ftplib
import os
import hashlib
import sys
from datetime import datetime
import time

# Credenciais FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Cores
class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    RESET = '\033[0m'

def ensure_remote_dir(ftp, remote_dir):
    """Cria diretório remoto se não existir"""
    parts = remote_dir.strip('/').split('/')
    current = ''
    for part in parts:
        current += '/' + part
        try:
            ftp.cwd(current)
        except:
            try:
                ftp.mkd(current)
                print(f"  📁 Created: {current}")
            except:
                pass
    ftp.cwd('/')

def upload_file(ftp, local_path, remote_path):
    """Upload arquivo com retry"""
    max_retries = 3
    for attempt in range(max_retries):
        try:
            with open(local_path, 'rb') as f:
                ftp.storbinary(f'STOR {remote_path}', f)
            return True
        except Exception as e:
            if attempt == max_retries - 1:
                print(f"  {Colors.RED}✗ FAILED after {max_retries} attempts: {e}{Colors.RESET}")
                return False
            time.sleep(1)
    return False

def upload_directory(ftp, local_dir, remote_dir, exclude_dirs=[]):
    """Upload diretório recursivamente"""
    uploaded = 0
    failed = 0
    skipped = 0
    
    for root, dirs, files in os.walk(local_dir):
        # Remove excluded dirs
        dirs[:] = [d for d in dirs if d not in exclude_dirs]
        
        # Calculate relative path
        rel_path = os.path.relpath(root, local_dir)
        if rel_path == '.':
            remote_path = remote_dir
        else:
            remote_path = os.path.join(remote_dir, rel_path).replace('\\', '/')
        
        # Ensure remote directory exists
        ensure_remote_dir(ftp, remote_path)
        
        # Upload files
        for filename in files:
            # Skip hidden files and backups
            if filename.startswith('.') or filename.endswith(('~', '.bak', '.swp')):
                skipped += 1
                continue
            
            local_file = os.path.join(root, filename)
            remote_file = os.path.join(remote_path, filename).replace('\\', '/')
            
            file_size = os.path.getsize(local_file)
            rel_file = os.path.relpath(local_file, local_dir)
            
            print(f"  📤 {rel_file} ({file_size:,} bytes)")
            
            if upload_file(ftp, local_file, remote_file):
                uploaded += 1
                print(f"  {Colors.GREEN}✓{Colors.RESET}")
            else:
                failed += 1
    
    return uploaded, failed, skipped

def main():
    print("=" * 80)
    print("🚀 SPRINT 21 - DEPLOY COMPLETO AUTOMÁTICO")
    print("=" * 80)
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
    
    # Diretórios para deploy
    directories_to_deploy = [
        {
            'local': 'src',
            'remote': '/src',
            'description': 'Source code (Controllers, Models, Views)',
            'exclude': ['.git', '__pycache__', 'node_modules']
        },
        {
            'local': 'config',
            'remote': '/config',
            'description': 'Configuration files',
            'exclude': []
        },
        {
            'local': 'database',
            'remote': '/database',
            'description': 'Database migrations and seeds',
            'exclude': []
        }
    ]
    
    try:
        # Conectar FTP
        print(f"{Colors.BLUE}📡 Conectando ao FTP...{Colors.RESET}")
        print(f"   Host: {FTP_HOST}")
        print(f"   User: {FTP_USER}\n")
        
        ftp = ftplib.FTP(FTP_HOST, timeout=60)
        ftp.login(FTP_USER, FTP_PASS)
        
        print(f"{Colors.GREEN}✓ Conectado com sucesso!{Colors.RESET}\n")
        
        # Deploy de cada diretório
        total_uploaded = 0
        total_failed = 0
        total_skipped = 0
        
        for idx, dir_info in enumerate(directories_to_deploy, 1):
            print(f"\n{'=' * 80}")
            print(f"[{idx}/{len(directories_to_deploy)}] {dir_info['description']}")
            print(f"Local:  {dir_info['local']}")
            print(f"Remote: {dir_info['remote']}")
            print('=' * 80)
            
            if not os.path.exists(dir_info['local']):
                print(f"{Colors.YELLOW}⚠️  SKIP: Directory not found{Colors.RESET}\n")
                continue
            
            uploaded, failed, skipped = upload_directory(
                ftp, 
                dir_info['local'], 
                dir_info['remote'],
                dir_info['exclude']
            )
            
            total_uploaded += uploaded
            total_failed += failed
            total_skipped += skipped
            
            print(f"\n{Colors.CYAN}Summary:{Colors.RESET}")
            print(f"  ✓ Uploaded: {uploaded}")
            print(f"  ✗ Failed: {failed}")
            print(f"  ⊘ Skipped: {skipped}")
        
        ftp.quit()
        
        # Relatório final
        print("\n" + "=" * 80)
        print("📊 RELATÓRIO FINAL DO DEPLOY")
        print("=" * 80)
        print(f"✓ Arquivos enviados:  {Colors.GREEN}{total_uploaded}{Colors.RESET}")
        print(f"✗ Falhas:             {Colors.RED}{total_failed}{Colors.RESET}")
        print(f"⊘ Ignorados:          {Colors.YELLOW}{total_skipped}{Colors.RESET}")
        print(f"📁 Total:              {total_uploaded + total_failed + total_skipped}")
        print()
        
        if total_failed == 0:
            print(f"{Colors.GREEN}🎉 DEPLOY 100% COMPLETO!{Colors.RESET}")
            print(f"{Colors.YELLOW}⚠️  Próximo: Teste V12 para validação{Colors.RESET}")
            return 0
        else:
            print(f"{Colors.RED}⚠️  DEPLOY PARCIAL - {total_failed} arquivo(s) falharam{Colors.RESET}")
            return 1
            
    except ftplib.error_perm as e:
        print(f"{Colors.RED}✗ ERRO FTP: {e}{Colors.RESET}")
        return 1
    except Exception as e:
        print(f"{Colors.RED}✗ ERRO: {e}{Colors.RESET}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
