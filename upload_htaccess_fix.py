#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

print("="*60)
print("UPLOAD CORREÇÃO - .htaccess")
print("="*60)

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd('/')

# Upload .htaccess correto
print("\n1. Fazendo backup do .htaccess atual...")
try:
    with open('/tmp/htaccess_old_backup.txt', 'wb') as f:
        ftp.retrbinary('RETR .htaccess', f.write)
    print("   ✓ Backup salvo")
except:
    print("   ✗ Não foi possível fazer backup")

print("\n2. Fazendo upload do .htaccess correto...")
with open('.htaccess', 'rb') as f:
    ftp.storbinary('STOR .htaccess', f)
    size = os.path.getsize('.htaccess')
    print(f"   ✓ Upload OK: {size} bytes")

print("\n3. Verificando upload...")
with open('/tmp/htaccess_new_verify.txt', 'wb') as f:
    ftp.retrbinary('RETR .htaccess', f)

with open('/tmp/htaccess_new_verify.txt', 'r') as f:
    content = f.read()
    print(f"   ✓ Tamanho: {len(content)} bytes")
    print(f"   Primeiras 5 linhas:")
    for line in content.split('\n')[:5]:
        print(f"     {line}")

ftp.quit()

print("\n" + "="*60)
print("✓ UPLOAD CONCLUÍDO")
print("="*60)
