#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

print("=== VERIFICANDO .htaccess ===\n")

# Baixar .htaccess da raiz
ftp.cwd('/')
try:
    with open('/tmp/htaccess_root.txt', 'wb') as f:
        ftp.retrbinary('RETR .htaccess', f.write)
    print("✓ .htaccess existe na raiz")
    with open('/tmp/htaccess_root.txt', 'r') as f:
        content = f.read()
        print(f"  Tamanho: {len(content)} bytes")
        print(f"  Primeiras linhas:")
        for line in content.split('\n')[:5]:
            print(f"    {line}")
except Exception as e:
    print(f"✗ .htaccess não existe na raiz: {e}")

# Verificar public/.htaccess
print("\n=== VERIFICANDO public/.htaccess ===\n")
ftp.cwd('/public')
try:
    with open('/tmp/htaccess_public.txt', 'wb') as f:
        ftp.retrbinary('RETR .htaccess', f.write)
    print("✓ public/.htaccess existe")
    with open('/tmp/htaccess_public.txt', 'r') as f:
        content = f.read()
        print(f"  Tamanho: {len(content)} bytes")
        print(f"  Conteúdo:")
        print(content)
except Exception as e:
    print(f"✗ public/.htaccess não existe: {e}")

ftp.quit()
