#!/usr/bin/env python3
"""
DEPLOYMENT SIMPLIFICADO - HOSTINGER COMPARTILHADA
Deploy para /public_html/prestadores/ (RAIZ do subdomínio)
"""

import ftplib
import os
from pathlib import Path
from datetime import datetime

# Configurações FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/public_html/prestadores'

LOCAL_DIR = Path('/home/user/webapp')

stats = {'uploaded': 0, 'failed': 0, 'skipped': 0}

def connect_ftp():
    print(f"🔌 Conectando: {FTP_HOST}")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    print("✅ Conectado!")
    return ftp

def upload_file(ftp, local_file, remote_file):
    try:
        with open(local_file, 'rb') as f:
            ftp.storbinary(f'STOR {remote_file}', f)
        return True
    except Exception as e:
        print(f"   ❌ Erro: {e}")
        return False

def ensure_dir(ftp, path):
    dirs = path.strip('/').split('/')
    current = FTP_DIR
    
    for d in dirs:
        if not d:
            continue
        current += '/' + d
        try:
            ftp.cwd(current)
        except:
            try:
                ftp.mkd(current)
                print(f"📁 Criado: {current}")
            except:
                pass

def main():
    print("=" * 80)
    print("🚀 DEPLOYMENT SIMPLIFICADO - HOSTINGER COMPARTILHADA")
    print("=" * 80)
    print(f"📅 {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"📂 Destino: {FTP_HOST}{FTP_DIR}")
    print("=" * 80)
    print()
    
    ftp = connect_ftp()
    ftp.cwd(FTP_DIR)
    
    # 1. Arquivos na RAIZ
    print("\n📤 RAIZ:")
    print("-" * 80)
    root_files = ['index.php', '.htaccess', 'test.php']
    for f in root_files:
        local_path = LOCAL_DIR / f
        if local_path.exists():
            print(f"📤 {f} ({local_path.stat().st_size} bytes)")
            if upload_file(ftp, str(local_path), f):
                stats['uploaded'] += 1
                print("   ✅ OK")
            else:
                stats['failed'] += 1
        else:
            print(f"⏭️  {f} não existe")
            stats['skipped'] += 1
    
    # 2. config/
    print("\n📤 config/:")
    print("-" * 80)
    ensure_dir(ftp, 'config')
    ftp.cwd(FTP_DIR + '/config')
    for f in (LOCAL_DIR / 'config').glob('*.php'):
        print(f"📤 config/{f.name} ({f.stat().st_size} bytes)")
        if upload_file(ftp, str(f), f.name):
            stats['uploaded'] += 1
            print("   ✅ OK")
        else:
            stats['failed'] += 1
    
    # 3. src/ (recursivo)
    print("\n📤 src/:")
    print("-" * 80)
    ensure_dir(ftp, 'src')
    
    for root, dirs, files in os.walk(LOCAL_DIR / 'src'):
        root_path = Path(root)
        rel_path = root_path.relative_to(LOCAL_DIR / 'src')
        
        # Criar subdiretórios
        if str(rel_path) != '.':
            remote_dir = f"src/{rel_path}".replace('\\', '/')
            ensure_dir(ftp, remote_dir)
        
        # Upload arquivos
        for fname in files:
            if fname.endswith('.php'):
                local_file = root_path / fname
                remote_path = f"src/{rel_path}/{fname}".replace('\\', '/')
                remote_path = remote_path.replace('src/./', 'src/')
                
                # Navegar para diretório correto
                remote_dir = '/'.join(remote_path.split('/')[:-1])
                try:
                    ftp.cwd(FTP_DIR + '/' + remote_dir)
                except:
                    ensure_dir(ftp, remote_dir)
                    ftp.cwd(FTP_DIR + '/' + remote_dir)
                
                print(f"📤 {remote_path} ({local_file.stat().st_size} bytes)")
                if upload_file(ftp, str(local_file), fname):
                    stats['uploaded'] += 1
                    print("   ✅ OK")
                else:
                    stats['failed'] += 1
    
    # 4. assets/
    print("\n📤 assets/:")
    print("-" * 80)
    ensure_dir(ftp, 'assets')
    
    for subdir in ['css', 'js', 'images']:
        ensure_dir(ftp, f'assets/{subdir}')
        ftp.cwd(FTP_DIR + f'/assets/{subdir}')
        
        asset_dir = LOCAL_DIR / 'assets' / subdir
        if asset_dir.exists():
            for f in asset_dir.iterdir():
                if f.is_file():
                    print(f"📤 assets/{subdir}/{f.name} ({f.stat().st_size} bytes)")
                    if upload_file(ftp, str(f), f.name):
                        stats['uploaded'] += 1
                        print("   ✅ OK")
                    else:
                        stats['failed'] += 1
    
    ftp.quit()
    
    print("\n" + "=" * 80)
    print("📊 ESTATÍSTICAS:")
    print("=" * 80)
    print(f"✅ Uploaded: {stats['uploaded']}")
    print(f"❌ Failed: {stats['failed']}")
    print(f"⏭️  Skipped: {stats['skipped']}")
    print()
    
    if stats['failed'] == 0:
        print("🎉 DEPLOYMENT COMPLETO COM SUCESSO!")
        return 0
    else:
        print(f"⚠️  DEPLOYMENT COM {stats['failed']} FALHAS")
        return 1

if __name__ == '__main__':
    import sys
    sys.exit(main())
