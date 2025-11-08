#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

print("=== CONTEÚDO DE /src/ ===")
ftp.cwd('/src')
files = []
ftp.retrlines('NLST', files.append)
for f in sorted(files):
    try:
        size = ftp.size(f)
        print(f"  {f}: {size:,} bytes")
    except:
        print(f"  {f}: (diretório)")

print("\n=== CONTEÚDO DE /config/ ===")
ftp.cwd('/config')
files = []
ftp.retrlines('NLST', files.append)
for f in sorted(files):
    try:
        size = ftp.size(f)
        print(f"  {f}: {size:,} bytes")
    except:
        print(f"  {f}: (diretório)")

ftp.quit()
