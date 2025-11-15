#!/usr/bin/env python3
"""
Re-deploy ProjetoController.php with correct fix
"""

import ftplib
import os
import sys

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

FILE_TO_DEPLOY = 'src/Controllers/ProjetoController.php'

def ensure_dir(ftp, path):
    """Ensure directory exists on FTP"""
    dirs = []
    while path:
        dirs.append(path)
        path = os.path.dirname(path)
    
    dirs.reverse()
    
    for directory in dirs:
        if directory and directory != '/':
            try:
                ftp.cwd(f'/{directory}')
            except:
                try:
                    ftp.mkd(f'/{directory}')
                except:
                    pass

def main():
    print("=" * 80)
    print("RE-DEPLOYING ProjetoController.php Fix")
    print("=" * 80)
    
    # Verify local file exists and has fix
    if not os.path.exists(FILE_TO_DEPLOY):
        print(f"✗ Local file not found: {FILE_TO_DEPLOY}")
        return 1
    
    # Check if fix is present
    with open(FILE_TO_DEPLOY, 'r') as f:
        content = f.read()
        if 'return $this->projeto;' not in content:
            print("✗ Local file doesn't contain the fix!")
            return 1
    
    print(f"✓ Local file verified - contains fix")
    print(f"✓ File size: {os.path.getsize(FILE_TO_DEPLOY)} bytes")
    
    # Connect to FTP
    print(f"\n🔌 Connecting to {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("✓ Connected")
    except Exception as e:
        print(f"✗ Connection failed: {e}")
        return 1
    
    # Ensure directory exists
    remote_dir = os.path.dirname(FILE_TO_DEPLOY)
    ensure_dir(ftp, remote_dir)
    
    # Upload file
    print(f"\n📤 Uploading {FILE_TO_DEPLOY}...")
    try:
        with open(FILE_TO_DEPLOY, 'rb') as f:
            ftp.storbinary(f'STOR /{FILE_TO_DEPLOY}', f)
        print(f"✓ Upload successful!")
    except Exception as e:
        print(f"✗ Upload failed: {e}")
        ftp.quit()
        return 1
    
    # Verify upload
    print(f"\n🔍 Verifying upload...")
    try:
        size = ftp.size(f'/{FILE_TO_DEPLOY}')
        local_size = os.path.getsize(FILE_TO_DEPLOY)
        if size == local_size:
            print(f"✓ File size matches: {size} bytes")
        else:
            print(f"⚠️  File size mismatch: remote={size}, local={local_size}")
    except:
        print(f"⚠️  Could not verify file size")
    
    ftp.quit()
    
    print("\n" + "=" * 80)
    print("✅ RE-DEPLOYMENT COMPLETE")
    print("=" * 80)
    print("\nProjetoController.php has been re-deployed with the correct fix.")
    print("The Projetos module should now work correctly.")
    
    return 0

if __name__ == '__main__':
    sys.exit(main())
