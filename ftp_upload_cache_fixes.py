#!/usr/bin/env python3
"""Upload cache fixing files"""

import ftplib
import os

def upload(local, remote):
    try:
        ftp = ftplib.FTP('ftp.clinfec.com.br')
        ftp.login('u673902663.genspark1', 'Genspark1@')
        
        print(f"Uploading {local} -> {remote}")
        
        with open(local, 'rb') as f:
            ftp.storbinary(f'STOR {remote}', f)
        
        size = ftp.size(remote)
        print(f"✅ Success! Size: {size} bytes")
        
        ftp.quit()
        return 0
    except Exception as e:
        print(f"❌ Error: {e}")
        return 1

# Upload files
base = "/domains/clinfec.com.br/public_html/prestadores"

files = [
    ('.user.ini', f'{base}/.user.ini'),
    ('nuclear_cache_clear_v2.php', f'{base}/nuclear_cache_clear_v2.php'),
]

for local, remote in files:
    upload(local, remote)
