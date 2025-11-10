#!/usr/bin/env python3
import ftplib

ftp = ftplib.FTP('ftp.clinfec.com.br', timeout=120)
ftp.login('u673902663.genspark1', 'Genspark1@')

print("Uploading src/Helpers/FluxoCaixaHelper.php...")
with open('src/Helpers/FluxoCaixaHelper.php', 'rb') as f:
    ftp.storbinary('STOR /domains/clinfec.com.br/public_html/prestadores/src/Helpers/FluxoCaixaHelper.php', f)

print("✓ FluxoCaixaHelper.php uploaded!")
ftp.quit()
