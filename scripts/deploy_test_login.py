#!/usr/bin/env python3
"""
Deploy Test Login Script
"""

import ftplib
import os

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/'

def deploy():
    print("🚀 DEPLOY: test_login_direct.php\n")
    
    local_file = '/home/user/webapp/test_login_direct.php'
    remote_file = 'test_login_direct.php'
    
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.set_pasv(True)
        ftp.cwd(FTP_DIR)
        
        with open(local_file, 'rb') as f:
            ftp.storbinary(f'STOR {remote_file}', f)
        
        size = ftp.size(remote_file)
        print(f"✅ Upload OK: {size} bytes")
        
        ftp.quit()
        
        print(f"\n🎯 ACESSE:")
        print(f"   https://prestadores.clinfec.com.br/test_login_direct.php")
        
        return 0
    except Exception as e:
        print(f"❌ ERRO: {e}")
        return 1

if __name__ == '__main__':
    exit(deploy())
