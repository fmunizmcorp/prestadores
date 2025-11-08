#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd('/')

# Tenta baixar index.php da raiz
try:
    with open('index_raiz_atual.php', 'wb') as f:
        ftp.retrbinary('RETR index.php', f.write)
    print("✓ Downloaded index.php from root")
except Exception as e:
    print(f"✗ Error: {e}")

# Tenta baixar public/index.php
ftp.cwd('/public')
try:
    with open('index_public_atual.php', 'wb') as f:
        ftp.retrbinary('RETR index.php', f.write)
    print("✓ Downloaded public/index.php")
except Exception as e:
    print(f"✗ Error downloading public/index.php: {e}")

ftp.quit()
