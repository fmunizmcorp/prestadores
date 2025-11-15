#!/usr/bin/env python3
"""
Sprint 23 - Deploy DatabaseMigration Fix
Corrige o erro fatal: Call to undefined method App\Database::exec()
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
    """Calculate MD5 hash"""
    if isinstance(content, str):
        content = content.encode('utf-8')
    return hashlib.md5(content).hexdigest()

print("\n" + "="*80)
print("SPRINT 23 - DEPLOY DATABASEMIGRATION FIX")
print("="*80)
print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
print(f"Erro corrigido: Call to undefined method App\\Database::exec()\n")

try:
    # Read local file
    local_file = "src/DatabaseMigration.php"
    with open(local_file, 'rb') as f:
        content = f.read()
    
    local_md5 = md5_hash(content)
    print(f"📖 Arquivo local: {local_file}")
    print(f"   Tamanho: {len(content):,} bytes")
    print(f"   MD5: {local_md5}")
    
    # Verify fix is present
    content_str = content.decode('utf-8')
    if "->getConnection()" in content_str:
        print(f"   ✅ Correção presente: ->getConnection()")
    else:
        print(f"   ❌ ERRO: Correção não encontrada!")
        exit(1)
    
    # Connect to FTP
    print(f"\n📡 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado")
    
    # Navigate
    ftp.cwd(FTP_REMOTE_DIR)
    print(f"📂 Diretório: {FTP_REMOTE_DIR}")
    
    # Upload
    remote_file = "src/DatabaseMigration.php"
    print(f"\n📤 Uploading {remote_file}...")
    ftp.storbinary(f'STOR {remote_file}', BytesIO(content))
    
    # Verify
    size = ftp.size(remote_file)
    print(f"✅ Upload completo: {size:,} bytes")
    
    # Download to verify
    print(f"\n🔍 Verificando MD5...")
    server_content = BytesIO()
    ftp.retrbinary(f'RETR {remote_file}', server_content.write)
    server_md5 = md5_hash(server_content.getvalue())
    
    print(f"\nMD5 Local:    {local_md5}")
    print(f"MD5 Servidor: {server_md5}")
    
    if server_md5 == local_md5:
        print(f"\n✅✅✅ DEPLOY 100% VERIFICADO! ✅✅✅")
    else:
        print(f"\n❌ MD5 DIFERENTE!")
    
    ftp.quit()
    
    print("\n" + "="*80)
    print("PRÓXIMOS PASSOS")
    print("="*80)
    print("1. ✅ DatabaseMigration.php corrigido")
    print("2. 🔄 Testar sistema novamente")
    print("3. 🔄 Verificar se erro fatal foi resolvido")
    
except Exception as e:
    print(f"\n❌ ERRO: {e}")
    import traceback
    traceback.print_exc()
