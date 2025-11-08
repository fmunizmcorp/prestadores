#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

print("=== CONTEÚDO DE /src/controllers/ ===\n")
ftp.cwd('/src/controllers')
files = []
ftp.retrlines('NLST', files.append)
print(f"Total de arquivos: {len(files)}\n")
for f in sorted(files):
    if f not in ['.', '..']:
        try:
            # Download e verifica tamanho
            with open(f'/tmp/check_{f}', 'wb') as local_f:
                ftp.retrbinary(f'RETR {f}', local_f.write)
            import os
            size = os.path.getsize(f'/tmp/check_{f}')
            print(f"  ✓ {f}: {size:,} bytes")
        except Exception as e:
            print(f"  ✗ {f}: ERRO - {e}")

ftp.quit()
