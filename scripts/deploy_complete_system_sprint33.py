#!/usr/bin/env python3
"""
Sprint 33 - DEPLOYMENT COMPLETO DO SISTEMA
Deploy de TODOS os arquivos para o subdomínio prestadores.clinfec.com.br
Metodologia: SCRUM + PDCA
"""

import ftplib
import os
import sys
from pathlib import Path
from datetime import datetime

# Configurações FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_REMOTE_DIR = '/public_html/prestadores'

# Diretório local
LOCAL_DIR = Path('/home/user/webapp')

# Contadores
stats = {
    'total': 0,
    'success': 0,
    'failed': 0,
    'skipped': 0
}

def connect_ftp():
    """Conecta ao servidor FTP"""
    print(f"🔌 Conectando ao servidor FTP: {FTP_HOST}")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado com sucesso!")
    return ftp

def ensure_remote_dir(ftp, remote_path):
    """Garante que o diretório remoto existe"""
    dirs = remote_path.strip('/').split('/')
    current = ''
    
    for dir_name in dirs:
        if not dir_name:
            continue
            
        current += '/' + dir_name
        
        try:
            ftp.cwd(current)
        except ftplib.error_perm:
            try:
                ftp.mkd(current)
                print(f"📁 Criado diretório: {current}")
                ftp.cwd(current)
            except ftplib.error_perm as e:
                # Diretório pode já existir
                pass

def upload_file(ftp, local_file, remote_file):
    """Faz upload de um arquivo"""
    try:
        with open(local_file, 'rb') as f:
            ftp.storbinary(f'STOR {remote_file}', f)
        return True
    except Exception as e:
        print(f"   ❌ Erro: {e}")
        return False

def deploy_directory(ftp, local_path, remote_path):
    """Deploy recursivo de um diretório"""
    
    for item in local_path.iterdir():
        # Ignorar arquivos/pastas
        if item.name in ['.git', '.gitignore', '__pycache__', 'node_modules', 
                        '.DS_Store', 'scripts', 'docs', 'uploaded_reports',
                        'uploaded_files']:
            stats['skipped'] += 1
            continue
        
        # Ignorar arquivos de documentação
        if item.suffix in ['.md', '.txt', '.pdf'] and item.parent == LOCAL_DIR:
            stats['skipped'] += 1
            continue
        
        if item.is_file():
            stats['total'] += 1
            
            # Caminho relativo
            rel_path = item.relative_to(LOCAL_DIR)
            remote_file_path = f"{remote_path}/{rel_path}"
            
            # Garantir diretório existe
            remote_dir = os.path.dirname(remote_file_path)
            ensure_remote_dir(ftp, remote_dir)
            
            # Nome do arquivo remoto
            remote_filename = os.path.basename(remote_file_path)
            
            print(f"📤 [{stats['total']}] {rel_path} ({item.stat().st_size} bytes)")
            
            # Upload
            if upload_file(ftp, str(item), remote_filename):
                stats['success'] += 1
                print(f"   ✅ Sucesso!")
            else:
                stats['failed'] += 1
        
        elif item.is_dir():
            # Recursivo
            deploy_directory(ftp, item, remote_path)

def main():
    print("=" * 80)
    print("🚀 SPRINT 33 - DEPLOYMENT COMPLETO DO SISTEMA")
    print("=" * 80)
    print(f"📅 Data: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"📂 Local: {LOCAL_DIR}")
    print(f"🌐 Remoto: {FTP_HOST}{FTP_REMOTE_DIR}")
    print("=" * 80)
    print()
    
    try:
        # Conectar
        ftp = connect_ftp()
        
        # Mudar para diretório prestadores
        print(f"\n📁 Mudando para: {FTP_REMOTE_DIR}")
        ftp.cwd(FTP_REMOTE_DIR)
        
        # Deploy das pastas principais
        print("\n" + "=" * 80)
        print("📤 INICIANDO DEPLOYMENT")
        print("=" * 80 + "\n")
        
        folders_to_deploy = ['config', 'src', 'public']
        
        for folder in folders_to_deploy:
            folder_path = LOCAL_DIR / folder
            if folder_path.exists():
                print(f"\n📁 Deployando pasta: {folder}/")
                print("-" * 80)
                deploy_directory(ftp, folder_path, FTP_REMOTE_DIR)
        
        # Fechar conexão
        ftp.quit()
        
        # Estatísticas finais
        print("\n" + "=" * 80)
        print("📊 ESTATÍSTICAS FINAIS")
        print("=" * 80)
        print(f"✅ Arquivos enviados: {stats['success']}")
        print(f"❌ Falhas: {stats['failed']}")
        print(f"⏭️  Ignorados: {stats['skipped']}")
        print(f"📦 Total processado: {stats['total']}")
        
        if stats['failed'] == 0:
            print("\n🎉 DEPLOYMENT COMPLETO COM SUCESSO!")
            print("=" * 80)
            return 0
        else:
            print(f"\n⚠️  DEPLOYMENT COMPLETO COM {stats['failed']} FALHAS")
            print("=" * 80)
            return 1
        
    except Exception as e:
        print(f"\n❌ ERRO FATAL: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
