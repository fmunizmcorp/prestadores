#!/usr/bin/env python3
"""
DEPLOY CRÍTICO SPRINT 33
Envia apenas arquivos essenciais para desbloquear o sistema
"""

import ftplib
import os
from pathlib import Path

# Credenciais FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Arquivos críticos
PROJECT_ROOT = Path(__file__).parent.parent

CRITICAL_FILES = {
    'public/index.php': 'index.php',
    'config/cache_control.php': 'config/cache_control.php',
}

def main():
    print("🚀 DEPLOY CRÍTICO SPRINT 33")
    print("=" * 60)
    
    try:
        # Conectar
        print(f"Conectando a {FTP_HOST}...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.set_pasv(True)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Conectado: {ftp.getwelcome()}\n")
        
        # Upload de arquivos críticos
        for local_path, remote_path in CRITICAL_FILES.items():
            local_file = PROJECT_ROOT / local_path
            
            if not local_file.exists():
                print(f"⚠️ Arquivo não encontrado: {local_path}")
                continue
            
            print(f"📤 Enviando: {local_path} → {remote_path}")
            
            # Criar diretório se necessário
            if '/' in remote_path:
                remote_dir = os.path.dirname(remote_path)
                try:
                    ftp.mkd(remote_dir)
                except:
                    pass  # Já existe
            
            # Upload
            with open(local_file, 'rb') as f:
                ftp.storbinary(f'STOR {remote_path}', f)
            
            print(f"   ✅ {remote_path} enviado")
        
        print("\n" + "=" * 60)
        print("✅ DEPLOY CRÍTICO CONCLUÍDO!")
        print("🌐 URL: https://prestadores.clinfec.com.br")
        print("⏳ Aguarde 2-3 minutos para cache limpar")
        print("=" * 60)
        
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
