#!/usr/bin/env python3
"""
Script para listar arquivos no diretório raiz e entender a estrutura
Sprint 33 - Diagnóstico de estrutura do servidor
"""

import ftplib
import sys

# Configurações FTP
FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'timeout': 60
}

def main():
    print("=" * 70)
    print("LISTAGEM DE ARQUIVOS NO SERVIDOR")
    print("Sprint 33 - Diagnóstico de Estrutura")
    print("=" * 70)
    
    try:
        # Conectar FTP
        print("\n1️⃣ Conectando ao FTP...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado: {ftp.getwelcome()}")
        
        # Verificar /public_html
        print("\n" + "=" * 70)
        print("2️⃣ LISTANDO: /public_html (raiz do site)")
        print("=" * 70)
        
        ftp.cwd('/public_html')
        files = []
        ftp.retrlines('LIST', files.append)
        
        print(f"\n📁 Total: {len(files)} itens\n")
        
        # Separar arquivos e diretórios
        directories = []
        regular_files = []
        
        for line in files:
            parts = line.split()
            if len(parts) >= 9:
                permissions = parts[0]
                name = ' '.join(parts[8:])
                size = parts[4] if len(parts) > 4 else '0'
                
                if permissions.startswith('d'):
                    directories.append((name, 'DIR'))
                else:
                    directories.append((name, size))
        
        # Mostrar diretórios
        print("📂 DIRETÓRIOS:")
        dir_count = 0
        for name, info in sorted(directories):
            if info == 'DIR':
                dir_count += 1
                # Destacar prestadores
                if name == 'prestadores':
                    print(f"   🎯 {name}/  ← APLICAÇÃO AQUI")
                elif name.startswith('wp-'):
                    print(f"   🔷 {name}/  ← WordPress")
                else:
                    print(f"   📁 {name}/")
        
        print(f"\n   Total: {dir_count} diretórios")
        
        # Mostrar arquivos importantes
        print("\n📄 ARQUIVOS IMPORTANTES:")
        file_count = 0
        for name, size in sorted(directories):
            if size != 'DIR':
                # Mostrar apenas arquivos importantes
                if name in ['index.php', '.htaccess', 'wp-config.php', 'index.html']:
                    file_count += 1
                    if name == '.htaccess':
                        print(f"   ⚙️ {name} ({size} bytes) ← IMPORTANTE!")
                    elif name == 'wp-config.php':
                        print(f"   🔧 {name} ({size} bytes) ← WordPress Config")
                    elif name.startswith('index.'):
                        print(f"   🏠 {name} ({size} bytes) ← Index File")
                    else:
                        print(f"   📄 {name} ({size} bytes)")
        
        if file_count == 0:
            print("   (Nenhum arquivo de configuração encontrado)")
        
        # Verificar se é WordPress
        print("\n" + "=" * 70)
        print("3️⃣ ANÁLISE DA ESTRUTURA")
        print("=" * 70)
        
        has_wordpress = any(name.startswith('wp-') for name, info in directories)
        has_prestadores = any(name == 'prestadores' and info == 'DIR' for name, info in directories)
        has_wp_config = any(name == 'wp-config.php' and info != 'DIR' for name, info in directories)
        
        print("\n🔍 DETECÇÃO:")
        if has_wordpress:
            print("   ✅ WordPress detectado (diretórios wp-*)")
        else:
            print("   ❌ WordPress NÃO detectado")
        
        if has_wp_config:
            print("   ✅ wp-config.php encontrado")
        else:
            print("   ❌ wp-config.php NÃO encontrado")
        
        if has_prestadores:
            print("   ✅ /prestadores/ encontrado")
        else:
            print("   ❌ /prestadores/ NÃO encontrado")
        
        print("\n💡 CONCLUSÃO:")
        if has_wordpress or has_wp_config:
            print("   🔷 Este é um servidor WordPress")
            print("   🎯 Aplicação prestadores está em subdiretório")
            print("   ⚠️ WordPress pode estar interceptando /prestadores/")
        else:
            print("   ❓ Estrutura não é WordPress padrão")
            print("   🎯 Verificar configuração do servidor")
        
        # Fechar conexão
        ftp.quit()
        print("\n" + "=" * 70)
        print("✅ LISTAGEM CONCLUÍDA")
        print("=" * 70)
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
