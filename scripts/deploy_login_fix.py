#!/usr/bin/env python3
"""
Deploy Login Fix
"""

import ftplib
import os

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/'

def deploy():
    print("🚀 DEPLOY: Correção do formulário de login\n")
    
    files = [
        ('src/Views/auth/login.php', 'src/Views/auth/login.php')
    ]
    
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.set_pasv(True)
        ftp.cwd(FTP_DIR)
        
        for local_file, remote_file in files:
            full_local_path = f'/home/user/webapp/{local_file}'
            
            print(f"📤 Enviando {local_file}...")
            
            # Delete first
            try:
                ftp.delete(remote_file)
                print(f"   🗑️  Deletado: {remote_file}")
            except:
                pass
            
            # Upload
            with open(full_local_path, 'rb') as f:
                ftp.storbinary(f'STOR {remote_file}', f)
            
            size = ftp.size(remote_file)
            print(f"   ✅ Uploaded: {size} bytes\n")
        
        ftp.quit()
        
        print(f"✅ Deploy completo!")
        print(f"\n🧪 TESTE AGORA:")
        print(f"   https://prestadores.clinfec.com.br/?page=login")
        print(f"\n📝 Credenciais:")
        print(f"   E-mail: admin@clinfec.com.br")
        print(f"   Senha: Master@2024")
        
        return 0
    except Exception as e:
        print(f"❌ ERRO: {e}")
        return 1

if __name__ == '__main__':
    exit(deploy())
