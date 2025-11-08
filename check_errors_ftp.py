#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

print("Conectando ao FTP...")
ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

print("\n=== ESTRUTURA DE DIRETÓRIOS ===")
ftp.cwd('/')
print("Conteúdo da raiz:")
dirs = []
files = []
ftp.retrlines('LIST', lambda x: (dirs.append(x) if x.startswith('d') else files.append(x)))
for d in sorted(dirs)[:10]:
    print(f"  DIR:  {d.split()[-1]}")
for f in sorted(files)[:20]:
    print(f"  FILE: {f.split()[-1]}")

# Verificar se existe public/
try:
    print("\n=== CONTEÚDO DE public/ ===")
    ftp.cwd('/public')
    ftp.retrlines('NLST', lambda x: print(f"  {x}"))
except:
    print("  ❌ Diretório public/ não existe!")

# Verificar index.php
print("\n=== ARQUIVOS INDEX ===")
ftp.cwd('/')
try:
    print(f"  index.php na raiz: {ftp.size('index.php')} bytes")
except:
    print("  ❌ index.php não existe na raiz")

try:
    print(f"  public/index.php: {ftp.size('public/index.php')} bytes")
except:
    print("  ❌ public/index.php não existe")

ftp.quit()
