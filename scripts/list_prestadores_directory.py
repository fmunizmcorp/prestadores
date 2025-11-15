#!/usr/bin/env python3
"""
Script para listar arquivos em /public_html/prestadores
Sprint 33 - Verificar conteúdo do diretório correto
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
    print("LISTAGEM DE /public_html/prestadores/")
    print("Sprint 33 - Verificar Arquivos no Diretório Correto")
    print("=" * 70)
    
    try:
        # Conectar FTP
        print("\n1️⃣ Conectando ao FTP...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado: {ftp.getwelcome()}")
        
        # Verificar /public_html/prestadores
        print("\n" + "=" * 70)
        print("2️⃣ LISTANDO: /public_html/prestadores/")
        print("=" * 70)
        
        try:
            ftp.cwd('/public_html/prestadores')
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
                        regular_files.append((name, size))
            
            # Mostrar diretórios
            if directories:
                print("📂 DIRETÓRIOS:")
                for name, _ in sorted(directories):
                    if name not in ['.', '..']:
                        if name in ['config', 'src', 'public']:
                            print(f"   ✅ {name}/ ← Esperado")
                        else:
                            print(f"   📁 {name}/")
            
            # Mostrar arquivos
            if regular_files:
                print("\n📄 ARQUIVOS:")
                for name, size in sorted(regular_files):
                    if name in ['index.php', '.htaccess']:
                        print(f"   ✅ {name} ({size} bytes) ← Esperado")
                    else:
                        print(f"   📄 {name} ({size} bytes)")
            
            # Análise
            print("\n" + "=" * 70)
            print("3️⃣ ANÁLISE")
            print("=" * 70)
            
            has_index = any(name == 'index.php' for name, _ in regular_files)
            has_htaccess = any(name == '.htaccess' for name, _ in regular_files)
            has_config = any(name == 'config' for name, _ in directories if name not in ['.', '..'])
            has_src = any(name == 'src' for name, _ in directories if name not in ['.', '..'])
            
            print("\n🔍 VERIFICAÇÃO:")
            print(f"   {'✅' if has_index else '❌'} index.php")
            print(f"   {'✅' if has_htaccess else '❌'} .htaccess")
            print(f"   {'✅' if has_config else '❌'} config/")
            print(f"   {'✅' if has_src else '❌'} src/")
            
            if has_index and has_htaccess and has_config and has_src:
                print("\n✅ Estrutura CORRETA encontrada!")
                print("   Aplicação está no lugar certo")
            else:
                print("\n⚠️ Estrutura INCOMPLETA!")
                print("   Faltam arquivos/diretórios essenciais")
            
        except ftplib.error_perm as e:
            print(f"❌ Erro ao acessar diretório: {e}")
            print("   O diretório pode não existir ou não ter permissões")
        
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
