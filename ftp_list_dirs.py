#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

print("Conectando ao FTP...")
ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

print("\nDiretório atual:", ftp.pwd())
print("\nConteúdo:")
ftp.retrlines('LIST')

print("\n\nTentando acessar public_html:")
try:
    ftp.cwd('public_html')
    print("PWD:", ftp.pwd())
    print("\nConteúdo de public_html:")
    ftp.retrlines('LIST')
except Exception as e:
    print("Erro:", e)

print("\n\nTentando acessar domains:")
try:
    ftp.cwd('/domains')
    print("PWD:", ftp.pwd())
    print("\nConteúdo de domains:")
    ftp.retrlines('LIST')
except Exception as e:
    print("Erro:", e)

ftp.quit()
