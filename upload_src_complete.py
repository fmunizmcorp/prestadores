#!/usr/bin/env python3
import ftplib
import os
from pathlib import Path

ftp = ftplib.FTP('ftp.clinfec.com.br', timeout=120)
ftp.login('u673902663.genspark1', 'Genspark1@')

print("Uploading src/ directory...")
count = 0

for root, dirs, files in os.walk('src'):
    for file in files:
        if file.endswith('.php'):
            local_path = os.path.join(root, file)
            remote_path = '/' + local_path.replace('\\', '/')
            
            # Criar diretórios
            remote_dir = os.path.dirname(remote_path)
            parts = remote_dir.split('/')
            current = ''
            for part in parts:
                if not part:
                    continue
                current += '/' + part
                try:
                    ftp.cwd(current)
                except:
                    try:
                        ftp.mkd(current)
                    except:
                        pass
            
            # Upload
            try:
                with open(local_path, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_path}', f)
                count += 1
                if count % 20 == 0:
                    print(f"  {count} arquivos...")
            except Exception as e:
                print(f"✗ {local_path}: {e}")

print(f"\n✓ {count} arquivos PHP enviados")
ftp.quit()
