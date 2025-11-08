#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

print("=== ESTRUTURA DE /src/views/ ===\n")
ftp.cwd('/src/views')
dirs = []
ftp.retrlines('NLST', dirs.append)
print(f"Diretórios/arquivos: {len(dirs)}\n")
for d in sorted(dirs):
    if d not in ['.', '..']:
        print(f"  {d}")

ftp.quit()
