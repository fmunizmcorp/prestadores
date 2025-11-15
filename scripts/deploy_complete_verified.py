#!/usr/bin/env python3
"""
DEPLOYMENT COMPLETO E VERIFICADO
Garante que TODOS os arquivos são copiados corretamente
"""

import ftplib
import os
from pathlib import Path

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/public_html/prestadores'

LOCAL_DIR = Path('/home/user/webapp')

def connect():
    print("🔌 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    print("✅ Conectado!\n")
    return ftp

def upload_file(ftp, local_path, remote_path):
    """Upload e verifica"""
    try:
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        
        # Verificar tamanho
        size = ftp.size(remote_path)
        local_size = os.path.getsize(local_path)
        
        if size == local_size:
            print(f"   ✅ OK ({size} bytes)")
            return True
        else:
            print(f"   ⚠️  Tamanhos diferentes: local={local_size}, remoto={size}")
            return False
    except Exception as e:
        print(f"   ❌ ERRO: {e}")
        return False

def main():
    print("=" * 80)
    print("🚀 DEPLOYMENT COMPLETO E VERIFICADO")
    print("=" * 80)
    print()
    
    ftp = connect()
    ftp.cwd(FTP_DIR)
    
    stats = {'ok': 0, 'fail': 0}
    
    # 1. ARQUIVOS CRÍTICOS NA RAIZ
    print("📤 ARQUIVOS CRÍTICOS NA RAIZ:")
    print("-" * 80)
    
    critical_files = [
        'index.php',
        '.htaccess',
        'test.php'
    ]
    
    for fname in critical_files:
        local_file = LOCAL_DIR / fname
        if not local_file.exists():
            print(f"❌ {fname} NÃO EXISTE LOCALMENTE!")
            stats['fail'] += 1
            continue
        
        print(f"📤 {fname} ({local_file.stat().st_size} bytes)")
        if upload_file(ftp, str(local_file), fname):
            stats['ok'] += 1
        else:
            stats['fail'] += 1
    
    # 2. CONFIG
    print("\n📤 CONFIG:")
    print("-" * 80)
    
    try:
        ftp.cwd(FTP_DIR + '/config')
    except:
        ftp.mkd('config')
        ftp.cwd(FTP_DIR + '/config')
    
    for f in (LOCAL_DIR / 'config').glob('*.php'):
        print(f"📤 config/{f.name} ({f.stat().st_size} bytes)")
        if upload_file(ftp, str(f), f.name):
            stats['ok'] += 1
        else:
            stats['fail'] += 1
    
    # 3. ASSETS
    print("\n📤 ASSETS:")
    print("-" * 80)
    
    ftp.cwd(FTP_DIR)
    
    for subdir in ['css', 'js']:
        try:
            ftp.cwd(FTP_DIR + f'/assets/{subdir}')
        except:
            try:
                ftp.mkd('assets')
            except:
                pass
            try:
                ftp.mkd(f'assets/{subdir}')
            except:
                pass
            ftp.cwd(FTP_DIR + f'/assets/{subdir}')
        
        asset_dir = LOCAL_DIR / 'assets' / subdir
        if asset_dir.exists():
            for asset_file in asset_dir.glob('*'):
                if asset_file.is_file():
                    print(f"📤 assets/{subdir}/{asset_file.name} ({asset_file.stat().st_size} bytes)")
                    if upload_file(ftp, str(asset_file), asset_file.name):
                        stats['ok'] += 1
                    else:
                        stats['fail'] += 1
    
    ftp.quit()
    
    print("\n" + "=" * 80)
    print("📊 RESULTADO:")
    print("=" * 80)
    print(f"✅ Sucesso: {stats['ok']}")
    print(f"❌ Falhas: {stats['fail']}")
    
    if stats['fail'] == 0:
        print("\n🎉 TODOS OS ARQUIVOS COPIADOS COM SUCESSO!")
    else:
        print(f"\n⚠️  {stats['fail']} ARQUIVO(S) COM PROBLEMA!")
    
    return 0 if stats['fail'] == 0 else 1

if __name__ == '__main__':
    import sys
    sys.exit(main())
