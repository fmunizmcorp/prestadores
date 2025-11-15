#!/usr/bin/env python3
"""
Sprint 23 - FORCE DEPLOY
Força o upload do index.php corrigido para o servidor
"""
import ftplib
import hashlib
import time
from datetime import datetime

# FTP Credentials
FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_REMOTE_DIR = "/domains/clinfec.com.br/public_html/prestadores"

def md5_hash(content):
    """Calculate MD5 hash"""
    if isinstance(content, str):
        content = content.encode('utf-8')
    return hashlib.md5(content).hexdigest()

def force_deploy():
    """Force upload of corrected index.php"""
    print("\n" + "="*80)
    print("SPRINT 23 - FORCE DEPLOY")
    print("="*80)
    print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Objetivo: Forçar upload do index.php corrigido (Sprint 22)\n")
    
    try:
        # Read local file
        local_file = "public/index.php"
        print(f"📖 Lendo arquivo local: {local_file}")
        with open(local_file, 'rb') as f:
            local_content_bytes = f.read()
        
        local_size = len(local_content_bytes)
        local_md5 = md5_hash(local_content_bytes)
        
        print(f"   Tamanho: {local_size:,} bytes")
        print(f"   MD5: {local_md5}")
        
        # Count corrections
        local_content_str = local_content_bytes.decode('utf-8')
        uppercase_count = local_content_str.count("'/Controllers/")
        print(f"   ✅ Contém {uppercase_count} ocorrências de '/Controllers/' (maiúsculo)")
        
        # Connect to FTP
        print("\n📡 Conectando ao FTP...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Conectado como: {FTP_USER}")
        
        # Navigate to remote directory
        ftp.cwd(FTP_REMOTE_DIR)
        print(f"📂 Diretório: {FTP_REMOTE_DIR}")
        
        # Backup old file first
        remote_file = "public/index.php"
        backup_file = f"public/index.php.backup_sprint23_{int(time.time())}"
        
        print(f"\n💾 Fazendo backup do arquivo atual...")
        try:
            ftp.rename(remote_file, backup_file)
            print(f"✅ Backup criado: {backup_file}")
        except Exception as e:
            print(f"⚠️  Aviso: Não foi possível fazer backup: {e}")
        
        # Upload new file
        print(f"\n📤 Uploading arquivo corrigido...")
        from io import BytesIO
        ftp.storbinary(f'STOR {remote_file}', BytesIO(local_content_bytes))
        
        # Verify upload
        print(f"\n🔍 Verificando upload...")
        uploaded_size = ftp.size(remote_file)
        print(f"   Tamanho no servidor: {uploaded_size:,} bytes")
        
        if uploaded_size == local_size:
            print(f"✅ Tamanho IDÊNTICO!")
        else:
            print(f"❌ Tamanho DIFERENTE! Local: {local_size}, Servidor: {uploaded_size}")
            return False
        
        # Download to verify MD5
        print(f"\n📥 Baixando para verificar MD5...")
        server_content = BytesIO()
        ftp.retrbinary(f'RETR {remote_file}', server_content.write)
        server_content_bytes = server_content.getvalue()
        server_md5 = md5_hash(server_content_bytes)
        
        print(f"\n" + "="*80)
        print("VERIFICAÇÃO MD5")
        print("="*80)
        print(f"Local:    {local_md5}")
        print(f"Servidor: {server_md5}")
        
        if server_md5 == local_md5:
            print("\n✅✅✅ DEPLOY 100% VERIFICADO! ✅✅✅")
            print("   Arquivo no servidor é IDÊNTICO ao local!")
            
            # Count on server too
            server_content_str = server_content_bytes.decode('utf-8')
            server_uppercase = server_content_str.count("'/Controllers/")
            print(f"   ✅ Servidor tem {server_uppercase} ocorrências de '/Controllers/'")
            
            success = True
        else:
            print("\n❌ MD5 DIFERENTE - Deploy falhou!")
            success = False
        
        ftp.quit()
        
        if success:
            print("\n" + "="*80)
            print("PRÓXIMOS PASSOS")
            print("="*80)
            print("1. ✅ Deploy verificado")
            print("2. 🔄 Limpar OPcache (criar script)")
            print("3. 🔄 Testar sistema novamente")
            print("4. 🔄 Commit + Push + PR")
        
        return success
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    result = force_deploy()
    exit(0 if result else 1)
