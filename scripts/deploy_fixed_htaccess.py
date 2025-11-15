#!/usr/bin/env python3
"""
Deploy .htaccess corrigido para /public_html/prestadores/
Sprint 33 - Corrigir roteamento
"""

import ftplib
import sys
from io import BytesIO
import time

# Configurações FTP
FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'timeout': 60
}

# Conteúdo do .htaccess correto
HTACCESS_CONTENT = """# Clinfec Prestadores - .htaccess
# Sprint 33 - Fixed version to handle all requests

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /prestadores/
    
    # Allow direct access to existing files and directories
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Route everything else to index.php
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Security settings
<FilesMatch "\\.(htaccess|htpasswd|ini|log|sh|sql)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Disable directory listing
Options -Indexes

# PHP settings (if allowed)
<IfModule mod_php.c>
    php_flag display_errors On
    php_flag log_errors On
</IfModule>
"""

def backup_file(ftp, remote_path):
    """Cria backup do arquivo"""
    try:
        backup_path = remote_path + '.backup_sprint33_' + str(int(time.time()))
        buffer = BytesIO()
        ftp.retrbinary(f'RETR {remote_path}', buffer.write)
        buffer.seek(0)
        ftp.storbinary(f'STOR {backup_path}', buffer)
        return backup_path
    except Exception as e:
        print(f"⚠️ Erro ao fazer backup: {e}")
        return None

def upload_file(ftp, remote_path, content):
    """Envia arquivo para o servidor"""
    try:
        buffer = BytesIO(content.encode('utf-8'))
        ftp.storbinary(f'STOR {remote_path}', buffer)
        return True
    except Exception as e:
        print(f"❌ Erro ao enviar {remote_path}: {e}")
        return False

def main():
    print("=" * 70)
    print("DEPLOY DE .htaccess CORRIGIDO")
    print("Sprint 33 - Corrigir Roteamento")
    print("=" * 70)
    
    try:
        # Conectar FTP
        print("\n1️⃣ Conectando ao FTP...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado: {ftp.getwelcome()}")
        
        # Mudar para diretório
        ftp.cwd('/public_html/prestadores')
        print(f"📁 Diretório: {ftp.pwd()}")
        
        # Fazer backup
        print("\n2️⃣ Fazendo backup do .htaccess atual...")
        htaccess_path = '.htaccess'
        backup_path = backup_file(ftp, htaccess_path)
        if backup_path:
            print(f"✅ Backup criado: {backup_path}")
        else:
            print("⚠️ Não foi possível criar backup (arquivo pode não existir)")
        
        # Upload novo .htaccess
        print("\n3️⃣ Enviando .htaccess corrigido...")
        if upload_file(ftp, htaccess_path, HTACCESS_CONTENT):
            print(f"✅ .htaccess enviado ({len(HTACCESS_CONTENT)} bytes)")
            
            print("\n📄 CONTEÚDO ENVIADO:")
            print("-" * 70)
            print(HTACCESS_CONTENT)
            print("-" * 70)
        else:
            print("❌ Falha ao enviar .htaccess")
            ftp.quit()
            return 1
        
        # Testar
        print("\n" + "=" * 70)
        print("4️⃣ PRÓXIMOS PASSOS")
        print("=" * 70)
        
        print("\n✅ .htaccess corrigido foi enviado!")
        print("\n💡 Aguarde ~30 segundos para o servidor atualizar")
        print("\n🧪 Depois teste:")
        print("   1. https://clinfec.com.br/prestadores/test_basic.php")
        print("   2. https://clinfec.com.br/prestadores/")
        print("\n❗ IMPORTANTE: O teste #1 deve retornar 'OK' agora!")
        
        # Fechar conexão
        ftp.quit()
        print("\n" + "=" * 70)
        print("✅ DEPLOY CONCLUÍDO")
        print("=" * 70)
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
