#!/usr/bin/env python3
"""
DEPLOY COMPLETO RÁPIDO - SPRINT 33
Deploy de todos os arquivos necessários de forma otimizada
"""

import ftplib
import os
from pathlib import Path
import sys

# Credenciais
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

PROJECT_ROOT = Path(__file__).parent.parent

def upload_directory(ftp, local_dir, remote_base='', exclude=None):
    """Upload recursivo de diretório"""
    if exclude is None:
        exclude = ['.git', '__pycache__', '.pyc', '.DS_Store', 'node_modules', '.backup', '.OLD']
    
    uploaded = 0
    failed = 0
    
    for root, dirs, files in os.walk(local_dir):
        # Filtrar diretórios excluídos
        dirs[:] = [d for d in dirs if not any(excl in d for excl in exclude)]
        
        # Caminho relativo
        rel_path = os.path.relpath(root, local_dir)
        if rel_path == '.':
            remote_path = remote_base
        else:
            remote_path = f"{remote_base}/{rel_path}".replace('\\', '/') if remote_base else rel_path.replace('\\', '/')
        
        # Criar diretório remoto
        if remote_path:
            try:
                ftp.mkd(remote_path)
            except:
                pass  # Já existe
        
        # Upload de arquivos
        for filename in files:
            # Filtrar arquivos excluídos
            if any(excl in filename for excl in exclude):
                continue
            
            local_file = os.path.join(root, filename)
            remote_file = f"{remote_path}/{filename}".replace('\\', '/') if remote_path else filename
            
            try:
                with open(local_file, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_file}', f)
                uploaded += 1
                print(f"  ✅ {remote_file}")
            except Exception as e:
                failed += 1
                print(f"  ❌ {remote_file}: {e}")
    
    return uploaded, failed

def main():
    print("🚀 DEPLOY COMPLETO RÁPIDO - SPRINT 33")
    print("=" * 70)
    
    total_uploaded = 0
    total_failed = 0
    
    try:
        # Conectar
        print(f"\n1️⃣ Conectando a {FTP_HOST}...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.set_pasv(True)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Conectado!\n")
        
        # Deploy config/
        print("2️⃣ Deploying config/...")
        config_dir = PROJECT_ROOT / 'config'
        if config_dir.exists():
            up, fail = upload_directory(ftp, str(config_dir), 'config')
            total_uploaded += up
            total_failed += fail
            print(f"   Config: {up} arquivos, {fail} falhas\n")
        
        # Deploy src/
        print("3️⃣ Deploying src/...")
        src_dir = PROJECT_ROOT / 'src'
        if src_dir.exists():
            up, fail = upload_directory(ftp, str(src_dir), 'src')
            total_uploaded += up
            total_failed += fail
            print(f"   Src: {up} arquivos, {fail} falhas\n")
        
        # Deploy public/ (arquivos adicionais, index.php já foi)
        print("4️⃣ Deploying public/...")
        public_dir = PROJECT_ROOT / 'public'
        if public_dir.exists():
            # Apenas .htaccess e assets
            for item in public_dir.iterdir():
                if item.is_file() and item.name != 'index.php':  # index.php já foi
                    try:
                        with open(item, 'rb') as f:
                            ftp.storbinary(f'STOR {item.name}', f)
                        total_uploaded += 1
                        print(f"  ✅ {item.name}")
                    except Exception as e:
                        total_failed += 1
                        print(f"  ❌ {item.name}: {e}")
        
        print("\n" + "=" * 70)
        print(f"✅ DEPLOY CONCLUÍDO!")
        print(f"📊 Arquivos enviados: {total_uploaded}")
        print(f"❌ Falhas: {total_failed}")
        print(f"🌐 URL: https://prestadores.clinfec.com.br")
        print(f"👤 Login: admin@clinfec.com.br / password")
        print("=" * 70)
        
        ftp.quit()
        return total_failed == 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    sys.exit(0 if main() else 1)
