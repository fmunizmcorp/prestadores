#!/usr/bin/env python3
"""
Sprint 23 - Deploy Index.php (Migrations Desabilitadas)
TEMPORÁRIA: Desabilita migrations até OPcache expirar
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
print("SPRINT 23 - DEPLOY INDEX.PHP (MIGRATIONS DESABILITADAS)")
print("="*80)
print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
print("Ação: Desabilitar migrations temporariamente até OPcache expirar\n")

try:
    # Read local file
    local_file = "public/index.php"
    with open(local_file, 'rb') as f:
        content = f.read()
    
    local_md5 = md5_hash(content)
    print(f"📖 Arquivo local: {local_file}")
    print(f"   Tamanho: {len(content):,} bytes")
    print(f"   MD5: {local_md5}")
    
    # Verify changes
    content_str = content.decode('utf-8')
    if "TEMPORARIAMENTE DESABILITADO - Sprint 23" in content_str:
        print(f"   ✅ Migrations desabilitadas")
    else:
        print(f"   ❌ ERRO: Mudança não encontrada!")
        exit(1)
    
    # Connect to FTP
    print(f"\n📡 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado")
    
    # Navigate
    ftp.cwd(FTP_REMOTE_DIR)
    print(f"📂 Diretório: {FTP_REMOTE_DIR}")
    
    # Backup first
    import time
    backup_name = f"public/index.php.backup_before_disable_migrations_{int(time.time())}"
    try:
        ftp.rename("public/index.php", backup_name)
        print(f"💾 Backup: {backup_name}")
    except:
        print(f"⚠️  Aviso: Não foi possível fazer backup")
    
    # Upload
    remote_file = "public/index.php"
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
        exit(1)
    
    ftp.quit()
    
    print("\n" + "="*80)
    print("IMPORTANTE")
    print("="*80)
    print("✅ Migrations desabilitadas temporariamente")
    print("✅ Sistema deve funcionar agora (sem erro fatal)")
    print("⚠️  Esta é uma solução TEMPORÁRIA")
    print("\nPróximo passo:")
    print("  Testar sistema: https://clinfec.com.br/prestadores/")
    
except Exception as e:
    print(f"\n❌ ERRO: {e}")
    import traceback
    traceback.print_exc()
