#!/usr/bin/env python3
"""
Script para encontrar o diretório raiz do WordPress
Sprint 33 - Localizar wp-config.php e .htaccess correto
"""

import ftplib
import sys
from io import BytesIO

# Configurações FTP
FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'timeout': 60
}

def check_directory(ftp, path, name=""):
    """Verifica se diretório tem WordPress"""
    try:
        current = ftp.pwd()
        ftp.cwd(path)
        files = []
        ftp.retrlines('NLST', files.append)
        
        has_wp_config = 'wp-config.php' in files
        has_htaccess = '.htaccess' in files
        has_wp_content = 'wp-content' in files
        has_wp_includes = 'wp-includes' in files
        
        ftp.cwd(current)
        
        return {
            'path': path,
            'name': name,
            'wp_config': has_wp_config,
            'htaccess': has_htaccess,
            'wp_content': has_wp_content,
            'wp_includes': has_wp_includes,
            'is_wordpress': has_wp_config and (has_wp_content or has_wp_includes)
        }
    except:
        return None

def download_file_content(ftp, path):
    """Baixa conteúdo de arquivo"""
    try:
        buffer = BytesIO()
        ftp.retrbinary(f'RETR {path}', buffer.write)
        return buffer.getvalue().decode('utf-8', errors='replace')
    except:
        return None

def main():
    print("=" * 70)
    print("PROCURANDO RAIZ DO WORDPRESS")
    print("Sprint 33 - Localizar Configuração Correta")
    print("=" * 70)
    
    try:
        # Conectar FTP
        print("\n1️⃣ Conectando ao FTP...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado: {ftp.getwelcome()}")
        
        # Diretórios para verificar
        print("\n" + "=" * 70)
        print("2️⃣ VERIFICANDO DIRETÓRIOS")
        print("=" * 70)
        
        check_dirs = [
            ('/public_html', 'public_html (raiz atual)'),
            ('/public_html/public_html', 'public_html/public_html (nested)'),
            ('/', 'root FTP'),
        ]
        
        wordpress_locations = []
        
        for dir_path, dir_name in check_dirs:
            print(f"\n🔍 Verificando: {dir_name}")
            print(f"   Caminho: {dir_path}")
            
            result = check_directory(ftp, dir_path, dir_name)
            if result:
                print(f"   wp-config.php: {'✅' if result['wp_config'] else '❌'}")
                print(f"   .htaccess: {'✅' if result['htaccess'] else '❌'}")
                print(f"   wp-content/: {'✅' if result['wp_content'] else '❌'}")
                print(f"   wp-includes/: {'✅' if result['wp_includes'] else '❌'}")
                
                if result['is_wordpress']:
                    print(f"   🎯 WORDPRESS ENCONTRADO!")
                    wordpress_locations.append(result)
            else:
                print(f"   ❌ Diretório não acessível")
        
        # Análise
        print("\n" + "=" * 70)
        print("3️⃣ ANÁLISE")
        print("=" * 70)
        
        if wordpress_locations:
            print(f"\n✅ {len(wordpress_locations)} instalação(ões) WordPress encontrada(s):")
            for loc in wordpress_locations:
                print(f"\n📍 {loc['name']}")
                print(f"   Caminho: {loc['path']}")
                
                # Baixar e mostrar .htaccess
                htaccess_path = loc['path'].rstrip('/') + '/.htaccess'
                print(f"\n   📄 Lendo .htaccess...")
                
                htaccess_content = download_file_content(ftp, htaccess_path)
                if htaccess_content:
                    print(f"   Tamanho: {len(htaccess_content)} bytes")
                    
                    # Verificar se menciona prestadores
                    if 'prestadores' in htaccess_content.lower():
                        print(f"   ✅ Contém 'prestadores'")
                    else:
                        print(f"   ⚠️ NÃO contém 'prestadores'")
                    
                    # Mostrar conteúdo
                    print(f"\n   --- CONTEÚDO DO .HTACCESS ---")
                    for line in htaccess_content.split('\n')[:50]:  # Primeiras 50 linhas
                        print(f"   {line}")
                    if len(htaccess_content.split('\n')) > 50:
                        print(f"   ... ({len(htaccess_content.split('\n')) - 50} linhas omitidas)")
                    print(f"   --- FIM ---")
                else:
                    print(f"   ❌ Não foi possível ler o arquivo")
        else:
            print("\n❌ Nenhuma instalação WordPress encontrada nos diretórios verificados")
        
        # Recomendação
        print("\n" + "=" * 70)
        print("4️⃣ RECOMENDAÇÃO")
        print("=" * 70)
        
        if wordpress_locations:
            wp_root = wordpress_locations[0]['path']
            print(f"\n💡 O .htaccess do WordPress está em: {wp_root}")
            print(f"\n📝 Você precisa adicionar ao .htaccess do WordPress:")
            print(f"""
# Excluir /prestadores/ do WordPress routing
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{{REQUEST_URI}} ^/prestadores [NC]
    RewriteRule ^ - [L]
</IfModule>
            """)
        
        # Fechar conexão
        ftp.quit()
        print("\n" + "=" * 70)
        print("✅ VERIFICAÇÃO CONCLUÍDA")
        print("=" * 70)
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
