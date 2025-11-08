#!/usr/bin/env python3
import ftplib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

files_to_check = [
    '/src/Database.php',
    '/src/DatabaseMigration.php',
    '/config/database.php',
    '/config/version.php',
    '/public/index.php',
]

print("=== VERIFICANDO ARQUIVOS CRÍTICOS ===\n")
for file_path in files_to_check:
    try:
        # Tenta obter tamanho
        size = ftp.size(file_path)
        print(f"✓ {file_path}: {size:,} bytes")
    except Exception as e:
        print(f"✗ {file_path}: ERRO - {e}")

# Tenta fazer download de Database.php
print("\n=== TENTANDO DOWNLOAD DE Database.php ===")
try:
    with open('/tmp/Database_test.php', 'wb') as f:
        ftp.retrbinary('RETR /src/Database.php', f.write)
    import os
    size = os.path.getsize('/tmp/Database_test.php')
    print(f"✓ Download OK: {size} bytes")
    with open('/tmp/Database_test.php', 'r') as f:
        content = f.read(100)
        print(f"Primeiros bytes: {repr(content)}")
except Exception as e:
    print(f"✗ Erro no download: {e}")

ftp.quit()
