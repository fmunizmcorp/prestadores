#!/usr/bin/env python3
"""
Sprint 23 - Upload OPcache Clear Script
"""
import ftplib
from datetime import datetime

FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_REMOTE_DIR = "/domains/clinfec.com.br/public_html/prestadores"

print("\n" + "="*80)
print("SPRINT 23 - UPLOAD OPCACHE CLEAR SCRIPT")
print("="*80)
print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")

try:
    # Read local file
    local_file = "clear_opcache_sprint23.php"
    with open(local_file, 'rb') as f:
        content = f.read()
    
    print(f"📖 Arquivo local: {local_file} ({len(content):,} bytes)")
    
    # Connect to FTP
    print(f"\n📡 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado")
    
    # Navigate to directory
    ftp.cwd(FTP_REMOTE_DIR)
    print(f"📂 Diretório: {FTP_REMOTE_DIR}")
    
    # Upload
    from io import BytesIO
    print(f"\n📤 Uploading...")
    ftp.storbinary(f'STOR {local_file}', BytesIO(content))
    
    # Verify
    size = ftp.size(local_file)
    print(f"✅ Upload completo: {size:,} bytes")
    
    ftp.quit()
    
    # Print URL
    url = f"https://clinfec.com.br/prestadores/{local_file}"
    print(f"\n" + "="*80)
    print("✅✅✅ SCRIPT DEPLOYADO COM SUCESSO! ✅✅✅")
    print("="*80)
    print(f"\n🔗 URL: {url}")
    print(f"\nAcesse essa URL no navegador para limpar o OPcache!")
    print(f"\n" + "="*80)
    
except Exception as e:
    print(f"\n❌ ERRO: {e}")
    import traceback
    traceback.print_exc()
