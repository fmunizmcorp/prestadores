#!/usr/bin/env python3
"""
Deploy Diagnostic Auth Script
Envia script de diagnóstico para o servidor
"""

import ftplib
import os

# FTP Credentials
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/'

def deploy_diagnostic():
    """Deploy diagnostic script"""
    
    print("🚀 DEPLOY: diagnostic_auth.php\n")
    
    local_file = '/home/user/webapp/diagnostic_auth.php'
    remote_file = 'diagnostic_auth.php'
    
    try:
        # Connect
        print("🔌 Conectando ao FTP...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.set_pasv(True)
        ftp.cwd(FTP_DIR)
        print(f"✅ Conectado: {ftp.pwd()}\n")
        
        # Upload
        print(f"📤 Enviando {remote_file}...")
        with open(local_file, 'rb') as f:
            ftp.storbinary(f'STOR {remote_file}', f)
        
        # Verify
        size = ftp.size(remote_file)
        local_size = os.path.getsize(local_file)
        
        if size == local_size:
            print(f"✅ Upload OK: {size} bytes")
        else:
            print(f"⚠️  Tamanhos diferentes: local={local_size}, remoto={size}")
        
        ftp.quit()
        
        print(f"\n🎯 ACESSE AGORA:")
        print(f"   https://prestadores.clinfec.com.br/diagnostic_auth.php")
        print(f"\n✅ Script deployado com sucesso!")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        return 1

if __name__ == '__main__':
    exit(deploy_diagnostic())
