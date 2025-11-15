#!/usr/bin/env python3
"""
Upload do auto_deploy_sprint31.php para o servidor
"""

import ftplib
import os
import sys

def upload_with_passive(host, user, password, local_file, remote_file):
    """Upload usando modo passivo"""
    try:
        print(f"Conectando a {host}...")
        ftp = ftplib.FTP()
        ftp.set_pasv(True)  # Modo passivo
        ftp.connect(host, 21, timeout=60)
        ftp.login(user, password)
        
        print("Navegando para diretório...")
        # Tentar diferentes caminhos
        paths = [
            '/public_html/prestadores/public',
            '/domains/clinfec.com.br/public_html/prestadores/public',
            '/prestadores/public'
        ]
        
        for path in paths:
            try:
                ftp.cwd(path)
                print(f"✅ Pasta encontrada: {path}")
                break
            except:
                continue
        
        print(f"Fazendo upload de {os.path.basename(local_file)}...")
        with open(local_file, 'rb') as file:
            ftp.storbinary(f'STOR {remote_file}', file)
        
        size = os.path.getsize(local_file)
        print(f"✅ Upload concluído: {size} bytes")
        
        ftp.quit()
        return True
        
    except Exception as e:
        print(f"❌ Erro: {e}")
        return False

# Configurações
configs = [
    ('clinfec.com.br', 'u673902663', ';>?I4dtn~2Ga'),
    ('ftp.clinfec.com.br', 'u673902663', ';>?I4dtn~2Ga'),
]

local_file = '/home/user/webapp/public/auto_deploy_sprint31.php'
remote_file = 'auto_deploy_sprint31.php'

print("\n" + "=" * 80)
print("📤 UPLOAD AUTO DEPLOY")
print("=" * 80)
print()

for host, user, password in configs:
    print(f"\nTentando {host}...")
    if upload_with_passive(host, user, password, local_file, remote_file):
        print("\n✅ SUCESSO!")
        print(f"\n🌐 Acesse: http://clinfec.com.br/prestadores/public/auto_deploy_sprint31.php?password=sprint31deploy2024")
        print()
        sys.exit(0)
    print()

print("❌ Todas as tentativas falharam")
sys.exit(1)
