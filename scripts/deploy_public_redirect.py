#!/usr/bin/env python3
"""
Deploy redirect em /public/
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

# Garantir que /public existe
try:
    ftp.cwd(FTP_DIR + '/public')
    print("📁 /public já existe")
except:
    ftp.cwd(FTP_DIR)
    ftp.mkd('public')
    print("📁 /public criado")
    ftp.cwd(FTP_DIR + '/public')

# Upload do index.php redirect
print("\n📤 Uploading redirect index.php...")
with open('/home/user/webapp/public_redirect/index.php', 'rb') as f:
    ftp.storbinary('STOR index.php', f)
print("✅ Deploy completo!")

ftp.quit()
