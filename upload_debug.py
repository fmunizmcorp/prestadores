#!/usr/bin/env python3
import ftplib
import sys

# FTP Configuration
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663'
FTP_PASS = 'MCorp@2024!'
REMOTE_DIR = '/domains/clinfec.com.br/public_html/prestadores'

try:
    print("Conectando ao servidor FTP...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(REMOTE_DIR)
    
    print("Enviando debug_failing_routes.php...")
    with open('debug_failing_routes.php', 'rb') as f:
        ftp.storbinary('STOR debug_failing_routes.php', f)
    
    print("✓ Arquivo enviado com sucesso!")
    ftp.quit()
    
except Exception as e:
    print(f"✗ Erro: {e}")
    sys.exit(1)
