#!/usr/bin/env python3
"""
Deploy Main Layout File
"""

import ftplib
import os
import time

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/'

def deploy():
    print("🚀 DEPLOY: main.php layout\n")
    
    files = [
        'src/Views/layouts/main.php'
    ]
    
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.set_pasv(True)
        ftp.cwd(FTP_DIR)
        
        for filename in files:
            full_path = f'/home/user/webapp/{filename}'
            
            print(f"📤 Enviando {filename}...")
            
            # Upload
            with open(full_path, 'rb') as f:
                ftp.storbinary(f'STOR {filename}', f)
            
            size = ftp.size(filename)
            local_size = os.path.getsize(full_path)
            
            if size == local_size:
                print(f"   ✅ OK: {size} bytes\n")
            else:
                print(f"   ⚠️  Size mismatch: local={local_size}, remote={size}\n")
        
        ftp.quit()
        
        print(f"✅ Deploy completo!")
        print(f"\n🧪 TESTE LOGIN AGORA:")
        print(f"   https://prestadores.clinfec.com.br/?page=login")
        print(f"   admin@clinfec.com.br / Master@2024")
        
        return 0
    except Exception as e:
        print(f"❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    exit(deploy())
