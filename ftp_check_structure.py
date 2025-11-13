#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

print("=== ESTRUTURA FTP ===\n")
print("Diretório inicial:", ftp.pwd())
print("\nConteúdo do diretório raiz:")
files = []
ftp.retrlines('NLST', files.append)
for f in sorted(files):
    print(f"  - {f}")

# Verificar se 'public' existe
if 'public' in files:
    print("\n✓ Diretório 'public' encontrado")
    print("\nConteúdo de /public:")
    ftp.cwd('/public')
    public_files = []
    ftp.retrlines('NLST', public_files.append)
    for f in sorted(public_files)[:10]:
        print(f"  - {f}")

ftp.quit()
