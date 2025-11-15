#!/usr/bin/env python3
"""Download file via FTP"""

import ftplib
import sys

def download_file(remote_path, local_path):
    try:
        ftp = ftplib.FTP('ftp.clinfec.com.br')
        ftp.login('u673902663.genspark1', 'Genspark1@')
        
        print(f"Downloading: {remote_path}")
        print(f"To: {local_path}")
        
        with open(local_path, 'wb') as f:
            ftp.retrbinary(f'RETR {remote_path}', f.write)
        
        ftp.quit()
        
        print(f"✅ Success! Downloaded {local_path}")
        return 0
        
    except Exception as e:
        print(f"❌ Error: {e}")
        return 1

if __name__ == '__main__':
    if len(sys.argv) != 3:
        print("Usage: python3 ftp_download_file.py <remote_path> <local_path>")
        sys.exit(1)
    
    sys.exit(download_file(sys.argv[1], sys.argv[2]))
