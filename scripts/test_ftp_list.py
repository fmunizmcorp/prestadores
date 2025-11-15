#!/usr/bin/env python3
"""Testar listagem FTP"""
import ftplib

ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.set_pasv(True)
ftp.login('u673902663.genspark1', 'Genspark1@')

print("Diretório atual:", ftp.pwd())
print("\nArquivos na raiz:")
ftp.retrlines('LIST')

print("\n\nChecking index.php:")
try:
    size = ftp.size('index.php')
    print(f"✅ index.php existe ({size} bytes)")
except:
    print("❌ index.php NÃO ENCONTRADO")

print("\nChecking config/:")
try:
    ftp.cwd('config')
    print("✅ config/ existe")
    ftp.retrlines('LIST')
    ftp.cwd('..')
except:
    print("❌ config/ NÃO ENCONTRADO")

ftp.quit()
