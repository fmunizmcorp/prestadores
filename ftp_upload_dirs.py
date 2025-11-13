#!/usr/bin/env python3

import ftplib
import os
import tarfile

# Extract tarballs locally
print("=== Extracting tarballs locally ===")
tarballs = ['config_deploy.tar.gz', 'src_deploy.tar.gz', 'database_deploy.tar.gz']

extract_dir = '/tmp/prestadores_deploy'
os.makedirs(extract_dir, exist_ok=True)

for tarball in tarballs:
    print(f"Extracting {tarball}...")
    with tarfile.open(tarball, 'r:gz') as tar:
        tar.extractall(path=extract_dir)
    print(f"✅ {tarball} extracted")

print("\n=== Uploading to FTP /public_html/ ===")

# FTP connection
ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.login('u673902663.genspark1', 'Genspark1@')
ftp.cwd('/public_html')

def upload_directory(ftp, local_dir, remote_dir=''):
    """Recursively upload directory contents"""
    for item in os.listdir(local_dir):
        local_path = os.path.join(local_dir, item)
        remote_path = f"{remote_dir}/{item}" if remote_dir else item
        
        if os.path.isfile(local_path):
            print(f"  Uploading file: {remote_path}")
            with open(local_path, 'rb') as f:
                ftp.storbinary(f'STOR {item}', f)
        elif os.path.isdir(local_path):
            print(f"  Creating directory: {remote_path}")
            try:
                ftp.mkd(item)
            except ftplib.error_perm:
                pass  # Directory already exists
            
            ftp.cwd(item)
            upload_directory(ftp, local_path, remote_path)
            ftp.cwd('..')

# Upload each directory
for dir_name in ['config', 'src', 'database']:
    local_path = os.path.join(extract_dir, dir_name)
    if os.path.isdir(local_path):
        print(f"\n[Uploading {dir_name}/]")
        try:
            ftp.mkd(dir_name)
        except ftplib.error_perm:
            pass
        
        ftp.cwd(dir_name)
        upload_directory(ftp, local_path)
        ftp.cwd('..')
        print(f"✅ {dir_name}/ uploaded")

ftp.quit()

print("\n=== UPLOAD COMPLETE ===")
print("Directories deployed to /public_html/:")
print("  - config/")
print("  - src/")
print("  - database/")

