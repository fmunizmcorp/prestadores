#!/usr/bin/env python3
"""
DEPLOY COMPLETO PARA /prestadores - SPRINT 33
"""

import ftplib
import os
from pathlib import Path

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/public_html/prestadores'

PROJECT_ROOT = Path(__file__).parent.parent

def upload_dir_recursive(ftp, local_dir, remote_dir='', exclude=None, base_dir=''):
    """Upload recursivo"""
    if exclude is None:
        exclude = ['.git', '__pycache__', '.pyc', '.DS_Store', 'node_modules', '.backup', '.bak', '.OLD']
    
    uploaded = 0
    failed = 0
    
    for item in Path(local_dir).iterdir():
        if any(excl in item.name for excl in exclude):
            continue
        
        if item.is_file():
            remote_file = f"{remote_dir}/{item.name}" if remote_dir else item.name
            try:
                with open(item, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_file}', f)
                uploaded += 1
                print(f"  ✅ {remote_file}")
            except Exception as e:
                failed += 1
                print(f"  ❌ {remote_file}: {e}")
        
        elif item.is_dir():
            new_remote_dir = f"{remote_dir}/{item.name}" if remote_dir else item.name
            try:
                ftp.mkd(new_remote_dir)
            except:
                pass  # Já existe
            
            u, f = upload_dir_recursive(ftp, str(item), new_remote_dir, exclude, base_dir)
            uploaded += u
            failed += f
    
    return uploaded, failed

def main():
    print("🚀 DEPLOY COMPLETO - SPRINT 33")
    print("=" * 70)
    
    total_up = 0
    total_fail = 0
    
    try:
        print("\n1️⃣ Conectando...")
        ftp = ftplib.FTP(FTP_HOST, timeout=60)
        ftp.set_pasv(True)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd(FTP_DIR)
        print(f"✅ Conectado em: {ftp.pwd()}\n")
        
        # Deploy config/
        print("2️⃣ Deploy config/...")
        config_dir = PROJECT_ROOT / 'config'
        if config_dir.exists():
            try:
                ftp.mkd('config')
            except:
                pass
            u, f = upload_dir_recursive(ftp, str(config_dir), 'config')
            total_up += u
            total_fail += f
            print(f"   {u} enviados, {f} falhas\n")
        
        # Deploy src/
        print("3️⃣ Deploy src/...")
        src_dir = PROJECT_ROOT / 'src'
        if src_dir.exists():
            try:
                ftp.mkd('src')
            except:
                pass
            u, f = upload_dir_recursive(ftp, str(src_dir), 'src')
            total_up += u
            total_fail += f
            print(f"   {u} enviados, {f} falhas\n")
        
        # Deploy public/ files (exceto index que já foi)
        print("4️⃣ Deploy public/...")
        public_dir = PROJECT_ROOT / 'public'
        if public_dir.exists():
            for item in public_dir.iterdir():
                if item.is_file() and item.name not in ['index.php', 'index_sprint31.php']:
                    try:
                        with open(item, 'rb') as f:
                            ftp.storbinary(f'STOR {item.name}', f)
                        total_up += 1
                        print(f"  ✅ {item.name}")
                    except Exception as e:
                        total_fail += 1
                        print(f"  ❌ {item.name}: {e}")
        
        print("\n" + "=" * 70)
        print(f"✅ DEPLOY COMPLETO!")
        print(f"📊 {total_up} arquivos enviados")
        print(f"❌ {total_fail} falhas")
        print(f"🌐 https://prestadores.clinfec.com.br")
        print("=" * 70)
        
        ftp.quit()
        return total_fail == 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    import sys
    sys.exit(0 if main() else 1)
