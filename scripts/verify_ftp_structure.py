#!/usr/bin/env python3
"""
Verificar estrutura FTP
"""

import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/public_html/prestadores'

print("🔌 Conectando...")
ftp = ftplib.FTP(FTP_HOST, timeout=60)
ftp.login(FTP_USER, FTP_PASS)
print("✅ Conectado!\n")

print("📂 Estrutura em /public_html/prestadores:")
print("=" * 80)

ftp.cwd(FTP_DIR)
files = []
ftp.retrlines('LIST', files.append)

print("\nArquivos na RAIZ:")
for f in files:
    if not f.startswith('d'):  # Não é diretório
        parts = f.split()
        if len(parts) >= 9:
            filename = ' '.join(parts[8:])
            size = parts[4]
            print(f"  📄 {filename} ({size} bytes)")

print("\nDiretórios:")
for f in files:
    if f.startswith('d'):  # É diretório
        parts = f.split()
        if len(parts) >= 9:
            dirname = ' '.join(parts[8:])
            print(f"  📁 {dirname}/")

ftp.quit()
print("\n✅ Verificação completa!")
