#!/usr/bin/env python3
"""
Sprint 23 - Verify Deploy Status
Verifica se o deploy do Sprint 22 está realmente no servidor
"""
import ftplib
import hashlib
from datetime import datetime

# FTP Credentials
FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_REMOTE_DIR = "/domains/clinfec.com.br/public_html/prestadores"

def md5_hash(content):
    """Calculate MD5 hash"""
    return hashlib.md5(content.encode('utf-8')).hexdigest()

def download_server_file():
    """Download public/index.php from server"""
    print("\n" + "="*80)
    print("SPRINT 23 - VERIFICAÇÃO DE DEPLOY")
    print("="*80)
    print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Objetivo: Verificar se correções Sprint 22 estão no servidor\n")
    
    try:
        # Connect to FTP
        print("📡 Conectando ao FTP...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Conectado como: {FTP_USER}")
        
        # Navigate to remote directory
        ftp.cwd(FTP_REMOTE_DIR)
        print(f"📂 Diretório: {FTP_REMOTE_DIR}")
        
        # Download public/index.php
        remote_file = "public/index.php"
        local_file = "SERVER_index.php"
        
        print(f"\n📥 Baixando: {remote_file}")
        with open(local_file, 'wb') as f:
            ftp.retrbinary(f'RETR {remote_file}', f.write)
        
        # Get file size
        file_size = ftp.size(remote_file)
        print(f"✅ Download completo: {file_size} bytes")
        
        ftp.quit()
        
        # Read server content
        with open(local_file, 'r', encoding='utf-8') as f:
            server_content = f.read()
        
        # Read local corrected content
        with open('public/index.php', 'r', encoding='utf-8') as f:
            local_content = f.read()
        
        # Calculate MD5
        server_md5 = md5_hash(server_content)
        local_md5 = md5_hash(local_content)
        
        print("\n" + "="*80)
        print("COMPARAÇÃO MD5")
        print("="*80)
        print(f"Servidor: {server_md5}")
        print(f"Local:    {local_md5}")
        
        if server_md5 == local_md5:
            print("\n✅ IDÊNTICOS - Deploy está correto!")
            print("   Problema é OPcache servindo versão antiga")
        else:
            print("\n❌ DIFERENTES - Deploy NÃO foi aplicado!")
            print("   Precisamos refazer o deploy")
        
        # Count occurrences
        print("\n" + "="*80)
        print("ANÁLISE DE CONTEÚDO")
        print("="*80)
        
        server_lowercase = server_content.count("'/controllers/")
        server_uppercase = server_content.count("'/Controllers/")
        local_lowercase = local_content.count("'/controllers/")
        local_uppercase = local_content.count("'/Controllers/")
        
        print("\nServidor:")
        print(f"  '/controllers/' (minúsculo): {server_lowercase} ocorrências")
        print(f"  '/Controllers/' (maiúsculo): {server_uppercase} ocorrências")
        
        print("\nLocal (corrigido):")
        print(f"  '/controllers/' (minúsculo): {local_lowercase} ocorrências")
        print(f"  '/Controllers/' (maiúsculo): {local_uppercase} ocorrências")
        
        if server_lowercase > 0:
            print("\n❌ CONFIRMADO: Servidor TEM '/controllers/' minúsculo!")
            print("   Deploy NÃO foi aplicado corretamente")
        else:
            print("\n✅ Servidor não tem '/controllers/' minúsculo")
        
        print("\n" + "="*80)
        print("RESULTADO FINAL")
        print("="*80)
        
        if server_md5 != local_md5:
            print("🔴 DEPLOY FALHOU - Refazer deploy necessário")
            return False
        else:
            print("🟡 DEPLOY OK - Problema é OPcache")
            return True
            
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return None

if __name__ == "__main__":
    result = download_server_file()
    exit(0 if result else 1)
