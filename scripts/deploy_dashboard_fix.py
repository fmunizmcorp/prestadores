#!/usr/bin/env python3
"""
Deploy Dashboard Fix
"""

import ftplib
import os
import time

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/'

def deploy():
    print("🚀 DEPLOY: Correção do Dashboard\n")
    
    files = [
        'index.php'
    ]
    
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.set_pasv(True)
        ftp.cwd(FTP_DIR)
        
        for filename in files:
            full_path = f'/home/user/webapp/{filename}'
            
            print(f"📤 Enviando {filename}...")
            
            # Delete first
            try:
                ftp.delete(filename)
                print(f"   🗑️  Deletado: {filename}")
                time.sleep(0.2)
            except:
                pass
            
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
        print(f"\n⏳ Aguarde 30 segundos para cache processar...\n")
        time.sleep(30)
        
        print(f"🧪 TESTE AGORA:")
        print(f"   1. Acesse: https://prestadores.clinfec.com.br/?page=login")
        print(f"   2. Login: admin@clinfec.com.br / Master@2024")
        print(f"   3. Dashboard deve carregar corretamente!")
        
        return 0
    except Exception as e:
        print(f"❌ ERRO: {e}")
        return 1

if __name__ == '__main__':
    exit(deploy())
