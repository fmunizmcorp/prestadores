#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

print("🚀 Uploading OPcache cleaner script...")

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

local_file = 'clear_opcache_automatic.php'
remote_file = '/clear_opcache_automatic.php'

with open(local_file, 'rb') as f:
    ftp.storbinary(f'STOR {remote_file}', f)

file_size = ftp.size(remote_file)
print(f"✓ Upload completo: {file_size} bytes")
print(f"\n🌐 Acesse: https://clinfec.com.br/clear_opcache_automatic.php")
print("   (Este script tentará limpar o OPcache automaticamente)\n")

ftp.quit()
