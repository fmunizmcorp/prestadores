#!/usr/bin/env python3
"""
DEPLOY PARA /prestadores - SPRINT 33
Deploy correto para o subdiretório prestadores
"""

import ftplib
import os
from pathlib import Path

# Credenciais
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_DIR = '/public_html/prestadores'  # Diretório correto

PROJECT_ROOT = Path(__file__).parent.parent

def upload_file(ftp, local_file, remote_file):
    """Upload single file"""
    try:
        with open(local_file, 'rb') as f:
            ftp.storbinary(f'STOR {remote_file}', f)
        return True
    except Exception as e:
        print(f"  ❌ Erro: {e}")
        return False

def main():
    print("🚀 DEPLOY PARA /prestadores - SPRINT 33")
    print("=" * 70)
    
    try:
        # Conectar
        print(f"\n1️⃣ Conectando a {FTP_HOST}...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.set_pasv(True)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Conectado: {ftp.pwd()}\n")
        
        # Navegar para prestadores
        print(f"2️⃣ Navegando para {FTP_DIR}...")
        try:
            ftp.cwd(FTP_DIR)
            print(f"✅ Diretório atual: {ftp.pwd()}\n")
        except:
            print(f"⚠️ Diretório {FTP_DIR} não existe. Criando...")
            ftp.mkd(FTP_DIR)
            ftp.cwd(FTP_DIR)
            print(f"✅ Criado e navegado para: {ftp.pwd()}\n")
        
        # Upload index.php
        print("3️⃣ Deploying index.php...")
        index_local = PROJECT_ROOT / 'public' / 'index.php'
        if index_local.exists():
            # Backup primeiro
            try:
                from datetime import datetime
                timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
                ftp.rename('index.php', f'index.php.bak_{timestamp}')
                print(f"   💾 Backup criado: index.php.bak_{timestamp}")
            except:
                pass
            
            if upload_file(ftp, str(index_local), 'index.php'):
                print(f"   ✅ index.php enviado ({index_local.stat().st_size} bytes)\n")
            else:
                print("   ❌ Falha no upload de index.php\n")
        
        # Upload config/cache_control.php
        print("4️⃣ Deploying config/cache_control.php...")
        
        # Criar diretório config se não existir
        try:
            ftp.mkd('config')
        except:
            pass
        
        cache_local = PROJECT_ROOT / 'config' / 'cache_control.php'
        if cache_local.exists():
            if upload_file(ftp, str(cache_local), 'config/cache_control.php'):
                print(f"   ✅ cache_control.php enviado ({cache_local.stat().st_size} bytes)\n")
        
        # Verificar arquivos
        print("5️⃣ Verificando arquivos...")
        try:
            size = ftp.size('index.php')
            print(f"   ✅ index.php: {size} bytes")
        except:
            print(f"   ❌ index.php NÃO ENCONTRADO")
        
        try:
            size = ftp.size('config/cache_control.php')
            print(f"   ✅ config/cache_control.php: {size} bytes")
        except:
            print(f"   ⚠️ config/cache_control.php NÃO ENCONTRADO")
        
        print("\n" + "=" * 70)
        print("✅ DEPLOY CONCLUÍDO!")
        print(f"📍 Diretório: {FTP_DIR}")
        print(f"🌐 URL: https://prestadores.clinfec.com.br")
        print(f"⏳ Aguarde 2-3 minutos para cache limpar")
        print("=" * 70)
        
        ftp.quit()
        return True
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    import sys
    sys.exit(0 if main() else 1)
