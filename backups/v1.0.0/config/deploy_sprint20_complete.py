#!/usr/bin/env python3
"""
SPRINT 20 - DEPLOY COMPLETO AUTOMÁTICO VIA FTP
Deploy de TODOS os arquivos críticos corrigidos para produção
"""

import ftplib
import os
import hashlib
import sys
from datetime import datetime

# Credenciais FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Cores para output
class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    RESET = '\033[0m'

def calculate_md5(filepath):
    """Calcula MD5 de um arquivo"""
    md5 = hashlib.md5()
    with open(filepath, 'rb') as f:
        for chunk in iter(lambda: f.read(4096), b""):
            md5.update(chunk)
    return md5.hexdigest()

def upload_file(ftp, local_path, remote_path):
    """Upload de arquivo via FTP com verificação"""
    try:
        # Calcular MD5 antes do upload
        md5_before = calculate_md5(local_path)
        file_size = os.path.getsize(local_path)
        
        print(f"  📤 Uploading: {os.path.basename(local_path)}")
        print(f"     Local size: {file_size:,} bytes")
        print(f"     MD5: {md5_before}")
        
        # Upload
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        
        # Verificar tamanho remoto
        remote_size = ftp.size(remote_path)
        
        if remote_size == file_size:
            print(f"  {Colors.GREEN}✓ SUCCESS{Colors.RESET}: {remote_path}")
            print(f"     Remote size: {remote_size:,} bytes (match!)\n")
            return True
        else:
            print(f"  {Colors.RED}✗ ERROR{Colors.RESET}: Size mismatch!")
            print(f"     Remote size: {remote_size:,} bytes (expected {file_size:,})\n")
            return False
            
    except Exception as e:
        print(f"  {Colors.RED}✗ ERROR{Colors.RESET}: {e}\n")
        return False

def main():
    print("=" * 70)
    print("🚀 SPRINT 20 - DEPLOY AUTOMÁTICO COMPLETO VIA FTP")
    print("=" * 70)
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
    
    # Arquivos para deploy (CRÍTICOS para Sprint 20)
    # FTP raiz = /public_html (já está aí ao conectar)
    files_to_deploy = [
        {
            'local': 'public/index.php',
            'remote': '/public/index.php',
            'critical': True,
            'description': 'Front controller com fix ROOT_PATH'
        },
        {
            'local': '.htaccess',
            'remote': '/.htaccess',
            'critical': True,
            'description': 'Rewrite rules atualizadas'
        }
    ]
    
    try:
        # Conectar FTP
        print(f"{Colors.BLUE}📡 Conectando ao FTP...{Colors.RESET}")
        print(f"   Host: {FTP_HOST}")
        print(f"   User: {FTP_USER}\n")
        
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        
        print(f"{Colors.GREEN}✓ Conectado com sucesso!{Colors.RESET}\n")
        
        # Deploy de cada arquivo
        success_count = 0
        fail_count = 0
        
        for idx, file_info in enumerate(files_to_deploy, 1):
            print(f"[{idx}/{len(files_to_deploy)}] {file_info['description']}")
            print("-" * 70)
            
            local_path = file_info['local']
            remote_path = file_info['remote']
            
            # Verificar se arquivo local existe
            if not os.path.exists(local_path):
                print(f"  {Colors.RED}✗ SKIP{Colors.RESET}: Arquivo local não encontrado: {local_path}\n")
                fail_count += 1
                continue
            
            # Upload
            if upload_file(ftp, local_path, remote_path):
                success_count += 1
            else:
                fail_count += 1
                if file_info['critical']:
                    print(f"  {Colors.RED}⚠️  CRITICAL FILE FAILED!{Colors.RESET}\n")
        
        ftp.quit()
        
        # Relatório final
        print("=" * 70)
        print("📊 RELATÓRIO FINAL DO DEPLOY")
        print("=" * 70)
        print(f"✓ Sucessos: {Colors.GREEN}{success_count}{Colors.RESET}")
        print(f"✗ Falhas:   {Colors.RED}{fail_count}{Colors.RESET}")
        print(f"📁 Total:    {len(files_to_deploy)}")
        print()
        
        if fail_count == 0:
            print(f"{Colors.GREEN}🎉 DEPLOY 100% COMPLETO!{Colors.RESET}")
            print(f"{Colors.YELLOW}⚠️  ATENÇÃO: Limpe o OPcache do servidor para ativar mudanças{Colors.RESET}")
            return 0
        else:
            print(f"{Colors.RED}⚠️  DEPLOY PARCIAL - {fail_count} arquivo(s) falharam{Colors.RESET}")
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
