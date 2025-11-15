#!/usr/bin/env python3
"""
Sprint 24 - Clean Deploy
Deploy após limpeza OPcache via PHP 8.3
"""
import ftplib
import hashlib
from datetime import datetime
from io import BytesIO

FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_REMOTE_DIR = "/domains/clinfec.com.br/public_html/prestadores"

def md5_hash(content):
    if isinstance(content, str):
        content = content.encode('utf-8')
    return hashlib.md5(content).hexdigest()

print("\n" + "="*80)
print("SPRINT 24 - CLEAN DEPLOY")
print("="*80)
print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
print("Status: OPcache limpo via PHP 8.3")
print("Ação: Deploy clean de arquivos corrigidos\n")

files_to_deploy = [
    ("public/index.php", "public/index.php"),
    ("src/DatabaseMigration.php", "src/DatabaseMigration.php"),
]

try:
    # Connect
    print("📡 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado\n")
    
    ftp.cwd(FTP_REMOTE_DIR)
    
    for local_file, remote_file in files_to_deploy:
        print(f"📤 {remote_file}")
        
        # Read local
        with open(local_file, 'rb') as f:
            content = f.read()
        
        local_md5 = md5_hash(content)
        print(f"   Local MD5: {local_md5}")
        print(f"   Tamanho: {len(content):,} bytes")
        
        # Upload
        ftp.storbinary(f'STOR {remote_file}', BytesIO(content))
        
        # Verify
        server_content = BytesIO()
        ftp.retrbinary(f'RETR {remote_file}', server_content.write)
        server_md5 = md5_hash(server_content.getvalue())
        
        print(f"   Server MD5: {server_md5}")
        
        if server_md5 == local_md5:
            print(f"   ✅ VERIFICADO!\n")
        else:
            print(f"   ❌ DIFERENTE!\n")
    
    ftp.quit()
    
    print("="*80)
    print("✅ DEPLOY COMPLETO")
    print("="*80)
    print("\nPróximo passo: Testar sistema")
    
except Exception as e:
    print(f"\n❌ ERRO: {e}")
    import traceback
    traceback.print_exc()
