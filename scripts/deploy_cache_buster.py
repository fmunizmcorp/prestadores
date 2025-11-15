#!/usr/bin/env python3
"""
Deploy Cache-Busted Files
DELETE + Upload strategy para forçar cache clear
"""

import ftplib
import os
import time

# FTP Credentials
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/'  # Conecta direto na raiz do subdomínio

def connect_ftp():
    """Connect to FTP server"""
    print("🔌 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.set_pasv(True)
    
    # Change to directory
    ftp.cwd(FTP_DIR)
    print(f"✅ Conectado: {ftp.pwd()}\n")
    return ftp

def delete_file(ftp, remote_path):
    """Delete file if exists"""
    try:
        ftp.delete(remote_path)
        print(f"   🗑️  Deletado: {remote_path}")
        return True
    except ftplib.error_perm:
        # File doesn't exist, that's ok
        return False

def upload_file(ftp, local_path, remote_path):
    """Upload and verify file"""
    try:
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        
        # Verify size
        size = ftp.size(remote_path)
        local_size = os.path.getsize(local_path)
        
        if size == local_size:
            print(f"   ✅ Uploaded: {remote_path} ({size} bytes)")
            return True
        else:
            print(f"   ⚠️  Size mismatch: local={local_size}, remote={size}")
            return False
            
    except Exception as e:
        print(f"   ❌ Error uploading {remote_path}: {e}")
        return False

def main():
    """Deploy cache-busted files"""
    
    print("🚀 DEPLOY CACHE-BUSTER - DELETE + Upload\n")
    
    # Files to deploy (with cache buster)
    files = [
        'index.php',
        'src/Controllers/AuthController.php',
        'src/Controllers/BaseController.php',
        'src/Controllers/DashboardController.php',
        'src/Controllers/EmpresaTomadoraController.php',
        'src/Controllers/EmpresaPrestadoraController.php',
        'src/Controllers/ContratoController.php',
        'src/Controllers/ProjetoController.php',
        'src/Controllers/AtividadeController.php',
        'src/Controllers/ServicoController.php',
        'src/Controllers/ServicoValorController.php',
        'src/Database.php',
        'src/helpers.php',
        'config/database.php',
        'config/app.php',
        'config/config.php',
    ]
    
    base_path = '/home/user/webapp'
    
    try:
        ftp = connect_ftp()
        
        success = 0
        failed = 0
        
        for file_rel in files:
            print(f"\n📦 {file_rel}")
            local_path = os.path.join(base_path, file_rel)
            remote_path = file_rel
            
            if not os.path.exists(local_path):
                print(f"   ⚠️  Local file não existe")
                failed += 1
                continue
            
            # DELETE first
            delete_file(ftp, remote_path)
            
            # Wait a moment
            time.sleep(0.2)
            
            # Upload
            if upload_file(ftp, local_path, remote_path):
                success += 1
            else:
                failed += 1
        
        ftp.quit()
        
        print(f"\n\n📊 RESULTADO:")
        print(f"   ✅ Sucesso: {success}")
        print(f"   ❌ Falhas: {failed}")
        
        print(f"\n🧪 TESTE:")
        print(f"   https://prestadores.clinfec.com.br/?page=login")
        print(f"\n⏳ Aguarde 30 segundos e teste novamente")
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        return 1
    
    return 0

if __name__ == '__main__':
    exit(main())
