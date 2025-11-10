#!/usr/bin/env python3
import ftplib
import os
import sys

# FTP Configuration
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
REMOTE_BASE = '/domains/clinfec.com.br/public_html/prestadores'

files_to_upload = [
    'src/Views/atividades/index.php',
    'src/Models/Fornecedor.php',
    'src/Models/Cliente.php'
]

try:
    print("Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=120)
    ftp.login(FTP_USER, FTP_PASS)
    print("✓ Conectado!")
    
    uploaded = 0
    for file_path in files_to_upload:
        if not os.path.exists(file_path):
            print(f"✗ Arquivo não encontrado: {file_path}")
            continue
        
        remote_path = f"{REMOTE_BASE}/{file_path}"
        
        # Criar diretórios se necessário
        remote_dir = os.path.dirname(remote_path)
        dirs = remote_dir.split('/')
        current_path = ""
        for d in dirs:
            if d:
                current_path += "/" + d
                try:
                    ftp.mkd(current_path)
                except:
                    pass  # Diretório já existe
        
        # Upload do arquivo
        print(f"Uploading {file_path}...")
        with open(file_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        
        uploaded += 1
        print(f"✓ {file_path} enviado!")
    
    ftp.quit()
    print(f"\n✓ Upload completo! {uploaded}/{len(files_to_upload)} arquivos enviados")
    
except Exception as e:
    print(f"✗ Erro: {e}")
    sys.exit(1)
