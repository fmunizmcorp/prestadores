#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

# Check models
print("=== CONTEÚDO DE /src/models/ ===\n")
try:
    ftp.cwd('/src/models')
    files = []
    ftp.retrlines('NLST', files.append)
    print(f"Total: {len(files)} arquivos\n")
    for f in sorted(files)[:20]:
        if f not in ['.', '..']:
            print(f"  {f}")
except Exception as e:
    print(f"✗ Erro: {e}")

# Check helpers
print("\n=== CONTEÚDO DE /src/helpers/ ===\n")
try:
    ftp.cwd('/src/helpers')
    files = []
    ftp.retrlines('NLST', files.append)
    print(f"Total: {len(files)} arquivos\n")
    for f in sorted(files)[:20]:
        if f not in ['.', '..']:
            print(f"  {f}")
except Exception as e:
    print(f"✗ Erro: {e}")

ftp.quit()
